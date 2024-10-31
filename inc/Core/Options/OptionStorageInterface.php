<?php

namespace NativeRent\Core\Options;

interface OptionStorageInterface {
	/**
	 * Option getter
	 *
	 * @param  string      $name     Option name.
	 * @param  scalar|null $default  Default value.
	 *
	 * @return scalar|null
	 */
	public function get( $name, $default = null );

	/**
	 * Option setter
	 *
	 * @param  string      $name   Option name.
	 * @param  scalar|null $value  Option value.
	 *
	 * @return bool Returns true, if the option has been updated/created.
	 */
	public function set( $name, $value );

	/**
	 * Delete the option.
	 *
	 * @param  string $name  Option name.
	 *
	 * @return void
	 */
	public function delete( $name );
}
