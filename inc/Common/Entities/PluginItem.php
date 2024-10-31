<?php

namespace NativeRent\Common\Entities;

use JsonSerializable;

/**
 * Plugin item class for creating a collection of all installed plugins.
 */
class PluginItem implements JsonSerializable {
	/**
	 * @var string
	 */
	public $name;
	/**
	 * @var string
	 */
	public $version;

	/**
	 * @var string
	 */
	public $url;

	/**
	 * @param  string $name     Plugin name.
	 * @param  string $version  Plugin version.
	 * @param  string $url      Plugin page URL.
	 */
	public function __construct( $name, $version, $url = '' ) {
		$this->name    = $name;
		$this->version = $version;
		$this->url     = $url;
	}

	/**
	 * {@inheritDoc}
	 */
	#[\ReturnTypeWillChange]
	public function jsonSerialize() {
		return (array) $this;
	}
}
