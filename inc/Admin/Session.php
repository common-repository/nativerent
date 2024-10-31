<?php

namespace NativeRent\Admin;

/** TODO: need to refactor. */
class Session {
	const PAYLOAD_KEY = 'NATIVERENT_SESSION';

	/** @var array<string, mixed> */
	private $payload;

	/**
	 * @param  array<string, mixed> $payload
	 */
	public function __construct( $payload = [] ) {
		$this->payload = $payload;
	}

	/**
	 * @return self
	 */
	public static function init() {
		self::initSession();

		return new self( self::readPayload() );
	}

	/**
	 * @return void
	 */
	protected static function initSession() {
		if ( PHP_SESSION_ACTIVE !== session_status() ) {
			session_start();
		}
	}

	/**
	 * @return array
	 */
	protected static function readPayload() {
		$payload = [];
		if ( isset( $_SESSION[ self::PAYLOAD_KEY ] ) && is_array( $_SESSION[ self::PAYLOAD_KEY ] ) ) {
			$payload = $_SESSION[ self::PAYLOAD_KEY ]; // @phpcs:ignore
			unset( $_SESSION[ self::PAYLOAD_KEY ] );
		}

		return $payload;
	}

	/**
	 * @param string $key
	 * @param mixed  $payload
	 *
	 * @return void
	 */
	protected function addToSession( $key, $payload ) {
		self::initSession();
		$_SESSION[ self::PAYLOAD_KEY ][ $key ] = $payload;
	}

	/**
	 * @param  string $key
	 * @param  mixed  $payload
	 *
	 * @return $this
	 */
	public function add( $key, $payload ) {
		$this->payload[ $key ] = $payload;
		$this->addToSession( $key, $payload );

		return $this;
	}

	/**
	 * @param  string|null $key
	 * @param mixed       $default
	 *
	 * @return array<string, mixed>
	 */
	public function get( $key = null, $default = null ) {
		if ( ! is_null( $key ) ) {
			return isset( $this->payload[ $key ] ) ? $this->payload[ $key ] : $default;
		}

		return $this->payload;
	}
}
