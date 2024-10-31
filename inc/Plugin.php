<?php

namespace NativeRent;

use Closure;
use Exception;
use NativeRent\Admin\Bootloader as AdminBootloader;
use NativeRent\Api\Bootloader as ApiBootloader;
use NativeRent\Common\Bootloader as CommonBootloader;
use NativeRent\Core\Container\Container;
use NativeRent\Frontend\Bootloader as FrontendBootloader;
use Psr\Container\ContainerInterface;

use function is_admin;
use function is_null;

/**
 * Plugin bootstrapper class.
 */
final class Plugin {

	/**
	 * The single instance of the class.
	 *
	 * @var self|null
	 */
	private static $instance = null;

	/**
	 * @var Container
	 */
	protected $container;

	/**
	 * Init flag.
	 *
	 * @var bool
	 */
	protected $initialized = false;

	/**
	 * Instance getter.
	 *
	 * @return self
	 */
	public static function instance() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Protected constructor.
	 */
	private function __construct() {
		$this->container = new Container();
	}

	/**
	 * Container getter.
	 *
	 * @return ContainerInterface
	 */
	public function getContainer() {
		return $this->container;
	}

	/**
	 * Init bootstrapper.
	 *
	 * @return void
	 * @throws Exception|\Throwable
	 */
	public function init() {
		if ( $this->initialized ) {
			return;
		}

		$err = $this->tryCatch(
			function () {
				$this->registerBootloaders();
			}
		);

		// Error handling.
		if ( ! is_null( $err ) ) {
			try {
				nrent_capture_err( $err );
			} finally {
				throw $err;
			}
		}

		$this->initialized = true;
	}

	/**
	 * @param  Closure $action
	 *
	 * @return Exception|\Throwable|null
	 */
	private function tryCatch( Closure $action ) {
		try {
			$action();
		} catch ( Exception $e ) {
			return $e;
		} catch ( \Throwable $e ) {
			return $e;
		}

		return null;
	}

	/**
	 * @return void
	 */
	private function registerBootloaders() {
		new CommonBootloader( $this->container );
		if ( defined( 'NATIVERENT_UNINSTALL' ) ) {
			return;
		}

		switch ( true ) {
			case $this->isApiRequest():
				new ApiBootloader( $this->container );
				break;
			case is_admin():
				new AdminBootloader( $this->container );
				break;
			case $this->isRequestToFrontend():
				new FrontendBootloader( $this->container );
				break;
		}
	}


	/**
	 * Detection API request.
	 *
	 * @phpcs:disable WordPress.Security.ValidatedSanitizedInput
	 *
	 * @return false
	 */
	private function isApiRequest() {
		$mime = isset( $_SERVER['HTTP_ACCEPT'] )
			? $_SERVER['HTTP_ACCEPT']
			: ( isset( $_SERVER['CONTENT_TYPE'] ) ? $_SERVER['CONTENT_TYPE'] : null );

		if ( ! is_string( $mime ) || false === stripos( $mime, 'application/json' ) ) {
			return false;
		}

		return (
				isset( $_GET[ NATIVERENT_API_V1_PARAM ] )
				|| isset( $_SERVER[ strtoupper( 'HTTP_X_' . NATIVERENT_API_V1_PARAM ) ] )
		);
	}

	private function isRequestToFrontend() {
		return ! wp_doing_cron() && isset( $_SERVER['REQUEST_URI'] );
	}

	/**
	 * A dummy magic method to prevent class from being cloned
	 */
	public function __clone() {
	}

	/**
	 * A dummy magic method to prevent class from being un-serialized
	 */
	public function __wakeup() {
	}
}
