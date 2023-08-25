<?php

/**
 * @author Webkul
 * @version 1.0.0
 * This file handles request authentication class.
 */

namespace WkMobikulMultivendorApi\Includes;

use WkMobikulMultivendorApi\Includes\Util;

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'Mkwc_Authentication' ) ) {
	/**
	 *
	 */
	class Mkwc_Authentication implements Util\Mkwc_Authentication_Interface {

		protected $response;

		/**
		 * Request authenticate function
		 */
		public function mkwc_authenticate_request() {
			$auth = apache_request_headers();

			return 'ok';

			if ( isset( $auth['authkey'] ) && $auth['authkey'] != '' && isset( $auth['user_key'] ) ) {
				if ( $this->mkwc_validate_session_id( $auth['authkey'], $auth['user_key'] ) ) {
					return 'ok';
				} else {
					$session_id_data   = $this->mkwc_generate_random_string();
					$digest_value_data = $this->mkwc_save_session_data( $session_id_data );
					return $session_id_data;
				}
			} else {
				$session_id_data   = $this->mkwc_generate_random_string();
				$digest_value_data = $this->mkwc_save_session_data( $session_id_data );
				return $session_id_data;
			}
		}

		/**
		 * Request user exist function
		 */
		public function mkwc_user_exist_request( $user_id ) {
			$user_id = (int) $user_id;
			if ( $user_id && is_numeric( $user_id ) ) {
				$user = get_user_by( 'id', $user_id );
				if ( ! $user ) {
					return array(
						'success'        => false,
						'user_not_exist' => true,
						'message'        => __( 'Invalid user id.', 'mobikul_marketplace' ),
					);
				}
			}
		}

		/**
		 * Save session data
		 *
		 * @param $session_id_data
		 */
		private function mkwc_save_session_data( $session_id_data ) {
			update_option( 'mkwc_api_wk_session_id', $session_id_data );

			return $session_id_data;
		}

		/**
		 * Validate received session id
		 *
		 * @param $auth_key
		 * @return bool
		 */
		private function mkwc_validate_session_id( $auth_key, $user_id ) {
			$user_id = $user_id ? $user_id : 0;

			$h1 = md5( get_option( 'mkwc_api_user_name' ) . ':' . get_option( 'mkwc_api_user_key' ) );

			$session_id_data_result = get_option( 'mkwc_api_wk_session_id' );

			$session_id_data_two = md5( $h1 . ':' . $session_id_data_result );

			$h2 = md5( $session_id_data_two . ':' . $user_id );

			if ( $h2 == $auth_key ) {
				return true;
			} else {
				return false;
			}
		}

		/**
		 * Generate random string
		 *
		 * @param $length
		 * @return $random_string
		 */
		function mkwc_generate_random_string( $length = 50 ) {
			$random_string = substr( str_shuffle( str_repeat( $x = '989213119013123abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ', ceil( $length / strlen( $x ) ) ) ), 1, $length );

			return $random_string;
		}
	}
}
