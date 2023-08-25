<?php

/**
 * @author Webkul
 * @version 1.0.0
 * This file handles Instagram tag Data
 */

namespace WkMobikulMultivendorApi\Templates\Admin\Category;
use WkMobikulMultivendorApi\Helper;
use WkMobikulMultivendorApi\Includes\Admin;

if (!defined('ABSPATH')) {
    exit;
}

if (! class_exists('Mkwc_Save_Category_Data')) {
    /**
     *Save notification data
     */
    class Mkwc_Save_Category_Data extends Admin\mkwc_Form_Errors
    {
        public $category_data = array();

        public function __construct( $data )
        {
            $this->category_data = $data;

            $this->noti_data_handler = new Helper\Mkwc_Notification();

            $this->mkwc_save_category_raw();
        }

        public function mkwc_save_category_raw()
        {
            $mkwc_id  = filter_input( INPUT_GET, 'category_id', FILTER_SANITIZE_NUMBER_INT );

            $mkwc_order = isset( $this->category_data['mkwc_sort_order'] ) ? $this->category_data['mkwc_sort_order'] : '';

            $mkwc_order = ( ! empty( $mkwc_order ) ) ? (int)$mkwc_order : '';

            $mkwc_cat_id = isset( $this->category_data['mkwc_category_procat'] ) ? $this->category_data['mkwc_category_procat'] : '';

            $mkwc_cat_id = ( ! empty( $mkwc_cat_id ) ) ? (int)$mkwc_cat_id : '';

            $mkwc_status = isset( $this->category_data['mkwc_category_status'] ) ? $this->category_data['mkwc_category_status'] : '';

            $mkwc_status = ! empty( $mkwc_status ) ? sanitize_text_field( $mkwc_status ) : '';
            if( empty( $mkwc_order ) ) {
                $message = __( 'Please enter a order number.', 'mobikul_marketplace' );
                parent::mkwc_set_error_code( 1 );
                parent::mkwc_print_notification( $message );
            }
            if( empty( $mkwc_cat_id ) ) {
                $message = __( 'Please select a category.', 'mobikul_marketplace' );
                parent::mkwc_set_error_code( 1 );
                parent::mkwc_print_notification( $message );
            }
            if( empty( $mkwc_status ) ) {
                $message = __( 'Please select a status.', 'mobikul_marketplace' );
                parent::mkwc_set_error_code( 1 );
                parent::mkwc_print_notification( $message );
            }
            if( parent::mkwc_get_error_code() == 0 ) {
                if( isset( $this->category_data['mkwc_save_category'] ) ) {
                    add_term_meta( $mkwc_cat_id, 'api_featured_sort_order', $mkwc_order );

                    add_term_meta( $mkwc_cat_id, 'api_featured_category', $mkwc_status );

                    $message = __( 'Category Save successfully', 'mobikul_marketplace' );
                    parent::mkwc_print_notification( $message );
                } elseif ( isset( $this->category_data['mkwc_update_category'] ) ) {
                    update_term_meta( $mkwc_id, 'api_featured_sort_order', $mkwc_order );

                    update_term_meta( $mkwc_id, 'api_featured_category', $mkwc_status );

                    $message = __( 'Category updated successfully', 'mobikul_marketplace' );
                    parent::mkwc_print_notification( $message );
                }
            } else {
                $message = __( 'Please fill up all the required fields ', 'mobikul_marketplace' );
                parent::mkwc_print_notification( $message );
            }
        }
    }
}
