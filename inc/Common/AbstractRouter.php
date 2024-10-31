<?php

namespace NativeRent\Common;

use NativeRent\Core\Routing\Route;
use NativeRent\Core\Routing\RouterInterface;
use NativeRent\Core\Routing\RoutesCollection;

abstract class AbstractRouter implements RouterInterface, RoutesCollection {
	/**
	 * @var string
	 */
	protected $namespace;

	/**
	 * Route key => Route
	 *
	 * @var array<string, Route>
	 */
	protected $routes = [];

	/**
	 * Name => route key.
	 *
	 * @var array<string, string>
	 */
	protected $namesMap = [];

	/**
	 * Route registration.
	 *
	 * @param  string                      $method  Request method: post, get.
	 * @param  string                      $slug    Action slug.
	 * @param  array<class-string, string> $action  Action handler.
	 * @param  string|null                 $name    Route name.
	 *
	 * @return self
	 */
	public function registerRoute( $method, $slug, $action, $name = null ) {
		return $this->addRoute(
			new Route(
				$method,
				$this->getPathByActionSlug( $slug ),
				$action,
				$name
			)
		);
	}

	/**
	 * {@inheritDoc}
	 */
	public function addRoute( Route $route ) {
		$routeKey                  = $this->getRoutekey( $route->method, $route->path );
		$this->routes[ $routeKey ] = $route;
		if ( ! is_null( $route->name ) ) {
			$this->namesMap[ $route->name ] = $routeKey;
		}

		return $this;
	}

	/**
	 * {@inheritDoc}
	 */
	public function getRouteByName( $name ) {
		$routeKey = $this->namesMap[ $name ];
		if ( empty( $routeKey ) ) {
			return null;
		}

		return isset( $this->routes[ $routeKey ] ) ? $this->routes[ $routeKey ] : null;
	}

	/**
	 * Get auto-generated name of route.
	 *
	 * @param  string $method  Get/Post.
	 * @param  string $path    Route path.
	 *
	 * @return string
	 */
	protected function getRouteKey( $method, $path ) {
		return strtolower( $method . ' ' . $path );
	}

	/**
	 * Get a full route path by action slug.
	 *
	 * @param  string $slug  Action slug.
	 *
	 * @return string
	 */
	abstract protected function getPathByActionSlug( $slug );
}
