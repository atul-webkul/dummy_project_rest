<?php
// Shortcode for user to display on the page they want the bid request to be visible for the responder
function bm_responder_invitation() {

	global $wpdb;

	if (! isset($_GET['hash'])) {
		return;
	}

	if (! isset($_GET['invitation_id'])) {
		return;
	}

	$hash = sanitize_text_field($_GET['hash']);
	$invite = sanitize_text_field($_GET['invitation_id']);


	$query = "SELECT bid_id, hash FROM " . BM_EMAILS . " WHERE bid_id = %d AND hash = %s";
	$data  = array(
        $invite,
        $hash
    );

	$query   = $wpdb->prepare( $query, $data );
	$result = $wpdb->get_row( $query );


        $dbBid  = $result->bid_id;
        $dbHash = $result->hash;

	$content  = '';

	if (($invite === $dbBid) && ($hash === $dbHash)) {
        $content .= bm_responder_responses();
    } else {
        $content .= '<p>This page is to display bid information for the invited supplier.  Please check the email you were invited from and click the link that was provided.  By doing so, it will populate the appropriate information so you can respond.</p>';
    }

	echo $content;
}

function bm_front_end_display($args) {
	$today = date( 'Y-m-d H:i:s' );
	global $wpdb;

	if ( isset($_GET[ 'bid_accepted' ]) ) {
		return bm_user_bid_accepted();
	} elseif ( isset($_GET[ 'bmuser_bid_past' ]) ) {
		return bm_user_bid_past();
	} elseif ( isset($_GET[ 'bid_response' ]) ) {
		return bm_user_bid_review();
	} elseif ( isset($_GET[ 'bmuser_bid_active' ]) ) {
		return bm_user_bid_active();
	}elseif ( isset($_GET[ 'sp_make_payment' ]) ) {
		return sp_project_payment();
	} elseif ( isset($_GET[ 'hide' ]) ) {
		return bm_hide_bid();
	} elseif ( isset($_GET[ 'view_hidden' ]) && $_GET[ 'view_hidden' ] == "true" ) {
		return bm_view_hidden();
	} elseif ( isset($_GET[ 'unhide' ]) && $_GET[ 'unhide' ] == "true" ) {
		return bm_unhide_bid();
	}

	if ( isset($_GET[ 'accept' ]) && $_GET[ 'accept' ] == 'true' ) {

		$bm_bid_id = (int)$_GET[ 'bid_id' ];
		$responseId = (int)$_GET[ 'response_id' ];

		//  Run the update on the bm_bids table
		$query = "UPDATE " . BM_BIDS . " SET accepted_flag = %d WHERE bid_id = %d";
		$data = array(
			1,
			$bm_bid_id
		);

		$query = $wpdb->prepare( $query, $data );
		$wpdb->get_results( $query );

		//  Run the update on the bm_bids_responses table
		$query = "UPDATE " . BM_BIDS_RESPONSES . " SET bid_accepted = %d WHERE bid_id = %d AND id = %d";
		$data = array(
			1,
			$bm_bid_id,
			$responseId
		);

		$query = $wpdb->prepare( $query, $data );
		$wpdb->get_results( $query );
		bm_user_emails();
	}

	$content = '<div class="wrap">';
	$shortcode = '';
	$args = shortcode_atts(array(
		'loggedin'           => 'FALSE',
		'accepted_bids'      => 'FALSE',
		'past_bids'          => 'FALSE',
		'with_responses'     => 'FALSE',
		'sp_bid_accepted'     => 'FALSE',
		'active_bids'        => 'TRUE'
	), $args);

	if ($args['active_bids'] == 'TRUE') {
		// List active bids
		$active_bids = new WPBM_Bids();
		$shortcode .= $active_bids->get_active_bids_table($today);
	}


	if ($args['accepted_bids'] == 'TRUE') {
		// List accepted bids
		$accepted_bids = new WPBM_Bids();
		$shortcode .= $accepted_bids->get_accepted_bids_table();
	}


	if ($args['with_responses'] == 'TRUE') {
		// List bids with responses
		$with_responses = new WPBM_Bids();
		$shortcode .= $with_responses->get_bids_with_responses_table($today);
	}
	
	if ($args['sp_bid_accepted'] == 'TRUE') {
		// List bids with responses
		$with_responses = new WPBM_Bids();
		$shortcode .= $with_responses->sp_accepted_bids_table();
	}



	if ($args['past_bids'] == 'TRUE') {
		// List past bids
		$past_bids = new WPBM_Bids();
		$shortcode .= $past_bids->get_past_bids_table($today);
	}

	if ($args['loggedin'] == 'TRUE') {
		if (is_user_logged_in()) {
			$content .= $shortcode;
		} else {
			$content .= 'You must be logged in to see the bids on this site.';
		}
	} else {
		$content .= $shortcode;
	}

	$content .= '</div>';

	return $content;
}

add_shortcode('bm-invite', 'bm_responder_invitation');
add_shortcode('bm-bid-display', 'bm_front_end_display');
