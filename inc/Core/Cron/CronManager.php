<?php

namespace NativeRent\Core\Cron;

final class CronManager {

	/** @var TasksRegistryInterface */
	private $registry;

	/** @var TaskInterface[] */
	private $scheduledTasks = [];

	/**
	 * @param  TasksRegistryInterface $registry
	 */
	public function __construct( TasksRegistryInterface $registry ) {
		$this->registry = $registry;
	}

	/**
	 * Schedule task.
	 *
	 * @param  TaskInterface $task
	 *
	 * @return void
	 */
	public function scheduleTask( TaskInterface $task ) {
		$this->registry->registerTask( $task );
		$this->scheduledTasks[] = $task;
	}

	/**
	 * Cancel all scheduled tasks.
	 *
	 * @return void
	 */
	public function cancelAllTasks() {
		foreach ( $this->scheduledTasks as $task ) {
			$this->registry->unregisterTask( $task );
		}
	}
}
