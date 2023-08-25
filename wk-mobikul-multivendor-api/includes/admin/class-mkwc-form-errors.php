<?php

/**
 * @author Webkul
 * @version 1.0.0
 * This file handles backend settings error
 */

namespace WkMobikulMultivendorApi\Includes\Admin;

defined( 'ABSPATH' ) || exit;

if ( ! class_exists('mkwc_Form_Errors') ) {

    /**
     *Settings error msg
     */

    class mkwc_Form_Errors
    {
        public $error_code = 0;

        public function __construct( $error_code = '' )
        {
            $this->error_code = $error_code;
        }

        public function mkwc_set_error_code( $code )
        {
            if( !empty( $code )  ) {
                $this->error_code = $code;
            }
        }

        public function mkwc_get_error_code()
        {
            return $this->error_code;
        }

        public function mkwc_print_notification( $message )
        {
            if( is_admin() ) {
                if( $this->error_code == 0 ) {
                    echo '<div class="notice notice-success is-dismissible">
                              <p><strong>'. $message . '</strong></p>
                              <button type="button" class="notice-dismiss">
                                  <span class="screen-reader-text">
                                      ' . __( 'Dismiss this notice.', 'mobikul_marketplace' ) . '
                                  </span>
                              </button>
                          </div>';
                } else if( $this->error_code == 1 ) {
                    echo '<div class="notice notice-error is-dismissible">
                              <p><strong>'. $message . '</strong></p>
                              <button type="button" class="notice-dismiss">
                                  <span class="screen-reader-text">
                                      ' . __( 'Dismiss this notice.', 'mobikul_marketplace' ) . '
                                  </span>
                              </button>
                          </div>';
                }
            } else {
                if( $this->error_code == 0 ) {
                    wc_print_notice( $message, 'success' );
                } else if( $this->error_code == 1 ) {
                    wc_print_notice( $message, 'error' );
                }
            }
        }
    }
}
