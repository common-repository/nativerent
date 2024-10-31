<?php

namespace NativeRent\Api;

use NativeRent\Common\AbstractBootloader;
use NativeRent\Core\Container\Exceptions\DependencyNotFound;
use NativeRent\Core\Routing\RouteDispatcher;
use NativeRent\Core\Routing\RouterInterface;
use NativeRent\Core\Routing\RoutesCollection;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;

/**
 * Bootloader of API context.
 */
final class Bootloader extends AbstractBootloader {

	/**
	 * API routes definition.
	 *
	 * @var array[]
	 */
	private static $routes
		= [
			[ 'POST', 'state', [ Controller::class, 'state' ], 'api.state' ],
			[ 'POST', 'check', [ Controller::class, 'check' ], 'api.check' ],
			[ 'POST', 'articles', [ Controller::class, 'articles' ], 'api.articles' ],
			[ 'POST', 'updateAdvPatterns', [ Controller::class, 'updateAdvPatterns' ], 'api.updateAdvPatterns' ],
			[ 'POST', 'updateMonetizations', [ Controller::class, 'updateMonetizations' ], 'api.updateMonetizations' ],
			[ 'POST', 'updateAdUnitsConfig', [ Controller::class, 'updateAdUnitsConfig' ], 'api.updateAdUnitsConfig' ],
		];

	/**
	 * @inheritdoc
	 * @throws DependencyNotFound
	 * @throws ContainerExceptionInterface
	 * @throws NotFoundExceptionInterface
	 */
	public function onRegister() {
		$this->initRouting();

		// Dispatching route.
		add_action(
			'plugins_loaded',
			function () {
				$this->container->get( RouteDispatcher::class )->dispatch();
			},
			NATIVERENT_PLUGIN_MIN_PRIORITY
		);
	}

	/**
	 * Router and routes init.
	 *
	 * @return void
	 */
	private function initRouting() {
		$this->container
			->bind(
				ApiRouter::class,
				function () {
					$router = new ApiRouter( NATIVERENT_API_V1_PARAM );
					foreach ( self::$routes as $r ) {
						$router->registerRoute( $r[0], $r[1], $r[2], isset( $r[3] ) ? $r[3] : null );
					}

					return $router;
				}
			)
			->bind(
				RouterInterface::class,
				function ( ContainerInterface $c ) {
					return $c->get( ApiRouter::class );
				}
			)
			->bind(
				RoutesCollection::class,
				function ( ContainerInterface $c ) {
					return $c->get( ApiRouter::class );
				}
			)
			// Define route dispatcher.
			->bind(
				RouteDispatcher::class,
				function ( ContainerInterface $c ) {
					return new RouteDispatcher( $c->get( RouterInterface::class ) );
				}
			);
	}
}
