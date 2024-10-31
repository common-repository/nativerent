<?php

namespace NativeRent\Admin\Notices;

use NativeRent\Admin\Views\UnauthorizedNoticeContent;
use NativeRent\Core\Container\Exceptions\DependencyNotFound;
use NativeRent\Core\Notices\AbstractNotice;
use NativeRent\Core\View\Exceptions\TemplateNotFound;
use NativeRent\Core\View\Renderer;

use function nrentapp;

class Unauthorized extends AbstractNotice {
	/** @var string */
	protected $level = self::LEVEL_INFO;

	/** @var array<string, mixed> */
	public $options
		= [
			'dismissible' => true,
		];

	/**
	 * {@inheritDoc}
	 *
	 * @throws TemplateNotFound
	 * @throws DependencyNotFound
	 */
	public function getContent() {
		return nrentapp( Renderer::class )->render(
			new UnauthorizedNoticeContent( $this )
		);
	}
}
