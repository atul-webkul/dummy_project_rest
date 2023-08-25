<?php

/**
 * @author Webkul
 * @version 1.0.0
 * This file handles homepage functions.
 */

namespace WkMobikulMultivendorApi\Includes\Front\Homepage;

use WkMobikulMultivendorApi\Includes\Front\Homepage\Util;
use WkMobikulMultivendorApi\Includes;
use WkMobikulMultivendorApi\Factory;
use WkMobikulMultivendorApi\Helper;

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'Mkwc_Homepage' ) ) {
	/**
	 * Homepage API class
	 */
	class Mkwc_Homepage extends Includes\Mkwc_Authentication implements Util\Mkwc_Homepage_Interface {


		public static $base = 'homepage';

		protected $response;

		protected $product_store_handler;

		protected $cart_handler;

		/**
		 * App homepage data
		 */
		function mkwc_get_homepage() {
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

			$customer_check = $this->mkwc_user_exist_request( $customer_id );

			if ( isset( $customer_check['success'] ) && ! $customer_check['success'] ) {
				return $customer_check;
			}

			$width = isset( $_GET['width'] ) ? $_GET['width'] : '';

			if ( ! $width ) {
				return array(
					'success' => false,
					'message' => __( 'Sorry, you must provide all parameters.', 'mobikul_marketplace' ),
				);
			}
			$response_handler = new \WP_REST_Response();

			$data = array(
				'homepage_products' => array(
					array(
						'title'    => __( 'Featured Products', 'mobikul_marketplace' ),
						'products' => $this->mkwc_get_featured_products( $width ),
					),
					array(
						'title'    => __( 'New Products', 'mobikul_marketplace' ),
						'products' => $this->mkwc_get_new_products( $width ),
					),
					array(
						'title'    => __( 'Sale Products', 'mobikul_marketplace' ),
						'name'     => 'sale_products',
						'products' => $this->mkwc_get_sale_products( $width ),
					),
				),
				'banners'           => $this->mkwc_get_banners( $width ),
				'featured_category' => $this->mkwc_get_featured_categories( $width ),
				'categories'        => $this->mkwc_get_categories(),
				'settings'          => $this->mkwc_default_settings(),
				'count'             => $this->mkwc_cart_count(),
			);

			$data = $this->mkwc_get_carousel_products( $data, $width );

			$response_handler->set_data( $data );

			return $response_handler;
		}

		/**
		 * Get featured category data
		 *
		 * @param $width
		 * @return $featured_categories
		 */
		public function mkwc_get_featured_categories( $width ) {
			$product_store_handler = new Helper\Mkwc_Product_store();
			$data                  = array();

			$featured_categories = $product_store_handler->mkwc_return_featured_category_data();

			if ( $featured_categories ) {
				$data = $product_store_handler->mkwc_prepare_featured_categories( $featured_categories, $width );
			} else {
				$data = array();
			}

			return $data;
		}

		/**
		 * Get store categories
		 *
		 * @return $categories
		 */
		public function mkwc_get_categories() {
			$general_data_handler = new Helper\Mkwc_General_Data();
			$prepared_data        = array();

			$cat_list = get_terms(
				'product_cat',
				array(
					'hide_empty' => false,
					'parent'     => 0,
				)
			);

			if ( $cat_list ) {
				foreach ( $cat_list as $key => $cat ) {
					$children  = count( get_term_children( (int) $cat->term_id, 'product_cat' ) );
					$icon_id   = get_term_meta( $cat->term_id, 'product_cat_icon_id', true );
					$icon_url  = wp_get_attachment_image_url( $icon_id, 'full' );
					$post_data = array(
						'ID'            => $cat->term_id,
						'name'          => html_entity_decode( get_term_by( 'id', (int) $cat->term_id, 'product_cat' )->name ),
						'icon'          => $icon_url ? $icon_url : '',
						'dominantColor' => isset( $icon_url ) ? $general_data_handler->mkwc_get_dominant_Color( $icon_url ) : $general_data_handler->mkwc_get_dominant_Color( '' ),
						'children'      => $children,
					);

					$prepared_data[] = $post_data;
				}
			}

			return $prepared_data;
		}

		/**
		 * Get homepage banners
		 *
		 * @param $width
		 * @return $banners
		 */
		public function mkwc_get_banners( $width ) {
			$product_store_handler = new Helper\Mkwc_Product_store();

			$data = array();

			$banners = $product_store_handler->mkwc_return_homepage_banners();

			if ( $banners ) {
				$data = $product_store_handler->mkwc_prepare_homepage_banner_data( $banners, $width );
			} else {
				$data = array();
			}

			return $data;
		}

		/**
		 * Get homepage carousels
		 *
		 * @param $width
		 * @return $carousels
		 */
		public function mkwc_get_carousel_products( $data, $width ) {
			$product_store_handler = new Helper\Mkwc_Product_store();

			$pro_products = $cat_products = $result = $merge_data = $merge_pro_data = array();
			$per_page     = get_option( 'posts_per_page' );
			$page         = isset( $_GET['page'] ) ? intval( $_GET['page'] ) : '';
			$carousels    = $product_store_handler->mkwc_return_homepage_carousels();
			if ( is_array( $carousels ) ) {
				foreach ( $carousels as $ckey => $cvalue ) {
					$product_ids = $cvalue->carousel_ids ? maybe_unserialize( $cvalue->carousel_ids ) : array();
					if ( $cvalue->type === 'products' ) {
						if ( is_array( $product_ids ) ) {
							foreach ( $product_ids as $pkey => $pvalue ) {
								$pro_products[] = wc_get_product( $pvalue );
							}
						}
						if ( $pro_products ) {
							$merge_pro_data[] = array(
								'title'    => $cvalue->title,
								'name'     => str_replace( ' ', '_', $cvalue->title ),
								'products' => $product_store_handler->mkwc_prepare_homepage_featured_products( $pro_products, $width ),
							);
						}
					} elseif ( $cvalue->type === 'categories' ) {
						if ( is_array( $product_ids ) ) {
							foreach ( $product_ids as $pkey => $pvalue ) {
								$category       = get_term( $pvalue );
								$cat_slug       = $category->slug;
								$product_wrap   = Factory\Mkwc_Product_Factory::mkwc_query_products( $page, $per_page, array( 'product_cat' => $cat_slug ) );
								$product_data[] = $product_wrap->posts ? $product_wrap->posts : '';
							}
							if ( is_array( $product_data ) ) {
								foreach ( $product_data as $pdkey => $pd_value ) {
									foreach ( $pd_value as $pd_key => $pd_id ) {
										$cat_products[] = wc_get_product( $pd_id );
									}
								}
							}
							if ( $cat_products ) {
								$merge_data[] = array(
									'title'    => $cvalue->title,
									'name'     => str_replace( ' ', '_', $cvalue->title ),
									'products' => $product_store_handler->mkwc_prepare_homepage_featured_products( $cat_products, $width ),
								);
							}
						}
					}
				}
			}
			if ( ! empty( $merge_pro_data ) ) {
				$homepage                  = isset( $data['homepage_products'] ) ? $data['homepage_products'] : array();
				$data['homepage_products'] = array_merge( $homepage, $merge_pro_data );
			}
			if ( ! empty( $merge_data ) ) {
				$homepage                  = isset( $data['homepage_products'] ) ? $data['homepage_products'] : array();
				$data['homepage_products'] = array_merge( $homepage, $merge_data );
			}

			return $data;
		}

		/**
		 * Get new products
		 *
		 * @param $width
		 * @return array $new_products
		 */
		public function mkwc_get_new_products( $width ) {
			$product_store_handler = new Helper\Mkwc_Product_store();

			$data = array();

			$new_products = $product_store_handler->mkwc_return_homepage_product_data( 'new' );

			if ( $new_products ) {
				$data = $product_store_handler->mkwc_prepare_homepage_products( $new_products, $width, 'new' );
			} else {
				$data = array();
			}

			return $data;
		}

		/**
		 * Get sale products
		 *
		 * @param $width
		 * @return array $sale_products
		 */
		public function mkwc_get_sale_products( $width ) {
			$product_store_handler = new Helper\Mkwc_Product_store();

			$data = array();

			$sale_products = $product_store_handler->mkwc_return_sale_product_data();

			if ( $sale_products ) {
				$data = $product_store_handler->mkwc_prepare_homepage_featured_products( $sale_products, $width );
			} else {
				$data = array();
			}

			return $data;
		}

		/**
		 * Get featured products
		 *
		 * @param $width
		 * @return array $featured_products
		 */
		public function mkwc_get_featured_products( $width ) {
			$product_store_handler = new Helper\Mkwc_Product_store();

			$data = array();

			$featured_products = $product_store_handler->mkwc_return_featured_product_data();

			if ( $featured_products ) {
				$data = $product_store_handler->mkwc_prepare_homepage_featured_products( $featured_products, $width );
			} else {
				$data = array();
			}

			return $data;
		}

		/**
		 * WooCommerce default settings
		 */
		public function mkwc_default_settings() {
			$woocommerce_enable_myaccount_registration  = get_option( 'woocommerce_enable_myaccount_registration', true );
			$woocommerce_registration_generate_username = get_option( 'woocommerce_registration_generate_username', true );
			$woocommerce_registration_generate_password = get_option( 'woocommerce_registration_generate_password', true );
			$woocommerce_enable_guest_checkout          = get_option( 'woocommerce_enable_guest_checkout', true );

			$response = array(
				'woocommerce_enable_myaccount_registration' => $woocommerce_enable_myaccount_registration,
				'woocommerce_registration_generate_username' => $woocommerce_registration_generate_username,
				'woocommerce_registration_generate_password' => $woocommerce_registration_generate_password,
				'woocommerce_enable_guest_checkout' => $woocommerce_enable_guest_checkout,
			);

			return $response;
		}

		/**
		 * Get user cart count
		 */

		public function mkwc_cart_count() {
			 $customer_id = isset( $_GET['customer_id'] ) ? $_GET['customer_id'] : '';

			 $guest_id = isset( $_GET['guest_id'] ) ? $_GET['guest_id'] : '';

			 $id = ! empty( $customer_id ) ? $customer_id : $guest_id;

			 $count = '';
			if ( ! empty( $id ) ) {
				$cartdata_handler = new Helper\Mkwc_Cart_Data();

				$cart_items = $cartdata_handler->mkwc_registered_cart_items( $id );

				if ( ! empty( $cart_items['cart'] ) ) {
					$product_count = count( $cart_items['cart'] );

					foreach ( $cart_items['cart'] as $ckey => $cvalue ) {
						$product_count += $cvalue['quantity'] - 1;
					}
					$count = $product_count;
				} else {
					$count = 0;
				}
			}
			 return $count;
		}
	}
}
