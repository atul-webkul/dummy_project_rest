<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 21/06/2019
 * Time: 9:11 CH
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
if ( function_exists( 'wmc_get_price' ) ) {
	class WOOMULTI_CURRENCY_Plugin_Yith_Add_On {
		public function __construct() {
			add_filter( 'wapo_print_option_price', array( $this, 'compatible_yith_add_on' ) );
		}

		public function compatible_yith_add_on( $price ) {
			return wmc_get_price( $price );
		}
	}
}