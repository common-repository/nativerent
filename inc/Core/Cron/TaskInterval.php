<?php

namespace NativeRent\Core\Cron;

use InvalidArgumentException;

class TaskInterval {
	/** @var int */
	protected $seconds;

	/**
	 * @param  int $seconds
	 *
	 * @throws InvalidArgumentException
	 */
	public function __construct( $seconds ) {
		if ( ! is_int( $seconds ) ) {
			throw new InvalidArgumentException( 'Interval value must be integer' );
		}

		$this->seconds = $seconds;
	}

	/**
	 * @return int
	 */
	public function getSeconds() {
		return $this->seconds;
	}

	public static function everyMinute() {
		return new self( 60 );
	}

	public static function everyFiveMinutes() {
		return new self( 60 * 5 );
	}

	public static function halfHourly() {
		return new self( 60 * 30 );
	}

	public static function hourly() {
		return new self( 60 * 60 );
	}

	public static function twiceDaily() {
		return new self( 60 * 60 * 12 );
	}

	public static function daily() {
		return new self( 60 * 60 * 24 );
	}
}
