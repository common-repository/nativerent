<?php

namespace NativeRent\Common\Entities;

use JsonSerializable;

/**
 * Native Rent monetizations struct.
 */
class Monetizations implements JsonSerializable {
	const MODERATION = - 1;
	const REJECTED = 0;
	const APPROVED = 1;

	/**
	 * Regular monetization status.
	 *
	 * @var int Status value.
	 */
	protected $regular;

	/**
	 * NTGB monetization status.
	 *
	 * @var int Status value.
	 */
	protected $ntgb;

	/**
	 * Constructor
	 *
	 * @param  int|numeric-string $regular  Regular status.
	 * @param  int|numeric-string $ntgb     NTGB status.
	 */
	public function __construct(
		$regular = self::MODERATION,
		$ntgb = self::MODERATION
	) {
		$this->setRegularStatus( $regular );
		$this->setNtgbStatus( $ntgb );
	}

	/**
	 * Status value validation
	 *
	 * @param  int|string $val  Status value.
	 *
	 * @return bool
	 */
	protected function isValidStatus( $val ) {
		return in_array(
			$val,
			[
				self::APPROVED,
				self::MODERATION,
				self::REJECTED,
			]
		);
	}

	/**
	 * Regular monetization status setter.
	 *
	 * @param  int|numeric-string $val  Status value.
	 *
	 * @return void
	 */
	protected function setRegularStatus( $val ) {
		$this->regular = (int) ( $this->isValidStatus( $val ) ? $val : self::MODERATION );
	}

	/**
	 * NTGB monetization status setter.
	 *
	 * @param  int|numeric-string $val  Status value.
	 *
	 * @return void
	 */
	protected function setNtgbStatus( $val ) {
		$this->ntgb = (int) ( $this->isValidStatus( $val ) ? $val : self::MODERATION );
	}

	/**
	 * Regular status getter
	 *
	 * @return int
	 */
	public function getRegularStatus() {
		return $this->regular;
	}

	/**
	 * NTGB status getter
	 *
	 * @return int
	 */
	public function getNtgbStatus() {
		return $this->ntgb;
	}

	/**
	 * Check to all monetizations is rejected.
	 *
	 * @return bool
	 */
	public function isAllRejected() {
		return (
			$this->isRegularRejected() && $this->isNtgbRejected()
		);
	}

	/**
	 * Check to all monetizations on moderation.
	 *
	 * @return bool
	 */
	public function isAllOnModeration() {
		return (
			$this->isRegularOnModeration() && $this->isNtgbOnModeration()
		);
	}

	/**
	 * Check to all monetizations is approved.
	 *
	 * @return bool
	 */
	public function isAllApproved() {
		return (
			$this->isRegularApproved() && $this->isNtgbApproved()
		);
	}

	/**
	 * Check to REGULAR monetization is rejected.
	 *
	 * @return bool
	 */
	public function isRegularRejected() {
		return ( self::REJECTED === $this->regular );
	}

	/**
	 * Check to REGULAR monetization is approved.
	 *
	 * @return bool
	 */
	public function isRegularApproved() {
		return ( self::APPROVED === $this->regular );
	}

	/**
	 * Check to REGULAR monetization on moderation.
	 *
	 * @return bool
	 */
	public function isRegularOnModeration() {
		return ( self::MODERATION === $this->regular );
	}

	/**
	 * Check to NTGB monetizations is rejected.
	 *
	 * @return bool
	 */
	public function isNtgbRejected() {
		return ( self::REJECTED === $this->ntgb );
	}

	/**
	 * Check to NTGB monetizations is approved.
	 *
	 * @return bool
	 */
	public function isNtgbApproved() {
		return ( self::APPROVED === $this->ntgb );
	}

	/**
	 * Check to NTGB monetizations on moderation.
	 *
	 * @return bool
	 */
	public function isNtgbOnModeration() {
		return ( self::MODERATION === $this->ntgb );
	}

	/**
	 * Has approved monetizations.
	 *
	 * @return bool
	 */
	public function hasApproved() {
		return (
			self::APPROVED === $this->regular || self::APPROVED === $this->ntgb
		);
	}

	/**
	 * Has on moderation monetizations.
	 *
	 * @return bool
	 */
	public function hasOnModeration() {
		return (
			self::MODERATION === $this->regular || self::MODERATION === $this->ntgb
		);
	}

	/**
	 * Hydrator
	 *
	 * @param  array{regular?: int, ntgb?: int} $data  Arrayed data.
	 *
	 * @return self
	 */
	public static function hydrate( $data ) {
		return new self(
			(int) ( isset( $data['regular'] ) ? $data['regular'] : self::MODERATION ),
			(int) ( isset( $data['ntgb'] ) ? $data['ntgb'] : self::MODERATION )
		);
	}

	/**
	 * Convert to array.
	 *
	 * @return array{regular: int, ntgb: int}
	 */
	public function toArray() {
		return [
			'regular' => $this->getRegularStatus(),
			'ntgb'    => $this->getNtgbStatus(),
		];
	}

	/**
	 * {@inheritDoc}
	 */
	#[\ReturnTypeWillChange]
	public function jsonSerialize() {
		return $this->toArray();
	}
}
