<?php

namespace NativeRent\Common;

use NativeRent\Core\Container\Container;

/**
 * Base bootloader class.
 */
abstract class AbstractBootloader {
	/** @var Container */
	protected $container;

	/**
	 * Bootloader initialization.
	 *
	 * @param  Container $container  DI container.
	 */
	public function __construct( Container $container ) {
		$this->container = $container;
		$this->onRegister();
	}

	/**
	 * This method calls when bootloader was registered.
	 *
	 * @return void
	 */
	abstract public function onRegister();
}
