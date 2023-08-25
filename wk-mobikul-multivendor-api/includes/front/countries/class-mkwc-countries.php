<?php

/**
 * @author Webkul
 * @version 1.0.0
 * This file handles countries list.
 */

namespace WkMobikulMultivendorApi\Includes\Front\Countries;

use WkMobikulMultivendorApi\Includes;
use WkMobikulMultivendorApi\Includes\Front\Countries\Util;
use WkMobikulMultivendorApi\Helper;

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'Mkwc_Countries' ) ) {
  /**
   * Countries class
   */
    class Mkwc_Countries extends Includes\Mkwc_Authentication implements Util\Mkwc_Countries_Interface
    {
        public static $base = 'countries';

        /**
         * Get countries endpoint callback
         * @return $countries
         *
         */
        public function mkwc_get_countries()
        {
            $validate_auth_key = $this->mkwc_authenticate_request( 0 );

            if ( $validate_auth_key != 'ok' ) {
                return array(
                    'session_id'  => $validate_auth_key,
                    'status'      => 401,
                    'invalid_auth_key_error' => __( 'Please provide valid Auth Key.', 'mobikul_marketplace' ),
                    'success'     => false,
                );
            }
            global $woocommerce;

            $data = array();

            $sdata = array();

            $i = 0;

      	    $countries = WC()->countries->get_shipping_countries();

      	    foreach ( $countries as $ckey => $cvalue ) {
        	    	if ( $i == 0 ) {
                        $states = WC()->countries->get_states();
        	    	}

        	    	$state_data = array();

                $new_arr = array();

        	    	if ( array_key_exists( $ckey, $states ) && ! empty( $states[$ckey] ) ) {
          	    		$keys   = array_keys( $states[$ckey] );

          	    		$values = array_values( $states[$ckey] );

                    for ( $i=1; $i < count($keys) ; $i++ ) {
          	    			$new_arr[] = array( "key" => $keys[$i], 'name' => $values[$i] );
          	    		}
        	    	}

        	    	$data[] = array(
                    "code"    => $ckey,
                    "name"    => html_entity_decode($cvalue),
                    "states"  => $new_arr,
                );

      			    $i++;
      	    }

      	    return array(
              'countries' => $data,
              'success'   => true,
              'message'   => ''
            );
        }
    }
}
