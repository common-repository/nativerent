<?php

namespace NativeRent\Common\Integration\API\Payloads;

final class MonetizationsPayload {
	use AuthorizedPayload;

	/**
	 * @param string $siteID Current site ID.
	 */
	public function __construct( $siteID ) {
		$this->siteID = $siteID;
	}
}
