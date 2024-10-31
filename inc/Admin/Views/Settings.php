<?php

namespace NativeRent\Admin\Views;

use NativeRent\Common\Entities\AdUnitProps;
use NativeRent\Common\Entities\AdUnitsConfig;
use NativeRent\Common\Entities\Monetizations;
use NativeRent\Core\Container\Exceptions\DependencyNotFound;
use NativeRent\Core\View\Exceptions\TemplateNotFound;
use NativeRent\Core\View\ViewInterface;

use function nrentview_e;

class Settings implements ViewInterface {
	/** @var string */
	public $actionURL;

	/** @var bool */
	public $regularSettings;

	/** @var bool */
	public $ntgbSettings;

	/** @var AdUnitsConfig */
	public $adUnitsConfig;

	/** @var array<string, mixed> */
	public $labels;

	/** @var string */
	public $demoPageURL;

	/**
	 * @param  AdUnitsConfig $adUnitsConfig
	 * @param  Monetizations $monetizations
	 * @param  string        $demoPageURL
	 *
	 * @throws \Exception
	 */
	public function __construct(
		AdUnitsConfig $adUnitsConfig,
		Monetizations $monetizations,
		$demoPageURL
	) {
		$this->adUnitsConfig   = $adUnitsConfig;
		$this->regularSettings = ! $monetizations->isRegularRejected();
		$this->ntgbSettings    = ! $monetizations->isNtgbRejected();
		$this->demoPageURL     = $demoPageURL;
		$this->actionURL       = nrentroute( 'settings.update' )->path;
		$this->labels          = $this->labels();
	}

	/**
	 * Get regular ad-units list.
	 *
	 * @return array[]
	 */
	private function labels() {
		return [
			'horizontalTop'    => [
				'title'       => __( '1. Верхний блок', 'nativerent' ),
				'description' => __(
					'Должен быть виден пользователю при загрузке страницы без прокрутки экрана. Рекомендуем размещать блок в самом верху статьи: после заголовка, после анонса статьи или перед оглавлением.',
					'nativerent'
				),
			],
			'horizontalMiddle' => [
				'title'       => __( '2. Средний блок', 'nativerent' ),
				'description' => __( 'Рекомендуем размещать блок в центре статьи.', 'nativerent' ),
			],
			'horizontalBottom' => [
				'title'       => __( '3. Нижний блок', 'nativerent' ),
				'description' => __(
					'Рекомендуем размещать блок внизу статьи, лучше всего после последнего абзаца.',
					'nativerent'
				),
			],
			'popupTeaser'      => [
				'title'       => __( '4. Всплывающий блок', 'nativerent' ),
				'settings'    => [
					'desktopTeaser'    => __( 'Разрешить вывод тизера на десктопе', 'nativerent' ),
					'mobileTeaser'     => __( 'Разрешить вывод тизера на мобильных платформах', 'nativerent' ),
					'mobileFullscreen' => __( 'Разрешить вывод фулскрина на мобильных платформах', 'nativerent' ),
				],
				'description' => '',
			],
			'ntgb'             => [
				'title'       => __( 'НТГБ', 'nativerent' ),
				'description' => '',
				'settings'    => [
					'noInsertion' => __( 'Место используется для аукциона Яндекс', 'nativerent' ),
					'fallbackCode' => [
						'title'       => __( 'Вы можете добавить код-заглушку к НТГБ', 'nativerent' ),
						'description' => __(
							'Сохраните в этом поле HTML-код рекламного блока, который мы будем загружать на странице,
							когда нет рекламы Native Rent. Статистика показов этого кода не будет
							отображаться в статистике Native Rent.',
							'nativerent'
						),
					],
				],
			],
		];
	}

	/**
	 * @param  AdUnitProps $props
	 * @param  string      $formsName
	 * @param  bool        $disabled
	 *
	 * @return void
	 * @throws DependencyNotFound
	 * @throws TemplateNotFound
	 */
	public function showPlacementSelectors( AdUnitProps $props, $formsName, $disabled = false ) {
		// nrent[adUnitsConfig][{$type}][{$unitName}] .
		nrentview_e( new PlacementSelectors( $props, $formsName, $disabled ) );
	}

	/**
	 * @param  bool $ntgb
	 *
	 * @return void
	 * @throws DependencyNotFound
	 * @throws TemplateNotFound
	 */
	public function showDemoUnitsPrompt( $ntgb = false ) {
		nrentview_e( new PromptLayout( new PromptDemoUnits( $this->demoPageURL, $ntgb ) ) );
	}

	/**
	 * {@inheritDoc}
	 */
	public function getTemplatePath() {
		return 'admin/settings';
	}
}
