<?php

namespace NativeRent\Admin\Views;

use NativeRent\Common\Entities\AdUnitProps;
use NativeRent\Core\View\ViewInterface;

class PlacementSelectors implements ViewInterface {

	/** @var AdUnitProps */
	public $adUnitProps;

	/** @var string */
	public $baseFormsName;

	/** @var bool */
	public $disabled;

	/**
	 * @param  AdUnitProps $adUnitProps
	 * @param  string      $baseFormsName
	 * @param  bool        $disabled
	 */
	public function __construct( AdUnitProps $adUnitProps, $baseFormsName = '', $disabled = false ) {
		$this->adUnitProps   = $adUnitProps;
		$this->baseFormsName = $baseFormsName;
		$this->disabled = $disabled;
	}

	/**
	 * Getting a list of block placement settings.
	 *
	 * @return array[]
	 */
	public function adUnitSelectorOptions() {
		return [
			'firstParagraph'  => [
				'before' => __( 'первым абзацем (p)', 'nativerent' ),
				'after'  => __( 'первого абзаца (p)', 'nativerent' ),
			],
			'middleParagraph' => [
				'before' => __( 'средним абзацем (p)', 'nativerent' ),
				'after'  => __( 'среднего абзаца (p)', 'nativerent' ),
			],
			'lastParagraph'   => [
				'before' => __( 'последним абзацем (p)', 'nativerent' ),
				'after'  => __( 'последнего абзаца (p)', 'nativerent' ),
			],
			'firstTitle'      => [
				'before' => __( 'первым заголовком (h2)', 'nativerent' ),
				'after'  => __( 'первого заголовка (h2)', 'nativerent' ),
			],
			'middleTitle'     => [
				'before' => __( 'средним заголовком (h2)', 'nativerent' ),
				'after'  => __( 'среднего заголовка (h2)', 'nativerent' ),
			],
			'lastTitle'       => [
				'before' => __( 'последним заголовком (h2)', 'nativerent' ),
				'after'  => __( 'последнего заголовка (h2)', 'nativerent' ),
			],
			''                => [
				'before' => __( '(задать свой селектор)', 'nativerent' ),
				'after'  => __( '(задать свой селектор)', 'nativerent' ),
			],
		];
	}

	/**
	 * @return array{before: string, after: string}
	 */
	public function adUnitInsertOptions() {
		return [
			'before' => __( 'Перед', 'nativerent' ),
			'after'  => __( 'После', 'nativerent' ),
		];
	}

	/**
	 * @inheritDoc
	 */
	public function getTemplatePath() {
		return 'admin/placement-selectors';
	}
}
