<?php

namespace NativeRent\Common\Entities;

use ArrayAccess;
use Countable;
use InvalidArgumentException;
use JsonSerializable;

/**
 * @implements ArrayAccess<string, AdUnitProps>
 */
class AdUnitsConfigNTGB implements JsonSerializable, ArrayAccess, Countable {

	/** @var array<string, array<string, mixed> */
	private static $defaultState
		= [
			'1' => [
				'insert'         => 'after',
				'autoSelector'   => 'middleParagraph',
				'customSelector' => '',
				'settings'       => [
					'inactive'     => false,
					'noInsertion'  => false,
					'fallbackCode' => '',
				],
			],
			'2' => [
				'insert'         => 'after',
				'autoSelector'   => 'lastParagraph',
				'customSelector' => '',
				'settings'       => [
					'inactive'     => true,
					'noInsertion'  => false,
					'fallbackCode' => '',
				],
			],
			'3' => [
				'insert'         => 'after',
				'autoSelector'   => 'firstParagraph',
				'customSelector' => '',
				'settings'       => [
					'inactive'     => true,
					'noInsertion'  => false,
					'fallbackCode' => '',
				],
			],
		];

	/** @var array<string, AdUnitProps> */
	private $configs = [];

	/**
	 * @param  array<string, AdUnitProps> $configs
	 */
	public function __construct( $configs = [] ) {
		$this->fill( $configs );
	}

	/**
	 * @param  array<string|int, AdUnitProps|array> $data
	 *
	 * @return void
	 */
	private function fill( $data = [] ) {
		foreach ( self::$defaultState as $name => $defaultProps ) {
			$this->offsetSet(
				$name,
				isset( $data[ $name ] ) && $data[ $name ] instanceof AdUnitProps
					? $data[ $name ]
					: array_merge( $defaultProps, ! empty( $data[ $name ] ) ? $data[ $name ] : [] )
			);
		}
	}

	/**
	 * {@inheritDoc}
	 */
	#[\ReturnTypeWillChange]
	public function jsonSerialize() {
		return array_map(
			function ( AdUnitProps $props ) {
				return $props->jsonSerialize();
			},
			$this->configs
		);
	}

	/**
	 * @param  string $offset
	 *
	 * @return bool
	 */
	#[\ReturnTypeWillChange]
	public function offsetExists( $offset ) {
		return isset( $this->configs[ $offset ] );
	}

	/**
	 * @param  string|int $offset
	 *
	 * @return AdUnitProps|null
	 */
	#[\ReturnTypeWillChange]
	public function offsetGet( $offset ) {
		return $this->offsetExists( (string) $offset ) ? $this->configs[ (string) $offset ] : null;
	}

	/**
	 * @param  string            $offset
	 * @param  AdUnitProps|array $value
	 *
	 * @return void
	 * @throws InvalidArgumentException
	 */
	#[\ReturnTypeWillChange]
	public function offsetSet( $offset, $value ) {
		if ( is_int( $offset ) ) {
			$offset = (string) $offset;
		}
		if ( ! is_string( $offset ) ) {
			throw new InvalidArgumentException( 'The unit name must be string' );
		}
		if ( ! isset( self::$defaultState[ $offset ] ) ) {
			throw new InvalidArgumentException( 'Invalid unit name' );
		}
		if ( ! $value instanceof AdUnitProps && ! is_array( $value ) ) {
			throw new InvalidArgumentException( 'Config must be `AdUnitProps` instance or array' );
		}

		$props = is_array( $value )
			? new AdUnitProps(
				$value,
				isset( self::$defaultState[ $offset ] ) ? self::$defaultState[ $offset ] : self::$defaultState[1]
			)
			: $value;

		$props->settings['inactive'] = ! empty( $props->settings['inactive'] );
		$props->settings['noInsertion'] = ! empty( $props->settings['noInsertion'] );
		if ( ! isset( $props->settings['fallbackCode'] ) ) {
			$props->settings['fallbackCode'] = '';
		}

		$this->configs[ $offset ] = $props;
	}

	/**
	 * @param  string $offset
	 *
	 * @return void
	 */
	#[\ReturnTypeWillChange]
	public function offsetUnset( $offset ) {
		if ( $this->offsetExists( $offset ) ) {
			unset( $this->configs[ $offset ] );
		}
	}

	/**
	 * {@inheritDoc}
	 */
	#[\ReturnTypeWillChange]
	public function count() {
		return count( $this->configs );
	}

	/**
	 * @return array<string, AdUnitProps>
	 */
	public function getIterable() {
		return $this->configs;
	}

	/**
	 * @return array<string, AdUnitProps>
	 */
	public function getActiveUnits() {
		return array_filter(
			$this->configs,
			function ( AdUnitProps $props ) {
				return empty( $props->settings['inactive'] );
			}
		);
	}
}
