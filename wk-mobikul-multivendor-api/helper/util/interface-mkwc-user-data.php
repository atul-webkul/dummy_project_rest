<?php

/**
 * @author Webkul
 * @version 1.0.0
 * This file handles user data helper functions interface.
 */

namespace WkMobikulMultivendorApi\Helper\Util;

defined( 'ABSPATH' ) || exit;

/**
 *  user data interface
 */
interface Mkwc_User_Data_Interface
{
    /**
     * Check User exist
     * @param $user_object
     * @return $user_data
     */
    public function mkwc_check_user_exist( $email, $pass );

    /**
     * Update User address
     * @param $user_object
     * @return $update_user_data
     */
    public function mkwc_update_user_address( $id, $data );

    /**
     * User billing address
     * @param $user_object
     * @return $user_billing_format
     */
    public function mkwc_user_billing_address_format();

    /**
     * User shipping address
     * @param $user_object
     * @return $user_shipping_format
     */
    public function mkwc_user_shipping_address_format();

    /**
     * Check User is seller
     * @param $user_object
     * @return $user_seller
     */
    public function mkwc_user_seller_info( $user_id );

    /**
     * get user session
     * @param $user_object
     * @return $user_session
     */
    public function mkwc_get_user_session( $table_name, $user_id );

    /**
     * insert into user session
     * @param $user_object
     * @return $user_session
     */
    public function mkwc_insert_user_session( $table_name, $user_id, $session, $session_expiry );

    /**
     * Delete user session
     * @param $user_object
     * @return $user_session
     */
    public function mkwc_delete_user_session( $table_name, $user_id );

    /**
     * update into user session
     * @param $user_object
     * @return $user_session
     */
    public function mkwc_update_user_session( $table_name, $guest_session_value, $session_expiry, $customer_id );

    /**
     * update seller info
     * @param $seller_object
     * @return $seller_info
     */
    public function mkwc_seller_meta( $user, $seller_key, $seller_val );

    /**
     * Retrieve user password
     * @param $user_email
     * @return $user_password
     */
    public function mkwc_send_password_reset_email( $user_email );
}
