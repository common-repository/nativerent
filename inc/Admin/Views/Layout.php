<?php

namespace NativeRent\Admin\Views;

use NativeRent\Core\View\ViewInterface;

class Layout implements ViewInterface {
	/**
	 * @var string
	 */
	protected $templatePath = 'admin/layout';

	/**
	 * Main content.
	 *
	 * @var ViewInterface
	 */
	public $content;

	/** @var bool */
	public $withFooter = false;

	/**
	 * @param  ViewInterface $content
	 * @param  bool          $withFooter
	 */
	public function __construct( ViewInterface $content, $withFooter = false ) {
		add_action(
			'admin_enqueue_scripts',
			function () {
				wp_enqueue_script(
					'nativerent-admin-script',
					plugins_url( 'static/admin/main.js', NATIVERENT_PLUGIN_FILE ),
					[],
					filemtime( NATIVERENT_PLUGIN_DIR . '/static/admin/main.js' )
				);
				wp_enqueue_style(
					'nativerent-admin-style',
					plugins_url( 'static/admin/main.css', NATIVERENT_PLUGIN_FILE ),
					[],
					filemtime( NATIVERENT_PLUGIN_DIR . '/static/admin/main.css' )
				);
			}
		);

		$this->content    = $content;
		$this->withFooter = $withFooter;
	}

	/**
	 * {@inheritDoc}
	 */
	public function getTemplatePath() {
		return $this->templatePath;
	}
}
