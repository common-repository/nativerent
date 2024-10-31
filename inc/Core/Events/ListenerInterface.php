<?php

namespace NativeRent\Core\Events;

interface ListenerInterface {
	/**
	 * @param  EventInterface $event
	 *
	 * @return mixed|void
	 */
	public function __invoke( EventInterface $event );
}
