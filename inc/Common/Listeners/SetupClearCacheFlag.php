<?php

namespace NativeRent\Common\Listeners;

use NativeRent\Common\Events\OptionUpdated;
use NativeRent\Common\Events\PluginActivated;
use NativeRent\Common\Events\PluginVersionChanged;
use NativeRent\Common\Options;
use NativeRent\Core\Events\EventInterface;
use NativeRent\Core\Events\ListenerInterface;

/**
 * This is a general handler for updating the value of the `clearCacheFlag` option.
 */
final class SetupClearCacheFlag implements ListenerInterface {

	/** @var Options */
	private $options;

	public function __construct( Options $options ) {
		$this->options = $options;
	}

	/**
	 * @param PluginActivated|OptionUpdated|EventInterface $event
	 */
	public function __invoke( EventInterface $event ) {
		switch ( $event::getEventName() ) {
			case OptionUpdated::getEventName():
				$this->optionUpdatedHandler( $event );
				break;
			case PluginActivated::getEventName():
				$this->pluginActivatedHandler( $event );
				break;
			case PluginVersionChanged::getEventName():
				$this->pluginVersionChangedHandler( $event );
				break;
			default:
				$this->options->setClearCacheFlag( 1 );
		}
	}

	/**
	 * @param  OptionUpdated $event
	 *
	 * @return void
	 */
	private function optionUpdatedHandler( OptionUpdated $event ) {
		$options = [
			Options::OPT_ADUNITS_CONFIG,
			Options::OPT_ADV_PATTERNS,
			Options::OPT_MONETIZATIONS,
			Options::OPT_SITE_MODERATION_STATUS,
		];
		if ( in_array( $event->getOption(), $options, true ) ) {
			$this->options->setClearCacheFlag( 1 );
		}
	}

	private function pluginActivatedHandler( PluginActivated $event ) {
		$this->options->setClearCacheFlag( ! empty( $this->options->getSiteID() ) ? 1 : 0 );
	}

	private function pluginVersionChangedHandler( PluginVersionChanged $event ) {
		$this->options->setClearCacheFlag( ! empty( $this->options->getSiteID() ) ? 1 : 0 );
	}
}
