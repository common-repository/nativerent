<?php

namespace NativeRent\Admin;

/**
 * TODO: это нужно отрефакторить.
 */
final class SiteCache {
	/**
	 * Check if clearing possible
	 *
	 * @return bool
	 */
	public static function isClearingCachePossible() {
		// WP Super Cache.
		if ( self::isWpSuperCache() || self::isWpSuperCache1() ) {
			return true;
		}

		// W3 Total Cache.
		if ( self::isW3TotalCache() ) {
			return true;
		}

		// WP Fastest Cache.
		if ( self::isWpFastestCache() ) {
			return true;
		}

		// Autoptimize.
		if ( self::isAutoptimize() ) {
			return true;
		}

		// WP Optimize.
		if ( self::isWpOptomize() ) {
			return true;
		}

		// Comet Cache.
		if ( self::isCometCache() ) {
			return true;
		}

		// Cachify.
		if ( self::isCachify() ) {
			return true;
		}

		// Rapid Cache.
		if ( self::isRapidCache() ) {
			return true;
		}

		// Swift Performance.
		if ( self::isSwiftPerformanceCache() ) {
			return true;
		}

		// WP Engine.
		if ( self::isWpEngine() ) {
			return true;
		}

		// SG Optimizer.
		if ( self::isSitegroundOptimizer() ) {
			return true;
		}

		// D-WP cache.
		if ( self::isDWpCache() ) {
			return true;
		}

		// Nginx Helper.
		if ( self::isNginxHelper() ) {
			return true;
		}

		// Breeze cache.
		if ( self::isBreeze() ) {
			return true;
		}

		// Hummingbird.
		if ( self::isHummingbird() ) {
			return true;
		}

		// HyperCache.
		if ( self::isHyperCache() ) {
			return true;
		}

		// WP Rocket.
		if ( self::isRocket() ) {
			return true;
		}

		// Seraphinite Accelerator.
		if ( self::isSeraphAccel() ) {
			return true;
		}

		// Simple Cache.
		if ( self::isSimpleCache() ) {
			return true;
		}

		return false;
	}

	/**
	 * Clear cache
	 */
	public static function clearCache() {
		// WP Super Cache.
		if ( self::isWpSuperCache() ) {
			if ( is_multisite() ) {
				\wp_cache_clear_cache( get_current_blog_id() );
			} else {
				\wp_cache_clear_cache();
			}
		} elseif ( self::isWpSuperCache1() ) {
			global $cache_path;
			if ( is_multisite() ) {
				\prune_super_cache( get_supercache_dir( get_current_blog_id() ), true );
				\prune_super_cache( $cache_path . 'blogs/', true );
			} else {
				\prune_super_cache( $cache_path . 'supercache/', true );
				\prune_super_cache( $cache_path, true );
			}
			// W3 Total Cache.
		} elseif ( self::isW3TotalCache() ) {
			\w3tc_pgcache_flush();
			// WP Fastest Cache.
		} elseif ( self::isWpFastestCache() ) {
			$wpfc = new \WpFastestCache();
			$wpfc->deleteCache( true );
			// Autoptimize.
		} elseif ( self::isAutoptimize() ) {
			\autoptimizeCache::clearall();
			// WP Optimize.
		} elseif ( self::isWpOptomize() ) {
			\WP_Optimize()->get_page_cache()->purge();
			// Comet Cache.
		} elseif ( self::isCometCache() ) {
			\comet_cache::clear();
			// Cachify.
		} elseif ( self::isCachify() ) {
			do_action( 'cachify_flush_cache' );
			// Rapid Cache.
		} elseif ( self::isRapidCache() ) {
			\rapidcache_clear_cache();
			// Swift Performance.
		} elseif ( self::isSwiftPerformanceCache() ) {
			\Swift_Performance_Cache::clear_all_cache();
			// WP Engine.
		} elseif ( self::isWpEngine() ) {
			\WpeCommon::purge_varnish_cache();
			// SG Optimizer.
		} elseif ( self::isSitegroundOptimizer() ) {
			\sg_cachepress_purge_cache();
			// Nginx Helper.
		} elseif ( self::isNginxHelper() ) {
			do_action( 'rt_nginx_helper_purge_all' );
			// Breeze cache.
		} elseif ( self::isBreeze() ) {
			do_action( 'breeze_clear_all_cache' );
			// Hummingbird.
		} elseif ( self::isHummingbird() ) {
			do_action( 'wphb_clear_page_cache' );
			// HyperCache.
		} elseif ( self::isHyperCache() ) {
			do_action( 'autoptimize_action_cachepurged' );
			// D-WP cache.
		} elseif ( self::isDWpCache() ) {
			\d_cache::get()->clear_all();
			// WP Rocket.
		} elseif ( self::isRocket() ) {
			\rocket_clean_domain();
			// Seraphinite Accelerator.
		} elseif ( self::isSeraphAccel() ) {
			\seraph_accel\CacheOp( 0 );
			// Simple Cache.
		} elseif ( self::isSimpleCache() ) {
			sc_cache_flush();
		}
	}

	/**
	 * WP Super Cache.
	 *
	 * @return bool
	 */
	private static function isWpSuperCache() {
		return function_exists( 'wp_cache_clear_cache' );
	}

	/**
	 * WP Super Cache.
	 *
	 * @return bool
	 */
	private static function isWpSuperCache1() {
		return ( file_exists( WP_CONTENT_DIR . '/wp-cache-config.php' ) && function_exists( 'prune_super_cache' ) );
	}

	/**
	 * W3 Total Cache.
	 *
	 * @return bool
	 */
	private static function isW3TotalCache() {
		return function_exists( 'w3tc_pgcache_flush' );
	}

	/**
	 * WP Fastest Cache.
	 *
	 * @return bool
	 */
	private static function isWpFastestCache() {
		return ( class_exists( 'WpFastestCache' ) && method_exists( 'WpFastestCache', 'deleteCache' ) );
	}

	/**
	 * Autoptimize.
	 *
	 * @return bool
	 */
	private static function isAutoptimize() {
		return ( class_exists( 'autoptimizeCache' ) && is_callable( [ 'autoptimizeCache', 'clearall' ] ) );
	}

	/**
	 * WP Optimize.
	 *
	 * @return bool
	 */
	private static function isWpOptomize() {
		return ( class_exists( 'WP_Optimize' ) && method_exists( 'WP_Optimize', 'get_page_cache' ) );
	}

	/**
	 * Comet Cache.
	 *
	 * @return bool
	 */
	private static function isCometCache() {
		return ( class_exists( '\\comet_cache' ) && is_callable( [ '\\comet_cache', 'clear' ] ) );
	}

	/**
	 * Cachify.
	 *
	 * @return bool
	 */
	private static function isCachify() {
		if ( has_action( 'cachify_flush_cache' ) ) {
			return true;
		}

		return false;
	}

	/**
	 * Rapid Cache.
	 *
	 * @return bool
	 */
	private static function isRapidCache() {
		return function_exists( 'rapidcache_clear_cache' );
	}

	/**
	 * Swift Performance.
	 *
	 * @return bool
	 */
	private static function isSwiftPerformanceCache() {
		return ( class_exists( 'Swift_Performance_Cache' )
				&& is_callable(
					[
						'Swift_Performance_Cache',
						'clear_all_cache',
					]
				)
		);
	}

	/**
	 * WP Engine.
	 *
	 * @return bool
	 */
	private static function isWpEngine() {
		return ( class_exists( 'WpeCommon' ) && is_callable( [ 'WpeCommon', 'purge_varnish_cache' ] ) );
	}

	/**
	 * SiteGround Optimizer.
	 *
	 * @return bool
	 */
	private static function isSitegroundOptimizer() {
		return ( function_exists( 'sg_cachepress_purge_cache' ) );
	}

	/**
	 * D-WP cache.
	 *
	 * @return bool
	 */
	private static function isDWpCache() {
		return class_exists( 'd_cache' );
	}

	/**
	 * Nginx Helper.
	 *
	 * @return bool
	 */
	private static function isNginxHelper() {
		return defined( 'NGINX_HELPER_BASENAME' );
	}

	/**
	 * Breeze cache.
	 */
	private static function isBreeze() {
		return class_exists( 'Breeze_Admin' );
	}

	/**
	 * Hummingbird.
	 */
	private static function isHummingbird() {
		return class_exists( 'Hummingbird\\WP_Hummingbird' );
	}

	/**
	 * HyperCache.
	 */
	private static function isHyperCache() {
		return class_exists( 'HyperCache' );
	}

	/**
	 * WP Rocket.
	 */
	private static function isRocket() {
		return function_exists( 'rocket_clean_domain' );
	}

	/**
	 * Seraphinite Accelerator.
	 */
	private static function isSeraphAccel() {
		return function_exists( 'seraph_accel\\CacheOp' );
	}

	/**
	 * Simple Cache.
	 */
	private static function isSimpleCache() {
		return function_exists( 'sc_cache_flush' );
	}
}
