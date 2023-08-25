<?php

/**
 * @author Webkul
 * @version 1.0.0
 * This file handles seller shop followers functions.
 */

namespace WkMobikulMultivendorApi\Includes\Front\Seller;

use WkMobikulMultivendorApi\Includes;
use WkMobikulMultivendorApi\Includes\Front\Seller\Util;
use WkMobikulMultivendorApi\Helper;

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'Mkwc_Seller_Shop_Details' ) ) {
	/**
	 *
	 */
	class Mkwc_Seller_Shop_Details extends Includes\Mkwc_Authentication implements Util\Mkwc_Seller_Shop_Details_Interface {

		public static $base = 'seller/shop';

		protected $seller_data_handler;

		protected $mp_form_handler;

		protected $product_store_handler;

		/**
		 * Seller shop details
		 */
		public function mkwc_get_seller_shop_details() {
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
			$mp_form_handler     = new Mkwc_Form_Handler();
			$seller_id           = isset( $_GET['seller_id'] ) ? $_GET['seller_id'] : '';
			$width               = isset( $_GET['width'] ) ? $_GET['width'] : '';
			$seller_check        = $this->mkwc_user_exist_request( $seller_id );

			if ( isset( $seller_check['success'] ) && ! $seller_check['success'] ) {
				return $seller_check;
			}

			if ( empty( $seller_id ) || empty( $width ) ) {
				return array(
					'success' => false,
					'message' => __( 'Sorry, you must provide all parameters.', 'mobikul_marketplace' ),
				);
			}

			$is_seller = $seller_data_handler->mkwc_check_seller_by_id( $seller_id );

			if ( ! $is_seller ) {
				return array(
					'success' => false,
					'message' => __( 'Invalid seller ID.', 'mobikul_marketplace' ),
				);
			}

			$seller_data = $this->mkwc_get_seller_details( $seller_id );
			$seller_name = isset( $seller_data['first_name'] ) ? $seller_data['first_name'] . ' ' . $seller_data['last_name'] : $seller_data['nickname'];

			$banner       = $mp_form_handler->get_user_avatar( $seller_id, 'shop_banner' );
			$banner_image = isset( $banner['0']->meta_value ) ? $banner['0']->meta_value : '';
			$banner_image = ! empty( $banner_image ) ? wp_upload_dir()['baseurl'] . '/' . $banner_image : '';
			$shop_logo    = $mp_form_handler->get_user_avatar( $seller_id, 'company_logo' );
			$shop_logo    = isset( $shop_logo['0']->meta_value ) ? $shop_logo['0']->meta_value : '';
			$shop_logo    = ! empty( $shop_logo ) ? wp_upload_dir()['baseurl'] . '/' . $shop_logo : '';
			$review_data  = $this->get_review( $seller_id );
			$num_of_stars = $total_feedback = $price_stars = $value_stars = $quality_stars = 0;

			if ( $review_data ) {
			foreach ( $review_data as $item ) {
			$num_of_stars  += $item->price_r;
			$price_stars   += $item->price_r;
			$num_of_stars  += $item->value_r;
			$value_stars   += $item->value_r;
			$num_of_stars  += $item->quality_r;
			$quality_stars += $item->quality_r;
			$total_feedback++;
			}
			}

			if ( $num_of_stars != 0 ) {
			$quality = $num_of_stars / ( $total_feedback * 3 );

			$price_stars /= $total_feedback;

			$value_stars /= $total_feedback;

			$quality_stars /= $total_feedback;
			} else {
			$quality = 0;
			}

			$average_rating = array(
			'average'     => number_format( $quality, 2 ),
			'price'       => number_format( $price_stars, 2 ),
			'value'       => number_format( $value_stars, 2 ),
			'quality'     => number_format( $quality_stars, 2 ),
			'total_count' => esc_attr( $total_feedback ),
			);

			$seller_user_data = get_user_by( 'ID', $seller_id );

			$seller_email = isset( $seller_user_data->user_email ) ? $seller_user_data->user_email : '';

			$seller_phone = isset( $seller_data['billing_phone'] ) ? $seller_data['billing_phone'] : '';

			$seller_location = '';

			if ( get_option( 'wkmp_show_seller_address' ) == 'yes' ) :
			if ( isset( $seller_data['billing_country'] ) && $seller_data['billing_country'] ) :
			$seller_location = html_entity_decode( WC()->countries->countries[ $seller_data['billing_country'] ] );
			endif;
			endif;

			$seller_social = array();

			if ( get_option( 'wkmp_show_seller_social_links' ) == 'yes' ) :
			if ( isset( $seller_data['social_facebook'] ) && $seller_data['social_facebook'] ) {
			$seller_social['facebook'] = $seller_data['social_facebook'];
			}

			if ( isset( $seller_data['social_twitter'] ) && $seller_data['social_twitter'] ) {
			$seller_social['twitter'] = $seller_data['social_twitter'];
			}

			if ( isset( $seller_data['social_gplus'] ) && $seller_data['social_gplus'] ) {
			$seller_social['google_plus'] = $seller_data['social_gplus'];
			}

			if ( isset( $seller_data['social_linkedin'] ) && $seller_data['social_linkedin'] ) {
			$seller_social['linkedin'] = $seller_data['social_linkedin'];
			}

			if ( isset( $seller_data['social_youtube'] ) && $seller_data['social_youtube'] ) {
			$seller_social['youtube'] = $seller_data['social_youtube'];
			}
			endif;

			$seller_about = isset( $seller_data['about_shop'] ) ? esc_attr( $seller_data['about_shop'] ) : 'N/A';

			 $seller_recent_product = $this->mkwc_get_seller_recent_product( $seller_id, $width );

			$recent_reviews = array();

			$review_data = apply_filters( 'mkt_filter_seller_reviews', $review_data );

			foreach ( $review_data as $key => $value ) {
			$review_author = get_user_by( 'ID', $value->user_id );

			if ( $review_author ) {
			$display_name = $review_author->display_name;
			} else {
			$display_name = '-';
			}

			$recent_reviews[] = array(
			'title'       => $value->review_summary,
			'author'      => $display_name,
			'date'        => date( 'd M Y', strtotime( $value->review_time ) ),
			'description' => $value->review_desc,
			'review'      => array(
			'price'   => number_format( $value->price_r, 2 ),
			'value'   => number_format( $value->value_r, 2 ),
			'quality' => number_format( $value->quality_r, 2 ),
			),
			);
			}

			return apply_filters(
			'mkwc_get_seller_shop_custom_details',
			array(
			'seller_name'     => $seller_name,
			'banner_image'    => $banner_image,
			'shop_logo'       => $shop_logo,
			'average_rating'  => $average_rating,
			'email'           => $seller_email,
			'phone'           => $seller_phone,
			'location'        => $seller_location,
			'social'          => $seller_social,
			'recent_products' => $seller_recent_product,
			'about'           => $seller_about,
			'recent_reviews'  => $recent_reviews,
			'success'         => true,
			'message'         => '',
			)
			);

			return $this->wkmp_displayForm( $seller_id, $seller_recent_product );
		}

		function wkmp_displayForm( $user_id, $products ) {

			global $wpdb;

			$wpmp_obj10 = new \MP_Form_Handler();
			$wpmp_obj10->profile_edit_redirection();
			wp_set_current_user( $user_id );
			$currentUser         = wp_get_current_user();
			$current_lavel       = $current_user->membership_level->id;
			$current_lavel       = ! empty( $current_lavel ) ? $current_lavel : 0;
			$defaultRoomPhotoUrl = WK_MARKETPLACE . 'assets/images/default_room.jpg';
			$bannerImageUrl      = get_user_meta( $currentUser->ID, '_thumbnail_id_shop_banner', true );
			if ( ! empty( $bannerImageUrl ) ) {
				$bannerImageUrl = wp_get_attachment_image_url( $bannerImageUrl, 'full' );
			}
			$businessLogo = get_user_meta( $currentUser->ID, '_thumbnail_id_company_logo', true );
			if ( ! empty( $businessLogo ) ) {
				$businessLogo = wp_get_attachment_image_url( $businessLogo, 'full' );
			}
			if ( empty( $bannerImageUrl ) ) {
				$bannerImageUrl = $defaultRoomPhotoUrl;
			}
			if ( empty( $businessLogo ) ) {
				$businessLogo = WK_MARKETPLACE . '/assets/images/shop-logo.png';
			}

			$selleravatar = $wpmp_obj10->get_user_avatar( $currentUser->ID, 'avatar' );
			$sell_data    = $wpmp_obj10->spreview( $currentUser->ID );
			$seller_all   = array();
			if ( is_array( $sell_data ) ) {
				foreach ( $sell_data as $key => $value ) {
					$seller_all[ $value->meta_key ] = $value->meta_value;
				}
			} else {
				$seller_all[] = '';
			}

			$reviews = array_filter(
				array_map(
					function( $review ) {
						if ( ! $review ) {
							return null;
						}
						$numStars = $review->price_r +
						$review->value_r +
						$review->quality_r;

						return array(
							'id'       => 0,
							'reviewer' => array(
								'name' => $review->nickname,
							),
							'score'    => $numStars == 0 ? 0 : $numStars / 5,
							'date'     => $review->review_time,
							'comment'  => $review->review_desc,
						);
					},
					$wpmp_obj10->original_review( $user_id ) ?? array()
				)
			);

			$ownerType   = in_array( 'serviceproviders', $userRoles ) ? 'service-provider' : ( in_array( 'architect', $userRoles ) ? 'architect' : 'seller' );
			$profileData = array(
				'phone_number' => $currentUser->phone_number,
				'user_id'      => $currentUser->ID,
				'seller_name'  => $currentUser->user_login,
				'banner_image' => $bannerImageUrl,
				'shop_logo'    => $businessLogo,
				'roll'         => $userRoles,
				'email'        => $currentUser->user_email,
				'page_title'   => 'Profile',
				'type'         => $ownerType,
				'socials'      => array(
					'facebook' => isset( $seller_all['social_facebook'] ) ? $seller_all['social_facebook'] : '',
					'twitter'  => isset( $seller_all['social_twitter'] ) ? $seller_all['social_twitter'] : '',
					'linkedin' => isset( $seller_all['social_linkedin'] ) ? $seller_all['social_linkedin'] : '',
					'youtube'  => isset( $seller_all['social_youtube'] ) ? $seller_all['social_youtube'] : '',
				),
				'shop_name'    => isset( $seller_all['shop_name'] ) ? $seller_all['shop_name'] : '',
				'member_since' => $currentUser->user_registered,
				'profile'      => isset( $selleravatar[0]->meta_value ) ?
						content_url() . '/uploads/' . $selleravatar[0]->meta_value :
						WK_MARKETPLACE . 'assets/images/genric-male.png',
				'address'      => isset( $seller_all['wk_user_address'] ) ? $seller_all['wk_user_address'] : 'N/A',
				'ratings'      => array(
					'average_rating' => count( $reviews ) > 0 ? array_sum(
						array_column( $reviews, 'score' )
					) / count( $reviews ) : 0,
					'reviews'        => $reviews,
				),
				'products'     => $products,
				'success'      => true,
				'message'      => '',
			);

			return $profileData;
		}


		/**
		 * Get review.
		 *
		 * @param int $id id.
		 */
		public function get_review( $id ) {
			global $wpdb;

			return $wpdb->get_results( $wpdb->prepare( "SELECT * from {$wpdb->prefix}mpfeedback where seller_id = %d  order by review_time desc limit 3", $id,  ) );
		}

		/**
		 * Seller Info
		 */
		public function mkwc_get_seller_details( $seller_id ) {
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

			$seller_all = array();

			$seller_check = $this->mkwc_user_exist_request( $seller_id );

			if ( isset( $seller_check['success'] ) && ! $seller_check['success'] ) {
				return $seller_check;
			}

			$sell_data = $mp_form_handler->spreview( $seller_id );

			$seller_product = $mp_form_handler->seller_product( $seller_id );

			$lenghtProduct = count( $seller_product );

			if ( is_array( $sell_data ) ) {
				foreach ( $sell_data as $key => $value ) {
					$seller_all[ $value->meta_key ] = $value->meta_value;
				}
			}

			return $seller_all;
		}

		/**
		 * Seller Recent Product
		 */
		public function mkwc_get_seller_recent_product( $sellerid, $width ) {
			$validate_auth_key = $this->mkwc_authenticate_request();

			if ( $validate_auth_key != 'ok' ) {
				return array(
					'session_id'             => $validate_auth_key,
					'status'                 => 401,
					'invalid_auth_key_error' => __( 'Please provide valid Auth Key.', 'mobikul_marketplace' ),
					'success'                => false,
				);
			}
			$product_store_handler = new Helper\Mkwc_Product_store();

			$data = array();

			$seller_check = $this->mkwc_user_exist_request( $sellerid );

			if ( isset( $seller_check['success'] ) && ! $seller_check['success'] ) {
				return $seller_check;
			}

			$seller_products = $product_store_handler->mkwc_return_seller_product_data( $sellerid );

			if ( $seller_products ) {
				$data = $product_store_handler->mkwc_prepare_homepage_products( $seller_products, $width, 'new' );
			} else {
				$data = array();
			}

			return $data;
		}
	}
}
