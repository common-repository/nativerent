<?php

namespace NativeRent\Common\Integration\API;

use Closure;
use NativeRent\Common\Integration\API\Payloads\AuthPayload;
use NativeRent\Common\Integration\API\Payloads\MonetizationsPayload;
use NativeRent\Common\Integration\API\Payloads\ReportErrorPayload;
use NativeRent\Common\Integration\API\Payloads\SendStatePayload;
use NativeRent\Common\Integration\API\Payloads\SendStatusPayload;

final class Client implements ClientInterface {
	/**
	 * @var ClientInterface|AuthorizedClientInterface $client
	 */
	private $client;

	/**
	 * Additional request error handlers.
	 *
	 * @var array<array-key, Closure | callable(RequestException): void>
	 */
	private $onRequestErrorHandlers = [];

	public function __construct( ClientInterface $client ) {
		$this->client = $client;
	}

	/**
	 * @param  Closure | callable(RequestException): void $handler
	 *
	 * @return self
	 */
	public function onRequestError( Closure $handler ) {
		$this->onRequestErrorHandlers[] = $handler;

		return $this;
	}

	/**
	 * Setup token.
	 *
	 * @param  string $token  Auth token.
	 *
	 * @return $this
	 */
	public function withToken(
		#[\SensitiveParameter]
		$token
	) {
		if ( $this->client instanceof AuthorizedClientInterface ) {
			$this->client->setAuthenticationToken( $token );
		}

		return $this;
	}

	/**
	 * @param  Closure $cb
	 *
	 * @return mixed|void
	 * @throws RequestException
	 */
	private function wrapRequest( Closure $cb ) {
		try {
			return $cb();
		} catch ( RequestException $re ) {
			foreach ( $this->onRequestErrorHandlers as $handler ) {
				$handler( $re );
			}

			if ( ! $re->isSuppressed() ) {
				throw $re;
			}
		}
	}

	/**
	 * {@inheritDoc}
	 */
	public function auth( AuthPayload $payload ) {
		return $this->wrapRequest(
			function () use ( $payload ) {
				return $this->client->auth( $payload );
			}
		);
	}

	/**
	 * {@inheritDoc}
	 */
	public function sendStatus( SendStatusPayload $payload ) {
		return $this->wrapRequest(
			function () use ( $payload ) {
				return $this->client->sendStatus( $payload );
			}
		);
	}

	/**
	 * {@inheritDoc}
	 */
	public function sendState( SendStatePayload $payload ) {
		return $this->wrapRequest(
			function () use ( $payload ) {
				return $this->client->sendState( $payload );
			}
		);
	}


	/**
	 * {@inheritDoc}
	 */
	public function monetizations( MonetizationsPayload $payload ) {
		return $this->wrapRequest(
			function () use ( $payload ) {
				return $this->client->monetizations( $payload );
			}
		);
	}

	/**
	 * {@inheritDoc}
	 *
	 * @throws RequestException
	 */
	public function reportError( ReportErrorPayload $payload ) {
		return $this->client->reportError( $payload );
	}
}
