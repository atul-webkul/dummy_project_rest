<?php

/**
 * @author Webkul
 * @version 1.0.0
 * This file handles checkout related functions
 */

namespace WkMobikulMultivendorApi\Includes\Front\Cart;

use WkMobikulMultivendorApi\Includes;
use WkMobikulMultivendorApi\Includes\Front\Cart\Util;
use WkMobikulMultivendorApi\Helper;

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'Mkwc_Cart' ) ) {
    /**
     * Cart add product class
     */
    class Mkwc_Cart extends Includes\Mkwc_Authentication implements Util\Mkwc_Cart_Interface
    {
        public static $base = 'cart';

        protected $cartdata;

        /**
         * cart add endpoint callback
         * @param
         * @return $cart_key
         */
        public function mkwc_cart( $product_data, $auth = true )
        {
            if( $auth ){
              $validate_auth_key = $this->mkwc_authenticate_request();

              if ( $validate_auth_key != 'ok' ) {
                return array(
                  'session_id'  => $validate_auth_key,
                  'status'      => 401,
                  'invalid_auth_key_error' => esc_html__( 'Please provide valid Auth Key.', 'mobikul_marketplace' ),
                  'success'     => false,
                );
              }
            }
            $request = isset( $_GET['request'] ) ? $_GET['request'] : 'add';

            if( empty( $request ) ){
               return array(
                 'success' => false,
                 'message' => __( 'Sorry, you must provide all parameters.', 'mobikul_marketplace' )
               );
            }
            $cartdata_handler = new Helper\Mkwc_Cart_Data();

            $data = is_object( $product_data ) ? $product_data->get_json_params() : $product_data;

            $product_id   = isset( $data['product_id'] ) ? $data['product_id'] : '';

            $variation_id = isset( $data['variation_id'] ) ? $data['variation_id'] : 0;

            $add_variations = isset( $data['variations'] ) ? $data['variations'] : [];

            $variations = $cust_variations = [];
            if( $request == 'add' ){
              if ( $variation_id ) {
                $productvariation = wc_get_product( $variation_id );

                $variations = $productvariation->get_variation_attributes();
              }
              if( is_array( $variations ) && is_array( $add_variations ) && ( count( $variations ) == count( $add_variations ) ) ){
                $variations   = array_combine( array_keys( $variations ), array_values( $add_variations ) );
              }
            } else {
              foreach ( $add_variations as $adkey => $advalue ) {
                $cust_variations[] = $advalue['option'];
              }
              if ( $variation_id ) {
                $productvariation = wc_get_product( $variation_id );

                $variations = $productvariation->get_variation_attributes();
              }
              if( is_array( $variations ) && is_array( $cust_variations ) && ( count( $variations ) == count( $cust_variations ) ) ){
                $variations   = array_combine( array_keys( $variations ), array_values( $cust_variations ) );
              }
            }
            $quantity     = isset( $data['quantity'] ) ? $data['quantity'] : '';

            $customer_id  = isset( $data['customer_id'] ) ? $data['customer_id'] : '';

            $guest_user   = isset( $data['guest_id'] ) ? $data['guest_id'] : '';

            $coupons      = isset( $data['coupons'] ) ? $data['coupons'] : array();

            $customer_check = $this->mkwc_user_exist_request( $customer_id );
            
            if( isset( $customer_check['success'] ) && ! $customer_check['success'] ){
                return $customer_check;
            }

            if ( empty( $product_id ) ){
                return array(
                  "success" => false,
                  "message" => __( 'Sorry, you must provide all parameters.', 'mobikul_marketplace' )
                );
            }

            $product_id = strpos( $product_id, ',' ) ? explode( ',', $product_id ) : $product_id;

            $quantity = strpos( $quantity, ',' ) ? explode( ',', $quantity ) : $quantity;

            $user_id = ! empty( $customer_id ) ? $customer_id : $guest_user;

            if ( ! $user_id ) {
                $user_id = $cartdata_handler->generate_customer_id();
            }

            if ( is_array( $product_id ) && ! empty( $product_id ) ) {
          			foreach ( $product_id as $pkey => $pvalue ) {
        						$new_quantity = $quantity;

                    if ( is_array( $quantity ) )
                        $new_quantity = $quantity[ $pkey ];

        						$response = maybe_unserialize( $cartdata_handler->mkwc_cart_request_handler( $pvalue, $user_id, $new_quantity, $variation_id, $variations, $coupons, $datacount = '' ) );

                    $coupons = array();
          			}
        		} else {
        				$response = maybe_unserialize( $cartdata_handler->mkwc_cart_request_handler( $product_id, $user_id, $quantity, $variation_id, $variations, $coupons, $datacount = '' ) );
        		}
            if ( ! is_array( $response ) ) {
                return array(
                  "success" => false,
                  "message" => __( 'Error Updating Cart.', 'mobikul_marketplace' )
                );
            }

            if ( ! empty( $response['cart'] ) ) {
                $product_count = count( $response['cart'] );

                foreach ( $response['cart'] as $ckey => $cvalue ) {
                    $product_count += $cvalue['quantity'] - 1;
                }
                $response['count'] = $product_count;
            } else if( array_key_exists( 'status', $response ) ){
                $p_count = 0;

                foreach ( $response['cart_data'] as $cdkey => $cdvalue ) {
                  $p_count += $cdvalue['quantity'];
                }
                $response['count'] = $p_count;

                unset( $response['cart_data'] );
            }
            if( ! empty( $response['cart'] ) ){
                if( empty( $customer_id ) && empty( $guest_user ) ){
                    $response['guest_id'] = $user_id;
                }
                $response['success'] = true;
                $response['message'] = __( 'Item has been added to your cart.', 'mobikul_marketplace' );
            }
            return $response;
        }

        /**
         * Offline cart add endpoint callback
         * @param
         * @return $cart_key
         */
        public function mkwc_cart_offline( $offline_data )
        {
          $validate_auth_key = $this->mkwc_authenticate_request();

          if ( $validate_auth_key != 'ok' ) {
            return array(
              'session_id'  => $validate_auth_key,
              'status'      => 401,
              'invalid_auth_key_error' => esc_html__( 'Please provide valid Auth Key.', 'mobikul_marketplace' ),
              'success'     => false,
            );
          }

          $cartdata_handler = new Helper\Mkwc_Cart_Data();

          $offline_data = is_object( $offline_data ) ? $offline_data->get_json_params() : $offline_data;
          
          $return_id = '';
          $request = isset( $_GET['request'] ) ? $_GET['request'] : '';
          $customer_id = isset( $offline_data['customer_id'] ) ? $offline_data['customer_id'] : '';
          $guest_id = isset( $offline_data['guest_id'] ) ? $offline_data['guest_id'] : '';

          if( empty( $customer_id) && empty( $guest_id ) ){
            $guest_id = $cartdata_handler->generate_customer_id();
            $return_id = $guest_id;
          }
          $product_data = isset( $offline_data['data'] ) ? $offline_data['data'] : [];
          
          $customer_check = $this->mkwc_user_exist_request( $customer_id );
            
          if( isset( $customer_check['success'] ) && ! $customer_check['success'] ){
              return $customer_check;
          }
          if( empty( $request ) || ! is_array( $product_data ) ){
              return array(
                'success' => false,
                'message' => __( 'Sorry, you must provide all parameters.', 'mobikul_marketplace' )
              );
          }
          if( is_array( $product_data ) && $product_data ){ 
            foreach ( $product_data as $pd => $prodata ) {
              if( isset( $prodata['customer_id'] ) && $prodata['customer_id'] && $customer_id ){
                $prodata['customer_id'] = $customer_id;
              } else if( $customer_id ) {
                $prodata['customer_id'] = $customer_id;
              }
              if( isset( $prodata['guest_id'] ) && $prodata['guest_id'] && $guest_id ){
                $prodata['guest_id'] = $guest_id;
              } else if( $guest_id ) {
                $prodata['guest_id'] = $guest_id;
              }
              $response = $this->mkwc_cart( $prodata, $auth = false );
            }
            if( isset( $response['success'] ) && $response['success'] && ! empty( $return_id ) ){
              $response['guest_id'] = $return_id;
            }
          } else {
            $response = [
              'success' => false,
              'message' => esc_html__( 'Opps something went wrong in data!', 'mobikul_marketplace' )
            ];
          }
          
          return $response;
        }
    }
}
