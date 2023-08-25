<?php

/**
 * @author Webkul
 * @version 1.0.0
 * This file handles product store helper function interface.
 */

namespace WkMobikulMultivendorApi\Helper\Util;

defined( 'ABSPATH' ) || exit;

interface Mkwc_Product_Store_Interface {
    /**
     * Return product ids based on section
     * @param $section
     * @return $product_ids
     */
    public function mkwc_return_homepage_product_data( $section );

    /**
     * Return product ids based on seller id
     * @param $seller_id
     * @return $product_ids
     */
    public function mkwc_return_seller_product_data( $seller_id );

    /**
     * Prepare homepage product section data
     * @param $product_object, $width
     * @return $prepared_data
     */
    public function mkwc_prepare_homepage_products( $featured_products, $width );

    /**
     * Return homepage banners
     * @return $banners
     */
    public function mkwc_return_homepage_banners();

    /**
     * Delete homepage banners
     * @return $banners
     */
    public function mkwc_delete_homepage_banners( $id );

    /**
     * Return image based on size
     * @param $image_meta, $size
     * @return $image
     */
    public function mkwc_image_downsize( $image_meta, $size, $attachment_id );

    /**
     * Return product image based on size
     * @param $product_id, $size
     * @return $product_image
     */
    public function mkwc_product_image_downsize( $product_id, $size );

    /**
     * Return banner image based on size
     * @param $banner_id, $size, $image
     * @return $banner_image
     */
    public function mkwc_banner_image_downsize( $banner_id, $size, $attachment_id );

    /**
     * Prepare banner data
     * @param $banners, $width
     * @return $banner_prepared_data
     */
    public function mkwc_prepare_homepage_banner_data( $banners, $width );

    /**
     * Prepare featured categories
     * @param $featured_categories, $width
     * @return $prepared_data
     */
    public function mkwc_prepare_featured_categories( $featured_categories, $width );

    /**
     * Return featured categories
     */
    public function mkwc_return_featured_category_data();

    /**
     * Return category image based on size
     * @param $term_id, $size
     * @return $cat_image
     */
    public function mkwc_category_image_downsize( $term_id, $size );

    /**
     * Prepare grouped product price
     * @param $product_id, $children
     * @return $price
     */
    public function mkwc_get_grouped_price_range( $product_id, $children );

    /**
     * Get product childrens
     * @param $product_id, $children
     * @return array $children
     */
    public function mkwc_get_product_children( $id, $children );

    /**
     * Return all existing sku
     * @return Array $sku
     */
    public function mkwc_check_sku( $sku );

    /**
     * Return image src based on ids
     * @param Array|Int $image_ids
     * @return Array $image
     */
    public function mkwc_get_image_src( $ids );

    /**
     * Return product author
     * @param $product_id
     * @return $product_author
     */
    public function mkwc_get_product_author( $product_id );

    /**
     * Return prepared product children data
     * @param String serialised $children, $size
     * @return Array $child_data
     */
    public function mkwc_get_product_children_data( $children, $size );
}
