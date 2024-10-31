<?php

namespace NativeRent\Common\Integration\API;

use Exception;

use function is_numeric;
use function wp_remote_retrieve_body;
use function wp_remote_retrieve_response_code;

class RequestException extends Exception {

	/** @var bool */
	protected $suppress = false;

	public function suppress() {
		$this->suppress = true;
	}

	/** @return bool */
	public function isSuppressed() {
		return $this->suppress;
	}

	/**
	 * @param  int $status
	 *
	 * @return bool
	 */
	public static function isClientStatusError( $status ) {
		return is_numeric( $status ) && 400 <= $status && 500 > $status;
	}

	public function isClientError() {
		return self::isClientStatusError( $this->getCode() );
	}

	public static function fromWpError( \WP_Error $error ) {
		return new self( $error->get_error_message(), $error->get_error_code() );
	}

	public static function fromWpHttpResponse( array $response ) {
		return new self( wp_remote_retrieve_body( $response ), wp_remote_retrieve_response_code( $response ) );
	}
}
