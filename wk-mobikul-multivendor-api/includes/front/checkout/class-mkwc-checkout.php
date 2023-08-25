<?php

/**
 * @author Webkul
 * @version 1.0.0
 * This file handles checkout related functions
 */

namespace WkMobikulMultivendorApi\Includes\Front\Checkout;

use WkMobikulMultivendorApi\Includes;
use WkMobikulMultivendorApi\Includes\Front\Checkout\Util;
use WkMobikulMultivendorApi\Helper;

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'Mkwc_Checkout' ) ) {
    /**
     * Checkout class
     */
    class Mkwc_Checkout extends Includes\Mkwc_Authentication implements Util\Mkwc_Checkout_Interface
    {
        public static $base = 'checkout';

        protected $checkout_handler;

        protected $cartdata;

    		protected $amount = 0;

        /**
         * Payment method endpoint callback
         * @param
         * @return $payment_methods
         */
        public function mkwc_payment_methods()
        {
            $validate_auth_key = $this->mkwc_authenticate_request( 0 );

            if ( $validate_auth_key != 'ok' ) {
                return array(
                    'session_id'  => $validate_auth_key,
                    'status'      => 401,
                    'invalid_auth_key_error' => __( 'Please provide valid Auth Key.', 'mobikul_marketplace' ),
                    'success'     => false,
                );
            }
            $payment_gateways = array();

            $payment_methods = WC()->payment_gateways();

            if( ! empty( $payment_methods ) ){
              foreach ( $payment_methods->payment_gateways as $key => $value ) {
                  if( $value->enabled == 'yes' )
                    $payment_gateways[] = array( "title" => $value->title, "description" =>
                    strip_tags($value->description), "method_title" => $value->id );
              }
            }

            if( empty( $payment_gateways ) ){
              return array(
                'success' => false,
                'message' => __( 'Sorry, No Payment gateways Found.', 'mobikul_marketplace' )
              );
            }

            return array(
              'payment_gateways' => $payment_gateways,
              'success' => true,
              'message' => ''
            );
        }

        /**
         * Place Order endpoint callback
         * @param
         * @return $order_details
         */
        public function mkwc_place_order( $data )
        {
            $validate_auth_key = $this->mkwc_authenticate_request();

            if ( $validate_auth_key != 'ok' ) {
                return array(
                    'session_id'  => $validate_auth_key,
                    'status'      => 401,
                    'invalid_auth_key_error' => __( 'Please provide valid Auth Key.', 'mobikul_marketplace' ),
                    'success'     => false,
                );
            }

            global $wpdb;

            wc_transaction_query( 'start' );

            $data        = is_object( $data ) ? $data->get_param('order') : '';

            $customer_id = isset( $data['customer_id'] ) ? $data['customer_id'] : '';

            $guest_id    = isset( $data['guest_id'] ) ? $data['guest_id'] : '';

            $id = ! empty( $customer_id ) ? $customer_id : $guest_id;

            $customer_check = $this->mkwc_user_exist_request( $customer_id );

            if( isset( $customer_check['success'] ) && ! $customer_check['success'] ){
                return $customer_check;
            }

            if( empty( $id ) ){
                return array(
                    'success' => false,
                    'message' => __( 'Sorry, You must provide all the parameters.', 'mobikul_marketplace' )
                );
            }
            $cartdata_handler = new Helper\Mkwc_Cart_Data();

            $checkout_handler = new Helper\Mkwc_Checkout_Data();

            try {
                if ( ! isset( $data ) ) {
                    return array(
                        'status'  => false,
                        'message' => sprintf( __( 'No %1$s data specified to create %1$s', 'mobikul_marketplace' ), 'order' )
                    );
                }

                $table_name = $wpdb->prefix . 'woocommerce_sessions';

                $cart_item  = $cartdata_handler->mkwc_get_cart_custom_details( $id );

                $data['order'] = $this->mkwc_get_customer_details( $cart_item, $id );

                $data = $data['order'];

                $data = apply_filters( 'woocommerce_api_create_order_data', $data, $this );

                // default order args, note that status is checked for validity in wc_create_order()
                $default_order_args = array(
                            'status'        => isset( $data['status'] ) ? $data['status'] : '',
                            'customer_note' => isset( $data['note'] ) ? $data['note'] : null,
                );

                // if creating order for existing customer
                if ( ! empty( $customer_id ) ) {
                        // make sure customer exists
                        if ( false === get_user_by( 'id', $customer_id ) ) {
                    return array(
                        'success' => false,
                        'message' => __( 'Customer ID is invalid.', 'mobikul_marketplace' )
                    );
                        }
                }

                $default_order_args['customer_id'] = $id;

                // create the pending order
                $order = $checkout_handler->mkwc_create_base_order( $default_order_args, $data );

                if ( is_wp_error( $order ) ) {
                    return array(
                        'success' => false,
                        'message' => sprintf( __( 'Cannot create order: %s', 'mobikul_marketplace' ), implode( ', ', $order->get_error_messages() ) )
                    );
                }
                // billing/shipping addresses
                $checkout_handler->mkwc_set_order_addresses( $order, $data );

                $lines = array(
                        'line_item' => 'line_items',
                        'shipping'  => 'shipping_lines',
                        'fee'       => 'fee_lines',
                        'coupon'    => 'coupon_lines',
                );

                foreach ( $lines as $line_type => $line ) {
                    if ( isset( $data[ $line ] ) && is_array( $data[ $line ] ) ) {
                        $set_item = "mkwc_set_{$line_type}";

                        foreach ( $data[ $line ] as $item ) {
                                $this->$set_item( $order, $item, 'create' );
                        }
                    }
                }
                $cart_contents_total = $data['cart_contents_total'];

                $taxes = $data['taxes'];

                $discount_cart_tax = $data['discount_cart_tax'] ? $data['discount_cart_tax'] : 0;

                $shipping_taxes = $data['shipping_taxes'] ? $data['shipping_taxes'] : 0;

                // calculate totals and set them.
                $order->calculate_totals();

                $order_total = $order->get_total();

                update_post_meta( $order->get_id(), '_cart_discount', $this->amount );

                $order->set_discount_tax( $discount_cart_tax );

                $order->set_cart_tax( $taxes );

                $order->set_shipping_tax( $shipping_taxes );

                $order->set_total( $order_total );

                // payment method (and payment_complete() if `paid` == true).
                if ( isset( $data['payment_details'] ) && is_array( $data['payment_details'] ) ) {
                    // method ID & title are required.
                    if ( empty( $data['payment_details']['method_id'] ) || empty( $data['payment_details']['method_title'] ) ) {
                        return array(
                            'success' => false,
                            'message' => __( 'Payment method ID and title are required.', 'mobikul_marketplace' )
                        );
                    }

                    update_post_meta( $order->get_id(), '_payment_method', $data['payment_details']['method_id'] );

                    update_post_meta( $order->get_id(), '_payment_method_title', $data['payment_details']['method_title'] );

                    // mark as paid if set.
                    if ( isset( $data['payment_details']['paid'] ) && true === $data['payment_details']['paid'] ) {
                            $checkout_handler->mkwc_payment_complete( $order, $order->get_id(), isset( $data['payment_details']['transaction_id'] ) ? $data['payment_details']['transaction_id'] : '' );
                    }
                }

                if ( isset( $data['shipping_method'] ) && is_array( $data['shipping_method'] ) ) {
                    update_user_meta( $id, 'mkwc_chosen_shipping_methods', $data['shipping_method'] );
                }

                // set order currency.
                if ( isset( $data['currency'] ) ) {
                    if ( ! array_key_exists( $data['currency'], get_woocommerce_currencies() ) ) {
                        return array(
                            'success' => false,
                            'message' => __( 'Provided order currency is invalid.', 'mobikul_marketplace' )
                        );
                    }
                    update_post_meta( $order->get_id(), '_order_currency', $data['currency'] );
                }

                // set order meta
                if ( isset( $data['order_meta'] ) && is_array( $data['order_meta'] ) ) {
                            $checkout_handler->mkwc_set_order_meta( $order->get_id(), $data['order_meta'] );
                }

                wc_delete_shop_order_transients( $order->get_id() );

                do_action( 'woocommerce_api_create_order', $order->get_id(), $data, $this );

                wc_transaction_query( 'commit' );

                delete_user_meta( $id, 'session_tokens' );

                delete_user_meta( $id, '_woocommerce_persistent_cart_' . get_current_blog_id() );

                $session_id =  get_user_meta( $id, 'user_web_session', true );

                if ( $session_id ) {
                        $wpdb->get_results("DELETE FROM $table_name WHERE session_key = '$session_id'");
                }

                $wpdb->get_results("DELETE FROM $table_name WHERE session_key = '$id'");

                wc_reduce_stock_levels( $order->get_id() );

                WC()->cart = new \WC_Cart();

                WC()->session = new \WC_Session_Handler();

                WC()->cart->empty_cart();

                $order->save();

                $ship_sess = get_user_meta( $id, 'mkwc_shipping_sess_cost', true );

                $ship_chosen = get_user_meta( $id, 'mkwc_chosen_shipping_methods', true );

                WC()->session->set('chosen_shipping_methods', $ship_chosen);

                WC()->session->set('shipping_sess_cost', $ship_sess);

                delete_user_meta( $id, 'mkwc_chosen_shipping_methods' );

                delete_user_meta( $id, 'mkwc_shipping_sess_cost' );

                do_action( 'woocommerce_checkout_order_processed', $order->get_id() );

                return array(
                    'success'  => true,
                    'message'  => '',
                    'order_id' => $order->get_id()
                );
            } catch ( WP_Error $e ) {
                wc_transaction_query( 'rollback' );

                return new \WP_Error( $e->getErrorCode(), $e->getMessage(), array( 'status' => $e->getCode() ) );
            }
        }

        public function mkwc_get_customer_details( $data, $id )
        {
            $payment_method = isset( $data['payment_method'] ) ? $data['payment_method'] : '';

            $shipping_method = isset( $data['shipping_method'] ) ? $data['shipping_method'] : '';

            $payment_method_id = isset( $payment_method['method_title'] ) ? $payment_method['method_title'] : '';

            $payment_method_title = isset( $payment_method['title'] ) ? $payment_method['title'] : '';

            $shipping_method_id = isset( $shipping_method['method_id'] ) ? $shipping_method['method_id'] : '';

            $shipping_method_title = isset( $shipping_method['method_title'] ) ? $shipping_method['method_title'] : '';

            $shipping_lines = isset( $data['shipping_lines'] ) ? $data['shipping_lines'] : '';

            $customer = isset( $data['customer'] ) ? maybe_unserialize( $data['customer'] ) : '';

            $b_first_name = isset( $customer['first_name'] ) ? $customer['first_name'] : '';

            $b_last_name = isset( $customer['last_name'] ) ? $customer['last_name'] : '';

            $b_address_one = isset( $customer['address_1'] ) ? $customer['address_1'] : '';

            $b_address_two = isset( $customer['address_2'] ) ? $customer['address_2'] : '';

            $b_city = ( isset( $customer['city'] ) && ! empty( $customer['city'] ) ) ? $customer['city'] : '';

            $b_state = ( isset( $customer['state'] ) && ! empty( $customer['state'] ) ) ? $customer['state'] : '';

            $b_postcode = ( isset( $customer['postcode'] ) && ! empty( $customer['postcode'] ) ) ? $customer['postcode'] : '';

            $b_country = ( isset( $customer['country'] ) && ! empty( $customer['country'] ) ) ? $customer['country'] : '';

            $b_email = ( isset( $customer['email'] ) && ! empty( $customer['email'] ) ) ? $customer['email'] : '';

            $b_phone = ( isset( $customer['phone'] ) && ! empty( $customer['phone'] ) ) ? $customer['phone'] : '';

            $s_first_name = ( isset( $customer['shipping_first_name'] ) && ! empty( $customer['shipping_first_name'] ) ) ? $customer['shipping_first_name'] : '';

            $s_last_name = ( isset( $customer['shipping_last_name'] ) && ! empty( $customer['shipping_last_name'] ) ) ? $customer['last_name'] : '';

            $s_address_one = ( isset( $customer['shipping_address_1'] ) && ! empty( $customer['shipping_address_1'] ) ) ? $customer['shipping_address_1'] : '';

            $s_address_two = ( isset( $customer['shipping_address_2'] ) && ! empty( $customer['shipping_address_2'] ) ) ? $customer['shipping_address_2'] : '';

            $s_city = ( isset( $customer['shipping_city'] ) && ! empty( $customer['shipping_city'] ) ) ? $customer['shipping_city'] : '';

            $s_state = ( isset( $customer['shipping_state'] ) && ! empty( $customer['shipping_state'] ) ) ? $customer['shipping_state'] : '';

            $s_postcode = ( isset( $customer['shipping_postcode'] ) && ! empty( $customer['shipping_postcode'] ) ) ? $customer['shipping_postcode'] : '';

            $s_country = ( isset( $customer['shipping_country'] ) && ! empty( $customer['shipping_country'] ) ) ? $customer['shipping_country'] : '';

            $cart_contents_total = isset( $data['cart_contents_total'] ) ? $data['cart_contents_total'] : '';

            $total = isset( $data['total'] ) ? $data['total'] : '';

            $subtotal = isset( $data['subtotal'] ) ? $data['subtotal'] : '';

            $subtotal_ex_tax = isset( $data['subtotal_ex_tax'] ) ? $data['subtotal_ex_tax'] : '';

            $tax_total = isset( $data['tax_total'] ) ? $data['tax_total'] : '';

            $taxes = isset( $data['taxes'] ) ? $data['taxes'] : '';

            $shipping_taxes = isset( $data['shipping_taxes'] ) ? $data['shipping_taxes'] : '';

            $discount_cart = isset( $data['discount_cart'] ) ? $data['discount_cart'] : '';

            $discount_cart_tax = isset( $data['discount_cart_tax'] ) ? $data['discount_cart_tax'] : '';

            $shipping_total = isset( $data['shipping_total'] ) ? $data['shipping_total'] : '';

            $shipping_tax_total = isset( $data['shipping_tax_total'] ) ? $data['shipping_tax_total'] : '';

            $fees = isset( $data['fees'] ) ? $data['fees'] : '';

            $fee_total = isset( $data['fee_total'] ) ? $data['fee_total'] : array();

            $applied_coupons = isset( $data['applied_coupons'] ) ? $data['applied_coupons'] : array();

            $coupon_discount_amounts = isset( $data['coupon_discount_amounts'] ) ? $data['coupon_discount_amounts'] : array();

            $coupon_lines = array();
            if( ! empty( $coupon_discount_amounts ) ){
                foreach ( $coupon_discount_amounts as $key => $value ) {
                    $coupon_lines[$key]['amount'] = $value['coupon_amount'];
                    $coupon_lines[$key]['code'] = $value['coupon_code'];
                }
            }

            $coupon_discount_tax_amounts = isset( $data['coupon_discount_tax_amounts'] ) ? $data['coupon_discount_tax_amounts'] : '';

            $removed_cart_contents = isset( $data['removed_cart_contents'] ) ? $data['removed_cart_contents'] : array();

            $shipping_methods = isset( $data['shipping_methods'] ) ? $data['shipping_methods'] : array();

            $tax_total = ( $tax_total && $discount_cart_tax ) ? $tax_total - $discount_cart_tax : $tax_total;

            $data = array(
                    'customer_id' => $id,
                    'payment_details' => array(
                        'method_id' => $payment_method_id,
                        'method_title' => $payment_method_title,
                        'paid' => $payment_method_id ? true : false
                    ),
                    'shipping_method' => array(
                        'method_id' => $shipping_method_id,
                        'method_title' => $shipping_method_title
                    ),
                    'applied_coupons' => array( $applied_coupons ),
                    'coupon_discount_amounts' => array( $coupon_discount_amounts ),
                    'coupon_discount_tax_amounts' => $coupon_discount_tax_amounts,
                    'removed_cart_contents' => array( $removed_cart_contents ),
                    'shipping_methods' => array( $shipping_methods ),
                    'cart_contents_total' => $cart_contents_total,
                    'total' => $total,
                    'subtotal'  => $subtotal,
                    'subtotal_ex_tax' => $subtotal_ex_tax,
                    'tax_total' => $tax_total,
                    'taxes' => $taxes,
                    'shipping_taxes'  => $shipping_taxes,
                    'discount_cart' => $discount_cart,
                    'discount_cart_tax' => $discount_cart_tax,
                    'shipping_total'  => $shipping_total,
                    'shipping_tax_total'  => $shipping_tax_total,
                    'fee_total' => $fee_total,
                    'fees'  => $fees,
                    'shipping_lines' => array( $shipping_lines ),
                    'coupon_lines'   => $coupon_lines,
                    'line_items'   => $data['cart'],
                    'billing_address' => array(
                        'first_name' => $b_first_name,
                        'last_name'  => $b_last_name,
                        'address_1'  => $b_address_one,
                        'address_2'  => $b_address_two,
                        'city'       => $b_city,
                        'state'      => $b_city,
                        'postcode'   => $b_postcode,
                        'country'    => $b_country,
                        'email'      => $b_email,
                        'phone'      => $b_phone
                    ),
                    'shipping_address' => array(
                        'first_name' => $s_first_name,
                        'last_name'  => $s_last_name,
                        'address_1'  => $s_address_one,
                        'address_2'  => $s_address_two,
                        'city'       => $s_city,
                        'state'      => $s_state,
                        'postcode'   => $s_postcode,
                        'country'    => $s_country
                    )
            );
            return $data;
        }

        /**
         * Set line items
         * @param $order
         * @return $items
         */
        public function mkwc_set_line_item( $order, $item, $action )
        {
            $creating  = ( 'create' === $action );

            $item_args = array();

            // product is always required
            if ( ! isset( $item['product_id'] ) && ! isset( $item['sku'] ) ) {
                return array(
                  'success' => false,
                  'message' => __( 'Product ID or SKU is required', 'mobikul_marketplace' )
                );
            }

            // when updating, ensure product ID provided matches
            if ( 'update' === $action ) {
                $item_product_id   = wc_get_order_item_meta( $item['id'], '_product_id' );

                $item_variation_id = wc_get_order_item_meta( $item['id'], '_variation_id' );
                if ( $item['product_id'] != $item_product_id && $item['product_id'] != $item_variation_id ) {
                    return array(
                        'success' => false,
                        'message' => __( 'Product ID provided does not match this line item', 'mobikul_marketplace' )
                    );
                }
            }

            if ( isset( $item['product_id'] ) ) {
                $product_id = $item['product_id'];
            } elseif ( isset( $item['sku'] ) ) {
                $product_id = wc_get_product_id_by_sku( $item['sku'] );
            }
            $variation_id = 0;

            $product = wc_get_product( $item['product_id'] );
            if ( ! empty( $item['variation_id'] ) ) {
                $variation_id = $item['variation_id'];

                $variation = $product->get_child( $variation_id );

                if ( ! empty( $variation ) ) {
                    $variations = array();

                    foreach ( $variation->get_variation_attributes() as $attribute_name => $attribute ) {
                        $variations[ $attribute_name ] = $attribute;
                    }
                    $item_args['variation'] = $variations;
                }
            }

            $product = wc_get_product( $variation_id ? $variation_id : $product_id );

            // must be a valid WC_Product
            if ( ! is_object( $product ) ) {
                return array(
                  'success' => false,
                  'message' => __( 'Product is invalid', 'mobikul_marketplace' )
                );
            }

            // quantity must be positive float
            if ( isset( $item['quantity'] ) && floatval( $item['quantity'] ) <= 0 ) {
                return array(
                  'success' => false,
                  'message' => __( 'Product quantity must be a positive float', 'mobikul_marketplace' )
                );
            }

            // quantity is required when creating
            if ( $creating && ! isset( $item['quantity'] ) ) {
                return array(
                  'success' => false,
                  'message' => __( 'Product quantity is required', 'mobikul_marketplace' )
                );
            }
            if ( $creating ) {
                $line_item = new \WC_Order_Item_Product();
            } else {
                $line_item = new \WC_Order_Item_Product( $item['id'] );
            }
            $line_item->set_product( $product );

            $line_item->set_order_id( $order->get_id() );

            if ( isset( $item['quantity'] ) ) {
                $line_item->set_quantity( $item['quantity'] );
            }

            if ( isset( $item['line_total'] ) ) {
                $line_item->set_total( $item['line_total'] );
            }

            if ( isset( $item['line_tax'] ) ) {
                $line_item->set_total_tax( $item['line_tax'] );
            }
            if ( isset( $item['line_subtotal'] ) ) {
                $line_item->set_subtotal( $item['line_subtotal'] );
            }
            if ( isset( $item['line_subtotal_tax'] ) ) {
                $line_item->set_subtotal_tax( $item['line_subtotal_tax'] );
            }
            if ( $variation_id ) {
                $line_item->set_variation_id( $variation_id );

                $line_item->set_variation( $item['variations'] );
            }
            apply_filters( 'mkwc_set_custom_meta_data', $line_item, $item );

            // Save or add to order.
            if ( $creating ) {
                $order->add_item( $line_item );
            } else {
                $item_id = $line_item->save();


                if ( ! $item_id ) {
                  return array(
                    'success' => false,
                    'message' => __( 'Cannot create line item, try again.', 'mobikul_marketplace' ) );
                }
            }
      }

      /**
       * Set shipping
       * @param $order
       * @return $items
       */
        public function mkwc_set_shipping( $order, $shipping, $action )
        {
            WC()->session = new \WC_Session_Handler();

            WC()->cart = new \WC_Cart();

        		if ( isset( $shipping['total'] ) ) {
        				$symbol = html_entity_decode( get_woocommerce_currency_symbol( $order->get_currency() ) );
        				preg_match_all( '!\d+!', $shipping['total'], $total );
        				$shipping['total'] = str_replace( $symbol, '', $shipping['total'] );
        		}
        		if ( 'create' === $action ) {
                $shipping_taxes = \WC_Tax::calc_shipping_tax( $shipping['total'], \WC_Tax::get_shipping_tax_rates() );

              	$rate = new \WC_Shipping_Rate( $shipping['method_id'], isset( $shipping['method_title'] ) ? $shipping['method_title'] : '', isset( $shipping['total'] ) ? floatval( $shipping['total'] ) : 0, $shipping_taxes, $shipping['method_id'] );

          			$item = new \WC_Order_Item_Shipping();

          			$item->set_order_id( $order->get_id() );

          			$item->set_shipping_rate( $rate );

          			$order->add_item( $item );
        		} else {
          			$item = new \WC_Order_Item_Shipping( $shipping['id'] );

          			if ( isset( $shipping['method_id'] ) ) {
          				$item->set_method_id( $shipping['method_id'] );
          			}
          			if ( isset( $shipping['method_title'] ) ) {
          				$item->set_method_title( $shipping['method_title'] );
          			}
          			if ( isset( $shipping['total'] ) ) {
          				$currency = get_woocommerce_currency_symbol();

          				$shipping['total'] = str_replace( $currency, '', $shipping['total'] );

          				$item->set_total( floatval( $shipping['total'] ) );
          			}
    			      $shipping_id = $item->save();
          			if ( ! $shipping_id ) {
            				return array(
                      'success' => false,
                      'message' => __( 'Cannot update shipping method, try again.', 'mobikul_marketplace' )
                    );
          			}
        		}
      	}

        public function mkwc_set_coupon( $order, $coupon, $action )
        {
        		// coupon amount must be positive float
        		if ( isset( $coupon['amount'] ) && floatval( $coupon['amount'] ) < 0 ) {
                return array(
                    'success' => false,
                    'message' => __( 'Coupon discount total must be a positive amount.', 'mobikul_marketplace' )
                );
        		}

        		if ( 'create' === $action ) {
        			// coupon code is required
        			if ( empty( $coupon['code'] ) ) {
                  return array(
                      'success' => false,
                      'message' => __( 'Coupon code is required.', 'mobikul_marketplace' )
                  );
        			}
        			if( isset( $coupon['amount'] ) ) {
      				     $currency = html_entity_decode(get_woocommerce_currency_symbol());
	                 $amount = str_replace($currency, '', $coupon['amount']);
        			}
        			$coupon_id = $order->add_coupon( $coupon['code'], isset( $coupon['amount'] ) ? floatval( $amount ) : 0, floatval(1) );
              if ( ! $coupon_id ) {
                  return array(
                      'success' => false,
                      'message' => __( 'Cannot create coupon, try again.', 'mobikul_marketplace' )
                  );
        			} else {
		              $this->amount += floatval( $amount );
        			}
        		} else {
        			  $coupon_args = array();

          			if ( isset( $coupon['code'] ) ) {
      				      $coupon_args['code'] = $coupon['code'];
          			}
          			if ( isset( $coupon['amount'] ) ) {
      				      $coupon_args['discount_amount'] = floatval( $coupon['amount'] );
          			}
      			    $coupon_id = $order->update_coupon( $coupon['id'], $coupon_args );

          			if ( ! $coupon_id ) {
                    return array(
                        'success' => false,
                        'message' => __( 'Cannot update coupon, try again.', 'mobikul_marketplace' )
                    );
          			}
        		}
      	}
    }
}
