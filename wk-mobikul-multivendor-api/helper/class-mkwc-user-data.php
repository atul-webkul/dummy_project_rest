<?php

/**
 * @author Webkule
 * @version 1.0.0
 * This file handles category data helper functions.
 */

namespace WkMobikulMultivendorApi\Helper;

use WkMobikulMultivendorApi\Helper;
use WkMarketplace\Helper as mp_helper;

defined( 'ABSPATH' ) || exit;
// implements Util\Mkwc_User_Data_Interface
if ( ! class_exists( 'Mkwc_User_Data' ) ) {
	/**
	 * Category data class
	 */
	class Mkwc_User_Data {

		protected $wpdb;

		function __construct() {
			global $wp, $wpdb;

			$this->wpdb = $wpdb;
		}

		/**
		 * Check User exist
		 *
		 * @param $user_object
		 * @return $user_data
		 */
		public function mkwc_check_user_exist( $email, $pass ) {
			$user_exist = apply_filters( 'authenticate', null, $email, $pass );

			if ( is_wp_error( $user_exist ) ) {

				$user_exist = false;
			}

			return $user_exist;
		}

		/**
		 * Update User address
		 *
		 * @param $user_object
		 * @return $update_user_data
		 */
		public function mkwc_update_user_address( $id, $data ) {
			$cartdata_handler = new Helper\Mkwc_Cart_Data();

			// Customer first name.
			if ( isset( $data['first_name'] ) && $data['first_name'] ) {
				update_user_meta( $id, 'first_name', wc_clean( $data['first_name'] ) );
			}

			// Customer last name.
			if ( isset( $data['last_name'] ) && $data['last_name'] ) {
				update_user_meta( $id, 'last_name', wc_clean( $data['last_name'] ) );
			}

			// Customer billing address.
			if ( isset( $data['billing_address'] ) ) {
				foreach ( $this->mkwc_user_billing_address_format() as $address ) {
					if ( isset( $data['billing_address'][ $address ] ) ) {
						update_user_meta( $id, 'billing_' . $address, wc_clean( $data['billing_address'][ $address ] ) );
					}
				}
			}

			// Customer shipping address.
			if ( isset( $data['shipping_address'] ) ) {
				foreach ( $this->mkwc_user_shipping_address_format() as $address ) {
					if ( isset( $data['shipping_address'][ $address ] ) ) {
						update_user_meta( $id, 'shipping_' . $address, wc_clean( $data['shipping_address'][ $address ] ) );
					}
				}
			}

			do_action( 'woocommerce_api_update_customer_data', $id, $data );

			$request = isset( $_GET['request'] ) ? $_GET['request'] : '';

			if ( $request == 'checkout' ) {
				$customer_methods = $this->mkwc_update_shipping_methods( $id, $data, $user = true );

				$custom_check = $cartdata_handler->mkwc_return_cart_empty( $id );

				$is_shipping_eligible = $cartdata_handler->mkwc_return_shipping_eligible( $custom_check );

				$message_print = ( $is_shipping_eligible == false ) ? __( 'No shipping methods required.', 'mobikul_marketplace' ) : __( 'No shipping methods found.', 'mobikul_marketplace' );

				return array(
					'billing_address'      => ( isset( $customer_methods['billing_address'] ) && ! empty( $customer_methods['billing_address'] ) ) ? $customer_methods['billing_address'] : __( 'Billing address not saved.', 'mobikul_marketplace' ),
					'shipping_address'     => ( isset( $customer_methods['shipping_address'] ) && ! empty( $customer_methods['shipping_address'] ) ) ? $customer_methods['shipping_address'] : __( 'Shipping address not saved.', 'mobikul_marketplace' ),
					'shipping_methods'     => ( isset( $customer_methods['shipping_address'] ) && ! empty( $customer_methods['shipping_address'] ) ) ? $customer_methods['shipping_methods'] : '',
					'is_shipping_eligible' => $is_shipping_eligible,
					'total'                => $customer_methods['cart_total'],
					'shipping_message'     => ( empty( $customer_methods['shipping_address'] ) || empty( $customer_methods['shipping_methods'] ) ) ? $message_print : '',
					'success'              => true,
					'message'              => __( 'Account Updated Successfully.', 'mobikul_marketplace' ),
				);
			} else {
				return array(
					'success' => true,
					'message' => __( 'Account Updated Successfully.', 'mobikul_marketplace' ),
				);
			}
		}


		/**
		 * Shipping methods
		 *
		 * @param $user_id
		 * @return $shipping_methods
		 */
		public function mkwc_update_shipping_methods( $id, $data, $user ) {
			$cartdata_handler = new Helper\Mkwc_Cart_Data();

			$b_first_name = ( isset( $data['billing_address']['first_name'] ) && ! empty( $data['billing_address']['first_name'] ) ) ? $data['billing_address']['first_name'] : '';

			$b_last_name = ( isset( $data['billing_address']['last_name'] ) && ! empty( $data['billing_address']['last_name'] ) ) ? ' ' . $data['billing_address']['last_name'] : '';

			$b_address_one = ( isset( $data['billing_address']['address_1'] ) && ! empty( $data['billing_address']['address_1'] ) ) ? " \n" . $data['billing_address']['address_1'] : '';

			$b_address_two = ( isset( $data['billing_address']['address_2'] ) && ! empty( $data['billing_address']['address_2'] ) ) ? " \n" . $data['billing_address']['address_2'] : '';

			$b_city = ( isset( $data['billing_address']['city'] ) && ! empty( $data['billing_address']['city'] ) ) ? " \n" . $data['billing_address']['city'] : '';

			$b_state = ( isset( $data['billing_address']['state'] ) && ! empty( $data['billing_address']['state'] ) ) ? " \n" . $data['billing_address']['state'] : '';

			$b_postcode = ( isset( $data['billing_address']['postcode'] ) && ! empty( $data['billing_address']['postcode'] ) ) ? " \n" . $data['billing_address']['postcode'] : '';

			$b_country = ( isset( $data['billing_address']['country'] ) && ! empty( $data['billing_address']['country'] ) ) ? " \n" . $data['billing_address']['country'] : '';

			$b_address = ! empty( $b_address_one ) ? $b_address_one : $b_address_two;

			$billing_address = $b_first_name . $b_last_name . $b_address . $b_city . $b_state . $b_postcode . $b_country;

			$s_first_name = ( isset( $data['shipping_address']['first_name'] ) && ! empty( $data['shipping_address']['first_name'] ) ) ? $data['shipping_address']['first_name'] : '';

			$s_last_name = ( isset( $data['shipping_address']['last_name'] ) && ! empty( $data['shipping_address']['last_name'] ) ) ? ' ' . $data['shipping_address']['last_name'] : '';

			$s_address_one = ( isset( $data['shipping_address']['address_1'] ) && ! empty( $data['shipping_address']['address_1'] ) ) ? " \n" . $data['shipping_address']['address_1'] : '';

			$s_address_two = ( isset( $data['shipping_address']['address_2'] ) && ! empty( $data['shipping_address']['address_2'] ) ) ? " \n" . $data['shipping_address']['address_2'] : '';

			$s_city = ( isset( $data['shipping_address']['city'] ) && ! empty( $data['shipping_address']['city'] ) ) ? " \n" . $data['shipping_address']['city'] : '';

			$s_state = ( isset( $data['shipping_address']['state'] ) && ! empty( $data['shipping_address']['state'] ) ) ? " \n" . $data['shipping_address']['state'] : '';

			$s_postcode = ( isset( $data['shipping_address']['postcode'] ) && ! empty( $data['shipping_address']['postcode'] ) ) ? ' ' . $data['shipping_address']['postcode'] : '';

			$s_country = ( isset( $data['shipping_address']['country'] ) && ! empty( $data['shipping_address']['country'] ) ) ? " \n" . $data['shipping_address']['country'] : '';

			$s_address = ! empty( $s_address_one ) ? $s_address_one : $s_address_two;

			$shipping_address = $s_first_name . $s_last_name . $s_address . $s_city . $s_state . $s_postcode . $s_country;

			$shipping_methods = $cartdata_handler->mkwc_get_shipping_methods( $id, $data, $user = false );

			$shipping_methods = $shipping_methods ? $shipping_methods : '';

			if ( empty( $billing_address ) && empty( $shipping_address ) ) {
				return array(
					'success' => false,
					'message' => __( 'No shipping method found.', 'mobikul_marketplace' ),
				);
			}

			$cartdata_handler->mkwc_merge_cart_customer_address( $id, $data );

			$cartdata_handler->mkwc_merge_cart_address_data( $id, 'billing_address', $billing_address );

			$cartdata_handler->mkwc_merge_cart_address_data( $id, 'shipping_address', $shipping_address );

			return array(
				'billing_address'  => $billing_address ? $billing_address : '',
				'shipping_address' => $shipping_address ? $shipping_address : '',
				'shipping_methods' => $shipping_methods,
				'cart_total'       => $cartdata_handler->pay_amount,
			);
		}

		/**
		 * User billing address
		 *
		 * @param $user_object
		 * @return $user_billing_format
		 */
		public function mkwc_user_billing_address_format() {
			$billFields = array(
				'first_name',
				'last_name',
				'company',
				'address_1',
				'address_2',
				'city',
				'state',
				'postcode',
				'country',
				'email',
				'phone',
			);

			$billing_address = apply_filters( 'woocommerce_api_customer_billing_address', $billFields );

			return $billing_address;
		}

		/**
		 * User shipping address
		 *
		 * @param $user_object
		 * @return $user_shipping_format
		 */
		public function mkwc_user_shipping_address_format() {
			$shipFields = array(
				'first_name',
				'last_name',
				'company',
				'address_1',
				'address_2',
				'city',
				'state',
				'postcode',
				'country',
				'phone',
			);

			$shipping_address = apply_filters( 'woocommerce_api_customer_shipping_address', $shipFields );

			return $shipping_address;
		}

		/**
		 * Check User is seller
		 *
		 * @param $user_object
		 * @return $user_seller
		 */
		public function mkwc_user_seller_info( $user_id ) {
			$seller_info = $this->wpdb->get_var( "SELECT user_id FROM {$this->wpdb->prefix}mpsellerinfo WHERE user_id = {$user_id} and seller_value='seller'" );

			return $seller_info;
		}

		/**
		 * get user session
		 *
		 * @param $user_object
		 * @return $user_session
		 */
		public function mkwc_get_user_session( $table_name, $user_id ) {
			$session_check = $this->wpdb->get_results( "SELECT * FROM {$table_name} WHERE session_key = '$user_id'" );

			return $session_check;
		}

		/**
		 * insert into user session
		 *
		 * @param $user_object
		 * @return $user_session
		 */
		public function mkwc_insert_user_session( $table_name, $user_id, $session, $session_expiry ) {
			$data = $this->wpdb->get_results( "INSERT INTO {$table_name} (session_key, session_value, session_expiry) VALUES ('{$user_id}', '{$session}', '{$session_expiry}')" );

			return $data;
		}

		/**
		 * Delete from user session
		 *
		 * @param $user_object
		 * @return $user_session
		 */
		public function mkwc_delete_user_session( $table_name, $user_id ) {
			$data = $this->wpdb->get_results( "DELETE FROM {$table_name} WHERE session_key = '$user_id'" );

			return $data;
		}

		/**
		 * update into user session
		 *
		 * @param $user_object
		 * @return $user_session
		 */
		public function mkwc_update_user_session( $table_name, $guest_session_value, $session_expiry, $customer_id ) {
			$data = $this->wpdb->get_results( "UPDATE {$table_name} SET session_value = {$guest_session_value}, session_expiry = {$session_expiry} WHERE session_key = '$customer_id'" );

			return $data;
		}

		/**
		 * update into user session
		 *
		 * @param $user_object
		 * @return $user_session
		 */
		public function mkwc_update_user_session_value( $table_name, $session_value, $customer_id ) {
			$data = $this->wpdb->get_results( "UPDATE $table_name SET session_value = '$session_value' WHERE session_key = '$customer_id'" );

			return $data;
		}

		/**
		 * update seller meta
		 *
		 * @param $user_object
		 * @return $user_session
		 */
		public function mkwc_seller_meta( $user, $seller_key, $seller_val, $isseller ) {

			/* defining role as marketplace seller*/
			$user_role = new \WP_User( $user );

			if ( 1 === $isseller ) {
				$isseller  = 1;
				$seller_vl = 'seller';
				$user_role->add_role( 'wk_marketplace_seller' );
			} elseif ( 2 === $isseller ) {
				$isseller  = 2;
				$seller_vl = 'architect';
				$user_role->add_role( 'wk_marketplace_seller' );
				$user_role->set_role( 'architect' );
				return true;
			} elseif ( 3 === $isseller ) {
				$isseller  = 3;
				$seller_vl = 'serviceproviders';
				$user_role->add_role( 'wk_marketplace_seller' );
				$user_role->set_role( 'serviceproviders' );
				return true;
			}

			if ( get_option( 'wkmp_auto_approve_seller' ) ) {
				$user_role->set_role( 'wk_marketplace_seller' );
			} else {
				$user_role->set_role( get_option( 'default_role' ) );
			}

			$seller_table = $this->wpdb->prefix . 'mpsellerinfo';

			if ( get_option( 'wkmp_auto_approve_seller' ) ) {
				$seller = array(
					'user_id'      => $user,
					'seller_key'   => $seller_key,
					'seller_value' => 'seller',
				);
			} else {
				$seller = array(
					'user_id'      => $user,
					'seller_key'   => $seller_key,
					'seller_value' => 'customer',
				);
			}

			$seller_res = $this->wpdb->insert( $seller_table, $seller );

			return $seller_res;
		}

		/**
		 * Send password reset link
		 *
		 * @param $user_object
		 * @return $password_reset_link
		 */
		public function mkwc_send_password_reset_email( $user_login ) {
			if ( empty( $user_login ) ) {
				return false;
			} elseif ( strpos( $user_login, '@' ) ) {
				$user_data = get_user_by( 'email', trim( $user_login ) );

				if ( empty( $user_data ) ) {
					return false;
				}
			} else {
				$login = trim( $user_login );

				$user_data = get_user_by( 'login', $login );
			}

			if ( ! $user_data ) {
				return false;
			}

			// redefining user_login ensures we return the right case in the email
			$user_login = $user_data->user_login;

			$user_email = $user_data->user_email;

			$user_id = $user_data->ID;

			do_action( 'retreive_password', $user_login );  // Misspelled and deprecated

			do_action( 'retrieve_password', $user_login );

			$this->wpdb->update( $this->wpdb->users, array( 'user_activation_key' => '' ), array( 'user_login' => $user_login ) );

			$key = $this->wpdb->get_var( $this->wpdb->prepare( "SELECT user_activation_key FROM {$this->wpdb->users} WHERE user_login = %s", $user_login ) );

			if ( empty( $key ) ) {
				$key = get_password_reset_key( $user_data );

				do_action( 'retrieve_password_key', $user_login, $key );
			}

			$message = __( 'Someone requested that the password be reset for the following account:', 'mobikul_marketplace' ) . "\r\n\r\n";

			$message .= network_home_url( '/' ) . "\r\n\r\n";

			$message .= sprintf( __( 'Username: %s', 'mobikul_marketplace' ), $user_login ) . "\r\n\r\n";

			$message .= __( 'If this was a mistake, just ignore this email and nothing will happen.', 'mobikul_marketplace' ) . "\r\n\r\n";

			$message .= __( 'To reset your password, visit the following address:', 'mobikul_marketplace' ) . "\r\n\r\n";

			$message .= html_entity_decode(
				esc_url(
					add_query_arg(
						array(
							'key' => $key,
							'id'  => $user_id,
						),
						wc_get_endpoint_url( 'lost-password', '', wc_get_page_permalink( 'myaccount' ) )
					)
				)
			) . "\r\n";

			if ( is_multisite() ) {
				$blogname = $GLOBALS['current_site']->site_name;
			} else {              // The blogname option is escaped with esc_html on the way into the database in sanitize_option
				// we want to reverse this for the plain text arena of emails.
				$blogname = wp_specialchars_decode( get_option( 'blogname' ), ENT_QUOTES );
			}

			$title = sprintf( __( '[%s] Password Reset', 'mobikul_marketplace' ), $blogname );

			$title = apply_filters( 'retrieve_password_title', $title );

			$message = apply_filters( 'retrieve_password_message', $message, $key );

			if ( $message && ! wp_mail( $user_email, $title, $message ) ) {
				return array(
					'success' => false,
					'message' => __( 'The e-mail could not be sent. Possible reason: your host may have disabled the mail() function..', 'mobikul_marketplace' ),
				);
			}

			return array(
				'success' => true,
				'message' => '',
			);
		}


		/**
		 * Become a seller form post handler.
		 *
		 * @param array $customer_data Customer data.
		 */
		public function mkwc_become_a_seller( $customer_data ) {
			global $wkmarketplace;
			$customer_id = isset( $customer_data['wkmp_customer_id'] ) ? intval( wp_unslash( $customer_data['wkmp_customer_id'] ) ) : get_current_user_id();

			if ( $customer_id > 0 ) {
				$errors     = array();
				$first_name = empty( $customer_data['wkmp_firstname'] ) ? '' : $customer_data['wkmp_firstname'];
				$last_name  = empty( $customer_data['wkmp_lastname'] ) ? '' : $customer_data['wkmp_lastname'];
				$shop_name  = empty( $customer_data['wkmp_shopname'] ) ? '' : $customer_data['wkmp_shopname'];
				$store_url  = empty( $customer_data['wkmp_shopurl'] ) ? '' : $customer_data['wkmp_shopurl'];
				$sel_phone  = empty( $customer_data['wkmp_shopphone'] ) ? '' : $customer_data['wkmp_shopphone'];
				$user       = wkmp_get_seller_id_by_shop_address( $store_url );

				if ( empty( $first_name ) ) {
					$errors[] = esc_html__( 'Please enter your first name.', 'mobikul_marketplace' );
				}

				if ( empty( $last_name ) ) {
					$errors[] = esc_html__( 'Please enter your last name.', 'mobikul_marketplace' );
				}

				$shopname_visibility = get_option( 'wkmp_shop_name_visibility', 'required' );

				if ( 'remove' !== $shopname_visibility ) {
					if ( ( empty( $shop_name ) && 'required' === $shopname_visibility ) || ( ! empty( $shop_name ) && ! preg_match( '/^[-A-Za-z0-9_\s]{1,40}$/', $shop_name ) ) ) {
						$errors[] = esc_html__( 'Enter a valid shop name.', 'mobikul_marketplace' );
					}
				}

				$shopurl_visibility = get_option( 'wkmp_shop_url_visibility', 'required' );

				if ( 'remove' !== $shopurl_visibility ) {
					if ( empty( $store_url ) && 'required' === $shopurl_visibility ) {
						$errors[] = esc_html__( 'Please enter valid shop URL.', 'mobikul_marketplace' );
					} elseif ( preg_match( '/[\'^£$%&*()}{@#~?><>,|=_+¬]/', $store_url ) ) {
						$errors[] = esc_html__( 'You can not use special characters in shop url except HYPHEN(-).', 'mobikul_marketplace' );
					} elseif ( ctype_space( $store_url ) ) {
						$errors[] = esc_html__( 'White space(s) aren\'t allowed in shop url.', 'mobikul_marketplace' );
					} elseif ( $user ) {
						$errors[] = esc_html__( 'This shop URl already EXISTS, please try different shop url.', 'mobikul_marketplace' );
					}
				}

				if ( empty( $sel_phone ) || strlen( $sel_phone ) > 18 || ! preg_match( '/^\s*(?:\+?(\d{1,3}))?([-. (]*(\d{3})[-. )]*)?((\d{3})[-. ]*(\d{2,4})(?:[-.x ]*(\d+))?)\s*$/', $sel_phone ) ) {
					$errors[] = esc_html__( 'Please enter a valid phone number.', 'mobikul_marketplace' );
				}

				if ( ! empty( $errors ) ) {
					foreach ( $errors as $error ) {
						wc_print_notice( $error, 'error' );
					}
				} else {
					$customer_user = get_user_by( 'ID', $customer_id );
					$auto_approve  = get_option( '_wkmp_auto_approve_seller', false );

					$user_email = ( $customer_user instanceof \WP_User ) ? $customer_user->user_email : 'NA';
					$user_login = ( $customer_user instanceof \WP_User ) ? $customer_user->user_login : 'NA';

					$data = array(
						'user_id'      => $customer_id,
						'user_email'   => $user_email,
						'user_login'   => $user_login,
						'auto_approve' => $auto_approve,
						'firstname'    => $first_name,
						'lastname'     => $last_name,
						'store_name'   => $shop_name,
						'shop_url'     => $store_url,
						'phone'        => $sel_phone,
					);

					update_user_meta( $customer_id, 'first_name', $first_name );
					update_user_meta( $customer_id, 'billing_first_name', $first_name );
					update_user_meta( $customer_id, 'last_name', $last_name );
					update_user_meta( $customer_id, 'billing_last_name', $last_name );
					update_user_meta( $customer_id, 'shop_name', $shop_name );
					update_user_meta( $customer_id, 'shop_address', $store_url );
					update_user_meta( $customer_id, 'billing_phone', $sel_phone );
					$query_handler = Helper\WKWC_General_Queries::get_instance();
					$query_handler->wkmp_set_seller_meta( $customer_id );
					$query_handler->wkmp_set_seller_default_commission( $customer_id );

					$success_message = esc_html__( 'Your request has been successfully sent to Administrator. You will be notified via Email once it is processed.', 'mobikul_marketplace' );

					if ( $auto_approve ) {
						$success_message = esc_html__( 'Congratulations!! Your request has been accepted. Now you are a seller on the site.', 'mobikul_marketplace' );
					}

					update_user_meta( $customer_id, 'wkmp_show_register_notice', $success_message );

					do_action( 'wkmp_customer_become_seller', $data );
					do_action( 'wkmp_customer_become_seller_to_admin', $data );

					return array(
						'success' => true,
						'message' => esc_html__( 'Your request has been successfully sent to Administrator. You will be notified via Email once it is processed.', 'mobikul_marketplace' ),
					);

				}
			}
		}

	}
}

