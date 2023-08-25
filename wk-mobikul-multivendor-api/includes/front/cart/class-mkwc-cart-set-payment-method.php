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
use WkMobikulMultivendorApi\Includes\Front\Cart;

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'Mkwc_Cart_Set_Payment_Method' ) ) {
    /**
     * Cart add product class
     */
    class Mkwc_Cart_Set_Payment_Method  extends Includes\Mkwc_Authentication implements Util\Mkwc_Cart_Set_Payment_Method_Interface
    {
        public static $base = 'cart/payment_method';

        protected $cartget;

        protected $cartdata;

        protected $wpdb;

        function __construct()
        {
            global $wpdb;

            $this->wpdb = $wpdb;
        }

        /**
         * Set Shipping methods endpoint callback
         * @param
         * @return $cart_key
         */
        public function mkwc_cart_set_payment_method( $data )
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

            $cartget_handler = new Cart\Mkwc_Cart_Get();

            $shipping_data = is_object( $data ) ? $data->get_params() : '';

            $customer_id = isset( $shipping_data['customer_id'] ) ? $shipping_data['customer_id'] : '';

            $guest_id = isset( $shipping_data['guest_id'] ) ? $shipping_data['guest_id'] : '';

            $user_id = ! ( $customer_id ) ? $guest_id : $customer_id;

            $customer_check = $this->mkwc_user_exist_request( $customer_id );
            
            if( isset( $customer_check['success'] ) && ! $customer_check['success'] ){
                return $customer_check;
            }

            if( empty( $user_id ) ){
                return array(
                    'success' => false,
                    'message' => __( 'Sorry, you must provide all the parameters.', 'mobikul_marketplace' )
                );
            }
            $cartdata_handler = new Helper\Mkwc_Cart_Data();

            $custom_check = $cartdata_handler->mkwc_return_cart_empty( $user_id );

            if( empty( $custom_check ) ){
              return array(
                'success' => false,
                'isCartEmpty' => true,
                'message' => __( 'your cart is empty.', 'mobikul_marketplace' )
              );
            }

            $payment_method = isset( $shipping_data['payment_method'] ) ? $shipping_data['payment_method'] : '';

            $key = 'payment_method';

            $cartdata_handler->mkwc_merge_cart_data( $user_id, $key, $payment_method );

            $cart_datas = $cartget_handler->mkwc_cart_get();

            $cart_datas['success'] = true;

            return $cart_datas;
        }
    }
}
