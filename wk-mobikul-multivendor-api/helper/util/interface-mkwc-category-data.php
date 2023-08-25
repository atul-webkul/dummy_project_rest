<?php

/**
 * @author Webkul
 * @version 1.0.0
 * This file handles category data helper functions interface.
 */

namespace WkMobikulMultivendorApi\Helper\Util;

defined( 'ABSPATH' ) || exit;

/**
 *  Category data interface
 */
interface Mkwc_Category_Data_Interface
{
    /**
     * Prepare category data
     * @param $category_object
     * @return $prepared_category_data
     */
    public function mkwc_prepare_category( $category );

    /**
     * Get subcategories by category id
     * @param $term_id
     * @return $sub_categories
     */
    public function mkwc_get_sub_categories( $term_id );
}
