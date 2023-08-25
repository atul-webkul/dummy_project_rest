<?php

/**
 * @author Webkul
 * @version 1.0.0
 * This file handles checkout related functions
 */

namespace WkMobikulMultivendorApi\Includes\Front\Cart;

use WkMobikulMultivendorApi\Includes;
use WkMobikulMultivendorApi\Includes\Front\Cart\Util;
use WkMobikulMultivendorApi\Helper;

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'Mkwc_Cart_Update' ) ) {
    /**
     * Cart add product class
     */
    class Mkwc_Cart_Update extends Includes\Mkwc_Authentication implements Util\Mkwc_Cart_Update_Interface
    {
        public static $base = 'cart/update';

        protected $cartdata;

        public $update_item_count;

        /**
         * cart update endpoint callback
         * @param
         * @return $cart_key
         */
        public function mkwc_cart_update( $data )
        {
            $validate_auth_key = $this->mkwc_authenticate_request();

            if ( $validate_auth_key != 'ok' ) {
                return array(
                    'session_id'  => $validate_auth_key,
                    'status'      => 401,
                    'invalid_auth_key_error' => __( 'Please provide valid Auth Key.', 'mobikul_marketplace' ),
                    'success'     => false,
                );
            }

            $data = is_object( $data ) ? $data->get_json_params() : $data;

            $cartdata_handler = new Helper\Mkwc_Cart_Data();

            return $cartdata_handler->mkwc_cart_update_data( $data );
        }
    }
}
