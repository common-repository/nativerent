<?php

namespace NativeRent\Common\Entities;

use JsonSerializable;

class AdUnitsConfig implements JsonSerializable {

	/** @var AdUnitsConfigRegular */
	public $regular;

	/** @var AdUnitsConfigNTGB */
	public $ntgb;

	/**
	 * @param  array{regular?: AdUnitsConfigRegular|array, ntgb?: AdUnitsConfigNTGB|array} $data
	 */
	public function __construct( $data = [] ) {
		if ( ! empty( $data['regular'] ) ) {
			$this->regular = is_array( $data['regular'] )
				? new AdUnitsConfigRegular( $data['regular'] )
				: ( $data['regular'] instanceof AdUnitsConfigRegular ? $data['regular'] : null );
		}
		if ( empty( $this->regular ) ) {
			$this->regular = new AdUnitsConfigRegular();
		}

		if ( ! empty( $data['ntgb'] ) ) {
			$this->ntgb = is_array( $data['ntgb'] )
				? new AdUnitsConfigNTGB( $data['ntgb'] )
				: ( $data['ntgb'] instanceof AdUnitsConfigNTGB ? $data['ntgb'] : null );
		}
		if ( empty( $this->ntgb ) ) {
			$this->ntgb = new AdUnitsConfigNTGB();
		}
	}

	/**
	 * @return array
	 */
	#[\ReturnTypeWillChange]
	public function jsonSerialize() {
		return (array) $this;
	}
}
