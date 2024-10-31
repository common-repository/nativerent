<?php

namespace NativeRent\Common\Migrations;

use NativeRent\Common\Entities\AdUnitsConfig;
use NativeRent\Core\Migration\MigrationInterface;

use function is_array;
use function json_decode;
use function json_encode;
use function update_option;

class V185 implements MigrationInterface {
	/**
	 * {@inheritDoc}
	 */
	public function getVersion() {
		return '1.8.5';
	}

	/**
	 * {@inheritDoc}
	 */
	public function __invoke() {
		$oldConfig = json_decode( get_option( 'nativerent.adUnitsConfig', '{}' ), true );
		$newConfig = [];

		if ( is_array( $oldConfig ) ) {
			if ( isset( $oldConfig['ntgb'] ) ) {
				if ( is_array( $oldConfig['ntgb'] ) && isset( $oldConfig['ntgb']['insert'] ) ) {
					$newConfig['ntgb'] = [ '1' => $oldConfig['ntgb'] ];
				}
				unset( $oldConfig['ntgb'] );
			}

			$newConfig['regular'] = $oldConfig;
		}

		// TODO: класс AdUnitConfig нужно вынести в директорию миграции V185.
		update_option( 'nativerent.adUnitsConfig', json_encode( new AdUnitsConfig( $newConfig ) ) );
	}
}
