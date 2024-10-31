<?php

namespace NativeRent\Frontend;

use Exception;
use NativeRent\Common\AbstractBootloader;
use NativeRent\Common\Options;
use NativeRent\Frontend\AdBlocker\AdBlocker;
use NativeRent\Frontend\AdBlocker\WPRocketExtension;
use NativeRent\Frontend\Head\HeadTemplate;
use NativeRent\Frontend\Integration\AdBlockingPipeline;
use NativeRent\Frontend\Integration\HeadIntegrationPipeline;
use NativeRent\Frontend\Integration\OutputHandler;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

use function add_action;
use function is_page;
use function is_single;
use function plugins_url;

use const NATIVERENT_PLUGIN_FILE;
use const NATIVERENT_PLUGIN_MAX_PRIORITY;
use const NATIVERENT_PLUGIN_MIN_PRIORITY;
use const NATIVERENT_PLUGIN_VERSION;

final class Bootloader extends AbstractBootloader {
	/**
	 * {@inheritDoc}
	 */
	public function onRegister() {
		add_action( 'template_redirect', [ $this, 'templateRedirectHandler' ], $this->getTemplateRedirectPriority() );
		add_action( 'shutdown', [ $this, 'shutdownHandler' ], NATIVERENT_PLUGIN_MAX_PRIORITY );
	}

	/**
	 * Priority of `template_redirect` handler.
	 *
	 * @return  int|float
	 */
	protected function getTemplateRedirectPriority() {
		// Fix priority for Hyper Cache.
		if ( class_exists( \HyperCache::class ) ) {
			return 1;
		}

		return NATIVERENT_PLUGIN_MIN_PRIORITY;
	}

	/**
	 * Getting Native Rent static host.
	 *
	 * @return string
	 */
	public static function getStaticHost() {
		$host = getenv( 'NATIVERENT_STATIC_HOST' );
		if ( ! empty( $host ) ) {
			return esc_url_raw( wp_unslash( $host ) );
		}

		return 'https://static.nativerent.ru';
	}

	/**
	 * Get full URL of content.js
	 *
	 * @return string
	 */
	public static function getPluginScriptPath() {
		return preg_replace(
			'/^https?\:/ui',
			'',
			plugins_url( 'static/content.js?ver=' . urlencode( NATIVERENT_PLUGIN_VERSION ), NATIVERENT_PLUGIN_FILE )
		);
	}

	/**
	 * @return void
	 * @throws ContainerExceptionInterface
	 * @throws NotFoundExceptionInterface
	 */
	public function templateRedirectHandler() {
		if ( ! is_single() && ! is_page() && ! is_main_query() ) {
			return;
		}

		try {
			$options = $this->container->get( Options::class );
			$state   = $options->getStateOptions();
			if ( $state->monetizations->isAllRejected() || $state->siteModerationStatus->isRejected() ) {
				return;
			}

			$this->container->bind(
				AdBlocker::class,
				function () {
					$patterns = $this->container->get( Options::class )->getAdvPatterns();

					return new AdBlocker(
						is_array( $patterns ) ? $patterns : [],
						[ new WPRocketExtension() ]
					);
				}
			);
			$this->container->bind(
				HeadTemplate::class,
				function () use ( $state ) {
					return new HeadTemplate(
						self::getStaticHost(),
						self::getPluginScriptPath(),
						$state
					);
				}
			);

			// Inserting reference element to HTML of the post content.
			add_filter(
				'the_content',
				/**
				 * @param  string  $content
				 * @return string
				 */
				function ( $content ) {
					return '<div class="nativerent-content-integration"></div>' . PHP_EOL . $content;
				},
				NATIVERENT_PLUGIN_MIN_PRIORITY
			);

			// Output handler.
			$handler = new OutputHandler(
				[
					new AdBlockingPipeline(
						$state->monetizations,
						function () {
							return $this->container->get( AdBlocker::class );
						}
					),
					new HeadIntegrationPipeline( $state, $this->container->get( HeadTemplate::class ) ),
				]
			);
			ob_start(
				function ( $buffer ) use ( $handler ) {
					try {
						return $handler( $buffer );
					} catch ( Exception $e ) {
						nrent_capture_err( $e );
						return $buffer;
					} catch ( \Throwable $e ) {
						nrent_capture_err( $e );
						return $buffer;
					}
				}
			);

		} catch ( Exception $e ) {
			nrent_capture_err( $e );
		} catch ( \Throwable $e ) {
			nrent_capture_err( $e );
		}
	}


	public function shutdownHandler() {
		if ( in_array( OutputHandler::class . '::__invoke', ob_list_handlers() ) ) {
			ob_end_flush();
		}
	}
}
