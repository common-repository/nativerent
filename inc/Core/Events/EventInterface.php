<?php

namespace NativeRent\Core\Events;

interface EventInterface {
	/**
	 * Get the event name.
	 *
	 * @return string Event name.
	 */
	public static function getEventName();
}
