<?php

namespace NativeRent\Common\Cron;

use NativeRent\Core\Cron\TaskInterface;
use NativeRent\Core\Cron\TaskInterval;
use NativeRent\Core\Cron\TasksRegistryInterface;

use function add_action;
use function add_filter;
use function wp_next_scheduled;
use function wp_schedule_event;
use function wp_unschedule_hook;

/**
 * WP Cron definition.
 */
class WpCronTasksRegistry implements TasksRegistryInterface {
	/** @var string */
	private $namePrefix;

	/** @var int[] */
	private $registeredIntervals = [];

	/**
	 * @param  string $namePrefix
	 */
	public function __construct( $namePrefix = '' ) {
		$this->namePrefix = empty( $namePrefix ) ? '' : $namePrefix . '_';
	}

	/**
	 * {@inheritDoc}
	 */
	public function registerTask( TaskInterface $task ) {
		$taskName = $this->getTaskName( $task );
		$this->registerInterval( $task->getInterval() );
		add_action( $taskName, $task->getHandler() );
		if ( ! wp_next_scheduled( $taskName ) ) {
			wp_schedule_event( time(), $this->getIntervalName( $task->getInterval() ), $taskName );
		}
	}

	/**
	 * {@inheritDoc}
	 */
	public function unregisterTask( TaskInterface $task ) {
		wp_unschedule_hook( $this->getTaskName( $task ) );
	}

	/**
	 * @param  string $baseName
	 *
	 * @return string
	 */
	private function getName( $baseName ) {
		return $this->namePrefix . $baseName;
	}

	private function getTaskName( TaskInterface $task ) {
		$taskName = $task->getName();

		return $this->getName( ! empty( $taskName ) ? $taskName : spl_object_hash( $task ) );
	}

	private function getIntervalName( TaskInterval $interval ) {
		return $this->getName( 'every_' . $interval->getSeconds() . '_seconds' );
	}

	private function registerInterval( TaskInterval $interval ) {
		if ( in_array( $interval->getSeconds(), $this->registeredIntervals, true ) ) {
			return;
		}

		add_filter(
			'cron_schedules',
			function ( $recurrence ) use ( $interval ) {
				$name = $this->getIntervalName( $interval );
				$recurrence[ $name ] = [
					'interval' => $interval->getSeconds(),
					'display'  => 'Every ' . $interval->getSeconds() . ' seconds',
				];

				return $recurrence;
			}
		);

		$this->registeredIntervals[] = $interval->getSeconds();
	}
}
