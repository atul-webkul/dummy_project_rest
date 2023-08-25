<?php

/**
 * @author Webkul
 * @version 1.0.0
 * This file handles user related functions
 */

namespace WkMobikulMultivendorApi\Includes\Front\Users;

use WkMobikulMultivendorApi\Includes\Front\Users\Util;
use WkMobikulMultivendorApi\Includes;
use WkMobikulMultivendorApi\Helper;
use WkMobikulMultivendorApi\Includes\Front\Cart;
use WkMarketplace\Templates\Front\Customer;

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'Mkwc_Users' ) ) {
	/**
	 * Users class
	 */
	class Mkwc_Users extends Includes\Mkwc_Authentication implements Util\Mkwc_Users_Interface {

		public static $base = 'user';

		protected $wpdb;

		protected $response;

		protected $userdata;

		protected $general_data;

		protected $user_login_handler;

		function __construct() {
			global $wp, $wpdb;

			$this->wpdb = $wpdb;
		}

		/**
		 * Delete user
		 *
		 * @param int $userId
		 *
		 * @return json $response
		 */
		public function mkwc_delete_user( $data ) {

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

			$data = ( is_object( $data ) ) ? $data->get_params() : $data;

			$user_id  = $data['user_id'];
			$password = $data['password'];

			$user_check = $this->mkwc_user_exist_request( $user_id );

			if ( isset( $user_check['success'] ) && ! $user_check['success'] ) {

				$response = $user_check;

			} else {

				$userinfos = get_user_by( 'id', $user_id );

				$userinfo = $userinfos->data;

				$user_exist = wp_authenticate( $userinfo->user_login, $password );

				if ( is_wp_error( $user_exist ) ) {
					$response = array(
						'success' => false,
						'message' => esc_html__( 'Authentication Failed..!!', 'mobikul_marketplace' ),
					);
				} else {

					if ( ! function_exists( 'wp_delete_user' ) ) {
						require_once ABSPATH . 'wp-admin/includes/user.php';
					}

					$status = wp_delete_user( $user_id );

					if ( $status ) {
						$response = array(
							'success' => true,
							'message' => esc_html__( 'User deleted successfully..!!', 'mobikul_marketplace' ),
						);
					} else {
						$response = array(
							'success' => false,
							'message' => esc_html__( 'Something went wrong..!!', 'mobikul_marketplace' ),
						);
					}
				}
			}

			$response_handler->set_data( $response );

			return $response_handler;
		}

		/**
		 * Create User endpoint callback
		 *
		 * @param $data
		 * @return $user
		 */
		public function mkwc_create_user( $data ) {
			$count         = 0;
			$check         = 0;
			$cart_items    = array();
			$session_check = array();
			// $levels        = pmpro_getMembershipLevelsForUser( 1725 );

			// echo '<pre>';
			// print_r( $levels );
			// echo '</pre>';

			// exit;

			$table_name = $this->wpdb->prefix . 'woocommerce_sessions';

			$this->mkwc_login_set_session_expiration();

			if ( ! empty( $data['guest_id'] ) ) {
				$guest_id = $data['guest_id'];
			} else {
				$guest_id = '';
			}

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
			$userdata_handler = new Helper\Mkwc_User_Data();

			$data = ( is_object( $data ) ) ? $data->get_params() : $data;

			if ( ! isset( $data['password'] ) && empty( $data['password'] ) ) {
				return array(
					'success' => false,
					'message' => __( 'Sorry, you must provide all parameters.', 'mobikul_marketplace' ),
				);
			}

			$user_id = wc_create_new_customer( $data['email'], $data['username'], $data['password'] );

			if ( is_wp_error( $user_id ) ) {
				return array(
					'success' => false,
					'message' => esc_html( 'An account is already registered with your email address', 'mobikul_marketplace' ),
				);
			}

			if ( ! empty( $guest_id ) ) {

				$guest_session_data = $userdata_handler->mkwc_get_user_session( $table_name, $guest_id );
				if ( isset( $guest_session_data[0] ) ) {
					$guest_session_value = $guest_session_data[0]->session_value;
				} else {
					$guest_session_value = array();
				}

				if ( array_key_exists( 'cart', maybe_unserialize( $guest_session_value ) ) ) {
						$cart_items = maybe_unserialize( maybe_unserialize( $guest_session_value )['cart'] );
				}

				if ( ! empty( $cart_items ) ) {
						$data = $userdata_handler->mkwc_insert_user_session( $table_name, $user_id, $guest_session_value, $this->_session_expiration );
				}
			}

			if ( isset( $data['isSocial'] ) && $data['isSocial'] ) {
				update_user_meta( $user_id, 'mkwc_social_user_password', $data['password'] );
				if ( isset( $data['first_name'] ) && $data['first_name'] ) {
					update_user_meta( $user_id, 'first_name', $data['first_name'] );
				}
				if ( isset( $data['last_name'] ) && $data['last_name'] ) {
					update_user_meta( $user_id, 'last_name', $data['last_name'] );
				}
			}
			$service_type = '';
			if ( ! empty( $data['isSeller'] ) ) {
				$isseller = 0;
				if ( 1 === $data['isSeller'] ) {
					$isseller  = 1;
					$seller_vl = 'seller';
					$shop_name = isset( $data['shop_name'] ) ? $data['shop_name'] : '';
					$store_url = isset( $data['shop_url'] ) ? $data['shop_url'] : '';
				} elseif ( 2 === $data['isSeller'] ) {
					$isseller  = 2;
					$seller_vl = 'architect';
					$shop_name = isset( $data['company_name'] ) ? $data['shop_name'] : '';
					$store_url = isset( $data['profile_name'] ) ? $data['shop_url'] : '';
				} elseif ( 3 === $data['isSeller'] ) {
					$isseller     = 3;
					$seller_vl    = 'serviceproviders';
					$shop_name    = isset( $data['company_name'] ) ? $data['company_name'] : '';
					$store_url    = isset( $data['profile_name'] ) ? $data['profile_name'] : '';
					$service_type = isset( $data['service_type'] ) ? $data['service_type'] : '';
				}
				$user_creds = array();
				$user_nick  = isset( $data['first_name'] ) ? $data['first_name'] : '';

				$user_first_name = isset( $data['first_name'] ) ? $data['first_name'] : '';
				$user_last_name  = isset( $data['last_name'] ) ? $data['last_name'] : '';
				$user_email      = $data['email'];

				$sel_phone = isset( $data['phone'] ) ? $data['phone'] : '';

				/*check for activation*/
				try {
					if ( email_exists( $user_email ) ) {
						$user_creds   = array(
							'user_nicename' => $store_url,
							'display_name'  => $user_nick,
						);
						$newuser_id   = wp_update_user( $user_creds );
						$Isregistered = 'Regitered';
					}
					if ( ! empty( $service_type ) ) {
						update_user_meta( $user_id, 'provider', $service_type );
					}
					update_user_meta( $user_id, 'first_name', $user_first_name );
					update_user_meta( $user_id, 'last_name', $user_last_name );
					update_user_meta( $user_id, 'shop_name', $shop_name );
					update_user_meta( $user_id, 'shop_address', $store_url );
					update_user_meta( $user_id, 'billing_phone', $sel_phone );
					$result    = $userdata_handler->mkwc_seller_meta( $user_id, 'role', $seller_vl, $isseller );
					$tableName = 'new_seller';
				} catch ( Exception $e ) {
					if ( $e->getMessage() != 'success' ) {
						return array(
							'success' => false,
							'message' => $e->getMessage(),
						);
					}
				}
			}
			$seller_info      = $userdata_handler->mkwc_user_seller_info( $user_id );
			$user             = $this->mkwc_get_user( $user_id );
			$user['isSeller'] = $seller_info ? 1 : $isseller;
			$response_handler->set_data( $user );

			return $response_handler;
		}

		public function mkwc_login_set_session_expiration() {
				$this->_session_expiring   = time() + intval( apply_filters( 'wc_session_expiring', 60 * 60 * 47 ) ); // 47 Hours.
				$this->_session_expiration = time() + intval( apply_filters( 'wc_session_expiration', 60 * 60 * 48 ) ); // 48 Hours.
		}

		/**
		 * Get user by user id
		 *
		 * @param $user_id
		 * @return $user
		 */
		public function mkwc_get_user( $user_id ) {
			$user_id = ( is_object( $user_id ) ) ? (int) $user_id->get_param( 'user_id' ) : $user_id;

			$validate_auth_key = $this->mkwc_authenticate_request();

			if ( $validate_auth_key != 'ok' ) {
				return array(
					'session_id'             => $validate_auth_key,
					'status'                 => 401,
					'invalid_auth_key_error' => __( 'Please provide valid Auth Key.', 'mobikul_marketplace' ),
					'success'                => false,
				);
			}

			$user = get_user_by( 'id', $user_id );

			if ( ! $user ) {
				return array(
					'success' => false,
					'message' => __( 'Invalid user id.', 'mobikul_marketplace' ),
				);
			}

			$user_data = array(
				'id'               => $user->ID,
				'created_at'       => $user->user_registered,
				'email'            => $user->user_email,
				'first_name'       => $user->first_name,
				'last_name'        => $user->last_name,
				'username'         => $user->user_login,
				'display_name'     => $user->first_name ? $user->first_name . ' ' . $user->last_name : $user->user_login,
				'avatar_url'       => $this->mkwc_get_avatar_url( $user->customer_email ),
				'profile_image'    => $this->mkwc_get_user_media_url( $user->ID, 'profile' ),
				'banner_image'     => $this->mkwc_get_user_media_url( $user->ID, 'banner' ),
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
				'success'          => true,
				'message'          => '',
			);

			return $user_data;
		}

		/**
		 * Set user profile and banner
		 *
		 * @param $user_id
		 * @return $profiel and banner
		 */
		public function mkwc_set_user_profile_banner( $data ) {
			$response_handler = new \WP_REST_Response();

			$data              = ( is_object( $data ) ) ? $data->get_params() : $data;
			$user_id           = isset( $data['user_id'] ) ? $data['user_id'] : '';
			$image_id          = isset( $data['image_id'] ) ? $data['image_id'] : '';
			$banner_id         = isset( $data['banner_id'] ) ? $data['banner_id'] : '';
			$type              = isset( $data['type'] ) ? $data['type'] : '';
			$request           = isset( $data['request'] ) ? $data['request'] : '';
			$validate_auth_key = $this->mkwc_authenticate_request();

			if ( $validate_auth_key != 'ok' ) {
				return array(
					'session_id'             => $validate_auth_key,
					'status'                 => 401,
					'invalid_auth_key_error' => __( 'Please provide valid Auth Key.', 'mobikul_marketplace' ),
					'success'                => false,
				);
			}

			$user_check = $this->mkwc_user_exist_request( $user_id );

			if ( isset( $user_check['success'] ) && ! $user_check['success'] ) {
				return $user_check;
			}

			if ( empty( $user_id ) || ( empty( $banner_id ) && empty( $image_id ) ) || empty( $type ) || empty( $request ) ) {
				if ( $request !== 'delete' ) {
					return array(
						'success' => false,
						'message' => __( 'Sorry, you must provide all parameters.', 'mobikul_marketplace' ),
					);
				}
			}

			if ( $type === 'profile' ) {
				update_user_meta( $user_id, 'mobikul_profile_image', $image_id );
				if ( $request === 'add' ) {
					$response_handler->set_data(
						array(
							'success' => true,
							'message' => esc_html__( 'Profile image successfully added.', 'mobikul_marketplace' ),
						)
					);
				} elseif ( $request === 'update' ) {
					$response_handler->set_data(
						array(
							'success' => true,
							'message' => esc_html__( 'Profile image successfully updated.', 'mobikul_marketplace' ),
						)
					);
				} else {
					$response_handler->set_data(
						array(
							'success' => true,
							'message' => esc_html__( 'Profile image successfully deleted.', 'mobikul_marketplace' ),
						)
					);
				}
				return $response_handler;
			} elseif ( $type === 'banner' ) {
				update_user_meta( $user_id, 'mobikul_banner_image', $banner_id );
				if ( $request === 'add' ) {
					$response_handler->set_data(
						array(
							'success' => true,
							'message' => esc_html__( 'Banner image successfully added.', 'mobikul_marketplace' ),
						)
					);
				} elseif ( $request === 'update' ) {
					$response_handler->set_data(
						array(
							'success' => true,
							'message' => esc_html__( 'Banner image successfully updated.', 'mobikul_marketplace' ),
						)
					);
				} else {
					$response_handler->set_data(
						array(
							'success' => true,
							'message' => esc_html__( 'Banner image successfully deleted.', 'mobikul_marketplace' ),
						)
					);
				}
				return $response_handler;
			} elseif ( $type === 'both' ) {
				update_user_meta( $user_id, 'mobikul_profile_image', $image_id );
				if ( $request === 'add' ) {
					$response_handler->set_data(
						array(
							'success' => true,
							'message' => esc_html__( 'Profile and banner successfully added.', 'mobikul_marketplace' ),
						)
					);
				} elseif ( $request === 'update' ) {
					$response_handler->set_data(
						array(
							'success' => true,
							'message' => esc_html__( 'Profile and banner successfully updated.', 'mobikul_marketplace' ),
						)
					);
				} else {
					$response_handler->set_data(
						array(
							'success' => true,
							'message' => esc_html__( 'Profile and banner successfully deleted.', 'mobikul_marketplace' ),
						)
					);
				}
				return $response_handler;
			}
		}

		/**
		 * Get user avatar url
		 *
		 * @param $user_email
		 * @return $avatar
		 */
		public function mkwc_get_avatar_url( $email ) {
				$avatar_html = get_avatar( $email );

				// Get the URL of the avatar from the provided HTML
				preg_match( '/src=["|\'](.+)[\&|"|\']/U', $avatar_html, $matches );

			if ( isset( $matches[1] ) && ! empty( $matches[1] ) ) {
				return esc_url_raw( $matches[1] );
			}

				return '';
		}

		/**
		 * Get user media url
		 *
		 * @param $user_email
		 * @return $avatar
		 */
		public function mkwc_get_user_media_url( $user_id, $type = 'profile' ) {

			$product_store = new Helper\Mkwc_Product_store();

			$width   = isset( $_GET['width'] ) ? $_GET['width'] : '';
			$mFactor = isset( $_GET['mFactor'] ) ? $_GET['mFactor'] : 1;
			$height  = 0.5 * $width;
			$height *= $mFactor;
			$width  *= $mFactor;

			$size = array(
				'width'  => floatval( $width ),
				'height' => floatval( $height ),
				'crop'   => 1,
			);

			if ( $type === 'banner' ) {

				$attachment_id = get_user_meta( $user_id, 'mobikul_banner_image', true );

			} else {
				$attachment_id = get_user_meta( $user_id, 'mobikul_profile_image', true );
			}

			$attachment = '';

			if ( $attachment_id ) {
				$image_meta = wp_get_attachment_metadata( $attachment_id );

				$attachment = $product_store->mkwc_image_downsize( $image_meta, $size, $attachment_id );

				$attachment = isset( $attachment[0] ) ? $attachment[0] : '';
			}

			return $attachment;
		}

		/**
		 * Check input shop url is available
		 *
		 * @return $response
		 */
		public function mkwc_check_shop_url() {
			$validate_auth_key = $this->mkwc_authenticate_request( 0 );

			if ( $validate_auth_key != 'ok' ) {
				return array(
					'session_id'             => $validate_auth_key,
					'status'                 => 401,
					'invalid_auth_key_error' => __( 'Please provide valid Auth Key.', 'mobikul_marketplace' ),
					'success'                => false,
				);
			}
			$url_slug = isset( $_GET['slug'] ) ? sanitize_key( $_GET['slug'] ) : '';

			if ( ! $url_slug ) {
				return array(
					'success' => false,
					'message' => __( 'Sorry, you must provide all parameters.', 'mobikul_marketplace' ),
				);
			}

			if ( ctype_space( $url_slug ) ) {
				return array(
					'success' => false,
					'message' => __( 'Invalid shop url.', 'mobikul_marketplace' ),
				);
			}

			$user = get_user_by( 'slug', $url_slug );

			if ( $user != '' ) {
				return array(
					'success' => false,
					'message' => __( 'Not available.', 'mobikul_marketplace' ),
				);
			} else {
				return array(
					'success' => true,
					'message' => __( 'Available.', 'mobikul_marketplace' ),
				);
			}
		}


		public function mkwc_get_user_orders( $user_id ) {
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
			$orders           = array();
			$user_id          = ( is_object( $user_id ) ) ? (int) $user_id->get_param( 'user_id' ) : $user_id;
			$order_store      = new Helper\Mkwc_Order_store();
			$user_check       = $this->mkwc_user_exist_request( $user_id );

			if ( isset( $user_check['success'] ) && ! $user_check['success'] ) {
				return $user_check;
			}

			$orders = $order_store->mkwc_get_customer_orders( $user_id ) ? $order_store->mkwc_get_customer_orders( $user_id ) : array();
			if ( $orders ) {
				$orders = array_merge(
					$orders,
					array(
						'success' => true,
						'message' => '',
					)
				);
			} else {
				$orders = array_merge(
					$orders,
					array(
						'success' => false,
						'message' => __( 'No data found.', 'mobikul_marketplace' ),
					)
				);
			}

			$response_handler->set_data( $orders );

			return $response_handler;
		}

		/**
		 * Get order by customer id
		 *
		 * @param $customer_id
		 * @return $order_id
		 */
		public function mkwc_get_user_order( $id, $fields = null, $auth = true ) {
			if ( $auth ) {
				$validate_auth_key = $this->mkwc_authenticate_request();

				if ( $validate_auth_key != 'ok' ) {
					return array(
						'session_id'             => $validate_auth_key,
						'status'                 => 401,
						'invalid_auth_key_error' => esc_html__( 'Please provide valid Auth Key.', 'mobikul_marketplace' ),
						'success'                => false,
					);
				}
			}

			$general_handler = new Helper\Mkwc_General_Data();

			if ( is_wp_error( $id ) ) {
				return $id;
			}

			$id = ( is_object( $id ) ) ? $id->get_param( 'order_id' ) : $id;

			$order = wc_get_order( $id );

			if ( ! $order ) {
				return array(
					'success' => false,
					'message' => __( 'Sorry, No Result Found.', 'mobikul_marketplace' ),
				);
			}

			$order_post = get_post( $id );

			$customer_id = $order->get_user_id();

			$customer = new \WC_Customer( $customer_id );

			$total = $general_handler->mkwc_get_formatted_price( $order->get_total(), array( 'currency' => $order->get_currency() ) );

			$subtotal = $general_handler->mkwc_get_formatted_price( $order->get_subtotal(), array( 'currency' => $order->get_currency() ) );

			$total_tax = $general_handler->mkwc_get_formatted_price( $order->get_total_tax(), array( 'currency' => $order->get_currency() ) );

			$total_shipping = $general_handler->mkwc_get_formatted_price( $order->get_total_shipping(), array( 'currency' => $order->get_currency() ) );

			$cart_tax = $general_handler->mkwc_get_formatted_price( $order->get_cart_tax(), array( 'currency' => $order->get_currency() ) );

			$shipping_tax = $general_handler->mkwc_get_formatted_price( $order->get_shipping_tax(), array( 'currency' => $order->get_currency() ) );

			$total_discount = $general_handler->mkwc_get_formatted_price( $order->get_total_discount(), array( 'currency' => $order->get_currency() ) );

			$status_bg = '#e5e5e5'; // pending, cancelled, refunded
			if ( $order->get_status() === 'processing' ) {
				$status_bg = '#c6e1c6';
			} elseif ( $order->get_status() === 'on' ) {
				$status_bg = '#f8dda7';
			} elseif ( $order->get_status() === 'completed' ) {
				$status_bg = '#c8d7e1';
			} elseif ( $order->get_status() === 'failed' ) {
				$status_bg = '#eba3a3';
			}
			$order_data = array(
				'id'                        => $order->get_id(),
				'order_number'              => $order->get_order_number(),
				'created_at'                => isset( $order_post->post_date_gmt ) ? $order_post->post_date_gmt : '',
				'updated_at'                => isset( $order_post->post_modified_gmt ) ? $order_post->post_modified_gmt : '',
				'completed_at'              => $order->get_date_completed(),
				'status'                    => $order->get_status(),
				'status_bg'                 => $status_bg,
				'currency'                  => $order->get_currency(),
				'total'                     => $total ? $total : '',
				'subtotal'                  => $subtotal ? $subtotal : '',
				'total_line_items_quantity' => $order->get_item_count(),
				'total_tax'                 => $total_tax ? $total_tax : '',
				'total_shipping'            => $total_shipping ? $total_shipping : '',
				'cart_tax'                  => $cart_tax ? $cart_tax : '',
				'shipping_tax'              => $shipping_tax ? $shipping_tax : '',
				'total_discount'            => $total_discount ? $total_discount : '',
				'shipping_methods'          => $order->get_shipping_method(),
				'payment_details'           => array(
					'method_id'    => $order->get_payment_method(),
					'method_title' => $order->get_payment_method_title(),
					'paid'         => isset( $order->paid_date ) ? $order->paid_date : '',
				),
				'billing_address'           => array(
					'first_name' => $customer->get_billing_first_name(),
					'last_name'  => $customer->get_billing_last_name(),
					'company'    => $customer->get_billing_company(),
					'address_1'  => $customer->get_billing_address_1(),
					'address_2'  => $customer->get_billing_address_2(),
					'city'       => $customer->get_billing_city(),
					'state'      => $customer->get_billing_state(),
					'postcode'   => $customer->get_billing_postcode(),
					'country'    => $customer->get_billing_country(),
					'email'      => $customer->get_billing_email(),
					'phone'      => $customer->get_billing_phone(),
				),
				'shipping_address'          => array(
					'first_name' => $customer->get_shipping_first_name(),
					'last_name'  => $customer->get_shipping_last_name(),
					'company'    => $customer->get_shipping_company(),
					'address_1'  => $customer->get_shipping_address_1(),
					'address_2'  => $customer->get_shipping_address_2(),
					'city'       => $customer->get_shipping_city(),
					'state'      => $customer->get_shipping_state(),
					'postcode'   => $customer->get_shipping_postcode(),
					'country'    => $customer->get_shipping_country(),
				),
				'note'                      => $order->get_customer_note(),
				'customer_ip'               => $order->get_customer_ip_address(),
				'customer_user_agent'       => $order->get_customer_user_agent(),
				'customer_id'               => $order->get_user_id(),
				'view_order_url'            => $order->get_view_order_url(),
				'line_items'                => array(),
				'shipping_lines'            => array(),
				'tax_lines'                 => array(),
				'fee_lines'                 => array(),
				'coupon_lines'              => array(),
			);

			// add line items
			foreach ( $order->get_items() as $item_id => $item ) {

				$product     = $order->get_product_from_item( $item );
				$product_id  = null;
				$product_sku = null;

				// Check if the product exists.
				if ( is_object( $product ) ) {
					$product_id  = ( isset( $product->variation_id ) ) ? $product->variation_id : $product->get_id();
					$product_sku = $product->get_sku();
				}

				$meta = new \WC_Order_Item_Meta( $item, $product );

				$item_meta = array();

				$hideprefix = '_';

				foreach ( $meta->get_formatted( $hideprefix ) as $meta_key => $formatted_meta ) {
					$item_meta[] = array(
						'name'   => $formatted_meta['key'],
						'option' => $formatted_meta['value'],
						'title'  => $formatted_meta['label'],
					);
				}

				$line_subtotal = $general_handler->mkwc_get_formatted_price( $order->get_line_subtotal( $item ), array( 'currency' => $order->get_currency() ) );

				$line_total = $general_handler->mkwc_get_formatted_price( $order->get_line_total( $item ), array( 'currency' => $order->get_currency() ) );

				$line_tax_total = $general_handler->mkwc_get_formatted_price( $order->get_line_tax( $item ), array( 'currency' => $order->get_currency() ) );

				$line_price = $general_handler->mkwc_get_formatted_price( $order->get_item_total( $item ), array( 'currency' => $order->get_currency() ) );

				$order_data['line_items'][] = array(
					'id'            => $item_id,
					'subtotal'      => $line_subtotal ? $line_subtotal : '',
					'total'         => $line_total ? $line_total : '',
					'total_tax'     => $line_tax_total ? $line_tax_total : '',
					'price'         => $line_price ? $line_price : '',
					'quantity'      => (int) $item['qty'],
					'tax_class'     => ( ! empty( $item['tax_class'] ) ) ? $item['tax_class'] : null,
					'name'          => html_entity_decode( $item['name'] ),
					'product_id'    => $product ? $product->get_id() : '',
					'product_image' => $product ? get_the_post_thumbnail_url( $product->get_id(), 'post-thumbnail' ) : '',
					'sku'           => $product_sku,
					'variation'     => $item_meta,
				);
			}

			// add shipping
			foreach ( $order->get_shipping_methods() as $shipping_item_id => $shipping_item ) {

				$shipping_line_total = $general_handler->mkwc_get_formatted_price( $shipping_item['cost'], array( 'currency' => $order->get_currency() ) );

				$order_data['shipping_lines'][] = array(
					'id'           => $shipping_item_id,
					'method_id'    => $shipping_item['method_id'],
					'method_title' => $shipping_item['name'],
					'total'        => $shipping_line_total ? $shipping_line_total : '',
				);
			}

			// add taxes
			foreach ( $order->get_tax_totals() as $tax_code => $tax ) {

				$tax_line_total = $general_handler->mkwc_get_formatted_price( $tax->amount, array( 'currency' => $order->get_currency() ) );

				$order_data['tax_lines'][] = array(
					'code'     => $tax_code,
					'title'    => $tax->label,
					'total'    => $tax_line_total ? $tax_line_total : '',
					'compound' => (bool) $tax->is_compound,
				);
			}

			// add fees
			foreach ( $order->get_fees() as $fee_item_id => $fee_item ) {

				$fee_line_total = $general_handler->mkwc_get_formatted_price( $order->get_line_total( $fee_item ), array( 'currency' => $order->get_currency() ) );

				$fee_line_total_tax = $general_handler->mkwc_get_formatted_price( $order->get_line_tax( $fee_item ), array( 'currency' => $order->get_currency() ) );

				$order_data['fee_lines'][] = array(
					'id'        => $fee_item_id,
					'title'     => $fee_item['name'],
					'tax_class' => ( ! empty( $fee_item['tax_class'] ) ) ? $fee_item['tax_class'] : null,
					'total'     => $fee_line_total ? $fee_line_total : '',
					'total_tax' => $fee_line_total_tax ? $fee_line_total_tax : '',
				);
			}

			// add coupons
			foreach ( $order->get_items( 'coupon' ) as $coupon_item_id => $coupon_item ) {

				$coupon_amount = $general_handler->mkwc_get_formatted_price( $coupon_item['discount_amount'], array( 'currency' => $order->get_currency() ) );

				$order_data['coupon_lines'][] = array(
					'id'     => $coupon_item_id,
					'code'   => $coupon_item['name'],
					'amount' => $coupon_amount ? $coupon_amount : '',
				);
			}

			return array(
				'order'   => apply_filters( 'woocommerce_api_order_response', $order_data, $order, $fields ),
				'success' => true,
				'message' => '',
			);
		}

		/**
		 * Get order by customer id
		 *
		 * @param $customer_id
		 * @return $order_id
		 */
		public function mkwc_user_reorder( $id ) {
			$validate_auth_key = $this->mkwc_authenticate_request( 0 );

			if ( $validate_auth_key != 'ok' ) {
				return array(
					'session_id'             => $validate_auth_key,
					'status'                 => 401,
					'invalid_auth_key_error' => esc_html__( 'Please provide valid Auth Key.', 'mobikul_marketplace' ),
					'success'                => false,
				);
			}

			$id               = ( is_object( $id ) ) ? $id->get_param( 'order_id' ) : $id;
			$cart_handler_obj = new Cart\Mkwc_Cart();
			$cart_empty_obj   = new Cart\Mkwc_Cart_Empty();
			$response_handler = new \WP_REST_Response();

			$order_details = $this->mkwc_get_user_order( $id, null, false );
			$response      = array();
			if ( isset( $order_details['order']['line_items'] ) ) {
				$line_item_arr = $order_details['order']['line_items'];
				$product_data  = array();
				if ( is_array( $line_item_arr ) ) {
					$cart_empty_obj->mkwc_cart_empty( array( 'customer_id' => $order_details['order']['customer_id'] ) );
					foreach ( $line_item_arr as $lark => $larv ) {
						$product_data['product_id']   = $larv['product_id'];
						$product_data['variations']   = $larv['variation'];
						$product_data['variation_id'] = '';
						$product_data['quantity']     = $larv['quantity'];
						$product_data['customer_id']  = $order_details['order']['customer_id'];

						$response = $cart_handler_obj->mkwc_cart( $product_data, false );
					}

					if ( isset( $response->data['success'] ) && $response->data['success'] == true ) {
						$response = array(
							'success' => true,
							'message' => $response->data['message'],
						);
					}
				}
			} else {
				$response = array(
					'success' => false,
					'message' => esc_html__( 'Sorry, please provide correct order id.', 'mobikul_marketplace' ),
				);
			}
			if ( isset( $response['success'] ) && $response['success'] == true ) {
				$response['message'] = esc_html__( 'The cart has been filled with the items from your previous order.', 'mobikul_marketplace' );
			}
			$response_handler->set_data( $response );

			return $response_handler;
		}

		/**
		 * User login endpoint callback
		 *
		 * @param $data
		 * @return $user
		 */

		public function mkwc_user_login( $data ) {
			$validate_auth_key = $this->mkwc_authenticate_request( 0 );

			if ( $validate_auth_key != 'ok' ) {
				return array(
					'session_id'             => $validate_auth_key,
					'status'                 => 401,
					'invalid_auth_key_error' => __( 'Please provide valid Auth Key.', 'mobikul_marketplace' ),
					'success'                => false,
				);
			}
			$user_login_handler = new Helper\Mkwc_User_Login_Data();

			if ( empty( $data['key'] ) || empty( $data['pass'] ) ) {
					return array(
						'success' => false,
						'message' => __( 'Sorry, you must provide all parameters.', 'mobikul_marketplace' ),
					);
			}

			return $user_login_handler->mkwc_user_login_data( $data );
		}

		/**
		 * User social login endpoint callback
		 *
		 * @param $data
		 * @return $user
		 */

		public function mkwc_user_social_login( $data ) {
			$validate_auth_key = $this->mkwc_authenticate_request( 0 );

			if ( $validate_auth_key != 'ok' ) {
				return array(
					'session_id'             => $validate_auth_key,
					'status'                 => 401,
					'invalid_auth_key_error' => __( 'Please provide valid Auth Key.', 'mobikul_marketplace' ),
					'success'                => false,
				);
			}
			$user_login_handler = new Helper\Mkwc_User_Login_Data();

			if ( empty( $data['email'] ) ) {
					return array(
						'success' => false,
						'message' => __( 'Sorry, you must provide all parameters.', 'mobikul_marketplace' ),
					);
			}
			$user_data = get_user_by( 'email', $data['email'] );
			if ( $user_data ) {
				$response = $this->mkwc_get_user_social_login( $user_data->ID, $data );
			} else {
				$createData     = array(
					'username'   => $data['email'],
					'email'      => $data['email'],
					'password'   => $this->mkwc_generate_random_password(),
					'first_name' => isset( $data['first_name'] ) ? $data['first_name'] : '',
					'last_name'  => isset( $data['last_name'] ) ? $data['last_name'] : '',
					'isSocial'   => true,
				);
				$createResponse = $this->mkwc_create_user( $createData );
				$createResponse = ( is_object( $createResponse ) && isset( $createResponse->data ) ) ? $createResponse->data : $createResponse;
				$userId         = isset( $createResponse['id'] ) ? $createResponse['id'] : '';
				$response       = $this->mkwc_get_user_social_login( $userId, $data );
			}
			return $response;
		}

		/**
		 * User Logout endpoint callback
		 *
		 * @return $response
		 */
		public function mkwc_user_logout( $id ) {

			$validate_auth_key = $this->mkwc_authenticate_request();
			if ( $validate_auth_key != 'ok' ) {
				return array(
					'session_id'             => $validate_auth_key,
					'status'                 => 401,
					'invalid_auth_key_error' => __( 'Please provide valid Auth Key.', 'mobikul_marketplace' ),
					'success'                => false,
				);
			}

			$userdata_handler = new Helper\Mkwc_User_Data();
			$id               = ( is_object( $id ) ) ? $id->get_param( 'user_id' ) : $id;
			if ( empty( $id ) ) {
				return array(
					'success' => false,
					'message' => __( 'Sorry, you must provide all parameters.', 'mobikul_marketplace' ),
				);
			}
			$table_name = $this->wpdb->prefix . 'woocommerce_sessions';
			$session    = $userdata_handler->mkwc_get_user_session( $table_name, $id );
			if ( ! empty( $session ) ) {

				$cart_data = ( ! empty( $session[0]->session_value ) ) ? maybe_unserialize( $session[0]->session_value )['cart'] : '';

				$cart_data = array( 'cart' => $cart_data );

				update_user_meta( $id, '_mobikul_api_persistent_cart', $session[0]->session_value );

				update_user_meta( $id, '_mobikul_session_expiry', $session[0]->session_expiry );

				update_user_meta( $id, '_woocommerce_persistent_cart_' . get_current_blog_id(), $cart_data );

				$userdata_handler->mkwc_delete_user_session( $table_name, $id );
			}
			return array(
				'success' => true,
				'message' => __( 'You\'ve been successfully logged out.', 'mobikul_marketplace' ),
			);
		}

		/**
		 * Set user address endpoint callback
		 *
		 * @param $data
		 * @return $user
		 **/
		public function mkwc_set_user_address( $user_id ) {
			$validate_auth_key = $this->mkwc_authenticate_request();

			if ( $validate_auth_key != 'ok' ) {
				return array(
					'session_id'             => $validate_auth_key,
					'status'                 => 401,
					'invalid_auth_key_error' => __( 'Please provide valid Auth Key.', 'mobikul_marketplace' ),
					'success'                => false,
				);
			}

			$userdata_handler = new Helper\Mkwc_User_Data();

			$cartdata_handler = new Helper\Mkwc_Cart_Data();

			$data = $user_id->get_json_params();

			if ( ! $data ) {
				 $data = $_POST;
			}

				$customer_id = ( is_object( $user_id ) ) ? $user_id->get_param( 'user_id' ) : $user_id;

			$user_check = $this->mkwc_user_exist_request( $customer_id );

			if ( isset( $user_check['success'] ) && ! $user_check['success'] ) {
				return $user_check;
			}

			$user = get_userdata( $customer_id );

			$request = isset( $_GET['request'] ) ? $_GET['request'] : '';

			if ( get_userdata( $customer_id ) == true ) {
				$user_data = $userdata_handler->mkwc_update_user_address( $customer_id, $data );

				if ( $user_data['success'] == true ) {
					return $user_data;
				} else {
					return array(
						'success' => false,
						'message' => __( 'Address Updation Failed.', 'mobikul_marketplace' ),
					);
				}
			} else {
				if ( $request == 'checkout' ) {
					$customer_id = ( is_object( $user_id ) ) ? $user_id->get_param( 'user_id' ) : $user_id;

					$customer_methods = $userdata_handler->mkwc_update_shipping_methods( $customer_id, $data, $user = false );

					$custom_check = $cartdata_handler->mkwc_return_cart_empty( $customer_id );

					$is_shipping_eligible = $cartdata_handler->mkwc_return_shipping_eligible( $custom_check );

					$message_print = ( $is_shipping_eligible == false ) ? __( 'No shipping methods required.', 'mobikul_marketplace' ) : __( 'No shipping method found.', 'mobikul_marketplace' );

					return array(
						'billing_address'      => ! empty( $customer_methods['billing_address'] ) ? $customer_methods['billing_address'] : __( 'Billing address not saved.', 'mobikul_marketplace' ),
						'shipping_address'     => ! empty( $customer_methods['shipping_address'] ) ? $customer_methods['shipping_address'] : __( 'Shipping address not saved.', 'mobikul_marketplace' ),
						'shipping_methods'     => ! empty( $customer_methods['shipping_address'] ) ? $customer_methods['shipping_methods'] : '',
						'total'                => $customer_methods['cart_total'],
						'is_shipping_eligible' => $is_shipping_eligible,
						'success'              => ( empty( $customer_methods['shipping_address'] ) || empty( $customer_methods['shipping_methods'] ) ) ? false : true,
						'shipping_message'     => ( empty( $customer_methods['shipping_address'] ) || empty( $customer_methods['shipping_methods'] ) ) ? $message_print : '',
					);
				} else {
					return array(
						'success' => false,
						'message' => __( 'User not registered.', 'mobikul_marketplace' ),
					);
				}
			}
		}





		/**
		 * Create user as seller endpoint callback
		 *
		 * @param $data
		 * @return $user
		 **/
		public function mkwc_make_user_as_seller( $data ) {
			global $wkmarketplace;
			$validate_auth_key = $this->mkwc_authenticate_request();

			if ( $validate_auth_key != 'ok' ) {
				return array(
					'session_id'             => $validate_auth_key,
					'status'                 => 401,
					'invalid_auth_key_error' => __( 'Please provide valid Auth Key.', 'mobikul_marketplace' ),
					'success'                => false,
				);
			}
			$data = $data->get_params();

			$user_id    = $data['user_id'];
			$user_check = $this->mkwc_user_exist_request( $user_id );

			if ( isset( $user_check['success'] ) && ! $user_check['success'] ) {
				return $user_check;
			}
			$shop_name  = $data['shop_name'];
			$shop_phone = $data['shop_phone'];
			$shop_url   = str_replace( ' ', '-', strtolower( $shop_name ) );

			$first_name = get_user_meta( $user_id, 'first_name', true );
			$first_name = empty( $first_name ) ? get_user_meta( $user_id, 'billing_first_name', true ) : $first_name;
			$first_name = empty( $first_name ) ? get_user_meta( $user_id, 'shipping_first_name', true ) : $first_name;

			$last_name = get_user_meta( $user_id, 'last_name', true );
			$last_name = empty( $last_name ) ? get_user_meta( $user_id, 'billing_last_name', true ) : $last_name;
			$last_name = empty( $last_name ) ? get_user_meta( $user_id, 'shipping_last_name', true ) : $last_name;

			$shop_slug_exist = wkmp_get_seller_id_by_shop_address( $shop_url );

			if ( $shop_slug_exist ) {
				return array(
					'success' => false,
					'message' => __( 'This shop URl already EXISTS, please try different shop url.', 'mobikul_marketplace' ),
				);
			}

			$data = array(
				'wkmp_customer_id' => $user_id,
				'wkmp_firstname'   => $first_name,
				'wkmp_lastname'    => $last_name,
				'wkmp_shopname'    => $shop_name,
				'wkmp_shopurl'     => $shop_url,
				'wkmp_shopphone'   => $shop_phone,
			);

			$userdata_handler = new Helper\Mkwc_User_Data();

			$result = $userdata_handler->mkwc_become_a_seller( $data );

			return $result;
		}




		/**
		 * edit user account endpoint callback
		 *
		 * @param $data
		 * @return $user
		 **/
		public function mkwc_edit_user_account( $user_id ) {
			$validate_auth_key = $this->mkwc_authenticate_request();

			if ( $validate_auth_key != 'ok' ) {
				return array(
					'session_id'             => $validate_auth_key,
					'status'                 => 401,
					'invalid_auth_key_error' => __( 'Please provide valid Auth Key.', 'mobikul_marketplace' ),
					'success'                => false,
				);
			}

			$data = $user_id->get_json_params();

				$id = ( is_object( $user_id ) ) ? (int) $user_id->get_param( 'user_id' ) : $user_id;

			$user_check = $this->mkwc_user_exist_request( $id );

			if ( isset( $user_check['success'] ) && ! $user_check['success'] ) {
				return $user_check;
			}

			if ( ! $data ) {
				$data = $_POST;
			}
				$user = get_user_by( 'id', $id );

			$first_name = isset( $data['first_name'] ) ? $data['first_name'] : '';

			$last_name = isset( $data['last_name'] ) ? $data['last_name'] : '';

			$email = isset( $data['email'] ) ? $data['email'] : '';
			// if ( empty( $first_name ) ) {
			// return array(
			// 'success' => false,
			// 'message' => __( 'Please enter first name.', 'mobikul_marketplace' ),
			// );
			// } elseif ( empty( $last_name ) ) {
			// return array(
			// 'success' => false,
			// 'message' => __( 'Please enter last name.', 'mobikul_marketplace' ),
			// );
			// } elseif ( empty( $email ) ) {
			// return array(
			// 'success' => false,
			// 'message' => __( 'Please enter email.', 'mobikul_marketplace' ),
			// );
			// }
			if ( isset( $data['first_name'] ) ) {
				update_user_meta( $id, 'first_name', wc_clean( $data['first_name'] ) );
			}
			if ( isset( $data['last_name'] ) ) {
				update_user_meta( $id, 'last_name', wc_clean( $data['last_name'] ) );
			}
			if ( isset( $data['email'] ) ) {
				$user_email = wp_update_user(
					array(
						'ID'         => $id,
						'user_email' => $data['email'],
					)
				);

				if ( is_wp_error( $user_email ) ) {
					return array(
						'success' => false,
						'message' => __( 'Account Updation Failed.', 'mobikul_marketplace' ),
					);
				}
			}
			if ( isset( $data['old_password'] ) && $data['old_password'] ) {
				// return array(
				// 'success' => false,
				// 'message' => __( 'Demo user is not allowed to change the password.', 'mobikul_marketplace' ),
				// );
				$user_authenticate = wp_authenticate( $user->data->user_email, $data['old_password'] );

				if ( array_key_exists( 'errors', $user_authenticate ) ) {
						return array(
							'success' => false,
							'message' => __( 'Incorrect Password.', 'mobikul_marketplace' ),
						);
				} elseif ( $data['old_password'] && isset( $data['new_password'] ) && $data['new_password'] && strcmp( $data['old_password'], $data['new_password'] ) == 0 ) {
					return array(
						'success' => false,
						'message' => __( 'New password should not same as old password.', 'mobikul_marketplace' ),
					);
				} elseif ( isset( $data['new_password'] ) && isset( $data['confirm_password'] ) ) {
					if ( strcmp( $data['new_password'], $data['confirm_password'] ) == 0 ) {
						$user_pass = wp_update_user(
							array(
								'ID'        => $id,
								'user_pass' => $data['new_password'],
							)
						);

						if ( is_wp_error( $user_pass ) ) {
								return array(
									'success' => false,
									'message' => __( 'Account Updation Failed.', 'mobikul_marketplace' ),
								);
						}
					} else {
						return array(
							'success' => false,
							'message' => __( 'Confirm Password does not match new password.', 'mobikul_marketplace' ),
						);
					}
				} elseif ( ( isset( $data['new_password'] ) && ! isset( $data['confirm_password'] ) ) || ( ! isset( $data['new_password'] ) && isset( $data['confirm_password'] ) ) ) {
						return array(
							'success' => false,
							'message' => __( 'Confirm Password does not match new password.', 'mobikul_marketplace' ),
						);
				}
			}
			if ( ! isset( $data['first_name'] ) && ! isset( $data['last_name'] ) && ! isset( $data['email'] ) && ! isset( $data['old_password'] ) ) {
				return array(
					'success' => false,
					'message' => __( 'Sorry, Nothing to update.', 'mobikul_marketplace' ),
				);
			}

			return array(
				'success' => true,
				'message' => __( 'Updated Successfully.', 'mobikul_marketplace' ),
			);
		}

		/**
		 * User downloads endpoint callback
		 *
		 * @return $response
		 **/
		public function mkwc_user_downloads( $user_id, $fields = null ) {
			$validate_auth_key = $this->mkwc_authenticate_request();

			if ( $validate_auth_key != 'ok' ) {
				return array(
					'session_id'             => $validate_auth_key,
					'status'                 => 401,
					'invalid_auth_key_error' => __( 'Please provide valid Auth Key.', 'mobikul_marketplace' ),
					'success'                => false,
				);
			}

			if ( is_wp_error( $user_id ) ) {
				return $user_id;
			}

				$id = ( is_object( $user_id ) ) ? (int) $user_id->get_param( 'user_id' ) : $user_id;

			$user_check = $this->mkwc_user_exist_request( $id );

			if ( isset( $user_check['success'] ) && ! $user_check['success'] ) {
				return $user_check;
			}
				$downloads = array();

				$_downloads = wc_get_customer_available_downloads( $id );

			foreach ( $_downloads as $key => $download ) {
				$downloads[ $key ] = $download;

				$downloads[ $key ]['access_expires'] = $downloads[ $key ]['access_expires'];
			}

				$response = apply_filters( 'woocommerce_api_customer_downloads_response', $downloads, $id, $fields );

			if ( ! empty( $response ) ) {
				$file_ext_type = array();
				foreach ( $response as $key => $value ) {
					if ( isset( $value['file']['file'] ) && $value['file']['file'] ) {
						$file_ext_type = wp_check_filetype( $value['file']['file'] );
					}
					$response[ $key ]['download_name'] = html_entity_decode( $value['download_name'] );

					$response[ $key ]['downloads_remaining'] = $value['downloads_remaining'] ? $value['downloads_remaining'] : '∞';

					$response[ $key ]['access_expires'] = $value['access_expires'] ? $value['access_expires'] : 'Never';

					$response[ $key ]['download_extension'] = ( isset( $file_ext_type['ext'] ) && $file_ext_type['ext'] ) ? $file_ext_type['ext'] : '';

					$response[ $key ]['download_type'] = ( isset( $file_ext_type['type'] ) && $file_ext_type['type'] ) ? $file_ext_type['type'] : '';
				}
				return array(
					'downloads' => $response,
					'success'   => true,
					'message'   => '',
				);
			} else {
				return array(
					'downloads' => '',
					'success'   => false,
					'message'   => __( 'No downloads available yet.', 'mobikul_marketplace' ),
				);
			}

		}

		/**
		 * User reviews endpoint callback
		 *
		 * @return $response
		 **/
		public function mkwc_user_reviews( $user_id ) {
			echo 'hi';
			exit;
			$validate_auth_key = $this->mkwc_authenticate_request();

			if ( $validate_auth_key != 'ok' ) {
				return array(
					'session_id'             => $validate_auth_key,
					'status'                 => 401,
					'invalid_auth_key_error' => __( 'Please provide valid Auth Key.', 'mobikul_marketplace' ),
					'success'                => false,
				);
			}

			if ( is_wp_error( $user_id ) ) {
				return $user_id;
			}

			$user_id = ( is_object( $user_id ) ) ? (int) $user_id->get_param( 'user_id' ) : $user_id;

			$user_check = $this->mkwc_user_exist_request( $user_id );

			if ( isset( $user_check['success'] ) && ! $user_check['success'] ) {
				return $user_check;
			}
			$response_handler     = new \WP_REST_Response();
			$product_store        = new Helper\Mkwc_Product_store();
			$general_data_handler = new Helper\Mkwc_General_Data();
			$width                = isset( $_GET['width'] ) ? $_GET['width'] : '';
			$mFactor              = isset( $_GET['mFactor'] ) ? $_GET['mFactor'] : 1;
			$img_size             = floor( $width / 2 * $mFactor );
			$size                 = array(
				'width'  => floatval( $img_size ),
				'height' => floatval( $img_size ),
				'crop'   => 1,
			);
			$reviews              = array();
			$args                 = array(
				'user_id' => $user_id, // use user_id
				'status'  => 'approve',
			);
			$user_comments        = get_comments( $args );

			foreach ( $user_comments as $comment ) :
				$product_id[] = $comment->comment_post_ID;
		  endforeach;

			$product_id = array_unique( $product_id );
			foreach ( $product_id as $pdkey => $pdvalue ) {
				$comments      = get_approved_comments( $pdvalue );
				$product       = wc_get_product( $pdvalue );
				$product_image = $product_store->mkwc_product_image_downsize( $pdvalue, $size );
				$user_rating   = 0;
				$rating_count  = 0;
				foreach ( $comments as $cmkey => $cmvalue ) {
					if ( (int) $cmvalue->user_id === (int) $user_id ) {
						$user_rating += get_comment_meta( $cmvalue->comment_ID, 'rating', true );
						$reviews[]    = array(
							'id'             => $cmvalue->comment_ID,
							'created_at'     => $cmvalue->comment_date_gmt,
							'review'         => $cmvalue->comment_content,
							'rating'         => get_comment_meta( $cmvalue->comment_ID, 'rating', true ),
							'reviewer_name'  => $cmvalue->comment_author,
							'reviewer_email' => $cmvalue->comment_author_email,
							'verified'       => wc_review_is_from_verified_owner( $cmvalue->comment_ID ),
						);
						$rating_count++;
					}
				}
				$average_rating = $user_rating / $rating_count;
				// $product_data[] = array(
				// 'product_id'     => $pdvalue,
				// 'name'           => get_the_title( $pdvalue ),
				// 'image'          => isset( $product_image[0] ) ? $product_image[0] : '',
				// 'dominantColor'  => isset( $product_image[0] ) ? $general_data_handler->mkwc_get_dominant_Color( $product_image[0] ) : $general_data_handler->mkwc_get_dominant_Color( '' ),
				// 'average_rating' => wc_format_decimal( $product->get_average_rating(), 2 ),
				// 'rating_count'   => (int) $product->get_rating_count(),
				// 'reviews'        => $reviews,
				// );

				$product_data[] = array(
					'product_id'     => $pdvalue,
					'name'           => get_the_title( $pdvalue ),
					'image'          => isset( $product_image[0] ) ? $product_image[0] : '',
					'dominantColor'  => isset( $product_image[0] ) ? $general_data_handler->mkwc_get_dominant_Color( $product_image[0] ) : $general_data_handler->mkwc_get_dominant_Color( '' ),
					'average_rating' => wc_format_decimal( $average_rating, 2 ),
					'rating_count'   => (int) $rating_count,
					'reviews'        => $reviews,
				);
				$reviews        = array();
			}

			if ( $product_data ) {
				$data = array(
					'success' => true,
					'message' => '',
					'data'    => $product_data,
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
		 * User forgot password endpoint callback
		 *
		 * @return $response
		 **/
		public function mkwc_user_forgot_password( $data ) {
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

			$userdata_handler = new Helper\Mkwc_User_Data();

				$email = ( is_object( $data ) ) ? $data->get_param( 'email' ) : $data;

			if ( empty( $email ) ) {
				return array(
					'success' => false,
					'message' => __( 'Sorry, you must provide all parameters.', 'mobikul_marketplace' ),
				);
			}

			if ( email_exists( $email ) ) {
					$retreive_password = $userdata_handler->mkwc_send_password_reset_email( $email );

				if ( $retreive_password['success'] ) {
						$response_handler->set_data(
							array(
								'success' => true,
								'message' => __( 'Password reset email has been sent.', 'mobikul_marketplace' ),
							)
						);

						return $response_handler;
				} else {
							$response_handler->set_data(
								array(
									'success' => false,
									'message' => $retreive_password['message'],
								)
							);
						return $response_handler;
				}
			} else {
					$response_handler->set_data(
						array(
							'success' => false,
							'message' => __( 'Invalid Email.', 'mobikul_marketplace' ),
						)
					);
			}
				return $response_handler;
		}

		public function mkwc_generate_random_password( $length = 14 ) {
			$random_string = substr( str_shuffle( str_repeat( $x = '989213119013123abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ', ceil( $length / strlen( $x ) ) ) ), 1, $length );

			return $random_string;
		}

		public function mkwc_get_user_social_login( $id, $data ) {
			$user_login_handler = new Helper\Mkwc_User_Login_Data();
			$userPass           = get_user_meta( $id, 'mkwc_social_user_password', true );
			$loginData          = array(
				'key'      => $data['email'],
				'pass'     => $userPass,
				'guest_id' => isset( $data['guest_id'] ) ? $data['guest_id'] : '',
			);
			return $user_login_handler->mkwc_user_login_data( $loginData );
		}
	}
}
