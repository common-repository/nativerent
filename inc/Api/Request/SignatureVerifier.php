<?php

namespace NativeRent\Api\Request;

use InvalidArgumentException;

/**
 * API request verification class.
 */
final class SignatureVerifier {
	/** @var string */
	private $secretKey;

	/**
	 * @param  string $secretKey
	 *
	 * @throws InvalidArgumentException
	 */
	public function __construct(
		#[\SensitiveParameter]
		$secretKey
	) {
		if ( ! is_string( $secretKey ) ) {
			throw new InvalidArgumentException( 'Invalid `secretKey` value. Must be string.' );
		}

		$this->secretKey = $secretKey;
	}

	/**
	 * Verify signature value.
	 *
	 * @param  array $requestBody  Decoded request body.
	 *
	 * @return bool
	 */
	public function verify( $requestBody ) {
		if ( ! is_array( $requestBody ) ) {
			return false;
		}

		$sign = @$requestBody['signature'];
		if ( empty( $sign ) ) {
			return false;
		}

		unset( $requestBody['signature'] );
		$expectedSign = $this->getSignature( $requestBody );

		return ( $expectedSign === $sign );
	}

	/**
	 * Generate signature by request body.
	 *
	 * @param  array $body
	 *
	 * @return string
	 */
	private function getSignature( $body ) {
		return md5( json_encode( $body ) . $this->secretKey );
	}
}
