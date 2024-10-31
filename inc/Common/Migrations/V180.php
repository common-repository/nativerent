<?php

namespace NativeRent\Common\Migrations;

use NativeRent\Core\Migration\MigrationInterface;

class V180 implements MigrationInterface {
	/**
	 * {@inheritDoc}
	 */
	public function getVersion() {
		return '1.8.0';
	}

	/**
	 * {@inheritDoc}
	 */
	public function __invoke() {
		delete_option( 'nativerent.selfCheckReport' );
	}
}
