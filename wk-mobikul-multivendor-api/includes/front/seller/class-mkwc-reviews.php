<?php

/**
 * @author Webkul
 * @version 1.0.0
 * This file handles products api functions.
 */

namespace WkMobikulMultivendorApi\Includes\Front\Seller;

use WkMobikulMultivendorApi\Includes;
use WkMobikulMultivendorApi\Helper;

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'Mkwc_Reviews' ) ) {
	/**
	 *
	 */
	class Mkwc_Reviews extends Includes\Mkwc_Authentication {

		public static $base = 'seller/reviews';

		protected $response;

		/**
		 * Set product review
		 *
		 * @param $post_data object
		 * @return $status
		 */
		public function mkwc_set_seller_review( $data ) {
			$validate_auth_key = $this->mkwc_authenticate_request( 0 );
			if ( $validate_auth_key != 'ok' ) {
				return array(
					'session_id'             => $validate_auth_key,
					'status'                 => 401,
					'invalid_auth_key_error' => __( 'Please provide valid Auth Key.', 'mobikul_marketplace' ),
					'success'                => false,
				);
			}
			$response_handler = new \WP_REST_Response();
			$data             = $data->get_params();
			$seller_id        = $data['seller_id'];
			$seller_check     = $this->mkwc_user_exist_request( $seller_id );
			if ( isset( $seller_check['success'] ) && ! $seller_check['success'] ) {
				return $seller_check;
			}
			$user_id    = ( isset( $data['user_id'] ) && is_numeric( $data ['user_id'] ) ) ? $data['user_id'] : 0;
			$user_check = $this->mkwc_user_exist_request( $user_id );

			if ( isset( $user_check['success'] ) && ! $user_check['success'] ) {
				return $user_check;
			}

			$user_details    = get_user_by( 'ID', $user_id );
			$nickname        = $user_details->display_name;
			$price_rating    = ( isset( $data['feed_price'] ) && is_numeric( $data ['feed_price'] ) ) ? $data['feed_price'] : 0;
			$value_rating    = ( isset( $data['feed_value'] ) && is_numeric( $data ['feed_value'] ) ) ? $data['feed_value'] : 0;
			$quality_rating  = ( isset( $data['feed_quality'] ) && is_numeric( $data ['feed_quality'] ) ) ? $data['feed_quality'] : 0;
			$ureview_subject = isset( $data['review_subject'] ) ? filter_var( $data['review_subject'], FILTER_SANITIZE_STRING ) : '';
			$ureview         = isset( $data['review'] ) ? filter_var( $data['review'], FILTER_SANITIZE_STRING ) : '';

			if ( ! $price_rating || ! $value_rating || ! $quality_rating || ! $ureview_subject || ! $ureview ) {
					return array(
						'success' => false,
						'message' => __( 'Sorry, you must provide all parameters.', 'mobikul_marketplace' ),
					);
			}

			$review_status = apply_filters( 'wkmp_api_default_seller_review_status', 0, $data );

			$data = array(
				'mp_wk_seller' => $seller_id,
				'mp_wk_user'   => $user_id,
				'feed_price'   => $price_rating,
				'feed_value'   => $value_rating,
				'feed_quality' => $quality_rating,
				'nickname'     => $nickname,
				'feed_summary' => $ureview_subject,
				'feed_review'  => $ureview,
				'status'       => $review_status,
			);

			$seller_review_handler = new Helper\Mkwc_Seller_Feedback();
			$seller_review_handler->wkmp_insert_seller_feedback( $data, $seller_id );

			$response_data = array();

			$response_data = array_merge(
				$response_data,
				array(
					'success' => true,
					'message' => __( 'Review added succesfully. Your review is awaiting approval', 'mobikul_marketplace' ),
				)
			);

			$response_handler->set_data( $response_data );

			return $response_handler;
		}
	}
}
