<?php

/**
 * @author Webkul
 * @version 1.0.0
 * This file handles media upload related functions
 */

namespace WkMobikulMultivendorApi\Includes\Front\Media;

use WkMobikulMultivendorApi\Includes;
use WkMobikulMultivendorApi\Includes\Front\Media\Util;

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'Mkwc_Media_Upload' ) ) {
    /**
     * Media class
     */
    class Mkwc_Media_Upload extends Includes\Mkwc_Authentication implements Util\Mkwc_Media_Upload_Interface
    {
        public static $base = 'media';

        /**
         * Image upload endpoint callback
         * @param $data
         * @return $id
        */
        public function mkwc_media_upload( $data )
        {
            $validate_auth_key = $this->mkwc_authenticate_request( 0 );

            if ( $validate_auth_key != 'ok' ) {
                return array(
                    'session_id'  => $validate_auth_key,
                    'status'      => 401,
                    'invalid_auth_key_error' => esc_html__( 'Please provide valid Auth Key.', 'mobikul-marketplace' ),
                    'success'     => false,
                );
            }
            $data = is_object( $data ) ? $data->get_file_params() : $data['profile_Image'];

            $profile_image = isset( $data['profile_Image'] ) ? $data['profile_Image'] : '';

            $profile_name = isset( $profile_image['name'] ) ? $profile_image['name'] : '';

            $profile_type = isset( $profile_image['type'] ) ? $profile_image['type'] : '';

            if( empty( $profile_image ) || empty( $profile_name ) || empty( $profile_type ) ) {
                return array(
                  'success' => false,
                  'message' => esc_html__( 'Sorry, please provide all the parameters.', 'mobikul-marketplace' )
                );
            }

            //$data['profile_Image']['type'] = 'image/jpeg';

            $result = $this->mkwc_set_attachment( $data );

            return $result;
        }

        public function mkwc_set_attachment( $files )
        {
            $wordpress_upload_dir = wp_upload_dir();

            $i = 1;

            $file_url = '';

            $profile_image = $files['profile_Image'];

            $file_path  = ( isset( $profile_image['name'] ) && $profile_image['name'] ) ? $wordpress_upload_dir['path'] . '/' . $profile_image['name'] : '';

            $file_mime  = $profile_image['type'];

            if ( empty( $profile_image ) ) {
                return array(
                  'success' => false,
                  'message' => esc_html__( 'File is not selected.', 'mobikul-marketplace' )
                );
            }

            if ( $profile_image['error'] ) {
                return array(
                  'success' => false,
                  'message' => $profile_image['error']
                );
            }

            if ( $profile_image['size'] > wp_max_upload_size() ) {
                return array(
                  'success' => false,
                  'message' => esc_html__( 'It is too large than expected.', 'mobikul-marketplace' )
                );
            }

            if ( !in_array( $file_mime, get_allowed_mime_types() ) ){
                return array(
                  'success' => false,
                  'message' => esc_html__( 'WordPress doesn\'t allow this type of uploads.', 'mobikul-marketplace' )
                );
            }

            while ( file_exists( $file_path ) ) {
                $i++;

                $file_path = $wordpress_upload_dir['path'] . '/' . $i . '_' . $profile_image['name'];

                $file_url = $wordpress_upload_dir['baseurl'] . '/' . $i . '_' . $profile_image['name'];
            }
            // looks like everything is OK

            if ( move_uploaded_file( $profile_image['tmp_name'], $file_path ) ) {
              	$upload_id = wp_insert_attachment( array(
              		'guid'           => $file_url,
              		'post_mime_type' => $file_mime,
              		'post_title'     => preg_replace( '/\.[^.]+$/', '', $profile_image['name'] ),
              		'post_content'   => '',
              		'post_status'    => 'inherit'
              	), $file_path );

              	// wp_generate_attachment_metadata() won't work if you do not include this file
              	require_once( ABSPATH . 'wp-admin/includes/image.php' );

              	// Generate and save the attachment metas into the database
              	$result = wp_update_attachment_metadata( $upload_id, wp_generate_attachment_metadata( $upload_id, $file_path ) );

                if ( $result ) {
                    return array(
                        'success'   => true,
                        'message'   => '',
                        'image_id'  => $upload_id,
                        'file'      => $file_url ? $file_url : wp_get_attachment_url($upload_id),
                    );
                }
            } else {
                return array(
                    'success'   => false,
                    'message'   => esc_html__( 'Something went wrong. Please try agin!', 'mobikul-marketplace' ),
                );
            }
        }
    }
}
