<?php

namespace NativeRent\Admin\Notices;

use InvalidArgumentException;
use NativeRent\Core\Notices\AbstractNotice;

class Notice extends AbstractNotice {

	/**
	 * @param  string               $content
	 * @param  string               $level
	 * @param  array<string, mixed> $options
	 *
	 * @throws InvalidArgumentException
	 */
	public function __construct( $content, $level = self::LEVEL_INFO, $options = [] ) {
		if ( ! is_string( $content ) ) {
			throw new InvalidArgumentException( 'Notice content must be string' );
		}
		if ( ! is_string( $level ) ) {
			throw new InvalidArgumentException( 'Notice level must be string' );
		}
		if ( ! is_array( $options ) ) {
			throw new InvalidArgumentException( 'Options must be array' );
		}

		$this->content = $content;
		$this->level   = $level;
		$this->options = $options;
	}
}
