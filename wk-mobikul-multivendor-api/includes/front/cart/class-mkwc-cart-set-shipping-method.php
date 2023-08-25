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
use WkMobikulMultivendorApi\Includes\Front\Checkout;

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'Mkwc_Cart_Set_Shipping_Method' ) ) {
    /**
     * Cart add product class
     */
    class Mkwc_Cart_Set_Shipping_Method extends Includes\Mkwc_Authentication implements Util\Mkwc_Cart_Set_Shipping_Method_Interface
    {
        public static $base = 'cart/shipping_method';

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
        public function mkwc_cart_set_shipping_method( $data )
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

            $checkout_handler = new Checkout\Mkwc_Checkout();

            $general_data_handler = new Helper\Mkwc_General_Data();

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
            $shipping_method = array();

            $shipping_method['shipping_method'] = isset( $shipping_data['shipping_method'] ) ? $shipping_data['shipping_method'] : '';

            $shipping_method['shipping_lines']  = array(
              'method_id'    => isset( $shipping_data['shipping_method']['method_id'] ) ? $shipping_data['shipping_method']['method_id'] : '',
              'method_title' => isset( $shipping_data['shipping_method']['method_title'] ) ? $shipping_data['shipping_method']['method_title'] : '',
              'total'        => isset( $shipping_data['shipping_method']['cost'] ) ? $shipping_data['shipping_method']['cost'] : ''
            );
            $key = array('shipping_method','shipping_lines');

            $request = $cartdata_handler->mkwc_merge_cart_data( $user_id, $key, $shipping_method );

            if( isset( $request['success'] ) && $request['success'] == false ){
                return array(
                    'success' => false,
                    'message' => $request['message']
                );
            }

            $cart_data = $cartdata_handler->mkwc_get_cart_custom_details( $user_id );

            $cart_total = isset( $cart_data['total'] ) ? $general_data_handler->mkwc_get_formatted_price( $cart_data['total'] ) : '';

            $payment_methods = $checkout_handler->mkwc_payment_methods();

            $payment_methods['total'] = $cart_total ? $cart_total : '';

            return $payment_methods;
        }
    }
}
