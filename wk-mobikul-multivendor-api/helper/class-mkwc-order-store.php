<?php

/**
 * @author Webkul
 * @version 1.0.0
 * This file handles order store functions.
 */

namespace WkMobikulMultivendorApi\Helper;

use WkMobikulMultivendorApi\Helper;

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'Mkwc_Order_store' ) ) {
	/**
	 *
	 */
	class Mkwc_Order_store implements Util\Mkwc_Order_Store_Interface {

		protected $wpdb;

		public function __construct() {
			global $wpdb;

			$this->wpdb = $wpdb;
		}

		/**
		 * Get orders by customer id
		 *
		 * @param $customer_id
		 * @return $order_ids
		 */
		public function mkwc_get_customer_orders( $customer_id ) {
			$product_store        = new Helper\Mkwc_Product_store();
			$general_data_handler = new Helper\Mkwc_General_Data();
			$customer_order_ids   = array();
			$width                = isset( $_GET['width'] ) ? intval( $_GET['width'] ) : '';
			$mFactor              = isset( $_GET['mFactor'] ) ? $_GET['mFactor'] : 1;
			$height               = 0.5 * $width;
			$height              *= $mFactor;
			$width               *= $mFactor;

			$size = array(
				'width'  => floatval( $width ),
				'height' => floatval( $height ),
				'crop'   => 1,
			);

			$customer_orders = get_posts(
				array(
					'numberposts' => -1,
					'meta_key'    => '_customer_user',
					'meta_value'  => $customer_id,
					'post_type'   => wc_get_order_types(),
					'post_status' => array_keys( wc_get_order_statuses() ),
				)
			);

			if ( ! empty( $customer_orders ) ) {
				foreach ( $customer_orders as $key => $value ) {
					$order = wc_get_order( $value->ID );
					$total = $order->get_total() . ' for ' . count( $order->get_items() ) . ' item(s)';

					$status_bg = '#e5e5e5'; // pending, cancelled, refunded
					if ( explode( '-', $value->post_status )[1] === 'processing' ) {
						$status_bg = '#c6e1c6';
					} elseif ( explode( '-', $value->post_status )[1] === 'on' ) {
						$status_bg = '#f8dda7';
					} elseif ( explode( '-', $value->post_status )[1] === 'completed' ) {
						$status_bg = '#c8d7e1';
					} elseif ( explode( '-', $value->post_status )[1] === 'failed' ) {
						$status_bg = '#eba3a3';
					}
					$items       = array();
					$order_items = $order->get_items();
					foreach ( $order_items as $oikey => $oivalue ) {
						$product  = $oivalue->get_product();
						$quantity = $oivalue->get_quantity();
						if ( ! $product ) {
							continue;
						}
						$product_id   = $product->get_id();
						$product_name = $product->get_title();
						$seller_id    = get_post_field( 'post_author', $product_id );
						$image_id     = $product->get_image_id();
						$image_meta   = wp_get_attachment_metadata( $image_id );
						$attachment   = $product_store->mkwc_image_downsize( $image_meta, $size, $image_id );
						$items[]      = array(
							'seller_id'     => $seller_id,
							'id'            => $product_id,
							'title'         => $product_name,
							'image'         => isset( $attachment[0] ) ? $attachment[0] : '',
							'qty'           => $quantity,
							'dominantColor' => isset( $attachment[0] ) ? $general_data_handler->mkwc_get_dominant_Color( $attachment[0] ) : $general_data_handler->mkwc_get_dominant_Color( '' ),
						);
					}
					// 'on' === explode( '-', $value->post_status )[1] ? 'on-hold' : explode( '-', $value->post_status )[1]
					$customer_order_ids[] = array(
						'id'        => $value->ID,
						'date'      => get_the_date( 'Y-m-d', $value->ID ),
						'status'    => explode( 'wc-', $value->post_status )[1],
						'status_bg' => $status_bg,
						'total'     => $general_data_handler->mkwc_get_formatted_price( $total ),
						'items'     => $items,
					);
				}

				$orders = array(
					'orders' => $customer_order_ids,
				);
			} else {
				$orders = array();
			}

			return $orders;
		}
	}
}
