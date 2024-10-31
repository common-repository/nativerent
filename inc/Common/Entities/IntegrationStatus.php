<?php

namespace NativeRent\Common\Entities;

use InvalidArgumentException;

/** TODO: need tests */
class IntegrationStatus {
	const UNINSTALLED = 'uninstalled';
	const DEACTIVATED = 'deactivated';
	const ACTIVATED = 'activated';

	/** @var string */
	protected $value;

	/**
	 * @param string $value
	 * @throws InvalidArgumentException
	 */
	public function __construct( $value ) {
		if ( ! $this->checkStatusValue( $value ) ) {
			throw new InvalidArgumentException( 'Invalid status value' );
		}

		$this->value = $value;
	}

	/**
	 * Current status value.
	 *
	 * @return string
	 */
	public function getValue() {
		return $this->value;
	}

	/**
	 * @param string $status
	 *
	 * @return bool
	 */
	private function checkStatusValue( $status ) {
		return in_array( $status, [ self::ACTIVATED, self::DEACTIVATED, self::UNINSTALLED ] );
	}

	/**
	 * @return self
	 */
	public static function activated() {
		return new self( self::ACTIVATED );
	}

	/**
	 * @return self
	 */
	public static function deactivated() {
		return new self( self::DEACTIVATED );
	}

	/**
	 * @return self
	 */
	public static function uninstalled() {
		return new self( self::UNINSTALLED );
	}

	/**
	 * @return string
	 */
	public function __toString() {
		return $this->getValue();
	}
}
