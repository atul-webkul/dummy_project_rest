<?php

/**
 * @author Webkul
 * @version 1.0.0
 * This file handles Cart empty related functions
 */

namespace WkMobikulMultivendorApi\Includes\Front\Cart;

use WkMobikulMultivendorApi\Includes;
use WkMobikulMultivendorApi\Includes\Front\Cart\Util;
use WkMobikulMultivendorApi\Helper;
use WkMobikulMultivendorApi\Includes\Front\Cart;

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'Mkwc_Cart_Empty' ) ) {
    /**
     * Cart empty class
     */
    class Mkwc_Cart_Empty implements Util\Mkwc_Cart_Empty_Interface
    {
        public static $base = 'cart/empty';

        protected $cartget;

        protected $cartdata;

        protected $wpdb;

        function __construct()
        {
            global $wpdb;

            $this->wpdb = $wpdb;
        }

        /**
         * cart empty endpoint callback
         * @param
         * @return $cart_key
         */
        public function mkwc_cart_empty( $data )
        {
            $cartdata_handler = new Helper\Mkwc_Cart_Data();

            $response_handler = new \WP_REST_Response();

            $table_name = $this->wpdb->prefix . 'woocommerce_sessions';

            $user_data = is_object( $data ) ? $data->get_params() : $data;
            
            $customer_id = isset( $user_data['customer_id'] ) ? $user_data['customer_id'] : '';

            $guest_id = isset( $user_data['guest_id'] ) ? $user_data['guest_id'] : '';

            $user_id = ! ( $customer_id ) ? $guest_id : $customer_id;
            if( empty( $user_id ) ){
                return array(
                    'success' => false,
                    'message' => esc_html__( 'Sorry, you must provide all the parameters.', 'mobikul-marketplace' )
                );
            }
            $cart_datas = $cartdata_handler->mkwc_get_cart_custom_details($user_id);
            $myarr = array_map(array($this,'wkmc_empty_cart_data'), $cart_datas);

            $sereliaze_myarr = maybe_serialize($myarr);
            $this->wpdb->get_row( $this->wpdb->prepare("UPDATE {$table_name} SET session_value = %s WHERE session_key = %s", $sereliaze_myarr, $user_id ) );
            update_user_meta( $user_id, '_woocommerce_persistent_cart_' . get_current_blog_id(), array(
                'cart' => $myarr['cart']
            ) );
            $getcart_handler  = new cart\Mkwc_Cart_Get();
            $id['userid'] =  $user_id;
            $response = $getcart_handler->mkwc_cart_get($id);
            $response['success'] = true;
            $response['message'] = esc_html__('All products removed from cart successfully','mobikul-marketplace');
            $response_handler->set_data( $response );
            return $response_handler;
        }

        public function wkmc_empty_cart_data($cart_datas)
        {
            return [];
        }
    }
}
