<?php

namespace NativeRent\Frontend\AdBlocker;

interface ExtensionInterface {
	/**
	 * A handler for each match found.
	 *
	 * @param  string $match  Found match by patterns.
	 *
	 * @return string
	 */
	public function matchHandler( $match );
}
