<?php

namespace NativeRent\Admin\Requests;

use function wp_verify_nonce;

abstract class AbstractRequest {
	/**
	 * Nonce verification method.
	 *
	 * @param  string|int $action  Action name.
	 *
	 * @return bool
	 */
	protected function verifyNonce( $action = - 1 ) {
		return (bool) wp_verify_nonce(
			isset( $_POST['_wpnonce'] ) ? sanitize_key( $_POST['_wpnonce'] ) : '',
			$action
		);
	}
}
