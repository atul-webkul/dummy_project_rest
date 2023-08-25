<?php

/**
 * @author Webkul
 * @version 1.0.0
 * This file handles checkout related functions
 */

namespace WkMobikulMultivendorApi\Includes\Front\Cart;

use WkMobikulMultivendorApi\Includes;
use WkMobikulMultivendorApi\Includes\Front\Cart\Util;
use WkMobikulMultivendorApi\Helper;
use WkMobikulMultivendorApi\Includes\Front\Homepage;

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'Mkwc_Cart_Get' ) ) {
	/**
	 * Cart add product class
	 */
	class Mkwc_Cart_Get extends Includes\Mkwc_Authentication implements Util\Mkwc_Cart_Get_Interface {

		public static $base = 'cart/get';

		protected $cartdata;

		/**
		 * cart get endpoint callback
		 *
		 * @param
		 * @return $cart_data
		 */
		public function mkwc_cart_get() {
			$validate_auth_key = $this->mkwc_authenticate_request();

			if ( $validate_auth_key != 'ok' ) {
				return array(
					'session_id'             => $validate_auth_key,
					'status'                 => 401,
					'invalid_auth_key_error' => __( 'Please provide valid Auth Key.', 'mobikul_marketplace' ),
					'success'                => false,
				);
			}
			$general_data_handler = new Helper\Mkwc_General_Data();

			global $wpdb;

			$response = array();

			$set = array();

			$coupon = array();

			$customer_id = isset( $_GET['customer_id'] ) ? $_GET['customer_id'] : '';

			$guest_id = isset( $_GET['guest_id'] ) ? $_GET['guest_id'] : '';

			$id = ! empty( $customer_id ) ? $customer_id : $guest_id;

			$customer_check = $this->mkwc_user_exist_request( $customer_id );

			if ( isset( $customer_check['success'] ) && ! $customer_check['success'] ) {
				return $customer_check;
			}

			if ( empty( $id ) ) {
				return array(
					'success' => false,
					'message' => __( 'Sorry, you must provide all parameters.', 'mobikul_marketplace' ),
				);
			}
			$cartdata_handler = new Helper\Mkwc_Cart_Data();

			$homepage_handler = new Homepage\Mkwc_Homepage();

			$cart_items = $cartdata_handler->mkwc_registered_cart_items( $id );

			if ( empty( $cart_items ) ) {
				$cart_items = array( 'cart' => array() );
			} else {
				if ( isset( $cart_items['cart'] ) && is_array( $cart_items['cart'] ) ) {
					foreach ( $cart_items['cart'] as $key => $value ) {
						if ( isset( $cart_items['cart'][ $key ] ) && is_array( $cart_items['cart'][ $key ] ) ) {
							if ( array_key_exists( 'product_id', $cart_items['cart'][ $key ] ) ) {
								$response[] = $value;
							}
						}
					}
				}
				if ( ! empty( $response ) ) {
					foreach ( $response as $rkey => $rvalue ) {
						$attributes = array();

						$product = wc_get_product( $rvalue['product_id'] );

						$product_type = $product->get_type();

						$rvalue['line_total'] = ( isset( $rvalue['line_total'] ) && ! empty( $rvalue['line_total'] ) ) ? $general_data_handler->mkwc_get_formatted_price( $rvalue['line_total'] ) : '';

						$rvalue['line_subtotal'] = ( isset( $rvalue['line_subtotal'] ) && ! empty( $rvalue['line_subtotal'] ) ) ? $general_data_handler->mkwc_get_formatted_price( $rvalue['line_subtotal'] ) : '';

						$set[ $rkey ] = $rvalue;

						$set[ $rkey ]['name'] = $product->get_title();

						if ( $product_type == 'variable' ) {
							$_product                   = new \WC_Product_Variation( $rvalue['variation_id'] );
							$set[ $rkey ]['unit_price'] = get_post_meta( $rvalue['variation_id'], '_sale_price', true ) > 0 ? $general_data_handler->mkwc_get_formatted_price( get_post_meta( $rvalue['variation_id'], '_sale_price', true ) ) : $general_data_handler->mkwc_get_formatted_price( get_post_meta( $rvalue['variation_id'], '_price', true ) );
						} else {
							$set[ $rkey ]['unit_price'] = get_post_meta( $rvalue['product_id'], '_sale_price', true ) > 0 ? $general_data_handler->mkwc_get_formatted_price( get_post_meta( $rvalue['product_id'], '_sale_price', true ) ) : $general_data_handler->mkwc_get_formatted_price( get_post_meta( $rvalue['product_id'], '_price', true ) );
						}
						$item_image                        = wp_get_attachment_url( get_post_thumbnail_id( $product->is_type( 'variable' ) ? $_product->get_variation_id() : $product->get_id() ) );
						$set[ $rkey ]['image']             = $item_image ? $item_image : wc_placeholder_img_src();
						$set[ $rkey ]['dominantColor']     = $general_data_handler->mkwc_get_dominant_Color( wp_get_attachment_url( get_post_thumbnail_id( $product->is_type( 'variable' ) ? $_product->get_variation_id() : $product->get_id() ) ) );
						$set[ $rkey ]['sold_individually'] = $product->is_sold_individually();

						if ( ! empty( $rvalue['variation_id'] ) ) {

							$variation = $product->get_child( $rvalue['variation_id'] );

							$attribute_title = array();

							foreach ( $product->get_attributes() as $attribute ) {
								if ( $attribute['id'] ) {
									$attribute_title[ strtolower( $attribute['name'] ) ] = wc_attribute_label( $attribute['name'] );
								} else {
									$attribute_title[ strtolower( $attribute['name'] ) ] = ucwords( str_replace( 'pa_', '', $attribute['name'] ) );
								}
							}
							if ( ! empty( $variation ) ) {
								$attrvalue      = $attr_title = '';
								$variation_attr = array();
								$parent_attr    = get_post_meta( $variation->get_parent_id(), '_product_attributes', true );

								if ( isset( $rvalue['variation'] ) && is_array( $rvalue['variation'] ) ) {
									if ( count( $variation->get_variation_attributes() ) == count( $rvalue['variation'] ) ) {
										$variation_attr = array_combine( array_keys( $variation->get_variation_attributes() ), array_values( $rvalue['variation'] ) );
									}
								} else {
									$variation_attr = $variation->get_variation_attributes();
								}
								foreach ( $variation_attr as $attribute_name => $attribute ) {

									$attr_name = str_replace( 'attribute_', '', str_replace( 'pa_', '', $attribute_name ) );

									$attr_title = $attribute_title[ str_replace( 'attribute_', '', $attribute_name ) ];

									foreach ( $parent_attr as $key => $value ) {
										if ( isset( $value['is_taxonomy'] ) && $value['is_taxonomy'] == 1 ) {
											$parent_attr_data = wc_get_product_terms( $variation->get_parent_id(), str_replace( 'attribute_', '', $attribute_name ), array( 'fields' => 'all' ) );
											if ( $parent_attr_data ) {
												foreach ( $parent_attr_data as $k => $val ) {
													if ( str_replace( 'attribute_', '', $attribute_name ) == $val->taxonomy && $attribute == $val->slug ) {
														$attrvalue = $val->name;
													}
												}
											}
										} else {
											$attrvalue = $attribute;
										}
									}

									// taxonomy-based attributes are prefixed with `pa_`, otherwise simply `attribute_`
									$attributes[] = array(
										'name'   => ucwords( str_replace( 'attribute_', '', str_replace( 'pa_', '', $attribute_name ) ) ),
										'option' => $attribute,
										'value'  => $attrvalue,
										'title'  => $attr_title,
									);
								}
							}
						}
						$set[ $rkey ]['variation'] = $attributes;
					}
				}
				$cart_items['cart'] = $set;

				$cart_items['applied_coupons'] = isset( $cart_items['applied_coupons'] ) ? maybe_unserialize( $cart_items['applied_coupons'] ) : '';

				$cart_items['coupon_discount_amounts'] = isset( $cart_items['coupon_discount_amounts'] ) ? $cart_items['coupon_discount_amounts'] : '';

				$cart_items['coupon_discount_tax_amounts'] = isset( $cart_items['coupon_discount_tax_amounts'] ) ? maybe_unserialize( $cart_items['coupon_discount_tax_amounts'] ) : '';

				$cart_items['cart_contents_total'] = isset( $cart_items['cart_contents_total'] ) ? $general_data_handler->mkwc_get_formatted_price( $cart_items['cart_contents_total'] ) : '';

				$cart_items['total'] = isset( $cart_items['total'] ) ? $general_data_handler->mkwc_get_formatted_price( $cart_items['total'] ) : '';

				$cart_items['subtotal'] = isset( $cart_items['subtotal'] ) ? $general_data_handler->mkwc_get_formatted_price( $cart_items['subtotal'] ) : '';

				if ( WC()->cart ) {
					WC()->cart->set_subtotal( $cart_items['subtotal'] );
				}

				$cart_items['discount_cart'] = isset( $cart_items['discount_cart'] ) ? $general_data_handler->mkwc_get_formatted_price( $cart_items['discount_cart'] ) : $general_data_handler->mkwc_get_formatted_price( '0' );

				$cart_items['shipping_total'] = ( isset( $cart_items['shipping_total'] ) && ! empty( $cart_items['shipping_total'] ) ) ? $general_data_handler->mkwc_get_formatted_price( $cart_items['shipping_total'] ) : $general_data_handler->mkwc_get_formatted_price( '0' );

				$cart_items['subtotal_ex_tax'] = isset( $cart_items['subtotal_ex_tax'] ) ? $general_data_handler->mkwc_get_formatted_price( $cart_items['subtotal_ex_tax'] ) : '';

				$cart_items['tax_total'] = isset( $cart_items['tax_total'] ) ? $cart_items['tax_total'] : '';

				$cart_items['taxes'] = isset( $cart_items['taxes'] ) ? $cart_items['taxes'] : '';

			}
			$discount_cart_tax = ( isset( $cart_items['discount_cart_tax'] ) && $cart_items['discount_cart_tax'] ) ? $cart_items['discount_cart_tax'] : 0;

			if ( empty( $cart_items['tax_total'] ) ) {
				$cart_items['tax_total'] = $general_data_handler->mkwc_get_formatted_price( '0' );
			} else {
				$cart_items['taxes'] = (float) $cart_items['tax_total'] - $discount_cart_tax;

				$cart_items['taxes'] = $general_data_handler->mkwc_get_formatted_price( $cart_items['taxes'] );

					$cart_items['tax_total'] = $cart_items['tax_total'] - $discount_cart_tax;

				$cart_items['tax_total'] = $general_data_handler->mkwc_get_formatted_price( $cart_items['tax_total'] );
			}
			foreach ( $cart_items as $key => $value ) {
				if ( empty( $value ) ) {
					$cart_items[ $key ] = array();
				}
			}

			$cart_items['is_cartEligible'] = true;

			$new_array = array();

			$i = 0;
			if ( ( isset( $cart_items['applied_coupons'] ) && is_array( $cart_items['applied_coupons'] ) ) && count( $cart_items['applied_coupons'] ) > 0 ) {
				foreach ( $cart_items['applied_coupons'] as $new_coupon ) {
					$new_array[ $i ]['coupon_code'] = $new_coupon;

					$new_array[ $i ]['coupon_amount'] = array_sum( array_column( $cart_items['coupon_discount_amounts'], $new_coupon ) );

					$i++;
				}
			}
			$cart_items['coupon_discount_amounts'] = $new_array;

			if ( ! empty( $cart_items['coupon_discount_amounts'] ) ) {
				foreach ( $cart_items['coupon_discount_amounts'] as $key => $value ) {
					$coupon[ $key ] = array(
						'coupon_code'   => $value['coupon_code'],
						'coupon_amount' => $general_data_handler->mkwc_get_formatted_price( $value['coupon_amount'] ),
					);
				}

				$cart_items['coupon_discount_amounts'] = $coupon;

				$cart_items['is_valid'] = $cartdata_handler->mkwc_get_coupon_message( $cart_items['coupon_discount_amounts'] );
				if ( isset( $cart_items['is_valid']['message'] ) && ! empty( $cart_items['is_valid']['message'] ) ) {
					$cart_items['message']         = $cart_items['is_valid']['message'];
					$cart_items['is_cartEligible'] = false;
				}
			}
			if ( ! empty( $cart_items['cart'] ) ) {
				$product_count = count( $cart_items['cart'] );

				foreach ( $cart_items['cart'] as $ckey => $cvalue ) {
					$product_count += $cvalue['quantity'] - 1;
				}
				$cart_items['count'] = $product_count;
			} else {
				$cart_items['count'] = 0;
			}
			if ( isset( $cart_items['cart_totals'] ) ) {
				unset( $cart_items['cart_totals'] );
			}
			$cart_items['settings'] = $homepage_handler->mkwc_default_settings();

			return apply_filters( 'mkwc_cart_get_filter_data', $cart_items );
		}

		/**
		 * Cart Get Shipping methods of register user endpoint callback
		 *
		 * @param $id
		 * @return $shipping methods
		 */
		public function mkwc_cart_get_shipping_methods() {
			$validate_auth_key = $this->mkwc_authenticate_request();

			if ( $validate_auth_key != 'ok' ) {
				return array(
					'session_id'             => $validate_auth_key,
					'status'                 => 401,
					'invalid_auth_key_error' => __( 'Please provide valid Auth Key.', 'mobikul_marketplace' ),
					'success'                => false,
				);
			}

			$customer_id = isset( $_GET['customer_id'] ) ? $_GET['customer_id'] : '';

			$guest_id = isset( $_GET['guest_id'] ) ? $_GET['guest_id'] : '';

			$id = ! empty( $customer_id ) ? $customer_id : $guest_id;

			$customer_check = $this->mkwc_user_exist_request( $customer_id );

			if ( isset( $customer_check['success'] ) && ! $customer_check['success'] ) {
				return $customer_check;
			}
			if ( empty( $id ) ) {
				return array(
					'success' => false,
					'message' => __( 'Sorry, you must provide all parameters.', 'mobikul_marketplace' ),
				);
			}
			$cartdata_handler = new Helper\Mkwc_Cart_Data();

			$custom_check = $cartdata_handler->mkwc_return_cart_empty( $id );

			if ( empty( $custom_check ) ) {
				return array(
					'success'     => false,
					'isCartEmpty' => true,
					'message'     => __( 'your cart is empty.', 'mobikul_marketplace' ),
				);
			}

			$customer_methods = $cartdata_handler->mkwc_get_customer_shipping_methods( $id );

			$is_shipping_eligible = $cartdata_handler->mkwc_return_shipping_eligible( $custom_check );

			$message_print = ( $is_shipping_eligible == false ) ? __( 'No shipping methods required.', 'mobikul_marketplace' ) : __( 'No shipping method found.', 'mobikul_marketplace' );

			return array(
				'billing_address'      => ! empty( $customer_methods['billing_address'] ) ? $customer_methods['billing_address'] : __( 'Billing address not saved.', 'mobikul_marketplace' ),
				'shipping_address'     => ! empty( $customer_methods['shipping_address'] ) ? $customer_methods['shipping_address'] : __( 'Shipping address not saved.', 'mobikul_marketplace' ),
				'shipping_method'      => ! empty( $customer_methods['shipping_address'] ) ? $customer_methods['shipping_method'] : '',
				'shipping_methods'     => ! empty( $customer_methods['shipping_address'] ) ? $customer_methods['shipping_methods'] : '',
				'total'                => $customer_methods['cart_total'],
				'is_shipping_eligible' => $is_shipping_eligible,
				'success'              => ( empty( $customer_methods['shipping_address'] ) || empty( $customer_methods['shipping_methods'] ) ) ? false : true,
				'message'              => ( empty( $customer_methods['shipping_address'] ) || empty( $customer_methods['shipping_methods'] ) ) ? $message_print : '',
			);
		}
	}
}
