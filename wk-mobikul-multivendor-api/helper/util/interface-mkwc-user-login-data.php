<?php

/**
 * @author Webkul
 * @version 1.0.0
 * This file handles user login data helper functions interface.
 */

namespace WkMobikulMultivendorApi\Helper\Util;

defined( 'ABSPATH' ) || exit;

/**
 *  user data interface
 */
interface Mkwc_User_Login_Data_Interface
{
    /**
     * User Login
     * @param $user_id
     * @return $user_cart_data
     */
    public function mkwc_user_login_data( $data );
}
