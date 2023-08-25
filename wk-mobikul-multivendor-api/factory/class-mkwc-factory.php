<?php

/**
 * @author Webkul
 * @version 1.0.0
 * This file handles main factory functions.
 */

namespace WkMobikulMultivendorApi\Factory;

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'Mkwc_Factory' ) ) {
	/**
	 * Main Factory Class
	 */
	class Mkwc_Factory {

		/**
		 * Return class object
		 */
		public static function mkwc_get_api_object( $classname ) {
			$reflectionClass = new \ReflectionClass( $classname );

			return $reflectionClass->newInstanceArgs();
		}
	}
}
