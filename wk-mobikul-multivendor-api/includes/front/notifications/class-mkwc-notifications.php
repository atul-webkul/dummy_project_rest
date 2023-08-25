<?php

/**
 * @author Webkul
 * @version 1.0.0
 * This file handles media upload related functions
 */

namespace WkMobikulMultivendorApi\Includes\Front\Notifications;

use WkMobikulMultivendorApi\Includes;
use WkMobikulMultivendorApi\Includes\Front\Notifications\Util;
use WkMobikulMultivendorApi\Helper;

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'Mkwc_Notifications' ) ) {
    /**
     * External Links
     */
    class Mkwc_Notifications extends Includes\Mkwc_Authentication implements Util\Mkwc_Notifications_Interface
    {
        public static $base = 'notifications';

        protected $wpdb;

        function __construct()
        {
            global $wp, $wpdb;

            $this->wpdb = $wpdb;

            $this->table_name = $this->wpdb->prefix . 'mp_notification';
        }
        /**
         * External Links endpoint callback
         * @param $data
         * @return $id
        */
        public function mkwc_get_notifications()
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
            $response   = new \WP_REST_Response();
            $product_store = new Helper\Mkwc_Product_store();
            $general_data_handler = new Helper\Mkwc_General_Data();
            $notifications = [];

            $width = isset( $_GET['width'] ) ? intval( $_GET['width'] ) : '';

            if ( ! $width ) {
                return array(
                    'success' => 0,
                    'message' => __( 'Sorry, you must provide all parameters.', 'mobikul_marketplace' ),
                );
            }
            $mFactor = isset($_GET['mFactor']) ? $_GET['mFactor'] : 1;
            $height = 0.5 * $width;
            $height *= $mFactor;
            $width *= $mFactor;

            $size = array(
                'width'   => floatval( $width ),
                'height'  => floatval( $height ),
                'crop'    => 0,
            );
            $data = $this->wpdb->get_results("SELECT * FROM $this->table_name WHERE status = 'enabled'", ARRAY_A);
            if ( ! $data ) {
                $response->set_data( 
                    array( 
                        'success' => false, 
                        'message' => __( 'No notification found.', 'mobikul_marketplace') 
                    )
                );
                return $response;
            }

            if( is_array( $data ) ){
                foreach ($data as $dakey => $davalue) {
                    $image_meta = wp_get_attachment_metadata( $davalue['banner'] );
                    $attachment = $product_store->mkwc_image_downsize( $image_meta, $size, $davalue['banner'] );
                    $notifications[] = [
                        'id'    => $davalue['id'],
                        'image' => isset( $attachment[0] ) ? $attachment[0] : '',
                        'dominantColor' => isset( $attachment[0] ) ? $general_data_handler->mkwc_get_dominant_Color( $attachment[0] ) : $general_data_handler->mkwc_get_dominant_Color(''),
                        'title' => $davalue['title'],
                        'content' => $davalue['content'],
                        'type'    => $davalue['type'],
                        'term_id' => $davalue['term_id']
                    ];
                }                
            }
            $result = array( 'data' => $notifications, 'success' => true, 'message' => '' );

            $response->set_data( $result );

            return $response;
        }
    }
}
