<?php

namespace NativeRent\Common\Entities;

/**
 * Site moderation status implementation.
 */
class SiteModerationStatus {
	const MODERATION = 1;
	const APPROVED = 2;
	const REJECTED = 3;

	/**
	 * Current status.
	 *
	 * @var int
	 */
	private $value;

	/**
	 * @param  int|numeric-string|null $value  Status value.
	 */
	public function __construct( $value = null ) {
		$this->value = $this->isValidStatus( $value ) ? (int) $value : self::MODERATION;
	}

	/**
	 * Checking status value.
	 *
	 * @param  int|numeric-string $value  Status value.
	 *
	 * @return bool
	 */
	private function isValidStatus( $value ) {
		return in_array( $value, [ self::MODERATION, self::REJECTED, self::APPROVED ] );
	}

	/**
	 * Status value MODERATION ?
	 *
	 * @return bool
	 */
	public function isModeration() {
		return self::MODERATION === $this->value;
	}

	/**
	 * Status value REJECTED ?
	 *
	 * @return bool
	 */
	public function isRejected() {
		return self::REJECTED === $this->value;
	}

	/**
	 * Get current value.
	 *
	 * @return int
	 */
	public function getValue() {
		return $this->value;
	}
}
