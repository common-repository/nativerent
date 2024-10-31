<?php

namespace NativeRent\Common\Cron\Tasks;

use NativeRent\Common\NRentService;

final class UpdateMonetizations {
	/**
	 * @var NRentService
	 */
	protected $nativeRentService;

	public function __construct( NRentService $nativeRentService ) {
		$this->nativeRentService = $nativeRentService;
	}

	public function __invoke() {
		$this->nativeRentService->loadMonetizations();
	}
}
