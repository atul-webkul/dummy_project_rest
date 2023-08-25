<?php

/**
 * @author Webkul
 * @version 1.0.0
 * This file handles category data helper functions.
 */

namespace WkMobikulMultivendorApi\Helper;

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'Mkwc_Category_Data' ) ) {
    /**
     * Category data class
     */
    class Mkwc_Category_Data implements Util\Mkwc_Category_Data_Interface
    {
        /**
         * Prepare category data
         * @param $category_object
         * @return $prepared_category_data
         */
        public function mkwc_prepare_category( $category )
        {
            $post_fields = [];

            $categories = $this->mkwc_get_sub_categories( $category->term_id );

            if ( ! empty( $categories ) ) {
                foreach ( $categories as $key => $value ) {
                    $children = count( get_term_children( (int)$value->term_id, 'product_cat' ) );
                    $post_fields[] = array(
                        'id'    => $value->term_id,
                        'name'  => $value->name,
                        'child' => $children
                    );
                }
            }

            $_post = array(
                'id'        => $category->term_id,
                'name'      => $category->name,
                'children'  => $post_fields,
            );

            return $_post;
        }

        /**
         * Get subcategories by category id
         * @param $term_id
         * @return $sub_categories
         */
        public function mkwc_get_sub_categories( $term_id )
        {
            $args = array(
                'child_of'    => $term_id,
                'taxonomy'    => 'product_cat',
                'hide_empty'  => false,
            );

            $sub_categories = get_categories( $args );

            return $sub_categories;
        }
    }
}
