<?php

namespace NativeRent\Common\Entities;

use JsonSerializable;

class AdUnitProps implements JsonSerializable {
	/**
	 * Insertion method.
	 *
	 * @var string after|before
	 */
	public $insert = 'after';

	/**
	 * Preconfigured selector for placement unit.
	 *
	 * @var string firstParagraph|middleParagraph|lastParagraph
	 */
	public $autoSelector = '';

	/**
	 * Custom selector for placement unit.
	 * Ex. `article .content > h2`
	 *
	 * @var string
	 */
	public $customSelector = '';

	/**
	 * Some additional unit props.
	 *
	 * @var array<string, mixed>
	 */
	public $settings = [];

	/**
	 * @param  array{insert?: string, autoSelector?: string, customSelector?: string, settings?: array<string, mixed>} $data
	 * @param  array{insert?: string, autoSelector?: string, customSelector?: string, settings?: array<string, mixed>} $defaults
	 */
	public function __construct( $data = [], $defaults = [] ) {
		$this->fill(
			is_array( $data ) ? $data : [],
			is_array( $defaults ) ? $defaults : []
		);
	}

	/**
	 * Fill props.
	 *
	 * @param  array{insert?: string, autoSelector?: string, customSelector?: string, settings?: array<string, mixed>} $data
	 * @param  array{insert?: string, autoSelector?: string, customSelector?: string, settings?: array<string, mixed>} $defaults
	 *
	 * @return void
	 */
	protected function fill( $data, $defaults = [] ) {
		$props = [
			'insert'         => 'is_string',
			'autoSelector'   => 'is_string',
			'customSelector' => 'is_string',
			'settings'       => 'is_array',
		];

		foreach ( $props as $prop => $checkTypeFunc ) {
			$this->$prop = ! self::fillCondition( $data, $prop, $checkTypeFunc )
				? ( self::fillCondition( $defaults, $prop, $checkTypeFunc ) ? $defaults[ $prop ] : $this->$prop )
				: $data[ $prop ];
		}
	}

	/**
	 * @param  array  $data
	 * @param  string $prop
	 * @param  string $typeCheckFunc
	 *
	 * @return bool
	 */
	private static function fillCondition( $data, $prop, $typeCheckFunc = 'is_string' ) {
		return isset( $data[ $prop ] ) && call_user_func( $typeCheckFunc, $data[ $prop ] );
	}

	/**
	 * {@inheritDoc}
	 */
	#[\ReturnTypeWillChange]
	public function jsonSerialize() {
		return (array) $this;
	}
}
