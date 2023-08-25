<?php

/**
 * @author Webkul
 * @version 1.0.0
 * This file handles seller ask to admin functions.
 */

namespace WkMobikulMultivendorApi\Includes\Front\Seller;

use WkMobikulMultivendorApi\Includes;
use WkMobikulMultivendorApi\Includes\Front\Seller\Util;
use WkMobikulMultivendorApi\Helper;

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'Mkwc_Seller_Ask_Admin' ) ) {
    /**
     *
     */
    class Mkwc_Seller_Ask_Admin extends Includes\Mkwc_Authentication implements Util\Mkwc_Seller_Ask_admin_Interface
    {
        public static $base = 'seller/asktoadmin';

        protected $seller_data_handler;

        protected $response;

        /**
         * Seller asked queries list
         */
        public function mkwc_get_seller_asked_queries()
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
                    'message' => __( ' Sorry, you must provide all parameters.', 'mobikul_marketplace' ),
                ];
            }

            $data = $seller_data_handler->mkwc_get_asked_queries( $seller_id, $page, $per_page );

            $response_handler->set_data( $data );

            return $response_handler;
        }

        /**
         * Submit query to admin
         */
        public function mkwc_seller_send_query( $data )
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

            $seller_id = isset( $_GET['seller_id'] ) ? intval( $_GET['seller_id'] ) : '';

            $seller_check = $this->mkwc_user_exist_request( $seller_id );
            
            if( isset( $seller_check['success'] ) && ! $seller_check['success'] ){
                return $seller_check;
            }
            
            $data = $data->get_params();

            $seller_post_handler = new Helper\Mkwc_Seller_Post_Data();

            $success = 0;

            $message = '';

            if ( isset( $data['subject'] ) && isset( $data['message'] ) ) {
                $subject = ! empty( $data['subject'] ) ? strip_tags( wp_unslash( $data['subject'] ) ) : ''; // Input var okay.

            		$message = ! empty( $data['message'] ) ? strip_tags( wp_unslash( $data['message'] ) ) : ''; // Input var okay.

            		if ( ! empty( $subject ) && ! empty( $message ) ) { // Input var okay.
                    $message_length = strlen( $message );

                    if ( ! preg_match( '/^[A-Za-z0-9 ]{1,100}$/', $subject ) ) {
                        $success = 0;
                        $message = __( 'Subject Invalid.', 'mobikul_marketplace' );
                    } elseif ( $message_length < 10 || $message_length > 500 ) {
                        $success = 0;
                        $message = __( 'Message length should be greater than 10 and less than 500.', 'mobikul_marketplace' );
                    } else {
                        $result = $seller_post_handler->mkwc_set_seller_query_admin( $seller_id, $subject, $message );

                        if ( $result ) {
                            $success = true;
                            $message = __( 'Query submitted successfully.', 'mobikul_marketplace' );
                        } else {
                            $success = false;
                            $message = __( 'Query submission failed.', 'mobikul_marketplace' );
                        }
                    }
                } else {
                    $success = false;
                    $message = __( 'Fill required fields.', 'mobikul_marketplace' );
            		}
            } else {
                $success = false;
                $message = __( 'Sorry, you must provide all parameters.', 'mobikul_marketplace' );
            }

            $response_handler->set_data( array(
                'success' => $success,
                'message' => $message,
            ) );

            return $response_handler;
        }
    }
}
