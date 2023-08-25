<?php

/**
 * @author Webkul
 * @version 1.0.0
 * This file handles seller notifications.
 */

namespace WkMobikulMultivendorApi\Includes\Front\Seller;

use WkMobikulMultivendorApi\Includes;
use WkMobikulMultivendorApi\Includes\Front\Seller\Util;
use WkMobikulMultivendorApi\Helper;

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'Mkwc_Seller_Notifications' ) ) {
    /**
     *
     */
    class Mkwc_Seller_Notifications extends Includes\Mkwc_Authentication implements Util\Mkwc_Seller_Notifications_Interface
    {
        public static $base = 'seller/notification/list';

        protected $seller_data_handler;

        protected $response;

        /**
         * Seller notification list
         */
        public function mkwc_get_seller_notifications()
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
                    'success' => 0,
                    'message' => __( ' Sorry, you must provide all parameters.', 'mobikul_marketplace' ),
                ];
            }

            $notifications = $seller_data_handler->mkwc_seller_notifications( $seller_id, $page, $per_page );

            if ( $notifications ) {
                $data = $notifications;
                $data = array_merge( $data, array(
                    'success' => true,
                    'message' => '',
                ) );
            } else {
                $data = array_merge( $data, array(
                    'success' => false,
                    'message' => __( 'No notifications found.', 'mobikul_marketplace' ),
                ) );
            }

            $response_handler->set_data( $data );

            return $response_handler;

        }
    }
}
