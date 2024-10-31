<?php

namespace NativeRent\Core\View;

interface ViewInterface {
	/**
	 * Get view template path (without .php).
	 *
	 * @return string
	 */
	public function getTemplatePath();
}
