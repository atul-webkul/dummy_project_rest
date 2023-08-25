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

if ( ! class_exists( 'Mkwc_Cart_Data' ) ) {
	/**
	 *
	 */
	class Mkwc_Cart_Data implements Util\Mkwc_Cart_Data_Interface {

		protected $wpdb;

		protected $current_customer = 0;

		public $update_item_count;

		public $temp_count;

		public $reg_user_id;

		public $shipping_method = array();

		public $shipping_methods = array();

		public $request_type = 'update';

		/** @var array Contains an array of cart items. */
		public $cart_contents = array();

		/** @var array Contains an array of removed cart items. */
		public $removed_cart_contents = array();

		/** @var array Contains an array of coupon codes applied to the cart. */
		public $applied_coupons = array();

		/** @var array Contains an array of coupon code discounts after they have been applied. */
		public $coupon_discount_amounts = array();

		/** @var array Contains an array of coupon code discount taxes. Used for tax incl pricing. */
		public $coupon_discount_tax_amounts = array();

		/** @var array Contains an array of coupon usage counts after they have been applied. */
		public $coupon_applied_count = array();

		/** @var array Array of coupons */
		public $coupons = array();

		public $coupon_count = 1;

		/** @var float The total cost of the cart items. */
		public $cart_contents_total;

		/** @var float Cart grand total. */
		public $total;

		/** @var float Cart subtotal. */
		public $subtotal;

		public $is_valid = true;

		public $coupon_indidual = array();

		/** @var float Cart subtotal without tax. */
		public $subtotal_ex_tax;

		/** @var float Total cart tax. */
		public $tax_total;

		/** @var array An array of taxes/tax rates for the cart. */
		public $taxes = array();   // changed to array

		/** @var array An array of taxes/tax rates for the shipping. */
		public $shipping_taxes;

		/** @var float Discount amount before tax */
		public $discount_cart;

		/** @var float Discounted tax amount. Used predominantly for displaying tax inclusive prices correctly */
		public $discount_cart_tax;

		/** @var float Total for additional fees. */
		public $fee_total;

		/** @var float Shipping cost. */
		public $shipping_total;

		/** @var float Shipping tax. */
		public $shipping_tax_total;

		public $pay_amount;

		protected static $c = 0;

		/** @var array cart_session_data. Array of data the cart calculates and stores in the session with defaults */
		public $cart_session_data = array(
			'cart_contents_total'         => 0,
			'total'                       => 0,
			'subtotal'                    => 0,
			'subtotal_ex_tax'             => 0,
			'tax_total'                   => 0,
			'taxes'                       => array(),
			'shipping_taxes'              => array(),
			'discount_cart'               => 0,
			'discount_cart_tax'           => 0,
			'shipping_total'              => 0,
			'shipping_tax_total'          => 0,
			'coupon_discount_amounts'     => array(),
			'coupon_discount_tax_amounts' => array(),
			'fee_total'                   => 0,
			'fees'                        => array(),
		);

		public $calculating_shipping = array(
			'cart_contents_total'         => 0,
			'total'                       => 0,
			'subtotal'                    => 0,
			'subtotal_ex_tax'             => 0,
			'tax_total'                   => 0,
			'taxes'                       => array(),
			'shipping_taxes'              => array(),
			'discount_cart'               => 0,
			'discount_cart_tax'           => 0,
			'shipping_total'              => 0,
			'shipping_tax_total'          => 0,
			'coupon_discount_amounts'     => array(),
			'coupon_discount_tax_amounts' => array(),
			'fee_total'                   => 0,
			'fees'                        => array(),
		);

		protected $general_data_handler;

		public function __construct() {
			global $wpdb;

			$this->wpdb = $wpdb;

			$this->request_type = isset( $_GET['request'] ) ? $_GET['request'] : '';
		}

		/**
		 * Auto-load in-accessible properties on demand.
		 *
		 * @param mixed $key
		 * @return mixed
		 */
		public function __get( $key ) {
			switch ( $key ) {
				case 'prices_include_tax':
					return wc_prices_include_tax();
				break;
				case 'round_at_subtotal':
					return 'yes' === get_option( 'woocommerce_tax_round_at_subtotal' );
				break;
				case 'tax_display_cart':
					return get_option( 'woocommerce_tax_display_cart' );
				break;
				case 'dp':
					return wc_get_price_decimals();
				break;
				case 'display_totals_ex_tax':
				case 'display_cart_ex_tax':
					return $this->tax_display_cart === 'excl';
				break;
				case 'cart_contents_weight':
					return $this->get_cart_contents_weight();
				break;
				case 'cart_contents_count':
					return $this->get_cart_contents_count();
				break;
				case 'tax':
						_deprecated_argument( 'WC_Cart->tax', '2.3', 'Use WC_Tax:: directly' );

					$this->tax = new \WC_Tax();

					return $this->tax;
				case 'discount_total':
						_deprecated_argument( 'WC_Cart->discount_total', '2.3', 'After tax coupons are no longer supported. For more information see: https://woocommerce.wordpress.com/2014/12/upcoming-coupon-changes-in-woocommerce-2-3/' );

					return 0;
			}
		}

		public function mkwc_cart_update_data( $data ) {
			  $data = is_object( $data ) ? $data->get_json_params() : $data;

			  global $wpdb;

			  $customer_id = '';

			  $this->applied_coupons = array();

			  $content = trim( file_get_contents( 'php://input' ) );

			  // Attempt to decode the incoming RAW post data from JSON.
			  $decoded_post_data = json_decode( $content, true );

			  $coupon_apply_check = isset( $data['coupon_apply'] ) ? $data['coupon_apply'] : false;

			  $coupon_remove_check = isset( $data['coupon_remove'] ) ? $data['coupon_remove'] : false;

			if ( empty( $data['cart'] ) && empty( $data['coupons'] ) ) {
				return array(
					'success' => false,
					'message' => __( 'Sorry, you must provide all parameters.', 'mobikul_marketplace' ),
				);
			}
			  // shipping method update case
			if ( isset( $data['shipping_method_id'] ) && $data['shipping_method_id'] ) {
				$this->shipping_method_id = $data['shipping_method_id'];
			} else {
				$this->shipping_method_id = '';
			}
			if ( isset( $decoded_post_data['request'] ) ) {
				$request_type = $decoded_post_data['request'];
			} else {
				$request_type = isset( $_GET['request'] ) ? $_GET['request'] : '';
			}
			if ( isset( $decoded_post_data['customer_id'] ) ) {
				$customer_id = $decoded_post_data['customer_id'];
			} else {
				$customer_id = $data['customer_id'];
			}
			if ( empty( $customer_id ) ) {
				if ( isset( $decoded_post_data['guest_id'] ) ) {
					$customer_id = $decoded_post_data['guest_id'];
				} else {
					$customer_id = $data['guest_id'];
				}
			}
			if ( isset( $data['coupons'] ) ) {
				$coupons = $data['coupons'];

				if ( gettype( $data['coupons'] ) == 'string' ) {
					$coupons = json_decode( $data['coupons'], true );
				}
				$applied = 0;

				$table_name = $this->wpdb->prefix . 'woocommerce_sessions';

				$session = $this->wpdb->get_row( "SELECT session_value FROM $table_name WHERE session_key = '$customer_id'", ARRAY_A );

				$session_value = isset( $session['session_value'] ) ? maybe_unserialize( $session['session_value'] ) : '';

				$session_value = isset( $session_value['applied_coupons'] ) ? $session_value['applied_coupons'] : '';

				$coupons = array_filter( $coupons );

				foreach ( $coupons as $coupon ) {
					$code = isset( $coupon['code'] ) ? $coupon['code'] : '';

					$check = $this->get_coupon_message_custom( $code );

					if ( $check != 'ok' ) {
						return array(
							'success' => false,
							'message' => $check,
						);
					}
				}
			} else {
				$coupons = array();
			}
			if ( isset( $data['cart'] ) ) {
				$this->update_item_count = count( $data['cart'] );

				foreach ( $data['cart'] as $key => $value ) {
					$product_id     = intval( $value['product_id'] );
					$quantity       = ! empty( $value['quantity'] ) ? $value['quantity'] : 0;
					$variation_id   = ! empty( $value['variation_id'] ) ? $value['variation_id'] : 0;
					$add_variations = isset( $value['variations'] ) ? $value['variations'] : array();
					$variations     = $variations = array();
					foreach ( $add_variations as $adkey => $advalue ) {
						$cust_variations[] = $advalue['option'];
					}
					if ( $variation_id ) {
						$productvariation = wc_get_product( $variation_id );

						$variations = $productvariation->get_variation_attributes();
					}
					if ( ( count( $variations ) == count( $cust_variations ) ) && is_array( $variations ) && is_array( $cust_variations ) ) {
						$variations = array_combine( array_keys( $variations ), array_values( $cust_variations ) );
					}
					if ( empty( $product_id ) ) {
						return array(
							'success' => false,
							'message' => __( 'Sorry, you must provide all parameters.', 'mobikul_marketplace' ),
						);
					}
					if ( ! empty( $customer_id ) ) {
						$response = maybe_unserialize( $this->mkwc_cart_request_handler( $product_id, $customer_id, $quantity, $variation_id, $variations, $coupons, $this->update_item_count ) );
					}
				}
			}
			if ( ! is_array( $response ) ) {
				return array(
					'success' => false,
					'message' => __( 'Error Updating Cart.', 'mobikul_marketplace' ),
				);
			}
			if ( ! empty( $response['cart'] ) ) {
				$product_count = count( $response['cart'] );

				foreach ( $response['cart'] as $ckey => $cvalue ) {
					$product_count += $cvalue['quantity'] - 1;
				}
				$response['count'] = $product_count;
			} elseif ( array_key_exists( 'status', $response ) ) {
				$p_count = 0;

				foreach ( $response['cart_data'] as $cdkey => $cdvalue ) {
					$p_count += $cdvalue['quantity'];
				}
				unset( $response['cart_data'] );
			}
			if ( ! empty( $response['applied_coupons'] ) ) {
				$response['success'] = true;
			} elseif ( ! empty( $response['cart'] ) ) {
				$response['success'] = true;
			} else {
				$response['success'] = false;
			}
			  $new_array = array();

			  $i = 0;
			if ( count( $response['applied_coupons'] ) > 0 && count( $response['coupon_discount_amounts'] ) > 0 ) {
				foreach ( $response['applied_coupons'] as $new_coupon ) {
					$new_array[ $i ]['coupon_code'] = $new_coupon;

					$new_array[ $i ]['coupon_amount'] = array_sum( array_column( $response['coupon_discount_amounts'], $new_coupon ) );
					$i++;
				}
			}
			  $response['coupon_discount_amounts'] = $new_array;

			  $response['is_cartEligible'] = true;

			if ( isset( $response['coupn_validation'] ) && count( $response['coupn_validation'] ) > 0 ) {
				$message = '';
				foreach ( $response['coupn_validation'] as $key => $value ) {
					$message = __( 'Coupon name is ' . $value['coupon_code'] . ' ,' . $value['eror_message'] . '', 'mobikul_marketplace' );
				}
				$response['message'] = $message;

				$response['success'] = false;
			}
			if ( isset( $this->coupon_indidual ) && count( $this->coupon_indidual ) > 0 ) {
				$response['success'] = false;

				$response['message'] = __( 'Notice:, coupon ' . $this->coupon_indidual[0] . ' has  been applied and cannot be used in conjunction with other coupons.', 'mobikul_marketplace' );
			}
			  unset( $response['coupn_validation'] );
			if ( empty( $response['message'] ) ) {
				if ( $coupon_apply_check ) {
					$response['message'] = __( 'Coupon code applied successfully.', 'mobikul_marketplace' );
				} elseif ( $coupon_remove_check ) {
					$response['message'] = __( 'Coupon has been removed.', 'mobikul_marketplace' );
				} else {
					$response['message'] = __( 'Cart updated.', 'mobikul_marketplace' );
				}
			}
			  return $response;
		}
		/**
		 * Save seller query to admin
		 *
		 * @param $seller_id, $subject, $message
		 * @return bool
		 */
		public function mkwc_cart_request_handler( $product_id, $user_id, $quantity = 1, $variation_id = 0, $variations = array(), $coupons = array(), $update_item_count = '' ) {
			  global $wpdb;

				$request_type = $this->request_type;

				$table_name = $wpdb->prefix . 'woocommerce_sessions';

			$table_name_two = $wpdb->prefix . 'usermeta';

			$this->reg_user_id = $user_id;

			$this->update_item_count = $update_item_count;

			$updated_old_cart = array();

				$session = $wpdb->get_results( "SELECT * FROM $table_name WHERE session_key = '$user_id'" );

			if ( empty( $session ) ) {
				$empty_data = array();

				$cart_key = $this->get_cart_data( $coupons, $product_id, $empty_data, $quantity, $variation_id, $variations );

				if ( is_array( $cart_key ) && ( array_key_exists( 'status', $cart_key ) || array_key_exists( 'message', $cart_key ) ) ) {
					return $cart_key;
				}
				if ( ! empty( $coupons ) ) {
					if ( $request_type == 'update' ) {
						foreach ( $coupons as $key => $value ) {
								$result = $this->add_discount( $value['code'], count( $coupons ) );

							$check = $this->get_coupon_message_custom( $value['code'] );
							if ( $check != 'ok' ) {
											$session_value['coupn_validation'][] = array(
												'coupon_code'  => $value['code'],
												'eror_message' => $check,
											);
							} else {
								$session_value['success'] = true;
							}
						}
					} else {
						foreach ( $coupons as $key => $value ) {
								$result = $this->add_discount( $value, count( $coupons ) );

							if ( $result != false && isset( $value ) && $value != '' ) {
								$session_value['success'] = 'ture';
							}
						}
					}
				}
				$cart_session = $this->cart_contents;

				unset( $cart_session[ $cart_key ]['data'] );

				if ( ! empty( $this->applied_coupons ) ) {
					  $applied_coupons = $this->applied_coupons;
				} else {
						$applied_coupons = array();
				}
				if ( ! empty( $this->coupon_discount_amounts ) ) {
						$coupon_discount_amounts = $this->coupon_discount_amounts;
				} else {
						$coupon_discount_amounts = array();
				}
				if ( ! empty( $this->coupon_discount_tax_amounts ) ) {
						$coupon_discount_tax_amounts = $this->coupon_discount_tax_amounts;
				} else {
						$coupon_discount_tax_amounts = array();
				}
				if ( ! empty( $this->removed_cart_contents ) ) {
						$removed_cart_contents = $this->removed_cart_contents;
				} else {
					  $removed_cart_contents = array();
				}
				$session_value['cart'] = $cart_session;

				$session_value['applied_coupons'] = $applied_coupons;

				$session_value['coupon_discount_amounts'] = $coupon_discount_amounts;

				$session_value['coupon_discount_tax_amounts'] = $coupon_discount_tax_amounts;

				$session_value['removed_cart_contents'] = $removed_cart_contents;

				$session_value['shipping_method'] = $this->shipping_method;

				$session_value['shipping_methods'] = $this->shipping_methods;

				foreach ( $this->cart_session_data as $key => $default ) {
					$session_value[ $key ] = $this->$key;
				}
				if ( $this->discount_cart ) {
					$session_value['cart_contents_total'] = $session_value['cart_contents_total'] - $this->discount_cart - $this->discount_cart_tax;
				}
				$session_value['total'] = $session_value['cart_contents_total'];

				$session_value = maybe_serialize( $session_value );

				$this->set_session_expiration();

				$wpdb->get_results( "INSERT INTO $table_name (session_key, session_value, session_expiry) VALUES ('$user_id', '$session_value', '$this->_session_expiring')" );
			} else {
				$old_cart = maybe_unserialize( $session[0]->session_value );

				$old_cart['cart'] = isset( $old_cart['cart'] ) ? maybe_unserialize( $old_cart['cart'] ) : array();

				if ( $request_type == 'add' || $request_type == 'delete' ) {
					if ( ! empty( $old_cart['applied_coupons'] ) ) {
						$coupons = maybe_unserialize( $old_cart['applied_coupons'] );
					}
				}

				foreach ( $old_cart['cart'] as $key => $value ) {
					$value['variation_id'] = isset( $value['variation_id'] ) ? $value['variation_id'] : '';

					$value['product_id'] = isset( $value['product_id'] ) ? $value['product_id'] : '';

					$product_data = wc_get_product( $value['variation_id'] ? $value['variation_id'] : $value['product_id'] );

					if ( $product_data ) {
						 $updated_old_cart['cart'][ $key ] = array_merge( $value, array( 'data' => $product_data ) );
					}
				}
				$updated_old_cart['cart'] = isset( $updated_old_cart['cart'] ) ? $updated_old_cart['cart'] : '';

				WC()->cart = new \Wc_Cart();

				WC()->cart->set_cart_contents( $updated_old_cart['cart'] );

				$cart_key = $this->get_cart_data( $coupons, $product_id, $updated_old_cart, $quantity, $variation_id, $variations );

				if ( is_array( $cart_key ) && ( array_key_exists( 'status', $cart_key ) || array_key_exists( 'message', $cart_key ) ) ) {
						return $cart_key;
				}
				if ( ! empty( $coupons ) ) {
					if ( $request_type == 'update' ) {
						foreach ( $coupons as $key => $value ) {
								$result = $this->add_discount( $value['code'], count( $coupons ) );

							$check = $this->get_coupon_message_custom( $value['code'] );
							if ( $check != 'ok' ) {
								$session_value['coupn_validation'][] = array(
									'coupon_code'  => $value['code'],
									'eror_message' => $check,
								);
							} else {
								$session_value['success'] = true;
							}
						}
					} else {
						foreach ( $coupons as $key => $value ) {
								$result = $this->add_discount( $value, count( $coupons ) );

							if ( $result != false && isset( $value ) && $value != '' ) {
								$session_value['success'] = 'ture';
							}
						}
					}
				}
				$cart_session = $this->cart_contents;

				if ( ! empty( $this->applied_coupons ) ) {
						$applied_coupons = $this->applied_coupons;
				} else {
					  $applied_coupons = array();
				}
				if ( ! empty( $this->coupon_discount_amounts ) ) {
						$coupon_discount_amounts = $this->coupon_discount_amounts;
				} else {
						$coupon_discount_amounts = array();
				}
				if ( ! empty( $this->coupon_discount_tax_amounts ) ) {
						$coupon_discount_tax_amounts = $this->coupon_discount_tax_amounts;
				} else {
						$coupon_discount_tax_amounts = array();
				}
				if ( ! empty( $this->removed_cart_contents ) ) {
						$removed_cart_contents = $this->removed_cart_contents;
				} else {
						$removed_cart_contents = array();
				}
				$session_value['cart'] = $cart_session;

				$session_value['applied_coupons'] = $applied_coupons;

				$session_value['coupon_discount_amounts'] = $coupon_discount_amounts;

				$session_value['coupon_discount_tax_amounts'] = $coupon_discount_tax_amounts;

				$session_value['removed_cart_contents'] = $removed_cart_contents;

				$session_value['shipping_method'] = $this->shipping_method;

				$session_value['shipping_methods'] = $this->shipping_methods;

				foreach ( $this->cart_session_data as $key => $default ) {
					$session_value[ $key ] = $this->$key;
				}
				foreach ( $session_value['cart'] as $key => $value ) {
					if ( is_array( $session_value['cart'][ $key ] ) && array_key_exists( 'data', $session_value['cart'][ $key ] ) ) {
						   unset( $session_value['cart'][ $key ]['data'] );
					}
				}
				if ( $this->discount_cart ) {
					$session_value['cart_contents_total'] = $session_value['cart_contents_total'] - $this->discount_cart_tax;
				}

				$session_value['total'] = $session_value['cart_contents_total'];

				update_user_meta(
					$user_id,
					'_woocommerce_persistent_cart_' . get_current_blog_id(),
					array(
						'cart' => $session_value['cart'],
					)
				);

				$session_value = maybe_serialize( $session_value );

				$session_id = get_user_meta( $user_id, 'user_web_session', true );

				// Update session if its close to expiring
				if ( time() > $this->_session_expiring ) {
					   $this->set_session_expiration();

					   $this->update_session_timestamp( $user_id, $this->_session_expiration, $table_name );

					if ( $session_id ) {
						$this->update_session_timestamp( $session_id, $this->_session_expiration, $table_name );
					}
				}
				if ( $session_id ) {
					$wpdb->get_results( "UPDATE $table_name SET session_value = '$session_value' WHERE session_key = '$session_id'" );
				}
				  $wpdb->get_results( "UPDATE $table_name SET session_value = '$session_value' WHERE session_key = '$user_id'" );
			}
				return $session_value;
		}

		/**
		 * Get weight of items in the cart.
		 *
		 * @since 2.5.0
		 * @return int
		 */
		public function get_cart_contents_weight() {
			$weight = 0;
			foreach ( $this->get_cart() as $cart_item_key => $values ) {
				$weight += $values['data']->get_weight() * $values['quantity'];
			}
			return apply_filters( 'woocommerce_cart_contents_weight', $weight );
		}

		/**
		 * Get number of items in the cart.
		 *
		 * @return int
		 */
		public function get_cart_contents_count() {
			return apply_filters( 'woocommerce_cart_contents_count', array_sum( wp_list_pluck( $this->get_cart(), 'quantity' ) ) );
		}

		public function get_cart_data( $coupons, $product_id = 0, $session_value = array(), $quantity = 1, $variation_id = 0, $variation = array() ) {
			try {
				  $product_temp_cart_quantity = 0;

				  $request_type = $this->request_type;

				if ( ! empty( $session_value ) ) {
						$cart_item_data = maybe_unserialize( $session_value )['cart'];
				} else {
					$cart_item_data = array();
				}

				  $product_id = absint( $product_id );

				$variation_id = absint( $variation_id );

				// Ensure we don't add a variation to the cart directly by variation ID
				if ( 'product_variation' == get_post_type( $product_id ) ) {
						$variation_id = $product_id;

					$product_id = wp_get_post_parent_id( $variation_id );
				}
					 // Get the product
					$product_data = wc_get_product( $variation_id ? $variation_id : $product_id );

					// Load cart item data - may be added by other plugins
					$cart_item_data = (array) apply_filters( 'woocommerce_add_cart_item_data', $cart_item_data, $product_id, $variation_id );

				  // Generate a ID based on product ID, variation ID, variation data, and other cart item data
				  $cart_id = $this->generate_cart_id( $product_id, $variation_id, $variation );

				  // Find the cart item key in the existing cart

				  $cart_item_key = $this->find_product_in_cart( $cart_id, $session_value );

				if ( $cart_item_key ) {
					  $product_temp_cart_quantity = (int) $session_value['cart'][ $cart_item_key ]['quantity'];

					$product_temp_cart_quantity += (int) $quantity;
				}

				if ( $request_type != 'delete' ) {
						// Force quantity to 1 if sold individually and check for existing item in cart
					if ( $product_data->is_sold_individually() ) {
						if ( array_key_exists( $cart_item_key, $cart_item_data ) ) {
								  return array(
									  'status'    => flase,
									  'message'   => __( 'Product is already in cart.', 'mobikul_marketplace' ),
									  'cart_data' => $cart_item_data,
								  );
						}
					}

						// Check product is_purchasable
					if ( ! $this->is_purchasable( $product_data ) ) {
						return array(
							'success' => false,
							'message' => __( 'Sorry, this product cannot be purchased.', 'mobikul_marketplace' ),
						);
					}

						// Stock check - only check if we're managing stock and backorders are not allowed
					if ( ! $product_data->is_in_stock() ) {
							 return array(
								 'success' => false,
								 'message' => sprintf( __( 'You cannot add %s to the cart because the product is out of stock.', 'mobikul_marketplace' ), $product_data->get_title() ),
							 );
					}

					if ( ! $product_data->has_enough_stock( $quantity ) ) {
						   return array(
							   'success' => false,
							   'message' => sprintf( __( 'You cannot add that amount of %1$s to the cart because there is not enough stock ( %2$s remaining ).', 'mobikul_marketplace' ), $product_data->get_title(), $product_data->get_stock_quantity() ),
						   );
					}

					if ( $product_data->is_type( 'variable' ) && empty( $variation_id ) && empty( $variation ) ) {
							 return array(
								 'success' => false,
								 'message' => __( 'You cannot add this product to the cart.', 'mobikul_marketplace' ),
							 );
					}

						// Stock check - this time accounting for whats already in-cart
					if ( $managing_stock = $product_data->managing_stock() ) {
							 $products_qty_in_cart = $this->get_cart_item_quantities();

						if ( $product_data->is_type( 'variation' ) && true === $managing_stock ) {
							$check_qty = isset( $products_qty_in_cart[ $variation_id ] ) ? $products_qty_in_cart[ $variation_id ] : 0;
						} else {
							  $check_qty = isset( $products_qty_in_cart[ $product_id ] ) ? $products_qty_in_cart[ $product_id ] : 0;
						}

							/**
							 * Check stock based on all items in the cart.
							 */
						if ( $this->request_type !== 'update' ) {
							if ( ! $product_data->has_enough_stock( $product_temp_cart_quantity ) ) {
								return array(
									'success' => false,
									'message' => sprintf( __( 'You cannot add that amount of %1$s to the cart because there is not enough stock ( %2$s remaining ).', 'mobikul_marketplace' ), $product_data->get_title(), $product_temp_cart_quantity ),
								);
							}
						} elseif ( ! $product_data->has_enough_stock( $check_qty + $quantity ) ) {
							return array(
								'success' => false,
								'message' => sprintf( __( 'You cannot add that amount of %1$s to the cart because there is not enough stock ( %2$s remaining ).', 'mobikul_marketplace' ), $product_data->get_title(), $check_qty ),
							);
						}
					}
				}

				  // If cart_item_key is set, the item is already in the cart
				if ( $cart_item_key || $request_type == 'delete' ) {
					$cart_item_data = array_filter( $cart_item_data );

						$this->cart_contents = array_merge(
							$cart_item_data,
							array(
								$cart_item_key => array(
									'product_id'   => $product_id,
									'variation_id' => $variation_id,
									'variation'    => $variation,
									'quantity'     => $quantity,
									'data'         => $product_data,
								),
							)
						);
					if ( $request_type == 'add' ) {
						$new_quantity = $quantity + $cart_item_data[ $cart_item_key ]['quantity'];
					} elseif ( $request_type == 'update' ) {
						$new_quantity = $quantity;
					} elseif ( $request_type == 'delete' ) {
						$new_quantity = 0;
					}
						$this->set_quantity( $cart_item_key, $new_quantity, true, $coupons );
				} else {
						$cart_item_key = $cart_id;

					$cart_item_data = array_filter( $cart_item_data );

						$this->cart_contents = array_merge(
							$cart_item_data,
							array(
								$cart_item_key => array(
									'product_id'   => $product_id,
									'variation_id' => $variation_id,
									'variation'    => $variation,
									'quantity'     => $quantity,
									'data'         => $product_data,
								),
							)
						);
						$item_count          = 0;
						// Add item after merging with $cart_item_data - hook to allow plugins to modify cart item.
					if ( ! empty( $this->cart_contents ) ) {
						foreach ( $this->cart_contents as $cid_key => $cid_value ) {
							if ( is_array( $cid_value ) && array_key_exists( 'product_id', $cid_value ) ) {
								$item_count++;
							}
						}
					}
					if ( $item_count == 1 || empty( $coupons ) ) {
						   $this->calculate_totals();
					}
				}
				  return $cart_item_key;
			} catch ( Exception $e ) {
				if ( $e->getMessage() ) {
						wc_add_notice( $e->getMessage(), 'error' );
				}
				return false;
			}
		}

		public function generate_cart_id( $product_id, $variation_id = 0, $variation = array(), $cart_item_data = array() ) {
			$id_parts = array( $product_id );

			if ( $variation_id && 0 != $variation_id ) {
				$id_parts[] = $variation_id;
			}

			if ( is_array( $variation ) && ! empty( $variation ) ) {
				$variation_key = '';
				foreach ( $variation as $key => $value ) {
					$variation_key .= trim( $key ) . trim( $value );
				}
				$id_parts[] = $variation_key;
			}

			if ( is_array( $cart_item_data ) && ! empty( $cart_item_data ) ) {
				$cart_item_data_key = '';
				foreach ( $cart_item_data as $key => $value ) {
					if ( is_array( $value ) || is_object( $value ) ) {
						$value = http_build_query( $value );
					}
					$cart_item_data_key .= trim( $key ) . trim( $value );
				}
				$id_parts[] = $cart_item_data_key;
			}

			return apply_filters( 'woocommerce_cart_id', md5( implode( '_', $id_parts ) ), $product_id, $variation_id, $variation, $cart_item_data );
		}

		public function find_product_in_cart( $cart_id = false, $session_value ) {
			if ( $cart_id !== false ) {
				 $session_value = maybe_unserialize( $session_value );
				if ( is_array( $session_value ) && ! empty( $session_value ) && isset( $session_value['cart'] ) ) {
					$session_value['cart'] = $session_value['cart'] ? $session_value['cart'] : array();
					foreach ( $session_value['cart'] as $cart_item_key => $cart_item ) {
						if ( $cart_item_key == $cart_id ) {
									 return $cart_item_key;
						}
					}
				}
			}
				return '';
		}

		public function calculate_totals() {
			WC()->cart = new \Wc_Cart();

			WC()->session = new \WC_Session_Handler();

			WC()->customer = new \WC_Customer( $this->reg_user_id );

			$this->coupons = $this->get_coupons();

			do_action( 'woocommerce_before_calculate_totals', $this );

			// new \WC_Cart_Totals( WC()->cart );

			$tax_rates = array();

			$shop_tax_rates = array();

			$cart = $this->get_cart();
			/**
			 * Calculate subtotals for items. This is done first so that discount logic can use the values.
			 */
			foreach ( $cart as $cart_item_key => $values ) {
				$_product = $values['data'];

				$line_price = get_post_meta( $_product->get_id(), '_sale_price', true ) > 0 ? get_post_meta( $_product->get_id(), '_sale_price', true ) * $values['quantity'] : get_post_meta( $_product->get_id(), '_price', true ) * $values['quantity'];

				$line_subtotal = 0;

				$line_subtotal_tax = 0;

				/**
				 * No tax to calculate.
				 */

				if ( ! $_product->is_taxable() ) {
						// Subtotal is the undiscounted price
						$this->subtotal += $line_price;

						$this->subtotal_ex_tax += $line_price;

				} elseif ( $this->prices_include_tax ) {
						// Get base tax rates
					if ( empty( $shop_tax_rates[ $_product->get_tax_class() ] ) ) {
						$shop_tax_rates[ $_product->get_tax_class() ] = \WC_Tax::get_base_tax_rates( $_product->get_tax_class() );
					}
						// Get item tax rates
					if ( empty( $tax_rates[ $_product->get_tax_class() ] ) ) {
						$tax_rates[ $_product->get_tax_class() ] = \WC_Tax::get_rates( $_product->get_tax_class() );
					}
						$base_tax_rates = $shop_tax_rates[ $_product->get_tax_class() ];

						$item_tax_rates = $tax_rates[ $_product->get_tax_class() ];

					if ( $item_tax_rates !== $base_tax_rates && apply_filters( 'woocommerce_adjust_non_base_location_prices', true ) ) {
						// Work out a new base price without the shop's base tax
						$taxes = \WC_Tax::calc_tax( $line_price, $base_tax_rates, true, true );

						// Now we have a new item price (excluding TAX)
						$line_subtotal = $line_price - array_sum( $taxes );

						// Now add modified taxes
						$tax_result = \WC_Tax::calc_tax( $line_subtotal, $item_tax_rates );

						$line_subtotal_tax = array_sum( $tax_result );

						/**
						 * Regular tax calculation (customer inside base and the tax class is unmodified.
						 */
					} else {
						// Calc tax normally
						$taxes = \WC_Tax::calc_tax( $line_price, $item_tax_rates, true );

						$line_subtotal_tax = array_sum( $taxes );

						$line_subtotal = $line_price - array_sum( $taxes );
					}
				} else {
						// Get item tax rates
					if ( empty( $tax_rates[ $_product->get_tax_class() ] ) ) {
						$tax_rates[ $_product->get_tax_class() ] = \WC_Tax::get_rates( $_product->get_tax_class() );
					}
						$item_tax_rates = $tax_rates[ $_product->get_tax_class() ];
						// Base tax for line before discount - we will store this in the order data
						$taxes = \WC_Tax::calc_tax( $line_price, $item_tax_rates );

						$line_subtotal_tax = array_sum( $taxes );

						$line_subtotal = $line_price;
				}
				// Add to main subtotal
				$this->subtotal += $line_subtotal + $line_subtotal_tax;

				$this->subtotal_ex_tax += $line_subtotal;
			}

			// Order cart items by price so coupon logic is 'fair' for customers and not based on order added to cart.
			uasort( $cart, array( $this, 'sort_by_subtotal' ) );

			/**
			 * Calculate totals for items.
			 */

			foreach ( $cart as $cart_item_key => $values ) {
				$_product = $values['data'];

				// Prices
				$base_price = get_post_meta( $_product->get_id(), '_sale_price', true ) > 0 ? get_post_meta( $_product->get_id(), '_sale_price', true ) : get_post_meta( $_product->get_id(), '_price', true );

				$line_price = $base_price * $values['quantity'];

				// Tax data
				$taxes = array();

				$discounted_taxes = array();
				/**
				 * No tax to calculate.
				 */
				if ( ! $_product->is_taxable() ) {
					// Discounted Price (price with any pre-tax discounts applied)
					$discounted_price = $this->get_discounted_price( $values, $base_price, true );

					$line_subtotal_tax = 0;

					$line_subtotal = $line_price;

					$line_tax = 0;

					$line_total = round( $discounted_price * $values['quantity'], wc_get_rounding_precision() );

					/**
					 * Prices include tax.
					 */
				} elseif ( $this->prices_include_tax ) {

						$base_tax_rates = $shop_tax_rates[ $_product->get_tax_class() ];

						$item_tax_rates = $tax_rates[ $_product->get_tax_class() ];

					if ( $item_tax_rates !== $base_tax_rates && apply_filters( 'woocommerce_adjust_non_base_location_prices', true ) ) {
						// Work out a new base price without the shop's base tax
						$taxes = \WC_Tax::calc_tax( $line_price, $base_tax_rates, true, true );

						// Now we have a new item price (excluding TAX)
						$line_subtotal = round( $line_price - array_sum( $taxes ), wc_get_rounding_precision() );

						$taxes = \WC_Tax::calc_tax( $line_subtotal, $item_tax_rates );

						$line_subtotal_tax = array_sum( $taxes );

						// Adjusted price (this is the price including the new tax rate)
						$adjusted_price = ( $line_subtotal + $line_subtotal_tax ) / $values['quantity'];

						// Apply discounts and get the discounted price FOR A SINGLE ITEM
						$discounted_price = $this->get_discounted_price( $values, $adjusted_price, true );

						// Convert back to line price and round nicely
						$discounted_line_price = round( $discounted_price * $values['quantity'], $this->dp );

						// Now use rounded line price to get taxes.
						$discounted_taxes = \WC_Tax::calc_tax( $discounted_line_price, $item_tax_rates, true );

						$line_tax = array_sum( $discounted_taxes );

						$line_total = $discounted_line_price - $line_tax;
						/**
						 * Regular tax calculation (customer inside base and the tax class is unmodified.
						 */
					} else {
						// Work out a new base price without the item tax
						$taxes = \WC_Tax::calc_tax( $line_price, $item_tax_rates, true );

						// Now we have a new item price (including TAX)
						$line_subtotal = $line_price;

						$line_subtotal_tax = array_sum( $taxes );

						// Calc prices and tax (discounted)
						$discounted_price = $this->get_discounted_price( $values, $base_price, true );

						// Convert back to line price and round nicely
						$discounted_line_price = round( $discounted_price * $values['quantity'], $this->dp );

						// Now use rounded line price to get taxes.
						$discounted_taxes = \WC_Tax::calc_tax( $discounted_line_price, $item_tax_rates, true );

						$line_tax = array_sum( $discounted_taxes );

						$line_total = $discounted_line_price - $line_tax;
					}

					/**
					 * Prices exclude tax.
					 */
				} else {
					$item_tax_rates = $tax_rates[ $_product->get_tax_class() ];
					// Work out a new base price without the shop's base tax
					$taxes = \WC_Tax::calc_tax( $line_price, $item_tax_rates, false );

					// Now we have the item price (excluding TAX)
					$line_subtotal = $line_price;

					$line_subtotal_tax = array_sum( $taxes );
					// Now calc product rates
					$discounted_price = $this->get_discounted_price( $values, $base_price, true );

					$discounted_taxes = \WC_Tax::calc_tax( $discounted_price * $values['quantity'], $item_tax_rates );

					$discounted_tax_amount = array_sum( $discounted_taxes );

					$line_tax = $discounted_tax_amount;

					$line_total = $discounted_price * $values['quantity'];

					// Tax rows - merge the totals we just got
					if ( count( $discounted_taxes ) > 0 ) {
							$this_taxes = array_merge( $this->taxes, $discounted_taxes );

						foreach ( $this_taxes as $key ) {
								$this->taxes[ $key ] = ( isset( $discounted_taxes[ $key ] ) ? $discounted_taxes[ $key ] : 0 ) + ( isset( $this->taxes[ $key ] ) ? $this->taxes[ $key ] : 0 );
						}
					}
				}

					// Cart contents total is based on discounted prices and is used for the final total calculation
					$this->cart_contents_total += $line_total;

					// Store costs + taxes for lines
					$this->cart_contents[ $cart_item_key ]['line_total'] = round( $line_total, 2 );

					$this->cart_contents[ $cart_item_key ]['line_tax'] = round( $line_tax, 2 );

					$this->cart_contents[ $cart_item_key ]['line_subtotal'] = round( $line_subtotal, 2 );

					$this->cart_contents[ $cart_item_key ]['line_subtotal_tax'] = round( $line_subtotal_tax, 2 );

					// Store rates ID and costs - Since 2.2
					$this->cart_contents[ $cart_item_key ]['line_tax_data'] = array(
						'total'    => $discounted_taxes,
						'subtotal' => $taxes,
					);
			}

					$request_type = $this->request_type;

					$cart_subtotal = 0;

			foreach ( $this->cart_contents as $key => $value ) {
				$value['line_subtotal'] = isset( $value['line_subtotal'] ) ? $value['line_subtotal'] : '';
				$cart_subtotal         += $value['line_subtotal'];
			}

					WC()->cart->set_subtotal( $cart_subtotal );

					// Calculate the Shipping
					$this->calculate_shipping();
					// Trigger the fees API where developers can add fees to the cart
					$this->calculate_fees();

					$calculating_shipping_cart = $this->cart_contents;

					$countyP = 0.00;
			if ( ! empty( $calculating_shipping_cart ) ) {
				foreach ( $calculating_shipping_cart as $ckey => $cvalue ) {
					$cvalue['line_subtotal_tax'] = isset( $cvalue['line_subtotal_tax'] ) ? $cvalue['line_subtotal_tax'] : '';

					$countyP += $cvalue['line_subtotal_tax'];
				}
			}
			$temp_tax_var = $countyP + $this->calculating_shipping['shipping_taxes'];
			// Total up/round taxes and shipping taxes
			if ( $this->round_at_subtotal ) {
					$this->tax_total = $countyP + $this->calculating_shipping['shipping_taxes'];

					@$this->shipping_tax_total = array_sum( $this->calculating_shipping['shipping_taxes'] );
					$this->taxes               = $countyP;

					@$this->shipping_taxes = array_map( array( 'WC_Tax', 'round' ), $this->calculating_shipping['shipping_taxes'] );
			} else {
					$this->tax_total = ! empty( $temp_tax_var ) ? $countyP + $this->calculating_shipping['shipping_taxes'] : floatval( 0.00 );

					$this->shipping_tax_total = ! empty( $this->calculating_shipping['shipping_taxes'] ) ? $this->calculating_shipping['shipping_taxes'] : floatval( 0.00 );

					$this->shipping_taxes = ! empty( $this->calculating_shipping['shipping_taxes'] ) ? $this->calculating_shipping['shipping_taxes'] : floatval( 0.00 );

					$this->taxes = $countyP;
			}
			$this->calculating_shipping['taxes'] = $countyP;

			$this->calculating_shipping['tax_total'] = ! empty( $temp_tax_var ) ? $countyP + $this->calculating_shipping['shipping_taxes'] : floatval( 0.00 );
			// VAT exemption done at this point - so all totals are correct before exemption
			if ( WC()->customer->is_vat_exempt() ) {
						$this->remove_taxes();
			}

			// Allow plugins to hook and alter totals before final total is calculated
			do_action( 'woocommerce_calculate_totals', $this );

			$this->total = max( 0, apply_filters( 'woocommerce_calculated_total', round( $this->cart_contents_total + $this->tax_total + $this->shipping_tax_total + $this->shipping_total + $this->fee_total, $this->dp ), $this ) );

			$this->calculating_shipping['total'] = $this->total;

			do_action( 'woocommerce_after_calculate_totals', $this );

			$this->cart_contents_total += $this->tax_total + $this->shipping_total;

			$this->cart_contents_total = round( $this->cart_contents_total, 2 );

			$ship_sess = WC()->session->get( 'shipping_sess_cost' );

			if ( isset( $this->reg_user_id ) && ! empty( $ship_sess ) ) {
				update_user_meta( $this->reg_user_id, 'mkwc_shipping_sess_cost', $ship_sess );
			}
		}

		public function calculate_fees() {
				// Reset fees before calculation
				$this->fee_total = 0;

				$this->fees = array();

				// Fire an action where developers can add their fees
				do_action( 'woocommerce_cart_calculate_fees', $this );

				// If fees were added, total them and calculate tax
			if ( ! empty( $this->fees ) ) {
				foreach ( $this->fees as $fee_key => $fee ) {
					  $this->fee_total += $fee->amount;

					if ( $fee->taxable ) {
						// Get tax rates
						$tax_rates = \WC_Tax::get_rates( $fee->get_tax_class() );

						$fee_taxes = \WC_Tax::calc_tax( $fee->amount, $tax_rates, false );

						if ( ! empty( $fee_taxes ) ) {
								// Set the tax total for this fee
								$this->fees[ $fee_key ]->tax = array_sum( $fee_taxes );

								// Set tax data - Since 2.2
								$this->fees[ $fee_key ]->tax_data = $fee_taxes;

								$this_taxes_fee = array_merge( $this->taxes, $fee_taxes );

								// Tax rows - merge the totals we just got
							foreach ( $this_taxes_fee as $key ) {
								$this->taxes[ $key ] = ( isset( $fee_taxes[ $key ] ) ? $fee_taxes[ $key ] : 0 ) + ( isset( $this->taxes[ $key ] ) ? $this->taxes[ $key ] : 0 );
							}
						}
					}
				}
			}
		}

		public function is_purchasable( $product_data ) {
			$purchasable = true;

			// Products must exist of course
			if ( ! $this->exists( $product_data ) ) {
				$purchasable = false;
				// Other products types need a price to be set
			} elseif ( $product_data->get_price() === '' ) {
				$purchasable = false;
				// Check the product is published
			} elseif ( $product_data->get_status() !== 'publish' ) {
				$purchasable = false;
			}
			return apply_filters( 'woocommerce_is_purchasable', $purchasable, $product_data );
		}

		public function get_cart() {
			if ( ! did_action( 'wp_loaded' ) ) {
				_doing_it_wrong( __FUNCTION__, __( 'Get cart should not be called before the wp_loaded action.', 'mobikul_marketplace' ), '2.3' );
			}
				return array_filter( (array) $this->cart_contents );
		}

		/**
		 * Set session expiration.
		 */
		public function set_session_expiration() {
				$this->_session_expiring = time() + intval( apply_filters( 'wc_session_expiring', 60 * 60 * 47 ) ); // 47 Hours.

			  $this->_session_expiration = time() + intval( apply_filters( 'wc_session_expiration', 60 * 60 * 48 ) ); // 48 Hours.
		}

		public function update_session_timestamp( $android_id, $timestamp, $table_name ) {
				global $wpdb;

				$wpdb->update(
					$table_name,
					array(
						'session_expiry' => $timestamp,
					),
					array(
						'session_key' => $android_id,
					),
					array(
						'%d',
					)
				);
		}

		/**
		 * Get array of applied coupon objects and codes.
		 *
		 * @return array of applied coupons
		 */
		public function get_coupons( $deprecated = null ) {
			$coupons = array();

			if ( 'order' === $deprecated ) {
				return $coupons;
			}

			foreach ( $this->get_applied_coupons() as $code ) {
				$coupon = new \WC_Coupon( $code );

				$coupons[ $code ] = $coupon;
			}
			return $coupons;
		}

		/**
		 * Gets the array of applied coupon codes.
		 *
		 * @return array of applied coupons
		 */
		public function get_applied_coupons() {
			return $this->applied_coupons;
		}

		/**
		 * Function to apply discounts to a product and get the discounted price (before tax is applied).
		 *
		 * @param mixed $values
		 * @param mixed $price
		 * @param bool  $add_totals (default: false)
		 * @return float price
		 */
		public function get_discounted_price( $values, $price, $add_totals = false ) {
			if ( ! $price ) {
				return $price;
			}

			$undiscounted_price = $price;

			if ( ! empty( $this->coupons ) ) {
				$product = $values['data'];

				foreach ( $this->coupons as $code => $coupon ) {
					if ( $coupon->is_valid() && ( $coupon->is_valid_for_product( $product, $values ) || $coupon->is_valid_for_cart() ) ) {
						$discount_amount = $this->get_coupon_discount_amount( 'yes' === get_option( 'woocommerce_calc_discounts_sequentially', 'no' ) ? $price : $undiscounted_price, $coupon, $values, true );

						$discount_amount = min( $price, $discount_amount );

						$price = max( $price - $discount_amount, 0 );

						// Store the totals for DISPLAY in the cart
						if ( $add_totals ) {
							if ( $coupon->is_type( array( 'percent_product', 'percent' ) ) || $coupon->is_type( 'fixed_product' ) ) {
								$total_discount = $discount_amount * $values['quantity'];
							} else {
								$total_discount = $discount_amount;
							}
							$total_discount_tax = 0;

							if ( wc_tax_enabled() ) {
								$tax_rates = \WC_Tax::get_rates( $product->get_tax_class() );

								$taxes = \WC_Tax::calc_tax( $discount_amount, $tax_rates, $this->prices_include_tax );

								if ( $coupon->is_type( array( 'percent_product', 'percent' ) ) || $coupon->is_type( 'fixed_product' ) ) {
												$total_discount_tax = \WC_Tax::get_tax_total( $taxes ) * $values['quantity'];
								} else {
									$this->discount_cart_tax = 0;
									$total_discount_tax      = \WC_Tax::get_tax_total( $taxes );
								}
											$total_discount = $this->prices_include_tax ? $total_discount - $total_discount_tax : $total_discount;

											$this->discount_cart_tax += $total_discount_tax;
							}

							if ( $coupon->is_type( 'fixed_cart' ) ) {
								if ( empty( $this->coupon_discount_amounts ) || ! in_array( $code, array_column( $this->coupon_discount_amounts, 'coupon_code' ) ) ) {
									$cart_count = $this->get_cart() ? count( $this->get_cart() ) : 0;

									$this->discount_cart += ( $total_discount * $cart_count );

									$total_discount = $total_discount * $cart_count;

									$this->increase_coupon_discount_amount( $code, $total_discount, $total_discount_tax );

									$this->increase_coupon_applied_count( $code, $values['quantity'] );
								}
							} else {
								$this->discount_cart += $total_discount;

								$this->increase_coupon_discount_amount( $code, $total_discount, $total_discount_tax );

								$this->increase_coupon_applied_count( $code, $values['quantity'] );
							}
						}
					}

					// If the price is 0, we can stop going through coupons because there is nothing more to discount for this product.
					if ( 0 >= $price ) {
						break;
					}
				}
			}
			return apply_filters( 'woocommerce_get_discounted_price', $price, $values, $this );
		}

		public function get_coupon_discount_amount( $discounting_amount, $coupon, $cart_item = null, $single = false ) {
			$discount = 0;

			$cart_item_qty = is_null( $cart_item ) ? 1 : $cart_item['quantity'];

			if ( $coupon->is_type( array( 'percent_product', 'percent' ) ) ) {
				$discount = $coupon->get_amount() * ( $discounting_amount / 100 );
			} elseif ( $coupon->is_type( 'fixed_cart' ) && ! is_null( $cart_item ) && $this->subtotal_ex_tax ) {
				$cart_count = $this->get_cart() ? count( $this->get_cart() ) : 0;

				$discount = $coupon->get_amount() / $cart_count;
			} elseif ( $coupon->is_type( 'fixed_product' ) ) {
				$discount = min( $coupon->get_amount(), $discounting_amount );

				$discount = $single ? $discount : $discount * $cart_item_qty;
			}
			$discount = min( $discount, $discounting_amount );

			// Handle the limit_usage_to_x_items option
			if ( $coupon->is_type( array( 'percent_product', 'fixed_product' ) ) ) {
				if ( $discounting_amount ) {
					if ( '' == $coupon->limit_usage_to_x_items ) {
						$limit_usage_qty = $cart_item_qty;
					} else {
						$limit_usage_qty = min( $coupon->limit_usage_to_x_items, $cart_item_qty );

						$coupon->limit_usage_to_x_items = max( 0, $coupon->limit_usage_to_x_items - $limit_usage_qty );
					}
					if ( $single ) {
						$discount = ( $discount * $limit_usage_qty ) / $cart_item_qty;
					} else {
						$discount = ( $discount / $cart_item_qty ) * $limit_usage_qty;
					}
				}
			}
			$discount = wc_cart_round_discount( $discount, wc_get_rounding_precision() );

			return $discount;
		}

		/**
		 * Store how much discount each coupon grants.
		 *
		 * @access private
		 * @param string $code
		 * @param double $amount
		 * @param double $tax
		 */
		public function increase_coupon_discount_amount( $code, $amount, $tax ) {
				$this->coupon_discount_amounts[] = array(
					'coupon_code'   => $code,
					'coupon_amount' => isset( $this->coupon_discount_amounts[ $code ] ) ? $this->coupon_discount_amounts[ $code ] + $amount : $amount,
					$code           => isset( $this->coupon_discount_amounts[ $code ] ) ? $this->coupon_discount_amounts[ $code ] + $amount : $amount,

				);

				$this->coupon_discount_tax_amounts[ $code ] = isset( $this->coupon_discount_tax_amounts[ $code ] ) ? $this->coupon_discount_tax_amounts[ $code ] + $tax : $tax;
		}

		/**
		 * Store how many times each coupon is applied to cart/items.
		 *
		 * @access private
		 * @param string $code
		 * @param int    $count
		 */
		public function increase_coupon_applied_count( $code, $count = 1 ) {
			if ( empty( $this->coupon_applied_count[ $code ] ) ) {
				$this->coupon_applied_count[ $code ] = 0;
			}
			$this->coupon_applied_count[ $code ] += $count;
		}

		public function exists( $product_data ) {
			return empty( $product_data->get_post_data() ) ? false : true;
		}

		public function calculate_shipping() {
				global $wpdb;

				$_table = $wpdb->prefix . 'woocommerce_sessions';

				WC()->customer = new \WC_Customer( $this->reg_user_id );

				$country = WC()->customer->get_shipping_country();

				$state = WC()->customer->get_shipping_state();

				$postcode = WC()->customer->get_shipping_postcode();

				$city = WC()->customer->get_shipping_city();

			if ( $country ) {
				WC()->customer->set_location( $country, $state, $postcode, $city );

				WC()->customer->set_shipping_location( $country, $state, $postcode, $city );
			} else {
				WC()->customer->set_billing_address_to_base();

				WC()->customer->set_shipping_address_to_base();
			}
				WC()->customer->set_calculated_shipping( true );
				$value = WC()->customer->get_calculated_shipping();
			if ( empty( WC()->shipping ) ) {
				WC()->shipping = new \WC_Shipping();
			}
			if ( empty( WC()->session ) ) {
				WC()->session = new \WC_Session_Handler();
			}

			if ( $this->needs_shipping() && $this->show_shipping() ) {
				WC()->cart = new \WC_Cart();
				WC()->shipping->calculate_shipping( $this->get_shipping_packages() );
			} else {
				WC()->shipping->reset_shipping();
			}

			$packages = WC()->shipping->get_packages();

			foreach ( $packages as $i => $package ) {
				$available_method = $package['rates'];

				$method = current( $available_method );

				$methods = $available_method;
			}
				$this->shipping_methods = array();

			if ( isset( $methods ) && count( $methods ) > 0 ) {
				foreach ( $methods as $key => $value ) {
					array_push(
						$this->shipping_methods,
						array(
							'method_id'    => $value->id,
							'method_title' => $value->label,
							'cost'         => html_entity_decode( get_woocommerce_currency_symbol() ) . $value->cost,
							'tax'          => ! empty( $value->taxes ) ? array_sum( $value->taxes ) : 0.00,
							'instance_id'  => $value->instance_id,
						)
					);

					if ( isset( $this->shipping_method_id ) && $this->shipping_method_id && $value->id == $this->shipping_method_id ) {
						$method = $value;
					}
				}
			}
				$this->calculating_shipping = array();

				$session_check = WC()->session->get_session( $this->reg_user_id );

			if ( ! empty( $session_check ) ) {
				$this->calculating_shipping = maybe_unserialize( WC()->session->get_session( $this->reg_user_id )['cart_totals'] );
			}

				$this->calculating_shipping['shipping_total'] = isset( $method->cost ) ? $method->cost : '';

				$this->calculating_shipping['shipping_taxes'] = ! empty( $method->taxes ) ? array_sum( $method->taxes ) : 0.00;

				// Get totals for the chosen shipping method
				$this->shipping_total = $this->calculating_shipping['shipping_total'];  // Shipping Total

			$this->shipping_taxes  = $this->calculating_shipping['shipping_taxes'];
			$this->shipping_method = array(
				'method_id'    => isset( $method->id ) ? $method->id : '',
				'method_title' => isset( $method->label ) ? $method->label : '',
			);
		}

		public function get_shipping_packages( $data = array() ) {
			// Packages array for storing 'carts'
			$packages = array();

			$packages[0]['contents'] = $this->get_cart();       // Items in the package

			$packages[0]['contents_cost'] = 0;                       // Cost of items in the package, set below

			$packages[0]['applied_coupons'] = $this->applied_coupons;

			$packages[0]['user']['ID'] = isset( $this->reg_user_id ) ? $this->reg_user_id : 0;

			if ( $this->reg_user_id && is_numeric( $this->reg_user_id ) ) {
				WC()->customer                           = new \Wc_Customer( $this->reg_user_id );
				$packages[0]['destination']['country']   = WC()->customer->get_shipping_country();
				$packages[0]['destination']['state']     = WC()->customer->get_shipping_state();
				$packages[0]['destination']['postcode']  = WC()->customer->get_shipping_postcode();
				$packages[0]['destination']['city']      = WC()->customer->get_shipping_city();
				$packages[0]['destination']['address']   = WC()->customer->get_shipping_address();
				$packages[0]['destination']['address_2'] = WC()->customer->get_shipping_address_2();
			} else {
				if ( ! $data ) {
					$data                                = array();
					$guest_cart                          = $this->mkwc_get_cart_custom_details( $this->reg_user_id );
					$customer_data                       = isset( $guest_cart['customer'] ) ? maybe_unserialize( $guest_cart['customer'] ) : '';
					$data['shipping_address']['country'] = isset( $customer_data['shipping_country'] ) ? $customer_data['shipping_country'] : '';
					$data['shipping_address']['state']   = isset( $customer_data['shipping_state'] ) ? $customer_data['shipping_state'] : '';
							$data['shipping_address']['postcode']  = isset( $customer_data['shipping_postcode'] ) ? $customer_data['shipping_postcode'] : '';
							$data['shipping_address']['city']      = isset( $customer_data['shipping_city'] ) ? $customer_data['shipping_city'] : '';
							$data['shipping_address']['address_1'] = isset( $customer_data['shipping_address_1'] ) ? $customer_data['shipping_address_1'] : '';
					$data['shipping_address']['address_2']         = isset( $customer_data['shipping_address_2'] ) ? $customer_data['shipping_address_2'] : '';
				}
				$packages[0]['destination']['country']           = $data['shipping_address']['country'];
						$packages[0]['destination']['state']     = $data['shipping_address']['state'];
						$packages[0]['destination']['postcode']  = $data['shipping_address']['postcode'];
						$packages[0]['destination']['city']      = $data['shipping_address']['city'];
						$packages[0]['destination']['address']   = $data['shipping_address']['address_1'];
						$packages[0]['destination']['address_2'] = $data['shipping_address']['address_2'];
			}

			foreach ( $this->get_cart() as $item ) {
				if ( $item['data']->needs_shipping() ) {
					if ( isset( $item['line_total'] ) ) {
						$packages[0]['contents_cost'] += $item['line_total'];
					}
				}
			}

			return apply_filters( 'mkwc_woocommerce_cart_shipping_packages', $packages );
		}

		public function needs_shipping() {
			// If shipping is disabled or not yet configured, we can skip this.
			if ( ! wc_shipping_enabled() || 0 === wc_get_shipping_method_count( true ) ) {
				return false;
			}
			$needs_shipping = false;

			if ( ! empty( $this->cart_contents ) ) {
				foreach ( $this->cart_contents as $cart_item_key => $values ) {
					$_product = isset( $values['data'] ) ? $values['data'] : '';

					if ( ! $_product ) {
						continue;
					}
					if ( $_product->needs_shipping() ) {
						$needs_shipping = true;
					}
				}
			}
			return apply_filters( 'woocommerce_cart_needs_shipping', $needs_shipping );
		}

		/**
		 * Sees if the customer has entered enough data to calc the shipping yet.
		 *
		 * @return bool
		 */
		public function show_shipping() {
			if ( ! wc_shipping_enabled() || ! is_array( $this->cart_contents ) ) {
				return false;
			}
			if ( 'yes' === get_option( 'woocommerce_shipping_cost_requires_address' ) ) {
				if ( ! WC()->customer->has_calculated_shipping() ) {
					if ( ! WC()->customer->get_shipping_country() || ( ! WC()->customer->get_shipping_state() && ! WC()->customer->get_shipping_postcode() ) ) {
						return false;
					}
				}
			}
			return apply_filters( 'woocommerce_cart_ready_to_calc_shipping', true );
		}

		public function get_cart_item_quantities() {
			$quantities = array();

			foreach ( $this->get_cart() as $cart_item_key => $values ) {
				$_product = $values['data'];

				if ( $_product->is_type( 'variation' ) && true === $_product->managing_stock() ) {
					// Variation has stock levels defined so its handled individually
					$quantities[ $values['variation_id'] ] = isset( $quantities[ $values['variation_id'] ] ) ? $quantities[ $values['variation_id'] ] + $values['quantity'] : $values['quantity'];
				} else {
					$quantities[ $values['product_id'] ] = isset( $quantities[ $values['product_id'] ] ) ? $quantities[ $values['product_id'] ] + $values['quantity'] : $values['quantity'];
				}
			}
			return $quantities;
		}

		public function set_quantity( $cart_item_key, $quantity = 1, $refresh_totals = true, $coupons ) {
			if ( $quantity == 0 || $quantity < 0 ) {
				do_action( 'woocommerce_before_cart_item_quantity_zero', $cart_item_key );

				unset( $this->cart_contents[ $cart_item_key ] );
			} else {
				$old_quantity = $this->cart_contents[ $cart_item_key ]['quantity'];

				$this->cart_contents[ $cart_item_key ]['quantity'] = $quantity;

				do_action( 'woocommerce_after_cart_item_quantity_update', $cart_item_key, $quantity, $old_quantity );
			}

			$this->temp_count += 1;

			if ( $refresh_totals && ( empty( $coupons ) || gettype( $coupons ) == 'string' ) && $this->request_type != 'update' ) {
				$this->calculate_totals();
			}
			if ( $this->request_type == 'update' && $this->temp_count == $this->update_item_count && ( $this->update_item_count > 1 || empty( $coupons ) ) ) {
				$this->calculate_totals();
			}
			return true;
		}

		public function get_coupon_message_custom( $coupon_code ) {
			if ( empty( WC()->cart ) ) {
				WC()->cart = new \Wc_Cart();
			}
			if ( empty( WC()->session ) ) {
				WC()->session = new \WC_Session_Handler();
			}

			$the_coupon = new \WC_Coupon( $coupon_code );

			$discounts = new \WC_Discounts( WC()->cart );

			$valid = $discounts->is_coupon_valid( $the_coupon );

			if ( is_wp_error( $valid ) ) {
				$message = $valid->get_error_message();

				return html_entity_decode( strip_tags( $message ) );
			} else {
				return 'ok';
			}
		}

		/**
		 * get cart item by customer id
		 *
		 * @param $customer_id
		 * @return $cart_data
		 */
		public function mkwc_registered_cart_items( $customer_id ) {
			global $wpdb;

			$this->customer_id = $customer_id;
			$true              = false;

			try {
				$table_name = $wpdb->prefix . 'woocommerce_sessions';

				$session = $wpdb->get_results( "SELECT * FROM $table_name WHERE session_key = '$customer_id'" );

				$session_id = get_user_meta( $customer_id, 'user_web_session', true );

				$session_cookie = $wpdb->get_results( "SELECT * FROM $table_name WHERE session_key = '$session_id'" );

				if ( ( ! empty( $session ) && is_array( maybe_unserialize( $session[0]->session_value ) ) ) || ! empty( $session_cookie ) && is_array( maybe_unserialize( $session_cookie[0]->session_value ) ) ) {
					$cart_data         = maybe_unserialize( $session[0]->session_value );
					$cart_data['cart'] = isset( $cart_data['cart'] ) ? maybe_unserialize( $cart_data['cart'] ) : array();
					if ( empty( $cart_data['cart'] ) ) {
						$true = true;
					}
					if ( ! empty( $session_cookie ) && is_array( maybe_unserialize( $session_cookie[0]->session_value ) ) ) {
						$cart_data_cok = maybe_unserialize( $session_cookie[0]->session_value );

						$cart_data_cok_final['cart'] = maybe_unserialize( $cart_data_cok['cart'] );

						$cart_data['cart'] = array_merge( $cart_data['cart'], $cart_data_cok_final['cart'] );
					}
					if ( empty( $cart_data['cart'] ) ) {
						$cart_p_data = get_user_meta( $customer_id, '_woocommerce_persistent_cart_' . get_current_blog_id(), true );
						if ( $cart_p_data && isset( $cart_p_data['cart'] ) ) {
							$cart_data['cart'] = $cart_p_data['cart'];
						}
					}
					if ( $true ) {
						$wpdb->query(
							$wpdb->prepare(
								"INSERT INTO {$wpdb->prefix}woocommerce_sessions (`session_key`, `session_value`, `session_expiry`) VALUES (%s, %s, %d)
                       ON DUPLICATE KEY UPDATE `session_value` = VALUES(`session_value`), `session_expiry` = VALUES(`session_expiry`)",
								$customer_id,
								maybe_serialize( $cart_data ),
								$this->_session_expiration
							)
						);
					}
					$this->reg_user_id = $customer_id;

					$this->mkwc_custom_calculate_totals( $cart_data );

					if ( ! empty( $cart_data['cart'] ) ) {
						$cart_session = $this->cart_contents;

						if ( ! empty( $this->applied_coupons ) ) {
							$applied_coupons = $this->applied_coupons;
						} else {
							$applied_coupons = array();
						}
						if ( ! empty( $this->coupon_discount_amounts ) ) {
							$coupon_discount_amounts = $this->coupon_discount_amounts;
						} else {
							$coupon_discount_amounts = array();
						}
						if ( ! empty( $this->coupon_discount_tax_amounts ) ) {
							$coupon_discount_tax_amounts = $this->coupon_discount_tax_amounts;
						} else {
							$coupon_discount_tax_amounts = array();
						}
						if ( ! empty( $this->removed_cart_contents ) ) {
							$removed_cart_contents = $this->removed_cart_contents;
						} else {
							$removed_cart_contents = array();
						}
						$cart_data['cart'] = $cart_session;

						$cart_data['applied_coupons'] = $applied_coupons;

						$cart_data['coupon_discount_amounts'] = $coupon_discount_amounts;

						$cart_data['coupon_discount_tax_amounts'] = $coupon_discount_tax_amounts;

						$cart_data['removed_cart_contents'] = $removed_cart_contents;

						$cart_data['shipping_method'] = $this->shipping_method;

						$cart_data['shipping_methods'] = $this->shipping_methods;

						$cart_data['shipping_taxes'] = $this->shipping_taxes;
						foreach ( $this->cart_session_data as $key => $default ) {
							$cart_data[ $key ] = $this->$key;
						}
						foreach ( $cart_data['cart'] as $key => $value ) {
							if ( array_key_exists( 'data', $cart_data['cart'][ $key ] ) ) {
								unset( $cart_data['cart'][ $key ]['data'] );
							}
						}
						if ( $this->discount_cart ) {
							$cart_data['cart_contents_total'] = $cart_data['cart_contents_total'] - $this->discount_cart_tax;
						}
						$cart_data['total'] = $cart_data['cart_contents_total'];

						$cart_data['cart_totals'] = maybe_serialize( array( 'subtotal' => $cart_data['subtotal'] ) );
					}
					return $cart_data;
				} else {
					return 0;
				}
			} catch ( Exception $e ) {
				print_r( $e );
			}
		}

		public function mkwc_custom_calculate_totals( $cart_data ) {
			if ( count( $cart_data['cart'] ) > 0 ) {
				foreach ( $cart_data['cart'] as $key => $value ) {
					$product_data = wc_get_product( $value['variation_id'] ? $value['variation_id'] : $value['product_id'] );

					if ( $product_data ) {
						$updated_old_cart[ $key ] = array_merge( $value, array( 'data' => $product_data ) );
					}
				}
				$this->applied_coupons = isset( $cart_data['applied_coupons'] ) ? maybe_unserialize( $cart_data['applied_coupons'] ) : array();

				$this->cart_contents = $updated_old_cart;

				$this->calculate_totals();
			}
		}

		public function has_discount( $coupon_code = '' ) {
			return $coupon_code ? in_array( apply_filters( 'woocommerce_coupon_code', $coupon_code ), $this->applied_coupons ) : sizeof( $this->applied_coupons ) > 0;
		}

		public function add_discount( $coupon_code, $coupon_count ) {
			WC()->cart = new \Wc_Cart();

			WC()->customer = new \WC_Customer();

			// Coupons are globally disabled.
			if ( ! wc_coupons_enabled() ) {
				return false;
			}
			// Sanitize coupon code.
			$coupon_code = apply_filters( 'woocommerce_coupon_code', $coupon_code );

			// Get the coupon.
			$content = trim( file_get_contents( 'php://input' ) );

			// Attempt to decode the incoming RAW post data from JSON.
			$decoded_post_data = json_decode( $content, true );

			if ( isset( $decoded_post_data['cart'] ) ) {
				WC()->cart->set_cart_contents( array() );
				foreach ( $decoded_post_data['cart'] as $cart_data ) {
						$prod_unique_id = WC()->cart->generate_cart_id( $cart_data['product_id'] );

						unset( WC()->cart->cart_contents[ $prod_unique_id ] );
					$cart_data['quantity'] = isset( $cart_data['quantity'] ) ? $cart_data['quantity'] : '';
					WC()->cart->add_to_cart( $cart_data['product_id'], $cart_data['quantity'] );
				}
			}
			if ( isset( $_POST['cart'] ) ) {
				WC()->cart->set_cart_contents( array() );

				$cart_upadte_data = json_decode( stripslashes( $_POST['cart'] ) );

				foreach ( $cart_upadte_data as $post_data ) {
						$prod_unique_id = WC()->cart->generate_cart_id( $post_data->id );
						unset( WC()->cart->cart_contents[ $prod_unique_id ] );
						WC()->cart->add_to_cart( $post_data->id, $post_data->quantity );
				}
			}
			if ( isset( $decoded_post_data['product_id'] ) && isset( $_GET['request'] ) && $_GET['request'] == 'delete' ) {
					$prod_unique_id = WC()->cart->generate_cart_id( $decoded_post_data['product_id'] );

				WC()->cart->remove_cart_item( $prod_unique_id );
			}

			$the_coupon = new \WC_Coupon( $coupon_code );

			$discounts = new \WC_Discounts( WC()->cart );

			$valid = $discounts->is_coupon_valid( $the_coupon );
				// Check if applied.
			if ( $this->has_discount( $coupon_code ) ) {
				$the_coupon->add_coupon_message( \WC_Coupon::E_WC_COUPON_ALREADY_APPLIED );
				// return false;
			}
				// If its individual use then remove other coupons
			if ( $the_coupon->get_individual_use() == true ) {
				$this->applied_coupons = apply_filters( 'woocommerce_apply_individual_use_coupon', array(), $the_coupon, $this->applied_coupons );
			}
			if ( $the_coupon->is_valid() && ! $this->has_discount( $coupon_code ) ) {
				$this->applied_coupons[] = $coupon_code;
			}

			if ( $this->applied_coupons ) {
				foreach ( $this->applied_coupons as $code ) {
						$coupon = new \WC_Coupon( $code );

					if ( $coupon->get_individual_use() == true && false === apply_filters( 'woocommerce_apply_with_individual_use_coupon', false, $the_coupon, $coupon, $this->applied_coupons ) ) {
						$custom[] = $code;
						// Reject new coupon
						$coupon->add_coupon_message( \WC_Coupon::E_WC_COUPON_ALREADY_APPLIED_INDIV_USE_ONLY );
						// return false;
					}
				}
			}
			if ( isset( $custom ) && count( $custom ) > 0 ) {
				$this->applied_coupons = array();

				$this->coupon_indidual = array();

				$this->applied_coupons[] = $custom[0];

				$this->coupon_indidual[] = $custom[0];
			}

				// Choose free shipping.
			if ( $the_coupon->enable_free_shipping() ) {
				$packages = WC()->shipping->get_packages();

				$chosen_shipping_methods = WC()->session->get( 'chosen_shipping_methods' );
				foreach ( $packages as $i => $package ) {
					$chosen_shipping_methods[ $i ] = 'free_shipping';
				}
				WC()->session->set( 'chosen_shipping_methods', $chosen_shipping_methods );
			}
			if ( ! $the_coupon->is_valid() ) {
					$the_coupon->add_coupon_message( \WC_Coupon::WC_COUPON_SUCCESS );
			}
			if ( $this->coupon_count == $coupon_count ) {

				if ( $this->request_type == 'add' || $this->request_type == 'delete' || $this->update_item_count == 1 ) {
						$this->calculate_totals();
				}
			} else {
				$this->coupon_count++;
			}
			return true;
		}

		public function sort_by_subtotal( $a, $b ) {
				$first_item_subtotal = isset( $a['line_subtotal'] ) ? $a['line_subtotal'] : 0;

			$second_item_subtotal = isset( $b['line_subtotal'] ) ? $b['line_subtotal'] : 0;
			if ( $first_item_subtotal === $second_item_subtotal ) {
				return 0;
			}
				return ( $first_item_subtotal < $second_item_subtotal ) ? 1 : -1;
		}

		/**
		 * get coupon message by coupon code
		 *
		 * @param $coupon_codes
		 * @return $message
		 */
		public function mkwc_get_coupon_message( $coupon_codes ) {
			WC()->cart = new \Wc_Cart();

			$i = 0;

			$message = '';

			foreach ( $coupon_codes as $coupon_code ) {
				$coupon_code = ( isset( $coupon_code['coupon_code'] ) && ! empty( $coupon_code['coupon_code'] ) ) ? $coupon_code['coupon_code'] : '';
				$the_coupon  = new \WC_Coupon( $coupon_code );

				$discounts = new \WC_Discounts( WC()->cart );

				$valid = $discounts->is_coupon_valid( $the_coupon );

				if ( is_wp_error( $valid ) ) {
					$message = $valid->get_error_message();

					$message = 'Coupon name is ' . $coupon_code . '. ' . html_entity_decode( strip_tags( $message ) );

					$i++;
				}
			}
			if ( $i != 0 ) {
				return array(
					'status'  => false,
					'message' => $message,
				);
			} else {
				return array(
					'status'  => true,
					'message' => '',
				);
			}
		}

		/**
		 * Generate a unique customer ID for guests, or return user ID if logged in.
		 *
		 * Uses Portable PHP password hashing framework to generate a unique cryptographically strong ID.
		 *
		 * @return int|string
		 */
		public function generate_customer_id() {
			if ( is_user_logged_in() ) {
				return get_current_user_id();
			} else {
				require_once ABSPATH . 'wp-includes/class-phpass.php';

				$hasher = new \PasswordHash( 8, false );

				return md5( $hasher->get_random_bytes( 32 ) );
			}
		}

		public function mkwc_get_shipping_methods( $customer_id = '', $data, $user ) {

			$general_data_handler = new Helper\Mkwc_General_Data();

			$table_name = $this->wpdb->prefix . 'woocommerce_sessions';

			$methods = array();

			$this->cart_shipping_methods = array();

			$this->reg_user_id = $customer_id;
			if ( $user ) {
				$customer = new \WC_Customer( $customer_id );

				$country = $customer->get_shipping_country();

				$state = $customer->get_shipping_state();

				$postcode = $customer->get_shipping_postcode();

				$city = $customer->get_shipping_city();
			} else {
				$country = isset( $data['shipping_address']['country'] ) ? $data['shipping_address']['country'] : '';

				$state = isset( $data['shipping_address']['state'] ) ? $data['shipping_address']['state'] : '';

				$postcode = isset( $data['shipping_address']['postcode'] ) ? $data['shipping_address']['postcode'] : '';

				$city = isset( $data['shipping_address']['city'] ) ? $data['shipping_address']['city'] : '';
			}
			$cartcontents = $this->wpdb->get_row( "SELECT session_value FROM $table_name WHERE session_key = '$customer_id'", ARRAY_A );

			$sessionvalue = isset( $cartcontents['session_value'] ) ? maybe_unserialize( $cartcontents['session_value'] ) : '';

			$sessionvalue['cart'] = isset( $sessionvalue['cart'] ) ? maybe_unserialize( $sessionvalue['cart'] ) : '';

			$this->mkwc_custom_calculate_totals( $sessionvalue );

			foreach ( $this->cart_session_data as $key => $default ) {
				$sessionvalue[ $key ] = $this->$key;
			}
			if ( $this->discount_cart ) {
				$sessionvalue['cart_contents_total'] = $sessionvalue['cart_contents_total'] - $this->discount_cart_tax;
			}
			$sessionvalue['total'] = $sessionvalue['cart_contents_total'];

			$this->pay_amount = ( isset( $sessionvalue['total'] ) && ! empty( $sessionvalue['total'] ) ) ?
			$general_data_handler->mkwc_get_formatted_price( $sessionvalue['total'] ) : '';

			if ( isset( $sessionvalue['cart'] ) && ! empty( $sessionvalue['cart'] ) ) {
				$cart_value = maybe_unserialize( $sessionvalue['cart'] );
				foreach ( $cart_value as $key => $value ) {
					$product_data = wc_get_product( $value['variation_id'] ? $value['variation_id'] : $value['product_id'] );

					if ( $product_data ) {
						$cart_value[ $key ] = array_merge( $value, array( 'data' => $product_data ) );
					}
				}
				$this->cart_contents = isset( $cart_value ) ? $cart_value : '';
				if ( $country ) {
					WC()->customer->set_location( $country, $state, $postcode, $city );

					WC()->customer->set_shipping_location( $country, $state, $postcode, $city );
				} else {
					WC()->customer->set_billing_address_to_base();

					WC()->customer->set_shipping_address_to_base();
				}
				WC()->customer->set_calculated_shipping( true );

				$value = WC()->customer->get_calculated_shipping();

				if ( empty( WC()->shipping ) ) {
					WC()->shipping = new \WC_Shipping();
				}
				if ( empty( WC()->session ) ) {
					WC()->session = new \WC_Session_Handler();
				}

				if ( $this->needs_shipping() && $this->show_shipping() ) {
					if ( empty( WC()->cart ) ) {
						WC()->cart = new \WC_Cart();
					}
					WC()->shipping->calculate_shipping( $this->get_shipping_packages( $data ) );
				} else {
					WC()->shipping->reset_shipping();
				}
				$packages = WC()->shipping->get_packages();

				foreach ( $packages as $i => $package ) {
					$available_method = $package['rates'];

					$method = current( $available_method );

					$methods = $available_method;
				}

				foreach ( $methods as $key => $value ) {
					array_push(
						$this->cart_shipping_methods,
						array(
							'method_id'    => $value->id,
							'method_title' => $value->label,
							'cost'         => html_entity_decode( get_woocommerce_currency_symbol() ) . $value->cost,
							'tax'          => ! empty( $value->taxes ) ? array_sum( $value->taxes ) : 0.00,
						)
					);
				}
			}
			return $this->cart_shipping_methods;
		}

		public function mkwc_get_customer_shipping_methods( $customer_id ) {
			$shipping_methods = $this->mkwc_get_shipping_methods( $customer_id, $data = '', $user = true );

			$shipping_methods = $shipping_methods ? $shipping_methods : '';

			$shipping_method = $this->mkwc_get_cart_custom_details( $customer_id );

			$shipping_method = isset( $shipping_method['shipping_method'] ) ? $shipping_method['shipping_method'] : '';

			$user = get_user_by( 'id', $customer_id );

			$b_first_name = ( isset( $user->billing_first_name ) && ! empty( $user->billing_first_name ) ) ? $user->billing_first_name : '';

			$b_last_name = ( isset( $user->billing_last_name ) && ! empty( $user->billing_last_name ) ) ? ' ' . $user->billing_last_name : '';

			$b_address_one = ( isset( $user->billing_address_1 ) && ! empty( $user->billing_address_1 ) ) ? " \n" . $user->billing_address_1 : '';

			$b_address_two = ( isset( $user->billing_address_2 ) && ! empty( $user->billing_address_2 ) ) ? " \n" . $user->billing_address_2 : '';

			$b_city = ( isset( $user->billing_city ) && ! empty( $user->billing_city ) ) ? " \n" . $user->billing_city : '';

			$b_state = ( isset( $user->billing_state ) && ! empty( $user->billing_state ) ) ? " \n" . $user->billing_state : '';

			$b_postcode = ( isset( $user->billing_postcode ) && ! empty( $user->billing_postcode ) ) ? ' ' . $user->billing_postcode : '';

			$b_country = ( isset( $user->billing_country ) && ! empty( $user->billing_country ) ) ? " \n" . $user->billing_country : '';

			$b_address = ! empty( $b_address_one ) ? $b_address_one : $b_address_two;

			$billing_address = $b_first_name . $b_last_name . $b_address . $b_city . $b_state . $b_postcode . $b_country;

			$s_first_name = ( isset( $user->shipping_first_name ) && ! empty( $user->shipping_first_name ) ) ? $user->shipping_first_name : '';

			$s_last_name = ( isset( $user->shipping_first_name ) && ! empty( $user->shipping_first_name ) ) ? ' ' . $user->shipping_first_name : '';

			$s_address_one = ( isset( $user->shipping_address_1 ) && ! empty( $user->shipping_address_1 ) ) ? " \n" . $user->shipping_address_1 : '';

			$s_address_two = ( isset( $user->shipping_address_2 ) && ! empty( $user->shipping_address_2 ) ) ? " \n" . $user->shipping_address_2 : '';

			$s_city = ( isset( $user->shipping_city ) && ! empty( $user->shipping_city ) ) ? " \n" . $user->shipping_city : '';

			$s_state = ( isset( $user->shipping_state ) && ! empty( $user->shipping_state ) ) ? " \n" . $user->shipping_state : '';

			$s_postcode = ( isset( $user->shipping_postcode ) && ! empty( $user->shipping_postcode ) ) ? ' ' . $user->shipping_postcode : '';

			$s_country = ( isset( $user->shipping_country ) && ! empty( $user->shipping_country ) ) ? " \n" . $user->shipping_country : '';

			$s_address = ! empty( $s_address_one ) ? $s_address_one : $s_address_two;

			$shipping_address = $s_first_name . $s_last_name . $s_address . $s_city . $s_state . $s_postcode . $s_country;

			$user_data = array(
				'billing_address'  => array(
					'first_name' => $user->billing_first_name,
					'last_name'  => $user->billing_last_name,
					'company'    => $user->billing_company,
					'address_1'  => $user->billing_address_1,
					'address_2'  => $user->billing_address_2,
					'city'       => $user->billing_city,
					'state'      => $user->billing_state,
					'postcode'   => $user->billing_postcode,
					'country'    => $user->billing_country,
					'email'      => $user->billing_email,
					'phone'      => $user->billing_phone,
				),
				'shipping_address' => array(
					'first_name' => $user->shipping_first_name,
					'last_name'  => $user->shipping_last_name,
					'company'    => $user->shipping_company,
					'address_1'  => $user->shipping_address_1,
					'address_2'  => $user->shipping_address_2,
					'city'       => $user->shipping_city,
					'state'      => $user->shipping_state,
					'postcode'   => $user->shipping_postcode,
					'country'    => $user->shipping_country,
				),
			);

			$this->mkwc_merge_cart_address_data( $customer_id, 'billing_address', $billing_address );

			$this->mkwc_merge_cart_address_data( $customer_id, 'shipping_address', $shipping_address );

			$this->mkwc_merge_cart_customer_address( $customer_id, $user_data );

			return array(
				'billing_address'  => $billing_address ? $billing_address : '',
				'shipping_address' => $shipping_address ? $shipping_address : '',
				'shipping_method'  => $shipping_method,
				'shipping_methods' => $shipping_methods,
				'cart_total'       => $this->pay_amount,
			);
		}

		public function mkwc_merge_cart_data( $id, $set, $data ) {
			$is_shipping = isset( $set[0] ) ? $set[0] : '';

			$table_name = $this->wpdb->prefix . 'woocommerce_sessions';

			$cartcontents = $this->wpdb->get_row( "SELECT * FROM $table_name WHERE session_key = '$id'", ARRAY_A );

			$session_data_value = maybe_unserialize( $cartcontents['session_value'] );

			if ( empty( $session_data_value ) ) {
				return array(
					'success' => false,
					'message' => __( 'Empty cart.', 'mobikul_marketplace' ),
				);
			}

			if ( isset( $is_shipping ) && $is_shipping == 'shipping_method' ) {
				$session_data_value = array_merge(
					$session_data_value,
					array(
						$set['0'] => $data['shipping_method'],
						$set['1'] => $data['shipping_lines'],
					)
				);
			} else {
				$session_data_value = array_merge( $session_data_value, array( $set => $data ) );
			}

			if ( isset( $is_shipping ) && $is_shipping == 'shipping_method' ) {
				$session_data_value['cart'] = maybe_unserialize( $session_data_value['cart'] );

				$this->reg_user_id = $id;

				$this->mkwc_custom_calculate_totals( $session_data_value );

				if ( ! empty( $session_data_value['cart'] ) ) {
					$cart_session = $this->cart_contents;

					if ( ! empty( $this->applied_coupons ) ) {
						$applied_coupons = $this->applied_coupons;
					} else {
						$applied_coupons = array();
					}
					if ( ! empty( $this->coupon_discount_amounts ) ) {
						$coupon_discount_amounts = $this->coupon_discount_amounts;
					} else {
						$coupon_discount_amounts = array();
					}
					if ( ! empty( $this->coupon_discount_tax_amounts ) ) {
						$coupon_discount_tax_amounts = $this->coupon_discount_tax_amounts;
					} else {
						$coupon_discount_tax_amounts = array();
					}
					if ( ! empty( $this->removed_cart_contents ) ) {
						$removed_cart_contents = $this->removed_cart_contents;
					} else {
						$removed_cart_contents = array();
					}
					$session_data_value['cart'] = $cart_session;

					$session_data_value['applied_coupons'] = $applied_coupons;

					$session_data_value['coupon_discount_amounts'] = $coupon_discount_amounts;

					$session_data_value['coupon_discount_tax_amounts'] = $coupon_discount_tax_amounts;

					$session_data_value['removed_cart_contents'] = $removed_cart_contents;

					$session_data_value['shipping_method'] = $this->shipping_method;

					$session_data_value['shipping_methods'] = $this->shipping_methods;
					foreach ( $this->cart_session_data as $key => $default ) {
						$session_data_value[ $key ] = $this->$key;
					}
					foreach ( $session_data_value['cart'] as $key => $value ) {
						if ( array_key_exists( 'data', $session_data_value['cart'][ $key ] ) ) {
							unset( $session_data_value['cart'][ $key ]['data'] );
						}
					}
					if ( $this->discount_cart ) {
						$session_data_value['cart_contents_total'] = $session_data_value['cart_contents_total'] - $this->discount_cart_tax;
					}
					$session_data_value['total'] = $session_data_value['cart_contents_total'];

					$session_data_value['cart_totals'] = maybe_serialize( array( 'subtotal' => $session_data_value['subtotal'] ) );
				}
			}
			$session_data_value = maybe_serialize( $session_data_value );

			$session_id = get_user_meta( $id, 'user_web_session', true );

			if ( $session_id ) {
				$this->wpdb->get_results( "UPDATE $table_name SET session_value = '$session_data_value' WHERE session_key = '$session_id'" );
			}

			$this->wpdb->get_row( "UPDATE $table_name SET session_value = '$session_data_value' WHERE session_key = '$id'" );
		}

		public function mkwc_merge_cart_address_data( $id, $key, $data ) {
			$table_name = $this->wpdb->prefix . 'woocommerce_sessions';

			$cartcontents = $this->wpdb->get_row( "SELECT * FROM $table_name WHERE session_key = '$id'", ARRAY_A );

			$session_data_value = maybe_unserialize( $cartcontents['session_value'] );

			if ( empty( $session_data_value ) ) {
				return array(
					'success' => false,
					'message' => __( 'Empty cart.', 'mobikul_marketplace' ),
				);
			}

			$session_data_value = array_merge( $session_data_value, array( $key => $data ) );

			$session_data_value = maybe_serialize( $session_data_value );

			$this->wpdb->get_row( "UPDATE $table_name SET session_value = '$session_data_value' WHERE session_key = '$id'" );
		}

		public function mkwc_merge_cart_customer_address( $id, $data ) {
			$table_name = $this->wpdb->prefix . 'woocommerce_sessions';

			$cartcustomer = $this->wpdb->get_row( "SELECT * FROM $table_name WHERE session_key = '$id'", ARRAY_A );

			$cart_data_value = maybe_unserialize( $cartcustomer['session_value'] );

			$customer_data = $this->mkwc_cart_customer_format( $id, $data );

			$cart_data_value = array_merge( $cart_data_value, array( 'customer' => $customer_data ) );

			$cart_data_value = maybe_serialize( $cart_data_value );

			$result = $this->wpdb->get_row( "UPDATE $table_name SET session_value = '$cart_data_value' WHERE session_key = '$id'" );
		}

		public function mkwc_cart_customer_format( $id, $data ) {
			$billing = isset( $data['billing_address'] ) ? $data['billing_address'] : '';

			$shipping = isset( $data['shipping_address'] ) ? $data['shipping_address'] : '';

			$postcode = ( isset( $billing['postcode'] ) && ! empty( $billing['postcode'] ) ) ? $billing['postcode'] : '';
			$city     = ( isset( $billing['city'] ) && ! empty( $billing['city'] ) ) ? $billing['city'] : '';

			$address_1 = ( isset( $billing['address_1'] ) && ! empty( $billing['address_1'] ) ) ? $billing['address_1'] : '';

			$address_2 = ( isset( $billing['address_2'] ) && ! empty( $billing['address_2'] ) ) ? $billing['address_2'] : '';

			$state = ( isset( $billing['state'] ) && ! empty( $billing['state'] ) ) ? $billing['state'] : '';

			$country = ( isset( $billing['country'] ) && ! empty( $billing['country'] ) ) ? $billing['country'] : '';

			$shipping_postcode = ( isset( $shipping['postcode'] ) && ! empty( $shipping['postcode'] ) ) ? $shipping['postcode'] : '';

			$shipping_city = ( isset( $shipping['city'] ) && ! empty( $shipping['city'] ) ) ? $shipping['city'] : '';

			$shipping_address_1 = ( isset( $shipping['address_1'] ) && ! empty( $shipping['address_1'] ) ) ? $shipping['address_1'] : '';

			$shipping_address_2 = ( isset( $shipping['address_2'] ) && ! empty( $shipping['address_2'] ) ) ? $shipping['address_2'] : '';

			$shipping_state = ( isset( $shipping['state'] ) && ! empty( $shipping['state'] ) ) ? $shipping['state'] : '';

			$shipping_country = ( isset( $shipping['country'] ) && ! empty( $shipping['country'] ) ) ? $shipping['country'] : '';

			$first_name = ( isset( $billing['first_name'] ) && ! empty( $billing['first_name'] ) ) ? $billing['first_name'] : '';

			$last_name = ( isset( $billing['last_name'] ) && ! empty( $billing['last_name'] ) ) ? $billing['last_name'] : '';

			$company = ( isset( $billing['company'] ) && ! empty( $billing['company'] ) ) ? $billing['company'] : '';

			$phone = ( isset( $billing['phone'] ) && ! empty( $billing['phone'] ) ) ? $billing['phone'] : '';

			$email = ( isset( $billing['email'] ) && ! empty( $billing['email'] ) ) ? $billing['email'] : '';

			$shipping_first_name = ( isset( $shipping['first_name'] ) && ! empty( $shipping['first_name'] ) ) ? $shipping['first_name'] : '';

			$shipping_last_name = ( isset( $shipping['last_name'] ) && ! empty( $shipping['last_name'] ) ) ? $shipping['last_name'] : '';

			$shipping_company = ( isset( $shipping['company'] ) && ! empty( $shipping['company'] ) ) ? $shipping['company'] : '';

			$data = array(
				'id'                  => $id,
				'date_modified'       => '',
				'postcode'            => $postcode,
				'city'                => $city,
				'address_1'           => $address_1,
				'address'             => $address_1,
				'address_2'           => $address_1,
				'state'               => $state,
				'country'             => $country,
				'shipping_postcode'   => $shipping_postcode,
				'shipping_city'       => $shipping_city,
				'shipping_address_1'  => $shipping_address_1,
				'shipping_address'    => $shipping_address_1,
				'shipping_address_2'  => $shipping_address_2,
				'shipping_state'      => $shipping_state,
				'shipping_country'    => $shipping_country,
				'is_vat_exempt'       => '',
				'calculated_shipping' => '',
				'first_name'          => $first_name,
				'last_name'           => $last_name,
				'company'             => $company,
				'phone'               => $phone,
				'email'               => $email,
				'shipping_first_name' => $shipping_first_name,
				'shipping_last_name'  => $shipping_last_name,
				'shipping_company'    => $shipping_company,
			);

			return maybe_serialize( $data );
		}

		public function mkwc_get_cart_custom_details( $id ) {
			$table_name = $this->wpdb->prefix . 'woocommerce_sessions';

			$custom_details = $this->wpdb->get_row( "SELECT * FROM $table_name WHERE session_key = '$id'", ARRAY_A );

			$custom_details = isset( $custom_details['session_value'] ) ? maybe_unserialize( $custom_details['session_value'] ) : '';

			return $custom_details;
		}

		public function mkwc_return_cart_empty( $id ) {
			$table_name = $this->wpdb->prefix . 'woocommerce_sessions';

			$custom_details = $this->wpdb->get_row( "SELECT * FROM $table_name WHERE session_key = '$id'", ARRAY_A );

			$custom_details = isset( $custom_details['session_value'] ) ? maybe_unserialize( $custom_details['session_value'] ) : '';

			$empty_cart = ( isset( $custom_details['cart'] ) && $custom_details['cart'] ) ? maybe_unserialize( $custom_details['cart'] ) : '';

			return $empty_cart;
		}

		/**
		 * Cart virtual product
		 */
		public function mkwc_return_shipping_eligible( $cart_items ) {
			$product_count = $cart_items ? count( $cart_items ) : 0;

			if ( $product_count == 1 ) {
				foreach ( $cart_items as $skey => $svalue ) {
					$virtual_check = isset( $svalue['product_id'] ) ? get_product( $svalue['product_id'] ) : '';
				}

				if ( $virtual_check->is_virtual( 'yes' ) || $virtual_check->is_downloadable( 'yes' ) ) {
					return false;
				} else {
					return true;
				}
			} else {
				return true;
			}
		}

		/**
		 * Coupon applied message
		 */
		public function mkwc_get_coupon_add_remove( $coupons, $user_id, $code = '' ) {
			 $message = '';

			 $session = $this->mkwc_get_cart_custom_details( $user_id );

			 $session = isset( $session['applied_coupons'] ) ? $session['applied_coupons'] : '';

			if ( in_array( $code, $session ) && ( count( $coupons ) >= count( $session ) ) ) {
				$message = __( 'Coupon already applied.', 'mobikul_marketplace' );
			} elseif ( ! in_array( $code, $session ) ) {
				$message = __( 'Coupon applied.', 'mobikul_marketplace' );
			}
			 return $message ? $message : '';
		}
	}
}
