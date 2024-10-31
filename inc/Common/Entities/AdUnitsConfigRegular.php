<?php

namespace NativeRent\Common\Entities;

use InvalidArgumentException;
use JsonSerializable;

class AdUnitsConfigRegular implements JsonSerializable {
	/**
	 * @var AdUnitProps
	 */
	public $horizontalTop;

	/**
	 * @var AdUnitProps
	 */
	public $horizontalMiddle;

	/**
	 * @var AdUnitProps
	 */
	public $horizontalBottom;

	/**
	 * @var AdUnitProps
	 */
	public $popupTeaser;

	/**
	 * @param  array<string, AdUnitProps|array<string, mixed>> $data
	 *
	 * @throws InvalidArgumentException
	 */
	public function __construct( $data = [] ) {
		if ( ! is_array( $data ) ) {
			throw new InvalidArgumentException( 'Data arg must be array' );
		}
		$props = [
			'horizontalTop'    => [
				'insert'         => 'after',
				'autoSelector'   => 'firstParagraph',
				'customSelector' => '',
			],
			'horizontalMiddle' => [
				'insert'         => 'after',
				'autoSelector'   => 'middleParagraph',
				'customSelector' => '',
			],
			'horizontalBottom' => [
				'insert'         => 'after',
				'autoSelector'   => 'lastParagraph',
				'customSelector' => '',
			],
			'popupTeaser'      => [
				'insert'         => 'inside',
				'autoSelector'   => 'body',
				'customSelector' => '',
				'settings'       => [
					'desktopTeaser'    => true,
					'mobileTeaser'     => true,
					'mobileFullscreen' => true,
				],
			],
		];
		foreach ( $props as $prop => $defaults ) {
			$this->fill( $prop, $data, $defaults );
		}
	}

	/**
	 * @param  string                                          $propName
	 * @param  array<string, AdUnitProps|array<string, mixed>> $data
	 * @param  array<string, mixed>                            $defaults
	 *
	 * @return void
	 */
	private function fill( $propName, $data, $defaults = [] ) {
		if ( isset( $data[ $propName ] ) ) {
			if ( is_array( $data[ $propName ] ) ) {
				$this->$propName = new AdUnitProps( $data[ $propName ] );
			} elseif ( $data[ $propName ] instanceof AdUnitProps ) {
				$this->$propName = $data[ $propName ];
			}
		}
		if ( ! $this->$propName instanceof AdUnitProps ) {
			$this->$propName = new AdUnitProps( [], $defaults );
		}
		if ( ! empty( $defaults['settings'] ) ) {
			$this->$propName->settings = array_merge( $defaults['settings'], $this->$propName->settings );
		}
	}

	/**
	 * {@inheritDoc}
	 */
	#[\ReturnTypeWillChange]
	public function jsonSerialize() {
		return (array) $this;
	}
}
