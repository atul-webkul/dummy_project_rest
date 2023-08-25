<?php

/**
 * @author Webkul
 * @version 1.0.0
 * This file handles all admin end action callbacks.
 */

namespace WkMobikulMultivendorApi\Includes\Front\Seller;

use WkMobikulMultivendorApi\Includes;
use WkMobikulMultivendorApi\Includes\Front\Seller\Util;
use WkMobikulMultivendorApi\Helper;

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'Mkwc_Dashboard' ) ) {
	/**
	 *
	 */
	class Mkwc_Dashboard extends Includes\Mkwc_Authentication implements Util\Mkwc_Dashboard_Interface {

		public static $base = 'seller/dashboard';

		protected $response;

		protected $general_data_handler;

		protected $seller_data_handler;
		public $seller_user_id;
		function mkwc_get_dashboard() {
			$validate_auth_key = $this->mkwc_authenticate_request();

			if ( $validate_auth_key != 'ok' ) {
				return array(
					'session_id'             => $validate_auth_key,
					'status'                 => 401,
					'invalid_auth_key_error' => __( 'Please provide valid Auth Key.', 'mobikul_marketplace' ),
					'success'                => false,
				);
			}
			$response_handler     = new \WP_REST_Response();
			$general_data_handler = new Helper\Mkwc_General_Data();
			$seller_data_handler  = new Helper\Mkwc_Seller_Data();

			$data         = array();
			$seller_id    = isset( $_GET['seller_id'] ) ? intval( $_GET['seller_id'] ) : '';
			$seller_check = $this->mkwc_user_exist_request( $seller_id );
			if ( isset( $seller_check['success'] ) && ! $seller_check['success'] ) {
				return $seller_check;
			}

			$time = isset( $_GET['sort'] ) ? $_GET['sort'] : 'year';

			$sort_array = array(
				'year',
				'month',
				'7day',
				'last_month',
			);

			if ( ! in_array( $time, $sort_array, true ) ) {
				$time = 'year';
			}

			$this->seller_user_id = $seller_id;
			// $total_sales = $seller_data_handler->mkwc_get_total_seller_order_amount( $seller_id );

			$summary_data  = $seller_data_handler->mkwc_get_seller_sale_stats( $seller_id );
			$total_payout  = isset( $summary_data->paid_amount ) ? $summary_data->paid_amount : 0;
			$total_sales   = isset( $summary_data->seller_total_ammount ) ? $summary_data->seller_total_ammount : 0;
			$remaining_amt = $total_sales - $total_payout;
			$total_refund  = isset( $summary_data->total_refunded_amount ) ? $summary_data->total_refunded_amount : 0;
			$sale_history  = $seller_data_handler->mkwc_seller_sale_order_history( $seller_id, $time );
			$sale_history  = array_merge( $sale_history, array( 'sort_array' => $sort_array ) );
			$sale_history  = array_merge( $sale_history, array( 'sort' => $time ) );

			$data = array_merge(
				$data,
				array(
					'total_sale'       => $general_data_handler->mkwc_get_formatted_price( $this->mp_get_total_order_amount() ),
					'total_payout'     => $general_data_handler->mkwc_get_formatted_price( $total_payout ),
					'remaining_amount' => $general_data_handler->mkwc_get_formatted_price( $remaining_amt ),
					'total_refund'     => $general_data_handler->mkwc_get_formatted_price( $total_refund ),
					'total_products'   => $seller_data_handler->mkwc_get_seller_product_count( $seller_id ),
					'total_orders'     => $seller_data_handler->mkwc_get_seller_order_count( $seller_id ),
					'sale_history'     => $sale_history,
					'recent_orders'    => $seller_data_handler->mkwc_get_seller_orders( $seller_id, 10, 1 ),
					'success'          => true,
					'message'          => '',
				)
			);
			$data = array_merge( $data, $seller_data_handler->mkwc_seller_top_selling_products( $seller_id ) );
			$response_handler->set_data( $data );
			return $response_handler;
		}


		public function getOrderItemId() {
			global $wpdb;
			$sql      = "select woitems.order_item_id,woitems.order_id from {$wpdb->prefix}woocommerce_order_itemmeta woi join {$wpdb->prefix}woocommerce_order_items woitems on woitems.order_item_id=woi.order_item_id join {$wpdb->prefix}posts post on woi.meta_value=post.ID where woi.meta_key='_product_id' and post.ID=woi.meta_value and post.post_author='" . $this->seller_user_id . "' GROUP BY order_id";
			$result   = $wpdb->get_results( $sql );
			$ID       = array();
			$order_ID = array();
			foreach ( $result as $res ) {
				$ID[]       = $res->order_item_id;
				$order_ID[] = $res->order_id;
			}
			return implode( ',', $ID );
		}


		/*total order amount*/
		function mp_get_total_order_amount() {

			global $wpdb;

			$postid = $this->getOrderItemId();

			// $postid = $p_id['id'][0];
			if ( $postid ) {
				$sql = "select sum(meta_value) AS 'total_order_amount' from {$wpdb->prefix}woocommerce_order_itemmeta where meta_key='_line_total' and order_item_id in($postid)";

				return $wpdb->get_var( $sql );
			}
		}









	}
}
