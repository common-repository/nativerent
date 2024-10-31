<?php

namespace NativeRent\Common\Events;

use NativeRent\Core\Events\EventInterface;

class OptionUpdated implements EventInterface {

	/** @var string */
	private $option;

	/** @var mixed */
	private $value;

	/**
	 * @param string $option
	 * @param mixed  $value
	 */
	public function __construct( $option, $value = null ) {
		$this->option = $option;
		$this->value = $value;
	}

	/**
	 * @return string
	 */
	public function getOption() {
		return $this->option;
	}

	/**
	 * @return mixed
	 */
	public function getValue() {
		return $this->value;
	}

	/**
	 * {@inheritDoc}
	 */
	public static function getEventName() {
		return 'option-updated';
	}
}
