<?php

namespace NativeRent\Admin\Listeners;

use NativeRent\Admin\Events\SettingsUpdated;
use NativeRent\Common\Options;
use NativeRent\Core\Events\EventInterface;
use NativeRent\Core\Events\ListenerInterface;

final class SetupClearCacheFlag implements ListenerInterface {
	/** @var Options */
	private $options;

	public function __construct( Options $options ) {
		$this->options = $options;
	}

	/**
	 * @param  SettingsUpdated|EventInterface $event
	 *
	 * @return void
	 */
	public function __invoke( EventInterface $event ) {
		$this->options->setClearCacheFlag( 2 );
	}
}
