<?php

namespace NativeRent\Admin\Notices;

use NativeRent\Admin\SiteCache;
use NativeRent\Admin\Views\CacheNoticeContent;
use NativeRent\Core\Notices\AbstractNotice;
use NativeRent\Core\Notices\NoticeInterface;
use NativeRent\Core\View\Renderer;

class ClearCacheNotice extends AbstractNotice {
	/** @var string */
	protected $level = NoticeInterface::LEVEL_WARNING;

	/** @var int */
	public $cacheFlag;

	/** @var array<string, mixed> */
	public $options
		= [
			'dismissible' => true,
		];

	/**
	 * @param  int $cacheFlag
	 */
	public function __construct( $cacheFlag = 1 ) {
		$this->cacheFlag = $cacheFlag;
	}

	public function getContent() {
		return nrentapp( Renderer::class )->render(
			new CacheNoticeContent( $this, SiteCache::isClearingCachePossible() )
		);
	}
}
