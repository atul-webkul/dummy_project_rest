<?php

/**
 * @author Webkul
 * @version 1.0.0
 * This file handles seller edit profile functions.
 */

namespace WkMobikulMultivendorApi\Includes\Front\Seller;

use WkMobikulMultivendorApi\Includes;
use WkMobikulMultivendorApi\Includes\Front\Seller\Util;
use WkMobikulMultivendorApi\Helper;

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'Mkwc_Seller_Edit_Profile' ) ) {
	/**
	 * Seller edit profile
	 */
	class Mkwc_Seller_Edit_Profile extends Includes\Mkwc_Authentication implements Util\Mkwc_Seller_Edit_Profile_Interface {

		public static $base = 'seller/edit-profile';

		protected $mp_form_handler;

		protected $seller_data_handler;

		protected $wpdb;

		public function __construct() {
			global $wpdb;

			$this->wpdb = $wpdb;

			$this->usermeta = $this->wpdb->usermeta;
		}

		/**
		 * Seller edit profile
		 */
		public function mkwc_seller_edit_profile( $profile_data ) {
			$validate_auth_key = $this->mkwc_authenticate_request();

			if ( $validate_auth_key != 'ok' ) {
				return array(
					'session_id'             => $validate_auth_key,
					'status'                 => 401,
					'invalid_auth_key_error' => __( 'Please provide valid Auth Key.', 'mobikul_marketplace' ),
					'success'                => false,
				);
			}
			$seller_data_handler = new Helper\Mkwc_Seller_Data();

			$data = is_object( $profile_data ) ? $profile_data->get_json_params() : $profile_data;

			$stripe_val = array();

				$current_user = isset( $_GET['seller_id'] ) ? (int) $_GET['seller_id'] : '';

			$current_user_check = $this->mkwc_user_exist_request( $current_user );

			if ( isset( $current_user_check['success'] ) && ! $current_user_check['success'] ) {
				return $current_user_check;
			}

			$first_name = isset( $data['first_name'] ) ? sanitize_text_field( wp_unslash( $data['first_name'] ) ) : ''; // Input var okay.

				$last_name = isset( $data['last_name'] ) ? sanitize_text_field( wp_unslash( $data['last_name'] ) ) : ''; // Input var okay.

			$email = isset( $data['email'] ) ? sanitize_email( wp_unslash( $data['email'] ) ) : '';

				$shop_name = isset( $data['shop_name'] ) ? sanitize_text_field( wp_unslash( $data['shop_name'] ) ) : ''; // Input var okay.

				$shop_phone = isset( $data['phone'] ) ? strip_tags( wp_unslash( $data['phone'] ) ) : ''; // Input var okay.

				// new fields.
				$address_1 = isset( $data['address_line1'] ) ? strip_tags( $data['address_line1'] ) : ''; // Input var okay.

				$address_2 = isset( $data['address_line2'] ) ? strip_tags( $data['address_line2'] ) : ''; // Input var okay.

			$city = isset( $data['city'] ) ? strip_tags( $data['city'] ) : ''; // Input var okay.

				$postcode = isset( $data['postcode'] ) ? strip_tags( $data['postcode'] ) : ''; // Input var okay.

				$country = isset( $data['country'] ) ? strip_tags( $data['country'] ) : ''; // Input var okay.

			$state = isset( $data['state'] ) ? strip_tags( $data['state'] ) : '';

			$user_image = isset( $data['user_image'] ) ? $data['user_image'] : '';

			$shop_image = isset( $data['shop_logo'] ) ? $data['shop_logo'] : '';

			$banner_image = isset( $data['banner_image'] ) ? $data['banner_image'] : '';

			$banner_visibility = isset( $data['banner_display'] ) ? strip_tags( $data['banner_display'] ) : '';

			$about_shop = isset( $data['about_shop'] ) ? strip_tags( $data['about_shop'] ) : ''; // Input var okay.

			$fb_url = isset( $data['social']['facebook'] ) ? filter_var( wp_unslash( $data['social']['facebook'] ), FILTER_SANITIZE_URL ) : ''; // Input var okay.

				$twitter_url = isset( $data['social']['twitter'] ) ? filter_var( wp_unslash( $data['social']['twitter'] ), FILTER_SANITIZE_URL ) : ''; // Input var okay.

				$gplus_url = isset( $data['social']['google_plus'] ) ? filter_var( wp_unslash( $data['social']['google_plus'] ), FILTER_SANITIZE_URL ) : ''; // Input var okay.

				$in_url = isset( $data['social']['linkedin'] ) ? filter_var( wp_unslash( $data['social']['linkedin'] ), FILTER_SANITIZE_URL ) : ''; // Input var okay.

				$yt_url = isset( $data['social']['youtube'] ) ? filter_var( wp_unslash( $data['social']['youtube'] ), FILTER_SANITIZE_URL ) : ''; // Input var okay.

			$payment_method = isset( $data['payment_method'] ) ? strip_tags( $data['payment_method'] ) : ''; // Input var okay.

			if ( $first_name ) {
				if ( preg_match( '/^[A-Za-z0-9_-]{1,40}$/', $first_name ) ) {
					update_user_meta( $current_user, 'first_name', $first_name );
				} else {
					return array(
						'success' => false,
						'message' => __( 'First name is not valid.', 'mobikul_marketplace' ),
					);
				}
			} else {
					update_user_meta( $current_user, 'first_name', $first_name );
			}

			if ( $last_name ) {
				if ( preg_match( '/^[A-Za-z0-9_-]{1,40}$/', $last_name ) ) {
					update_user_meta( $current_user, 'last_name', $last_name );
				} else {
					return array(
						'success' => false,
						'message' => __( 'Last name is not valid.', 'mobikul_marketplace' ),
					);
				}
			} else {
					update_user_meta( $current_user, 'last_name', $last_name );
			}

			$userdata = array(
				'ID'         => $current_user,
				'user_email' => $email, // Input var okay.
			);

			if ( $email === false ) { // Input var okay.
				return array(
					'success' => false,
					'message' => __( 'E-mail not valid.', 'mobikul_marketplace' ),
				);
			} elseif ( $userdata['user_email'] ) {
					$c = wp_update_user( $userdata );

				if ( isset( $c->errors ) && $c->errors && isset( $c->errors['existing_user_email'][0] ) ) {
					return array(
						'success' => false,
						'message' => __( $c->errors['existing_user_email'][0], 'mobikul_marketplace' ),
					);
				}
			} else {
				return array(
					'success' => false,
					'message' => __( 'E-mail is required.', 'mobikul_marketplace' ),
				);
			}

			if ( $shop_name ) {
				if ( preg_match( '/^[-A-Za-z0-9_\s]{1,40}$/', $shop_name ) ) {
					 update_user_meta( $current_user, 'shop_name', $shop_name );
				} else {
					return array(
						'success' => false,
						'message' => __( 'Shop name is not valid.', 'mobikul_marketplace' ),
					);
				}
			} else {
				return array(
					'success' => false,
					'message' => __( 'Shop name is required.', 'mobikul_marketplace' ),
				);
			}

			if ( $shop_phone ) {
				if ( strlen( $shop_phone ) > 10 ) {
					return array(
						'success' => false,
						'message' => __( 'Phone number length must not exceed 10.', 'mobikul_marketplace' ),
					);
				} elseif ( preg_match( '/^[0-9]{1,10}$/', $shop_phone ) ) {
					update_user_meta( $current_user, 'billing_phone', $shop_phone );
				} else {
					return array(
						'success' => false,
						'message' => __( 'Entered phone number is not valid.', 'mobikul_marketplace' ),
					);
				}
			} else {
					update_user_meta( $current_user, 'billing_phone', '' );
			}

			if ( $address_1 ) {
				if ( preg_match( '/^[A-Za-z0-9_ -]{1,40}$/', $address_1 ) ) {
					 update_user_meta( $current_user, 'billing_address_1', $address_1 );
				}
			}

			if ( $address_2 ) {
				if ( preg_match( '/^[A-Za-z0-9_ -]{1,40}$/', $address_2 ) ) {
					 update_user_meta( $current_user, 'billing_address_2', $address_2 );
				}
			}

			if ( $city ) {
				if ( preg_match( '/^[A-Za-z0-9_ -]{1,40}$/', $city ) ) {
					 update_user_meta( $current_user, 'billing_city', $city );
				}
			}

			if ( $postcode ) {
				if ( preg_match( '/^[A-Z0-9]{1,10}$/', $postcode ) ) {
					 update_user_meta( $current_user, 'billing_postcode', $postcode );
				}
			}

			if ( $country ) {
						update_user_meta( $current_user, 'billing_country', $country );
			}

			if ( $state ) {
						global $woocommerce;

					  $countries_obj = new \WC_Countries();

					  $countries = $countries_obj->__get( 'countries' );

				$cntry = get_user_meta( $current_user, 'billing_country', true );

				if ( WC()->countries->get_states( $cntry ) ) {
					$states = WC()->countries->get_states( $cntry );

					if ( isset( $states[ $state ] ) ) {
						  update_user_meta( $current_user, 'billing_state', $state );
					} elseif ( in_array( $state, $states, true ) ) {
						$state_code = array_search( $state, $states, true );

						update_user_meta( $current_user, 'billing_state', $state_code );
					}
				} else {
					  update_user_meta( $current_user, 'billing_state', $state );
				}
			}

			if ( $banner_visibility == 'yes' ) {
						update_user_meta( $current_user, 'shop_banner_visibility', 'yes' );
			} else {
					update_user_meta( $current_user, 'shop_banner_visibility', 'no' );
			}

			if ( $about_shop ) {
				update_user_meta( $current_user, 'about_shop', $about_shop );
			}

			if ( $fb_url ) {
				if ( filter_var( $fb_url, FILTER_VALIDATE_URL ) !== false ) {
					update_user_meta( $current_user, 'social_facebook', $fb_url );
				} else {
					return array(
						'success' => false,
						'message' => __( 'Facebook URL not valid.', 'mobikul_marketplace' ),
					);
				}
			} else {
					update_user_meta( $current_user, 'social_facebook', '' );
			}

			if ( $twitter_url ) {
				if ( filter_var( $twitter_url, FILTER_VALIDATE_URL ) !== false ) {
					 update_user_meta( $current_user, 'social_twitter', $twitter_url );
				} else {
					return array(
						'success' => false,
						'message' => __( 'Twitter URL not valid.', 'mobikul_marketplace' ),
					);
				}
			} else {
					update_user_meta( $current_user, 'social_twitter', '' );
			}

			if ( $gplus_url ) {
				if ( filter_var( $gplus_url, FILTER_VALIDATE_URL ) !== false ) {
					update_user_meta( $current_user, 'social_gplus', $gplus_url );
				} else {
					return array(
						'success' => false,
						'message' => __( 'Google Plus URL not valid.', 'mobikul_marketplace' ),
					);
				}
			} else {
				  update_user_meta( $current_user, 'social_gplus', '' );
			}

			if ( $in_url ) {
				if ( filter_var( $in_url, FILTER_VALIDATE_URL ) !== false ) {
					  update_user_meta( $current_user, 'social_linkedin', $in_url );
				} else {
					return array(
						'success' => false,
						'message' => __( 'LinkedIN URL not valid.', 'mobikul_marketplace' ),
					);
				}
			} else {
				  update_user_meta( $current_user, 'social_linkedin', '' );
			}

			if ( $yt_url ) {
				if ( filter_var( $yt_url, FILTER_VALIDATE_URL ) !== false ) {
					   update_user_meta( $current_user, 'social_youtube', $yt_url );
				} else {
					return array(
						'success' => false,
						'message' => __( 'Youtube URL not valid.', 'mobikul_marketplace' ),
					);
				}
			} else {
					update_user_meta( $current_user, 'social_youtube', '' );
			}

			if ( ! empty( $payment_method ) ) {
				$stripe_val['standard'] = strip_tags( $payment_method ); // Input var okay.
				update_user_meta( $current_user, 'mp_seller_payment_method', $stripe_val );
			} else {
				update_user_meta( $current_user, 'mp_seller_payment_method', '' );
			}
			if ( $user_image ) {
				$avtar_type = 'avatar';

				$seller_data_handler->mkwc_update_seller_media( $current_user, $avtar_type, $user_image );
			}
			if ( $shop_image ) {
				$avtar_type = 'company_logo';

				$seller_data_handler->mkwc_update_seller_media( $current_user, $avtar_type, $shop_image );
			}if ( $banner_image ) {
				$avtar_type = 'shop_banner';

				$seller_data_handler->mkwc_update_seller_media( $current_user, $avtar_type, $banner_image );
			}
			return array(
				'success' => true,
				'message' => __( 'Profile updated successfully.', 'mobikul_marketplace' ),
			);
		}


		/**
		 * Seller get edit profile
		 */
		public function mkwc_seller_get_edit_profile() {
			$validate_auth_key = $this->mkwc_authenticate_request();

			if ( $validate_auth_key != 'ok' ) {
				return array(
					'session_id'             => $validate_auth_key,
					'status'                 => 401,
					'invalid_auth_key_error' => __( 'Please provide valid Auth Key.', 'mobikul_marketplace' ),
					'success'                => false,
				);
			}
			$mp_form_handler = new Mkwc_Form_Handler();

			$seller_data_handler = new Helper\Mkwc_Seller_Data();

			$seller_id = isset( $_GET['seller_id'] ) ? $_GET['seller_id'] : '';

			$seller_check = $this->mkwc_user_exist_request( $seller_id );

			if ( isset( $seller_check['success'] ) && ! $seller_check['success'] ) {
				return $seller_check;
			}

			if ( empty( $seller_id ) ) {
				return array(
					'success' => false,
					'message' => __( 'Sorry, you must provide all parameters.', 'mobikul_marketplace' ),
				);
			}

			$current_user = get_user_by( 'ID', $seller_id );

			if ( $current_user->ID ) {

				$avatar = $mp_form_handler->get_user_avatar( $current_user->ID, 'avatar' );

				$shop_banner = $mp_form_handler->get_user_avatar( $current_user->ID, 'shop_banner' );

				$com_logo = $mp_form_handler->get_user_avatar( $current_user->ID, 'company_logo' );

				$usermeta_row_data = $seller_data_handler->mkwc_get_details( $current_user->ID );

				$user_meta_arr = array();

				foreach ( $usermeta_row_data as $key => $value ) {
					$user_meta_arr[ $value->meta_key ] = $value->meta_value;
				}
			}

			$username = isset( $current_user->user_login ) ? $current_user->user_login : '';

			$first_name = isset( $user_meta_arr['first_name'] ) ? $user_meta_arr['first_name'] : '';

			$last_name = isset( $user_meta_arr['last_name'] ) ? $user_meta_arr['last_name'] : '';

			$email = isset( $current_user->user_email ) ? $current_user->user_email : '';

			$shop_name = isset( $user_meta_arr['shop_name'] ) ? $user_meta_arr['shop_name'] : '';

			$shop_url = isset( $user_meta_arr['shop_address'] ) ? $user_meta_arr['shop_address'] : '';

			$shop_phone = isset( $user_meta_arr['billing_phone'] ) ? $user_meta_arr['billing_phone'] : '';

			$address_1 = isset( $user_meta_arr['billing_address_1'] ) ? $user_meta_arr['billing_address_1'] : '';

			$address_2 = isset( $user_meta_arr['billing_address_2'] ) ? $user_meta_arr['billing_address_2'] : '';

			$city = isset( $user_meta_arr['billing_city'] ) ? $user_meta_arr['billing_city'] : '';

			$postcode = isset( $user_meta_arr['billing_postcode'] ) ? $user_meta_arr['billing_postcode'] : '';

			$country = isset( $user_meta_arr['billing_country'] ) ? $user_meta_arr['billing_country'] : '';

			$state = isset( $user_meta_arr['billing_state'] ) ? $user_meta_arr['billing_state'] : '';

			$user_image = isset( $avatar[0]->meta_value ) ? wp_upload_dir()['baseurl'] . '/' . $avatar[0]->meta_value : '';

			$shop_image = isset( $com_logo[0]->meta_value ) ? wp_upload_dir()['baseurl'] . '/' . $com_logo[0]->meta_value : '';

			$banner_display = isset( $user_meta_arr['shop_banner_visibility'] ) ? $user_meta_arr['shop_banner_visibility'] : '';

			$banner_image = isset( $shop_banner[0]->meta_value ) ? wp_upload_dir()['baseurl'] . '/' . $shop_banner[0]->meta_value : '';

			$about_shop = isset( $user_meta_arr['about_shop'] ) ? $user_meta_arr['about_shop'] : '';

			$facebook = isset( $user_meta_arr['social_facebook'] ) ? $user_meta_arr['social_facebook'] : '';

			$twitter_url = isset( $user_meta_arr['social_twitter'] ) ? $user_meta_arr['social_twitter'] : '';

			$google_plus = isset( $user_meta_arr['social_gplus'] ) ? $user_meta_arr['social_gplus'] : '';

			$linkedin_url = isset( $user_meta_arr['social_linkedin'] ) ? $user_meta_arr['social_linkedin'] : '';

			$youtube = isset( $user_meta_arr['social_youtube'] ) ? $user_meta_arr['social_youtube'] : '';

			$stripe_unserialize_data = maybe_unserialize( $user_meta_arr['mp_seller_payment_method'] );

			if ( isset( $stripe_unserialize_data['standard'] ) && $stripe_unserialize_data['standard'] == 'hahah' ) {
				$stripe_unserialize_data['standard'] = '';
			}

			$payment_method = isset( $stripe_unserialize_data['standard'] ) ? $stripe_unserialize_data['standard'] : '';

			return array(
				'username'       => $username,
				'first_name'     => $first_name,
				'last_name'      => $last_name,
				'email'          => $email,
				'shop_name'      => $shop_name,
				'shop_url'       => $shop_url,
				'phone'          => $shop_phone,
				'address_line1'  => $address_1,
				'address_line2'  => $address_2,
				'city'           => $city,
				'postcode'       => $postcode,
				'country'        => $country,
				'state'          => $state,
				'user_image'     => $user_image,
				'shop_logo'      => $shop_image,
				'banner_display' => $banner_display,
				'banner_image'   => $banner_image,
				'about_shop'     => $about_shop,
				'social'         => array(
					'facebook'    => $facebook,
					'twitter'     => $twitter_url,
					'google_plus' => $google_plus,
					'linkedin'    => $linkedin_url,
					'youtube'     => $youtube,
				),
				'payment_method' => $payment_method,
				'success'        => true,
				'message'        => '',
			);
		}
	}
}
