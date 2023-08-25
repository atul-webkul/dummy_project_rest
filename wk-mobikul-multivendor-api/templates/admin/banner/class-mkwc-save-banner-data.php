<?php

/**
 * @author Webkul
 * @version 1.0.0
 * This file handles Instagram tag Data
 */

namespace WkMobikulMultivendorApi\Templates\Admin\Banner;
use WkMobikulMultivendorApi\Helper;
use WkMobikulMultivendorApi\Includes\Admin;

if (!defined('ABSPATH')) {
    exit;
}

if ( ! class_exists('Mkwc_Save_Banner_Data') ) {
    /**
     *Save notification data
     */
    class Mkwc_Save_Banner_Data extends Admin\mkwc_Form_Errors
    {
        public $banner_data = array();

        public function __construct( $data )
        {
            global $wpdb;

            $this->wpdb = $wpdb;

            $this->table_name = $wpdb->prefix . 'mp_banner';

            $this->banner_data = $data;

            $this->mkwc_save_banner_raw();
        }

        public function mkwc_save_banner_raw()
        {
            $mkwc_id  = filter_input( INPUT_GET, 'banner_id', FILTER_SANITIZE_NUMBER_INT );

            $mkwc_img = isset( $this->banner_data['mkwc_banner_image'] ) ? $this->banner_data['mkwc_banner_image'] : '';

            $mkwc_img = ( ! empty( $mkwc_img ) ) ? (int)$mkwc_img : '';

            $mkwc_type = isset( $this->banner_data['mkwc_banner_type'] ) ? $this->banner_data['mkwc_banner_type'] : '';

            $mkwc_type = ! empty( $mkwc_type ) ? sanitize_text_field( $mkwc_type ) : '';

            $mkwc_term_id = isset( $this->banner_data['mkwc_banner_procat'] ) ? $this->banner_data['mkwc_banner_procat'] : '';

            $mkwc_term_id = ! empty( $mkwc_term_id ) ? sanitize_text_field( $mkwc_term_id ) : '';
            $mkwc_term_id = ( $mkwc_type == 'image' ) ? 0 : $mkwc_term_id;
            $mkwc_status = isset( $this->banner_data['mkwc_banner_status'] ) ? $this->banner_data['mkwc_banner_status'] : '';

            $mkwc_status = ! empty( $mkwc_status ) ? sanitize_text_field( $mkwc_status ) : '';
            if( empty( $mkwc_img ) ) {
                $message = __( 'Please select a image.', 'mobikul_marketplace' );
                parent::mkwc_set_error_code( 1 );
                parent::mkwc_print_notification( $message );
            }
            if( empty( $mkwc_type ) ) {
                $message = __( 'Please select a type.', 'mobikul_marketplace' );
                parent::mkwc_set_error_code( 1 );
                parent::mkwc_print_notification( $message );
            }
            if( $mkwc_type != 'image' && empty( $mkwc_term_id ) ) {
                $message = __( 'Please select a product/Category.', 'mobikul_marketplace' );
                parent::mkwc_set_error_code( 1 );
                parent::mkwc_print_notification( $message );
            }
            if( empty( $mkwc_status ) ) {
                $message = __( 'Please select a status.', 'mobikul_marketplace' );
                parent::mkwc_set_error_code( 1 );
                parent::mkwc_print_notification( $message );
            }
            if( parent::mkwc_get_error_code() == 0 ) {
                if( isset( $this->banner_data['mkwc_save_banner'] ) ) {
                    $this->wpdb->get_results("INSERT INTO $this->table_name ( image, banner_type, term_id, status ) VALUES ( '$mkwc_img', '$mkwc_type', '$mkwc_term_id', '$mkwc_status' )");

                    $message = __( 'Banner Save successfully.', 'mobikul_marketplace' );
                    parent::mkwc_print_notification( $message );
                } elseif ( isset( $this->banner_data['mkwc_update_banner'] ) ) {
                    $this->wpdb->get_row( "UPDATE $this->table_name SET image = '$mkwc_img', banner_type = '$mkwc_type', term_id = '$mkwc_term_id', status = '$mkwc_status' WHERE id = '$mkwc_id'" );

                    $message = __( 'Banner updated successfully.', 'mobikul_marketplace' );
                    parent::mkwc_print_notification( $message );
                }
            } else {
                $message = __( 'Please fill up all the required fields ', 'mobikul_marketplace' );
                parent::mkwc_print_notification( $message );
            }
        }
    }
}
