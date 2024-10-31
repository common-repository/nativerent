<?php

namespace NativeRent\Common\Migrations;

use Exception;
use NativeRent\Common\Migrations\V144\Migrations_Autoconfig;
use NativeRent\Core\Migration\MigrationInterface;
use QM_DB;

use function esc_sql;
use function maybe_unserialize;
use function update_option;

class V0 implements MigrationInterface {
	/**
	 * {@inheritDoc}
	 */
	public function getVersion() {
		return '0';
	}

	/**
	 * {@inheritDoc}
	 */
	public function __invoke() {
		$this->v13();
		$this->v14();
		$this->v144();
	}

	protected function v13() {
		/** @var QM_DB $wpdb */
		global $wpdb;
		$old_option_name = 'nativerent_options';
		$table_name      = $wpdb->prefix . $old_option_name;
		$found_table     = $wpdb->get_var(
			$wpdb->prepare( 'SHOW TABLES LIKE %s', $wpdb->esc_like( $table_name ) )
		);
		if ( $found_table === $table_name ) {
			$old_options = $wpdb->get_results( 'SELECT * FROM ' . esc_sql( $table_name ) );
			if ( is_array( $old_options ) ) {
				$nativerent_options = get_option( 'nativerent_options', [] );
				foreach ( $old_options as $old_option ) {
					$opt_name = maybe_unserialize( $old_option->name );
					if ( isset( $nativerent_options[ $opt_name ] ) ) {
						continue;
					}
					$nativerent_options[ $opt_name ] = maybe_unserialize( $old_option->value );
				}

				update_option( $old_option_name, $nativerent_options );
			}
		}
	}

	protected function v14() {
		$old_options = get_option( 'nativerent_options', [] );
		if ( ! empty( $old_options ) ) {
			foreach ( $old_options as $opt => $v ) {
				update_option( 'nativerent.' . $opt, $v );
			}
		}

		// delete old and unnecessary data....
		delete_option( 'nativerent_options' );
		delete_option( 'nativerent.jsURL' );
		delete_option( 'nativerent.cssURL' );

		/** @var QM_DB $wpdb */
		global $wpdb;
		$wpdb->query( 'DROP TABLE IF EXISTS ' . esc_sql( $wpdb->prefix . 'nativerent_options' ) );
		$wpdb->query( 'DROP TABLE IF EXISTS ' . esc_sql( $wpdb->prefix . 'nativerent_adv' ) );
	}

	protected function v144() {
		try {
			// revert configs patches from old versions.
			Migrations_Autoconfig::uninstall();
		} catch ( Exception $e ) {
			return;
		} catch ( \Throwable $t ) {
			return;
		}
	}
}
