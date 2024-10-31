<?php

namespace NativeRent\Admin\Views;

class PromptDemoUnits extends Prompt {

	/** @var string */
	public $pageURL;

	/** @var bool */
	public $ntgb;

	/**
	 * @param  string $pageURL
	 * @param  bool   $ntgb
	 */
	public function __construct( $pageURL, $ntgb = false ) {
		$this->pageURL = $pageURL;
		$this->ntgb    = $ntgb;
	}

	/**
	 * {@inheritDoc}
	 */
	public function getTemplatePath() {
		return 'admin/prompts/demo-units';
	}

	/**
	 * {@inheritDoc}
	 */
	public function getPromptType() {
		return self::TYPE_INFO;
	}
}
