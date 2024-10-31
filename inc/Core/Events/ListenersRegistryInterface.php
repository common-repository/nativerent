<?php

namespace NativeRent\Core\Events;

interface ListenersRegistryInterface {
	/**
	 * Adding event listeners.
	 *
	 * @param  string                                                                                        $eventName
	 * @param  array<int, ListenerInterface|class-string<ListenerInterface>|(callable(): ListenerInterface)> $listeners
	 *
	 * @return void
	 */
	public function addListeners( $eventName, $listeners );
}
