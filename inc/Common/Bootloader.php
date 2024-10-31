<?php

namespace NativeRent\Common;

use Closure;
use NativeRent\Common\Articles\RepositoryInterface as ArticlesRepository;
use NativeRent\Common\Articles\WpPostsRepository;
use NativeRent\Common\Cron\Tasks\UpdateMonetizations;
use NativeRent\Common\Cron\WpCronTasksRegistry;
use NativeRent\Common\Events\OptionUpdated;
use NativeRent\Common\Events\PluginActivated;
use NativeRent\Common\Events\PluginDeactivated;
use NativeRent\Common\Events\PluginUninstalled;
use NativeRent\Common\Events\PluginVersionChanged;
use NativeRent\Common\Integration\API\WpClient;
use NativeRent\Common\Listeners\PluginActivated\NotifyAboutActivation;
use NativeRent\Common\Listeners\PluginDeactivated\NotifyAboutDeactivation;
use NativeRent\Common\Listeners\PluginDeactivated\UnregisterCronTasks;
use NativeRent\Common\Listeners\PluginUninstalled\Logout;
use NativeRent\Common\Listeners\SendState;
use NativeRent\Common\Listeners\SetupClearCacheFlag;
use NativeRent\Common\Migrations\V0;
use NativeRent\Common\Migrations\V170;
use NativeRent\Common\Migrations\V180;
use NativeRent\Common\Migrations\V185;
use NativeRent\Core\Container\Exceptions\DependencyNotFound;
use NativeRent\Core\Cron\CronManager;
use NativeRent\Core\Cron\Task;
use NativeRent\Core\Cron\TaskInterval;
use NativeRent\Core\Events\DispatcherInterface;
use NativeRent\Core\Events\ListenerInterface;
use NativeRent\Core\Events\ListenersRegistryInterface;
use NativeRent\Core\Events\WpEventBus;
use NativeRent\Core\Migration\Migrator;
use NativeRent\Core\Options\WpOptionStorage;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;

use function esc_url;
use function getenv;
use function wp_unslash;

final class Bootloader extends AbstractBootloader {
	/**
	 * Cron-tasks definition.
	 *
	 * @return array<array-key, array{int|TaskInterval, class-string<Closure>, string} [<interval>, <handler>, <name>]
	 */
	private static function cronTasks() {
		return [
			[ NATIVERENT_UPDATE_MONETIZATIONS_INTERVAL, UpdateMonetizations::class, 'cron_update_monetizations' ],
		];
	}

	/**
	 * Event listeners definition.
	 *
	 * @return array<string, ListenerInterface[]>
	 */
	private static function eventListeners() {
		return [
			PluginUninstalled::getEventName() => [ Logout::class ],
			PluginActivated::getEventName() => [ NotifyAboutActivation::class, SetupClearCacheFlag::class ],
			PluginDeactivated::getEventName() => [ NotifyAboutDeactivation::class, UnregisterCronTasks::class ],
			OptionUpdated::getEventName() => [ SetupClearCacheFlag::class ],
			PluginVersionChanged::getEventName() => [ SetupClearCacheFlag::class, SendState::class ],
		];
	}

	/**
	 * {@inheritDoc}
	 *
	 * @throws ContainerExceptionInterface
	 * @throws DependencyNotFound
	 * @throws NotFoundExceptionInterface
	 */
	public function onRegister() {
		$this->container
			->bind(
				ArticlesRepository::class,
				function () {
					return new WpPostsRepository();
				}
			)
			->bind(
				DispatcherInterface::class,
				function ( ContainerInterface $c ) {
					return new WpEventBus(
						'nativerent_',
						function ( $listenerClass ) use ( $c ) {
							return $c->get( $listenerClass );
						}
					);
				}
			)->bind(
				ListenersRegistryInterface::class,
				function ( ContainerInterface $c ) {
					return $c->get( DispatcherInterface::class );
				}
			)
			->bind(
				Options::class,
				function ( ContainerInterface $c ) {
					return new Options(
						new WpOptionStorage(),
						new OptionsObserver( $c->get( DispatcherInterface::class ) )
					);
				}
			)
			->bind(
				NRentService::class,
				function ( ContainerInterface $c ) {
					$env_host = getenv( 'NATIVERENT_API_HOST' );
					$options  = $c->get( Options::class );
					$client   = new WpClient(
						! empty( $env_host ) ? esc_url( wp_unslash( $env_host ) ) : 'http://plain.nativerent.ru',
						$options->getIntegrationAccessToken()
					);

					return new NRentService( $client, $options );
				}
			)
			->bind(
				CronManager::class,
				function () {
					return new CronManager( new WpCronTasksRegistry( 'ntrnt' ) );
				}
			);

		$this
			->initEvents()
			->initHooks();

		if ( ! defined( 'NATIVERENT_UNINSTALL' ) ) {
			$this
				->initMigrations()
				->initCronTasks();
		}
	}

	/**
	 * Register migrations.
	 *
	 * @return $this
	 * @throws ContainerExceptionInterface
	 * @throws DependencyNotFound
	 * @throws NotFoundExceptionInterface
	 */
	protected function initMigrations() {
		add_action(
			'init',
			function () {
				$options = $this->container->get( Options::class );
				$fromVersion = $options->getPluginVersion();

				/**
				 * In older versions of the plugin (below 1.6), the version value was not saved in the database,
				 * so we set it to 0 by default to start a zero migration.
				 */
				$fromVersion = empty( $fromVersion ) ? '0' : $fromVersion;
				if ( NATIVERENT_PLUGIN_VERSION !== $fromVersion ) {
					$migrations = ( '0' === $fromVersion
						? [ new V0(), new V185() ]
						: [ new V170(), new V180(), new V185() ]
					);
					$migrator = new Migrator( $migrations );
					$migrator->run( $fromVersion, NATIVERENT_PLUGIN_VERSION );
					$options->setPluginVersion( NATIVERENT_PLUGIN_VERSION );
					$this->container->get( DispatcherInterface::class )->dispatch(
						new PluginVersionChanged( NATIVERENT_PLUGIN_VERSION, $fromVersion )
					);
				}
			}
		);

		return $this;
	}

	/**
	 * @throws DependencyNotFound
	 * @throws ContainerExceptionInterface
	 * @throws NotFoundExceptionInterface
	 */
	private function initCronTasks() {
		$this->container->bind(
			UpdateMonetizations::class,
			function () {
				return new UpdateMonetizations( $this->container->get( NRentService::class ) );
			}
		);

		$manager = $this->container->get( CronManager::class );
		foreach ( self::cronTasks() as $taskArgs ) {
			$manager->scheduleTask(
				new Task(
					$taskArgs[0] instanceof TaskInterval ? $taskArgs[0] : new TaskInterval( (int) $taskArgs[0] ),
					$this->container->get( $taskArgs[1] ),
					! empty( $taskArgs[2] ) ? $taskArgs[2] : null
				)
			);
		}
	}

	/**
	 * Register events and listeners.
	 *
	 * @return $this
	 * @throws DependencyNotFound
	 */
	private function initEvents() {
		// Init listeners.
		$this->container
			->bind(
				Logout::class,
				function ( ContainerInterface $c ) {
					return new Logout( $c->get( NRentService::class ) );
				}
			)
			->bind(
				NotifyAboutActivation::class,
				function ( ContainerInterface $c ) {
					return new NotifyAboutActivation( $c->get( NRentService::class ) );
				}
			)
			->bind(
				NotifyAboutDeactivation::class,
				function ( ContainerInterface $c ) {
					return new NotifyAboutDeactivation( $c->get( NRentService::class ) );
				}
			)
			->bind(
				UnregisterCronTasks::class,
				function ( ContainerInterface $c ) {
					return new UnregisterCronTasks( $c->get( CronManager::class ) );
				}
			)
			->bind(
				SetupClearCacheFlag::class,
				function ( ContainerInterface $c ) {
					return new SetupClearCacheFlag( $c->get( Options::class ) );
				}
			)
			->bind(
				SendState::class,
				function ( ContainerInterface $c ) {
					return new SendState( $c->get( NRentService::class ) );
				}
			);

		// Subscribing listeners to events.
		$registry = $this->container->get( ListenersRegistryInterface::class );
		foreach ( self::eventListeners() as $event => $listeners ) {
			$registry->addListeners( $event, $listeners );
		}

		return $this;
	}

	/**
	 * Plugin hooks registration.
	 *
	 * @return self
	 * @throws DependencyNotFound
	 */
	private function initHooks() {
		register_activation_hook(
			NATIVERENT_PLUGIN_FILE,
			function () {
				$this->container->get( DispatcherInterface::class )->dispatch( new PluginActivated() );
			}
		);
		register_deactivation_hook(
			NATIVERENT_PLUGIN_FILE,
			function () {
				$this->container->get( DispatcherInterface::class )->dispatch( new PluginDeactivated() );
			}
		);

		return $this;
	}
}
