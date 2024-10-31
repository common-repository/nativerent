<?php

namespace NativeRent\Frontend\Head;

use InvalidArgumentException;
use NativeRent\Common\Entities\AdUnitProps;
use NativeRent\Common\Entities\AdUnitsConfigNTGB;
use NativeRent\Common\Entities\AdUnitsConfigRegular;
use NativeRent\Common\Entities\StateOptions;
use WP_Rocket\Plugin;

class HeadTemplate {
	/**
	 * Path to nativerent JS file
	 *
	 * @var string
	 */
	private static $defaultJsPath = '/js/codes/nativerent.v2.js';

	/**
	 * Path to NTGB script.
	 *
	 * @var string
	 */
	private static $ntgbJsPath = '/js/codes/ntgb.v1.js';

	/**
	 * Head Integration Mark
	 *
	 * @var string
	 */
	private static $integrationClass = 'nativerent-integration-head';

	/** @var string */
	private $scriptsHost;

	/** @var StateOptions $stateOptions */
	private $stateOptions;

	/** @var string */
	private $pathToPluginScript;

	/**
	 * Cached additional attrs for integrated script tags.
	 *
	 * @var string|null
	 */
	private $additionalScriptAttrs = null;

	/**
	 * Seraphinite Accelerator JS optimizer flag.
	 *
	 * @var bool|null
	 */
	private $seraphAccelDelayJS = null;

	/**
	 * @param  string       $scriptsHost         Host of Native Rent scripts.
	 * @param  string       $pathToPluginScript  Path to plugin script.
	 * @param  StateOptions $stateOptions
	 *
	 * @throws InvalidArgumentException
	 */
	public function __construct( $scriptsHost, $pathToPluginScript, StateOptions $stateOptions ) {
		if ( ! is_string( $scriptsHost ) ) {
			throw new InvalidArgumentException( 'The `scriptsHost` argument must be a string' );
		}
		if ( ! is_string( $pathToPluginScript ) ) {
			throw new InvalidArgumentException( 'The `pathToPluginScript` argument must be a string' );
		}
		$this->scriptsHost        = $scriptsHost;
		$this->pathToPluginScript = $pathToPluginScript;
		$this->stateOptions       = $stateOptions;
	}

	/**
	 * Getting additional attributes for script tags.
	 *
	 * @note data-no-optimize="1" - especially for Lightspeed Cache plugin and WP Rocket.
	 * @note data-skip-moving="true" | data-wpfc-render="false" - WP Fastest Cache optimization skip.
	 * @note seraph-accel-crit="1" - Seraphinite Accelerator skip.
	 * @note data-cfasync="false" - Cloudflare RocketLoader skip.
	 *
	 * @return string
	 */
	private function getAdditionalScriptAttributes() {
		if ( is_string( $this->additionalScriptAttrs ) ) {
			return $this->additionalScriptAttrs;
		}
		$attrs = 'data-no-optimize="1" data-skip-moving="true"';

		// WP Fastest Cache Render Blocking.
		if ( isset( $GLOBALS['wp_fastest_cache_options']->wpFastestCacheRenderBlocking ) ) {
			$attrs .= ' data-wpfc-render="false"';
		}

		// Seraphinite Accelerator JS optimization.
		if ( $this->seraphAccelEnabled() ) {
			$attrs .= ' seraph-accel-crit="1"';
		}

		$this->additionalScriptAttrs = $attrs;

		return $attrs;
	}

	/**
	 * @return string
	 */
	public function getRegularScriptPath() {
		return $this->scriptsHost . self::$defaultJsPath;
	}

	/**
	 * @return string
	 */
	public function getNtgbScriptPath() {
		return $this->scriptsHost . self::$ntgbJsPath;
	}

	/**
	 * Render template.
	 *
	 * @return string
	 */
	public function render() {
		$integrationCode = '';

		if ( ! $this->stateOptions->monetizations->isRegularRejected() ) {
			// NOTE: the script will automatically download NTGB if needed.
			$integrationCode .= $this->getRegularHeadTemplate();
			$this->addPreloadHeader( $this->getRegularScriptPath() );
		} elseif ( ! $this->stateOptions->monetizations->isNtgbRejected() ) {
			// Only NTGB script.
			$integrationCode .= $this->getNtgbHeadTemplate();
			$this->addPreloadHeader( $this->getNtgbScriptPath() );
		}

		// NRentCounter init script.
		$integrationCode .= $this->getCounterInitTemplate();

		// Ad-units arrangement configuration.
		$integrationCode .= $this->renderAdUnitsConfig();

		// Plugin script for arrangement blocks and unblocking other adv scripts.
		$integrationCode .= $this->getPluginJsTemplate();

		return $this->templateWrapper( $integrationCode );
	}

	/**
	 * Get template for regular NR head integration.
	 *
	 * @return string
	 */
	private function getRegularHeadTemplate() {
		$tmpl  = '';
		$jsURL = $this->getRegularScriptPath();

		// Additional script for working with WP Rocket delay JS.
		if ( class_exists( Plugin::class ) ) {
			$tmpl .= $this->wpRocketLazyLoadFixTemplate();
		}
		if ( $this->seraphAccelEnabled() ) {
			$tmpl .= $this->seraphAccelFixTemplate();
		}

		// Preload tag.
		$tmpl .= sprintf(
			'<link rel="preload" as="script" href="%s" class="%s" crossorigin />',
			$jsURL,
			self::$integrationClass
		);

		// Main script.
		$tmpl .= sprintf(
			'<script class="%s" src="%s" onerror="%s" %s async crossorigin></script>', //phpcs:ignore
			self::$integrationClass,
			$jsURL,
			/** @lang JavaScript */
			'(window.NRentPlugin=window.NRentPlugin||[]).push(\'error_loading_script\')',
			self::getAdditionalScriptAttributes()
		);

		return $tmpl;
	}

	/**
	 * Get template for NTGB head integration.
	 *
	 * @return string
	 */
	private function getNtgbHeadTemplate() {
		$tpml  = '';
		$jsURL = self::getNtgbScriptPath();

		// Preload tag.
		$tpml .= sprintf(
			'<link rel="preload" as="script" href="%s" class="%s" crossorigin />',
			$jsURL,
			self::$integrationClass
		);

		// NTGB script.
		$tpml .= sprintf(
			'<script class="%s" src="%s" %s async crossorigin></script>', //phpcs:ignore
			self::$integrationClass,
			$jsURL,
			self::getAdditionalScriptAttributes()
		);

		return $tpml;
	}

	/**
	 * Get NR counter init template.
	 *
	 * @return string
	 */
	private function getCounterInitTemplate() {
		return sprintf(
			'<script class="%s" type="text/javascript" %s>' .
			'(window.NRentCounter=window.NRentCounter||[]).push({id:"%s",lightMode:%s,created:%d})' .
			'</script>',
			self::$integrationClass,
			self::getAdditionalScriptAttributes(),
			$this->stateOptions->siteID,
			'undefined',
			time()
		);
	}

	/**
	 * Template of `content.js` integration.
	 *
	 * @return string
	 */
	public function getPluginJsTemplate() {
		return sprintf(
			'<script class="%s" src="%s" %s defer></script>', //phpcs:ignore
			self::$integrationClass,
			$this->pathToPluginScript,
			self::getAdditionalScriptAttributes()
		);
	}

	/**
	 * Wrapper for rendered template.
	 *
	 * @param  string $tmpl  Complete template.
	 *
	 * @return string
	 */
	private function templateWrapper( $tmpl ) {
		return '<!--noptimize-->' . str_replace( [ "\n", "\t" ], '', $tmpl ) . '<!--/noptimize-->';
	}

	/**
	 * Render ad-units config.
	 *
	 * @return string
	 */
	private function renderAdUnitsConfig() {
		$adUnits = [];
		if ( ! $this->stateOptions->monetizations->isRegularRejected() ) {
			$adUnits = array_merge(
				$adUnits,
				$this->mapRegularUnitsConfig( $this->stateOptions->adUnitsConfig->regular )
			);
		}
		if ( ! $this->stateOptions->monetizations->isNtgbRejected() ) {
			$adUnits = array_merge( $adUnits, $this->mapNtgbUnitsConfig( $this->stateOptions->adUnitsConfig->ntgb ) );
		}

		return sprintf(
			'<script class="%s" %s>' .
			'!0!==window.NRentAdUnitsLoaded&&(window.NRentAdUnitsLoaded=!0,window.NRentAdUnits=%s)' .
			'</script>',
			self::$integrationClass,
			self::getAdditionalScriptAttributes(),
			json_encode( $adUnits )
		);
	}

	/**
	 * @param  AdUnitsConfigRegular $config
	 *
	 * @return array
	 */
	private function mapRegularUnitsConfig( AdUnitsConfigRegular $config ) {
		$units = [];

		/**
		 * @var string      $type
		 * @var AdUnitProps $props
		 */
		foreach ( $config as $type => $props ) {
			$adUnit = $this->mapAdUnitProps( $type, $props );
			if ( 'popupTeaser' === $type ) {
				$adUnit['insert']             = 'inside';
				$adUnit['autoSelector']       = 'body';
				$adUnit['settings']['mobile'] = [];
				if ( ! empty( $props->settings['mobileTeaser'] ) ) {
					$adUnit['settings']['mobile'][] = 'teaser';
				}
				if ( ! empty( $props->settings['mobileFullscreen'] ) ) {
					$adUnit['settings']['mobile'][] = 'fullscreen';
				}
				$adUnit['settings']['desktop'] = [];
				if ( ! empty( $props->settings['desktopTeaser'] ) ) {
					$adUnit['settings']['desktop'][] = 'teaser';
				}
			}
			$units[] = $adUnit;
		}

		return $units;
	}

	/**
	 * @param  AdUnitsConfigNTGB $config
	 *
	 * @return array
	 */
	private function mapNtgbUnitsConfig( AdUnitsConfigNTGB $config ) {
		$units = [];

		/**
		 * @var string      $name
		 * @var AdUnitProps $props
		 */
		foreach ( $config->getIterable() as $name => $props ) {
			if ( ! empty( $props->settings['inactive'] ) || ! empty( $props->settings['noInsertion'] ) ) {
				continue;
			}
			$adUnit           = $this->mapAdUnitProps( 'ntgb', $props );
			$adUnit['unitId'] = (string) $name;
			$units[]          = $adUnit;
		}

		return $units;
	}

	/**
	 * @param  string      $type
	 * @param  AdUnitProps $props
	 *
	 * @return array
	 */
	private function mapAdUnitProps( $type, AdUnitProps $props ) {
		$adUnit                 = [];
		$adUnit['type']         = $type;
		$adUnit['insert']       = $props->insert;
		$adUnit['autoSelector'] = rawurlencode( $props->autoSelector );
		$adUnit['selector']     = rawurlencode( $props->customSelector );
		$adUnit['settings']     = [];

		return $adUnit;
	}

	/**
	 * Add `Link` header for preloading script.
	 *
	 * @param string $scriptUrl
	 *
	 * @return void
	 */
	private function addPreloadHeader( $scriptUrl ) {
		if ( empty( $scriptUrl ) || headers_sent() ) {
			return;
		}

		header( 'Link: <' . $scriptUrl . '>; rel=preload; as=script; crossorigin', false );
	}


	/**
	 * Additional script for working with WP Rocket delay JS execution.
	 *
	 * @see https://docs.wp-rocket.me/article/1349-delay-javascript-execution
	 *
	 * @return string
	 */
	private function wpRocketLazyLoadFixTemplate() {
		/** @lang HTML */
		return <<<TEMPLATE
		<script class="{$this::$integrationClass}" type="text/javascript" {$this->getAdditionalScriptAttributes()}>
		Array.from(["keydown","mousedown","mousemove","touchmove","touchstart","touchend","wheel","rocket-DOMContentLoaded"]).forEach(function(e){window.addEventListener(e,function(){window.NRentRocketDOMContentLoaded=!0},{once:!0})});
		window.NRentPluginUnblockHandler = function (el) {
			if (window.NRentRocketDOMContentLoaded === true && el.nodeType === 1 && el.tagName === "SCRIPT" && el.getAttribute("type") === 'rocketlazyloadscript') {
				el.removeAttribute("type");
				var src = "data-rocket-src";
				if (el.hasAttribute(src)) {
					el.setAttribute("src", el.getAttribute(src));
					el.removeAttribute(src);
				}
			}
			return el;
		}
		</script>
TEMPLATE;
	}

	/**
	 * Additional script for working with Seraphinite Accelerator scripts optimizations.
	 *
	 * @see https://www.s-sols.com/ru/docs/wordpress/accelerator/settings-accel/settings-scripts-accel
	 *
	 * @return string
	 */
	private function seraphAccelFixTemplate() {
		/** @lang HTML */
		return <<<TEMPLATE
		<script class="{$this::$integrationClass}" type="text/javascript" {$this->getAdditionalScriptAttributes()}>
		Array.from(["scroll", "wheel", "mouseenter", "mousemove", "mouseover", "keydown", "click", "touchmove", "touchend", "seraph_accel_jsFinish"]).forEach(function(e){window.addEventListener(e,function(){window.NRentSeraphAccelJsFinish=!0},{once:!0})});
		window.NRentPluginUnblockHandler = function (el) {
			if (el.nodeType === 1 && el.tagName === "SCRIPT") {
				var dt = "data-type", jslzl = "o/js-lzl";
				if (window.NRentSeraphAccelJsFinish !== true) {
					if (el.hasAttribute("type") && !el.hasAttribute(dt)) {
						el.setAttribute(dt, el.getAttribute("type"));
					}
					el.setAttribute("type", jslzl);
				} 
				else if (el.hasAttribute("type") && Array.from([jslzl,jslzl+"s"]).indexOf(el.getAttribute("type")) >= 0) {
					if (el.hasAttribute(dt)) {
						el.setAttribute("type", el.getAttribute(dt));
						el.removeAttribute(dt);
					} else {
						el.removeAttribute("type");
					}
				}
			}
		};
</script>
TEMPLATE;
	}

	/**
	 * Checking Seraphinite Accelerator JS optimization.
	 *
	 * @return bool|null
	 */
	private function seraphAccelEnabled() {
		if ( is_null( $this->seraphAccelDelayJS ) ) {
			$this->seraphAccelDelayJS = (
				class_exists( \seraph_accel\Plugin::class ) &&
				class_exists( \seraph_accel\Gen::class ) &&
				method_exists( \seraph_accel\Plugin::class, 'SettGet' ) &&
				method_exists( \seraph_accel\Gen::class, 'GetArrField' ) &&
				\seraph_accel\Gen::GetArrField( \seraph_accel\Plugin::SettGet(), [ 'contPr', 'js', 'optLoad' ], false )
			);
		}

		return $this->seraphAccelDelayJS;
	}

	/**
	 * Convert to string.
	 *
	 * @return string
	 */
	public function __toString() {
		return $this->render();
	}
}
