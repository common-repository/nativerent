<?php

namespace NativeRent\Admin\Views;

use NativeRent\Admin\Notices\InvalidApiToken;
use NativeRent\Admin\Notices\Unauthorized;
use NativeRent\Core\View\ViewInterface;

class UnauthorizedNoticeContent implements ViewInterface {

	/** @var InvalidApiToken */
	public $notice;

	public function __construct( Unauthorized $notice ) {
		$this->notice = $notice;
	}

	/**
	 * {@inheritDoc}
	 */
	public function getTemplatePath() {
		return 'admin/notices/unauthorized';
	}
}
