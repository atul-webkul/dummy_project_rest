<?php

/**
 * @author Webkul
 * @version 1.0.0
 * This file handles all seller data functions.
 */

namespace WkMobikulMultivendorApi\Helper;

use WkMobikulMultivendorApi\Helper;
use WkMobikulMultivendorApi\Helper\Util;

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'WC_Admin_Report' ) ) {
	require WC_ABSPATH . 'includes/admin/reports/class-wc-admin-report.php';
}

if ( ! class_exists( 'Mkwc_Seller_Data' ) ) {
	/**
	 *
	 */
	class Mkwc_Seller_Data extends \WC_Admin_Report implements Util\Mkwc_Seller_Data_Interface {

		protected $wpdb;
		protected $usermeta;
		protected $sellerInfo;

		public function __construct() {
			global $wpdb;

				$this->wpdb = $wpdb;

				$this->usermeta = $this->wpdb->usermeta;

				$this->sellerInfo = $this->wpdb->prefix . 'mpsellerinfo';
		}

		/**
		 * Check User is seller or not by user id
		 *
		 * @param $user_id
		 * @return bool
		 */
		public function mkwc_check_seller_by_id( $user_id = '' ) {
			if ( $user_id ) {

				// $user_meta  = get_userdata( $user_id );
				// $user_roles = $user_meta->roles;

				$seller_info = $this->wpdb->get_var( "SELECT user_id FROM {$this->wpdb->prefix}mpsellerinfo where user_id = $user_id and seller_value='seller'" );

				if ( $seller_info > 0 ) {
					return true;
				} else {
					return true;
				}
			} else {
				return false;
			}
		}

		/**
		 * Return seller lifetime sale
		 *
		 * @param $seller_id
		 * @return $total_sale
		 */
		public function mkwc_get_total_seller_order_amount( $seller_id ) {
			$postid = $this->mkwc_get_seller_order_ids( $seller_id );

			if ( ! empty( $postid ) ) {
				$sql = "SELECT sum(meta_value) AS 'total_order_amount' from {$this->wpdb->prefix}woocommerce_order_itemmeta where meta_key='_line_total' and order_item_id in({$postid})";

				$total_value = $this->wpdb->get_var( $sql );
			} else {
				$total_value = 0;
			}

			return $total_value;
		}

		/**
		 * Return seller all orders
		 *
		 * @param $seller_id
		 * @return $order_ids
		 */
		public function mkwc_get_seller_order_ids( $seller_id ) {
			$sql = "SELECT DISTINCT woi.order_item_id
                    FROM {$this->wpdb->prefix}woocommerce_order_itemmeta woi
                    JOIN {$this->wpdb->prefix}woocommerce_order_items woitems ON woitems.order_item_id = woi.order_item_id
                    JOIN {$this->wpdb->prefix}posts post ON woi.meta_value = post.ID
                    WHERE ( woi.meta_key ='_product_id' OR woi.meta_key ='_variation_id' )
                    AND post.ID = woi.meta_value
                    AND post.post_author = {$seller_id}";

			$result = $this->wpdb->get_results( $sql );

			$ids = array();

			foreach ( $result as $res ) {
				$ids[] = $res->order_item_id;
			}

			return implode( ',', $ids );
		}

		/**
		 * Return seller sale stats
		 *
		 * @param $seller_id
		 * @return $stats
		 */
		public function mkwc_get_seller_sale_stats( $seller_id ) {
			$result = $this->wpdb->get_row( $this->wpdb->prepare( "SELECT seller_total_ammount, paid_amount, total_refunded_amount from {$this->wpdb->prefix}mpcommision where seller_id = '%d'", $seller_id ) );

			return $result;
		}

		/**
		 * Return seler order sale history
		 *
		 * @param $seller_id, $time
		 * @return $sale_history
		 */
		public function mkwc_seller_sale_order_history( $seller_id, $time ) {
			$order_items = array();

			$postid = $this->mkwc_get_seller_order_item_id( $seller_id );

			$this->calculate_current_range( $time );

			if ( $postid['order_item_id'] ) {
						$order_ID = $postid['order_id'];

						$ID = $postid['order_item_id'];

						$query = "SELECT posts.post_date as post_date, sum(meta.meta_value) as total_sales, count(*) as count FROM {$this->wpdb->prefix}posts AS posts join {$this->wpdb->prefix}woocommerce_order_items as items on items.order_id = posts.ID join {$this->wpdb->prefix}woocommerce_order_itemmeta as meta on meta.order_item_id = items.order_item_id WHERE posts.ID IN ($order_ID) and meta.meta_key='_line_total' and meta.order_item_id in($ID) and posts.post_type 	IN ( 'shop_order' )	AND 	posts.post_status 	IN ( 'wc-completed','wc-processing','wc-on-hold','wc-refunded')	AND posts.post_date >= '" . date( 'Y-m-d H:i:s', $this->start_date ) . "'	AND posts.post_date < '" . date( 'Y-m-d H:i:s', strtotime( '+1 DAY', $this->end_date ) ) . "' GROUP BY " . $this->group_by_query;

						$data = $this->wpdb->get_results( $query );

						$amount = array( 'order_amounts' => $this->prepare_chart_data( $data, 'post_date', 'total_sales', $this->chart_interval, $this->start_date, $this->chart_groupby ) );

						$count = array( 'order_count' => $this->prepare_chart_data( $data, 'post_date', 'count', $this->chart_interval, $this->start_date, $this->chart_groupby ) );

				switch ( $time ) {
					case 'year':
							$labels = array(
								__( 'Jan', 'marketplace' ),
								__( 'Feb', 'marketplace' ),
								__( 'Mar', 'marketplace' ),
								__( 'Apr', 'marketplace' ),
								__( 'May', 'marketplace' ),
								__( 'Jun', 'marketplace' ),
								__( 'Jul', 'marketplace' ),
								__( 'Aug', 'marketplace' ),
								__( 'Sep', 'marketplace' ),
								__( 'Oct', 'marketplace' ),
								__( 'Nov', 'marketplace' ),
								__( 'Dec', 'marketplace' ),
							);
						break;

					case 'month':
						foreach ( $amount['order_amounts'] as $key => $value ) {
							$labels[] = date( 'd M', substr( $key, 0, -3 ) );
						}
						break;

					case '7day':
						foreach ( $amount['order_amounts'] as $key => $value ) {
							$labels[] = date( 'd M', substr( $key, 0, -3 ) );
						}
						break;

					case 'last_month':
						foreach ( $amount['order_amounts'] as $key => $value ) {
							$labels[] = date( 'd M', substr( $key, 0, -3 ) );
						}
						break;

					default:
						break;
				}

						$order_amount = array(
							'order_amount' => array_map( array( $this, 'mkwc_round_chart_totals' ), array_values( $amount['order_amounts'] ) ),
							'labels'       => $labels,
							'count'        => array_map( array( $this, 'mkwc_round_chart_totals' ), array_values( $count['order_count'] ) ),
						);

			} else {
				$order_amount = '';
			}

			return $order_amount;
		}

		/**
		 * Return seller order item ids
		 *
		 * @param $seller_id
		 * @return $order_item_ids
		 */
		public function mkwc_get_seller_order_item_id( $seller_id ) {
			$sql = "SELECT woitems.order_item_id,woitems.order_id from {$this->wpdb->prefix}woocommerce_order_itemmeta woi join {$this->wpdb->prefix}woocommerce_order_items woitems on woitems.order_item_id=woi.order_item_id join {$this->wpdb->prefix}posts post on woi.meta_value=post.ID where woi.meta_key='_product_id' and post.ID=woi.meta_value and post.post_author='" . $seller_id . "' GROUP BY order_id";

			$result = $this->wpdb->get_results( $sql );

			$order_item_ids = array();

			$order_ids = array();

			foreach ( $result as $res ) {
				$order_item_ids[] = $res->order_item_id;

				$order_ids[] = $res->order_id;
			}
			$order_item_ids = implode( ',', $order_item_ids );
			$order_ids      = implode( ',', $order_ids );

			return array(
				'order_item_id' => $order_item_ids,
				'order_id'      => $order_ids,
			);
		}

		function mkwc_round_chart_totals( $amount ) {
			if ( is_array( $amount ) ) {
				return array( $amount[0], wc_format_decimal( $amount[1], wc_get_price_decimals() ) );
			} else {
				return wc_format_decimal( $amount, wc_get_price_decimals() );
			}
		}

				/**
				 * Get top selling products for seller
				 *
				 * @param $seller_id
				 * @return $products
				 */
		public function mkwc_seller_top_selling_products( $seller_id ) {
			$sql = "SELECT wois.order_item_name AS  'title', post.ID, SUM( woi.meta_value ) AS  'qty'
						FROM {$this->wpdb->prefix}woocommerce_order_items AS wois
						LEFT JOIN {$this->wpdb->prefix}woocommerce_order_itemmeta AS woi ON woi.order_item_id = wois.order_item_id
						LEFT JOIN {$this->wpdb->prefix}woocommerce_order_itemmeta AS woi_auther ON woi_auther.order_item_id = wois.order_item_id
						JOIN {$this->wpdb->prefix}posts post ON post.ID = woi_auther.meta_value
						WHERE post.post_author='" . $seller_id . "' and post.post_status = 'publish' and woi.meta_key ='_qty'
						AND woi_auther.meta_key =  '_product_id'
						GROUP BY wois.order_item_name
						ORDER BY qty DESC
						LIMIT 5";

			$order_items = $this->wpdb->get_results( $sql );

			$order_items = array_map( array( $this, 'mkwc_top_selling_products' ), $order_items );
			return array( 'top_selling_products' => $order_items );
		}

				/**
				 * Get seller products count
				 *
				 * @param $seller_id
				 * @return $count
				 */
		public function mkwc_top_selling_products( $order_items ) {
			$product_store              = new Helper\Mkwc_Product_store();
			$general_data_handler       = new Helper\Mkwc_General_Data();
			$width                      = isset( $_GET['width'] ) ? intval( $_GET['width'] ) : 100;
			$mFactor                    = isset( $_GET['mFactor'] ) ? $_GET['mFactor'] : 1;
			$height                     = 0.5 * $width;
			$height                    *= $mFactor;
			$width                     *= $mFactor;
			$size                       = array(
				'width'  => floatval( $width ),
				'height' => floatval( $height ),
				'crop'   => 1,
			);
			$_product                   = wc_get_product( $order_items->ID );
			$image_meta                 = wp_get_attachment_metadata( get_post_thumbnail_id( $order_items->ID ) );
			$attachment                 = $product_store->mkwc_image_downsize( $image_meta, $size, get_post_thumbnail_id( $order_items->ID ) );
			$product_price              = $_product->get_price();
			$order_items->price         = $general_data_handler->mkwc_get_formatted_price( $product_price );
			$order_items->avg_rating    = $_product->get_average_rating();
			$order_items->image         = isset( $attachment[0] ) ? $attachment[0] : '';
			$order_items->dominantColor = isset( $attachment[0] ) ? $general_data_handler->mkwc_get_dominant_Color( $attachment[0] ) : $general_data_handler->mkwc_get_dominant_Color( '' );

			return $order_items;
		}

				/**
				 * Get seller products count
				 *
				 * @param $seller_id
				 * @return $count
				 */
		public function mkwc_get_seller_product_count( $seller_id ) {
			return $this->wpdb->get_var( "SELECT COUNT(*) AS 'product_count' FROM {$this->wpdb->prefix}posts as posts WHERE  post_type='product' AND post_author={$seller_id} AND post_status = 'publish'" );
		}

				/**
				 * Get seller products order count
				 *
				 * @param $seller_id
				 * @return $count
				 */
		public function mkwc_get_seller_order_count( $seller_id ) {
			 $response = $status = array();
			$postid    = $this->mkwc_get_seller_order_item_id( $seller_id );

			if ( $postid['order_item_id'] ) {
				$total_orders = $this->wpdb->get_var( "SELECT count(*) AS 'total_order_count' FROM {$this->wpdb->prefix}woocommerce_order_items as item join {$this->wpdb->prefix}posts as post on item.order_id=post.ID WHERE  post.post_type='shop_order' AND item.order_item_id in ($postid[order_item_id]) AND item.order_id=post.ID" );
			} else {
				$total_orders = 0;
			}
			$order_ids = ( isset( $postid['order_id'] ) && $postid['order_id'] ) ? $postid['order_id'] : array();
			foreach ( explode( ',', $order_ids ) as $key => $value ) {
				$status[] = str_replace( 'wc-', '', get_post_field( 'post_status', $value ) );
			}
			$status_count = array_count_values( $status );
			$response     = array(
				'total_orders'       => $total_orders,
				'order_status_count' => array(
					'completed'  => isset( $status_count['completed'] ) ? $status_count['completed'] : 0,
					'pending'    => isset( $status_count['pending'] ) ? $status_count['pending'] : 0,
					'processing' => isset( $status_count['processing'] ) ? $status_count['processing'] : 0,
					'on-hold'    => isset( $status_count['on-hold'] ) ? $status_count['on-hold'] : 0,
					'cancelled'  => isset( $status_count['cancelled'] ) ? $status_count['cancelled'] : 0,
					'refunded'   => isset( $status_count['refunded'] ) ? $status_count['refunded'] : 0,
					'failed'     => isset( $status_count['failed'] ) ? $status_count['failed'] : 0,
				),
			);

			return $response;
		}

				/**
				 * Get Seller Orders List
				 *
				 * @param $seller_id, $per_page, $page
				 * @return $order_data
				 */
		public function mkwc_get_seller_orders( $seller_id, $per_page, $page ) {
			$commission = new Helper\MKWC_Commission();

			$order_items = array();

			$postid = $this->mkwc_get_seller_order_item_id( $seller_id );

			$order_ids = ! empty( $postid['order_id'] ) ? explode( ',', $postid['order_id'] ) : array();

			rsort( $order_ids );

			$order_items = array();

			foreach ( $order_ids as $key => $order_id ) {
				$seller_order_data = $commission->wkmp_get_seller_final_order_info( $order_id, $seller_id );
				$count             = 0;
					$ordersss      = wc_get_order( $order_id );
					$itemsss       = $ordersss->get_items();
				foreach ( $itemsss as $item ) {
					//$product_idss = $item->get_product_id();
					$count = $count + absint( $item['qty'] );

				}

				$order_items[] = (object) array(
					'order_id'       => $order_id,
					'item_count'     => $count, //$seller_order_data['quantity'],
					'order_currency' => get_post_meta( $order_id, '_order_currency', true ),
					'order_total'    => $seller_order_data['total_seller_amount'],
					'order_date'     => get_post_field( 'post_date', $order_id ),
					'billing_email'  => get_post_meta( $order_id, '_billing_email', true ),
					'first_name'     => get_post_meta( $order_id, '_billing_first_name', true ),
					'last_name'      => get_post_meta( $order_id, '_billing_last_name', true ),
				);
			}

			if ( $postid['order_item_id'] ) {
					$order_items = array_map( array( $this, 'mkwc_total_format_mapping' ), $order_items );

					return $order_items;
			}
		}

				/**
				 * Mapping order total
				 *
				 * @param $item
				 * @return $item
				 */
		public function mkwc_total_format_mapping( $item ) {
			$product_store        = new Helper\Mkwc_Product_store();
			$general_data_handler = new Helper\Mkwc_General_Data();
			$width                = isset( $_GET['width'] ) ? intval( $_GET['width'] ) : 100;
			$mFactor              = isset( $_GET['mFactor'] ) ? $_GET['mFactor'] : 1;
			$height               = 0.5 * $width;
			$height              *= $mFactor;
			$width               *= $mFactor;

			$size              = array(
				'width'  => floatval( $width ),
				'height' => floatval( $height ),
				'crop'   => 1,
			);
			$item->order_total = html_entity_decode( strip_tags( wc_price( $item->order_total ) ) );
			$order             = wc_get_order( $item->order_id );
			$items             = array();
			$order_items       = $order->get_items();
			foreach ( $order_items as $oikey => $oivalue ) {

				$product = $oivalue->get_product();

				if ( $product ) {
					$product_id   = $product->get_id();
					$product_name = $product->get_title();
					$image_id     = $product->get_image_id();
					$image_meta   = wp_get_attachment_metadata( $image_id );
					$attachment   = $product_store->mkwc_image_downsize( $image_meta, $size, $image_id );
					$items[]      = array(
						'id'            => $product_id,
						'title'         => $product_name,
						'image'         => isset( $attachment[0] ) ? $attachment[0] : '',
						'dominantColor' => isset( $attachment[0] ) ? $general_data_handler->mkwc_get_dominant_Color( $attachment[0] ) : $general_data_handler->mkwc_get_dominant_Color( '' ),
					);
				}
			}

			$item->items = $items;

			return $item;
		}

				/**
				 * Return seller transactions
				 *
				 * @param $seller_id
				 * @return $transactions
				 */
		public function mkwc_get_seller_transactions( $seller_id ) {
				$transactions = array();

				$transactions = $this->wpdb->get_results( "SELECT * FROM {$this->wpdb->prefix}seller_transaction WHERE seller_id = '$seller_id' ORDER BY id DESC", ARRAY_A );

				return $transactions;
		}

				/**
				 * Return seller transaction data by id
				 *
				 * @param $seller_id, $transaction_id
				 * @return $transaction data
				 */
		public function mkwc_get_seller_transaction_by_id( $seller_id, $transaction_id ) {
			  $result = array();

				$result = $this->wpdb->get_row( $this->wpdb->prepare( "SELECT * FROM {$this->wpdb->prefix}seller_transaction WHERE seller_id = '%d' AND id = '%d'", $seller_id, $transaction_id ), ARRAY_A );

				return $result;
		}

				/**
				 * Return seller transaction data by id
				 *
				 * @param $seller_id, $transaction_id
				 * @return $transaction data
				 */
		public function mkwc_get_transaction_table_details( $seller_id, $orderId ) {
			global $commission;
			$order      = wc_get_order( $orderId );
			$item_count = $order->get_items();

			$sel_info     = $commission->wkmp_get_seller_final_order_info( $orderId, $seller_id );
			$product_name = '';
			if ( isset( $sel_info['product'] ) && $sel_info['product'] && is_array( $sel_info['product'] ) ) {
				foreach ( $sel_info['product'] as $pro_nme ) {
					if ( ! empty( $product_name ) ) {
						$product_name = $product_name . ' + ';
					}
					$product_name = $product_name . $pro_nme['title'];
				}
			}

			$quantity          = isset( $sel_info['quantity'] ) ? $sel_info['quantity'] : '';
			$line_total        = ( isset( $sel_info['product_total'] ) && isset( $sel_info['shipping'] ) ) ? $sel_info['product_total'] + $sel_info['shipping'] : '';
			$commission_amount = isset( $sel_info['total_commission'] ) ? $sel_info['total_commission'] : '';
			$subtotal          = isset( $sel_info['total_seller_amount'] ) ? $sel_info['total_seller_amount'] : '';
			$tip               = '';
			if ( $subtotal != $line_total ) {
				$tip  = round( $subtotal, 2 );
				$tip .= ' = ';
				$tip .= ( $line_total );
				if ( ! empty( $commission_amount ) ) {
					$tip .= ' - ';
					$tip .= $commission_amount . ' ( ' . __( 'Commission', 'mobikul_marketplace' ) . ' ) ';
				}
				$tip .= ' ';
			}
			return array(
				'order_id'     => $orderId,
				'product_name' => $product_name,
				'quantity'     => $quantity,
				'total_price'  => $line_total,
				'commission'   => $commission_amount,
				'subtotal'     => $subtotal,
				'tip'          => $tip,
			);
		}

				/**
				 * Get seller search product list
				 *
				 * @param $seller_id, $s
				 * @return $product_list
				 */
		public function mkwc_get_seller_search_products( $seller_id, $s ) {
				 $product_query = $this->wpdb->prepare( "SELECT SQL_CALC_FOUND_ROWS ID FROM {$this->wpdb->prefix}posts WHERE post_type = 'product' and post_status = 'publish' and post_author = '%d' and post_title like %s ORDER BY ID DESC", $seller_id, '%' . $s . '%' );

				$product_results = $this->wpdb->get_results( $product_query );

			if ( ! $product_results ) {
						return array(
							'success' => false,
							'message' => __( 'No product found.', 'mobikul_marketplace' ),
						);
			}

				$product_total_result = $this->wpdb->get_var( 'SELECT FOUND_ROWS();' );

			foreach ( $product_results as $key => $product ) {
				if ( is_wp_error( $product->ID ) ) {
						continue;
				}

							$title = get_post_field( 'post_title', $product->ID );

							$data[] = array(
								'id'    => $product->ID,
								'title' => $title,
							);
			}

							return array(
								'products'       => $data,
								'total_products' => $product_total_result,
								'success'        => true,
								'message'        => '',
							);
		}

				/**
				 * Get seller product list
				 *
				 * @param $seller_id
				 * @return $product_list
				 */
		public function mkwc_get_seller_products( $seller_id, $page, $per_page, $edit = true ) {
				$product_store = new Helper\Mkwc_Product_store();

				$offset = ( $page - 1 ) * $per_page;

				$additional_query = array();

				$product_result = array();

				$orderby = ( isset( $_GET['orderby'] ) && $_GET['orderby'] ) ? $_GET['orderby'] : '';

			if ( $orderby ) {
				switch ( $orderby ) {
					case 'date':
								$order_by   = 'date';
								$order_type = 'desc';
						break;

					case 'price':
										$order_by         = 'meta_value_num';
										$order_type       = 'asc';
										$additional_query = array(
											'meta_key' => '_price',
										);
						break;

					case 'price-desc':
										$order_by         = 'meta_value_num';
										$order_type       = 'desc';
										$additional_query = array(
											'meta_key' => '_price',
										);
						break;

					case 'rating':
						$order_by         = 'meta_value_num';
						$order_type       = 'desc';
						$additional_query = array(
							'meta_key' => '_price',
						);
						break;

					case 'price':
						$order_by         = 'meta_value_num';
						$order_type       = 'asc';
						$additional_query = array(
							'meta_key' => '_wc_average_rating',
						);
						break;

					case 'popularity':
						$order_by         = 'meta_value_num';
						$order_type       = 'asc';
						$additional_query = array(
							'meta_key' => 'total_sales',
						);
						break;

					case 'price':
						$order_by         = 'meta_value_num';
						$order_type       = 'asc';
						$additional_query = array(
							'meta_key' => 'total_sales',
						);
						break;

					default:
							$order_by   = 'menu_order title';
							$order_type = 'desc';
						break;
				}
			} else {
							$order_by   = 'date';
							$order_type = 'desc';
			}

							// set base query arguments
							$query_args = array(
								'author'         => $seller_id,
								'fields'         => 'ids',
								'post_type'      => 'product',
								'post_status'    => $edit ? array( 'publish', 'draft' ) : 'publish',
								'posts_per_page' => $per_page,
								'offset'         => $offset,
								'orderby'        => $order_by,
								'order'          => $order_type,
								'meta_query'     => array(),
							);

							$product_query = array_merge( $query_args, $additional_query );

							$product_data = new \wp_query( $product_query );

							$product_results = $product_data->posts;

							foreach ( $product_results as $key => $id ) {
								$product_result[] = (object) array( 'ID' => $id );
							}

							$data = $productdata = array();

							if ( ! $product_result ) {
								return array();
							}

							$product_total_result = $product_data->found_posts;

							$general_data_handler = new Helper\Mkwc_General_Data();

							foreach ( $product_result as $key => $product ) {
								if ( is_wp_error( $product->ID ) ) {
									continue;
								}

								$image_src = '';

								$regular_price = '';

								$price = '';

								$product_data = wc_get_product( $product->ID );

								$image = wp_get_attachment_image_src( get_post_thumbnail_id( $product->ID ), 'thumbnail' );

								if ( isset( $image[0] ) ) {
									$image_src = $image[0];
								}

								if ( $product_data->is_type( 'grouped' ) ) {
									$price = $product_store->mkwc_get_grouped_price_range( $product->ID, $product_data->get_children() );
								} elseif ( $product_data->is_type( 'variable' ) ) {
									$price_data = $general_data_handler->mkwc_get_variable_product_price( $product_data );

									$price = $price_data['price'];

									$regular_price = ( $price == $price_data['regular_price'] ) ? '' : $price_data['regular_price'];
								} else {
									$regular_price = $product_data->get_sale_price() ? $general_data_handler->mkwc_get_formatted_price( $product_data->get_regular_price() ) : '';
									$price         = $general_data_handler->mkwc_get_formatted_price( $product_data->get_price() );
								}

								$data[] = array(
									'id'            => $product->ID,
									'title'         => $product_data->get_title(),
									'stock'         => $product_data->get_stock_status(),
									'status'        => $product_data->get_status(),
									'price'         => $price,
									'regular_price' => $regular_price,
									'image'         => $image_src,
									'review_avg'    => $product_data->get_average_rating(),
								);
							}
							$productdata['products'] = $data;

							$productdata = array_merge( $productdata, array( 'total_products' => intval( $product_total_result ) ) );

							return $productdata;
		}

				/**
				 * Return seller orders list
				 *
				 * @param $seller_id, $page, $per_page
				 * @return $order_list data
				 */
		public function mkwc_get_seller_order_list( $seller_id, $page, $per_page ) {
			$query_result = '';

			$order_status = '';

			$offset = ( $page - 1 ) * $per_page;

			$order_detail = $this->wpdb->get_results( "SELECT SQL_CALC_FOUND_ROWS DISTINCT woitems.order_id from {$this->wpdb->prefix}woocommerce_order_itemmeta woi join {$this->wpdb->prefix}woocommerce_order_items woitems on woitems.order_item_id=woi.order_item_id join {$this->wpdb->prefix}posts post on woi.meta_value=post.ID where woi.meta_key='_product_id' and post.ID=woi.meta_value and post.post_author={$seller_id} order by woitems.order_id DESC LIMIT {$offset}, $per_page" );

			$order_total_result = $this->wpdb->get_var( 'SELECT FOUND_ROWS();' );

			if ( ! $order_detail ) {
					return array();
			}

			$general_data_handler = new Helper\Mkwc_General_Data();

			$product_store = new Helper\Mkwc_Product_store();

			$all_order_details = array();

			$orders_data = array();
			$width       = isset( $_GET['width'] ) ? intval( $_GET['width'] ) : 100;
			$mFactor     = isset( $_GET['mFactor'] ) ? $_GET['mFactor'] : 1;
			$mFactor     = floatval( $mFactor );
			$height      = 0.5 * $width;
			$height     *= $mFactor;
			$width      *= $mFactor;

			$size = array(
				'width'  => floatval( $width ),
				'height' => floatval( $height ),
				'crop'   => 1,
			);
			foreach ( $order_detail as $key => $order ) {
					$order_id = $order->order_id;

				if ( is_wp_error( $order_id ) ) {
						continue;
				}

				$order_data = wc_get_order( $order_id );

				$order_items = $order_data->get_items();

				if ( $this->wpdb->get_var( $this->wpdb->prepare( 'SHOW TABLES LIKE %s;', $this->wpdb->prefix . 'mpseller_orders' ) ) === $this->wpdb->prefix . 'mpseller_orders' ) {
						$query = $this->wpdb->prepare( "SELECT order_status from {$this->wpdb->prefix}mpseller_orders where order_id = '%d' and seller_id = '%d'", $order_id, $seller_id );

						$query_result = $this->wpdb->get_results( $query );
				}

				if ( $query_result ) {
						$order_status = $query_result[0]->order_status;
				}

				if ( ! $order_status ) {
						$order_status = get_post_field( 'post_status', $order_id );
				}

				$status_array  = wc_get_order_statuses();
				$pro_items     = array();
				$product_price = 0;
				$qty           = 0;

				$commission        = new Helper\MKWC_Commission();
				$ord_info          = $commission->wkmp_get_seller_order_info( $order_id, $seller_id );
				$seller_order_data = $commission->wkmp_get_seller_final_order_info( $order_id, $seller_id );
				foreach ( $order_items as $key => $items ) {
					$product_id = $items->get_product_id();

					$variable_id = $items->get_variation_id();

					$product = wc_get_product( $product_id );

					$product_author = get_post_field( 'post_author', $product_id );

					if ( $product_author == $seller_id ) {
						$qty += $items->get_quantity();
						if ( $variable_id != 0 ) {
							$product = new \WC_Product_Variation( $variable_id );
						}
						$product_price  = $seller_order_data['total_seller_amount'];
						$display_status = isset( $status_array[ $order_status ] ) ? $status_array[ $order_status ] : '-';
						$image_id       = $product->get_image_id();
						$image_meta     = wp_get_attachment_metadata( $image_id );
						$attachment     = $product_store->mkwc_image_downsize( $image_meta, $size, $image_id );
						$pro_items[]    = array(
							'id'            => $product->get_id(),
							'qty'           => $items->get_quantity(),
							'item_price'    => $product->get_price(),
							'title'         => $product->get_title(),
							'image'         => isset( $attachment[0] ) ? $attachment[0] : '',
							'dominantColor' => isset( $attachment[0] ) ? $general_data_handler->mkwc_get_dominant_Color( $attachment[0] ) : $general_data_handler->mkwc_get_dominant_Color( '' ),
						);

						$all_order_details[ $order_id ] = array(
							'order_date'    => date_format( $order_data->get_date_created(), 'Y-m-d H:i:s' ),
							'order_status'  => $display_status,
							'product_price' => $product_price,
							'qty'           => $qty,
							'currency'      => $order_data->get_currency(),
							'items'         => $pro_items,
							'commission'    => $ord_info['total_comision'],
						);
					}
				}
			}

			$order_by_table = array();

			$total_price = 0;

			foreach ( $all_order_details as $key => $val ) {
					$qty = $val['qty'];

					$total_price = $val['product_price'];

					$status = $val['order_status'];

					$date = $val['order_date'];

				if ( isset( $order_by_table[ $key ] ) ) {
						$total_price = $order_by_table[ $key ]['total_price'] + $total_price;
						$total_qty   = $order_by_table[ $key ]['quantity'] + $qty;
				} else {
								$total_qty = $qty;
				}
								$status_bg = '#e5e5e5'; // pending, cancelled, refunded
				if ( $status === 'processing' ) {
					$status_bg = '#c6e1c6';
				} elseif ( $status === 'on' ) {
					$status_bg = '#f8dda7';
				} elseif ( $status === 'completed' ) {
					$status_bg = '#c8d7e1';
				} elseif ( $status === 'failed' ) {
					$status_bg = '#eba3a3';
				}
								$order_by_table[ $key ] = array(
									'order_id'    => $key,
									'status'      => $status,
									'status_bg'   => $status_bg,
									'date'        => $date,
									'total_price' => $total_price,
									'quantity'    => $total_qty,
									'currency'    => $val['currency'],
									'items'       => $val['items'],
								);
			}

			if ( $order_by_table ) {
				$order_by_table = array_values( $order_by_table );

				$order_by_table = array_map( array( $this, 'mkwc_seller_order_price_format' ), $order_by_table );

				$orders_data = array_merge(
					$orders_data,
					array(
						'success' => true,
						'message' => '',
						'orders'  => $order_by_table,
					)
				);

				return $orders_data;
			} else {
				return array();
			}
		}

		public function mkwc_seller_order_price_format( $order_by_table ) {
			$general_data_handler = new Helper\Mkwc_General_Data();

			$order_by_table['total_price'] = $general_data_handler->mkwc_get_formatted_price( $order_by_table['total_price'], array( 'currency' => $order_by_table['currency'] ) );

			return $order_by_table;
		}

				/**
				 * Get order details by order id
				 *
				 * @param $seller_id, $order_id
				 * @return $order
				 */
		public function mkwc_get_seller_order_by_id( $seller_id, $order_id ) {
			  $commission = new Helper\MKWC_Commission();
			try {
						$order = wc_get_order( $order_id );
				if ( ! $order ) {
					return array(
						'success' => false,
						'message' => __( 'Invalid order ID.', 'mobikul_marketplace' ),
					);
				}

						$data = array();

						$general_data_handler = new Helper\Mkwc_General_Data();

						$order_detail_by_order_id = array();

						$order_items = $order->get_items();

						$mp_ord_data = $commission->wkmp_get_seller_final_order_info( $order_id, $seller_id );

						$total_seller_amount = isset( $mp_ord_data['total_seller_amount'] ) ? $mp_ord_data['total_seller_amount'] : '';
						$total_commission    = isset( $mp_ord_data['total_commission'] ) ? $mp_ord_data['total_commission'] : '';

						$shipping_cost = isset( $mp_ord_data['shipping'] ) ? $mp_ord_data['shipping'] : '';

				foreach ( $order_items as $key => $item ) {
					$product_id = $item->get_product_id();

					$variable_id = $item->get_variation_id();

					$product_total_price = $item->get_total();

					$qty = $item->get_quantity();

					$product_author = get_post_field( 'post_author', $product_id );

					if ( $product_author == $seller_id ) {
							$order_detail_by_order_id[] = array(
								'product_name'        => $item->get_name(),
								'product_id'          => $product_id,
								'qty'                 => $qty,
								'variable_id'         => $variable_id,
								'product_total_price' => $product_total_price,
							);
					}
				}

						$shipping_method = $order->get_shipping_method();

						$payment_method = $order->get_payment_method_title();

						$total_payment = 0;

						$variation_data = '';

						$line_items = array();

				if ( ! empty( $order_detail_by_order_id ) ) :
					foreach ( $order_detail_by_order_id as $key => $value ) {
							$total_payment = floatval( $total_payment ) + floatval( $value['product_total_price'] );

						if ( $value['variable_id'] == 0 ) {
							$product_name  = $value['product_name'];
							$product_total = $value['product_total_price'];
						} else {
								$product = new \WC_Product( $value['product_id'] );

								$attribute = $product->get_attributes();

								$attribute_name = '';

								$variation = new \WC_Product_Variation( $value['variable_id'] );

								$variation_attribute = $variation->get_variation_attributes();

								$product_name = $value['product_name'];

								$product_total = $value['product_total_price'];

							foreach ( $attribute as $k => $val ) {
									$attribute_name = $val['name'];

									$term = get_term_by( 'slug', $variation_attribute[ 'attribute_' . strtolower( $attribute_name ) ], $attribute_name );

									$variation_data['name'] = ucfirst( str_replace( 'pa_', '', $attribute_name ) );

									$variation_data['value'] = $term ? $term->name : '';
							}
						}

							$image_src = '';

							$image = wp_get_attachment_image_src( get_post_thumbnail_id( $value['product_id'] ), 'thumbnail' );

						if ( isset( $image[0] ) ) {
							$image_src = $image[0];
						}

							$line_items[] = array(
								'name'          => $product_name,
								'product_id'    => $value['product_id'],
								'variation_id'  => $value['variable_id'] == 0 ? '' : $value['variable_id'],
								'quantity'      => $value['qty'],
								'total'         => $general_data_handler->mkwc_get_formatted_price( $product_total, array( 'currency' => $order->get_currency() ) ),
								'variation'     => $variation_data,
								'product_image' => $image_src,
							);
					}

						$total_payment += floatval( $order->get_total_shipping() );

						$data = array(
							'order_id'         => $order_id,
							'subtotal'         => $general_data_handler->mkwc_get_formatted_price( $order->get_subtotal(), array( 'currency' => $order->get_currency() ) ),
							'total'            => $general_data_handler->mkwc_get_formatted_price( $total_seller_amount, array( 'currency' => $order->get_currency() ) ),
							'admin_commission' => $general_data_handler->mkwc_get_formatted_price( $total_commission, array( 'currency' => $order->get_currency() ) ),
							'shipping_total'   => $general_data_handler->mkwc_get_formatted_price( $shipping_cost, array( 'currency' => $order->get_currency() ) ),
							'cart_tax'         => $general_data_handler->mkwc_get_formatted_price( $order->get_cart_tax(), array( 'currency' => $order->get_currency() ) ),
							'total_discount'   => $general_data_handler->mkwc_get_formatted_price( $order->get_total_discount(), array( 'currency' => $order->get_currency() ) ),
							'line_items'       => $line_items,
							'shipping_address' => $order->get_address( 'shipping' ),
							'billing_address'  => $order->get_address( 'billing' ),
							'shipping'         => array(
								'name'  => $shipping_method,
								'value' => $shipping_cost ? $general_data_handler->mkwc_get_formatted_price( $shipping_cost, array( 'currency' => $order->get_currency() ) ) : 0,
							),
							'payment_method'   => $payment_method,
							'date'             => date_format( $order->get_date_created(), 'Y-m-d H:i:s' ),
							'status'           => ucfirst( $order->get_status() ),
							'order_status'     => wc_get_order_statuses(),
							'success'          => true,
							'message'          => '',
						);

								return $data;

								else :
										return array(
											'success' => false,
											'message' => __( 'Sorry, You can\'t access other seller\'s orders.', 'mobikul_marketplace' ),
										);
								endif;

			} catch ( Exception $e ) {
			}
		}

				/**
				 * Return seller notifications
				 *
				 * @param $seller_id, $page, $per_page
				 * @return $notifications
				 */
		public function mkwc_seller_notifications( $seller_id, $page, $per_page ) {
				 $notification = array();

				$offset = ( $page == 1 ) ? 0 : ( $page - 1 ) * $per_page;

				$type_order = $this->wpdb->get_results( "SELECT SQL_CALC_FOUND_ROWS * From {$this->wpdb->prefix}mp_notifications where type='order' and author_id = {$seller_id} order by id desc LIMIT $offset, $per_page", ARRAY_A );

				$type_order_total = $this->wpdb->get_var( 'SELECT FOUND_ROWS();' );

			if ( $type_order ) {
				foreach ( $type_order as $key => $value ) {
					$datetime1 = new \DateTime( date( 'F j, Y', strtotime( $value['timestamp'] ) ) );
					$datetime2 = new \DateTime( 'now' );
					$interval  = $datetime1->diff( $datetime2 );

					$notifications[] = strip_tags( $value['content'] ) . ' ' . $interval->days . ' day(s) ago';
				}

						$notification['order'] = array(
							'notification'   => $notifications,
							'total'          => $type_order_total,
							'data_displayed' => count( $notifications ),
						);
			} else {
					$notification['order'] = array();
			}

				$type_seller = $this->wpdb->get_results( "SELECT SQL_CALC_FOUND_ROWS * From {$this->wpdb->prefix}mp_notifications where type='seller' and author_id = {$seller_id} order by id desc LIMIT $offset, $per_page", ARRAY_A );

				$type_seller_total = $this->wpdb->get_var( 'SELECT FOUND_ROWS();' );

			if ( $type_seller ) {
				foreach ( $type_seller as $key => $val ) {
						$datetime1 = new \DateTime( date( 'F j, Y', strtotime( $val['timestamp'] ) ) );
						$datetime2 = new \DateTime( 'now' );
						$interval  = $datetime1->diff( $datetime2 );

						$s_notifications[] = strip_tags( $val['content'] ) . ' ' . $interval->days . ' day(s) ago';
				}

							$notification['seller'] = array(
								'notification'   => $s_notifications,
								'total'          => $type_seller_total,
								'data_displayed' => count( $s_notifications ),
							);
			} else {
				$notification['seller'] = array();
			}

							$type_product = $this->wpdb->get_results( "SELECT SQL_CALC_FOUND_ROWS * From {$this->wpdb->prefix}mp_notifications where type='product' and author_id = {$seller_id} order by id desc LIMIT $offset, $per_page", ARRAY_A );

							$type_product_total = $this->wpdb->get_var( 'SELECT FOUND_ROWS();' );

			if ( $type_product ) {
				foreach ( $type_product as $key => $val ) {
						$datetime1 = new \DateTime( date( 'F j, Y', strtotime( $val['timestamp'] ) ) );
						$datetime2 = new \DateTime( 'now' );
						$interval  = $datetime1->diff( $datetime2 );

						$p_notifications[] = strip_tags( $val['content'] ) . ' ' . $interval->days . ' day(s) ago';
				}

				$notification['product'] = array(
					'notification'   => $p_notifications,
					'total'          => $type_product_total,
					'data_displayed' => count( $p_notifications ),
				);
			} else {
				$notification['product'] = array();
			}

							return $notification;
		}

				/**
				 * Return seller shop followers
				 *
				 * @param $seller_id, $page, $per_page
				 * @return #shop_followers
				 */
		public function mkwc_get_followers( $seller_id, $page, $per_page ) {
				$followers = array();

				$offset = ( $page == 1 ) ? 0 : ( $page - 1 ) * $per_page;

				$customer_list = get_users(
					array(
						'meta_key'   => 'favourite_seller',
						'meta_value' => $seller_id,
						'number'     => $per_page,
						'offset'     => $offset,
					)
				);

				$total_followers = count(
					get_users(
						array(
							'meta_key'   => 'favourite_seller',
							'meta_value' => $seller_id,
							'number'     => -1,
						)
					)
				);

			if ( ! empty( $customer_list ) ) :
				foreach ( $customer_list as $key => $value ) {
					$followers_list[] = array(
						'user_id' => $value->ID,
						'name'    => $value->display_name,
						'email'   => $value->user_email,
					);
				}
						$followers = array(
							'success'        => true,
							'message'        => '',
							'followers'      => $followers_list,
							'data_displayed' => count( $followers_list ),
							'total'          => $total_followers,
						);

						return $followers;
						else :
								return array(
									'success' => false,
									'message' => __( 'No followers found!!!', 'mobikul_marketplace' ),
								);
						endif;
		}

				/**
				 * Return seller asked queries from admin
				 *
				 * @param $seller_id, $page, $per_page
				 * @return $queries
				 */
		public function mkwc_get_asked_queries( $seller_id, $page, $per_page ) {
				$queries = array();

				$offset = ( $page == 1 ) ? 0 : ( $page - 1 ) * $per_page;

				$query_result = $this->wpdb->get_results( $this->wpdb->prepare( "SELECT  SQL_CALC_FOUND_ROWS * FROM {$this->wpdb->prefix}mpseller_asktoadmin where seller_id = '%d' order by id desc LIMIT $offset, $per_page", $seller_id ) );

				$total_query = $this->wpdb->get_var( 'SELECT FOUND_ROWS();' );

			if ( $query_result ) {
						$queries = array(
							'success'        => true,
							'message'        => '',
							'queries'        => $query_result,
							'data_displayed' => count( $query_result ),
							'total'          => $total_query,
						);
			} else {
					$queries = array(
						'success' => true,
						'message' => __( 'No queries found!!!', 'mobikul_marketplace' ),
					);
			}

				return $queries;
		}

				/**
				 * Return seller details
				 *
				 * @param $seller_id
				 * @return $seller meta
				 */
		public function mkwc_get_details( $seller_id ) {
				$queries = $this->wpdb->get_results( "select * from $this->usermeta where user_id=" . $seller_id );
				return $queries;
		}

				/**
				 * Return seller ids
				 *
				 * @param ,
				 * @return #seller_ids
				 */
		public function mkwc_seller_ids() {
			 $query = "Select Distinct A.user_id,A.seller_value from {$this->sellerInfo} as A join {$this->wpdb->users} user on user.ID=A.user_id ORDER BY user.ID DESC";

			$ids = $this->wpdb->get_results( $query );

			return $ids;
		}

				/**
				 * Return seller ids
				 *
				 * @param ,
				 * @return #seller_ids
				 */
		public function mkwc_seller_products( $id ) {
				   $user_products = $this->wpdb->get_results( "SELECT COUNT( ID ) AS id FROM {$this->wpdb->prefix}posts WHERE post_author='" . $id . "' and post_type='product'" );
					return $user_products;
		}

				/**
				 * Return allowed categories and product types
				 *
				 * @param $seller_id
				 * @return Array $categories & $product_types
				 */
		public function mkwc_seller_product_prerequisites( $seller_id ) {
			   $categories = array();

				$allowed_types = array();

				$allowed_cat = get_user_meta( $seller_id, 'wkmp_seller_allowed_categories', true );

			if ( ! $allowed_cat ) {
						$allowed_categories = get_option( 'wkmp_seller_allowed_categories' );
			} else {
							$allowed_categories = $allowed_cat;
			}

							$cat_list = get_terms(
								'product_cat',
								array(
									'hide_empty' => false,
									'include'    => $allowed_categories,
								)
							);

			if ( $cat_list ) {
				foreach ( $cat_list as $key => $value ) {
					if ( $allowed_categories ) {
						if ( in_array( $value->slug, $allowed_categories ) ) {
							$categories[] = array(
								'id'    => $value->term_id,
								'title' => $value->name,
							);
						}
					} else {
										$categories[] = array(
											'id'    => $value->term_id,
											'title' => $value->name,
										);
					}
				}
			}

				$mp_product_type = wc_get_product_types();

				$allowed_product_types = get_option( 'wkmp_seller_allowed_product_types' );

			foreach ( $mp_product_type as $key => $pro_type ) {
				if ( $allowed_product_types ) {
					if ( in_array( $key, $allowed_product_types ) ) {
							$allowed_types[] = array(
								'id'    => $key,
								'title' => $pro_type,
							);
					}
				} else {
					$allowed_types[] = array(
						'id'    => $key,
						'title' => $pro_type,
					);
				}
			}

							return array(
								'categories'   => $categories,
								'productTypes' => $allowed_types,
							);
		}

				/**
				 * Update seller media
				 *
				 * @param ,
				 * @return #seller_media
				 */
		public function mkwc_update_seller_media( $user_id, $avtar_type, $attach_id ) {
				 update_user_meta( $user_id, '_thumbnail_id_' . $avtar_type, $attach_id );
		}
	}
}
