<?php

namespace NativeRent\Admin\Listeners;

use NativeRent\Admin\Events\SettingsUpdated;
use NativeRent\Common\Integration\API\RequestException;
use NativeRent\Common\NRentService;
use NativeRent\Core\Events\EventInterface;
use NativeRent\Core\Events\ListenerInterface;

final class SendState implements ListenerInterface {
	/** @var NRentService */
	private $nativeRentService;

	public function __construct( NRentService $nativeRentService ) {
		$this->nativeRentService = $nativeRentService;
	}

	/**
	 * @param  SettingsUpdated|EventInterface $event
	 *
	 * @return void
	 * @throws RequestException
	 */
	public function __invoke( EventInterface $event ) {
		$this->nativeRentService->sendCurrentState();
	}
}
