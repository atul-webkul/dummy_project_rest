<?php

/**
 * @author Webkul
 * @version 1.0.0
 * This file handles Instagram tag Data
 */

namespace WkMobikulMultivendorApi\Templates\Admin\Carousels;
use WkMobikulMultivendorApi\Helper;
use WkMobikulMultivendorApi\Includes\Admin;

if (!defined('ABSPATH')) {
    exit;
}

if ( ! class_exists('Mkwc_Save_Carousels_Data') ) {
    /**
     *Save notification data
     */
    class Mkwc_Save_Carousels_Data extends Admin\mkwc_Form_Errors
    {
        public $carousels_data = array();

        public function __construct( $data )
        {
            global $wpdb;

            $this->wpdb = $wpdb;

            $this->table_name = $wpdb->prefix . 'mp_carousels';

            $this->carousels_data = $data;

            $this->mkwc_save_carousels_raw();
        }

        public function mkwc_save_carousels_raw()
        {
            $product_store_handler = new Helper\Mkwc_Product_store();

            $mkwc_id  = filter_input( INPUT_GET, 'carousels_id', FILTER_SANITIZE_NUMBER_INT );

            $mkwc_title = isset( $this->carousels_data['mkwc_carousel_title'] ) ? $this->carousels_data['mkwc_carousel_title'] : '';

            $mkwc_title = ( ! empty( $mkwc_title ) ) ? $mkwc_title : '';

            $mkwc_type = isset( $this->carousels_data['mkwc_carousel_type'] ) ? $this->carousels_data['mkwc_carousel_type'] : '';

            $mkwc_type = ! empty( $mkwc_type ) ? sanitize_text_field( $mkwc_type ) : '';

            $mkwc_ids = isset( $this->carousels_data['mkwc_carousel_procat'] ) ? $this->carousels_data['mkwc_carousel_procat'] : '';
            
            $mkwc_ids = ! empty( $mkwc_ids ) ? maybe_serialize( array_map(function( $mkwc_id ){
                return sanitize_text_field( $mkwc_id );
            }, $mkwc_ids) ) : '';
            
            $mkwc_status = isset( $this->carousels_data['mkwc_carousel_status'] ) ? $this->carousels_data['mkwc_carousel_status'] : '';

            $mkwc_status = ! empty( $mkwc_status ) ? sanitize_text_field( $mkwc_status ) : '';
            
            $title_check = $product_store_handler->mkwc_return_homepage_carousels_var( $mkwc_title );
            
            if( empty( $mkwc_title ) ) {
                $message = __( 'Please enter a title.', 'mobikul_marketplace' );
                parent::mkwc_set_error_code( 1 );
                parent::mkwc_print_notification( $message );
            } else if( str_replace(' ','_',$title_check) == str_replace(' ','_',$mkwc_title ) && isset( $this->carousels_data['mkwc_save_carousels'] ) ){
                $message = __( 'Title' .' '.$mkwc_title .' '.'is already exist.', 'mobikul_marketplace' );
                parent::mkwc_set_error_code( 1 );
                parent::mkwc_print_notification( $message );
            }

            if( empty( $mkwc_type ) ) {
                $message = __( 'Please select a type.', 'mobikul_marketplace' );
                parent::mkwc_set_error_code( 1 );
                parent::mkwc_print_notification( $message );
            }
            if( empty( $mkwc_ids ) ) {
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
                if( isset( $this->carousels_data['mkwc_save_carousels'] ) ) {
                    $this->wpdb->get_results("INSERT INTO $this->table_name ( title, type, carousel_ids, status ) VALUES ( '$mkwc_title', '$mkwc_type', '$mkwc_ids', '$mkwc_status' )");

                    $message = __( 'Carousels Save successfully.', 'mobikul_marketplace' );
                    parent::mkwc_print_notification( $message );
                } elseif ( isset( $this->carousels_data['mkwc_update_carousels'] ) ) {
                    $this->wpdb->get_row( "UPDATE $this->table_name SET title = '$mkwc_title', type = '$mkwc_type', carousel_ids = '$mkwc_ids', status = '$mkwc_status' WHERE id = '$mkwc_id'" );

                    $message = __( 'Carousels updated successfully.', 'mobikul_marketplace' );
                    parent::mkwc_print_notification( $message );
                }
            }
        }
    }
}
