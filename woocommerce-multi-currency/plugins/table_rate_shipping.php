<?php

/**
 * Class WOOMULTI_CURRENCY_Plugin_Table_Rate_Shipping
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WOOMULTI_CURRENCY_Plugin_Table_Rate_Shipping {
	protected $settings;

	public function __construct() {

		$this->settings = WOOMULTI_CURRENCY_Data::get_ins();
		if ( $this->settings->get_enable() ) {
			add_filter( 'betrs_condition_tertiary_subtotal', array( $this, 'change_price' ), 10, 2 );
		}
	}

	public function change_price( $value, $cond ) {
		return wmc_get_price( $value );
	}
}