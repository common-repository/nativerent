<?php

namespace NativeRent\Admin;

use Exception;
use NativeRent\Admin\Events\SettingsUpdated;
use NativeRent\Admin\Requests\Auth;
use NativeRent\Admin\Requests\ClearCache;
use NativeRent\Admin\Requests\UpdateSettings;
use NativeRent\Admin\Views\AuthForm;
use NativeRent\Admin\Views\Layout;
use NativeRent\Admin\Views\PromptLayout;
use NativeRent\Admin\Views\PromptSiteRejected;
use NativeRent\Admin\Views\Settings;
use NativeRent\Common\Articles\RepositoryInterface;
use NativeRent\Common\NRentService;
use NativeRent\Common\Options;
use NativeRent\Core\Container\Exceptions\DependencyNotFound;
use NativeRent\Core\Events\DispatcherInterface;
use NativeRent\Core\View\Renderer;
use NativeRent\Core\View\ViewInterface;

use function add_action;
use function count;
use function nrentapp;
use function nrentroute;
use function rand;
use function wp_safe_redirect;
use function wpnrent_get_domain;

/**
 * Admin HTTP controller.
 */
class Controller {

	const ACTION_RENDER_VIEW = '_nrent_render_view';

	/** @var Options */
	private $options;

	/** @var DispatcherInterface */
	private $events;

	/** @var Session */
	private $session;

	/**
	 * @throws DependencyNotFound
	 */
	public function __construct() {
		$this->options = nrentapp( Options::class );
		$this->events  = nrentapp( DispatcherInterface::class );
		$this->session = Session::init();
	}

	/**
	 * Plugin homepage.
	 *
	 * @return void
	 * @throws Exception
	 */
	public function showSettings() {
		if ( ! $this->isAuthorized() ) {
			$this->redirectToRoute( 'auth.form' );

			return;
		}

		// Getting and checking moderation statuses.
		$moderationStatus = $this->options->getSiteModerationStatus();
		$monetizations    = $this->options->getMonetizations();
		if ( $moderationStatus->isRejected() || $monetizations->isAllRejected() ) {
			$this->displayView( new Layout( new PromptLayout( new PromptSiteRejected() ) ) );

			return;
		}

		// Getting random post permalink.
		$lastPosts       = nrentapp( RepositoryInterface::class )->getPublishedArticles( 1, 10 );
		$randomPermalink = '';
		if ( ! empty( $lastPosts ) ) {
			$randomPermalink = $lastPosts[ rand( 0, count( $lastPosts ) - 1 ) ]->permalink;
		}

		$this->displayView(
			new Layout(
				new Settings(
					$this->options->getAdUnitsConfig(),
					$this->options->getMonetizations(),
					$randomPermalink
				),
				true
			)
		);
	}

	/**
	 * Updating settings.
	 *
	 * @return void
	 * @throws Exception
	 */
	public function updateSettings() {
		if ( ! $this->isAuthorized() ) {
			$this->redirectToRoute( 'auth.form' );

			return;
		}

		$request = new UpdateSettings();
		if ( ! empty( $request->adUnitsConfig ) ) {
			if ( $this->options->setAdUnitsConfig( $request->adUnitsConfig ) ) {
				$this->events->dispatch( new SettingsUpdated() );
			}
		}

		$this->redirectToRoute( 'settings.show' );
	}

	/**
	 * Show auth form.
	 *
	 * @return void
	 * @throws Exception
	 */
	public function showAuthForm() {
		$this->displayView(
			new Layout(
				new AuthForm(
					$this->session->get( 'login', '' ),
					$this->session->get( 'errors', [] )
				)
			)
		);
	}

	/**
	 * Auth action.
	 *
	 * @return void
	 * @throws Exception
	 */
	public function auth() {
		$request = new Auth();
		$service = nrentapp( NRentService::class );
		$res     = $service->authorize(
			wpnrent_get_domain(),
			$request->login,
			$request->getPassword()
		);
		if ( $res['success'] ) {
			$this->options->setClearCacheFlag( 1 );
			$this->redirectToRoute( 'settings.show' );

			return;
		} elseif ( ! empty( $res['errors'] ) ) {
			$this->session->add( 'login', $request->login );
			$this->session->add( 'errors', $res['errors'] );
		}

		$this->redirectToRoute( 'auth.form' );
	}

	/**
	 * Logout and disintegration.
	 *
	 * @return void
	 * @throws DependencyNotFound
	 * @throws Exception
	 */
	public function logout() {
		nrentapp( NRentService::class )->logout();
		$this->options->setClearCacheFlag( 3 );
		$this->redirectToRoute( 'auth.form' );
	}

	/**
	 * Clear cache action.
	 *
	 * @return void
	 * @throws Exception
	 */
	public function resetCacheFlag() {
		$request = new ClearCache();
		if ( $request->needToClear ) {
			SiteCache::clearCache();
		}

		$this->options->setClearCacheFlag( 0 );
		$this->redirectToRoute( 'settings.show' );
	}

	/**
	 * @return bool
	 */
	private function isAuthorized() {
		return ! empty( $this->options->getSiteID() ) && ! empty( $this->options->getIntegrationAccessToken() );
	}

	/**
	 * Redirect by route name.
	 *
	 * @param  string $name    Route name.
	 * @param  int    $status  Redirect with status.
	 *
	 * @return void
	 * @throws Exception
	 */
	private function redirectToRoute( $name, $status = 302 ) {
		$this->redirect( nrentroute( $name )->path, $status );
	}

	/**
	 * Show view
	 *
	 * @param  ViewInterface $view  View instance.
	 *
	 * @return void
	 * @throws Exception
	 */
	private function displayView( ViewInterface $view ) {
		add_action(
			self::ACTION_RENDER_VIEW,
			function () use ( $view ) {
				nrentapp( Renderer::class )->display( $view );
			}
		);
	}

	/**
	 * Redirect.
	 *
	 * @param  string $location  Redirect to URL.
	 * @param  int    $status    Redirect status.
	 *
	 * @return void
	 */
	private function redirect( $location, $status = 302 ) {
		wp_safe_redirect( $location, $status );
		exit();
	}
}
