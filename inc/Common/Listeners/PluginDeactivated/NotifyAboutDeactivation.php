<?php

namespace NativeRent\Common\Listeners\PluginDeactivated;

use NativeRent\Common\Events\PluginDeactivated;
use NativeRent\Common\NRentService;
use NativeRent\Core\Events\EventInterface;
use NativeRent\Core\Events\ListenerInterface;

final class NotifyAboutDeactivation implements ListenerInterface {

	/** @var NRentService */
	private $service;

	public function __construct( NRentService $service ) {
		$this->service = $service;
	}

	/**
	 * @param PluginDeactivated|EventInterface $event
	 */
	public function __invoke( EventInterface $event ) {
		$this->service->sendDeactivatedStatus();
	}
}
