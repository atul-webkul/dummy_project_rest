<?php

/**
 * @author Webkul
 * @version 1.0.0
 * This file handles all seller data interface.
 */

namespace WkMobikulMultivendorApi\Helper\Util;

defined( 'ABSPATH' ) || exit;

interface Mkwc_Seller_Data_Interface {
    /**
     * App homepage data
     */
    public function mkwc_check_seller_by_id( $user_id = '' );

    /**
     * Return seller sale stats
     * @param $seller_id
     * @return $stats
     */
    public function mkwc_get_seller_sale_stats( $seller_id );

    /**
     * Return seller all orders
     * @param $seller_id
     * @return $order_ids
     */
    public function mkwc_get_seller_order_ids( $seller_id );

    /**
     * Return seller lifetime sale
     * @param $seller_id
     * @return $total_sale
     */
    public function mkwc_get_total_seller_order_amount( $seller_id );

    /**
     * Return seler order sale history
     * @param $seller_id, $time
     * @return $sale_history
     */
    public function mkwc_seller_sale_order_history( $seller_id, $time );

    /**
     * Get top selling products for seller
     * @param $seller_id
     * @return $products
     */
    public function mkwc_seller_top_selling_products( $seller_id );

    /**
     * Get seller products count
     * @param $seller_id
     * @return $count
     */
    public function mkwc_get_seller_product_count( $seller_id );

    /**
     * Get Seller Orders List
     * @param $seller_id, $per_page, $page
     * @return $order_data
     */
    public function mkwc_get_seller_orders( $seller_id, $per_page, $page );

    /**
     * Return seller transaction data by id
     * @param $seller_id, $transaction_id
     * @return $transaction data
     */
    public function mkwc_get_seller_transaction_by_id( $seller_id, $transaction_id );

    /**
     * Get seller product list
     * @param $seller_id
     * @return $product_list
     */
    public function mkwc_get_seller_products( $seller_id, $page, $per_page, $edit );

    /**
     * Return seller orders list
     * @param $seller_id, $page, $per_page
     * @return $order_list data
     */
    public function mkwc_get_seller_order_list( $seller_id, $page, $per_page );

    /**
     * Get order details by order id
     * @param $seller_id, $order_id
     * @return $order
     */
    public function mkwc_get_seller_order_by_id( $seller_id, $order_id );

    /**
     * Return seller shop followers
     * @param $seller_id, $page, $per_page
     * @return #shop_followers
     */
    public function mkwc_get_followers( $seller_id, $page, $per_page );

    /**
     * Return seller details
     * @param $seller_id,
     * @return #seller_details
     */
    public function mkwc_get_details( $seller_id );

    /**
     * Return seller ids
     * @param ,
     * @return #seller_ids
     */
    public function mkwc_seller_ids();

		/**
     * Return seller ids
     * @param ,
     * @return #seller_ids
     */
    public function mkwc_seller_products( $id );

    /**
     * Return allowed categories and product types
     * @param $seller_id
     * @return Array $categories & $product_types
     */
    public function mkwc_seller_product_prerequisites( $seller_id );

    /**
     * Update seller media
     * @param ,
     * @return #seller_media
     */
    public function mkwc_update_seller_media( $user_id, $avtar_type, $attach_id );

    /**
     * Get seller search product list
     * @param $seller_id, $s
     * @return $product_list
     */
    public function mkwc_get_seller_search_products( $seller_id, $s );
}
