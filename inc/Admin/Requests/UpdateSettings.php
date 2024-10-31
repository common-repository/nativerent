<?php

namespace NativeRent\Admin\Requests;

use NativeRent\Common\Entities\AdUnitsConfig;

use function base64_encode;
use function in_array;
use function sanitize_text_field;
use function trim;
use function wp_unslash;

/**
 * @phpcs:disable WordPress.Security.NonceVerification.Missing
 */
class UpdateSettings extends AbstractRequest {

	/**
	 * Config instance with sanitized data.
	 *
	 * @var AdUnitsConfig
	 */
	public $adUnitsConfig = null;

	public function __construct() {
		if ( $this->verifyNonce( 'nrent_settings' ) && isset( $_POST['nrent']['adUnitsConfig'] ) ) {
			// phpcs:ignore WordPress.Security
			$this->adUnitsConfig = $this->setAdUnitsConfig( $_POST['nrent']['adUnitsConfig'] );
		}
	}

	/**
	 * Filling request data with unslash and sanitizing.
	 *
	 * @param  array|null $data  Raw POST payload data.
	 *
	 * @return AdUnitsConfig
	 */
	private function setAdUnitsConfig( $data ) {
		if ( empty( $data ) ) {
			return null;
		}
		$sanitizedData = [];
		foreach ( $data as $type => $config ) {
			foreach ( $config as $unit => $props ) {
				foreach ( $props as $prop => $val ) {
					// Filling common properties.
					if ( in_array( $prop, [ 'insert', 'autoSelector', 'customSelector' ] ) ) {
						$sanitizedData[ $type ][ $unit ][ $prop ] = sanitize_text_field( wp_unslash( $val ) );
					}

					if ( 'settings' === $prop ) {
						// Filling regular popup teaser settings.
						if ( 'regular' === $type && 'popupTeaser' === $unit ) {
							$sanitizedData[ $type ][ $unit ][ $prop ] = [
								'mobileTeaser'     => ! empty( $val['mobileTeaser'] ),
								'mobileFullscreen' => ! empty( $val['mobileFullscreen'] ),
								'desktopTeaser'    => ! empty( $val['desktopTeaser'] ),
							];
						}
						if ( 'ntgb' === $type ) {
							// Filling NTGB fallback code.
							if ( isset( $val['fallbackCode'] ) ) {
								$sanitizedData[ $type ][ $unit ][ $prop ]['fallbackCode']
									= base64_encode( trim( wp_unslash( $val['fallbackCode'] ) ) );
							}
							// Unit status.
							if ( isset( $val['inactive'] ) ) {
								$sanitizedData[ $type ][ $unit ][ $prop ]['inactive'] = ! empty( $val['inactive'] );
							}

							// NoInsertion flag.
							if ( isset( $val['noInsertion'] ) ) {
								$sanitizedData[ $type ][ $unit ][ $prop ]['noInsertion'] = ! empty( $val['noInsertion'] );
							}
						}
					}
				}
			}
		}

		return new AdUnitsConfig( $sanitizedData );
	}
}
