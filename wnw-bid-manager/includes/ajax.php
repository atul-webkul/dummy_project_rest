<?php
function bm_hide_notes() {
    global $wpdb;

    if ($_POST['inputid']) {

        /*
         * We are going to run a magic show here and use this function to dismiss some bid manager settings
         */

        if ($_POST['inputid'] == 'smtp_notification') {
            $bm_settings = get_option('bid_manager_settings');
            $bm_settings = json_decode($bm_settings);
            $bm_settings->email_smtp_notification = FALSE;
            $bm_settings = json_encode($bm_settings);
            update_option('bid_manager_settings', $bm_settings);
        }

        if ($_POST['inputid'] == 'email_footer') {
            $bm_settings = get_option('bid_manager_settings');
            $bm_settings = json_decode($bm_settings);
            $bm_settings->email_footer_notification = FALSE;
            $bm_settings = json_encode($bm_settings);
            update_option('bid_manager_settings', $bm_settings);
        }


        $query = "UPDATE " . BM_NOTIFICATIONS . " SET dont_show = 0 WHERE id = %d";
        $data  = array(
            $_POST['inputid']
        );

        $query   = $wpdb->prepare( $query, $data );
        $wpdb->get_results( $query );
    }
    wp_die();
}

add_action( 'wp_ajax_my_action', 'bm_hide_notes' );
