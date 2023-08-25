<?php

/**
 * @author Webkul
 * @version 1.0.0
 * This file handles all file includes.
 */

use WkMobikulMultivendorApi\Includes\Front;
use WkMobikulMultivendorApi\Includes\Admin;

defined( 'ABSPATH' ) || exit;

require_once( MKWC_FILE . 'inc/autoload.php' );

if ( ! is_admin() ) {
    new Front\Mkwc_Front_Hook_Handler();
} else {
    new Admin\Mkwc_Hook_Handler();
}
