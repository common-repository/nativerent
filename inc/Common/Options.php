<?php

namespace NativeRent\Common;

use InvalidArgumentException;
use NativeRent\Common\Entities\AdUnitsConfig;
use NativeRent\Common\Entities\Monetizations;
use NativeRent\Common\Entities\SiteModerationStatus;
use NativeRent\Common\Entities\StateOptions;
use NativeRent\Core\Options\OptionStorageInterface;
use ReflectionClass;

use function is_null;
use function is_string;
use function stripos;

/**
 * Plugin options class.
 *
 * TODO: need tests of observer
 */
final class Options {
	const OPT_SITE_ID = 'siteID';
	const OPT_VERSION = 'version';
	const OPT_ADV_PATTERNS = 'advPatterns';
	const OPT_INVALID_TOKEN = 'invalidToken';
	const OPT_ADUNITS_CONFIG = 'adUnitsConfig';
	const OPT_MONETIZATIONS = 'monetizations';
	const OPT_SITE_MODERATION_STATUS = 'siteModerationStatus';
	const OPT_INTEGRATION_TOKEN = 'token';
	const OPT_PLUGIN_SECRET_KEY = 'secretKey';
	const OPT_CLEAR_CACHE_FLAG = 'clearCacheFlag';

	/**
	 * Options namespace.
	 *
	 * @var string
	 */
	private $namespace = 'nativerent';

	/**
	 * Options storage.
	 *
	 * @var OptionStorageInterface
	 */
	private $storage;

	/**
	 * Options observer.
	 *
	 * @var OptionsObserver|null
	 */
	private $observer;

	public function __construct( OptionStorageInterface $storage, OptionsObserver $observer = null ) {
		$this->storage = $storage;
		$this->observer = $observer;
	}

	/**
	 * Get full option name.
	 *
	 * @param  string $name
	 *
	 * @return string
	 */
	private function opt( $name ) {
		return $this->namespace . '.' . $name;
	}

	/**
	 * Get current siteID value.
	 *
	 * @return string|null
	 */
	public function getSiteID() {
		return $this->storage->get( $this->opt( self::OPT_SITE_ID ) );
	}

	/**
	 * Site ID setter.
	 *
	 * @param  string|null $siteID  Site ID issued by Native Rent.
	 *
	 * @return bool
	 *
	 * @throws InvalidArgumentException
	 */
	public function setSiteID( $siteID ) {
		if ( ! is_string( $siteID ) && ! is_null( $siteID ) ) {
			throw new InvalidArgumentException( 'Site ID must be string or null' );
		}

		return $this->storage->set( $this->opt( self::OPT_SITE_ID ), $siteID );
	}

	/**
	 * Get last saved plugin version.
	 *
	 * TODO: need to rename `nativerent_version` to `nativerent.version`!
	 *
	 * @return string|null
	 */
	public function getPluginVersion() {
		return $this->storage->get( 'nativerent_' . self::OPT_VERSION );
	}

	/**
	 * Persist plugin version.
	 *
	 * @param  string $version  Actual plugin version.
	 *
	 * @return bool
	 * @throws InvalidArgumentException
	 */
	public function setPluginVersion( $version ) {
		if ( ! is_string( $version ) ) {
			throw new InvalidArgumentException( 'Version must be a string value' );
		}

		return $this->storage->set( 'nativerent_' . self::OPT_VERSION, $version );
	}

	/**
	 * Get saved patterns for blocking 3rd-party advertisement.
	 *
	 * @return string[]|null
	 */
	public function getAdvPatterns() {
		$raw = $this->storage->get( $this->opt( self::OPT_ADV_PATTERNS ) );
		if ( ! is_string( $raw ) ) {
			return null;
		}

		$arrayed = json_decode( $raw, true );

		return is_array( $arrayed ) ? $arrayed : [];
	}

	/**
	 * Updating patterns of 3rd-party adv.
	 *
	 * @param  string[] $patterns
	 *
	 * @return bool
	 */
	public function updateAdvPatterns( $patterns ) {
		if ( ! is_array( $patterns ) ) {
			return false;
		}
		$patterns = array_values(
			array_filter(
				$patterns,
				function ( $p ) {
					return ( is_string( $p ) && '' !== $p );
				}
			)
		);
		$res = $this->storage->set(
			$this->opt( self::OPT_ADV_PATTERNS ),
			json_encode( $patterns )
		);
		if ( $res ) {
			$this->notifyObserverAboutUpdate( self::OPT_ADV_PATTERNS, $patterns );
		}

		return $res;
	}

	/**
	 * Get flag value about invalid token.
	 *
	 * @return bool
	 */
	public function getInvalidTokenFlag() {
		$res = $this->storage->get( $this->opt( self::OPT_INVALID_TOKEN ) );

		return 1 == $res;
	}

	/**
	 * Updating value of invalid token flag.
	 *
	 * @param  bool $val
	 *
	 * @return bool
	 */
	public function setInvalidTokenFlag( $val = true ) {
		return $this->storage->set( $this->opt( self::OPT_INVALID_TOKEN ), true === $val ? 1 : 0 );
	}

	/**
	 * Get ad-units configuration instance.
	 *
	 * @return AdUnitsConfig
	 */
	public function getAdUnitsConfig() {
		$raw = $this->storage->get( $this->opt( self::OPT_ADUNITS_CONFIG ) );
		if ( ! is_string( $raw ) ) {
			return new AdUnitsConfig();
		}
		$decoded = json_decode( $raw, true );

		return new AdUnitsConfig( is_array( $decoded ) ? $decoded : [] );
	}

	/**
	 * Updating ad-units configuration.
	 *
	 * @param  AdUnitsConfig $config
	 *
	 * @return  bool
	 */
	public function setAdUnitsConfig( AdUnitsConfig $config ) {
		$res = $this->storage->set( $this->opt( self::OPT_ADUNITS_CONFIG ), json_encode( $config ) );
		if ( $res ) {
			$this->notifyObserverAboutUpdate( self::OPT_ADUNITS_CONFIG, $config );
		}

		return $res;
	}

	/**
	 * Get secret key.
	 *
	 * @return string|null
	 */
	public function getSecretKey() {
		$res = $this->storage->get( $this->opt( 'secretKey' ) );
		if ( ! is_string( $res ) ) {
			return null;
		}

		return $res;
	}

	/**
	 * Get monetizations statuses.
	 *
	 * @return Monetizations
	 */
	public function getMonetizations() {
		$raw = $this->storage->get( $this->opt( self::OPT_MONETIZATIONS ) );
		if ( ! is_string( $raw ) ) {
			return new Monetizations();
		}
		$decoded = json_decode( $raw, true );

		return Monetizations::hydrate( is_array( $decoded ) ? $decoded : [] );
	}

	/**
	 * Updating monetizations statuses.
	 *
	 * @param  Monetizations $monetizations
	 *
	 * @return bool
	 */
	public function updateMonetizations( Monetizations $monetizations ) {
		$json = json_encode( $monetizations );
		if ( ! is_string( $json ) || '' === $json ) {
			return false;
		}
		$res = $this->storage->set( $this->opt( self::OPT_MONETIZATIONS ), $json );
		if ( $res ) {
			$this->notifyObserverAboutUpdate( self::OPT_MONETIZATIONS, $monetizations );
		}

		return $res;
	}

	/**
	 * Get current state options.
	 *
	 * @return StateOptions
	 */
	public function getStateOptions() {
		return new StateOptions(
			[
				self::OPT_SITE_ID                => $this->getSiteID(),
				self::OPT_VERSION                => $this->getPluginVersion(),
				self::OPT_ADUNITS_CONFIG         => $this->getAdUnitsConfig(),
				self::OPT_MONETIZATIONS          => $this->getMonetizations(),
				self::OPT_SITE_MODERATION_STATUS => $this->getSiteModerationStatus(),
			]
		);
	}

	/**
	 * Get integration API access token.
	 *
	 * @return string|null
	 */
	public function getIntegrationAccessToken() {
		$token = $this->storage->get( $this->opt( self::OPT_INTEGRATION_TOKEN ) );
		if ( ! is_string( $token ) || empty( $token ) ) {
			return null;
		}

		return $token;
	}

	/**
	 * Set integration API access token.
	 *
	 * @param  string|null $token
	 *
	 * @return bool
	 * @throws InvalidArgumentException
	 */
	public function setIntegrationAccessToken( $token ) {
		if ( ! is_string( $token ) && ! is_null( $token ) ) {
			throw new InvalidArgumentException( 'Token must be string or null' );
		}

		return $this->storage->set( $this->opt( self::OPT_INTEGRATION_TOKEN ), $token );
	}

	/**
	 * Save secret key to options storage.
	 *
	 * @param  string|null $secretKey
	 *
	 * @return bool
	 *
	 * @throws InvalidArgumentException
	 */
	public function setPluginSecretKey( $secretKey ) {
		if ( ! is_string( $secretKey ) && ! is_null( $secretKey ) ) {
			throw new InvalidArgumentException( 'Secret key must be string' );
		}

		return $this->storage->set( $this->opt( self::OPT_PLUGIN_SECRET_KEY ), $secretKey );
	}

	/**
	 * Plugin secret key getter.
	 * This key is used to access the plugin's API.
	 *
	 * @return string|null
	 */
	public function getPluginSecretKey() {
		$secretKey = $this->storage->get( $this->opt( self::OPT_PLUGIN_SECRET_KEY ) );
		if ( ! is_string( $secretKey ) || empty( $secretKey ) ) {
			return null;
		}

		return $secretKey;
	}

	/**
	 * Site moderation status getter.
	 *
	 * @return SiteModerationStatus
	 */
	public function getSiteModerationStatus() {
		$raw = $this->storage->get( $this->opt( self::OPT_SITE_MODERATION_STATUS ) );

		return new SiteModerationStatus( is_numeric( $raw ) ? $raw : null );
	}

	/**
	 * Site moderation status getter.
	 *
	 * @param  SiteModerationStatus $status
	 *
	 * @return bool
	 */
	public function setSiteModerationStatus( SiteModerationStatus $status ) {
		$res = $this->storage->set( $this->opt( self::OPT_SITE_MODERATION_STATUS ), $status->getValue() );
		if ( $res ) {
			$this->notifyObserverAboutUpdate( self::OPT_SITE_MODERATION_STATUS, $status );
		}

		return $res;
	}

	/**
	 * Updating clear cache flag value.
	 *
	 * @param  int $val
	 *
	 * @return bool
	 * @throws InvalidArgumentException
	 */
	public function setClearCacheFlag( $val ) {
		if ( ! is_numeric( $val ) ) {
			throw new InvalidArgumentException( 'Clear cache flag must be numeric value' );
		}

		return $this->storage->set( $this->opt( self::OPT_CLEAR_CACHE_FLAG ), (int) $val );
	}

	/**
	 * Getting clear cache flag value.
	 *
	 * @return int
	 */
	public function getClearCacheFlag() {
		$val = $this->storage->get( $this->opt( self::OPT_CLEAR_CACHE_FLAG ) );

		return is_numeric( $val ) ? $val : 0;
	}

	/**
	 * Purge options.
	 *
	 * @return void
	 */
	public function purge() {
		$const = ( new ReflectionClass( $this ) )->getConstants();
		foreach ( $const as $name => $val ) {
			if ( 0 !== stripos( $name, 'opt_' ) ) {
				continue;
			}
			if ( self::OPT_VERSION === $val ) {
				$optName = 'nativerent_' . $val;
			} else {
				$optName = $this->opt( $val );
			}
			$this->storage->delete( $optName );
		}
	}

	/**
	 * @param string $option
	 * @param mixed  $value
	 *
	 * @return void
	 */
	private function notifyObserverAboutUpdate( $option, $value = null ) {
		if ( is_null( $this->observer ) ) {
			return;
		}

		$this->observer->updated( $option, $value );
	}
}
