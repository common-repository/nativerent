<?php

namespace NativeRent\Core\Routing;

interface RouterInterface {
	/**
	 * Search for a suitable route.
	 *
	 * @param  string                         $method   Request method.
	 * @param  string                         $uri      Request URI.
	 * @param  array<string, string[]|string> $headers  Request headers.
	 *
	 * @return Route|null
	 */
	public function match( $method, $uri, $headers = [] );
}
