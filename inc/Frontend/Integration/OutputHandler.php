<?php

namespace NativeRent\Frontend\Integration;

/**
 * The main handler of the page's output content.
 */
final class OutputHandler {
	/** @var PipelineInterface[] */
	protected $pipelines;

	/**
	 * @param  PipelineInterface[] $pipelines
	 */
	public function __construct( $pipelines = [] ) {
		$this->pipelines = array_filter(
			$pipelines,
			function ( $p ) {
				return $p instanceof PipelineInterface;
			}
		);
	}

	/**
	 * Content handler.
	 *
	 * @param  string $output  Raw content of page.
	 *
	 * @return string Processed content.
	 */
	public function __invoke( $output ) {
		foreach ( $this->pipelines as $pipeline ) {
			$output = $pipeline( $output );
		}

		return $output;
	}
}
