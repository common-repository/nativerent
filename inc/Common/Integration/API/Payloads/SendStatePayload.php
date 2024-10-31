<?php

namespace NativeRent\Common\Integration\API\Payloads;

use NativeRent\Common\Entities\State;

final class SendStatePayload {
	use AuthorizedPayload;

	/**
	 * State instance.
	 *
	 * @var State
	 */
	private $state;

	/**
	 * @param string $siteID Current site ID.
	 * @param State  $state  State instance.
	 */
	public function __construct(
		$siteID,
		State $state
	) {
		$this->siteID = $siteID;
		$this->state  = $state;
	}

	/**
	 * @return State
	 */
	public function getState() {
		return $this->state;
	}
}
