<?php

/**
 * @author Webkul
 * @version 1.0.0
 * This file handles media upload related functions
 */

namespace WkMobikulMultivendorApi\Includes\Front\Externallinks;

use WkMobikulMultivendorApi\Includes;
use WkMobikulMultivendorApi\Includes\Front\Externallinks\Util;

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'Mkwc_External_Links' ) ) {
    /**
     * External Links
     */
    class Mkwc_External_Links extends Includes\Mkwc_Authentication implements Util\Mkwc_External_Links_Interface
    {
        public static $base = 'external-links';

        /**
         * External Links endpoint callback
         * @param $data
         * @return $id
        */
        public function mkwc_get_external_links()
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
            $links = get_option( 'mkwc_external_links' );
            if ( ! $links ) {
                $response->set_data( array( 'success' => false, 'message' => __( 'Kindly, add extra links.', 'mobikul_marketplace') ) );
                return $response;
            }

            // holds all the posts data
            $struct = array();
            foreach ( $links['title'] as $k => $title ) {
                $struct[] = array(
                    'title'	=> $title,
                    'link'	=> $links['links'][$k]
                );
            }

            $links = array( 'data' => $struct, 'success' => true, 'message' => '' );

            $response->set_data( $links );

            return $response;
        }
    }
}
