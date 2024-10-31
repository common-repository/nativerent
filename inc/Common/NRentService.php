<?php

namespace NativeRent\Common;

use Exception;
use NativeRent\Common\Entities\CmsInfo;
use NativeRent\Common\Entities\IntegrationStatus;
use NativeRent\Common\Entities\Monetizations;
use NativeRent\Common\Entities\State;
use NativeRent\Common\Integration\API\Client;
use NativeRent\Common\Integration\API\ClientInterface;
use NativeRent\Common\Integration\API\Payloads\AuthPayload;
use NativeRent\Common\Integration\API\Payloads\MonetizationsPayload;
use NativeRent\Common\Integration\API\Payloads\ReportErrorPayload;
use NativeRent\Common\Integration\API\Payloads\SendStatePayload;
use NativeRent\Common\Integration\API\Payloads\SendStatusPayload;
use NativeRent\Common\Integration\API\RequestException;

use function is_array;
use function is_string;
use function mt_rand;
use function strlen;

final class NRentService {
	/**
	 * @var Client
	 */
	private $client;

	/**
	 * @var Options
	 */
	private $options;

	public function __construct(
		ClientInterface $client,
		Options $options
	) {
		$this->client  = new Client( $client );
		$this->options = $options;

		$this->client->onRequestError(
			function ( RequestException $re ) {
				if ( 401 == $re->getCode() ) {
					$this->options->setInvalidTokenFlag();
				}

				// TODO: наверняка не лучшее решение просто глушить такие ошибки.
				if ( $re->isClientError() ) {
					$re->suppress();
				}
			}
		);
	}

	/**
	 * Authorize integration.
	 *
	 * TODO: Вместо отправки списка ошибок лучше выбрасывать исключение.
	 *
	 * @param  string $domain    Site domain (ASCII).
	 * @param  string $login     NR User login.
	 * @param  string $password  NR User password.
	 *
	 * @return array{success: bool, errors: string[]}
	 * @throws RequestException
	 */
	public function authorize(
		$domain,
		$login,
		#[\SensitiveParameter]
		$password
	) {
		$result    = [
			'success' => false,
			'errors'  => [],
		];
		$secretKey = self::generateSecretKey();
		$response = $this->client->auth( new AuthPayload( $domain, $login, $password, $secretKey ) );

		// Check auth errors.
		if ( ! empty( $response['errors'] ) ) {
			$result['errors'] = $response['errors'];

			return $result;
		}

		$isSuccess     = ( 1 == @$response['result'] );
		$siteID        = @$response['siteID'];
		$accessToken   = @$response['token'];
		$patterns      = @$response['settings']['patterns'];
		$monetizations = @$response['settings']['monetizations'];

		// Validate response data. TODO: нужно исключение.
		if ( ! $isSuccess || ! is_string( $siteID ) || ! is_string( $accessToken ) ) {
			$result['success'] = false;

			return $result;
		}

		// Saving data.
		$this->options->setPluginSecretKey( $secretKey );
		$this->options->setSiteID( $siteID );
		$this->options->setIntegrationAccessToken( $accessToken );
		if ( is_array( $patterns ) ) {
			$this->options->updateAdvPatterns( $patterns );
		}
		if ( is_array( $monetizations ) ) {
			$this->options->updateMonetizations( Monetizations::hydrate( $monetizations ) );
		}
		$result['success'] = true;

		// Set access token to client.
		$this->client->withToken( $accessToken );
		$this->options->setInvalidTokenFlag( false );

		// Activation handler.
		$this->sendActivatedStatus();

		// Send actual state to Native Rent.
		$this->sendCurrentState();

		return $result;
	}

	/**
	 * Actualize monetizations statuses.
	 *
	 * @return void
	 * @throws Exception
	 */
	public function loadMonetizations() {
		$siteID = $this->options->getSiteID();
		if ( empty( $siteID ) ) {
			return;
		}

		$response = $this->client->monetizations( new MonetizationsPayload( $siteID ) );
		if ( empty( $response['result'] ) || empty( $response['monetizations'] ) ) {
			return;
		}

		$this->options->updateMonetizations( Monetizations::hydrate( $response['monetizations'] ) );
	}

	/**
	 * Sending actual state to the NR system.
	 *
	 * @return bool
	 * @throws RequestException
	 */
	public function sendCurrentState() {
		if ( empty( $this->options->getSiteID() ) ) {
			return;
		}

		$state = new State(
			$this->options->getStateOptions(),
			CmsInfo::autoCreate()
		);
		$res   = $this->client->sendState( new SendStatePayload( $state->options->siteID, $state ) );

		return ! empty( $res['result'] );
	}

	/**
	 * Notify Native Rent about deactivation.
	 *
	 * @return void
	 * @throws RequestException
	 */
	public function sendDeactivatedStatus() {
		$this->sendStatus( IntegrationStatus::deactivated() );
	}

	/**
	 * Notify Native Rent about activation.
	 *
	 * @return void
	 * @throws RequestException
	 */
	public function sendActivatedStatus() {
		$this->sendStatus( IntegrationStatus::activated() );
	}

	/**
	 * Logout method.
	 *
	 * @param  bool $uninstall  Uninstall flag.
	 *
	 * @return void
	 * @throws RequestException
	 */
	public function logout( $uninstall = false ) {
		$siteID = $this->options->getSiteID();
		$this->options->purge();
		$this->sendStatus(
			$uninstall ? IntegrationStatus::uninstalled() : IntegrationStatus::deactivated(),
			$siteID
		);
	}

	/**
	 * Sending error to Native Rent tracker.
	 *
	 * @param  Exception|\Throwable $e
	 *
	 * @return void
	 * @throws RequestException
	 */
	public function sendErrorToTracker( $e ) {
		$siteID = $this->options->getSiteID();
		if ( empty( $siteID ) ) {
			return;
		}

		global $wp_version;
		$env = @getenv( 'WORDPRESS_ENV' );

		$this->client->reportError(
			new ReportErrorPayload(
				$siteID,
				$e,
				[
					'release'     => defined( 'NATIVERENT_PLUGIN_VERSION' ) ? NATIVERENT_PLUGIN_VERSION : 'undefined',
					'environment' => ! empty( $env ) ? $env : 'production',
				],
				[
					'cms' => 'Wordpress ' . ( ! empty( $wp_version ) ? $wp_version : '(undefined)' ),
				]
			)
		);
	}

	/**
	 * Send status to Native Rent.
	 *
	 * @param  IntegrationStatus $status
	 * @param  string|null       $siteID
	 *
	 * @return void
	 * @throws RequestException
	 */
	private function sendStatus( IntegrationStatus $status, $siteID = null ) {
		$siteID = ! empty( $siteID ) ? $siteID : $this->options->getSiteID();
		if ( empty( $siteID ) ) {
			return;
		}

		$this->client->sendStatus(
			new SendStatusPayload( $siteID, $status )
		);
	}

	/**
	 * Generates secret key.
	 *
	 * @param  int $length  Max length.
	 *
	 * @return string
	 */
	private static function generateSecretKey( $length = 32 ) {
		$alphabet   = '#$%abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890';
		$alpha_len  = strlen( $alphabet ) - 1;
		$secret_key = '';
		for ( $i = 0; $i < $length; $i++ ) {
			$secret_key .= $alphabet[ mt_rand( 0, $alpha_len ) ];
		}

		return $secret_key;
	}
}
