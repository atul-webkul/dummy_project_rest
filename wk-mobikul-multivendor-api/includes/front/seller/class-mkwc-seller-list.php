<?php

/**
 * @author Webkul
 * @version 1.0.0
 * This file handles seller list functions.
 */

namespace WkMobikulMultivendorApi\Includes\Front\Seller;

use WkMobikulMultivendorApi\Includes;
use WkMobikulMultivendorApi\Includes\Front\Seller;
use WkMobikulMultivendorApi\Helper;

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'Mkwc_Seller_List' ) ) {
	/**
	 * Seller list
	 */
	class Mkwc_Seller_List extends Includes\Mkwc_Authentication implements Util\Mkwc_Seller_List_Interface {

		public static $base = 'seller/list';

		protected $seller_data_handler;

		protected $mp_form_handler;

		protected $wpdb;

		public function __construct() {
			global $wpdb;

			$this->wpdb = $wpdb;
		}

		/**
		 * Seller list
		 */
		public function mkwc_seller_list() {
			$validate_auth_key = $this->mkwc_authenticate_request( 0 );

			if ( $validate_auth_key != 'ok' ) {
				return array(
					'session_id'             => $validate_auth_key,
					'status'                 => 401,
					'invalid_auth_key_error' => __( 'Please provide valid Auth Key.', 'mobikul_marketplace' ),
					'success'                => false,
				);
			}
			$seller_data_handler = new Helper\Mkwc_Seller_Data();

			$seller_shop_handler = new Seller\Mkwc_Seller_Shop_Details();

			$mp_form_handler = new Seller\Mkwc_Form_Handler();

			$seller_ids = $seller_data_handler->mkwc_seller_ids();

			$seller_list = array();

			foreach ( $seller_ids as $row ) {
						$user_id = $row->user_id;

				$seller_data = $seller_shop_handler->mkwc_get_seller_details( $user_id );

				$seller_name = isset( $seller_data['first_name'] ) ? $seller_data['first_name'] . ' ' . $seller_data['last_name'] : $seller_data['nickname'];

				$shop_logo = $mp_form_handler->get_user_avatar( $user_id, 'company_logo' );

				$shop_logo = isset( $shop_logo['0']->meta_value ) ? $shop_logo['0']->meta_value : '';

				$shop_logo = ! empty( $shop_logo ) ? wp_upload_dir()['baseurl'] . '/' . $shop_logo : '';

				$seller_products = $seller_data_handler->mkwc_seller_products( $user_id );

				$seller_products = isset( $seller_products[0]->id ) ? $seller_products[0]->id : '';

				$average_rating = $this->mkwc_average_rating( $user_id );

				$average_rating = isset( $average_rating ) ? $average_rating : '';

				$seller_list[] = array(
					'id'             => $user_id,
					'name'           => $seller_name,
					'logo'           => $shop_logo,
					'average_rating' => $average_rating,
					'product_count'  => $seller_products,
				);
			}

			if ( $seller_list ) {
				return array(
					'seller'  => $seller_list,
					'success' => true,
					'message' => '',
				);
			} else {
				return array(
					'seller'  => '',
					'success' => false,
					'message' => __( 'No seller found.', 'mobikul_marketplace' ),
				);
			}
		}

		/**
		 * Get review.
		 *
		 * @param int $id id.
		 */
		public function get_review( $id ) {
			global $wpdb;

			return $wpdb->get_results( $wpdb->prepare( "SELECT * from {$wpdb->prefix}mpfeedback where seller_id = %d and status = %d order by review_time desc limit 3", $id, 1 ) );
		}

		/**
		 * Seller average rating.
		 */
		public function mkwc_average_rating( $seller_id ) {
			$average_rating = array();

			$review_data = $this->get_review( $seller_id );

			$num_of_stars = $total_feedback = $price_stars = $value_stars = $quality_stars = 0;

			if ( $review_data ) {
				foreach ( $review_data as $item ) {
					$num_of_stars += $item->price_r;

					$price_stars += $item->price_r;

					$num_of_stars += $item->value_r;

					$value_stars += $item->value_r;

					$num_of_stars += $item->quality_r;

					$quality_stars += $item->quality_r;

					$total_feedback++;
				}
			}

			if ( $num_of_stars != 0 ) {
				$quality = $num_of_stars / ( $total_feedback * 3 );

				$price_stars /= $total_feedback;

				$value_stars /= $total_feedback;

				$quality_stars /= $total_feedback;
			} else {
				$quality = 0;
			}

			$average_rating = array(
				'average'     => number_format( $quality, 2 ),
				'price'       => number_format( $price_stars, 2 ),
				'value'       => number_format( $value_stars, 2 ),
				'quality'     => number_format( $quality_stars, 2 ),
				'total_count' => esc_attr( $total_feedback ),
			);

			return $average_rating;
		}
	}
}
