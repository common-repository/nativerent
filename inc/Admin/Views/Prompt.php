<?php

namespace NativeRent\Admin\Views;

use NativeRent\Core\View\ViewInterface;

abstract class Prompt implements ViewInterface {
	const TYPE_INFO = 'info';
	const TYPE_WARNING = 'warning';

	/** @return string */
	abstract public function getPromptType();

	/**
	 * Prompt title (optional).
	 *
	 * @return string|null
	 */
	public function getTitle() {
		return null;
	}
}
