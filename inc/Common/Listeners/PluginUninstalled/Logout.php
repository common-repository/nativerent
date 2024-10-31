<?php

namespace NativeRent\Common\Listeners\PluginUninstalled;

use NativeRent\Common\Events\PluginUninstalled;
use NativeRent\Common\NRentService;
use NativeRent\Core\Events\EventInterface;
use NativeRent\Core\Events\ListenerInterface;

final class Logout implements ListenerInterface {

	/** @var NRentService */
	private $service;

	public function __construct( NRentService $service ) {
		$this->service = $service;
	}

	/**
	 * @param PluginUninstalled|EventInterface $event
	 */
	public function __invoke( EventInterface $event ) {
		if ( ! defined( 'NATIVERENT_UNINSTALL' ) ) {
			return;
		}
		$this->service->logout( true );
	}
}
