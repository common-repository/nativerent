<?php

namespace NativeRent\Admin\Views;

use NativeRent\Admin\Notices\InvalidApiToken;
use NativeRent\Core\View\ViewInterface;

class InvalidTokenNoticeContent implements ViewInterface {

	/** @var InvalidApiToken */
	public $notice;

	public function __construct( InvalidApiToken $notice ) {
		$this->notice = $notice;
	}

	/**
	 * {@inheritDoc}
	 */
	public function getTemplatePath() {
		return 'admin/notices/invalid-token';
	}
}
