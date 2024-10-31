<?php

namespace NativeRent\Common\Listeners\PluginDeactivated;

use NativeRent\Common\Events\PluginDeactivated;
use NativeRent\Core\Cron\CronManager;
use NativeRent\Core\Events\EventInterface;
use NativeRent\Core\Events\ListenerInterface;

final class UnregisterCronTasks implements ListenerInterface {

	/** @var CronManager */
	private $cron;

	public function __construct( CronManager $cron ) {
		$this->cron = $cron;
	}

	/**
	 * @param PluginDeactivated|EventInterface $event
	 */
	public function __invoke( EventInterface $event ) {
		$this->cron->cancelAllTasks();
	}
}
