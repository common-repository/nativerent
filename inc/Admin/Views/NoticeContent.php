<?php

namespace NativeRent\Admin\Views;

use NativeRent\Core\Notices\NoticeInterface;
use NativeRent\Core\View\ViewInterface;

class NoticeContent implements ViewInterface {

	/** @var NoticeInterface */
	public $notice;

	/** @var string */
	private $template;

	public function __construct( NoticeInterface $notice, $template = '' ) {
		$this->notice   = $notice;
		$this->template = $template;
	}

	/**
	 * {@inheritDoc}
	 */
	public function getTemplatePath() {
		return $this->template;
	}
}
