<?php

namespace NativeRent\Admin\Notices;

use NativeRent\Admin\Views\InvalidTokenNoticeContent;
use NativeRent\Core\Container\Exceptions\DependencyNotFound;
use NativeRent\Core\Notices\AbstractNotice;
use NativeRent\Core\View\Exceptions\TemplateNotFound;
use NativeRent\Core\View\Renderer;

use function nrentapp;

class InvalidApiToken extends AbstractNotice {
	/** @var string */
	protected $level = self::LEVEL_WARNING;

	/** @var array<string, mixed> */
	public $options
		= [
			'dismissible' => true,
		];

	/**
	 * {@inheritDoc}
	 *
	 * @throws DependencyNotFound|TemplateNotFound
	 */
	public function getContent() {
		return nrentapp( Renderer::class )->render(
			new InvalidTokenNoticeContent( $this )
		);
	}
}
