<?php

/**
 * @author Webkul
 * @version 1.0.0
 * This file handles seller shop Collection functions.
 */

namespace WkMobikulMultivendorApi\Includes\Front\Seller;

use WkMobikulMultivendorApi\Includes;
use WkMobikulMultivendorApi\Includes\Front\Seller\Util;
use WkMobikulMultivendorApi\Helper;
use WkMobikulMultivendorApi\Factory;

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'Mkwc_Seller_Shop_Collection' ) ) {
	/**
	 *
	 */
	class Mkwc_Seller_Shop_Collection extends Includes\Mkwc_Authentication implements Util\Mkwc_Seller_Shop_Collection_Interface {

		public static $base = 'seller/collection';

		protected $seller_data_handler;

		/**
		 * Seller collection list
		 */
		public function mkwc_get_seller_shop_collection() {
			$validate_auth_key = $this->mkwc_authenticate_request();

			if ( $validate_auth_key != 'ok' ) {
				return array(
					'session_id'             => $validate_auth_key,
					'status'                 => 401,
					'invalid_auth_key_error' => __( 'Please provide valid Auth Key.', 'mobikul_marketplace' ),
					'success'                => false,
				);
			}

			$seller_data_handler = new Helper\Mkwc_Seller_Data();

			$factory_data_handler = new Factory\Mkwc_Product_Factory();

			$seller_id = isset( $_GET['seller_id'] ) ? $_GET['seller_id'] : '';

			$width = isset( $_GET['width'] ) ? $_GET['width'] : '';

			$page = isset( $_GET['page'] ) ? $_GET['page'] : '';

			$seller_check = $this->mkwc_user_exist_request( $seller_id );

			if ( isset( $seller_check['success'] ) && ! $seller_check['success'] ) {
				return $seller_check;
			}

			if ( empty( $seller_id ) || empty( $width ) || empty( $page ) ) {
				return array(
					'success' => false,
					'message' => __( 'Sorry, provide all the parameters.', 'mobikul_marketplace' ),
				);
			}

			$per_page = 6;

			$products = $seller_data_handler->mkwc_get_seller_products( $seller_id, $page, $per_page );

			$products_item = isset( $products['products'] ) ? $products['products'] : '';

			$total_products = isset( $products['total_products'] ) ? $products['total_products'] : '';

			$sort_label = $factory_data_handler->mkwc_products_sort_label();

						$filter_data = array(
							'filter_seller_id' => $seller_id,
							'status'           => 1,
							'start'            => 0,
							'limit'            => $this->wkmpapi_get_seller_total_feedbacks(
								array(
									'filter_seller_id' => $seller_id,
									'status'           => 1,
								)
							),
						);

						$reviews = $this->wkmpapi_get_seller_feedbacks( $filter_data );

						// $num_of_stars   = 0;
						// $total_feedback = 0;
						// $price_stars    = 0;
						// $value_stars    = 0;
						// $quality_stars  = 0;

						// if ( $reviews ) {
						// foreach ( $reviews as $item ) {
						// $num_of_stars  += $item->price_r;
						// $price_stars   += $item->price_r;
						// $num_of_stars  += $item->value_r;
						// $value_stars   += $item->value_r;
						// $num_of_stars  += $item->quality_r;
						// $quality_stars += $item->quality_r;
						// $total_feedback ++;
						// }
						// }

						// $quality = 0;
						// if ( $num_of_stars > 0 ) {
						// $quality = $num_of_stars / ( $total_feedback * 3 );

						// $price_stars   /= $total_feedback;
						// $value_stars   /= $total_feedback;
						// $quality_stars /= $total_feedback;
						// }

						// $rating_avg = ( $price_stars + $value_stars + $quality_stars ) / 3;

						// $seller_rating = array(
						// 'reviews'        => $reviews,
						// 'num_of_stars'   => $num_of_stars,
						// 'total_feedback' => $total_feedback,
						// 'price_stars'    => $price_stars,
						// 'value_stars'    => $value_stars,
						// 'quality'        => $quality,
						// 'quality_stars'  => $quality_stars,
						// 'rating_avg'     => $rating_avg,
						// );

						return array(
							'products'       => ! empty( $products_item ) ? $products_item : '',
							'data_displayed' => ! empty( $products_item ) ? count( $products_item ) : '',
							'total'          => ! empty( $total_products ) ? $total_products : '',
							'sort_label'     => ! empty( $sort_label ) ? $sort_label : '',
							'success'        => ! empty( $products_item ) ? true : false,
							'message'        => ! empty( $products_item ) ? '' : __( 'No product found.', 'mobikul_marketplace' ),
						);
		}

				/**
				 * Get total Seller feedback
				 *
				 * @param array $filter_data Filter data.
				 *
				 * @return int $total
				 */
		public function wkmpapi_get_seller_total_feedbacks( $filter_data ) {
			global $wpdb;
			$sql = "SELECT COUNT(*) FROM {$wpdb->prefix}mpfeedback WHERE ID > 0";

			if ( isset( $filter_data['filter_name'] ) && $filter_data['filter_name'] ) {
				$sql .= $wpdb->prepare( ' AND review_summary LIKE %s', '%' . esc_attr( $filter_data['filter_name'] ) . '%' );
			}

			if ( isset( $filter_data['filter_seller_id'] ) && $filter_data['filter_seller_id'] ) {
				$sql .= $wpdb->prepare( ' AND seller_id = %d', esc_attr( $filter_data['filter_seller_id'] ) );
			}

			if ( isset( $filter_data['filter_user_id'] ) && $filter_data['filter_user_id'] ) {
				$sql .= $wpdb->prepare( ' AND user_id = %d', esc_attr( $filter_data['filter_user_id'] ) );
			}

			if ( isset( $filter_data['status'] ) && $filter_data['status'] ) {
				$sql .= $wpdb->prepare( ' AND status = %d', esc_attr( $filter_data['status'] ) );
			}

			$total = $wpdb->get_var( $sql );

			return apply_filters( 'wkmpapi_get_seller_total_feedbacks', $total );
		}



		/**
		 * Get Seller feedback.
		 *
		 * @param array $filter_data Filter data.
		 *
		 * @return array $feedbacks
		 */
		public function wkmpapi_get_seller_feedbacks( $filter_data ) {
			global $wpdb;
			$sql = "SELECT * FROM {$wpdb->prefix}mpfeedback WHERE ID > 0";

			if ( isset( $filter_data['filter_name'] ) && $filter_data['filter_name'] ) {
				$sql .= $wpdb->prepare( ' AND review_summary LIKE %s', '%' . esc_attr( $filter_data['filter_name'] ) . '%' );
			}

			if ( isset( $filter_data['filter_seller_id'] ) && $filter_data['filter_seller_id'] ) {
				$sql .= $wpdb->prepare( ' AND seller_id = %d', esc_attr( $filter_data['filter_seller_id'] ) );
			}

			if ( isset( $filter_data['filter_user_id'] ) && $filter_data['filter_user_id'] ) {
				$sql .= $wpdb->prepare( ' AND user_id = %d', esc_attr( $filter_data['filter_user_id'] ) );
			}

			if ( isset( $filter_data['status'] ) && $filter_data['status'] ) {
				$sql .= $wpdb->prepare( ' AND status = %d', esc_attr( $filter_data['status'] ) );
			}

			$sql .= ' ORDER BY review_time DESC';

			if ( isset( $filter_data['start'] ) && isset( $filter_data['limit'] ) ) {
				$sql .= $wpdb->prepare( ' LIMIT %d, %d', esc_attr( $filter_data['start'] ), esc_attr( $filter_data['limit'] ) );
			}

			$feedbacks = $wpdb->get_results( $sql );

			return apply_filters( 'wkmp_get_seller_feedbacks', $feedbacks );
		}



	}
}
