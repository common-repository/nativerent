<?php

namespace NativeRent\Common\Listeners\PluginActivated;

use NativeRent\Common\Events\PluginActivated;
use NativeRent\Common\Integration\API\RequestException;
use NativeRent\Common\NRentService;
use NativeRent\Core\Events\EventInterface;
use NativeRent\Core\Events\ListenerInterface;

final class NotifyAboutActivation implements ListenerInterface {

	/** @var NRentService */
	private $service;

	public function __construct( NRentService $service ) {
		$this->service = $service;
	}

	/**
	 * @param PluginActivated|EventInterface $event
	 *
	 * @throws RequestException
	 */
	public function __invoke( EventInterface $event ) {
		$this->service->sendActivatedStatus();
	}
}
