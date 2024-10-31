<?php

namespace NativeRent\Api;

use NativeRent\Common\AbstractRouter;

use function is_array;
use function parse_str;
use function parse_url;
use function trim;

/**
 * Admin router
 */
final class ApiRouter extends AbstractRouter {
	/**
	 * @param  string $namespace  Main namespace.
	 */
	public function __construct( $namespace ) {
		$this->namespace = $namespace;
	}

	/**
	 * {@inheritDoc}
	 */
	public function match( $method, $uri, $headers = [] ) {
		$actionSlug = $this->getActionSlugByHeader( $headers );
		if ( empty( $actionSlug ) ) {
			$actionSlug = $this->getActionSlugByURI( $uri );
		}
		if ( empty( $actionSlug ) ) {
			return null;
		}

		$routeKey = $this->getRouteKey( $method, $this->getPathByActionSlug( $actionSlug ) );

		return ( isset( $this->routes[ $routeKey ] ) ? $this->routes[ $routeKey ] : null );
	}

	/**
	 *
	 * Getting the action slug by header.
	 *
	 * @param  array<string, string> $headers
	 *
	 * @return string|null
	 */
	private function getActionSlugByHeader( $headers ) {
		$normalizedHeaders = [];
		foreach ( $headers as $h => $v ) {
			$normalizedHeaders[ strtolower( $h ) ] = $v;
		}

		$headerName = strtolower( 'X-' . $this->namespace );

		return ( ! empty( $normalizedHeaders[ $headerName ] ) && is_string( $normalizedHeaders[ $headerName ] ) )
			? trim( $normalizedHeaders[ $headerName ] )
			: null;
	}

	/**
	 * Getting the action slug by URI.
	 *
	 * @param  string $uri
	 *
	 * @return string|null
	 */
	private function getActionSlugByURI( $uri ) {
		// Parsing URI and checking path and query.
		$parsed = parse_url( $uri );
		if (
			! is_array( $parsed )
			|| empty( $parsed['path'] )
			|| empty( $parsed['query'] )
		) {
			return null;
		}

		// Parsing query string and checking `?page` value.
		parse_str( $parsed['query'], $parsedQuery );
		if ( ! is_array( $parsedQuery ) || empty( $parsedQuery[ $this->namespace ] ) ) {
			return null;
		}

		// Getting `action` param and getting route by this param value.
		return ! empty( $parsedQuery[ $this->namespace ] ) ? trim( $parsedQuery[ $this->namespace ] ) : null;
	}

	/**
	 * Get a full route path by action slug.
	 *
	 * @param  string $slug  Action slug.
	 *
	 * @return string
	 */
	protected function getPathByActionSlug( $slug ) {
		return '/?' . $this->namespace . '=' . $slug;
	}
}
