<?php
/**
 * Rest api hook.
 */

defined( 'ABSPATH' ) || exit(); // Exit if accessed directly.

if ( ! class_exists( 'Wkmbsc_Front_Hook' ) ) {

	class Wkmbsc_Front_Hook {

		/**
		 * The single instance of the class.
		 *
		 * @var $instance
		 * @since 1.0.0
		 */
		protected static $instance = null;

		public function __construct() {
			$function_handler = new Wkmbsc_Route_Handler();
			add_action( 'rest_api_init', array( $function_handler, 'wkmbsc_restapi_init' ) );
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
}
