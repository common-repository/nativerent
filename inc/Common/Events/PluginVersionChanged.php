<?php

namespace NativeRent\Common\Events;

use NativeRent\Core\Events\EventInterface;

final class PluginVersionChanged implements EventInterface {

	/** @var string */
	private $newVersion;

	/** @var string|null */
	private $previousVersion;

	/**
	 * @param string      $newVersion
	 * @param string|null $previousVersion
	 */
	public function __construct( $newVersion, $previousVersion = null ) {
		$this->newVersion = $newVersion;
		$this->previousVersion = $previousVersion;
	}

	/**
	 * @return string
	 */
	public function getNewVersion() {
		return $this->newVersion;
	}

	/**
	 * @return string|null
	 */
	public function getPreviousVersion() {
		return $this->previousVersion;
	}

	/**
	 * {@inheritDoc}
	 */
	public static function getEventName() {
		return 'plugin-version-changed';
	}
}
