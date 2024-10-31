<?php

namespace NativeRent\Core\Cron;

use InvalidArgumentException;

final class Task implements TaskInterface {
	/** @var TaskInterval */
	protected $interval;

	/** @var callable */
	protected $handler;

	/** @var string|null */
	protected $name = null;

	/**
	 * @param  TaskInterval $interval
	 * @param  callable     $handler
	 * @param  string|null  $name
	 *
	 * @throws InvalidArgumentException
	 */
	public function __construct( TaskInterval $interval, $handler, $name = null ) {
		if ( ! is_callable( $handler ) ) {
			throw new InvalidArgumentException( 'Handler must be a callable' );
		}
		if ( ! is_string( $name ) && ! is_null( $name ) ) {
			throw new InvalidArgumentException( 'Task name must be a string or null' );
		}

		$this->interval = $interval;
		$this->handler  = $handler;
		$this->name     = $name;
	}

	/**
	 * {@inheritDoc}
	 */
	public function getName() {
		return $this->name;
	}

	/**
	 * {@inheritDoc}
	 */
	public function getInterval() {
		return $this->interval;
	}

	/**
	 * {@inheritDoc}
	 */
	public function getHandler() {
		return $this->handler;
	}
}
