<?php
/**
 * File handler.
 */

if ( ! class_exists( 'WKMPAPI_Main_Handler' ) ) {

	/**
	 * File handler class.
	 */
	class WKMPAPI_Main_Handler {

		/**
		 * Instance variable
		 *
		 * @var $instance
		 */
		protected static $instance = null;

		public function __construct() {
			if ( is_admin() ) {
			} else {
				WKMPAPI_Front_Hook::get_instance();
			}
		}

		/**
		 * This is a singleton page, access the single instance just using this method.
		 *
		 * @return object
		 */
		public static function get_instance() {
			if ( ! static::$instance ) {
				static::$instance = new self();
			}

			return static::$instance;
		}
	}
}
