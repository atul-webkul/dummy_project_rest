<?php

/**
 * @author Webkul
 * @version 1.0.0
 * This file handles product factory functions.
 */

namespace WkMobikulMultivendorApi\Factory;

use WkMobikulMultivendorApi\Helper;

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'Mkwc_Product_Factory' ) ) {

	/**
	 * Main Factory Class
	 */
	class Mkwc_Product_Factory {

		public static function mkwc_products_sort_label() {
			$sort_label = array(
				array(
					'display' => __( 'Default sorting', 'mobikul_marketplace' ),
					'value'   => 'menu_order',
				),
				array(
					'display' => __( 'Sort by popularity', 'mobikul_marketplace' ),
					'value'   => 'popularity',
				),
				array(
					'display' => __( 'Sort by average rating', 'mobikul_marketplace' ),
					'value'   => 'rating',
				),
				array(
					'display' => __( 'Sort by newness', 'mobikul_marketplace' ),
					'value'   => 'date',
				),
				array(
					'display' => __( 'Sort by price: low to high', 'mobikul_marketplace' ),
					'value'   => 'price',
				),
				array(
					'display' => __( 'Sort by price: high to low', 'mobikul_marketplace' ),
					'value'   => 'price-desc',
				),
			);

			return $sort_label;
		}

		/**
		 * Get products based on search keyword
		 *
		 * @param $s, $page, $per_page, $width
		 * @return $products
		 */
		public static function mkwc_get_search_products( $s, $page, $per_page, $width ) {
			$products     = array();
			$product_list = array();
			$sort_label   = self::mkwc_products_sort_label();
			$product_ids  = self::mkwc_query_products( $page, $per_page, array( 's' => $s ) );
			if ( $product_ids ) {
				if ( $product_ids->posts ) {
					foreach ( $product_ids->posts as $key => $product_id ) {
						$products[] = self::mkwc_get_prepared_product( $product_id, $width );
					}
				}
				if ( $products ) {
					$product_list = array_merge(
						$product_list,
						array(
							'total'          => intval( $product_ids->found_posts ),
							'data_displayed' => count( $products ),
							'products'       => $products,
							'sort_label'     => $sort_label,
						)
					);
				} else {
					$product_list = '';
				}
			}

			return $product_list;
		}


		/**
		 *
		 */
		public static function mkwc_get_search_suggestion_products( $s, $page, $per_page, $width ) {
			$products = array();

			$product_list = array();

			$product_ids = self::mkwc_query_products( $page, $per_page, array( 's' => $s ) );

			if ( $product_ids ) {
				if ( $product_ids->posts ) {
					foreach ( $product_ids->posts as $key => $product_id ) {
						$products[] = array(
							'id'    => $product_id,
							'title' => get_post_field( 'post_title', $product_id ),
						);
					}
				}

				if ( $products ) {
					$product_list = array_merge(
						$product_list,
						array(
							'total'          => intval( $product_ids->found_posts ),
							'data_displayed' => count( $products ),
							'products'       => $products,
						)
					);
				} else {
					$product_list = '';
				}
			}

			return $product_list;
		}

		/**
		 * Return store products
		 *
		 * @param $page, $per_page, $width
		 * @return $product_list
		 */
		public static function mkwc_get_products( $page, $per_page, $width ) {
			$products     = array();
			$product_list = array();
			$sort_label   = self::mkwc_products_sort_label();

			$product_ids = self::mkwc_query_products( $page, $per_page );

			if ( $product_ids ) {
				if ( $product_ids->posts ) {
					foreach ( $product_ids->posts as $key => $product_id ) {
						$products[] = self::mkwc_get_prepared_product( $product_id, $width );
					}
				}

				if ( $products ) {
					$product_list = array_merge(
						$product_list,
						array(
							'total'          => intval( $product_ids->found_posts ),
							'data_displayed' => count( $products ),
							'products'       => $products,
							'sort_label'     => $sort_label,
						)
					);
				} else {
					$product_list = '';
				}
			}

			return $product_list;
		}

		/**
		 * Prepare single product data
		 *
		 * @param $product_id, $width
		 * @return $product
		 */
		public static function mkwc_get_prepared_product( $product_id, $width ) {
			$product = wc_get_product( $product_id );

			if ( ! $product ) {
				return array();
			}

			$product_data = self::mkwc_get_product_data( $product, $width );

			if ( $product->is_type( 'variable' ) && $product->has_child() ) {
				$product_data['variations'] = self::mkwc_get_variation_data( $product, $width );
			}

			return $product_data;
		}

		/**
		 * Product variation data
		 *
		 * @param $product object
		 * @return $variation_data
		 */
		public static function mkwc_get_variation_data( $product, $width ) {
			$variations = array();

			$product_store = new Helper\Mkwc_Product_store();

			$general_data_handler = new Helper\Mkwc_General_Data();

			$product_type = $product->get_type();

			$mFactor = isset( $_GET['mFactor'] ) ? $_GET['mFactor'] : 1;
			$height  = 0.5 * $width;
			$height *= $mFactor;
			$width  *= $mFactor;

			$size = array(
				'width'  => floatval( $width ),
				'height' => floatval( $height ),
				'crop'   => 1,
			);

			foreach ( $product->get_children() as $child_id ) {
				$variation = $product->get_child( $child_id );

				if ( ! $variation->exists() ) {
					continue;
				}

				$post_data = get_post( $variation->get_variation_id() );

				$variations[] = array(
					'id'                => $variation->get_variation_id(),
					'created_at'        => $post_data->post_date_gmt,
					'updated_at'        => $post_data->post_modified_gmt,
					'downloadable'      => $variation->is_downloadable(),
					'virtual'           => $variation->is_virtual(),
					'permalink'         => $variation->get_permalink(),
					'sku'               => $variation->get_sku(),
					'taxable'           => $variation->is_taxable(),
					'tax_status'        => $variation->get_tax_status(),
					'tax_class'         => $variation->get_tax_class(),
					'stock_quantity'    => (int) $variation->get_stock_quantity(),
					'in_stock'          => $variation->is_in_stock(),
					'backordered'       => $variation->is_on_backorder(),
					'purchaseable'      => $variation->is_purchasable(),
					'visible'           => $variation->variation_is_visible(),
					'on_sale'           => $variation->is_on_sale(),
					'weight'            => $variation->get_weight() ? wc_format_decimal( $variation->get_weight(), 2 ) : '',
					'length'            => $variation->get_length(),
					'width'             => $variation->get_width(),
					'height'            => $variation->get_height(),
					'unit'              => get_option( 'woocommerce_dimension_unit' ),
					'shipping_class'    => $variation->get_shipping_class(),
					'shipping_class_id' => ( 0 !== $variation->get_shipping_class_id() ) ? $variation->get_shipping_class_id() : '',
					'image'             => self::mkwc_get_gallery_images( $variation, $product_store, $size ),
					'attributes'        => self::mkwc_get_attributes( $variation ),
					'attribute-string'  => self::mkwc_get_attribute_string( $variation ),
					'downloads'         => self::mkwc_get_downloads( $variation ),
					'download_limit'    => (int) $product->get_download_limit(),
					'download_expiry'   => (int) $product->get_download_expiry(),
					'sale_price'        => $variation->get_sale_price(),
					'price'             => $general_data_handler->mkwc_get_formatted_price( $variation->get_price() ),
					'regular_price'     => $general_data_handler->mkwc_get_formatted_price( $variation->get_regular_price() ),
				);
			}

			return $variations;
		}

		/**
		 * Get product downloads
		 *
		 * @param $product
		 * @return $downloads
		 */
		public static function mkwc_get_downloads( $product ) {
			$downloads = array();

			if ( $product->is_downloadable() ) {
				foreach ( $product->get_files() as $file_id => $file ) {
					$downloads[] = array(
						'id'   => $file_id, // do not cast as int as this is a hash
						'name' => $file['name'],
						'file' => $file['file'],
					);
				}
			}
			return $downloads;
		}

		/**
		 * Get attribute string
		 *
		 * @param $product
		 * @return $attribute_string
		 */
		public static function mkwc_get_attribute_string( $product ) {
			$attributes = array();

			if ( $product->is_type( 'variation' ) ) {
				// variation attributes
				foreach ( $product->get_variation_attributes() as $attribute_name => $attribute ) {
					// taxonomy-based attributes are prefixed with `pa_`, otherwise simply `attribute_`
					$attributes[] = $attribute;
				}
			}
			return implode( '-', $attributes );
		}

		/**
		 * Prepare single product data
		 *
		 * @param $product, $width
		 * @return $product_data
		 */
		public static function mkwc_get_product_data( $product, $width ) {
			$product_data = array();

			$mFactor = isset( $_GET['mFactor'] ) ? $_GET['mFactor'] : 1;
			$height  = 0.5 * $width;
			$height *= $mFactor;
			$width  *= $mFactor;

			$size = array(
				'width'  => floatval( $width ),
				'height' => floatval( $height ),
				'crop'   => 1,
			);

			$product_store = new Helper\Mkwc_Product_store();

			$general_data_handler = new Helper\Mkwc_General_Data();

			$image = $product_store->mkwc_product_image_downsize( $product->get_id(), $size );

			$product_type = $product->get_type();

			if ( 'grouped' == $product_type ) {
				$price_range = $product_store->mkwc_get_grouped_price_range( $product->get_id(), $product->get_children() );
			} elseif ( 'variable' == $product_type ) {
				$variable_price = $general_data_handler->mkwc_get_variable_product_price( $product );

				$price_range = $variable_price['price'];

				$regular_price_range = ( $price_range == $variable_price['regular_price'] ) ? '' : $variable_price['regular_price'];
			} else {
				$price_range = '';

				$regular_price_range = '';
			}

			$post_obj = get_post( $product->get_id() );

			$post_author = isset( $post_obj->post_author ) ? $post_obj->post_author : '';

			$post_author = get_the_author_meta( 'display_name', $post_author );

			$product_data['id'] = $product->get_id();

			$product_data['name'] = $product->get_slug();

			$product_data['title'] = $product->get_name();

			$product_meta = get_post_meta( $product->get_id() );

			foreach ( $product_meta as $key => $meta ) {
				$product_data[ ltrim( $key, '_' ) ] = $meta[0];
			}

			$product_data['price'] = ( $product_type == 'variable' || $product_type == 'grouped' ) ? $price_range : $general_data_handler->mkwc_get_formatted_price( $product->get_price() );

			$regular_price_view = ( $product->get_price() == $product->get_regular_price() ) ? '' : $product->get_regular_price() ? $general_data_handler->mkwc_get_formatted_price( $product->get_regular_price() ) : '';

			$product_data['regular_price']    = ( $product->get_type() == 'variable' ) ? $regular_price_range : $regular_price_view;
			$product_data['discount_percent'] = '';
			if ( $product->get_sale_price() ) {
				$product_data['discount_percent'] = round( 100 - ( $product->get_sale_price() / $product->get_regular_price() * 100 ) ) . '%';
			}
			if ( 'grouped' === $product_type ) {
				$product_data['children'] = $product_store->mkwc_get_product_children_data( $product_data['children'], $size );
			} else {
				$product_data['children'] = array();
			}
			$review_graph = isset( self::mkwc_get_product_review_graph( $product->get_id() )['review_graph'] ) ? self::mkwc_get_product_review_graph( $product->get_id() )['review_graph'] : '';
			$reviews      = isset( self::mkwc_get_product_review_graph( $product->get_id() )['reviews'] ) ? self::mkwc_get_product_review_graph( $product->get_id() )['reviews'] : '';

			$product_data = array_merge(
				$product_data,
				array(
					'product_type'         => $product->get_type(),
					'product_link'         => get_the_permalink( $product->get_id() ),
					'taxable'              => $product->is_taxable(),
					'purchaseable'         => $product->is_purchasable(),
					'shipping_required'    => $product->needs_shipping(),
					'shipping_taxable'     => $product->is_shipping_taxable(),
					'shipping_class'       => $product->get_shipping_class(),
					'shipping_class_id'    => ( 0 !== $product->get_shipping_class_id() ) ? $product->get_shipping_class_id() : '',
					'description'          => apply_filters( 'the_content', $product->get_post_data()->post_content ),
					'short_description'    => apply_filters( 'woocommerce_short_description', $product->get_post_data()->post_excerpt ),
					'reviews_allowed'      => ( 'open' === $product->get_post_data()->comment_status && post_type_supports( 'product', 'comments' ) ),
					'average_rating'       => wc_format_decimal( $product->get_average_rating(), 2 ),
					'star_rating'          => ( 'yes' === get_option( 'woocommerce_enable_review_rating' ) ),
					'star_rating_required' => ( 'yes' === get_option( 'woocommerce_review_rating_required' ) ),
					'rating_count'         => ( is_array( $reviews ) && ! empty( $reviews ) ) ? (int) count( $reviews ) : 0,
					'review_graph'         => $review_graph,
					'reviews'              => $reviews,
					'related_ids'          => $product_store->mkwc_get_product_children_data( array_map( 'absint', array_values( $product->get_upsells() ) ), $size ),
					'upsell_ids'           => array_map( 'absint', $product->get_upsells() ),
					'crosssell_ids'        => array_map( 'absint', $product->get_cross_sells() ),
					'categories'           => wp_get_post_terms( $product->get_id(), 'product_cat', array( 'fields' => 'names' ) ),
					'tags'                 => wp_get_post_terms( $product->get_id(), 'product_tag', array( 'fields' => 'names' ) ),
					'images'               => self::mkwc_get_gallery_images( $product, $product_store, $size ),
					'image'                => isset( $image[0] ) ? $image[0] : '',
					'dominantColor'        => isset( $image[0] ) ? $general_data_handler->mkwc_get_dominant_Color( $image[0] ) : $general_data_handler->mkwc_get_dominant_Color( '' ),
					'attributes'           => self::mkwc_get_attributes( $product ),
					'downloads'            => self::mkwc_get_downloads( $product ),
					'seller_id'            => $post_obj->post_author,
					'seller'               => ucfirst( $post_author ),
				)
			);

			return apply_filters( 'mkwc_get_custom_product_data', $product_data );
		}

		/**
		 * Get product attributes
		 *
		 * @param $product object
		 * @return $attributes
		 */
		public static function mkwc_get_attributes( $product ) {

			$attributes = array();

			$attr_value = '';

			$attr_value_name = '';

			if ( $product->is_type( 'variation' ) ) {
				$parent_attr = get_post_meta( $product->get_parent_id(), '_product_attributes', true );

				// variation attributes
				foreach ( $product->get_variation_attributes() as $attribute_name => $attribute ) {
					$attr_name = str_replace( 'attribute_', '', str_replace( 'pa_', '', $attribute_name ) );

					foreach ( $parent_attr as $key => $value ) {
						if ( isset( $value['is_taxonomy'] ) && $value['is_taxonomy'] == 1 ) {

							$parent_attr_data = wc_get_product_terms( $product->get_parent_id(), str_replace( 'attribute_', '', $attribute_name ), array( 'fields' => 'all' ) );

							if ( $parent_attr_data ) {
								foreach ( $parent_attr_data as $k => $val ) {
									if ( str_replace( 'attribute_', '', $attribute_name ) == $val->taxonomy && $attribute == $val->slug ) {
										$attr_value = $val->name;
									}
								}
							}
						} else {
							$attr_value = $attribute;
						}

						if ( $key == $attr_name ) {
										$attr_value_name = $value['name'];
						}
					}

					// taxonomy-based attributes are prefixed with `pa_`, otherwise simply `attribute_`
					$attributes[] = array(
						'name'   => ucwords( str_replace( 'attribute_', '', str_replace( 'pa_', '', $attribute_name ) ) ),
						'option' => $attribute,
						'value'  => $attr_value,
					);
				}
			} else {
				foreach ( $product->get_attributes() as $attribute ) {
					if ( $attribute['id'] ) {
						$attribute_title = wc_attribute_label( $attribute['name'] );
					} else {
						$attribute_title = ucwords( str_replace( 'pa_', '', $attribute['name'] ) );
					}

					$attributes[] = array(
						'name'      => ucwords( str_replace( 'pa_', '', $attribute['name'] ) ),
						'slug'      => $attribute['name'],
						'title'     => $attribute_title,
						'position'  => $attribute['position'],
						'visible'   => (bool) $attribute['is_visible'],
						'variation' => (bool) $attribute['is_variation'],
						'options'   => self::mkwc_get_attribute_options( $product->get_id(), $attribute ),
					);
				}
			}

			return $attributes;
		}

		/**
		 * Get attribute options
		 *
		 * @param $product_id, $attribute
		 * @return $attribute_options
		 */
		public static function mkwc_get_attribute_options( $product_id, $attribute ) {
			if ( isset( $attribute['is_taxonomy'] ) && $attribute['is_taxonomy'] ) {
				return wc_get_product_terms(
					$product_id,
					$attribute['name'],
					array(
						'fields' => 'all',
					)
				);
			} elseif ( isset( $attribute['value'] ) ) {
				foreach ( array_map( 'trim', explode( '|', $attribute['value'] ) ) as $key => $value ) {
					$arr[] = array(
						'name' => $value,
						'slug' => $value,
					);
				}
				return $arr;
			}

			return array();
		}

		/**
		 * Get product gallery images
		 *
		 * @param $product
		 * @return $gallery_images
		 */
		public static function mkwc_get_gallery_images( $product, $product_store, $size ) {
			$general_data_handler = new Helper\Mkwc_General_Data();
			$images               = $attachment_ids = array();

			if ( $product->is_type( 'variation' ) ) {
				if ( has_post_thumbnail( $product->get_variation_id() ) ) {
					$attachment_ids[] = get_post_thumbnail_id( $product->get_variation_id() );
				} elseif ( has_post_thumbnail( $product->get_id() ) ) {
					$attachment_ids[] = get_post_thumbnail_id( $product->get_id() );
				}
			} else {
				// add featured image
				if ( has_post_thumbnail( $product->get_id() ) ) {
					$attachment_ids[] = get_post_thumbnail_id( $product->get_id() );
				}
				// add gallery images
				$attachment_ids = array_merge( $attachment_ids, $product->get_gallery_attachment_ids() );
			}

			// build image data
			foreach ( $attachment_ids as $position => $attachment_id ) {
				$attachment_post = get_post( $attachment_id );

				if ( is_null( $attachment_post ) ) {
					continue;
				}

				$image_meta = wp_get_attachment_metadata( $attachment_id );

				$attachment = $product_store->mkwc_image_downsize( $image_meta, $size, $attachment_id );

				// $attachment = wp_get_attachment_image_src( $attachment_id, 'full' );
				$attachment = isset( $attachment[0] ) ? $attachment[0] : '';

				$images[] = array(
					'id'            => (int) $attachment_id,
					'src'           => $attachment,
					'dominantColor' => $attachment ? $general_data_handler->mkwc_get_dominant_Color( $attachment ) : $general_data_handler->mkwc_get_dominant_Color( '' ),
					'title'         => get_the_title( $attachment_id ),
				);
			}

			// set a placeholder image if the product has no images set
			if ( empty( $images ) ) {
				$images[] = array(
					'id'            => 0,
					'src'           => wc_placeholder_img_src(),
					'dominantColor' => wc_placeholder_img_src() ? $general_data_handler->mkwc_get_dominant_Color( wc_placeholder_img_src() ) : $general_data_handler->mkwc_get_dominant_Color( '' ),
					'title'         => __( 'Placeholder', 'mobikul_marketplace' ),
				);
			}

			return $images;
		}


		/**
		 * Store products query function
		 */
		public static function mkwc_query_products( $page, $per_page, $args = array(), $data = '' ) {
			$product_store    = new Helper\Mkwc_Product_store();
			$offset           = $per_page * ( $page - 1 );
			$additional_query = array();
			if ( ! empty( $data ) ) {
				$param = $data->get_params();
			} else {
				$param = array();
			}

			$orderby       = ( isset( $_GET['orderby'] ) && $_GET['orderby'] ) ? $_GET['orderby'] : '';
			$order         = ( isset( $_GET['order'] ) && $_GET['order'] ) ? $_GET['order'] : '';
			$type          = ( isset( $_GET['type'] ) && $_GET['type'] ) ? $_GET['type'] : '';
			$singleUserId  = ! empty( $param['user_id'] ) ? $param['user_id'] : '';
			$category_type = ( isset( $_GET['category_type'] ) && $_GET['category_type'] ) ? $_GET['category_type'] : '';
			$tax_query     = array();
			$meta_query    = array();

			if ( 'house-plans' === $category_type ) {

				$archiArr = array();
				$args1    = array(
					'role' => 'architect',
				);

				if ( ! empty( $singleUserId ) ) {
					$archiArr[] = $singleUserId;
				} else {
					$subscribers = get_users( $args1 );
					foreach ( $subscribers as $user ) {
						$archiArr[] = $user->ID;
					}
				}
				$metaqueryArr = array();
				if ( ! empty( $param['nfb'] ) ) {
					$metaqueryArr[] = array(
						'key'     => 'no__of_bebrooms',
						'value'   => $param['nfb'],
						'compare' => '=',
					);
				}

				if ( ! empty( $param['nfbb'] ) ) {
					$metaqueryArr[] = array(
						'key'     => 'no__of_bathrooms',
						'value'   => $param['nfbb'],
						'compare' => '=',
					);
				}

				if ( ! empty( $param['nfg'] ) ) {
					$metaqueryArr[] = array(
						'key'     => 'no_of_garage_stalls',
						'value'   => $param['nfg'],
						'compare' => '=',
					);
				}
				if ( ! empty( $param['archstyle'] ) ) {
					$metaqueryArr[] = array(
						'key'     => 'architectural_style',
						'value'   => $param['archstyle'],
						'compare' => '=',
					);
				}
				if ( ! empty( $param['cc'] ) ) {
					$metaqueryArr[] = array(
						'key'     => 'construction_cost',
						'value'   => $param['cc'],
						'compare' => '=',
					);
				}
				$paged = ( get_query_var( 'paged',$page ) ) ? get_query_var( 'paged',$page) : 1;

				if ( ! empty( $param['nfb'] ) || ! empty( $param['nfbb'] ) || ! empty( $param['nfg'] ) || ! empty( $param['archstyle'] ) || ! empty( $param['cc'] ) ) {
					// echo "AV";
					$args = array(

						'author__in'     => $archiArr,
						'orderby'        => 'post_date',
						'order'          => 'ASC',
						'posts_per_page' => '10',
						'post_type'      => 'product',
						'paged'          => $paged,
						'no_found_rows'  => true,
						'meta_query'     => array(
							'relation' => 'OR',
							$metaqueryArr,

						),

					);
				} else {
					$args = array(
						'author__in'     => $archiArr,
						'orderby'        => 'post_date',
						'order'          => 'ASC',
						'posts_per_page' => '16',
						'paged'          => $paged,
						'post_type'      => 'product',
						'post_status'    => 'publish',
					);
				}

				return new \WP_Query( $args );

			}

			if ( $orderby ) {
				switch ( $orderby ) {
					case 'id':
						$order_by   = 'ID';
						$order_type = ( 'ASC' === $order ) ? 'ASC' : 'DESC';
						break;
					case 'menu_order':
						$order_by   = 'menu_order title';
						$order_type = ( 'ASC' === $order ) ? 'ASC' : 'DESC';
						break;
					case 'title':
						$order_by   = 'title';
						$order_type = ( 'DESC' === $order ) ? 'DESC' : 'ASC';
						break;
					case 'date':
						$order_by   = array(
							'date'  => 'DESC',
							'title' => 'DESC',
						);
						$order_type = ( 'ASC' === $order ) ? 'ASC' : 'DESC';
						break;
					case 'price':
						$order_by         = array(
							'meta_value_num' => 'ASC',
							'title'          => 'DESC',
						);
						$order_type       = ( 'DESC' === $order ) ? 'DESC' : 'ASC';
						$additional_query = array(
							'meta_key' => '_price',
						);
						break;
					case 'price-desc':
						$order_by         = array(
							'meta_value_num' => 'DESC',
							'title'          => 'DESC',
						);
						$order_type       = 'DESC';
						$additional_query = array(
							'meta_key' => '_price',
						);
						break;
					case 'rating':
						$order_type = ( 'ASC' === $order ) ? 'ASC' : 'DESC';

						$order_by = array(
							'meta_value_num' => 'DESC',
							'title'          => 'DESC',
						);

						$additional_query = array(
							'meta_key' => '_wc_average_rating',

						);
						break;

					case 'popularity':
						$order_type       = ( 'ASC' === $order ) ? 'ASC' : 'DESC';
						$order_by         = array(
							'meta_value_num' => $order_type,
							'title'          => 'ASC',
						);
						$additional_query = array(
							'meta_key' => 'total_sales',
						);
						break;

					case 'popular_products':
						$order_by         = 'meta_value_num';
						$order_type       = ( 'DESC' === $order ) ? 'DESC' : 'ASC';
						$additional_query = array(
							'meta_key' => 'total_sales',
						);
						break;
					default:
						$order_by   = 'menu_order title';
						$order_type = ( 'DESC' === $order ) ? 'DESC' : 'ASC';
						break;
				}
			} else {
				$order_by   = 'menu_order title';
				$order_type = ( 'DESC' === $order ) ? 'DESC' : 'ASC';
			}

			if ( $type === 'featured_products' ) {
				$tax_query = array(
					array(
						'taxonomy' => 'product_visibility',
						'field'    => 'name',
						'terms'    => 'featured',
						'operator' => 'IN',
					),
				);
			} elseif ( $type === 'new_products' ) {
				$order_by   = 'date';
				$order_type = 'DESC';
			} elseif ( $type === 'sale_products' ) {
				$product_on_sale = wc_get_product_ids_on_sale();
				if ( is_array( $product_on_sale ) && $product_on_sale ) {
					// $additional_query = array(
					// 'post__in' => $product_on_sale,
					// $additional_query
					// );

					$ar1              = array(
						'post__in' => $product_ids,
					);
					$additional_query = array_merge( $additional_query, $ar1 );

				}
			} elseif ( $type ) {
				$type      = str_replace( '_', ' ', $type );
				$type_name = $product_store->mkwc_return_homepage_carousels_var( $type );
				if ( $type_name && $type === $type_name ) {
					$carousel_data = $product_store->mkwc_return_homepage_carousels_by_title( $type_name );
					if ( isset( $carousel_data[0]->type ) && $carousel_data[0]->type === 'products' && isset( $carousel_data[0]->carousel_ids ) && $carousel_data[0]->carousel_ids ) {
						$product_ids = maybe_unserialize( $carousel_data[0]->carousel_ids );
						if ( is_array( $product_ids ) && $product_ids ) {
							$ar               = array(
								'post__in' => $product_ids,
							);
							$additional_query = array_merge( $additional_query, $ar );

						}
					} elseif ( isset( $carousel_data[0]->type ) && $carousel_data[0]->type === 'categories' && isset( $carousel_data[0]->carousel_ids ) && $carousel_data[0]->carousel_ids ) {
						$cat_ids = maybe_unserialize( $carousel_data[0]->carousel_ids );
						if ( is_array( $cat_ids ) && $cat_ids ) {
							$tax_query = array(
								array(
									'taxonomy' => 'product_cat',
									'field'    => 'id',
									'terms'    => $cat_ids,
									'operator' => 'IN',
								),
							);
						}
					}
				}
			}
			// set base query arguments
			$query_args = array(
				'fields'         => 'ids',
				'post_type'      => 'product',
				'post_status'    => 'publish',
				'posts_per_page' => $per_page,
				'offset'         => $offset,
				'orderby'        => $order_by,
				'order'          => $order_type,
				'meta_query'     => $meta_query,
				'tax_query'      => $tax_query,
			);

			$query_args = array_merge( $query_args, $args );

			$query_args = array_merge( $query_args, $additional_query );

			return new \WP_Query( $query_args );
		}

		public static function mkwc_get_product_review_graph( $product_id ) {
			$reviews = $data = array();

			if ( ! wc_get_product( $product_id ) ) {
				return $data;
			}

			$comments = get_approved_comments( $product_id );

			foreach ( $comments as $comment ) {
				$created_at = gmdate( 'F d Y', strtotime( $comment->comment_date_gmt ) );
				$reviews[]  = array(
					'id'             => $comment->comment_ID,
					'created_at'     => $created_at,
					'review'         => $comment->comment_content,
					'rating'         => get_comment_meta( $comment->comment_ID, 'rating', true ),
					'reviewer_name'  => $comment->comment_author,
					'reviewer_email' => $comment->comment_author_email,
					'verified'       => wc_review_is_from_verified_owner( $comment->comment_ID ),
				);
			}

			if ( $reviews ) {
				$review_count = array_column( $reviews, 'rating' );

				$review_count = array_count_values( $review_count );

				$review_graph = array(
					'start_one'   => 0,
					'start_two'   => 0,
					'start_three' => 0,
					'start_four'  => 0,
					'start_five'  => 0,
				);

				if ( isset( $review_count[1] ) && $review_count[1] ) {
					$review_graph['start_one'] = $review_count[1];
				}

				if ( isset( $review_count[2] ) && $review_count[2] ) {
					$review_graph['start_two'] = $review_count[2];
				}

				if ( isset( $review_count[3] ) && $review_count[3] ) {
					$review_graph['start_three'] = $review_count[3];
				}

				if ( isset( $review_count[4] ) && $review_count[4] ) {
					$review_graph['start_four'] = $review_count[4];
				}

				if ( isset( $review_count[5] ) && $review_count[5] ) {
					$review_graph['start_five'] = $review_count[5];
				}

				$data = array(
					'reviews'      => array_slice( $reviews, 0, 3, true ),
					'review_graph' => $review_graph,
				);
			}

			return $data;
		}
	}
}
