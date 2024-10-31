<?php

namespace NativeRent\Admin\Requests;

use function sanitize_email;
use function sanitize_text_field;
use function wp_unslash;

/**
 * @phpcs:disable WordPress.Security.NonceVerification.Missing
 */
class Auth extends AbstractRequest {
	/**
	 * @var string
	 * @readonly
	 */
	public $login;

	/**
	 * @var string
	 * @readonly
	 */
	private $password;

	public function __construct() {
		if ( $this->verifyNonce( 'nrent_auth' ) ) {
			$this->login    = isset( $_POST['nrent_auth_login'] )
				? sanitize_email( wp_unslash( $_POST['nrent_auth_login'] ) )
				: null;
			$this->password = isset( $_POST['nrent_auth_password'] )
				? sanitize_text_field( wp_unslash( $_POST['nrent_auth_password'] ) )
				: null;
		}
	}

	/**
	 * @return string|null
	 */
	public function getPassword() {
		return $this->password;
	}
}
