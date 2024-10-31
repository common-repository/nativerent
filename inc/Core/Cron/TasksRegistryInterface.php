<?php

namespace NativeRent\Core\Cron;

interface TasksRegistryInterface {
	/**
	 * Register task.
	 *
	 * @param  TaskInterface $task
	 *
	 * @return void
	 */
	public function registerTask( TaskInterface $task );

	/**
	 * Unregister task.
	 *
	 * @param  TaskInterface $task
	 *
	 * @return void
	 */
	public function unregisterTask( TaskInterface $task );
}
