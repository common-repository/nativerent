<?php

namespace NativeRent\Frontend\Integration;

interface PipelineInterface {
	/**
	 * Pipeline handler.
	 *
	 * @param  string $content
	 *
	 * @return string
	 */
	public function __invoke( $content );
}
