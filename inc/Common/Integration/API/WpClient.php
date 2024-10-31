<?php

namespace NativeRent\Common\Integration\API;

use NativeRent\Common\Integration\API\Payloads\AuthPayload;
use NativeRent\Common\Integration\API\Payloads\MonetizationsPayload;
use NativeRent\Common\Integration\API\Payloads\ReportErrorPayload;
use NativeRent\Common\Integration\API\Payloads\SendStatePayload;
use NativeRent\Common\Integration\API\Payloads\SendStatusPayload;
use WP_Error;
use WP_Http;

use function is_null;
use function is_string;
use function json_decode;
use function json_encode;
use function wp_remote_retrieve_response_code;

/**
 * API Client definition with WordPress HTTP client.
 *
 * TODO: need tests...
 */
final class WpClient implements ClientInterface, AuthorizedClientInterface {
	/**
	 * HTTP client instance.
	 *
	 * @var WP_Http
	 */
	private $http;

	/**
	 * API host.
	 *
	 * @var string
	 */
	private $host;

	/**
	 * Access token.
	 *
	 * @var string
	 */
	private $token;

	/**
	 * Default API path.
	 *
	 * @var string
	 */
	private $defaultPath = '/api/v1/integration/wp/';

	/**
	 * @param  string  $host   API host.
	 * @param  string  $token  Access token.
	 * @param  WP_Http $http   WP Http client instance.
	 */
	public function __construct(
		$host,
		#[\SensitiveParameter]
		$token = '',
		WP_Http $http = null
	) {
		$this->host  = $host;
		$this->token = $token;
		$this->http  = is_null( $http ) ? new WP_Http() : $http;
	}

	/** {@inheritDoc} */
	public function setAuthenticationToken(
		#[\SensitiveParameter]
		$token
	) {
		$this->token = $token;
	}

	/**
	 * {@inheritDoc}
	 *
	 * @throws RequestException
	 */
	public function auth( AuthPayload $payload ) {
		try {
			$response = $this->sendRequest(
				$this->host . '/integration/wp/auth',
				$this->requestOpts(
					[
						'body' => json_encode(
							[
								'domain'    => $payload->getDomain(),
								'email'     => $payload->getLogin(),
								'password'  => $payload->getPassword(),
								'secretKey' => $payload->getSecretKey(),
							]
						),
					],
					false
				)
			);

			return ! empty( $response['body'] )
				? $response['body']
				: [
					'result' => 0,
					'errors' => [],
				];
		} catch ( RequestException $e ) {
			// Checking for validation errors.
			if ( $e->isClientError() && ! empty( $e->getMessage() ) ) {
				$responseData = json_decode( $e->getMessage(), true );
				if ( ! empty( $responseData['errors'] ) ) {
					return [
						'result' => 0,
						'errors' => $responseData['errors'],
					];
				}
			}
			throw $e;
		}
	}

	/**
	 * {@inheritDoc}
	 *
	 * @throws RequestException
	 */
	public function sendState( SendStatePayload $payload ) {
		$response = $this->sendRequest(
			$this->methodURL( 'state' ),
			$this->requestOpts(
				[
					'body' => json_encode(
						[
							'siteID' => $payload->getSiteID(),
							'state'  => $payload->getState(),
						]
					),
				]
			)
		);

		return ! empty( $response['body'] ) ? $response['body'] : [ 'result' => 0 ];
	}

	/**
	 * {@inheritDoc}
	 *
	 * @throws RequestException
	 */
	public function sendStatus( SendStatusPayload $payload ) {
		$response = $this->sendRequest(
			$this->methodURL( 'status' ),
			$this->requestOpts(
				[
					'body' => json_encode(
						[
							'siteID' => $payload->getSiteID(),
							'status'  => $payload->getStatus()->getValue(),
							'version' => $payload->getVersion(),
						]
					),
				]
			)
		);

		return ! empty( $response['body'] ) ? $response['body'] : [ 'result' => 0 ];
	}

	/**
	 * {@inheritDoc}
	 *
	 * @throws RequestException
	 */
	public function monetizations( MonetizationsPayload $payload ) {
		$response = $this->sendRequest(
			$this->methodURL( 'monetizations' ),
			$this->requestOpts(
				[
					'timeout' => 2,
					'body'    => json_encode(
						[
							'siteID' => $payload->getSiteID(),
						]
					),
				]
			)
		);

		return ! empty( $response['body'] )
			? $response['body']
			: [
				'result'        => 0,
				'monetizations' => [],
			];
	}

	/**
	 * {@inheritDoc}
	 *
	 * @throws RequestException
	 */
	public function reportError( ReportErrorPayload $payload ) {
		$this->sendRequest(
			$this->methodURL( 'reportError' ),
			$this->requestOpts(
				[
					'blocking' => false,
					'body'     => json_encode(
						[
							'siteID'  => $payload->getSiteID(),
							'tags'    => $payload->getTags(),
							'context' => $payload->getContext(),
							'error'   => $payload->getError(),
							'extra'   => $payload->getExtra(),
						]
					),
				]
			)
		);
	}

	/**
	 * Get full API method URL.
	 *
	 * @param  string $method
	 *
	 * @return string
	 */
	private function methodURL( $method ) {
		return $this->host . $this->defaultPath . $method;
	}

	/**
	 * Get request options.
	 *
	 * @param  array $replace
	 * @param  bool  $withToken
	 *
	 * @return array
	 */
	private function requestOpts( $replace = [], $withToken = true ) {
		return array_replace_recursive(
			[
				'sslverify'  => false,
				'timeout'    => 5,
				'user-agent' => 'nativerentplugin/' . NATIVERENT_PLUGIN_VERSION,
				'headers'    => [
					'Connection'      => 'keep-alive',
					'Accept'          => 'application/json',
					'Content-type'    => 'application/json; charset=utf-8',
					'X-Forwarded-For' => self::getXForwardedFor(),
					'Authorization'   => $withToken ? 'Bearer ' . $this->token : null,
				],
			],
			$replace
		);
	}

	/**
	 * Get X-Forwarder-For value.
	 *
	 * @return string
	 */
	private static function getXForwardedFor() {
		$remote_addr = sanitize_text_field(
			wp_unslash( isset( $_SERVER['REMOTE_ADDR'] ) ? $_SERVER['REMOTE_ADDR'] : '' )
		);
		if ( filter_var( $remote_addr, FILTER_VALIDATE_IP ) === false ) {
			return '';
		}

		return $remote_addr;
	}

	/**
	 * Sending request.
	 *
	 * @param  string $url      Request URL.
	 * @param  array  $options  WP Http request options.
	 *
	 * @return array
	 *
	 * @throws RequestException
	 */
	private function sendRequest( $url, $options ) {
		$res = $this->http->post( $url, $options );
		if ( $res instanceof WP_Error ) {
			throw RequestException::fromWpError( $res ); // phpcs:ignore
		}

		// Client errors.
		$statusCode = wp_remote_retrieve_response_code( $res );
		if ( RequestException::isClientStatusError( $statusCode ) ) {
			throw RequestException::fromWpHttpResponse( $res ); // phpcs:ignore
		}

		if ( is_string( @$res['body'] ) ) {
			$res['body'] = json_decode( $res['body'], true );
		}

		return $res;
	}
}
