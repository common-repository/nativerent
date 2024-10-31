<?php

namespace NativeRent\Core\Events;

/**
 * WordPress event bus class.
 */
class WpEventBus implements DispatcherInterface, ListenersRegistryInterface {
	/** @var string */
	private $eventNamePrefix;

	/**
	 * @var callable( class-string<ListenerInterface> ): ?ListenerInterface | null
	 */
	private $listenerResolver;

	/**
	 * @param  string                                                                 $eventNamePrefix
	 * @param  callable( class-string<ListenerInterface> ): ?ListenerInterface | null $listenerResolver
	 */
	public function __construct( $eventNamePrefix = '', $listenerResolver = null ) {
		$this->eventNamePrefix  = $eventNamePrefix;
		$this->listenerResolver = $listenerResolver;
	}

	/**
	 * {@inheritDoc}
	 */
	public function dispatch( EventInterface $event ) {
		do_action( $this->eventName( $event::getEventName() ), $event );
	}


	/**
	 * @param  ListenerInterface | class-string<ListenerInterface> | callable(): ListenerInterface $listener
	 * @param  callable( class-string<ListenerInterface> ): ?ListenerInterface | null              $listenerResolver
	 *
	 * @return callable( EventInterface ): mixed
	 */
	public static function getActionCallback( $listener, $listenerResolver ) {
		return function ( EventInterface $event ) use ( $listener, $listenerResolver ) {
			if ( ! is_null( $listenerResolver ) && is_string( $listener ) ) {
				$l = $listenerResolver( $listener );
				if ( $l instanceof ListenerInterface ) {
					return $l( $event );
				}
			} elseif ( $listener instanceof ListenerInterface ) {
				return $listener( $event );
			} elseif ( is_callable( $listener ) ) {
				$l = $listener();
				if ( $l instanceof ListenerInterface ) {
					return $l( $event );
				}
			}

			return null;
		};
	}

	/**
	 * {@inheritDoc}
	 */
	public function addListeners( $eventName, $listeners ) {
		foreach ( $listeners as $priority => $listener ) {
			add_action(
				$this->eventName( $eventName ),
				self::getActionCallback( $listener, $this->listenerResolver ),
				$priority
			);
		}
	}

	/**
	 * Get a full event name.
	 *
	 * @param  string $eventName
	 *
	 * @return string
	 */
	private function eventName( $eventName ) {
		return $this->eventNamePrefix . $eventName;
	}
}
