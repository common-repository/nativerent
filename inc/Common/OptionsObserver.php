<?php

namespace NativeRent\Common;

use NativeRent\Common\Events\OptionUpdated;
use NativeRent\Core\Events\DispatcherInterface;

/**
 * TODO: need tests
 */
final class OptionsObserver {

	/** @var DispatcherInterface */
	private $dispatcher;

	public function __construct( DispatcherInterface $dispatcher ) {
		$this->dispatcher = $dispatcher;
	}

	/**
	 * Updating option trigger.
	 *
	 * @param string $option Option name.
	 * @param mixed  $value Updated value.
	 *
	 * @return void
	 */
	public function updated( $option, $value ) {
		$this->dispatcher->dispatch( new OptionUpdated( $option, $value ) );
	}
}
