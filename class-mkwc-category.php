<?php

/**
 * @author Webkul
 * @version 1.0.0
 * This file handles category api functions.
 */

namespace WkMobikulMultivendorApi\Includes\Front\Category;

use WkMobikulMultivendorApi\Includes;
use WkMobikulMultivendorApi\Includes\Front\Category\Util;
use WkMobikulMultivendorApi\Helper;
use WkMobikulMultivendorApi\Factory;

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'Mkwc_Category' ) ) {
	/**
	 * Category main class
	 */
	class Mkwc_Category extends Includes\Mkwc_Authentication implements Util\Mkwc_Category_Interface {

		public static $base = 'category';

		protected $response;

		protected $category_handler;

		/**
		 * Get category details by category_id
		 *
		 * @param $data
		 * @return $category_detail
		 */
		public function mkwc_get_category( $data ) {
			$validate_auth_key = $this->mkwc_authenticate_request( 0 );

			if ( $validate_auth_key != 'ok' ) {
				return array(
					'session_id'             => $validate_auth_key,
					'status'                 => 401,
					'invalid_auth_key_error' => __( 'Please provide valid Auth Key.', 'mobikul_marketplace' ),
					'success'                => false,
				);
			}
			$category_handler = new Helper\Mkwc_Category_Data();

			$response_handler = new \WP_REST_Response();

			$category_data = array();

			$category_id = ( is_object( $data ) ) ? (int) $data->get_param( 'category_id' ) : $data;

			$category = get_term( $category_id );

			if ( ! $category ) {
				return array(
					'success' => 0,
					'message' => __( 'Invalid category id.', 'mobikul_marketplace' ),
				);
			}

			$category_data = $category_handler->mkwc_prepare_category( $category );

			if ( $category_data ) {
				$category_data = array_merge(
					$category_data,
					array(
						'success' => 1,
						'message' => '',
					)
				);
			} else {
				$category_data = array_merge(
					$category_data,
					array(
						'success' => 0,
						'message' => __( 'No data found.', 'mobikul_marketplace' ),
					)
				);
			}

			$category_data = apply_filters( 'mkwc_category_content', $category_data );

			$response_handler->set_data( $category_data );

			return $response_handler;
		}

		/**
		 * Get products by category id
		 *
		 * @param $data
		 * @return $category_products
		 */
		public function mkwc_get_category_products( $data ) {
			$validate_auth_key = $this->mkwc_authenticate_request( 0 );

			if ( $validate_auth_key != 'ok' ) {
				return array(
					'session_id'             => $validate_auth_key,
					'status'                 => 401,
					'invalid_auth_key_error' => __( 'Please provide valid Auth Key.', 'mobikul_marketplace' ),
					'success'                => false,
				);
			}
			$response_handler  = new \WP_REST_Response();
			$products          = array();
			$category_products = array();
			$category_id       = ( is_object( $data ) ) ? (int) $data->get_param( 'category_id' ) : $data;
			$page              = isset( $_GET['page'] ) ? intval( $_GET['page'] ) : '';
			$width             = isset( $_GET['width'] ) ? intval( $_GET['width'] ) : '';
			$per_page          = get_option( 'posts_per_page' );
			if ( ! $page ) {
				return array(
					'success' => 0,
					'message' => __( 'Sorry, you must provide all parameters.', 'mobikul_marketplace' ),
				);
			}

			$category = get_term( $category_id );

			if ( ! $category ) {
				return array(
					'success' => 0,
					'message' => __( 'Invalid category id.', 'mobikul_marketplace' ),
				);
			}

			$mFactor = isset( $_GET['mFactor'] ) ? $_GET['mFactor'] : 1;
			$height  = 0.5 * $width;
			$height *= $mFactor;
			$width  *= $mFactor;

			$size = array(
				'width'  => floatval( $width ),
				'height' => floatval( $height ),
				'crop'   => 1,
			);

			$cat_slug = $category->slug;

			$product_store        = new Helper\Mkwc_Product_store();
			$general_data_handler = new Helper\Mkwc_General_Data();
			$sort_label           = Factory\Mkwc_Product_Factory::mkwc_products_sort_label();
			$product_ids          = Factory\Mkwc_Product_Factory::mkwc_query_products( $page, $per_page, array( 'product_cat' => $cat_slug ), $data );
			$image_ids            = $banner = array();
			$image_ids            = get_term_meta( $category_id, 'product_cat_images_id', true );
			if ( ! empty( $image_ids ) ) {
				$image_ids = maybe_unserialize( $image_ids );
				$image_ids = explode( ',', $image_ids );
				foreach ( $image_ids as $bakey => $bavalue ) {
					$image_meta                        = wp_get_attachment_metadata( $bavalue );
					$attachment                        = $product_store->mkwc_image_downsize( $image_meta, $size, $bavalue );
					$banner[ $bakey ]['src']           = isset( $attachment[0] ) ? $attachment[0] : '';
					$banner[ $bakey ]['dominantColor'] = isset( $attachment[0] ) ? $general_data_handler->mkwc_get_dominant_Color( $attachment[0] ) : $general_data_handler->mkwc_get_dominant_Color( '' );
				}
			}

			$args = array(
				'child_of'   => $category_id,
				'taxonomy'   => 'product_cat',
				'hide_empty' => false,
			);

			$sub_categories = get_categories( $args );

			if ( ! empty( $sub_categories ) ) {
				foreach ( $sub_categories as $subkey => $subvalue ) {
					$children       = count( get_term_children( (int) $subvalue->term_id, 'product_cat' ) );
					$icon_id        = get_term_meta( (int) $subvalue->term_id, 'product_cat_icon_id', true );
					$icon_url       = wp_get_attachment_image_url( $icon_id, 'thumbnail' );
					$child_fields[] = array(
						'id'            => $subvalue->term_id,
						'name'          => $subvalue->name,
						'child'         => $children,
						'icon'          => $icon_url ? $icon_url : '',
						'dominantColor' => isset( $icon_url ) ? $general_data_handler->mkwc_get_dominant_Color( $icon_url ) : $general_data_handler->mkwc_get_dominant_Color( '' ),
					);
				}
			}
			if ( $product_ids ) {
				if ( $product_ids->posts ) {
					foreach ( $product_ids->posts as $key => $product_id ) {
						$products[] = Factory\Mkwc_Product_Factory::mkwc_get_prepared_product( $product_id, $width );
					}
				}

				if ( $products ) {
					$category_products = array_merge(
						$category_products,
						array(
							'total'          => intval( $product_ids->found_posts ),
							'data_displayed' => count( $products ),
							'products'       => $products,
							'banner'         => $banner,
							'sort_label'     => $sort_label,
							'children'       => $child_fields,
						)
					);
				} else {
					$category_products = array();
				}
			}

			if ( $category_products ) {
				$category_products = array_merge(
					$category_products,
					array(
						'success' => 1,
						'message' => '',
					)
				);
			} else {
				$category_products = array_merge(
					$category_products,
					array(
						'success' => 0,
						'message' => __( 'No data found.', 'mobikul_marketplace' ),
					)
				);
			}

			$category_products = apply_filters( 'mkwc_category_products_content', $category_products );

			$response_handler->set_data( $category_products );

			return $response_handler;
		}
	}
}
