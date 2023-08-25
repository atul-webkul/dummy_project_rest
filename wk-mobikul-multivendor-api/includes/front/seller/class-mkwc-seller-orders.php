<?php

/**
 * @author Webkul
 * @version 1.0.0
 * This file handles seller order functions.
 */

namespace WkMobikulMultivendorApi\Includes\Front\Seller;

use WkMobikulMultivendorApi\Includes;
use WkMobikulMultivendorApi\Includes\Front\Seller\Util;
use WkMobikulMultivendorApi\Helper;

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'Mkwc_Seller_Orders' ) ) {
	/**
	 *
	 */
	class Mkwc_Seller_Orders extends Includes\Mkwc_Authentication implements Util\Mkwc_Seller_Orders_Interface {

		public static $base = 'seller/order';

		protected $response;

		protected $general_data_handler;

		protected $seller_data_handler;

		/**
		 * Seller order list endpoint callback.
		 */
		public function mkwc_get_seller_orders_list() {
			$validate_auth_key = $this->mkwc_authenticate_request();

			if ( $validate_auth_key != 'ok' ) {
				return array(
					'session_id'             => $validate_auth_key,
					'status'                 => 401,
					'invalid_auth_key_error' => __( 'Please provide valid Auth Key.', 'mobikul_marketplace' ),
					'success'                => false,
				);
			}

			$response_handler = new \WP_REST_Response();

			$seller_data_handler = new Helper\Mkwc_Seller_Data();

			$orders = array();

			$seller_id = isset( $_GET['seller_id'] ) ? intval( $_GET['seller_id'] ) : '';

			$page = isset( $_GET['page'] ) ? intval( $_GET['page'] ) : '';

			$per_page = get_option( 'posts_per_page' );

			$seller_check = $this->mkwc_user_exist_request( $seller_id );

			if ( isset( $seller_check['success'] ) && ! $seller_check['success'] ) {
				return $seller_check;
			}

			if ( ! $page ) {
				return array(
					'success' => 0,
					'message' => __( 'Sorry, you must provide all parameters.', 'mobikul_marketplace' ),
				);
			}

			$orders = $seller_data_handler->mkwc_get_seller_order_list( $seller_id, $page, $per_page );
			$data   = array();
			if ( $orders ) {
				$data = $orders;
			} else {
				$data = array_merge(
					$data,
					array(
						'success' => false,
						'message' => __( 'No orders found.', 'mobikul_marketplace' ),
					)
				);
			}

			$response_handler->set_data( $data );

			return $response_handler;
		}

		/**
		 * Get seller order details by id
		 *
		 * @param $params
		 */
		public function mkwc_get_order_by_id( $params ) {
			$validate_auth_key = $this->mkwc_authenticate_request();

			if ( $validate_auth_key != 'ok' ) {
				return array(
					'session_id'             => $validate_auth_key,
					'status'                 => 401,
					'invalid_auth_key_error' => __( 'Please provide valid Auth Key.', 'mobikul_marketplace' ),
					'success'                => false,
				);
			}

			$response_handler = new \WP_REST_Response();

			$seller_data_handler = new Helper\Mkwc_Seller_Data();

			$data = array();

			$order_id = ( is_object( $params ) ) ? (int) $params->get_param( 'order_id' ) : $params;

			$seller_id = isset( $_GET['seller_id'] ) ? intval( $_GET['seller_id'] ) : '';

			$seller_check = $this->mkwc_user_exist_request( $seller_id );

			if ( isset( $seller_check['success'] ) && ! $seller_check['success'] ) {
				return $seller_check;
			}

			$data = $seller_data_handler->mkwc_get_seller_order_by_id( $seller_id, $order_id );

			$response_handler->set_data( $data );

			return $response_handler;
		}

		public function mkwc_update_order_status( $data ) {

			$validate_auth_key = $this->mkwc_authenticate_request();

			if ( $validate_auth_key != 'ok' ) {
				return array(
					'session_id'             => $validate_auth_key,
					'status'                 => 401,
					'invalid_auth_key_error' => __( 'Please provide valid Auth Key.', 'mobikul_marketplace' ),
					'success'                => false,
				);
			}

			global $wpdb;

			$table_name = $wpdb->prefix . 'mpseller_orders';

			$data = is_object( $data ) ? $data->get_param( 'order' ) : $data;

			$order_id = isset( $data['order_id'] ) ? $data['order_id'] : '';

			$seller_id = isset( $data['seller_id'] ) ? $data['seller_id'] : '';

			$order_status = isset( $data['order_status'] ) ? $data['order_status'] : '';

			$old_status = isset( $data['old_order_status'] ) ? $data['old_order_status'] : '';

			if ( empty( $order_id ) || empty( $seller_id ) || empty( $order_status ) || empty( $old_status ) ) {
				return array(
					'success' => false,
					'message' => __( 'Sorry, You must provide all the parameters.', 'mobikul_marketplace' ),
				);
			}

			$order = wc_get_order( $order_id );
			$items = $order->get_items();

			foreach ( $items as $key => $value ) {
				$author_array[] = get_post_field( 'post_author', $value->get_product_id() );
			}

			$order_author_count = count( $author_array );

			if ( empty( $order_status ) ) {

				return array(
					'success' => false,
					'message' => __( 'Select status for order.', 'mobikul_marketplace' ),
				);

			} elseif ( $order_status == $old_status ) {
				return array(
					'success' => false,
					'message' => __( 'Order status is already.', 'mobikul_marketplace' ),
				);

			} else {

				$sql = $wpdb->update(
					$table_name,
					array(
						'order_status' => $order_status,
					),
					array(
						'order_id'  => $order_id,
						'seller_id' => $seller_id,
					),
					array(
						'%s',
					),
					array(
						'%d',
						'%d',
					)
				);

				if ( $sql ) {

					$author_name = get_user_by( 'ID', $seller_id );

					$status_array = wc_get_order_statuses();

					$author_name = $author_name->user_nicename;

					$note = __( "Vendor `{$author_name}` changed Order Status from {$status_array[$old_status]} to {$status_array[$order_status]} for it's own products." );

					$query = $wpdb->prepare( "SELECT count(*) as total from $table_name where order_id = '%d' and order_status = '%s'", $order_id, $order_status );

					$query_result = $wpdb->get_results( $query );

					$order->update_status( $order_status, __( "Status updated to {$status_array[$order_status]} based on status updated by vendor's.", 'marketplace' ) );

				}

				return array(
					'success' => true,
					'message' => __( 'Order status updated.', 'mobikul_marketplace' ),
				);

			}

		}
	}
}
