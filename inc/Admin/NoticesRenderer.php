<?php

namespace NativeRent\Admin;

use NativeRent\Admin\Views\NoticeLayout;
use NativeRent\Admin\Views\Notices\WpNotice;
use NativeRent\Core\Notices\NoticeInterface;
use NativeRent\Core\View\Renderer;

/**
 * Notices registry implementation for WordPress.
 * TODO: need tests!
 */
final class NoticesRenderer {
	/** @var Renderer */
	private $renderer;

	public function __construct( Renderer $renderer ) {
		$this->renderer = $renderer;
	}

	public function __invoke( NoticeInterface $notice, $priority = 10 ) {
		add_action(
			'admin_notices',
			function () use ( $notice ) {
				$this->renderer->display( new NoticeLayout( $notice ) );
			},
			$priority
		);

		return $this;
	}
}
