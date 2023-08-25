<?php

/**
 * @author Webkul
 * @version 1.0.0
 * This file handles Instagram tag Data
 */

namespace WkMobikulMultivendorApi\Templates\Admin\Notification;
use WkMobikulMultivendorApi\Helper;
use WkMobikulMultivendorApi\Includes\Admin;

if (!defined('ABSPATH')) {
    exit;
}

if (! class_exists('Mkwc_Save_Notification_Data')) {
    /**
     *Save notification data
     */
    class Mkwc_Save_Notification_Data extends Admin\mkwc_Form_Errors
    {
        public $notification_data = array();

        public function __construct( $data )
        {
            $this->notification_data = $data;

            $this->noti_data_handler = new Helper\Mkwc_Notification();

            $this->mkwc_save_notification_raw();
        }

        public function mkwc_save_notification_raw()
        {
            $mkwc_id  = filter_input( INPUT_GET, 'id', FILTER_SANITIZE_NUMBER_INT );

            $mkwc_img = isset( $this->notification_data['mkwc_notification_image'] ) ? $this->notification_data['mkwc_notification_image'] : '';
            $mkwc_img = ( ! empty( $mkwc_img ) ) ? (int)$mkwc_img : '';

            $mkwc_title = isset( $this->notification_data['mkwc_notification_title'] ) ? $this->notification_data['mkwc_notification_title'] : '';

            $mkwc_title = ! empty( $mkwc_title ) ? sanitize_text_field( $mkwc_title ) : '';

            $mkwc_content = isset( $this->notification_data['mkwc_notification_content'] ) ? $this->notification_data['mkwc_notification_content'] : '';

            $mkwc_content = ! empty( $mkwc_content ) ? sanitize_text_field( $mkwc_content ) : '';

            $mkwc_type = isset( $this->notification_data['mkwc_notification_type'] ) ? $this->notification_data['mkwc_notification_type'] : '';

            $mkwc_type = ! empty( $mkwc_type ) ? sanitize_text_field( $mkwc_type ) : '';

            $mkwc_term_id = isset( $this->notification_data['mkwc_notification_procat'] ) ? $this->notification_data['mkwc_notification_procat'] : '';

            $mkwc_term_id = ! empty( $mkwc_term_id ) ? sanitize_text_field( $mkwc_term_id ) : '';

            $mkwc_status = isset( $this->notification_data['mkwc_notification_status'] ) ? $this->notification_data['mkwc_notification_status'] : '';

            $mkwc_status = ! empty( $mkwc_status ) ? sanitize_text_field( $mkwc_status ) : '';
            if( empty( $mkwc_img ) ) {
                $message = __( 'Please select a image.', 'mobikul_marketplace' );
                parent::mkwc_set_error_code( 1 );
                parent::mkwc_print_notification( $message );
            }
            if( empty( $mkwc_title ) ) {
                $message = __( 'Please enter a title.', 'mobikul_marketplace' );
                parent::mkwc_set_error_code( 1 );
                parent::mkwc_print_notification( $message );
            }
            if( empty( $mkwc_content ) ) {
                $message = __( 'Please enter some content.', 'mobikul_marketplace' );
                parent::mkwc_set_error_code( 1 );
                parent::mkwc_print_notification( $message );
            }
            if( empty( $mkwc_type ) ) {
                $message = __( 'Please select a type.', 'mobikul_marketplace' );
                parent::mkwc_set_error_code( 1 );
                parent::mkwc_print_notification( $message );
            }
            if( empty( $mkwc_term_id ) ) {
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
                if( isset( $this->notification_data['mkwc_save_notification'] ) ) {
                    $arrgs = array(
                        'image'   => $mkwc_img,
                        'title'   => $mkwc_title,
                        'content' => $mkwc_content,
                        'type'    => $mkwc_type,
                        'term_id' => $mkwc_term_id,
                        'status'  => $mkwc_status
                    );
                    $this->noti_data_handler->mkwc_notification_update( 'add', $arrgs );

                    $message = __( 'Notification Save successfully', 'mobikul_marketplace' );
                    parent::mkwc_print_notification( $message );
                } elseif ( isset( $this->notification_data['mkwc_update_notification'] ) ) {
                    $arrgs = array(
                        'id'      => $mkwc_id,
                        'image'   => $mkwc_img,
                        'title'   => $mkwc_title,
                        'content' => $mkwc_content,
                        'type'    => $mkwc_type,
                        'term_id' => $mkwc_term_id,
                        'status'  => $mkwc_status
                    );
                    $this->noti_data_handler->mkwc_notification_update( 'update', $arrgs );

                    $message = __( 'Notification updated successfully', 'mobikul_marketplace' );
                    parent::mkwc_print_notification( $message );
                }
            } else {
                $message = __( 'Please fill up all the required fields ', 'mobikul_marketplace' );
                parent::mkwc_print_notification( $message );
            }
        }
    }
}
