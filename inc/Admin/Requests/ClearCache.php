<?php

namespace NativeRent\Admin\Requests;

/**
 * @phpcs:disable WordPress.Security.NonceVerification.Missing
 */
class ClearCache extends AbstractRequest {
	/**
	 * @var bool
	 * @readonly
	 */
	public $needToClear = false;

	public function __construct() {
		if ( $this->verifyNonce( 'nrent_clear-cache' ) ) {
			$this->needToClear = isset( $_POST['nrent_clear_cache'] );
		}
	}
}
