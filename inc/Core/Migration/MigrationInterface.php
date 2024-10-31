<?php

namespace NativeRent\Core\Migration;

interface MigrationInterface {
	/**
	 * Migration version.
	 *
	 * @return string
	 */
	public function getVersion();

	/**
	 * Run migration.
	 *
	 * @return void
	 */
	public function __invoke();
}
