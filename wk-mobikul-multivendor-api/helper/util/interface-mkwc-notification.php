<?php

/**
 * @author Webkul
 * @version 1.0.0
 * This file handles user login data helper functions interface.
 */

namespace WkMobikulMultivendorApi\Helper\Util;

defined( 'ABSPATH' ) || exit;

/**
 *  Notification interface
 */
interface Mkwc_notification_Interface
{
    /**
     * Update notification
     * @param $notification_object
     * @return $notification
     */
    public function mkwc_notification_update( $request, $args );

    /**
     * get notification
     * @param $notification_id
     * @return $notifications
     */
    public function mkwc_get_notification( $id = '' );

    /**
     * Trash notification
     * @param $notification_id
     * @return $tash
     */
    public function mkwc_delete_notification( $id );

    /**
     * Send notification
     * @param $notification_id
     * @return $alert
     */
    public function mkwc_send_user_notification( $id, $bulk = false );

    /**
     * Send notification
     * @param $notification_id
     * @return $alert
     */
    public function mkwc_send_notification( $message );
}
