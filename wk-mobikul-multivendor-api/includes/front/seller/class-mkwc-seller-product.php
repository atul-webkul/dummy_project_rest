<?php

/**
 * @author Webkul
 * @version 1.0.0
 * This file handles seller add product functions.
 */

namespace WkMobikulMultivendorApi\Includes\Front\Seller;

use WkMobikulMultivendorApi\Includes;
use WkMobikulMultivendorApi\Includes\Front\Seller;
use WkMobikulMultivendorApi\Helper;
use WkMobikulMultivendorApi\Factory;

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'Mkwc_Seller_Product_Add' ) ) {
	/**
	 * Seller list
	 */
	class Mkwc_Seller_Product extends Includes\Mkwc_Authentication implements Util\Mkwc_Seller_Product_Interface {

		public static $base = 'seller/product';

		protected $response;

		protected $seller_data_handler;

		protected $product_store;

		/**
		 * Return seller products
		 *
		 * @param $search_keyword
		 * @return Array $result
		 */
		public function mkwc_get_linked_products() {
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

			$seller_data_handler = new Helper\Mkwc_Seller_Data();

			$seller_id = (int) $_GET['seller_id'];

			$s = isset( $_GET['s'] ) && $_GET['s'] ? sanitize_key( $_GET['s'] ) : '';

			$seller_check = $this->mkwc_user_exist_request( $seller_id );

			if ( isset( $seller_check['success'] ) && ! $seller_check['success'] ) {
				return $seller_check;
			}

			if ( ! $s ) {
				$response_handler->set_data(
					array(
						'success' => false,
						'message' => __( 'Search keyword is missing.', 'mobikul_marketplace' ),
					)
				);
			} else {
				$products = $seller_data_handler->mkwc_get_seller_search_products( $seller_id, $s );

				$response_handler->set_data( $products );
			}

			return $response_handler;
		}

		/**
		 * Delete seller product
		 *
		 * @param Array $data
		 * @return $status
		 */
		public function mkwc_delete_seller_product( $data ) {
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

			$product_store_handler = new Helper\Mkwc_Product_store();

			$data = $data->get_params();

			$seller_id = $data['seller_id'];

			$product_id = $data['product_id'];

			$product = wc_get_product( $product_id );

			$seller_check = $this->mkwc_user_exist_request( $seller_id );

			if ( isset( $seller_check['success'] ) && ! $seller_check['success'] ) {
				return $seller_check;
			}

			if ( ! $product ) {
				$response_handler->set_data(
					array(
						'success' => false,
						'message' => __( 'Invalid Product ID.', 'mobikul_marketplace' ),
					)
				);

				return $response_handler;
			}

			$product_author = $product_store_handler->mkwc_get_product_author( $product_id );

			if ( $product_author != $seller_id ) {
				$response_handler->set_data(
					array(
						'success' => false,
						'message' => __( 'You can\'t delete other seller\'s product.', 'mobikul_marketplace' ),
					)
				);

				return $response_handler;
			}

			$product_name = $product->get_title();

			if ( $product->is_type( 'variable' ) ) {
				foreach ( $product->get_children() as $child_id ) {
					$child = wc_get_product( $child_id );
					$child->delete( true );
				}
			}

			$product->delete( true );

			$result = $product->get_id() > 0 ? false : true;

			if ( $result ) {
				$response_handler->set_data(
					array(
						'success' => true,
						'message' => sprintf( __( '%s deleted successfully.', 'mobikul_marketplace' ), $product_name ),
					)
				);
			} else {
				$response_handler->set_data(
					array(
						'success' => false,
						'message' => sprintf( __( '%s cannot be deleted.', 'mobikul_marketplace' ), $product_name ),
					)
				);
			}

			return $response_handler;
		}

		/**
		 * Save edit product data
		 *
		 * @param Array $data
		 * @return $product_data
		 */
		public function mkwc_save_product_edit_data( $data ) {
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

			$product_store_handler = new Helper\Mkwc_Product_store();

			$data = $data->get_params();

			$errors = $this->mkwc_product_validation( $data );

			if ( ! empty( $errors ) ) {
				$response_handler->set_data(
					array(
						'success' => false,
						'message' => $errors,
					)
				);

				return $response_handler;
			}

			$product_id = (int) $data['product_id'];

			$product = wc_get_product( $product_id );

			$seller_id = (int) $data['seller_id'];

			$seller_check = $this->mkwc_user_exist_request( $seller_id );
			if ( isset( $seller_check['success'] ) && ! $seller_check['success'] ) {
				return $seller_check;
			}

			if ( ! $product ) {
				$response_handler->set_data(
					array(
						'success' => false,
						'message' => __( 'Invalid Product ID.', 'mobikul_marketplace' ),
					)
				);

				return $response_handler;
			}

			$product_author = $product_store_handler->mkwc_get_product_author( $product_id );

			if ( $product_author != $seller_id ) {
				$response_handler->set_data(
					array(
						'success' => false,
						'message' => __( 'You can\'t edit other seller\'s product.', 'mobikul_marketplace' ),
					)
				);

				return $response_handler;
			}

			$product_name = strip_tags( $data['product_name'] );

			$description = strip_tags( $data['description'] );

			$short_description = strip_tags( $data['short_description'] );

			$thumbnail_id = $data['thumbnail_id'];

			$regular_price = isset( $data['regular_price'] ) ? $data['regular_price'] : '';

			$sale_price = isset( $data['sale_price'] ) ? $data['sale_price'] : '';

			$product_type = isset( $data['product_type'] ) ? $data['product_type'] : '';

			$category = isset( $data['category'] ) ? $data['category'] : '';

			$product_status = isset( $data['product_status'] ) ? $data['product_status'] : '';

			$manage_stock = isset( $data['manage_stock'] ) ? $data['manage_stock'] : 'no';

			$stock_status = isset( $data['stock_status'] ) ? $data['stock_status'] : '';

			$stock_quantity = ( 'yes' == $manage_stock ) ? $data['stock_quantity'] : '';

			$download_limit = isset( $data['download_limit'] ) && $data['_download_limit'] ? $data['_download_limit'] : '-1';

			$download_expiry = isset( $data['download_expiry'] ) && $data['download_expiry'] ? $data['download_expiry'] : '-1';

			$backorder = isset( $data['backorders'] ) ? $data['backorders'] : 'no';

			$weight = ( isset( $data['weight'] ) && $data['weight'] ) ? wc_format_decimal( $data['weight'] ) : '';

			$length = ( isset( $data['length'] ) && $data['length'] ) ? wc_format_decimal( $data['length'] ) : '';

			$width = ( isset( $data['width'] ) && $data['width'] ) ? wc_format_decimal( $data['width'] ) : '';

			$height = ( isset( $data['height'] ) && $data['height'] ) ? wc_format_decimal( $data['height'] ) : '';

			$upsells = ( isset( $data['upsell_ids'] ) ) ? array_map( 'intval', $data['upsell_ids'] ) : '';

			$crosssells = ( isset( $data['crosssell_ids'] ) ) ? array_map( 'intval', $data['crosssell_ids'] ) : '';

			$product_attributes = ( isset( $data['product_attr'] ) ) ? $data['product_attr'] : array();


			$sold_individually = ( isset( $data['sold_individually'] ) ) ? $data['sold_individually'] : 'no';

			$max_purchase_qty = ( isset( $data['max_purchase_qty'] ) ) ? $data['max_purchase_qty'] : '';

			$product_image_gallery = ( isset( $data['product_image_gallery'] ) ) ? implode( ',', $data['product_image_gallery'] ) : '';

			$product_data = array(
				'ID'                    => $product_id,
				'post_author'           => $seller_id,
				'post_date'             => '',
				'post_date_gmt'         => '',
				'post_content'          => $description,
				'post_status'           => $product_status,
				'post_content_filtered' => $short_description,
				'post_title'            => htmlspecialchars( $product_name ),
				'post_excerpt'          => $short_description,
				'post_type'             => 'product',
				'comment_status'        => 'open',
				'ping_status'           => 'open',
				'post_password'         => '',
				'post_name'             => wp_strip_all_tags( $product_name ),
				'post_modified'         => '',
				'post_modified_gmt'     => '',
				'post_parent'           => '',
				'menu_order'            => '',
				'guid'                  => '',
			);

			if ( wp_update_post( $product_data ) ) {

				update_post_meta( $product_id, '_thumbnail_id', $thumbnail_id );

				update_post_meta( $product_id, '_regular_price', $regular_price );

				if ( $sale_price ) {
					update_post_meta( $product_id, '_sale_price', $sale_price );
					update_post_meta( $product_id, '_price', $sale_price );
				} else {
					update_post_meta( $product_id, '_price', $regular_price );
				}

				update_post_meta( $product_id, '_backorders', $backorder );

				update_post_meta( $product_id, '_stock_status', $stock_status );

				update_post_meta( $product_id, '_manage_stock', $manage_stock );

				update_post_meta( $product_id, '_stock', $stock_quantity );

				update_post_meta( $product_id, '_weight', $weight );

				update_post_meta( $product_id, '_length', $length );

				update_post_meta( $product_id, '_width', $width );

				update_post_meta( $product_id, '_height', $height );

				update_post_meta( $product_id, '_product_image_gallery', $product_image_gallery );

				if ( 'external' == $product_type ) {
					if ( isset( $data['product_url'] ) && isset( $data['button_text'] ) ) {
						$product_url = $data['product_url'];

						$button_text = $data['button_text'];

						update_post_meta( $product_id, '_product_url', esc_url_raw( $product_url ) );

						update_post_meta( $product_id, '_button_text', wc_clean( $button_text ) );
					}
				}

				if ( 'grouped' == $product_type ) {
					if ( isset( $data['grouped_products'] ) ) {
						$grouped_product_data = $data['grouped_products'] ? $data['grouped_products'] : array();

						update_post_meta( $product_id, '_children', $grouped_product_data );
					} else {
						update_post_meta( $product_id, '_children', array() );
					}
				}

				update_post_meta( $product_id, '_upsell_ids', $upsells );

				update_post_meta( $product_id, '_crosssell_ids', $crosssells );

				update_post_meta( $product_id, '_sold_individually', $sold_individually );

				update_post_meta( $product_id, '_wkmp_max_product_qty_limit', $max_purchase_qty );

				wp_set_object_terms( $product_id, $product_type, 'product_type', false );

				$this->mkwc_add_product_category( $category, $product_id );

				$table_name = 'product_approve';

				if ( ! get_option( 'wkmp_seller_allow_publish' ) ) {
					do_action( 'woocommerce_product_notifier_admin', $seller_id, $product_id );
				}

				$response_handler->set_data(
					array(
						'success'    => true,
						'product_id' => $product_id,
						'message'    => '',
					)
				);
			}elseif( !empty($product_attributes) && is_array($product_attributes) ){
				// Loop through product attributes
				foreach( $product_attributes as $attribute_val ) {

					$attr_name = 'pa_'.strtolower($attribute_val['attr_name']);
					$attr_value = $attribute_val['attr_val'];

					$attr_dt = implode( "|", $attr_value);
					// Get product attributes
    				$exist_attributes = get_post_meta( $product_id ,'_product_attributes', true);

					// Loop through product attributes
					foreach( $exist_attributes as $attribute => $attribute_data ) {
						// Target specif attribute  by its name
						if( $attr_name === $attribute_data['name'] ) {

							// Set the new value in the array
							$exist_attributes[$attribute]['value'] = $attr_dt;
							break; // stop the loop
						}
					}

					// Set updated attributes back in database
					update_post_meta( $product_id ,'_product_attributes', $exist_attributes );
				}

				$response_handler->set_data(
					array(
						'success'    => true,
						'product_id' => $product_id,
						'message'    => '',
					)
				);
			} else {
				$response_handler->set_data(
					array(
						'success'    => false,
						'product_id' => $product_id,
						'message'    => __( 'Product not updated.', 'mobikul_marketplace' ),
					)
				);
			}

			return $response_handler;
		}

		/**
		 * Return product data to edit action
		 *
		 * @param Array $data
		 * @return $product_data
		 */
		public function mkwc_get_product_edit_data( $data ) {
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

			$product_store_handler = new Helper\Mkwc_Product_store();

			$seller_data_handler = new Helper\Mkwc_Seller_Data();

			$seller_id = $_GET['seller_id'];

			$product_id = $data['product_id'];

			$product = wc_get_product( $product_id );

			$seller_check = $this->mkwc_user_exist_request( $seller_id );

			if ( isset( $seller_check['success'] ) && ! $seller_check['success'] ) {
				return $seller_check;
			}

			if ( ! $product ) {
				$response_handler->set_data(
					array(
						'success' => false,
						'message' => __( 'Invalid Product ID.', 'mobikul_marketplace' ),
					)
				);

				return $response_handler;
			}

			$product_author = $product_store_handler->mkwc_get_product_author( $product_id );

			if ( $product_author != $seller_id ) {
				$response_handler->set_data(
					array(
						'success' => false,
						'message' => __( 'You can\'t edit other seller\'s product.', 'mobikul_marketplace' ),
					)
				);

				return $response_handler;
			}

			$product_type = $product->get_type();

			$product_data = $seller_data_handler->mkwc_seller_product_prerequisites( $seller_id );

			$product_data = array_merge( $product->get_data(), $product_data );

			$backorder = array(
				array(
					'id'    => 'no',
					'title' => __( 'Do Not Allow', 'mobikul_marketplace' ),
				),
				array(
					'id'    => 'notify',
					'title' => __( 'Allow but notify customer', 'mobikul_marketplace' ),
				),
				array(
					'id'    => 'yes',
					'title' => __( 'Allow', 'mobikul_marketplace' ),
				),
			);

			$stock = array(
				array(
					'id'    => 'instock',
					'title' => __( 'In Stock', 'mobikul_marketplace' ),
				),
				array(
					'id'    => 'outofstock',
					'title' => __( 'Out Of Stock', 'mobikul_marketplace' ),
				),
			);

			if ( get_option( 'wkmp_seller_allow_publish' ) ) {
				$status = array(
					array(
						'id'    => 'publish',
						'title' => __( 'Publish', 'mobikul_marketplace' ),
					),
					array(
						'id'    => 'draft',
						'title' => __( 'Draft', 'mobikul_marketplace' ),
					),
				);
			} else {
				$status = array();
			}

			$product_data['max_purchase_qty'] = $product->get_meta( '_wkmp_max_product_qty_limit' );
			$product_data['thumbnail_id']     = $product_data['image_id'];

			$product_image = $product_store_handler->mkwc_get_image_src( $product_data['thumbnail_id'] );

			$product_data['image'] = $product_image && isset( $product_image[0]['image'] ) ? $product_image[0]['image'] : '';

			$gallery_images = $product_store_handler->mkwc_get_image_src( $product_data['gallery_image_ids'] );

			$product_attributes = get_post_meta( $product_id, '_product_attributes', true );

			$product_attributes = array_map( array( $this, 'mkwc_prepare_attribute_edit_data' ), $product_attributes );

			$sipping_class_ids = get_user_meta( $seller_id, 'shipping-classes', true );

			$shipping_classes = array();

			if ( $sipping_class_ids ) {
				$sipping_class_ids = maybe_unserialize( $sipping_class_ids );
				foreach ( $sipping_class_ids as $id_key => $id_value ) {
					$class              = get_term( $id_value );
					$shipping_classes[] = array(
						'id'    => $id_value,
						'title' => $class->name,
					);
				}
			}

			$downloads_data = array();

			$downloads = $product->get_downloads();

			if ( $downloads ) {
				foreach ( $downloads as $download_key => $download_value ) {
					$downloads_data[] = array(
						'id'   => $download_value['id'],
						'name' => $download_value['name'],
						'file' => $download_value['file'],
					);
				}
			}

			$product_data['upsell_ids'] = array_map( array( $this, 'mkwc_format_related_ids' ), $product_data['upsell_ids'] );

			$product_data['crosssell_ids'] = array_map( array( $this, 'mkwc_format_related_ids' ), $product_data['cross_sell_ids'] );

			$product_data['grouped_products'] = array_map( array( $this, 'mkwc_format_related_ids' ), $product_data['children'] );

			$product_data['children'] = array_map( array( $this, 'mkwc_format_related_ids' ), $product_data['children'] );

			unset( $product_data['cross_sell_ids'] );

			$product_data = array_merge(
				$product_data,
				array(
					'downloads'         => $downloads_data,
					'shipping_classes'  => $shipping_classes,
					'product_type'      => $product_type,
					'attributes'        => $product_attributes,
					'gallery_images'    => $gallery_images,
					'backorder_options' => $backorder,
					'stock_options'     => $stock,
					'status_options'    => $status,
					'success'           => true,
					'message'           => '',
				)
			);

			unset( $product_data['meta_data'] );

			$response_handler->set_data( $product_data );

			return $response_handler;
		}

		/**
		 * Format data for related products
		 *
		 * @param Product_Id $id
		 * @return Array $data
		 */
		public function mkwc_format_related_ids( $id ) {
			$product = wc_get_product( $id );

			if ( ! $product ) {
				return array();
			} else {
				return array(
					'id'    => $id,
					'title' => $product->get_title(),
				);
			}

		}

		public function mkwc_prepare_attribute_edit_data( $product_attribute = '' ) {
			if ( $product_attribute ) {

				$product_attribute['value'] = array_map( 'trim', explode( '|', $product_attribute['value'] ) );
			}
			return $product_attribute;
		}

		/**
		 * Add product callback
		 *
		 * @param Object $data
		 * @return Array $response
		 */
		public function mkwc_add_product( $data ) {
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

			$errors = '';

			$seller_id = $data['seller_id'];

			$errors = $this->mkwc_product_validation( $data );

			$seller_check = $this->mkwc_user_exist_request( $seller_id );

			if ( isset( $seller_check['success'] ) && ! $seller_check['success'] ) {
				return $seller_check;
			}

			if ( ! empty( $errors ) ) {
				$response_handler->set_data(
					array(
						'success' => false,
						'message' => $errors,
					)
				);

				return $response_handler;
			}

			$product_name = strip_tags( $data['product_name'] );

			$description = strip_tags( $data['description'] );

			$short_description = strip_tags( $data['short_description'] );

			$thumbnail_id = $data['thumbnail_id'];

			$sku = $data['sku'];

			$regular_price = isset( $data['regular_price'] ) ? $data['regular_price'] : '';

			$sale_price = isset( $data['sale_price'] ) ? $data['sale_price'] : '';

			$product_type = isset( $data['product_type'] ) ? $data['product_type'] : '';

			$category = isset( $data['category'] ) ? $data['category'] : '';

			$product_data = array(
				'post_author'           => $seller_id,
				'post_date'             => '',
				'post_date_gmt'         => '',
				'post_content'          => $description,
				'post_content_filtered' => $short_description,
				'post_title'            => htmlspecialchars( $product_name ),
				'post_excerpt'          => $short_description,
				'post_type'             => 'product',
				'comment_status'        => 'open',
				'ping_status'           => 'open',
				'post_password'         => '',
				'post_name'             => wp_strip_all_tags( $product_name ),
				'post_modified'         => '',
				'post_modified_gmt'     => '',
				'post_parent'           => '',
				'menu_order'            => '',
				'guid'                  => '',
			);

			$product_id = wp_insert_post( $product_data );

			if ( $product_id ) {
				add_post_meta( $product_id, '_thumbnail_id', $thumbnail_id );

				add_post_meta( $product_id, '_regular_price', $regular_price );

				if ( $sale_price ) {
					add_post_meta( $product_id, '_sale_price', $sale_price );
					add_post_meta( $product_id, '_price', $sale_price );
				} else {
					add_post_meta( $product_id, '_price', $regular_price );
				}

				add_post_meta( $product_id, '_sku', $sku );

				wp_set_object_terms( $product_id, $product_type, 'product_type', false );

				$this->mkwc_add_product_category( $category, $product_id );

				$response_handler->set_data(
					array(
						'success'   => true,
						'message'   => '',
						'productId' => $product_id,
					)
				);
			} else {
				$response_handler->set_data(
					array(
						'success' => false,
						'message' => __( 'Something went wrong, product not created.', 'mobikul_marketplace' ),
					)
				);
			}

			return $response_handler;
		}

		/**
		 * Set product categories
		 *
		 * @param Array $categories, $product_id
		 * @return NULL
		 */
		public function mkwc_add_product_category( $categories, $product_id ) {
			wp_set_object_terms( $product_id, $categories, 'product_cat' );
		}

		/**
		 * Return allowed product types and categories for seller
		 *
		 * @return Array $response
		 */
		public function mkwc_get_product_prerequisites() {
			$response_handler = new \WP_REST_Response();

			$seller_data_handler = new Helper\Mkwc_Seller_Data();

			$seller_id = $_GET['seller_id'];

			$data = $seller_data_handler->mkwc_seller_product_prerequisites( $seller_id );

			$data['success'] = true;

			$data['message'] = '';

			$response_handler->set_data( $data );

			return $response_handler;
		}

		/**
		 * Check sku exist or not
		 *
		 * @param Object $data
		 * @return Array $response
		 */
		public function mkwc_check_product_sku( $data ) {
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

			$seller_data_handler = new Helper\Mkwc_Seller_Data();

			$seller_id = $data['sellerId'];

			$sku = $data['sku'];

			$seller_check = $this->mkwc_user_exist_request( $seller_id );

			if ( isset( $seller_check['success'] ) && ! $seller_check['success'] ) {
				return $seller_check;
			}

			if ( empty( $seller_id ) || empty( $sku ) ) {
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

			if ( strlen( $sku ) < 3 ) {
				return array(
					'success' => false,
					'message' => __( 'Please enter valid product sku, it should be equal or larger than 3 characters.', 'mobikul_marketplace' ),
				);
			}

			$product_store = new Helper\Mkwc_Product_store();

			$sku_exists = $product_store->mkwc_check_sku( $sku );

			if ( $sku_exists ) {
					$response = array(
						'success' => false,
						'message' => __( 'SKU already exist, please select another SKU.', 'mobikul_marketplace' ),
					);
			} else {
					$response = array(
						'success' => true,
						'message' => __( 'SKU is OK', 'mobikul_marketplace' ),
					);
			}

			$response_handler->set_data( $response );

			return $response_handler;
		}

		/**
		 * Save product params validation
		 *
		 * @param Array $data
		 * @return Array $errors
		 */
		public function mkwc_product_validation( $data ) {
			$product_store_handler = new Helper\Mkwc_Product_store();

			if ( isset( $data['regular_price'] ) && ! is_numeric( $data['regular_price'] ) && ! empty( $data['regular_price'] ) ) {
				return __( 'Regular price is not a number.', 'mobikul_marketplace' );
			}

			if ( isset( $data['sale_price'] ) && ! is_numeric( $data['sale_price'] ) && ! empty( $data['sale_price'] ) ) {
				return __( 'Sale price is not a number.', 'mobikul_marketplace' );
			}

			if ( isset( $data['sale_price'] ) && isset( $data['regular_price'] ) && $data['regular_price'] && $data['sale_price'] && $data['sale_price'] >= $data['regular_price'] ) {
				return __( 'Sale price cannot be greater or equal to regular price.', 'mobikul_marketplace' );
			}

			if ( isset( $data['stock_quantity'] ) && ! is_numeric( $data['stock_quantity'] ) && ! empty( $data['stock_quantity'] ) ) {
				return __( 'Stock quantity is not a number.', 'mobikul_marketplace' );
			}

			if ( isset( $data['sku'] ) ) {
				$sku_exists = $product_store_handler->mkwc_check_sku( $data['sku'] );

				if ( $sku_exists ) {
					return __( 'SKU already exist, please select another SKU.', 'mobikul_marketplace' );
				}
			}
		}
	}
}
