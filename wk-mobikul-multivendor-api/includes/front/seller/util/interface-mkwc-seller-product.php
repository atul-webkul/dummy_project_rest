<?php

/**
 * @author Webkul
 * @version 1.0.0
 * This file handles seller add product interface.
 */

namespace WkMobikulMultivendorApi\Includes\Front\Seller\Util;

defined( 'ABSPATH' ) || exit;

interface Mkwc_Seller_Product_Interface {
    /**
     * Add product callback
     * @param Object $data
     * @return Array $response
     */
    public function mkwc_add_product( $data );

    /**
     * Check sku exist or not
     * @param Object $data
     * @return Array $response
     */
    public function mkwc_check_product_sku( $data );

    /**
     * Return allowed product types and categories for seller
     * @return Array $response
     */
    public function mkwc_get_product_prerequisites();

    /**
     * Save product params validation
     * @param Array $data
     * @return Array $errors
     */
    public function mkwc_product_validation( $data );

    /**
     * Save edit product data
     * @param Array $data
     * @return $product_data
     */
    public function mkwc_save_product_edit_data( $data );

    /**
     * Return product data to edit action
     * @param Array $data
     * @return $product_data
     */
    public function mkwc_get_product_edit_data( $data );

    /**
     * Format data for related products
     * @param Product_Id $id
     * @return Array $data
     */
    public function mkwc_format_related_ids( $id );

    /**
     * Delete seller product
     * @param Array $data
     * @return $status
     */
    public function mkwc_delete_seller_product( $data );

    /**
     * Return seller products
     * @param $search_keyword
     * @return Array $result
     */
    public function mkwc_get_linked_products();
}
