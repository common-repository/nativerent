<?php

namespace NativeRent\Core\Container;

use InvalidArgumentException;
use NativeRent\Core\Container\Exceptions\DependencyNotFound;
use Psr\Container\ContainerInterface;

use function is_callable;
use function is_string;

/**
 * Simple DI container. Implements PSR container.
 */
class Container implements ContainerInterface {
	/**
	 * Deps factories.
	 *
	 * @template T
	 * @var array<class-string<T>, callable(ContainerInterface): T>
	 */
	private $factories = [];

	/**
	 * Already resolved instances.
	 *
	 * @template T
	 * @var array<class-string<T>, T>
	 */
	private $instances = [];

	/**
	 * Register dependency.
	 *
	 * @template T
	 *
	 * @param class-string<T>                   $id
	 * @param T|callable(ContainerInterface): T $instance Concrete instance or factory function.
	 *
	 * @return self
	 * @throws InvalidArgumentException
	 */
	public function bind( $id, $instance ) {
		if ( ! is_string( $id ) ) {
			throw new InvalidArgumentException( 'Identifier of component must be a string' );
		}
		$isFactory = is_callable( $instance );
		if ( ! $isFactory && ! is_object( $instance ) ) {
			throw new InvalidArgumentException( 'Instance argument must be a function or object' );
		}

		// Register factory.
		if ( $isFactory ) {
			$this->factories[ $id ] = $instance;
		} else {
			$this->instances[ $id ] = $instance;
		}

		return $this;
	}

	/**
	 * {@inheritDoc}
	 *
	 * @template T
	 *
	 * @param class-string<T> $id
	 *
	 * @return T
	 * @throws DependencyNotFound
	 */
	public function get( $id ) {
		if ( $this->hasInstance( $id ) ) {
			return $this->instances[ $id ];
		}
		if ( $this->hasFactory( $id ) ) {
			$this->instances[ $id ] = $this->factories[ $id ]( $this );

			return $this->instances[ $id ];
		}

		throw new DependencyNotFound( json_encode( "Dependency `$id` not found", JSON_UNESCAPED_UNICODE ) );
	}

	/**
	 * {@inheritDoc}
	 *
	 * @param class-string $id
	 *
	 * @return bool
	 */
	public function has( $id ) {
		return ( $this->hasInstance( $id ) || $this->hasFactory( $id ) );
	}

	/**
	 * Factory availability check.
	 *
	 * @param class-string $id
	 *
	 * @return bool
	 */
	private function hasFactory( $id ) {
		return ( ! empty( $this->factories[ $id ] ) && is_callable( $this->factories[ $id ] ) );
	}

	/**
	 * Instance availability check.
	 *
	 * @param class-string $id
	 *
	 * @return bool
	 */
	private function hasInstance( $id ) {
		return ! empty( $this->instances[ $id ] );
	}
}
