<?php

namespace NativeRent\Admin\Views;

use NativeRent\Core\Notices\NoticeInterface;
use NativeRent\Core\View\ViewInterface;

class NoticeLayout implements ViewInterface {

	/** @var NoticeInterface */
	public $notice;

	public function __construct( NoticeInterface $notice ) {
		$this->notice = $notice;
	}

	/**
	 * {@inheritDoc}
	 */
	public function getTemplatePath() {
		return 'admin/wp-notice';
	}
}
