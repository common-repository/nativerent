<?php

namespace NativeRent\Common\Entities;

use JsonSerializable;

/**
 * Class for getting info about current CMS.
 */
class CmsInfo implements JsonSerializable {

	/**
	 * @var string
	 */
	private $cms;

	/**
	 * @var string
	 */
	private $version;

	/**
	 * @var PluginItem[]
	 */
	private $plugins;

	/**
	 * @param  string       $version
	 * @param  PluginItem[] $plugins
	 */
	public function __construct( $version = '', $plugins = [] ) {
		$this->cms     = 'WordPress';
		$this->version = $version;
		$this->plugins = $plugins;
	}

	/**
	 * Auto filled instance.
	 *
	 * @return self
	 */
	public static function autoCreate() {
		return new self( self::getWordPressVersion(), self::getAllInstalledPlugins() );
	}

	/**
	 * Get current WordPress version.
	 *
	 * @return string
	 */
	public static function getWordPressVersion() {
		global $wp_version;

		return ( ! empty( $wp_version ) ? $wp_version : 'undefined' );
	}

	/**
	 * Get all installed plugins.
	 *
	 * @return PluginItem[]
	 */
	public static function getAllInstalledPlugins() {
		if ( ! function_exists( 'get_plugins' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}
		$plugins = ( function_exists( 'get_plugins' ) ? get_plugins() : [] );
		$res     = [];
		foreach ( $plugins as $p ) {
			if ( empty( $p['Name'] ) ) {
				continue;
			}
			$res[] = new PluginItem(
				$p['Name'],
				isset( $p['Version'] ) ? $p['Version'] : 'undefined',
				isset( $p['PluginURI'] ) ? $p['PluginURI'] : ''
			);
		}

		return $res;
	}

	/**
	 * Get current CMS version.
	 *
	 * @return string
	 */
	public function getCmsVersion() {
		return $this->version;
	}

	/**
	 * Get current CMS name.
	 *
	 * @return string
	 */
	public function getCmsName() {
		return $this->cms;
	}

	/**
	 * Get all installed plugins.
	 *
	 * @return array
	 */
	public function getPlugins() {
		return $this->plugins;
	}

	/**
	 * {@inheritDoc}
	 */
	#[\ReturnTypeWillChange]
	public function jsonSerialize() {
		return [
			'cms'     => $this->getCmsName(),
			'version' => $this->getCmsVersion(),
			'plugins' => $this->getPlugins(),
		];
	}
}
