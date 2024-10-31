<?php

namespace NativeRent\Core\Cron;

interface TaskInterface {
	/**
	 * Job name getter.
	 *
	 * @return string
	 */
	public function getName();

	/**
	 * Get interval in seconds.
	 *
	 * @return TaskInterval
	 */
	public function getInterval();


	/**
	 * Job handler getter.
	 *
	 * @return callable
	 */
	public function getHandler();
}
