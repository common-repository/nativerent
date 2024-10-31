<?php

namespace NativeRent\Common\Events;

use NativeRent\Core\Events\EventInterface;

final class PluginActivated implements EventInterface {

	/**
	 * {@inheritDoc}
	 */
	public static function getEventName() {
		return 'plugin-activated';
	}
}
