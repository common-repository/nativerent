<?php

namespace NativeRent\Admin\Views;

use NativeRent\Core\View\ViewInterface;

class AuthForm implements ViewInterface {

	/**
	 * @var string
	 */
	public $actionURL;

	/**
	 * Login value from previous request.
	 *
	 * @var string
	 */
	public $login;

	/** @var string[] */
	public $errors;

	/**
	 * @param  string                 $login
	 * @param string<string, string> $errors
	 *
	 * @throws \Exception
	 */
	public function __construct( $login = '', $errors = [] ) {
		$this->actionURL = nrentroute( 'auth.auth' )->path;
		$this->login = $login;
		$this->errors = array_values( $errors );
	}

	/**
	 * {@inheritDoc}
	 */
	public function getTemplatePath() {
		return 'admin/auth-form';
	}
}
