<?php

function bm_update_08312017() {

    $managing_user = get_option( 'bm_free_user' );
    $current_key = get_user_meta( $managing_user, 'bm_google_api_key', TRUE );

    // If there is a managing user set, lets also stuff that into our bid_manager_settings
    if ($managing_user) {
        $more_settings = array(
            'free_user' => $managing_user
        );

        bm_save_main_settings($more_settings);
    }

    // If the user has an API key in their usermeta table lets put it in the options table where it really should be

    if ($current_key) {
        $more_settings = array(
            'bm_google_api_key' => $current_key
        );

        bm_save_main_settings($more_settings);
    }
}

function bm_update_08112017() {
    global $wpdb;

    $query = "ALTER TABLE " . BM_BIDS . " ADD bid_notes VARCHAR( 4500 ) after job_zip";
    $wpdb->get_results($query);

    $query = "ALTER TABLE " . BM_BIDS . " ADD bid_options VARCHAR( 1000 ) after has_response";
    $wpdb->get_results($query);

}

// Very first update
function bm_update_02162016() {

    global $wpdb;
    global $table_prefix;

    /*
     * Up until version 1.1.3 we did not include the wp prefix for the tables.
     * Therefore, we have to update all old table names to the new structure
     */

    // Change the bm_notifications table to whatever the users prefix is
    $query = "RENAME TABLE `bm_notifications` TO `" . $table_prefix . "bm_notifications`";
    $wpdb->get_results($query);

    // Change the bm_bids table to whatever the users prefix is
    $query = "RENAME TABLE `bm_bids` TO `" . $table_prefix . "bm_bids`";
    $wpdb->get_results($query);

    // Change the bm_bids_responses table to whatever the users prefix is
    $query = "RENAME TABLE `bm_bids_responses` TO `" . $table_prefix . "bm_bids_responses`";
    $wpdb->get_results($query);

    // Change the bm_responder table to whatever the users prefix is
    $query = "RENAME TABLE `bm_responder` TO `" . $table_prefix . "bm_responder`";
    $wpdb->get_results($query);

    // Change the bm_user table to whatever the users prefix is
    $query = "RENAME TABLE `bm_user` TO `" . $table_prefix . "bm_user`";
    $wpdb->get_results($query);

    // Change the bm_responder_emails table to whatever the users prefix is
    $query = "RENAME TABLE `bm_responder_emails` TO `" . $table_prefix . "bm_responder_emails`";
    $wpdb->get_results($query);

}