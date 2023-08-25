<?php

/**
 * @author Webkul
 * @version 1.0.0
 * This file handles order store functions.
 */

namespace WkMobikulMultivendorApi\Helper;
use WkMobikulMultivendorApi\Helper;

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'Mkwc_Checkout_Data' ) ) {
    /**
     *
     */
    class Mkwc_Checkout_Data implements Util\Mkwc_Checkout_Data_Interface
    {
        protected $wpdb;

        protected $general_data_handler;

        public function __construct()
        {
            global $wpdb;

            $this->wpdb = $wpdb;
        }

        /**
         * Get orders by customer id
         * @param $customer_id
         * @return $order_ids
         */
        public function mkwc_create_base_order( $args, $data ) {
	           return wc_create_order( $args );
      	}

        /**
         * Set order address
         * @param $order
         * @return $data
         */
        public function mkwc_set_order_addresses( $order, $data )
        {
        		$address_fields = array(
          			'first_name',
          			'last_name',
          			'company',
          			'email',
          			'phone',
          			'address_1',
          			'address_2',
          			'city',
          			'state',
          			'postcode',
          			'country',
        		);

        		$billing_address = $shipping_address = array();
        		// billing address
        		if ( isset( $data['billing_address'] ) && is_array( $data['billing_address'] ) ) {
          			foreach ( $address_fields as $field ) {
            				if ( isset( $data['billing_address'][ $field ] ) ) {
        					       $billing_address[ $field ] = wc_clean( $data['billing_address'][ $field ] );
            				}
          			}
      			    unset( $address_fields['email'] );

      			    unset( $address_fields['phone'] );
        		}
        		// shipping address
        		if ( isset( $data['shipping_address'] ) && is_array( $data['shipping_address'] ) ) {
          			foreach ( $address_fields as $field ) {
            				if ( isset( $data['shipping_address'][ $field ] ) ) {
        					       $shipping_address[ $field ] = wc_clean( $data['shipping_address'][ $field ] );
            				}
          			}
        		}
      		  $this->mkwc_set_address( $order, $billing_address, 'billing' );

            $this->mkwc_set_address( $order, $shipping_address, 'shipping' );
      	}

        /**
        * Set address by id
        * @param $customer_id, $address
        * @return $address
        */
        public function mkwc_set_address( $order, $posted, $type = 'billing' )
        {
      		foreach ( $posted as $key => $value ) {
      			if ( is_callable( array( $order, "set_{$type}_{$key}" ) ) ) {
      				$order->{"set_{$type}_{$key}"}( $value );
      			}
      		}
      	}

        /**
         * Set order meta by order id
         * @param $order_id
         * @return $order_meta
         */
        public function mkwc_set_order_meta( $order_id, $order_meta )
        {
            foreach ( $order_meta as $meta_key => $meta_value ) {
                if ( is_string( $meta_key ) && ! is_protected_meta( $meta_key ) && is_scalar( $meta_value ) ) {
                    update_post_meta( $order_id, $meta_key, $meta_value );
                }
            }
        }

        /**
         * Get order by customer id
         * @param $customer_id
         * @return $order
         */
        public function mkwc_get_order( $id, $fields = null )
        {
            $general_data_handler = new Helper\Mkwc_General_Data();

            if ( is_wp_error( $id ) )
              return $id;
            $id = ( is_object( $id ) ) ? $id->get_param( 'id' ) : $id;

            $order = wc_get_order( $id );

            if( ! $order ){
                return new \WP_Error( 'json_no_result_found', __( 'Sorry, No Result Found.' ), array( 'status' => 401 ) );
            }

            $order_post = get_post( $id );

            $customer_id = $order->get_user_id();

            $customer  = new \WC_Customer( $customer_id );

            $order_data = array(
              'id'                        => $order->get_id(),
              'order_number'              => $order->get_order_number(),
              'created_at'                => $order_post->post_date_gmt ,
              'updated_at'                => $order_post->post_modified_gmt ,
              'completed_at'              => $order->get_date_completed(),
              'status'                    => $order->get_status(),
              'currency'                  => $order->get_currency(),
              'total'                     => $general_data_handler->mkwc_get_formatted_price( $order->get_total() ),
              'subtotal'                  => $general_data_handler->mkwc_get_formatted_price( $this->mkwc_get_order_subtotal( $order ) ),
              'total_line_items_quantity' => $order->get_item_count(),
              'total_tax'                 => wc_format_decimal( $order->get_total_tax(), 2 ) ? $general_data_handler->mkwc_get_formatted_price( $order->get_total_tax() ) : '',
              'total_shipping'            => wc_format_decimal( $order->get_total_shipping(), 2 ) ? $general_data_handler->mkwc_get_formatted_price( $order->get_total_shipping() ) : '',
              'cart_tax'                  => wc_format_decimal( $order->get_cart_tax(), 2 ) ? $general_data_handler->mkwc_get_formatted_price( $order->get_cart_tax() ) : '',
              'shipping_tax'              => wc_format_decimal( $order->get_shipping_tax(), 2 ) ? $general_data_handler->mkwc_get_formatted_price( $order->get_shipping_tax() ) : '',
              'total_discount'            => wc_format_decimal( $order->get_total_discount(), 2 ) ? $general_data_handler->mkwc_get_formatted_price( $order->get_total_discount() ) : '',
              'shipping_methods'          => $order->get_shipping_method(),
              'payment_details' => array(
                'method_id'    => $order->get_payment_method(),
                'method_title' => $order->get_payment_method_title(),
                'paid'         => $order->is_paid(),
              ),
              'billing_address' => array(
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
              'shipping_address' => array(
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
            foreach( $order->get_items() as $item_id => $item ) {

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
              $image_url  =$this->mkwc_get_product_data( wc_get_product( $product->get_id() ) );

              $order_data['line_items'][] = array(
                'id'         => $item_id,
                'subtotal'   => wc_format_decimal( $order->get_line_subtotal( $item ), 2 ) ? $general_data_handler->mkwc_get_formatted_price( $order->get_line_subtotal( $item ) ) : '',
                'total'      => wc_format_decimal( $order->get_line_total( $item ), 2 ) ? $general_data_handler->mkwc_get_formatted_price( $order->get_line_total( $item ) ) : '',
                'total_tax'  => wc_format_decimal( $order->get_line_tax( $item ), 2 ) ? $general_data_handler->mkwc_get_formatted_price( $order->get_line_tax( $item ) ) : '',
                'price'      => wc_format_decimal( $order->get_item_total( $item ), 2 ) ? $general_data_handler->mkwc_get_formatted_price( $order->get_item_total( $item ) ) : '',
                'quantity'   => (int) $item['qty'],
                'tax_class'  => ( ! empty( $item['tax_class'] ) ) ? $item['tax_class'] : null,
                'name'       => html_entity_decode( $item['name'] ),
                'product_id'   => $product ? $product->get_id() : '',
                'product_image'=> $product ? get_the_post_thumbnail_url( $product->get_id(), 'post-thumbnail' ) : '',
                'sku'          => $product_sku,
                'variation'    => $item_meta,
              );
            }

            // add shipping
            foreach ( $order->get_shipping_methods() as $shipping_item_id => $shipping_item ) {
              $order_data['shipping_lines'][] = array(
                'id'           => $shipping_item_id,
                'method_id'    => $shipping_item['method_id'],
                'method_title' => $shipping_item['name'],
                'total'        => wc_format_decimal( $shipping_item['cost'], 2 ) ? $general_data_handler->mkwc_get_formatted_price( $shipping_item['cost'] ) : ''
              );
            }

            // add taxes
            foreach ( $order->get_tax_totals() as $tax_code => $tax ) {
                $order_data['tax_lines'][] = array(
                    'code'     => $tax_code,
                    'title'    => $tax->label,
                    'total'    => wc_format_decimal( $tax->amount, 2 ) ? $general_data_handler->mkwc_get_formatted_price( $tax->amount ) : '',
                    'compound' => (bool) $tax->is_compound,
                );
            }

            // add fees
            foreach ( $order->get_fees() as $fee_item_id => $fee_item ) {
                $order_data['fee_lines'][] = array(
                  'id'        => $fee_item_id,
                  'title'     => $fee_item['name'],
                  'tax_class' => ( ! empty( $fee_item['tax_class'] ) ) ? $fee_item['tax_class'] : null,
                  'total'     => wc_format_decimal( $order->get_line_total( $fee_item ), 2 ) ? $general_data_handler->mkwc_get_formatted_price( $order->get_line_total( $fee_item ) ) : '',
                  'total_tax' => wc_format_decimal( $order->get_line_tax( $fee_item ), 2 ) ? $general_data_handler->mkwc_get_formatted_price( $order->get_line_tax( $fee_item ) ) : ''
                );
            }

            // add coupons
            foreach ( $order->get_items( 'coupon' ) as $coupon_item_id => $coupon_item ) {

              $order_data['coupon_lines'][] = array(
                'id'     => $coupon_item_id,
                'code'   => $coupon_item['name'],
                'amount' => wc_format_decimal( $coupon_item['discount_amount'], 2 ) ? $general_data_handler->mkwc_get_formatted_price( $coupon_item['discount_amount'] ) : ''
              );
            }

            return array( 'order' => apply_filters( 'woocommerce_api_order_response', $order_data, $order, $fields ) );
        }

        /**
         * Get product image
         * @param $product
         * @return $data
         */
        public function mkwc_get_product_data( $product )
        {
        		if( empty( $_GET['width'] ) ){
      			    return array(
                    'success' => false,
                    'message' => __( 'Sorry, you must provide all parameters.', 'mobikul_marketplace' )
                );
        		}
      		  $height = 0.5 * $_GET['width'];

            $size = array( "width" => intval($_GET['width'] ), "height" => intval($height), "crop" => intval(0) );

            $image = $this->mkwc_image_downsize( $product->get_id(), $size );

            return $image[0];
      	}

        /**
         * Download product iamge by product id
         * @param $product_id
         * @return $image
         */
      	public function mkwc_image_downsize( $id, $size )
        {
  			      $meta = wp_get_attachment_metadata( get_post_thumbnail_id( $id ) );

    			    $wanted_width = $wanted_height = 0;

    			    if( empty( $meta['file'] ) )
                  return false;

			        $dir = wp_upload_dir();

              $image_path = $dir['baseurl'] . '/' . $meta['file'];

              $file_path  = $dir['basedir'] . '/' . $meta['file'];

      	      $extension  = pathinfo( $file_path, PATHINFO_EXTENSION );

      	      $extension  = '.' . $extension;

      	      $check_file_name = explode( $extension, $file_path )[0];

              $check_file_name .= '-' . $size['width'] . 'x' . $size['height'] . $extension;

            	if( file_exists( $check_file_name  ) ){
              		 $image_path  = explode( $extension, $image_path )[0];

              		 $image_path .= '-' . $size['width'] . 'x' . $size['height'] . $extension;

              		 return array(
    						         $image_path,
                         $size['width'],
    				             $size['height'],
    				             true,
        					 );
              }

      			 if ( empty( $meta ) )
                return false;
	            // get $size dimensions
    			    global $_wp_additional_image_sizes;

      				$wanted_width = $size ['width'];

              $wanted_height = $size ['height'];

              $wanted_crop = isset( $size ['crop'] ) ? $size ['crop'] : false;

      				// image size not found, create it
      				$attachment_path = get_attached_file( get_post_thumbnail_id( $id ) );

      				$image_editor = wp_get_image_editor( $attachment_path );

      				if ( ! is_wp_error( $image_editor ) ) {
        					 $image_editor->resize( $wanted_width, $wanted_height, false );

        					 $result_image_size = $image_editor->get_size();

                   $result_width = $wanted_width;

                   $result_height = $wanted_height;

        					 $suffix = $wanted_width . 'x' . $wanted_height;

                   $filename = $image_editor->generate_filename( $suffix );

        					 $image_editor->save( $filename );

        					 $result_filename = wp_basename( $filename );

        					 $meta['sizes'][ 'mobile-thumbnail' ] = array(
          						 'file'      => $result_filename,
          						 'width'     => $wanted_width,
          						 'height'    => $wanted_height,
          						 'mime-type' => get_post_mime_type( get_post_thumbnail_id( $id ) ),
        					 );

        					 wp_update_attachment_metadata( get_post_thumbnail_id( $id ), $meta );

        					 $img_url = wp_get_attachment_url( get_post_thumbnail_id( $id ) );

        					 $img_url_basename = wp_basename( $img_url );

        					 $img_url = str_replace( $img_url_basename, $result_filename, $img_url );

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
           * Check order payment complete by customer id
           * @param $customer_id
           * @return $order_ids
           */
          public function mkwc_payment_complete( $order, $id, $transaction_id = '' )
          {
              do_action( 'woocommerce_pre_payment_complete', $id );

              if ( null !== WC()->session ) {
                  WC()->session->set( 'order_awaiting_payment', false );
              }

              $valid_order_statuses = apply_filters( 'woocommerce_valid_order_statuses_for_payment_complete', array( 'on-hold', 'pending', 'failed', 'cancelled' ), $this );

              if ( $id && $this->mkwc_has_status( $order, $valid_order_statuses ) ) {
                $order_needs_processing = false;

                  if ( sizeof( $order->get_items() ) > 0 ) {
                      foreach ( $order->get_items() as $item ) {
                          if ( $_product = $order->get_product_from_item( $item ) ) {
                              $virtual_downloadable_item = $_product->is_downloadable() && $_product->is_virtual();

                              if ( apply_filters( 'woocommerce_order_item_needs_processing', ! $virtual_downloadable_item, $_product, $id ) ) {
                                $order_needs_processing = true;

                                break;
                              }
                          } else {
                              $order_needs_processing = true;

                              break;
                          }
                      }
                  }
                  $order->update_status( apply_filters( 'woocommerce_payment_complete_order_status', $order_needs_processing ? 'processing' : 'completed', $id ) );

                  add_post_meta( $id, '_paid_date', current_time( 'mysql' ), true );

                  if ( ! empty( $transaction_id ) ) {
                      update_post_meta( $id, '_transaction_id', $transaction_id );
                  }
              } else {
                  do_action( 'woocommerce_payment_complete_order_status_' . $this->mkwc_get_status( $order ), $id );
              }
          }

          /**
           * Check order status
           * @param $order
           * @return $status
           */
          public function mkwc_has_status( $order, $status )
          {
    		      return apply_filters( 'woocommerce_order_has_status', ( is_array( $status ) && in_array( $this->mkwc_get_status( $order ), $status ) ) || $this->mkwc_get_status( $order ) === $status ? true : false, $order, $status );
        	}

          /**
           * Get order status
           * @param $order
           * @return $status
           */
          public function mkwc_get_status( $order )
          {
              $post_status = get_post_status( $order->get_id() );

              return apply_filters( 'woocommerce_order_get_status', 'wc-' === substr( $post_status, 0, 3 ) ? substr( $post_status, 3 ) : $post_status, $order );
          }

          /**
           * Get order subtotal
           * @param $order
           * @return $subtotal
           */
          public function mkwc_get_order_subtotal( $order )
          {
              $subtotal = 0;
              // subtotal
              foreach ( $order->get_items() as $item ) {
                $subtotal += ( isset( $item['line_subtotal'] ) ) ? $item['line_subtotal'] : 0;
              }
              return $subtotal;
          }
    }
}
