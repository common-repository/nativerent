<?php

namespace NativeRent\Common\Integration\API;

interface AuthorizedClientInterface {
	/**
	 * Set authentication token.
	 *
	 * @param  string $token  Access token.
	 *
	 * @return void
	 */
	public function setAuthenticationToken(
		#[\SensitiveParameter]
		$token
	);
}
