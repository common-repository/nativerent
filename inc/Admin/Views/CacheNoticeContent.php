<?php

namespace NativeRent\Admin\Views;

use Exception;
use NativeRent\Admin\Notices\ClearCacheNotice;
use NativeRent\Core\View\ViewInterface;

use function nrentroute;

class CacheNoticeContent implements ViewInterface {

	/** @var ClearCacheNotice */
	public $notice;

	/** @var string */
	public $action;

	/** @var string */
	public $actionMethod;

	/** @var bool */
	public $clearButton = false;

	/**
	 * @param  ClearCacheNotice $notice
	 * @param  bool             $clearButton
	 *
	 * @throws Exception
	 */
	public function __construct( ClearCacheNotice $notice, $clearButton = false ) {
		$this->notice       = $notice;
		$route              = nrentroute( 'reset-cache-flag' );
		$this->action       = $route->path;
		$this->actionMethod = $route->method;
		$this->clearButton  = $clearButton;
	}

	/**
	 * {@inheritDoc}
	 */
	public function getTemplatePath() {
		return 'admin/notices/clear-cache-notice';
	}
}
