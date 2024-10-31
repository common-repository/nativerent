<?php

namespace NativeRent\Frontend\AdBlocker;

use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Engine\Optimization\DelayJS\HTML;
use WP_Rocket\Engine\Optimization\DynamicLists\DataManager;

/**
 * Extension for compatibility with WP Rocket plugin.
 */
final class WPRocketExtension implements ExtensionInterface {
	/** @var HTML|null */
	private $html = null;

	/** @var bool */
	private $delayJS = false;

	public function __construct() {
		if (
			version_compare( PHP_VERSION, '7.0.0' ) < 0
			||
			! class_exists( HTML::class )
			||
			! class_exists( Options_Data::class )
		) {
			return;
		}

		try {
			$opts = get_option( 'wp_rocket_settings' );
			if ( is_array( $opts ) && isset( $opts['delay_js'] ) ) {
				$this->html    = new HTML(
					new Options_Data( $opts ),
					class_exists( DataManager::class ) ? new DataManager() : null
				);
				$this->delayJS = true;
			}
		} catch ( \Throwable $e ) {
			$this->delayJS = false;
		}
	}

	/**
	 * {@inheritDoc}
	 */
	public function matchHandler( $match ) {
		if (
			! $this->delayJS
			|| is_null( $this->html )
			|| false !== stripos( $match, 'type="rocketlazyloadscript"' )
		) {
			return $match;
		}

		try {
			return $this->html->delay_js( $match );
		} catch ( \Throwable $e ) {
			return $match;
		}

		return $match;
	}
}
