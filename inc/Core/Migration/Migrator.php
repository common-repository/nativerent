<?php

namespace NativeRent\Core\Migration;

use Closure;

use function usort;
use function version_compare;

/**
 * Migrations runner class.
 */
class Migrator {
	/**
	 * @var MigrationInterface[]
	 */
	private $migrations = [];

	/**
	 * @var Closure<MigrationInterface[]>
	 */
	private $migrationsFactory = null;

	/**
	 * @param  Closure<MigrationInterface[]>|MigrationInterface[] $migrations  List of migrations.
	 */
	public function __construct( $migrations = [] ) {
		$this->migrations = is_array( $migrations ) ? $migrations : $this->migrations;
		$this->migrationsFactory = is_callable( $migrations ) ? $migrations : $this->migrationsFactory;
	}

	/**
	 * Sort migrations by version.
	 *
	 * @return void
	 */
	private function sortMigrations() {
		usort(
			$this->migrations,
			function ( MigrationInterface $a, MigrationInterface $b ) {
				if ( version_compare( $a->getVersion(), $b->getVersion(), '>' ) ) {
					return 1;
				}

				return - 1;
			}
		);
	}

	/**
	 * Migrations loader method.
	 *
	 * @return void
	 */
	private function initMigrations() {
		if ( empty( $this->migrations ) && is_callable( $this->migrationsFactory ) ) {
			$this->migrations = call_user_func( $this->migrationsFactory );
		}
	}

	/**
	 * @param  string $fromVersion  Previous version of plugin.
	 * @param  string $toVersion    Target version to migrate.
	 *
	 * @return MigrationInterface[]|false
	 */
	public function run( $fromVersion, $toVersion ) {
		if ( version_compare( $fromVersion, $toVersion, '>=' ) ) {
			return false;
		}

		$this->initMigrations();
		$this->sortMigrations();
		$executed = [];
		foreach ( $this->migrations as $migration ) {
			if (
				$migration instanceof MigrationInterface
				&& version_compare( $fromVersion, $migration->getVersion(), '<' )
			) {
				$migration();
				$executed[] = $migration;
			}
		}

		return $executed;
	}
}
