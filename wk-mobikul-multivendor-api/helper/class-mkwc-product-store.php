<?php

/**
 * @author Webkul
 * @version 1.0.0
 * This file handles product store functions.
 */

namespace WkMobikulMultivendorApi\Helper;

use WkMobikulMultivendorApi\Helper;

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'Mkwc_Product_store' ) ) {
	/**
	 *
	 */
	class Mkwc_Product_store implements Util\Mkwc_Product_Store_Interface {

		protected $wpdb;

		protected $general_data_handler;

		public function __construct() {
			global $wpdb;

			$this->wpdb = $wpdb;
		}

		/**
		 * Return prepared product children data
		 *
		 * @param String serialised $children, $size
		 * @return Array $child_data
		 */
		public function mkwc_get_product_children_data( $children, $size ) {
			$general_data_handler = new Helper\Mkwc_General_Data();
			$child_data           = array();
			if ( $children ) {
				$children = maybe_unserialize( $children );
				foreach ( $children as $child ) {
					$product = wc_get_product( $child );
					if ( ! $product ) {
						continue;
					}
					$product_type = $product->get_type();

					if ( 'variable' == $product_type ) {
						$variable_price = $general_data_handler->mkwc_get_variable_product_price( $product );

						$price_range = $variable_price['price'];

						$regular_price_range = $variable_price['regular_price'];
					} else {
						$price_range = '';

						$regular_price_range = '';
					}

					$price = ( $product_type == 'variable' ) ? $price_range : $general_data_handler->mkwc_get_formatted_price( $product->get_price() );

					$regular_price_view = ( $product->get_price() == $product->get_regular_price() ) ? '' : $general_data_handler->mkwc_get_formatted_price( $product->get_regular_price() );

					$regular_price = ( $product->get_type() == 'variable' ) ? $regular_price_range : $regular_price_view;

					$image = $this->mkwc_product_image_downsize( $product->get_id(), $size );

					$child_data[] = array(
						'id'             => $child,
						'name'           => get_the_title( $child ),
						'product_type'   => $product_type,
						'price'          => $price,
						'regular_price'  => $regular_price,
						'image'          => isset( $image[0] ) ? $image[0] : '',
						'average_rating' => wc_format_decimal( $product->get_average_rating(), 2 ),
						'rating_count'   => (int) $product->get_rating_count(),
					);
				}
			} else {
				$child_data = array();
			}

			return $child_data;
		}

		/**
		 * Return image src based on ids
		 *
		 * @param Array|Int $image_ids
		 * @return Array $image
		 */
		public function mkwc_get_image_src( $ids ) {
			$urls = array();

			if ( $ids ) {
				if ( is_array( $ids ) ) {
					foreach ( $ids as $key => $value ) {
						$src = wp_get_attachment_image_src( $value );

						if ( $src ) {
							$urls[] = array(
								'id'    => $value,
								'image' => $src[0],
							);
						}
					}
				} else {
					$src = wp_get_attachment_image_src( $ids );

					if ( $src ) {
						$urls[] = array(
							'id'    => $ids,
							'image' => $src[0],
						);
					}
				}
			}

			return $urls;
		}

		/**
		 * Return product author
		 *
		 * @param $product_id
		 * @return $product_author
		 */
		public function mkwc_get_product_author( $product_id ) {
			$product_author = '';

			if ( ! $product_id ) {
				return false;
			}

			$product_author = $this->wpdb->get_var( $this->wpdb->prepare( "SELECT post_author from {$this->wpdb->prefix}posts where ID='%d'", $product_id ) );

			return (int) $product_author;
		}

		/**
		 * Return all existing sku
		 *
		 * @return Array $sku
		 */
		public function mkwc_check_sku( $sku ) {
			$table_name = $this->wpdb->prefix . 'postmeta';

			$data = $this->wpdb->get_results( "SELECT post_id from $table_name where meta_key='_sku' and meta_value = '$sku'" );

			if ( count( $data ) ) {
				return true;
			} else {
				return false;
			}
		}

		/**
		 * Return featured categories
		 */
		public function mkwc_return_featured_category_data() {
			$posts_list = array();

			$posts_list = $this->wpdb->get_results( "SELECT * FROM {$this->wpdb->prefix}termmeta WHERE meta_key = 'api_featured_sort_order' ORDER BY meta_value" );

			return $posts_list;
		}

		/**
		 * Prepare featured categories
		 *
		 * @param $featured_categories, $width
		 * @return $prepared_data
		 */
		public function mkwc_prepare_featured_categories( $featured_categories, $width ) {
			$general_data_handler = new Helper\Mkwc_General_Data();

			$prepared_data = array();

			$mFactor = isset( $_GET['mFactor'] ) ? $_GET['mFactor'] : 1;

			$img_size = floor( $width / 3 * $mFactor );

			$size = array(
				'width'  => floatval( $img_size ),
				'height' => floatval( $img_size ),
				'crop'   => 1,
			);

			if ( $featured_categories ) {
				foreach ( $featured_categories as $key => $category ) {
					$category_id = (int) $category->term_id;

					$status = get_term_meta( $category_id, 'api_featured_category', true );

					if ( 'enabled' == $status ) {
						$cat_image = $this->mkwc_category_image_downsize( $category_id, $size );

						$post_fields = array(
							'id'            => $category_id,
							'name'          => html_entity_decode( get_term_by( 'id', $category_id, 'product_cat' )->name ),
							'image'         => isset( $cat_image[0] ) ? $cat_image[0] : '',
							'dominantColor' => isset( $cat_image[0] ) ? $general_data_handler->mkwc_get_dominant_Color( $cat_image[0] ) : $general_data_handler->mkwc_get_dominant_Color( '' ),
						);

						$prepared_data[] = $post_fields;
					}
				}
			}

			return $prepared_data;
		}

		/**
		 * Prepare featured categories for admin
		 *
		 * @param $featured_categories, $width
		 * @return $prepared_data
		 */
		public function mkwc_prepare_admin_featured_categories( $featured_categories ) {
			$prepared_data = array();

			$img_size = floor( 320 / 3 );

			$size = array(
				'width'  => intval( $img_size ),
				'height' => intval( $img_size ),
				'crop'   => 1,
			);

			if ( $featured_categories ) {
				foreach ( $featured_categories as $key => $category ) {
					$category_id = (int) $category->term_id;

					$status    = get_term_meta( $category_id, 'api_featured_category', true );
					$cat_image = $this->mkwc_category_image_downsize( $category_id, $size );

					$post_fields = array(
						'id'    => $category_id,
						'name'  => html_entity_decode( get_term_by( 'id', $category_id, 'product_cat' )->name ),
						'image' => isset( $cat_image[0] ) ? $cat_image[0] : '',
					);

					$prepared_data[] = $post_fields;
				}
			}

			return $prepared_data;
		}

		/**
		 * Return homepage banners
		 *
		 * @return $banners
		 */
		public function mkwc_return_homepage_banners() {
			$posts_list = array();

			$posts_list = $this->wpdb->get_results( "SELECT * FROM {$this->wpdb->prefix}mp_banner WHERE status != 'trash'" );

			return $posts_list;
		}

		/**
		 * Return homepage carousels
		 *
		 * @return $carousels
		 */
		public function mkwc_return_homepage_carousels() {
			$posts_list = array();

			$posts_list = $this->wpdb->get_results( "SELECT * FROM {$this->wpdb->prefix}mp_carousels WHERE status != 'trash'" );

			return $posts_list;
		}

		/**
		 * Return homepage carousels
		 *
		 * @return $carousels
		 */
		public function mkwc_return_homepage_carousels_var( $title ) {
			$posts_list = array();

			$posts_list = $this->wpdb->get_var( $this->wpdb->prepare( "SELECT title FROM {$this->wpdb->prefix}mp_carousels WHERE title = %s", $title ) );

			return $posts_list;
		}

		/**
		 * Return homepage carousels by title
		 *
		 * @return $carousels
		 */
		public function mkwc_return_homepage_carousels_by_title( $title ) {
			$posts_list = array();

			$posts_list = $this->wpdb->get_results( $this->wpdb->prepare( "SELECT carousel_ids, type FROM {$this->wpdb->prefix}mp_carousels WHERE title = %s", $title ) );

			return $posts_list;
		}

		/**
		 * Delete homepage carousels
		 *
		 * @return $carousels
		 */
		public function mkwc_delete_homepage_carousels( $id ) {
			$result = array();

			$result = $this->wpdb->delete(
				$this->wpdb->prefix . 'mp_carousels',
				array( 'id' => $id ),
				array( '%d' )
			);

			return $result;
		}

		/**
		 * Delete homepage banners
		 *
		 * @return $banners
		 */
		public function mkwc_delete_homepage_banners( $id ) {
			$result = array();

			$result = $this->wpdb->delete( $this->wpdb->prefix . 'mp_banner', array( 'id' => $id ) );

			return $result;
		}

		/**
		 * Prepare banner data
		 *
		 * @param $banners, $width
		 * @return $banner_prepared_data
		 */
		public function mkwc_prepare_homepage_banner_data( $banners, $width ) {
			$general_data_handler = new Helper\Mkwc_General_Data();

			$prepared_data = array();

			$mFactor = isset( $_GET['mFactor'] ) ? $_GET['mFactor'] : 1;
			$height  = 0.5 * $width;
			$height *= $mFactor;
			$width  *= $mFactor;

			$size = array(
				'width'  => floatval( $width ),
				'height' => floatval( $height ),
				'crop'   => 1,
			);

			if ( $banners ) {
				foreach ( $banners as $key => $banner ) {

					$image = $this->mkwc_banner_image_downsize( $banner->id, $size, $banner->image );

					$post_fields = array(
						'id'            => (int) $banner->term_id,
						'image'         => isset( $image[0] ) ? $image[0] : '',
						'dominantColor' => isset( $image[0] ) ? $general_data_handler->mkwc_get_dominant_Color( $image[0] ) : $general_data_handler->mkwc_get_dominant_Color( '' ),
						'banner_type'   => $banner->banner_type,
					);

					$prepared_data[] = $post_fields;
				}
			}

			return $prepared_data;
		}


		/**
		 * Return featured product ids based on section
		 *
		 * @param $blank
		 * @return $product_ids
		 */
		public function mkwc_return_featured_product_data() {
			$products = array();

			$tax_query[] = array(
				'taxonomy' => 'product_visibility',
				'field'    => 'name',
				'terms'    => 'featured',
				'operator' => 'IN',
			);
			$args        = array(
				'numberposts' => -1,
				'post_status' => 'published',
				'orderby'     => 'title',
				'order'       => 'ASC',
				'tax_query'   => $tax_query,
			);
			$products    = wc_get_products( $args );

			return $products;
		}

		/**
		 * Return sale product ids based on section
		 *
		 * @param $blank
		 * @return $product_ids
		 */
		public function mkwc_return_sale_product_data() {
			$products = $product_on_sale = array();

			$product_on_sale = wc_get_product_ids_on_sale();

			$product_on_sale = array_slice( $product_on_sale, 0, 5, true );
			foreach ( $product_on_sale as $pkey => $pvalue ) {
				$products[] = wc_get_product( $pvalue );
			}

			return $products;
		}

		/**
		 * Return product ids based on section
		 *
		 * @param $section
		 * @return $product_ids
		 */
		public function mkwc_return_homepage_product_data( $section = '' ) {
			if ( $section ) {
				switch ( $section ) {
					case 'featured':
						$posts_list = $this->wpdb->get_results( "SELECT post_id as ID FROM {$this->wpdb->prefix}postmeta WHERE meta_key = 'product_api_sort_order' ORDER BY meta_value" );

						return $posts_list;
					break;

					case 'new':
						$posts_list = $this->wpdb->get_results( "SELECT ID FROM {$this->wpdb->prefix}posts WHERE post_status = 'publish' AND post_type = 'product' ORDER BY post_date DESC LIMIT 5" );

						return $posts_list;
					break;

					default:
						return array();
					break;
				}
			}
		}

		/**
		 * Return product ids based on seller id
		 *
		 * @param $seller_id
		 * @return $product_ids
		 */
		public function mkwc_return_seller_product_data( $seller_id ) {
			if ( $seller_id ) {
				$posts_list = $this->wpdb->get_results( "SELECT ID FROM {$this->wpdb->prefix}posts WHERE post_status = 'publish' AND post_type = 'product' AND post_author = $seller_id ORDER BY post_date DESC LIMIT 6" );

				return $posts_list;
			}
		}

		/**
		 * Return banner image based on size
		 *
		 * @param $banner_id, $size, $image
		 * @return $banner_image
		 */
		public function mkwc_banner_image_downsize( $banner_id, $size, $attachment_id ) {
			$image_meta = wp_get_attachment_metadata( $attachment_id );

			$banner_image = $this->mkwc_image_downsize( $image_meta, $size, $attachment_id );

			return $banner_image;
		}

		/**
		 * Return product image based on size
		 *
		 * @param $product_id, $size
		 * @return $product_image
		 */
		public function mkwc_product_image_downsize( $product_id, $size ) {
			$attachment_id = get_post_thumbnail_id( $product_id );

			if ( ! $attachment_id ) {
				return array(
					wc_placeholder_img_src(),
				);
			}

			$image_meta = wp_get_attachment_metadata( $attachment_id );
			return $this->mkwc_image_downsize( $image_meta, $size, $attachment_id );
		}

		/**
		 * Return category image based on size
		 *
		 * @param $term_id, $size
		 * @return $cat_image
		 */
		public function mkwc_category_image_downsize( $term_id, $size ) {
			$attachment_id = get_term_meta( $term_id, 'thumbnail_id', true );

			$image_meta = wp_get_attachment_metadata( $attachment_id );

			return $this->mkwc_image_downsize( $image_meta, $size, $attachment_id );
		}

		/**
		 * Return image based on size
		 *
		 * @param $image_meta, $size
		 * @return $image
		 */
		public function mkwc_image_downsize( $image_meta, $size, $attachment_id ) {
			$out_width = $out_height = 0;

			if ( empty( $image_meta['file'] ) ) {
				return false;
			}

			$upload_dir       = wp_upload_dir();
			$image_path       = $upload_dir['baseurl'] . '/' . $image_meta['file'];
			$image_path       = str_replace( 'http:', 'https:', $image_path );
			$file_path        = $upload_dir['basedir'] . '/' . $image_meta['file'];
			$extension        = pathinfo( $file_path, PATHINFO_EXTENSION );
			$extension        = '.' . $extension;
			$check_file_name  = explode( $extension, $file_path )[0];
			$check_file_name .= '-' . $size['width'] . 'x' . $size['height'] . $extension;

			if ( file_exists( $check_file_name ) ) {
				$image_path  = explode( $extension, $image_path )[0];
				$image_path .= '-' . $size['width'] . 'x' . $size['height'] . $extension;

				return array(
					$image_path,
					$size['width'],
					$size['height'],
					true,
				);
			}

			if ( empty( $image_meta ) ) {
				return false;
			}

			// get $size dimensions
			global $_wp_additional_image_sizes;

			$out_width  = $size ['width'];
			$out_height = $size ['height'];
			$out_crop   = isset( $size ['crop'] ) ? $size ['crop'] : false;
			// image size not found, create it
			$attachment_path = get_attached_file( $attachment_id );
			$image_editor = wp_get_image_editor( $attachment_path );
			if ( ! is_wp_error( $image_editor ) ) {

				$image_editor->resize( $out_width, $out_height, false );

				$result_width    = $out_width;
				$result_height   = $out_height;
				$suffix          = $out_width . 'x' . $out_height;
				$filename        = $image_editor->generate_filename( $suffix );
				$resp            = $image_editor->save( $filename );
				$result_filename = wp_basename( $filename );

				$image_meta['sizes']['mobile-thumbnail'] = array(
					'file'      => $result_filename,
					'width'     => $out_width,
					'height'    => $out_height,
					'mime-type' => get_post_mime_type( $attachment_id ),

				);

				$update           = wp_update_attachment_metadata( $attachment_id, $image_meta );
				$img_url          = wp_get_attachment_url( $attachment_id );
				$img_url_basename = wp_basename( $img_url );
				$img_url          = str_replace( $img_url_basename, $result_filename, $img_url );
				return array(
					$img_url,
					$result_width,
					$result_height,
					true,
				);
			}

			return false;
		}

		/**
		 * Prepare homepage product section data
		 *
		 * @param $product_object, $width
		 * @return $prepared_data
		 */
		public function mkwc_prepare_homepage_products( $homepage_products, $width, $section = 'featured' ) {
			$general_data_handler = new Helper\Mkwc_General_Data();

			$prepared_data = array();

			if ( is_array( $homepage_products ) ) {
				$mFactor = isset( $_GET['mFactor'] ) ? $_GET['mFactor'] : 1;

				$img_size = floor( $width / 2 * $mFactor );

				$size = array(
					'width'  => floatval( $img_size ),
					'height' => floatval( $img_size ),
					'crop'   => 1,
				);
				foreach ( $homepage_products as $key => $post ) {
					$product_id = $post->ID;

					$product = wc_get_product( $product_id );

					$product_type = $product->get_type();

					$featured_status = get_post_meta( $product_id, 'product_api_status', true );

					$product_image = $this->mkwc_product_image_downsize( $product_id, $size );

					if ( $section == 'new' ) {
						$featured_status = 'enabled';
					}

					if ( $featured_status == 'enabled' ) {
						$price_range = '';

						$regular_price = '';

						if ( $product_type == 'grouped' ) {
							$price_range = $this->mkwc_get_grouped_price_range( $product_id, $product->get_children() );
						} elseif ( $product_type == 'variable' ) {
							$variable_price = $general_data_handler->mkwc_get_variable_product_price( $product );

							$price_range = $variable_price['price'];

							$regular_price = $variable_price['regular_price'];
						} else {
							$price_range = $general_data_handler->mkwc_get_formatted_price( $product->get_price() );

							$regular_price = $product->get_sale_price() ? $general_data_handler->mkwc_get_formatted_price( $product->get_regular_price() ) : '';
						}
						if ( ! $product->get_price() && $product_type != 'grouped' ) {
							$price_range = '';
						}

						// prepare common product fields
						$post_fields = array(
							'id'                => $product_id,
							'name'              => $product->get_name(),
							'banner_image'      => isset( $product_image[0] ) ? $product_image[0] : '',
							'dominantColor'     => isset( $product_image[0] ) ? $general_data_handler->mkwc_get_dominant_Color( $product_image[0] ) : $general_data_handler->mkwc_get_dominant_Color( '' ),
							'price'             => $price_range,
							'regular_price'     => $regular_price,
							'average_rating'    => wc_format_decimal( $product->get_average_rating(), 2 ),
							'short_description' => apply_filters( 'woocommerce_short_description', $product->get_short_description() ),
						);

						$prepared_data[] = $post_fields;
					}
				}
			}
			return apply_filters( 'mkwc_homepage_prepare_products', $prepared_data );
		}


		/**
		 * Prepare homepage featured product section data
		 *
		 * @param $product_object, $width
		 * @return $prepared_data
		 */
		public function mkwc_prepare_homepage_featured_products( $homepage_products, $width ) {
			$general_data_handler = new Helper\Mkwc_General_Data();

			$prepared_data = array();

			if ( is_array( $homepage_products ) ) {
				$mFactor = isset( $_GET['mFactor'] ) ? $_GET['mFactor'] : 1;

				$img_size = floor( $width / 2 * $mFactor );

				$size = array(
					'width'  => floatval( $img_size ),
					'height' => floatval( $img_size ),
					'crop'   => 1,
				);
				foreach ( $homepage_products as $key => $post ) {
					$product_id = $post->get_id();

					$product = wc_get_product( $product_id );

					$product_type = $product->get_type();

					$featured_status = $product->get_status();

					$product_image = $this->mkwc_product_image_downsize( $product_id, $size );

					if ( $featured_status == 'publish' ) {
						$price_range = '';

						$regular_price = '';

						if ( $product_type == 'grouped' ) {
							$price_range = $this->mkwc_get_grouped_price_range( $product_id, $product->get_children() );
						} elseif ( $product_type == 'variable' ) {
							$variable_price = $general_data_handler->mkwc_get_variable_product_price( $product );

							$price_range = $variable_price['price'];

							$regular_price = $variable_price['regular_price'];
						} else {
							$price_range = $general_data_handler->mkwc_get_formatted_price( $product->get_price() );

							$regular_price = $product->get_sale_price() ? $general_data_handler->mkwc_get_formatted_price( $product->get_regular_price() ) : '';
						}
						if ( ! $product->get_price() ) {
							$price_range = '';
						}

						// prepare common product fields
						$post_fields = array(
							'id'                => $product_id,
							'name'              => $product->get_name(),
							'banner_image'      => isset( $product_image[0] ) ? $product_image[0] : '',
							'dominantColor'     => isset( $product_image[0] ) ? $general_data_handler->mkwc_get_dominant_Color( $product_image[0] ) : $general_data_handler->mkwc_get_dominant_Color( '' ),
							'price'             => $price_range,
							'regular_price'     => $regular_price,
							'average_rating'    => wc_format_decimal( $product->get_average_rating(), 2 ),
							'short_description' => apply_filters( 'woocommerce_short_description', $product->get_short_description() ),
						);

						$prepared_data[] = $post_fields;
					}
				}
			}
			return apply_filters( 'mkwc_homepage_prepare_products', $prepared_data );
		}

		/**
		 * Get product childrens
		 *
		 * @param $product_id, $children
		 * @return array $children
		 */
		public function mkwc_get_product_children( $id, $children ) {
			if ( ! is_array( $children ) || empty( $children ) ) {
				$transient_name = 'wc_product_children_' . $id;
				$children       = array_filter( array_map( 'absint', (array) get_transient( $transient_name ) ) );

				if ( empty( $children ) ) {
					$args = apply_filters(
						'woocommerce_grouped_children_args',
						array(
							'post_parent' => $id,
							'post_type'   => 'product',
							'orderby'     => 'menu_order',
							'order'       => 'ASC',
							'fields'      => 'ids',
							'post_status' => 'publish',
							'numberposts' => -1,
						)
					);

					$children = get_posts( $args );

					set_transient( $transient_name, $children, DAY_IN_SECONDS * 30 );
				}
			}
			return (array) $children;
		}

		/**
		 * Prepare grouped product price
		 *
		 * @param $product_id, $children
		 * @return $price
		 */
		public function mkwc_get_grouped_price_range( $product_id, $children ) {
			$general_data_handler = new Helper\Mkwc_General_Data();

			$tax_display_mode = get_option( 'woocommerce_tax_display_shop' );

			$child_prices = array();

			foreach ( $this->mkwc_get_product_children( $product_id, $children ) as $child_id ) {
				$child = wc_get_product( $child_id );

				if ( $child && '' !== $child->get_price() ) {
					$child_prices[] = 'incl' === $tax_display_mode ? $child->get_price_including_tax() : $child->get_price_excluding_tax();
				}
			}

			if ( ! empty( $child_prices ) ) {
				$min_price = min( $child_prices );

				$max_price = max( $child_prices );
			} else {
				$min_price = '';

				$max_price = '';
			}

			if ( $min_price == $max_price ) {
				return $general_data_handler->mkwc_get_formatted_price( $min_price );
			} else {
				return $general_data_handler->mkwc_get_formatted_price_range( $min_price, $max_price );
			}
		}

		/**
		 * Get featured_category by id
		 *
		 * @param $category_id
		 * @return $category
		 */
		public function mkwc_check_featured_category_id( $category_id ) {
			$table_name = $this->wpdb->prefix . 'termmeta';

			$featured_category_data = array();

			$featured_category_data = $this->wpdb->get_results( "Select * from $table_name Where term_id = $category_id And meta_key = 'api_featured_category'" );

			return $featured_category_data;
		}
	}
}
