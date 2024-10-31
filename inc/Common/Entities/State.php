<?php

namespace NativeRent\Common\Entities;

use JsonSerializable;

/**
 * Plugin state struct.
 *
 * @property-read StateOptions $options
 * @property-read CmsInfo      $cmsInfo
 */
class State implements JsonSerializable {
	/**
	 * @var StateOptions
	 */
	public $options;

	/**
	 * @var CmsInfo
	 */
	public $cmsInfo;

	/**
	 * @param  StateOptions|null $options
	 * @param  CmsInfo|null      $cmsInfo
	 */
	public function __construct(
		StateOptions $options = null,
		CmsInfo $cmsInfo = null
	) {
		$this->options = ( is_null( $options ) ? new StateOptions() : $options );
		$this->cmsInfo = ( is_null( $cmsInfo ) ? new CmsInfo() : $cmsInfo );
	}

	/**
	 * {@inheritDoc}
	 */
	#[\ReturnTypeWillChange]
	public function jsonSerialize() {
		return (array) $this;
	}
}
