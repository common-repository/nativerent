<?php

namespace NativeRent\Common\Integration\API\Payloads;

trait AuthorizedPayload {
	/**
	 * @var string $siteID
	 */
	private $siteID;

	/**
	 * @return string
	 */
	public function getSiteID() {
		return $this->siteID;
	}
}
