<?php

namespace NativeRent\Core\Routing;

/**
 * Base interface of routes collection.
 */
interface RoutesCollection {
	/**
	 * Adding route to collection.
	 *
	 * @param  Route $route  Route instance.
	 *
	 * @return self
	 */
	public function addRoute( Route $route );

	/**
	 * Getting route by name from collection.
	 *
	 * @param  string $name  Route name.
	 *
	 * @return Route|null
	 */
	public function getRouteByName( $name );
}
