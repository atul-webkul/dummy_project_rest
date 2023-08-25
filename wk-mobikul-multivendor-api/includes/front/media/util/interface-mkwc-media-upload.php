<?php

/**
 * @author Webkul
 * @version 1.0.0
 * This file handles media upload api callback interface.
 */

namespace WkMobikulMultivendorApi\Includes\Front\Media\Util;

defined( 'ABSPATH' ) || exit;

/**
 * media function interface
 */
interface Mkwc_Media_Upload_Interface
{
    /**
     * Image upload endpoint callback
     * @param $data
     * @return $id
     */
    public function mkwc_media_upload( $data );
}
