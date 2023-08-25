<?php

/**
 * @author Webkul
 * @version 1.0.0
 * This file handles order store helper function interface.
 */

namespace WkMobikulMultivendorApi\Helper\Util;

defined( 'ABSPATH' ) || exit;

interface Mkwc_Checkout_Data_Interface {
    /**
     * Get orders by customer id
     * @param $customer_id
     * @return $order_ids
     */
    public function mkwc_create_base_order( $args, $data );

    /**
     * Set order address
     * @param $order
     * @return $data
     */
    public function mkwc_set_order_addresses( $order, $data );

    /**
     * Set address by id
     * @param $customer_id, $address
     * @return $address
     */
    public function mkwc_set_address( $id, $address, $type = 'billing' );

    /**
     * Set order meta by order id
     * @param $order_id
     * @return $order_meta
     */
    public function mkwc_set_order_meta( $order_id, $order_meta );

    /**
     * Get order by customer id
     * @param $customer_id
     * @return $order
     */
    public function mkwc_get_order( $id, $fields = null );

    /**
     * Get product image
     * @param $product
     * @return $data
     */
    public function mkwc_get_product_data( $product );

    /**
     * Download product iamge by product id
     * @param $product_id
     * @return $image
     */
    public function mkwc_image_downsize( $id, $size );

    /**
     * Check order payment complete by customer id
     * @param $customer_id
     * @return $order_ids
     */
    public function mkwc_payment_complete( $order, $id, $transaction_id = '' );

    /**
     * Check order status
     * @param $order
     * @return $status
     */
    public function mkwc_has_status( $order, $status );

    /**
     * Get order status
     * @param $order
     * @return $status
     */
    public function mkwc_get_status( $order );

    /**
     * Get order subtotal
     * @param $order
     * @return $subtotal
     */
    public function mkwc_get_order_subtotal( $order );
}
