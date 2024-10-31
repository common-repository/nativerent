<?php

namespace NativeRent\Api\Handlers;

use NativeRent\Common\Entities\AdUnitProps;
use NativeRent\Common\Options;

final class UpdateAdUnitsConfig {
	/** @var Options */
	protected $options;

	public function __construct( Options $options ) {
		$this->options = $options;
	}

	/**
	 * @param  array{regular?: array, ntgb?: array} $payload
	 *
	 * @return bool
	 */
	public function __invoke( $payload ) {
		if ( empty( $payload ) || ! is_array( $payload ) ) {
			return false;
		}

		$config = $this->options->getAdUnitsConfig();

		// Patching REGULAR units...
		if ( ! empty( $payload['regular'] ) ) {
			foreach ( $payload['regular'] as $type => $props ) {
				if ( empty( $props ) || 'popupTeaser' === $type || ! property_exists( $config->regular, $type ) ) {
					continue;
				}
				/** @var AdUnitProps $unitProps */
				$unitProps = $config->regular->$type;
				self::patchUnitProps( $unitProps, $props );
			}
		}

		// Patching NTGB units...
		if ( ! empty( $payload['ntgb'] ) ) {
			foreach ( $payload['ntgb'] as $unitName => $props ) {
				if ( empty( $props ) || ! isset( $config->ntgb[ $unitName ] ) ) {
					continue;
				}
				/** @var AdUnitProps $unitProps */
				$unitProps = $config->ntgb[ $unitName ];
				if ( is_null( $unitProps ) ) {
					continue;
				}
				self::patchUnitProps( $unitProps, $props );

				// Patching settings.
				if ( isset( $props['settings'] ) && array_key_exists( 'noInsertion', $props['settings'] ) ) {
					$noInsertion                        = $props['settings']['noInsertion'];
					$unitProps->settings['noInsertion'] = is_numeric( $noInsertion )
						? ( $noInsertion > 0 )
						: ! empty( $noInsertion );
				}
			}
		}
		$this->options->setAdUnitsConfig( $config );

		return true;
	}

	/**
	 * Common function for patching AdUnitProps.
	 *
	 * @param  AdUnitProps                $props
	 * @param  array<string, string|null> $values
	 *
	 * @return void
	 */
	protected static function patchUnitProps( AdUnitProps $props, array $values ) {
		foreach ( [ 'insert', 'autoSelector', 'customSelector' ] as $p ) {
			if ( array_key_exists( $p, $values ) ) {
				$props->$p = (string) $values[ $p ];
			}
		}
	}
}
