<?php

/**
 * @author Webkul
 * @version 1.0.0
 * This file handles front action hooks.
 */

namespace WkMobikulMultivendorApi\Includes\Front;

use WkMobikulMultivendorApi\Includes\Front;

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'Mkwc_Front_Hook_Handler' ) ) {
	/**
	 *
	 */
	class Mkwc_Front_Hook_Handler {

		public function __construct() {
			$front_function_obj = new Front\Mkwc_Front_Function_Handler();

			add_action( 'rest_api_init', array( $front_function_obj, 'mkwc_register_routes' ) );
		}
	}
}
