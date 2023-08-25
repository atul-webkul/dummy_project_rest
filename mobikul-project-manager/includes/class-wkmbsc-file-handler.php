<?php

final class Wkmbsc_File_Handler {

	/**
	 * The single instance of the class.
	 *
	 * @var $instance
	 * @since 1.0.0
	 */
	protected static $instance = null;

	public function __construct() {

		if ( is_admin() ) {

		} else {
			Wkmbsc_Front_Hook::get_instance();
		}

	}

	/**
	 * Main WKMBSC_File_Handler Instance.
	 *
	 * Ensures only one instance of WKMBSC_File_Handler is loaded or can be loaded.
	 *
	 * @return Main instance.
	 * @since 1.0.0
	 * @static
	 */
	public static function get_instance() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}
		return self::$instance;
	}


}


