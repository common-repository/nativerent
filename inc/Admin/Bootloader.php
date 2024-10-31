<?php

namespace NativeRent\Admin;

use NativeRent\Admin\Events\SettingsUpdated;
use NativeRent\Admin\Listeners\SendState;
use NativeRent\Admin\Listeners\SetupClearCacheFlag;
use NativeRent\Admin\Notices\ClearCacheNotice;
use NativeRent\Admin\Notices\InvalidApiToken;
use NativeRent\Admin\Notices\Unauthorized;
use NativeRent\Common\AbstractBootloader;
use NativeRent\Common\NRentService;
use NativeRent\Common\Options;
use NativeRent\Core\Container\Exceptions\DependencyNotFound;
use NativeRent\Core\Events\ListenersRegistryInterface;
use NativeRent\Core\Notices\NoticesRegistry;
use NativeRent\Core\Routing\RouteDispatcher;
use NativeRent\Core\Routing\RouterInterface;
use NativeRent\Core\Routing\RoutesCollection;
use NativeRent\Core\View\Renderer;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;

use function add_action;
use function add_menu_page;
use function admin_url;
use function do_action;
use function nrentroute;
use function plugins_url;
use function stripos;

use const NATIVERENT_PLUGIN_DIR;
use const NATIVERENT_PLUGIN_FILE;

/**
 * Bootloader of admin context.
 */
final class Bootloader extends AbstractBootloader {
	/**
	 * Routes definition.
	 * [ <method>, <path>, <action>, <route name> ]
	 *
	 * @var array<array-key, array{string, string, array<class-string, string>, string|null}
	 */
	private static $routes
		= [
			[ 'GET', '', [ Controller::class, 'showSettings' ], 'settings.show' ],
			[ 'POST', '', [ Controller::class, 'updateSettings' ], 'settings.update' ],
			[ 'GET', 'auth', [ Controller::class, 'showAuthForm' ], 'auth.form' ],
			[ 'POST', 'auth', [ Controller::class, 'auth' ], 'auth.auth' ],
			[ 'POST', 'logout', [ Controller::class, 'logout' ], 'logout' ],
			[ 'POST', 'reset-cache-flag', [ Controller::class, 'resetCacheFlag' ], 'reset-cache-flag' ],
		];

	/**
	 * Path to views.
	 *
	 * @var string
	 */
	private static $templatesPath = NATIVERENT_PLUGIN_DIR . '/resources/templates/';

	/**
	 * @inheritdoc
	 * @throws DependencyNotFound
	 * @throws ContainerExceptionInterface
	 * @throws NotFoundExceptionInterface
	 */
	public function onRegister() {
		// Register admin page on hook.
		add_action( 'admin_menu', [ $this, 'addMenuItems' ] );

		// Register views renderer.
		$this->container->bind(
			Renderer::class,
			function () {
				return new Renderer( NATIVERENT_TEMPLATES_DIR );
			}
		);

		$this->initRouting();
		$this->initEvents();
		$this->initNotices();

		// Dispatching route.
		add_action( 'wp_loaded', [ $this->container->get( RouteDispatcher::class ), 'dispatch' ] );
	}

	/**
	 * Admin routing
	 *
	 * @return void
	 */
	private function initRouting() {
		$this->container
			// Define admin router.
			->bind(
				AdminRouter::class,
				function () {
					$router = new AdminRouter( admin_url( 'admin.php' ), 'nativerent' );
					foreach ( self::$routes as $route ) {
						$router->registerRoute(
							$route[0],
							$route[1],
							$route[2],
							isset( $route[3] ) ? $route[3] : null
						);
					}

					return $router;
				}
			)
			->bind(
				RouterInterface::class,
				function ( ContainerInterface $c ) {
					return $c->get( AdminRouter::class );
				}
			)
			->bind(
				RoutesCollection::class,
				function ( ContainerInterface $c ) {
					return $c->get( AdminRouter::class );
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

	/**
	 * Initializing events and listeners.
	 *
	 * @return void
	 * @throws ContainerExceptionInterface
	 * @throws DependencyNotFound
	 * @throws NotFoundExceptionInterface
	 */
	private function initEvents() {
		// Init listeners.
		$this->container
			->bind(
				SetupClearCacheFlag::class,
				function ( ContainerInterface $c ) {
					return new SetupClearCacheFlag( $c->get( Options::class ) );
				}
			)->bind(
				SendState::class,
				function ( ContainerInterface $c ) {
					return new SendState( $c->get( NRentService::class ) );
				}
			);

		// Subscribing listeners to events.
		$this->container
			->get( ListenersRegistryInterface::class )
			->addListeners(
				SettingsUpdated::getEventName(),
				[ SetupClearCacheFlag::class, SendState::class ]
			);
	}

	private function initNotices() {
		$this->container
			->bind(
				NoticesRegistry::class,
				function () {
					return new NoticesRegistry();
				}
			);

		// Adding global assets.
		add_action(
			'admin_enqueue_scripts',
			function () {
				wp_enqueue_script(
					'nativerent-global-script',
					plugins_url( 'static/admin/global.js', NATIVERENT_PLUGIN_FILE ),
					[],
					filemtime( NATIVERENT_PLUGIN_DIR . '/static/admin/global.js' )
				);
			}
		);

		add_action(
			'admin_init',
			function () {
				$options = $this->container->get( Options::class );
				$registry = $this->container->get( NoticesRegistry::class );

				// Checking for cache flag and display a notification to clear the cache if necessary.
				$clearCacheFlag = $options->getClearCacheFlag();
				if ( $clearCacheFlag > 0 ) {
					$registry->addNotice( new ClearCacheNotice( $clearCacheFlag ) );
				}

				if (
					! isset( $_SERVER['REQUEST_URI'] ) ||
					false === stripos( esc_url_raw( wp_unslash( $_SERVER['REQUEST_URI'] ) ), nrentroute( 'auth.form' )->path )
				) {
					// Authorization notice.
					if ( empty( $options->getSiteID() ) ) {
						$registry->addNotice( new Unauthorized() );
					}
					// Checking for invalid token flag and display notice if necessary.
					elseif ( $options->getInvalidTokenFlag() ) {
						$registry->addNotice( new InvalidApiToken() );
					}
				}

				if ( ! $registry->isEmpty() ) {
					// Render all added notices.
					$renderer = new NoticesRenderer( $this->container->get( Renderer::class ) );
					foreach ( $registry->extractNotices() as $item ) {
						$renderer( $item[0], $item[1] );
					}
				}
			},
			999,
			0
		);
	}

	/**
	 * @return string
	 */
	public function addMenuItems() {
		return add_menu_page(
			'Native Rent',
			'Native Rent',
			'manage_options',
			'nativerent',
			function () {
				do_action( Controller::ACTION_RENDER_VIEW );
			},
			plugins_url( 'static/admin/icon.png', NATIVERENT_PLUGIN_FILE )
		);
	}
}
