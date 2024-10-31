<?php

namespace NativeRent\Frontend\Integration;

use NativeRent\Common\Entities\StateOptions;
use NativeRent\Frontend\Head\HeadTemplate;

class HeadIntegrationPipeline implements PipelineInterface {

	/** @var StateOptions */
	protected $stateOptions;

	/** @var HeadTemplate */
	protected $headTemplate;

	public function __construct(
		StateOptions $stateOptions,
		HeadTemplate $headTemplate
	) {
		$this->stateOptions = $stateOptions;
		$this->headTemplate = $headTemplate;
	}

	/** {@inheritDoc} */
	public function __invoke( $content ) {
		if ( empty( $this->stateOptions->siteID ) ) {
			return $content;
		}

		return preg_replace(
			'/<head(\s[^>]*|)>/i',
			'<head$1>' . PHP_EOL . $this->headTemplate->render(),
			$content,
			1
		);
	}
}
