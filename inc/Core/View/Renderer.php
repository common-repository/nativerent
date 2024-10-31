<?php

namespace NativeRent\Core\View;

use NativeRent\Core\View\Exceptions\TemplateNotFound;

use function is_readable;
use function ob_get_clean;
use function ob_start;

/**
 * This class renders views based on their template.
 */
class Renderer {
	/**
	 * Base templates directory path.
	 *
	 * @var string
	 */
	private $templatesDirPath;

	/**
	 * @param  string $templatesDirPath  Base path to templates.
	 */
	public function __construct( $templatesDirPath ) {
		$this->templatesDirPath = $templatesDirPath;
	}

	/**
	 * Get and check full template path.
	 *
	 * @param  string $path  Path to template.
	 *
	 * @return string Full path to template.
	 * @throws TemplateNotFound
	 */
	private function checkAndGetFullTemplatePath( $path ) {
		$path = ( $this->templatesDirPath . DIRECTORY_SEPARATOR . $path . '.php' );
		if ( ! is_readable( $path ) ) {
			throw new TemplateNotFound(
				'Template ' . json_encode( $path, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE ) . ' not found'
			);
		}

		return $path;
	}

	/**
	 * Render view template.
	 *
	 * @param  ViewInterface $view  View instance.
	 *
	 * @return string Rendered content.
	 * @throws TemplateNotFound
	 */
	public function render( ViewInterface $view ) {
		$fullPath = $this->checkAndGetFullTemplatePath( $view->getTemplatePath() );
		ob_start();
		include $fullPath;

		return ob_get_clean();
	}

	/**
	 * Render and display view.
	 *
	 * @param  ViewInterface $view
	 *
	 * @return void
	 * @throws TemplateNotFound
	 */
	public function display( ViewInterface $view ) {
		echo $this->render( $view ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}
}
