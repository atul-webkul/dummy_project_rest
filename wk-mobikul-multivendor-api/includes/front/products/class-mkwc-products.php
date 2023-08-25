<?php

/**
 * @author Webkul
 * @version 1.0.0
 * This file handles products api functions.
 */

namespace WkMobikulMultivendorApi\Includes\Front\Products;

use WkMobikulMultivendorApi\Includes;
use WkMobikulMultivendorApi\Includes\Front\Products\Util;
use WkMobikulMultivendorApi\Factory;
use WkMobikulMultivendorApi\Helper;

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'Mkwc_Products' ) ) {
	/**
	 *
	 */
	class Mkwc_Products extends Includes\Mkwc_Authentication implements Util\Mkwc_Products_Interface {

		public static $base = 'products';

		protected $response;

		/**
		 * Store product list endpoint callback
		 */
		public function mkwc_get_products() {
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
			$data             = array();
			$page             = isset( $_GET['page'] ) ? intval( $_GET['page'] ) : '';
			$width            = isset( $_GET['width'] ) ? intval( $_GET['width'] ) : '';
			$per_page         = get_option( 'posts_per_page' );

			if ( ! $page ) {
				return array(
					'success' => 0,
					'message' => __( 'Sorry, you must provide all parameters.', 'mobikul_marketplace' ),
				);
			}

			$products = Factory\Mkwc_Product_Factory::mkwc_get_products( $page, $per_page, $width );

			if ( $products ) {
				$data = array_merge( $data, $products );
				$data = array_merge(
					$data,
					array(
						'success' => 1,
						'message' => '',
					)
				);
			} else {
				$data = array_merge(
					$data,
					array(
						'success' => 0,
						'message' => __( 'No products found.', 'mobikul_marketplace' ),
					)
				);
			}

			$response_handler->set_data( $data );

			return $response_handler;
		}

		/**
		 * Get product by id
		 *
		 * @param $data
		 * @return $product
		 */
		public function mkwc_get_product( $data ) {

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

			$response_data = array();

			$product_id = ( is_object( $data ) ) ? (int) $data->get_param( 'product_id' ) : $data;

			$width = isset( $_GET['width'] ) ? intval( $_GET['width'] ) : '';

			$product = Factory\Mkwc_Product_Factory::mkwc_get_prepared_product( $product_id, $width );

			if ( ! empty( $product ) ) {
				$response_data = array_merge(
					$response_data,
					array(
						'success' => 1,
						'message' => '',
						'product' => $product,
					)
				);
			} else {

				$response_data = array_merge(
					$response_data,
					array(
						'success' => 0,
						'message' => __( 'No data found.', 'mobikul_marketplace' ),
					)
				);
			}

			$response_handler->set_data( $response_data );

			return $response_handler;
		}

		/**
		 * Get product by search query endpoint callback
		 *
		 * @return $products
		 */
		public function mkwc_get_search_product() {
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

			$data = array();

			$page = isset( $_GET['page'] ) ? intval( $_GET['page'] ) : '';

			$s = isset( $_GET['s'] ) ? filter_var( $_GET['s'], FILTER_SANITIZE_STRING ) : '';

			$width = isset( $_GET['width'] ) ? intval( $_GET['width'] ) : '';

			$per_page = get_option( 'posts_per_page' );

			if ( ! $page || ! $s ) {
				return array(
					'success' => 0,
					'message' => __( 'Sorry, you must provide all parameters.', 'mobikul_marketplace' ),
				);
			}

			$products = Factory\Mkwc_Product_Factory::mkwc_get_search_products( $s, $page, $per_page, $width );

			if ( $products ) {
				$data = array_merge( $data, $products );
				$data = array_merge(
					$data,
					array(
						'success' => 1,
						'message' => '',
					)
				);
			} else {
				$data = array_merge(
					$data,
					array(
						'success' => 0,
						'message' => __( 'No products found.', 'mobikul_marketplace' ),
					)
				);
			}

			$response_handler->set_data( $data );

			return $response_handler;
		}


		public function mkwc_get_search_suggestion_product() {
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

			$data = array();

			$page = isset( $_GET['page'] ) ? intval( $_GET['page'] ) : '';

			$s = isset( $_GET['s'] ) ? filter_var( $_GET['s'], FILTER_SANITIZE_STRING ) : '';

			$width = isset( $_GET['width'] ) ? intval( $_GET['width'] ) : '';

			$per_page = get_option( 'posts_per_page' );

			if ( ! $page || ! $s ) {
				return array(
					'success' => 0,
					'message' => __( 'Sorry, you must provide all parameters.', 'mobikul_marketplace' ),
				);
			}

			$products = Factory\Mkwc_Product_Factory::mkwc_get_search_suggestion_products( $s, $page, $per_page, $width );

			if ( $products ) {
				$data = array_merge( $data, $products );
				$data = array_merge(
					$data,
					array(
						'success' => 1,
						'message' => '',
					)
				);
			} else {
				$data = array_merge(
					$data,
					array(
						'success' => 0,
						'message' => __( 'No products found.', 'mobikul_marketplace' ),
					)
				);
			}

			$response_handler->set_data( $data );

			return $response_handler;
		}

		/**
		 * Get product review by product id
		 *
		 * @param $data
		 * @return $reviews
		 */
		public function mkwc_get_product_reviews( $data ) {
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

			$reviews = array();

			$product_id = ( is_object( $data ) ) ? (int) $data->get_param( 'product_id' ) : $data;

			if ( ! wc_get_product( $product_id ) ) {
				return array(
					'success' => false,
					'message' => __( 'Invalid product id.', 'mobikul_marketplace' ),
				);
			}

			$comments = get_approved_comments( $product_id );

				$reviews = array();

			foreach ( $comments as $comment ) {
				$created_at = gmdate( 'F d Y', strtotime( $comment->comment_date_gmt ) );
				$reviews[]  = array(
					'id'             => $comment->comment_ID,
					'created_at'     => $created_at,
					'review'         => $comment->comment_content,
					'rating'         => get_comment_meta( $comment->comment_ID, 'rating', true ),
					'reviewer_name'  => $comment->comment_author,
					'reviewer_email' => $comment->comment_author_email,
					'verified'       => wc_review_is_from_verified_owner( $comment->comment_ID ),
				);
			}

			if ( $reviews ) {
				$review_count = array_column( $reviews, 'rating' );

				$review_count = array_count_values( $review_count );

				$review_graph = array(
					'start_one'   => 0,
					'start_two'   => 0,
					'start_three' => 0,
					'start_four'  => 0,
					'start_five'  => 0,
				);

				if ( isset( $review_count[1] ) && $review_count[1] ) {
					$review_graph['start_one'] = $review_count[1];
				}

				if ( isset( $review_count[2] ) && $review_count[2] ) {
					$review_graph['start_two'] = $review_count[2];
				}

				if ( isset( $review_count[3] ) && $review_count[3] ) {
					$review_graph['start_three'] = $review_count[3];
				}

				if ( isset( $review_count[4] ) && $review_count[4] ) {
					$review_graph['start_four'] = $review_count[4];
				}

				if ( isset( $review_count[5] ) && $review_count[5] ) {
					$review_graph['start_five'] = $review_count[5];
				}

				$product = wc_get_product( $product_id );

				$data = array(
					'success'        => true,
					'message'        => '',
					'reviews'        => $reviews,
					'review_graph'   => $review_graph,
					'average_rating' => wc_format_decimal( $product->get_average_rating(), 2 ),
					'rating_count'   => (int) $product->get_rating_count(),
				);
			} else {
				$data = array(
					'success' => false,
					'message' => __( 'No reviews found.', 'mobikul_marketplace' ),
				);
			}

			$response_handler->set_data( $data );

			return $response_handler;
		}


		/**
		 * Set product review
		 *
		 * @param $post_data object
		 * @return $status
		 */
		public function mkwc_set_product_review( $data ) {
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

			$data = $data->get_params();

			$product_id = $data['product_id'];

			if ( ! wc_get_product( $product_id ) ) {
				return array(
					'success' => false,
					'message' => __( 'Invalid product id.', 'mobikul_marketplace' ),
				);
			}

			$uname   = isset( $data['name'] ) ? filter_var( $data['name'], FILTER_SANITIZE_STRING ) : '';
			$uemail  = isset( $data['email'] ) ? filter_var( $data['email'], FILTER_VALIDATE_EMAIL ) : '';
			$urating = ( isset( $data['rating'] ) && is_numeric( $data ['rating'] ) ) ? $data['rating'] : '';
			$ureview = isset( $data['review'] ) ? filter_var( $data['review'], FILTER_SANITIZE_STRING ) : '';

			if ( ! $uname || ! $uemail || ! $urating || ! $ureview ) {
					return array(
						'success' => false,
						'message' => __( 'Sorry, you must provide all parameters.', 'mobikul_marketplace' ),
					);
			}

			$comment_post_id      = $product_id;
			$comment_author       = $uname;
			$comment_author_email = $uemail;
			$comment_content      = $ureview;
			$comment_rating       = $urating;
			$time                 = current_time( 'mysql' );

			$user = get_user_by( 'email', $uemail );

			if ( ! empty( $user ) ) {
				$user_id = $user->ID;

				if ( array_key_exists( 'administrator', $user->caps ) ) {
					$comment_approved = 1;
				} else {
					$comment_approved = 1;
				}
			} else {
				$user_id          = 0;
				$comment_approved = 1;
			}

			if ( ! empty( $_SERVER['HTTP_X_FORWARDED_FOR'] ) ) {
				$comment_author_ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
			} else {
				$comment_author_ip = $_SERVER['REMOTE_ADDR'];
			}

			$data = array(
				'comment_post_ID'      => $comment_post_id,
				'comment_author'       => $comment_author,
				'comment_author_email' => $comment_author_email,
				'comment_author_url'   => '',
				'comment_content'      => $comment_content,
				'comment_type'         => '',
				'comment_parent'       => 0,
				'user_id'              => $user_id,
				'comment_author_IP'    => $comment_author_ip,
				'comment_agent'        => 'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.9.0.10) Gecko/2009042316 Firefox/3.0.10 (.NET CLR 3.5.30729)',
				'comment_date'         => $time,
				'comment_approved'     => $comment_approved,
			);

			$result = wp_insert_comment( $data );

			$response_data = array();

			update_comment_meta( $result, 'rating', $comment_rating );
			update_comment_meta( $result, 'verified', 0 );

			if ( $result ) {
				$response_data = array_merge(
					$response_data,
					array(
						'success' => true,
						'message' => __( 'Review added succesfully. Your review is awaiting approval', 'mobikul_marketplace' ),
					)
				);
			} else {
				$response_data = array_merge(
					$response_data,
					array(
						'success' => false,
						'message' => __( 'Review not added.', 'mobikul_marketplace' ),
					)
				);
			}

			$response_handler->set_data( $response_data );

			return $response_handler;
		}
	}
}
