<?php

/**
 * @author Webkul
 * @version 1.0.0
 * This file handles seller shop followers functions.
 */

namespace WkMobikulMultivendorApi\Includes\Front\Seller;

use WkMobikulMultivendorApi\Includes;
use WkMobikulMultivendorApi\Includes\Front\Seller\Util;
use WkMobikulMultivendorApi\Helper;

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'Mkwc_Seller_Shop_Followers' ) ) {
    /**
     *
     */
    class Mkwc_Seller_Shop_Followers extends Includes\Mkwc_Authentication implements Util\Mkwc_Seller_Shop_Followers_Interface
    {
        public static $base = 'seller/followers';

        protected $seller_data_handler;

        protected $response;

        /**
         * Seller notification list
         */
        public function mkwc_get_seller_shop_followers()
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

            $response_handler = new \WP_REST_Response();

            $seller_data_handler = new Helper\Mkwc_Seller_Data();

            $data = [];

            $seller_id = isset( $_GET['seller_id'] ) ? intval( $_GET['seller_id'] ) : '';

            $page = isset( $_GET['page'] ) ? intval( $_GET['page'] ) : '';

            $per_page = get_option( 'posts_per_page' );

            $seller_check = $this->mkwc_user_exist_request( $seller_id );
            
            if( isset( $seller_check['success'] ) && ! $seller_check['success'] ){
              return $seller_check;
            }

            if ( ! $page ) {
                return [
                    'success' => false,
                    'message' => __( 'Sorry, you must provide all parameters.', 'mobikul_marketplace' ),
                ];
            }

            $data = $seller_data_handler->mkwc_get_followers( $seller_id, $page, $per_page );

            $response_handler->set_data( $data );

            return $response_handler;
        }
    }
}
