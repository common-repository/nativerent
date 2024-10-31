<?php

namespace NativeRent\Common\Migrations;

use NativeRent\Common\Migrations\V170\Connect_Handler;
use NativeRent\Common\Migrations\V170\Settings;
use NativeRent\Core\Migration\MigrationInterface;

class V170 implements MigrationInterface {
	/**
	 * {@inheritDoc}
	 */
	public function getVersion() {
		return '1.7.0';
	}

	/**
	 * {@inheritDoc}
	 */
	public function __invoke() {
		Settings::instance()->uninstall();
		Connect_Handler::remove_connect_file_from_config();
	}
}
