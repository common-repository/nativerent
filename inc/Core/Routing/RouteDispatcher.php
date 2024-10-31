<?php

namespace NativeRent\Core\Routing;

use function call_user_func;
use function htmlspecialchars;
use function is_array;
use function is_null;

/**
 * Route dispatcher.
 * This class finds a suitable route and performs the desired action.
 */
class RouteDispatcher {
	/**
	 * @var RouterInterface
	 */
	protected $router;

	/**
	 * Constructor.
	 *
	 * @param  RouterInterface $router  Router instance.
	 */
	public function __construct( RouterInterface $router ) {
		$this->router = $router;
	}

	/**
	 * TODO: need headers tests.
	 *
	 * @return void
	 */
	public function dispatch() {
		if ( ! isset( $_SERVER['REQUEST_METHOD'] ) || ! isset( $_SERVER['REQUEST_URI'] ) ) {
			return;
		}

		$method  = htmlspecialchars( stripslashes( $_SERVER['REQUEST_METHOD'] ) ); // phpcs:ignore
		$uri     = filter_var( stripslashes( $_SERVER['REQUEST_URI'] ), FILTER_SANITIZE_URL ); // phpcs:ignore
		$headers = $this->getHeadersByGlobals();
		$route   = $this->router->match( $method, $uri, $headers );
		if ( ! is_null( $route ) ) {
			$this->performAction( $route );
		}
	}

	/**
	 * Get request headers.
	 *
	 * @return array<string, string>
	 */
	protected function getHeadersByGlobals() {
		$headers = [];
		foreach ( $_SERVER as $key => $value ) {
			$prefixPos = strpos( $key, 'HTTP_' );
			if ( 0 !== $prefixPos ) {
				continue;
			}

			$headers[ strtolower( str_replace( '_', '-', substr( $key, 5 ) ) ) ] = htmlspecialchars(
				stripslashes( $value )
			);
		}

		return $headers;
	}

	/**
	 * Performing route action.
	 *
	 * @param  Route $route
	 *
	 * @return void
	 */
	protected function performAction( Route $route ) {
		if ( ! is_array( $route->action ) ) {
			return;
		}
		call_user_func( [ new $route->action[0](), $route->action[1] ] );
	}
}
