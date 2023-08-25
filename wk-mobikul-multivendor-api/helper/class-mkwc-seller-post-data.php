<?php

/**
 * @author Webkul
 * @version 1.0.0
 * This file handles all seller data functions.
 */

namespace WkMobikulMultivendorApi\Helper;

use WkMobikulMultivendorApi\Helper;
use WkMobikulMultivendorApi\Helper\Util;

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'Mkwc_Seller_Post_Data' ) ) {
    /**
     *
     */
    class Mkwc_Seller_Post_Data implements Util\Mkwc_Seller_Post_Data_Interface
    {
        protected $wpdb;

        public function __construct()
        {
            global $wpdb;
            
            $this->wpdb = $wpdb;
        }

        /**
         * Save seller query to admin
         * @param $seller_id, $subject, $message
         * @return bool
         */
        public function mkwc_set_seller_query_admin( $seller_id, $subject, $message )
        {
            $current_time = date( 'Y-m-d H:i:s' );

            $sql = $this->wpdb->insert(
                $this->wpdb->prefix . 'mpseller_asktoadmin',
                array(
                    'seller_id'   => $seller_id,
                    'subject'     => $subject,
                    'message'     => $message,
                    'create_date' => $current_time,
                ),
                array(
                    '%d',
                    '%s',
                    '%s',
                    '%s',
                )
            );

            if ( $sql ) {
                return true;
            } else {
                return false;
            }
        }
    }
}
