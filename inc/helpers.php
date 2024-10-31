<?php

use NativeRent\Common\NRentService;
use NativeRent\Core\Container\Exceptions\DependencyNotFound;
use NativeRent\Core\Events\DispatcherInterface;
use NativeRent\Core\Events\EventInterface;
use NativeRent\Core\Routing\Route;
use NativeRent\Core\Routing\RoutesCollection;
use NativeRent\Core\View\Exceptions\TemplateNotFound;
use NativeRent\Core\View\Renderer;
use NativeRent\Core\View\ViewInterface;
use NativeRent\Plugin;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;

/*
 * COMMON HELPERS.
 * These helpers do not use CMS functionality.
 */

if ( ! function_exists( 'nrentapp' ) ) {
	/**
	 * Plugin container.
	 *
	 * @template T
	 *
	 * @param  class-string<T>|null $dep  Dependency.
	 *
	 * @return T|ContainerInterface
	 * @throws DependencyNotFound
	 * @throws ContainerExceptionInterface
	 * @throws NotFoundExceptionInterface
	 */
	function nrentapp( $dep = null ) {
		$c = Plugin::instance()->getContainer();
		if ( is_null( $dep ) ) {
			return $c;
		}

		return $c->get( $dep );
	}
}

if ( ! function_exists( 'nrentroute' ) ) {
	/**
	 * Get registered route by name.
	 *
	 * @param  string $name  Route name.
	 *
	 * @return Route
	 * @throws Exception
	 */
	function nrentroute( $name ) {
		$route = nrentapp( RoutesCollection::class )->getRouteByName( $name );
		if ( is_null( $route ) ) {
			throw new Exception(
				json_encode( "Route `$name` not found!", JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE )
			);
		}

		return $route;
	}
}

if ( ! function_exists( 'nrentevent' ) ) {
	/**
	 * Dispatching event.
	 *
	 * @param  EventInterface $event
	 *
	 * @return void
	 * @throws DependencyNotFound
	 */
	function nrentevent( $event ) {
		nrentapp( DispatcherInterface::class )->dispatch( $event );
	}
}

if ( ! function_exists( 'nrentview_e' ) ) {
	/**
	 * Render and display view.
	 *
	 * @param  ViewInterface $view  View instance.
	 *
	 * @throws DependencyNotFound|TemplateNotFound|InvalidArgumentException
	 */
	function nrentview_e( $view ) {
		if ( ! $view instanceof ViewInterface ) {
			throw new InvalidArgumentException( 'Argument `$view` must be instance of `ViewInterface`.' );
		}

		nrentapp( Renderer::class )->display( $view );
	}
}

if ( ! function_exists( 'nrent_capture_err' ) ) {
	/**
	 * Error capturing.
	 *
	 * @param  Exception | \Throwable $e
	 *
	 * @phpcs:disable Squiz.Commenting.FunctionCommentThrowTag.Missing
	 * @return void
	 */
	function nrent_capture_err( $e ) {
		$isDebug = ( 1 == getenv( 'NATIVERENT_DEBUG' ) );

		try {
			if ( ! nrentapp()->has( NRentService::class ) ) {
				return;
			}
			nrentapp( NRentService::class )->sendErrorToTracker( $e );
		} catch ( Exception $e ) {
			if ( $isDebug ) {
				throw $e;
			}
			return;
		} catch ( \Throwable $e ) {
			if ( $isDebug ) {
				throw $e;
			}
			return;
		}
	}
}

if ( ! function_exists( 'nrent_is_punycode' ) ) {
	/**
	 *
	 * Check the domain representation in punycode.
	 *
	 * @param string $domain
	 *
	 * @return bool
	 */
	function nrent_is_punycode( $domain ) {
		return (bool) preg_match( '/xn--[a-z0-9]+/', $domain );
	}
}

/*
 * WORDPRESS ONLY HELPERS.
 * These helpers can use wordpress functionality.
 */

if ( ! function_exists( 'wpnrent_get_domain' ) ) {
	/**
	 * Returns domain name of this site.
	 *
	 * @return string
	 */
	function wpnrent_get_domain() {
		$envDomain = getenv( 'NATIVERENT_AUTH_DOMAIN' );
		$domain    = ! empty( $envDomain )
			? sanitize_text_field( ( wp_unslash( $envDomain ) ) )
			: wp_parse_url( get_home_url(), PHP_URL_HOST );

		if ( nrent_is_punycode( $domain ) && function_exists( 'idn_to_utf8' ) ) {
			$domain = idn_to_utf8( $domain, 0, INTL_IDNA_VARIANT_UTS46 );
		}

		return ! empty( $domain ) ? $domain : '';
	}
}

if ( ! function_exists( 'wpnrent_verify_nonce' ) ) {
	/**
	 * Nonce verification method.
	 *
	 * @param  string|int $action  Action name.
	 *
	 * @return bool
	 */
	function wpnrent_verify_nonce( $action = - 1 ) {
		return (bool) wp_verify_nonce(
			isset( $_POST['_wpnonce'] ) ? sanitize_key( $_POST['_wpnonce'] ) : '',
			$action
		);
	}
}
