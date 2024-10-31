<?php

namespace NativeRent\Admin\Events;

use NativeRent\Core\Events\EventInterface;

final class SettingsUpdated implements EventInterface {
	/**
	 * {@inheritDoc}
	 */
	public static function getEventName() {
		return 'admin-settings-updated';
	}
}
