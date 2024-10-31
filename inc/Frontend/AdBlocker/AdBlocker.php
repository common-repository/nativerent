<?php

namespace NativeRent\Frontend\AdBlocker;

use Exception;

/**
 * Blocking third-party advertising.
 */
class AdBlocker {

	/** @var string[] */
	private $patterns;

	/** @var ExtensionInterface[] */
	private $extensions;

	/**
	 * @param  string[]             $patterns
	 * @param  ExtensionInterface[] $extensions
	 */
	public function __construct( $patterns, $extensions = [] ) {
		$this->patterns   = is_array( $patterns ) ? $patterns : [];
		$this->extensions = is_array( $extensions )
			? array_filter(
				$extensions,
				function ( $i ) {
					return ( $i instanceof ExtensionInterface );
				}
			)
			: [];
	}

	/**
	 * Block adv by patterns.
	 *
	 * @param  string                $content        HTML content.
	 * @param  callable(): bool|null $stopCondition  Stop condition callback.
	 *
	 * @return string Modified HTML.
	 */
	public function block( $content, $stopCondition = null ) {
		if ( ! is_array( $this->patterns ) ) {
			return $content;
		}

		foreach ( $this->patterns as $pattern ) {
			$modified = @preg_replace_callback( $pattern, [ $this, 'replaceHandler' ], $content );
			$content  = ! empty( $modified ) ? $modified : $content;
			if ( is_callable( $stopCondition ) && $stopCondition() ) {
				break;
			}
		}

		return $content;
	}

	/**
	 * Callback for `preg_replace_callback`.
	 *
	 * @param  array $matches  Matches of `preg_replace_callback`.
	 *
	 * @return string
	 */
	protected function replaceHandler( $matches ) {
		$match = $matches[0];
		foreach ( $this->extensions as $ext ) {
			try {
				$res = $ext->matchHandler( $match );
				if ( ! empty( $res ) ) {
					$match = $res;
				}
			} catch ( Exception $e ) {
				continue;
			}
		}
		if ( empty( $match ) ) {
			$match = $matches[0];
		}

		return '<meta property="nativerent-block" class="nRent_block_ce40f5ef6e84e162" content="' .
			   base64_encode( $match ) . '"/>';
	}
}
