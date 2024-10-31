<?php

use NativeRent\Common\Events\PluginUninstalled;
use NativeRent\Core\Events\DispatcherInterface;
use NativeRent\Plugin;

defined( 'WP_UNINSTALL_PLUGIN' ) || exit;
if ( ! defined( 'NATIVERENT_UNINSTALL' ) ) {
	define( 'NATIVERENT_UNINSTALL', 1 );
}

require_once __DIR__ . '/nativerent.php';

$container = Plugin::instance()->getContainer();
$container->get( DispatcherInterface::class )->dispatch( new PluginUninstalled() );

if ( ! empty( getenv( 'NATIVERENT_DEBUG' ) ) ) {
	die();
}
