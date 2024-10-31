<?php

namespace NativeRent\Common\Integration\API;

use NativeRent\Common\Integration\API\Payloads\AuthPayload;
use NativeRent\Common\Integration\API\Payloads\MonetizationsPayload;
use NativeRent\Common\Integration\API\Payloads\ReportErrorPayload;
use NativeRent\Common\Integration\API\Payloads\SendStatePayload;
use NativeRent\Common\Integration\API\Payloads\SendStatusPayload;

/**
 *  Native Rent CMS Integration API interface.
 */
interface ClientInterface {
	/**
	 * Native Rent authentication method.
	 *
	 * @param  AuthPayload $payload
	 *
	 * @return array{
	 *     result: int,
	 *     siteID?: string,
	 *     token?: string,
	 *     settings?: array{patterns: string[], monetizations: array{regular: int, ntgb: int}},
	 *     errors?: string[]
	 * }
	 *
	 * @throws RequestException
	 */
	public function auth( AuthPayload $payload );

	/**
	 * Send actual integration status.
	 *
	 * @param  SendStatusPayload $payload
	 *
	 * @return array{result: int}
	 *
	 * @throws RequestException
	 */
	public function sendStatus( SendStatusPayload $payload );

	/**
	 * Send actual integration status.
	 *
	 * @param  SendStatePayload $payload
	 *
	 * @return array{result: int}
	 *
	 * @throws RequestException
	 */
	public function sendState( SendStatePayload $payload );

	/**
	 * Get monetization statuses.
	 *
	 * @param  MonetizationsPayload $payload
	 *
	 * @return array{result: int, monetizations: array{regular: int, ntgb: int}
	 *
	 * @throws RequestException
	 */
	public function monetizations( MonetizationsPayload $payload );

	/**
	 * Error sending.
	 *
	 * @param  ReportErrorPayload $payload
	 *
	 * @return void
	 */
	public function reportError( ReportErrorPayload $payload );
}
