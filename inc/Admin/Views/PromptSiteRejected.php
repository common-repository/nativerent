<?php

namespace NativeRent\Admin\Views;

class PromptSiteRejected extends Prompt {
	/**
	 * {@inheritDoc}
	 */
	public function getTemplatePath() {
		return 'admin/prompts/site-rejected';
	}

	public function getTitle() {
		return __( 'Сайт отклонен модератором, монетизация отключена', 'nativerent' );
	}

	/**
	 * {@inheritDoc}
	 */
	public function getPromptType() {
		return self::TYPE_WARNING;
	}
}
