<?php

namespace NativeRent\Admin\Views;

use NativeRent\Core\View\ViewInterface;

class PromptLayout implements ViewInterface {

	/** @var Prompt */
	public $prompt;

	public function __construct( Prompt $prompt ) {
		$this->prompt = $prompt;
	}

	/**
	 * {@inheritDoc}
	 */
	public function getTemplatePath() {
		return 'admin/prompts/layout';
	}
}
