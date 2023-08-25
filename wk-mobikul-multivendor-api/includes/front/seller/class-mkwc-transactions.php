<?php

/**
 * @author Webkul
 * @version 1.0.0
 * This file handles seller transaction functions.
 */

namespace WkMobikulMultivendorApi\Includes\Front\Seller;

use WkMobikulMultivendorApi\Includes;
use WkMobikulMultivendorApi\Includes\Front\Seller\Util;
use WkMobikulMultivendorApi\Helper;

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'Mkwc_Transactions' ) ) {
    /**
     *
     */
    class Mkwc_Transactions extends Includes\Mkwc_Authentication implements Util\Mkwc_Transactions_Interface
    {
        public static $base = 'seller/transaction';

        protected $response;

        protected $general_data_handler;

        protected $seller_data_handler;

        /**
         * Return seller transactions
         */
        public function mkwc_get_transactions()
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

            $response_handler = new \WP_REST_Response();

            $seller_data_handler = new Helper\Mkwc_Seller_Data();

            $data = [];

            $seller_id = isset( $_GET['seller_id'] ) ? intval( $_GET['seller_id'] ) : '';

            $seller_check = $this->mkwc_user_exist_request( $seller_id );
            
            if( isset( $seller_check['success'] ) && ! $seller_check['success'] ){
              return $seller_check;
            }

            $transactions = $seller_data_handler->mkwc_get_seller_transactions( $seller_id );

            if ( $transactions ) {
                $transactions = array_map( array( $this, 'mkwc_transaction_price_format' ), $transactions );

                $data = array_merge( $data, array(
                    'transactions' => $transactions,
                    'success' => 1,
                    'message' => '',
                ) );
            } else {
                $data = array_merge( $data, array(
                    'success' => 0,
                    'message' => __( 'No transactions found.', 'mobikul_marketplace' ),
                ) );
            }

            $response_handler->set_data( $data );

            return $response_handler;
        }

        public function mkwc_transaction_price_format( $transaction )
        {
            $general_data_handler = new Helper\Mkwc_General_Data();

            $transaction['amount'] = $general_data_handler->mkwc_get_formatted_price( $transaction['amount'] );

            return $transaction;
        }

        /**
         * Return seller transaction by id
         * @param $params
         * @return $transaction_data
         */
        public function mkwc_get_transaction_by_id( $params )
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

            $response_handler = new \WP_REST_Response();
            $seller_data_handler = new Helper\Mkwc_Seller_Data();
            $general_data_handler = new Helper\Mkwc_General_Data();
            $data = [];
            $transaction_id = ( is_object( $params ) ) ? (int) $params->get_param( 'transaction_id' ) : $params;
            $seller_id = isset( $_GET['seller_id'] ) ? intval( $_GET['seller_id'] ) : '';
            $seller_check = $this->mkwc_user_exist_request( $seller_id );
            if( isset( $seller_check['success'] ) && ! $seller_check['success'] ){
              return $seller_check;
            }
            
            $transaction = $seller_data_handler->mkwc_get_seller_transaction_by_id( $seller_id, $transaction_id );
            if ( $transaction ) {
                $transaction['amount'] = $general_data_handler->mkwc_get_formatted_price( $transaction['amount'] );
                $orderId = isset($transaction['order_id']) ? $transaction['order_id'] : '';
                $details = $seller_data_handler->mkwc_get_transaction_table_details($seller_id, $orderId);
                $data = array_merge( $data, $transaction );
                $data = array_merge( $data, ['details' => $details] );
                $data = array_merge( $data, array(
                    'success' => 1,
                    'message' => '',
                ) );
            } else {
                $data = array_merge( $data, array(
                    'success' => 0,
                    'message' => __( 'No transaction found.', 'mobikul_marketplace' ),
                ) );
            }

            $response_handler->set_data( $data );

            return $response_handler;
        }
    }
}
