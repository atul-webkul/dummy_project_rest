<?php

class WPBM_Settings {


	private static $settings = array();
	const OPTION             = 'wpbm_settings';

	function get( $key, $default = null ) {
		self::load();
		if ( empty( self::$settings ) ) {
			self::$settings = array();
		}
		if ( array_key_exists( $key, self::$settings ) ) {
			return self::$settings[ $key ];
		}

		return $default;
	}

	function set( $key, $value ) {
		self::load();
		self::$settings[ $key ] = $value;
		update_option( self::OPTION, self::$settings );
	}

	function load( $force = false ) {
		if ( ! self::$settings || $force ) {
			self::$settings = get_option( self::OPTION );
		}

		return self::$settings;
	}

}
