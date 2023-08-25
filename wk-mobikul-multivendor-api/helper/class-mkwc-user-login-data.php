<?php

/**d
 *
 * @author Webkul
 * @version 1.0.0
 * This file handles category data helper functions.
 */

namespace WkMobikulMultivendorApi\Helper;

use WkMobikulMultivendorApi\Helper;

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'Mkwc_User_Data' ) ) {
	/**
	 * Category data class
	 */
	class Mkwc_User_Login_Data extends Helper\Mkwc_Cart_Data implements Util\Mkwc_User_Login_Data_Interface {

		protected $wpdb;

		protected $userdata;

		protected $response;

		function __construct() {
			global $wp, $wpdb;

			$this->wpdb = $wpdb;
		}

		/**
		 * User Login
		 *
		 * @param $user_id
		 * @return $user_cart_data
		 */
		public function mkwc_user_login_data( $data ) {
			$userdata_handler = new Helper\Mkwc_User_Data();
			$response_handler = new \WP_REST_Response();
			$count            = 0;
			$check            = 0;
			$cart_items       = array();
			$table_name       = $this->wpdb->prefix . 'woocommerce_sessions';
			$customer_email   = $data['key'];
			$customer_pass    = $data['pass'];

			$customer = $userdata_handler->mkwc_check_user_exist( $customer_email, $customer_pass );

			if ( $customer === false ) {
				return array(
					'success' => false,
					'message' => __( 'Invalid Credentials.', 'mobikul_marketplace' ),
				);
			} else {
				$user_data         = get_user_by( 'email', $customer->user_email );
				$customer_id       = $user_data->ID;
				$this->reg_user_id = $customer_id;

				if ( ! empty( $data['guest_id'] ) ) {
					$guest_id = $data['guest_id'];
				} else {
					$guest_id = '';
				}
				if ( isset( $_POST['guest_id'] ) ) {
					$guest_id = $_POST['guest_id'];
				}

				$this->mkwc_login_set_session_expiration();
				$session_check              = get_user_meta( $customer_id, '_woocommerce_persistent_cart_' . get_current_blog_id(), true );
				$session_final_data         = array();
				$session_final_data['cart'] = isset( $session_check['cart'] ) ? $session_check['cart'] : array();
				$seller_info                = $userdata_handler->mkwc_user_seller_info( $customer_id );

				// check if $session_check and $guest_id empty then set session_expiry
				if ( empty( $session_final_data ) && empty( $guest_id ) ) {
					$data = $userdata_handler->mkwc_insert_user_session( $table_name, $customer_id, $session_final_data, $this->_session_expiration );
				} elseif ( ! empty( $guest_id ) && ! empty( $session_check ) ) {

					$guest_session_data    = $userdata_handler->mkwc_get_user_session( $table_name, $guest_id );
					$customer_session_data = $session_final_data;
					if ( isset( $guest_session_data[0] ) ) {
						$guest_session_value = $guest_session_data[0]->session_value;
					} else {
						$guest_session_value = array();
					}

					if ( isset( $customer_session_data['cart'] ) ) {
						$customer_session_value = $customer_session_data;
					} else {
						$customer_session_value = array();
					}

					if ( array_key_exists( 'cart', maybe_unserialize( $guest_session_value ) ) || array_key_exists( 'cart', $customer_session_value ) ) {
						$cart_items_guest = maybe_unserialize( maybe_unserialize( $guest_session_value )['cart'] );

						if ( empty( $cart_items_guest ) ) {
							$cart_items_guest = array();
						}
						if ( array_key_exists( 'cart', $customer_session_value ) ) {
							$cart_items = $this->mkwc_manage_cart_quantity( $customer_session_value['cart'], $cart_items_guest );
						} else {
							$cart_items = maybe_unserialize( maybe_unserialize( $guest_session_value )['cart'] );
						}
					} else {
							$cart_items = array();
					}

					// here merge  cart guest user and customer id
					if ( count( $cart_items ) > 0 ) {
						$quantity = 0;
						foreach ( $cart_items as $key => $value ) {
							$product_data = wc_get_product( $value['variation_id'] ? $value['variation_id'] : $value['product_id'] );
							$quantity    += $value['quantity'];

							if ( $product_data ) {
								$updated_old_cart[ $key ] = array_merge( $value, array( 'data' => $product_data ) );
							}
						}
						$this->cart_contents = $updated_old_cart;

						$this->calculate_totals();
					}

					if ( ! empty( $cart_items ) ) {
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
						$session_value['cart']                        = $cart_session;
						$session_value['applied_coupons']             = $applied_coupons;
						$session_value['coupon_discount_amounts']     = $coupon_discount_amounts;
						$session_value['coupon_discount_tax_amounts'] = $coupon_discount_tax_amounts;
						$session_value['removed_cart_contents']       = $removed_cart_contents;
						$session_value['shipping_method']             = $this->shipping_method;
						$session_value['shipping_methods']            = $this->shipping_methods;

						foreach ( $this->cart_session_data as $key => $default ) {
							$session_value[ $key ] = $this->$key;
						}
						foreach ( $session_value['cart'] as $key => $value ) {
							if ( array_key_exists( 'data', $session_value['cart'][ $key ] ) ) {
								unset( $session_value['cart'][ $key ]['data'] );
							}
						}
						if ( $this->discount_cart ) {
							$session_value['cart_contents_total'] -= $this->discount_cart;
						}
						$session_value['total'] = $session_value['cart_contents_total'];

						$session_value['cart_totals'] = maybe_serialize( array( 'subtotal' => $session_value['subtotal'] ) );

						update_user_meta( $customer_id, '_woocommerce_persistent_cart_' . get_current_blog_id(), array( 'cart' => $session_value['cart'] ) );

						$session_value = maybe_serialize( $session_value );

						$session_id = get_user_meta( $customer_id, 'user_web_session', true );

						// Update session if its close to expiring
						if ( time() > $this->_session_expiring ) {
							$this->set_session_expiration();

							$this->update_session_timestamp( $customer_id, $this->_session_expiration, $table_name );

							if ( $session_id ) {
								$this->update_session_timestamp( $session_id, $this->_session_expiration, $table_name );
							}
						}

						if ( empty( $customer_session_data ) ) {
							$userdata_handler->mkwc_update_user_session_value( $table_name, $session_value, $customer_id );

							$userdata_handler->mkwc_delete_user_session( $table_name, $guest_id );
						} else {
							if ( $session_id ) {
								$userdata_handler->mkwc_update_user_session_value( $table_name, $session_value, $session_id );
							}

								$user_session_ = $userdata_handler->mkwc_get_user_session( $table_name, $customer_id );

							if ( empty( $user_session_ ) ) {

								$data = $userdata_handler->mkwc_insert_user_session( $table_name, $customer_id, $guest_session_value, $this->_session_expiration );
							} else {

								$userdata_handler->mkwc_update_user_session_value( $table_name, $session_value, $customer_id );
								$userdata_handler->mkwc_delete_user_session( $table_name, $guest_id );
							}

							// $userdata_handler->mkwc_update_user_session_value( $table_name, $session_value, $customer_id );

							// $userdata_handler->mkwc_delete_user_session( $table_name , $guest_id );
						}
					}
				} elseif ( ! empty( $guest_id ) && empty( $session_check ) ) {
					$guest_session_data = $userdata_handler->mkwc_get_user_session( $table_name, $guest_id );

					if ( isset( $guest_session_data[0] ) ) {
						$guest_session_value = $guest_session_data[0]->session_value;
					} else {
						$guest_session_value = array();
					}
					if ( array_key_exists( 'cart', maybe_unserialize( $guest_session_value ) ) ) {
						$cart_items = maybe_unserialize( maybe_unserialize( $guest_session_value )['cart'] );
					} elseif ( ! empty( $session ) ) {
						$cart_items = $session;
					} else {
						$cart_items = array();
					}
					if ( ! empty( $cart_items ) ) {
						$data = $userdata_handler->mkwc_insert_user_session( $table_name, $customer_id, $guest_session_value, $this->_session_expiration );
					}
				}
				if ( ! empty( $session ) && empty( $guest_id ) ) {
					if ( array_key_exists( 'cart', maybe_unserialize( $session ) ) ) {
						$cart_items = maybe_unserialize( maybe_unserialize( $session )['cart'] );
					} else {
						$cart_items = array();
					}
				}
				if ( ! empty( $cart_items ) && is_array( $cart_items ) ) {
					$product_count = count( $cart_items );
					foreach ( $cart_items as $ckey => $cvalue ) {
						$product_count += isset( $cvalue['quantity'] ) ? $cvalue['quantity'] - 1 : 0;
					}
					$count = $product_count;
				} else {
					$count = 0;
				}

				$current_data       = wp_set_current_user( $user_data->data->ID );
				$role               = (array) $current_data->roles;
				$current_user_roll  = ! empty( $role[0] ) ? $role[0] : '';
				$memebership_status = ! empty( $current_data->membership_level->ID ) ? true : false;
				$lavel_id           = ! empty( $current_data->membership_level->ID ) ? $current_data->membership_level->ID : 0;
				$response_handler->set_data(
					array(
						'id'                => (int) $user_data->data->ID,
						'name'              => $user_data->data->user_nicename,
						'display_name'      => $user_data->first_name ? $user_data->first_name . ' ' . $user_data->last_name : $user_data->user_login,
						'email'             => $user_data->data->user_email,
						'product_count'     => $count,
						'isSeller'          => $seller_info ? 1 : 0,
						'image'             => get_avatar_url( (int) $user_data->data->ID ),
						'membership_status' => $memebership_status,
						'level_id'          => $lavel_id,
						'roll'              => $current_user_roll,
						'success'           => true,
					)
				);
				return $response_handler;
			}
		}

		public function mkwc_manage_cart_quantity( $customer_session, $guest_session ) {
				$array_cart_data = array();

				$product_data = array();

				$merged_arr = array_merge( $customer_session, $guest_session );
			if ( empty( $merged_arr ) ) {
				$merged_arr = $guest_session;
			}
			foreach ( $merged_arr as $key => $value ) {
					$variation_id = $value['variation_id'] ? '-' . $value['variation_id'] : '';
					$product_data[ $value['product_id'] . $variation_id ] = array(
						'key'      => $key,
						'quantity' => is_array( $product_data ) && array_key_exists( $value['product_id'] . $variation_id, $product_data ) ? $product_data[ $value['product_id'] . $variation_id ]['quantity'] + $value['quantity'] : $value['quantity'],
					);
			}

				$product_data = array_column( $product_data, 'quantity', 'key' );

			foreach ( $merged_arr as $key => $value ) {
				if ( array_key_exists( $key, $product_data ) ) {
						$array_cart_data[ $key ] = $value;
				}
			}

				return $array_cart_data;
		}

		public function mkwc_login_set_session_expiration() {
				$this->_session_expiring   = time() + intval( apply_filters( 'wc_session_expiring', 60 * 60 * 47 ) ); // 47 Hours.
				$this->_session_expiration = time() + intval( apply_filters( 'wc_session_expiration', 60 * 60 * 48 ) ); // 48 Hours.
		}
	}
}
