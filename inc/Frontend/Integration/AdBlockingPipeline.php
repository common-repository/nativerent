<?php

namespace NativeRent\Frontend\Integration;

use Closure;
use NativeRent\Common\Entities\Monetizations;
use NativeRent\Frontend\AdBlocker\AdBlocker;

/**
 * Pipeline to blocking the 3rd-party advertising.
 */
final class AdBlockingPipeline implements PipelineInterface {

	/** @var Monetizations */
	private $monetizations;

	/** @var AdBlocker|Closure<AdBlocker> */
	private $adBlocker;

	/**
	 * @param  Monetizations                $monetizations
	 * @param  AdBlocker|Closure<AdBlocker> $adBlocker  AdBlocker instance or factory.
	 */
	public function __construct( Monetizations $monetizations, $adBlocker ) {
		$this->monetizations = $monetizations;
		$this->adBlocker     = $adBlocker;
	}

	/**
	 * {@inheritDoc}
	 */
	public function __invoke( $content ) {
		if ( $this->monetizations->isRegularRejected() ) {
			return $content;
		}

		if ( is_callable( $this->adBlocker ) ) {
			$this->adBlocker = call_user_func( $this->adBlocker );
		}

		return $this->adBlocker->block( $content );
	}
}
