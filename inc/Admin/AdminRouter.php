<?php

namespace NativeRent\Admin;

use NativeRent\Common\AbstractRouter;

use function is_array;
use function parse_str;
use function parse_url;
use function strtolower;
use function trim;

use const PHP_URL_PATH;

/**
 * Admin router
 */
final class AdminRouter extends AbstractRouter {
	/** @var string */
	private $rootPath;

	/**
	 * Construct.
	 *
	 * @param  string $rootPath   Root path.
	 * @param  string $namespace  Root route.
	 */
	public function __construct( $rootPath, $namespace ) {
		$parsed          = parse_url( $rootPath, PHP_URL_PATH );
		$this->rootPath  = is_string( $parsed ) ? $parsed : '';
		$this->namespace = strtolower( $namespace );
	}

	/**
	 * {@inheritDoc}
	 */
	public function match( $method, $uri, $headers = [] ) {
		// Parsing URI and checking path and query.
		$parsed = parse_url( $uri );
		if (
			! is_array( $parsed )
			|| empty( $parsed['path'] )
			|| empty( $parsed['query'] )
			|| strtolower( $parsed['path'] ) !== $this->rootPath
		) {
			return null;
		}

		// Parsing query string and checking `?page` value.
		parse_str( $parsed['query'], $parsedQuery );
		if (
			! is_array( $parsedQuery )
			|| empty( $parsedQuery['page'] )
			|| $this->namespace !== $parsedQuery['page']
		) {
			return null;
		}

		// Getting `action` param and getting route by this param value.
		$actionSlug = ( isset( $parsedQuery['action'] ) ? trim( $parsedQuery['action'] ) : '' );
		$routeKey   = $this->getRouteKey( $method, $this->getPathByActionSlug( $actionSlug ) );

		return ( isset( $this->routes[ $routeKey ] ) ? $this->routes[ $routeKey ] : null );
	}

	/**
	 * Get a full route path by action slug.
	 *
	 * @param  string $slug  Action slug.
	 *
	 * @return string
	 */
	protected function getPathByActionSlug( $slug ) {
		return $this->rootPath .
			   ( '?page=' . $this->namespace ) .
			   ( ! empty( $slug ) ? '&action=' . $slug : '' );
	}
}
