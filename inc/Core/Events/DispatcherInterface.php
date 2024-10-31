<?php

namespace NativeRent\Core\Events;

interface DispatcherInterface {

	/**
	 * Dispatch the event.
	 *
	 * @param  EventInterface $event
	 *
	 * @return void
	 */
	public function dispatch( EventInterface $event );
}
