<?php

namespace NativeRent\Common\Listeners;

use NativeRent\Common\Events\PluginActivated;
use NativeRent\Common\Events\PluginVersionChanged;
use NativeRent\Common\Integration\API\RequestException;
use NativeRent\Common\NRentService;
use NativeRent\Core\Events\EventInterface;
use NativeRent\Core\Events\ListenerInterface;

/**
 * General listener for sending the actual state to Native Rent.
 */
final class SendState implements ListenerInterface {
	/** @var NRentService */
	private $nativeRentService;

	public function __construct( NRentService $nativeRentService ) {
		$this->nativeRentService = $nativeRentService;
	}

	/**
	 * @param  PluginActivated|PluginVersionChanged|EventInterface $event
	 *
	 * @return void
	 * @throws RequestException
	 */
	public function __invoke( EventInterface $event ) {
		$this->nativeRentService->sendCurrentState();
	}
}
