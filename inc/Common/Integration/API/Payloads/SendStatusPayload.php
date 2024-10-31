<?php

namespace NativeRent\Common\Integration\API\Payloads;

use NativeRent\Common\Entities\IntegrationStatus;

final class SendStatusPayload {
	use AuthorizedPayload;

	/**
	 * State instance.
	 *
	 * @var IntegrationStatus
	 */
	private $status;

	/**
	 * @param string            $siteID Current site ID.
	 * @param IntegrationStatus $status  Integration status.
	 */
	public function __construct(
		$siteID,
		IntegrationStatus $status
	) {
		$this->siteID = $siteID;
		$this->status = $status;
	}

	/**
	 * @return IntegrationStatus
	 */
	public function getStatus() {
		return $this->status;
	}

	/**
	 * @return string
	 */
	public function getVersion() {
		return NATIVERENT_PLUGIN_VERSION;
	}
}
