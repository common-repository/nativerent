<?php

namespace NativeRent\Core\Options;

class WpOptionStorage implements OptionStorageInterface {
	/**
	 * {@inheritDoc}
	 */
	public function get( $name, $default = null ) {
		$v = get_option( $name, $default );

		return false === $v ? $default : $v;
	}

	/**
	 * {@inheritDoc}
	 */
	public function set( $name, $value ) {
		return update_option( $name, $value );
	}

	/**
	 * {@inheritDoc}
	 */
	public function delete( $name ) {
		delete_option( $name );
	}
}
