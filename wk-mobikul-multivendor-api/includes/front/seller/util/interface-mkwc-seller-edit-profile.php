<?php

/**
 * @author Webkul
 * @version 1.0.0
 * This file handles edit seller profile interface.
 */

namespace WkMobikulMultivendorApi\Includes\Front\Seller\Util;

defined( 'ABSPATH' ) || exit;

interface Mkwc_Seller_Edit_Profile_Interface {
    /**
     * Seller edit profile
     */
    public function mkwc_seller_edit_profile( $profile_data );

    /**
     * Seller get edit profile
     */
    public function mkwc_seller_get_edit_profile();
}
