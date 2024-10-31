<?php

namespace NativeRent\Common\Entities;

use JsonSerializable;

/**
 * @property-read string               $siteID
 * @property-read string               $version
 * @property-read AdUnitsConfig        $adUnitsConfig
 * @property-read Monetizations        $monetizations
 * @property-read SiteModerationStatus $siteModerationStatus
 */
class StateOptions implements JsonSerializable {
	/**
	 * @var string
	 */
	public $siteID;

	/**
	 * @var string
	 */
	public $version;

	/**
	 * @var AdUnitsConfig
	 */
	public $adUnitsConfig;

	/**
	 * @var Monetizations
	 */
	public $monetizations;

	/**
	 * @var SiteModerationStatus
	 */
	public $siteModerationStatus;

	/**
	 * @param  array{siteID?: string, version?: string, adUnitsConfig?: AdUnitsConfig|array, monetizations?: Monetizations, siteModerationStatus?: SiteModerationStatus|int|numeric-string} $props
	 */
	public function __construct( $props = [] ) {
		$this->fill( $props );
	}

	/**
	 * Filling the struct.
	 *
	 * @param  array{siteID?: string, version?: string, adUnitsConfig?: AdUnitsConfig|array, monetizations?: Monetizations, siteModerationStatus?: SiteModerationStatus|int|numeric-string} $props
	 *
	 * @return void
	 */
	protected function fill( $props ) {
		$this->siteID  = isset( $props['siteID'] ) && is_string( $props['siteID'] ) ? $props['siteID'] : '';
		$this->version = isset( $props['version'] ) && is_string( $props['version'] ) ? $props['version'] : '';

		// Filling adUnits config.
		if ( isset( $props['adUnitsConfig'] ) ) {
			$this->adUnitsConfig = is_array( $props['adUnitsConfig'] )
				? new AdUnitsConfig( $props['adUnitsConfig'] )
				: ( $props['adUnitsConfig'] instanceof AdUnitsConfig
					? $props['adUnitsConfig']
					: new AdUnitsConfig( [] ) );
		} else {
			$this->adUnitsConfig = new AdUnitsConfig( [] );
		}

		// Filling monetizations.
		if ( isset( $props['monetizations'] ) ) {
			$this->monetizations = $props['monetizations'] instanceof Monetizations
				? $props['monetizations']
				: Monetizations::hydrate( is_array( $props['monetizations'] ) ? $props['monetizations'] : [] );
		} else {
			$this->monetizations = Monetizations::hydrate( [] );
		}

		// Filling site moderation status.
		if ( isset( $props['siteModerationStatus'] ) ) {
			$this->siteModerationStatus = $props['siteModerationStatus'] instanceof SiteModerationStatus
				? $props['siteModerationStatus']
				: new SiteModerationStatus(
					is_numeric( $props['siteModerationStatus'] ) ? $props['siteModerationStatus'] : null
				);
		} else {
			$this->siteModerationStatus = new SiteModerationStatus();
		}
	}

	/**
	 * {@inheritDoc}
	 */
	#[\ReturnTypeWillChange]
	public function jsonSerialize() {
		return (array) $this;
	}
}
