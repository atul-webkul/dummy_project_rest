<?php

/**
 * @author Webkul
 * @version 1.0.0
 * This file handles users api callback interface.
 */

namespace WkMobikulMultivendorApi\Includes\Front\Users\Util;

defined( 'ABSPATH' ) || exit;

/**
 * Users function interface
 */
interface Mkwc_Users_Interface
{
    /**
     * Create User endpoint callback
     * @param $data
     * @return $user
     */
    public function mkwc_create_user( $data );

    /**
     * Get user avatar url
     * @param $user_email
     * @return $avatar
     */
    public function mkwc_get_avatar_url( $email );

    /**
     * Get user by user id
     * @param $user_id
     * @return $user
     */
    public function mkwc_get_user( $user_id );

    /**
     * Check input shop url is available
     * @return $response
     */
    public function mkwc_check_shop_url();

    /**
     * User Login endpoint callback
     * @return $response
     */
    public function mkwc_user_login( $data );

    /**
     * User Logout endpoint callback
     * @return $response
     */
    public function mkwc_user_logout( $data );

    /**
     * User Login endpoint callback
     * @return $response
     */
    public function mkwc_set_user_address( $user_id );

    /**
     * Edit user account endpoint callback
     * @return $response
    **/
    public function mkwc_edit_user_account( $user_id );

    /**
     * User downloads endpoint callback
     * @return $response
    **/
    public function mkwc_user_downloads( $user_id, $fields = null );

    /**
     * User forgot password endpoint callback
     * @return $response
    **/
    public function mkwc_user_forgot_password( $data );   
}
