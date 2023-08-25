<?php
/**
 * Rest api hook.
 */

if ( ! class_exists( 'WKMPAPI_Front_Hook' ) ) {

	/**
	 * Front hook handler class.
	 */
	class WKMPAPI_Front_Hook {

		/**
		 * Instance variable
		 *
		 * @var $instance
		 */
		protected static $instance = null;


		public function __construct() {
			$function_handler = WKMPAPI_Route_Handler::get_instance();
			add_action( 'rest_api_init', array( $function_handler, 'wkmpapi_create_route' ) );
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
