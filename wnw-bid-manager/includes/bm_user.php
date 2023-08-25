<?php
function bm_user_emails() {

	global $wpdb;

	$bm_user_id = get_current_user_id();

	$bm_bid_id = (isset($_GET[ 'bid_id' ]) ? (int)$_GET[ 'bid_id' ] : (int)$_GET[ 'bid_accepted' ]);
	$responseId = (int)$_GET[ 'response_id' ];

	//  Send this email to the responder if the requester accepts the bid
	if ( sanitize_text_field( $_GET[ 'accept' ] ) == "true" ) {


		$query = "SELECT job_name, job_street, job_street_two, job_city, job_state, job_zip, responder_poc, bmuser_busname, bmuser_poc, bmuser_phone, bmuser_email, bmuser_street, bmuser_street_two, bmuser_city, bmuser_state, bmuser_zip, responder_poc, responder_email FROM " . BM_BIDS_RESPONSES . " LEFT OUTER JOIN " . BM_BIDS . " ON " . BM_BIDS . ".bid_id=" . BM_BIDS_RESPONSES . ".bid_id LEFT OUTER JOIN " . BM_USER . " ON " . BM_USER . ".id=%d WHERE " . BM_BIDS_RESPONSES . ".id = %d AND " . BM_BIDS_RESPONSES . ".bid_id = %d";
		$data = array(
			$bm_user_id,
			$responseId,
			$bm_bid_id
		);

		$query = $wpdb->prepare( $query, $data );
		$results = $wpdb->get_results( $query );

		foreach ( $results as $record ) {
			$conEmail = stripslashes( $record->bmuser_email );
			$bm_responder_email = stripslashes( $record->responder_email );

			$bm_user_busname = stripslashes( $record->bmuser_busname );
			$bm_user_poc = stripslashes( $record->bmuser_poc );
			$bm_user_phone = $record->bmuser_phone;
			$bm_user_street = $record->bmuser_street;
			$bm_user_street2 = ($record->bmuser_street_two ? $record->bmuser_street_two : '');
			$bm_user_city = $record->bmuser_city;
			$bm_user_state = $record->bmuser_state;
			$bm_user_zip = $record->bmuser_zip;
			$bm_job_name = stripslashes( $record->job_name );
			$bm_job_street = stripslashes( $record->job_street );
			$bm_job_street2 = ($record->job_street_two ? $record->job_street_two : '');
			$bm_job_city = $record->job_city;
			$bm_job_state = $record->job_state;
			$bm_job_zip = $record->job_zip;
			$bm_responder_poc = $record->responder_poc;
		}

		$to = $bm_responder_email; // . $supCCEmail;

		$subject = $bm_user_busname . ' Accepts Your Bid for - ' . $bm_job_name; // The subject of the email

	    $message = '<p>Hello ' . $bm_responder_poc . '!</p>'; // begins the message
		$message .= '<p>congratulations youʼve been
selected to bid on a project. Please indicate if your availability for this project
and we wish you all the best with this bid</p>';
		
		$message .= '<p>' . $bm_user_busname . ' has accepted your bid for bid #' . $bm_bid_id . '</p>';
		$message .= '<p>Please contact us at bids@caribbeanhomenetwork.com or 1-844-265-4663 for more informaFon for award of contract.</p>';
		$message .= '<p style="font-size: 18px; font-weight: bold;">Contact Information:</p>';
		$message .= '<p>Contact Person: ' . $bm_user_poc . '</p>';
		$message .= '<p>Street: ' . $bm_user_street . '</p>';
		if ( $bm_user_street2 ) {
			$message .= '<p>Street Cont.: ' . $bm_user_street2 . '</p>';
		}
		$message .= '<p>City: ' . $bm_user_city . '</p>';
		$message .= '<p>Parish: ' . $bm_user_state . '</p>';
		
		$message .= '<p>Phone: ' . $bm_user_phone . '</p>';
		$message .= '<p><a href="' . $conEmail . '">Email ' . $bm_user_poc . '</a></p>';
		$message .= '<p style="font-size: 18px; font-weight: bold;">Job Details:</p>';
		$message .= '<p>Job Name: ' . $bm_job_name . '</p>';
		$message .= '<p>Street: ' . $bm_job_street . '</p>';
		if ( $bm_job_street2 ) {
			$message .= '<p>Street Cont.: ' . $bm_job_street2 . '</p>';
		}
		$message .= '<p>City: ' . $bm_job_city . '</p>';
		$message .= '<p>Parish: ' . $bm_job_state . '</p>';
		

		$headers = array(
			'Content-Type: text/html; charset=UTF-8',
			'From: Accept BID <bids@caribbeanhomenetwork.com>'
		);

		wp_mail( $to, $subject, $message, $headers );
		
		
		
		$queryOne = "select responder_email,responder_busname from wp_bm_bids_responses where bid_id = '".$bm_bid_id."' and bid_accepted = 0 ";
		$resultsOne = $wpdb->get_results( $queryOne );
		
		foreach ( $resultsOne as $recordone ) {
			
			
			$toone = $recordone->responder_email; // . $supCCEmail;

			$subjectone =  ' Not accepts Your Bid for - ' . $bm_bid_id; // The subject of the email

			$messageone = '<p>Hello ' . $recordone->responder_busname . '!</p>'; // begins the message
			$messageone .= '<p>Your quote has not accepted for bid #' . $bm_bid_id . '</p>';
			
			$headersone = array(
				'Content-Type: text/html; charset=UTF-8',
				'From: WP Bid Manager <no-reply@wordpress.org>'
			);

			wp_mail( $toone, $subjectone, $messageone, $headersone );
			
			
		}
		
		
		
	}


	//  Send this email to the responder if the requester retracts the bid
	if ( isset($_POST[ 'retract_bid' ]) && sanitize_text_field( $_POST[ 'retractbid' ] ) == 'RETRACT' ) {

		$query = "SELECT bmuser_email, responder_email FROM " . BM_BIDS_RESPONSES . " LEFT OUTER JOIN " . BM_USER . " ON " . BM_USER . ".id=%d  WHERE " . BM_BIDS_RESPONSES . ".id = %d AND " . BM_BIDS_RESPONSES . ".bid_id = %d";
		$data = array(
			$bm_user_id,
			$responseId,
			$bm_bid_id
		);

		$query = $wpdb->prepare( $query, $data );
		$results = $wpdb->get_results( $query );

		foreach ( $results as $record ) {
			$bm_responder_email = stripslashes( $record->responder_email );
		}

		$to = $bm_responder_email;

		$subject = 'The Requester Retracted Bid - ' . $bm_bid_id;

		$retractMessage = $_POST[ 'retract_message' ];
		$retractMessage = stripslashes( $retractMessage );

		$message = '<p>Hello,</p>';
		$message .= '<p>The requester retracted bid #' . $bm_bid_id . '</p>';
		$message .= '<p>Reason for Retracting:</p>';
		$message .= html_entity_decode( $retractMessage );

		$headers = array(
			'Content-Type: text/html; charset=UTF-8',
			'From: WP Bid Manager <no-reply@wordpress.org>'
		);

		wp_mail( $to, $subject, $message, $headers );
	}
}

function bm_retract_bid() {
	global $wpdb;

	$bm_bid_id = $_GET[ 'bid_accepted' ];
	$responseId = $_GET[ 'response_id' ];

	if ( sanitize_text_field( $_POST[ 'retractbid' ] ) !== "RETRACT" ) {
		echo '<p class="error">You must enter the word RETRACT, in all caps, in the field to retract this bid.</p>';
	}

	//  Update the bids table to reflect the bid is not accepted
	$query = "UPDATE " . BM_BIDS . " SET accepted_flag = 0 WHERE bid_id = %d";
	$data = array(
		$bm_bid_id
	);

	$query = $wpdb->prepare( $query, $data );
	$wpdb->query( $query );

	//  Update the bids responses table to reflect the bid is not accepted
	$query = "UPDATE " . BM_BIDS_RESPONSES . " SET bid_accepted = 0 WHERE bid_id = %d AND id = %d";
	$data = array(
		$bm_bid_id,
		$responseId
	);

	$query = $wpdb->prepare( $query, $data );
	$wpdb->query( $query );

	bm_user_emails();


}


function bm_create_bid() {
	global $wpdb;

	 $bm_user_id = get_current_user_id();
    //die;
	$bm_job_name = sanitize_text_field( $_POST[ 'job_name' ] );
	$dateNeeded = sanitize_text_field( $_POST[ 'date_needed' ] );
	//$enddateNeeded = sanitize_text_field( $_POST[ 'end_date_needed' ] );
	$bm_job_street = sanitize_text_field( $_POST[ 'job_street' ] );
	$bm_job_streetTwo = sanitize_text_field( $_POST[ 'job_street_two' ] );
	$bm_job_city = sanitize_text_field( $_POST[ 'job_city' ] );
	$bm_job_state = sanitize_text_field( $_POST[ 'job_state' ] );
	$bm_job_zip = sanitize_text_field( $_POST[ 'job_zip' ] );
	$bid_notes = stripslashes($_POST[ 'bid_notes' ]);
	$show_response_form = 1;
	$show_invite = 0;

	$bid_options = array(
		'response_form' => $show_response_form,
		'show_invite'   => $show_invite
	);

	$bid_options = json_encode( $bid_options );

	$address = $bm_job_street . ', ' . $bm_job_city . ' ' . $bm_job_state . ' ' . $bm_job_zip;

	$geocode = bm_get_lat_and_lng( $address );
	if ( $geocode !== FALSE ) {
		// save $geocode[�lat�] and $geocode[�lng�] to database
		$bm_lat = $geocode[ 'lat' ];
		$bm_lng = $geocode[ 'lng' ];
	}

	// Converts a date field to MYSQL standard: ex: "5/19/2015" => "2015-5-19 23:15:05"
	$dateNeeded = date( 'Y-m-d H:i:s', strtotime( $dateNeeded ) );
	//$enddateNeeded = date( 'Y-m-d H:i:s', strtotime( $enddateNeeded ) );

	$bm_file_path = bm_handle_file_upload( 'bmuser_bid_file', 'bid_requests_new/' );
	
	/* $query = "INSERT INTO " . BM_BIDS . " (bmuser_id, job_name, date_needed, end_date_needed, bmuser_bid_file, job_street, job_street_two, job_city, job_state, job_zip, bid_notes, lat, lng, bid_options)" */

	$query = "INSERT INTO " . BM_BIDS . " (bmuser_id, job_name, date_needed, bmuser_bid_file, job_street, job_street_two, job_city, job_state, job_zip, bid_notes, lat, lng, bid_options)" .
		"VALUES (%d, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s);";
	$data = array(
		$bm_user_id,
		$bm_job_name,
		$dateNeeded,
		//$enddateNeeded,
		$bm_file_path,
		$bm_job_street,
		$bm_job_streetTwo,
		$bm_job_city,
		$bm_job_state,
		$bm_job_zip,
		$bid_notes,
		$bm_lat,
		$bm_lng,
		$bid_options
	);

	$query = $wpdb->prepare( $query, $data );
	$wpdb->query( $query );
	
	$project_id = $wpdb->insert_id;
	
	if(is_admin()){
		$query2 = "INSERT INTO client_project_payment (client_id, project_id, payment_status)" .
		"VALUES (%d, %d, %s);";
	$data2 = array(
		$bm_user_id,
		$project_id,
		'Completed'
	);
	}
	else{
		
		$query2 = "INSERT INTO client_project_payment (client_id, project_id, payment_status)" .
		"VALUES (%d, %d, %s);";
	$data2 = array(
		$bm_user_id,
		$project_id,
		'unpaid'
	);
	}
	
	$query2 = $wpdb->prepare( $query2, $data2 );
	$wpdb->query( $query2 );
	
}

function bm_hide_bid() {

	global $wpdb;

	$bm_bid_id = (int)$_GET[ 'bid_id' ];
	// echo $bm_bid_id;
	$responseId = (int)$_GET[ 'response_id' ];
	// echo $responseId;

	$query = "UPDATE " . BM_BIDS_RESPONSES . " SET hidden = %d WHERE id = %d AND bid_id = %d";
	$data = array(
		1,
		$responseId,
		$bm_bid_id
	);

	$query = $wpdb->prepare( $query, $data );
	$wpdb->query( $query );

	if ( $_GET[ 'hide' ] == 'true' ) {

		$link = 'admin.php';
		$params = array('page' => 'bid_manager_dashboard', 'bid_response' => $bm_bid_id);
		$link = add_query_arg( $params, $link );
		$link = esc_url( $link, '', 'db' );
		if ( ! is_admin() ) {
			$link = get_permalink() . '?bid_response=' . $bm_bid_id;
		}

		$content = '<p class="success">You have successfully hidden this bid.</p>';
		$content .= '<p><a href="' . $link . '">&laquo Back to Bid</a></p>';

		echo $content;
	}
}

function bm_unhide_bid() {

	global $wpdb;

	$bm_bid_id = (int)$_GET[ 'bid_id' ];
	$responseId = (int)$_GET[ 'response_id' ];

	$query = "UPDATE " . BM_BIDS_RESPONSES . " SET hidden = 0 WHERE bid_id = %d AND id = %d";
	$data = array(
		$bm_bid_id,
		$responseId
	);

	$query = $wpdb->prepare( $query, $data );
	$wpdb->query( $query );

	if ( $_GET[ 'unhide' ] == 'true' ) {

		$link = 'admin.php';
		$params = array('page' => 'bid_manager_dashboard', 'bid_response' => $bm_bid_id);
		$link = add_query_arg( $params, $link );
		$link = esc_url( $link, '', 'db' );
		if ( ! is_admin() ) {
			$link = get_permalink() . '?bid_response=' . $bm_bid_id;
		}

		$content = '<p class="success">You have un-hidden this bid.</p>';
		$content .= '<p><a href="' . $link . '">&laquo Back to Bid</a></p>';

		echo $content;
	}

}

function bm_view_hidden() {

	global $wpdb;

	$bm_bid_id = (int)$_GET[ 'bid_id' ];

	$bm_user_id = get_current_user_id();

	$query = "SELECT " . BM_BIDS_RESPONSES . ".id, " . BM_BIDS_RESPONSES . ".bid_id, " . BM_BIDS_RESPONSES . ".responder_busname, " . BM_BIDS_RESPONSES . ".responder_poc, " . BM_BIDS_RESPONSES . ".responder_phone, " . BM_BIDS_RESPONSES . ".responder_email, " . BM_BIDS_RESPONSES . ".quoted_total FROM " . BM_BIDS_RESPONSES . " LEFT OUTER JOIN " . BM_USER . " ON " . BM_USER . ".id=%d WHERE bid_id = %d AND hidden = %d";
	$data = array(
		$bm_user_id,
		$bm_bid_id,
		1
	);

	$query = $wpdb->prepare( $query, $data );
	$results = $wpdb->get_results( $query );

	$hiddenBids = '';

	if ( $results ) {
		$hiddenBids .= <<<HIDDENBIDS
		
		<div class="responder_response blue_table">
		<h2>Bids you have hidden from your results</h2>
		<div class="responsive_table_v">
		<table class="form-table blue_table" border="1" bordercolor="#000">
		<thead>
		<tr>
		<th>
		Name
		</th>
		<th>
		Point of Contact
		</th>
		<th>
		Phone
		</th>
		<th>
		Email
		</th>
		<th>
		Quote Amount
		</th>
		<th>
		Show
		</th>
		</tr>
		</thead>
		<tbody>
HIDDENBIDS;

		foreach ( $results as $record ) {

			$link = 'admin.php';
			$params = array('page' => 'bid_manager_dashboard', 'bid_id' => $record->bid_id, 'response_id' => $record->id, 'unhide' => 'true');
			$link = add_query_arg( $params, $link );
			$link = esc_url( $link, '', 'db' );
			if ( ! is_admin() ) {
				$link = get_permalink() . '?bid_id=' . $record->bid_id . '&response_id=' . $record->id . '&unhide=true';
			}

			$row = '<tr>';
			$row .= '<td>' . stripslashes( $record->responder_busname ) . '</td>';
			$row .= '<td>' . stripslashes( $record->responder_poc ) . '</td>';
			$row .= '<td>' . $record->responder_phone . '</td>';
			$row .= '<td><a href="mailto:' . stripslashes( $record->responder_email ) . '">' . stripslashes( $record->responder_email ) . '</a></td>';
			$row .= '<td>$' . number_format( $record->quoted_total, 2 ) . '</td>';
			$row .= '<td><a class="button" href="' . $link . '">Un-hide &raquo;</a></td>';
			$row .= '</tr>';

			$hiddenBids .= $row;
		}

		$hiddenBids .= <<<HIDDENBIDS
		</tbody>
		</table>
		</div>
		</div>
HIDDENBIDS;

		echo $hiddenBids;

	} else {
		$dboard = BM_CDBOARD;
		$hiddenBids .= <<<HIDDENBIDS
			<p>You do not have any hidden bids to show.</p>
			<p><a class="button" href="{$dboard}">&laquo; Back to Dashboard</a></p>
HIDDENBIDS;

		echo $hiddenBids;
	}
}

function bm_create_user_record() {
	global $wpdb;

	$bm_user_id = get_current_user_id();

	$content = '';

	$bm_user_business = sanitize_text_field( $_POST[ 'comp_info_0' ] );
	$bm_user_poc = sanitize_text_field( $_POST[ 'comp_info_1' ] );
	$bm_user_phone = sanitize_text_field( $_POST[ 'comp_info_2' ] );
	$bm_user_email = sanitize_text_field( $_POST[ 'comp_info_3' ] );
	$bm_user_street = sanitize_text_field( $_POST[ 'comp_info_4' ] );
	$bm_user_street2 = sanitize_text_field( $_POST[ 'comp_info_5' ] );
	$bm_user_city = sanitize_text_field( $_POST[ 'comp_info_6' ] );
	$bm_user_state = sanitize_text_field( $_POST[ 'comp_info_7' ] );
	$bm_user_zip = sanitize_text_field( $_POST[ 'comp_info_8' ] );

	$address = $bm_user_street . ', ' . $bm_user_city . ' ' . $bm_user_state . ' ' . $bm_user_zip;

	$geocode = bm_get_lat_and_lng( $address );
	if ( $geocode !== FALSE ) {
		// save $geocode[�lat�] and $geocode[�lng�] to database
		$bm_lat = $geocode[ 'lat' ];
		$bm_lng = $geocode[ 'lng' ];
	}

	$query = "INSERT INTO " . BM_USER . " (id, bmuser_busname, bmuser_poc, bmuser_phone, bmuser_email, bmuser_street, bmuser_street_two, bmuser_city, bmuser_state, bmuser_zip, lat, lng)" .
		"VALUES (%d, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s);";
	$data = array(
		$bm_user_id,
		$bm_user_business,
		$bm_user_poc,
		$bm_user_phone,
		$bm_user_email,
		$bm_user_street,
		$bm_user_street2,
		$bm_user_city,
		$bm_user_state,
		$bm_user_zip,
		$bm_lat,
		$bm_lng
	);

	$query = $wpdb->prepare( $query, $data );
	$wpdb->query( $query );

	$success = '<p class="success">Success! You have successfully added your company information.</p>';

	$content .= $success;
	
	if(!is_admin())
	{
		$bm_user_id = get_current_user_id();
		$user_rol = new WP_User( $bm_user_id );
		$user_roles=$user_rol->roles;
		
		
		if(in_array( 'customer', $user_roles ))
		{
			$content .= "<a href='".site_url()."/my-account/create-project/' class='button' >NEXT</a> ";
		}
		else
		{
			$content .= "<a href='".site_url()."/creates-a-bid/' class='button' >NEXT</a> ";
		}
	}
	
	echo $content;
exit;
}

function bm_user_bid_review($error = '') {
	global $wpdb,$current_user;
	$current_user_member_level = $current_user->membership_level->ID;
	$bm_user_id = get_current_user_id();
	$user_rol = new WP_User( $bm_user_id );
	
	
	$user_roles=$user_rol->roles;
	if ( isset($_GET[ 'bmuser_bid' ]) ) {
		$recordId = (int)$_GET[ 'bmuser_bid' ];
	} elseif ( isset($_GET[ 'bmuser_bid_history' ]) ) {
		$recordId = (int)$_GET[ 'bmuser_bid_history' ];
	} elseif ( isset($_GET[ 'bid_response' ]) ) {
		$recordId = (int)$_GET[ 'bid_response' ];
	}

	$query = "SELECT * FROM " . BM_BIDS . " WHERE bid_id = %d";
	$data = array($recordId);
	$query = $wpdb->prepare( $query, $data );
	$record = $wpdb->get_row( $query );


	$bm_bid_id = $record->bid_id;
	$bm_job_name = stripslashes( $record->job_name );
	$requiredBy = date( 'F jS, Y', strtotime( $record->date_needed ) );
	$bm_job_street = stripslashes( $record->job_street );
	$bm_job_street2 = ($record->job_street_two ? $record->job_street_two : '');
	$bm_job_city = stripslashes( $record->job_city );
	$bm_job_state = stripslashes( $record->job_state );
	$bm_job_zip = $record->job_zip;
	$bm_user_file = stripslashes( $record->bmuser_bid_file );
	$bid_notes = stripslashes($record->bid_notes);


	$bm_user_bid_review = <<<CONTRACTORBIDREVIEW

	<div id="bid_response_bid_info" class="original_bid_info">
	<h1>Project Detail For: {$bm_job_name}</h1>

	<table class="form-table">
	<tr>
	<th scope="row">Project ID#:</th>
	<td>{$bm_bid_id}</td>
	</tr>
	<tr>
	<th scope="row">Project Name:</th>
	<td>{$bm_job_name}</td>
	</tr>
	<tr>
	<th scope="row">Expiry:</th>
	<td>{$requiredBy}</td>
	</tr>
	<tr>
	<th scope="row">Address:</th>
	<td>{$bm_job_street}</td>
	</tr>
CONTRACTORBIDREVIEW;

	if ( $bm_job_street2 ) {
		$bm_user_bid_review .= <<<CONTRACTORBIDREVIEW
	<tr>
	<th scope="row">Address Cont.:</th>
	<td>{$bm_job_street2}</td>
	</tr>
CONTRACTORBIDREVIEW;
	}

	$bm_user_bid_review .= <<<CONTRACTORBIDREVIEW
	<tr>
	<th scope="row">City:</th>
	<td>{$bm_job_city}</td>
	</tr>
	<tr>
	<th scope="row">Parish:</th>
	<td>{$bm_job_state}</td>
	</tr>
	</table>
	</div>
	<div id="bid_responder_responses">
    <p><a class="button-primary" href="{$bm_user_file}">Download original material list for: {$bm_job_name} &raquo;</a></p>
CONTRACTORBIDREVIEW;
	if ( ! empty($bid_notes) ) {
		$bm_user_bid_review .= <<<CONTRACTORBIDACTIVE
		<div id="bid_notes">
		<h3>About Project</h3>
		{$bid_notes}
		</div>
CONTRACTORBIDACTIVE;

	}
	$show_actions = get_option( 'bm_bid_response_actions' );

	$bm_user_bid_review .= <<<CONTRACTORBIDREVIEW
	<h2>Project Responses</h2>
CONTRACTORBIDREVIEW;


	$query = "SELECT * FROM " . BM_BIDS_RESPONSES . " WHERE bid_id = %d AND hidden = %d";
	$data = array(
		$recordId,
		0
	);

	$query = $wpdb->prepare( $query, $data );
	$results = $wpdb->get_results( $query );

	if ( $results ) {
		
		if($current_user_member_level == '8'){
			
			$bm_user_bid_review .= <<<CONTRACTORBIDREVIEW

		<div class="responder_response blue_table">
		<div class="responsive_table_v">
		<table id="bm_responses_list" class="form-table blue_table" border="1" bordercolor="#000">
		<thead>
		<tr>
		<th>
		Name
		</th>
		<th>
		Point of Contact
		</th>
		<th>
		Phone
		</th>
		<th>
		Email
		</th>
		<th>
		Quote Amount
		</th>
		
CONTRACTORBIDREVIEW;
		}
		elseif($current_user_member_level == '6' ||$current_user_member_level == '7' || $current_user_member_level == '9' || $current_user_member_level == '10'){
			
			$bm_user_bid_review .= <<<CONTRACTORBIDREVIEW

		<div class="responder_response blue_table">
		<div class="responsive_table_v">
		<table id="bm_responses_list" class="form-table blue_table" border="1" bordercolor="#000">
		<thead>
		<tr>
		<th>
		Name
		</th>
		<th>
		Point of Contact
		</th>
		<th>
		Phone
		</th>
		<th>
		Email
		</th>
		
		<th>
		Quote Amount
		</th>
		<th>
		Notes
		</th>
		<th>
		Responses
		</th>
		<th>
		Accept Bid
		</th>
CONTRACTORBIDREVIEW;
			
			
			
		}
		
		else{
		
		
		$bm_user_bid_review .= <<<CONTRACTORBIDREVIEW

		<div class="responder_response blue_table">
		<div class="responsive_table_v">
		<table id="bm_responses_list" class="form-table blue_table" border="1" bordercolor="#000">
		<thead>
		<tr>
		<th>
		Name
		</th>
		<th>
		Point of Contact
		</th>
		<th>
		Phone
		</th>
		<th>
		Email
		</th>
		<th>
		Quote Amount
		</th>
		<th>
		Notes
		</th>
		<th>
		Responses
		</th>
		
		
CONTRACTORBIDREVIEW;
		}

		if (is_admin()) {
			$bm_user_bid_review .= <<<CONTRACTORBIDREVIEW
			<th>
			Don't Show
		</th>
		<th>
		Accept Bid
		</th>
CONTRACTORBIDREVIEW;
		}


		$bm_user_bid_review .= <<<CONTRACTORBIDREVIEW
		</tr>
		</thead>
		<tbody>
CONTRACTORBIDREVIEW;
		foreach ( $results as $record ) {
			$row = '<tr>';
			$row .= '<td>' . stripslashes( $record->responder_busname ) . '</td>';
			$row .= '<td>' . stripslashes( $record->responder_poc ) . '</td>';
			$row .= '<td>' . $record->responder_phone . '</td>';
			$row .= '<td><a href="mailto:' . stripslashes( $record->responder_email ) . '">' . stripslashes( $record->responder_email ) . '</a></td>';
			
			
			$row .= '<td>$' . number_format( $record->quoted_total, 2 ) . '</td>';
			
			if($current_user_member_level == '6' ||$current_user_member_level == '7' || $current_user_member_level == '9' || $current_user_member_level == '10'){
			$row .= '<td>' . $record->responder_notes . '</td>';
			if ( ! empty($record->responder_bid_file) ) {

			
				$row .= '<td><a class="button button_blue" href="' . $record->responder_bid_file . '">View &raquo;</a>';

			} else {
				$row .= '<td>Not Available</td>';
			}
			}
			$link = 'admin.php';
			$params = array('page' => 'bid_manager_dashboard', 'bid_id' => $record->bid_id, 'response_id' => $record->id, 'hide' => 'true');
			$link = add_query_arg( $params, $link );
			$link = esc_url( $link, '', 'db' );
			if ( ! is_admin() ) {
				$link = get_permalink() . '?bid_id=' . $record->bid_id . '&response_id=' . $record->id . '&hide=true';
			}

			if (is_admin() || $current_user_member_level == '6' ||$current_user_member_level == '7' || $current_user_member_level == '9' || $current_user_member_level == '10') {
                
				if (is_admin()){
				$row .= '<td><a class="button button_red" href="' . $link . '">Hide &raquo;</a></td>';
				}
				$link = 'admin.php';
				$params = array('page' => 'bid_manager_dashboard', 'bid_id' => $record->bid_id, 'response_id' => $record->id, 'accept' => 'true');
				$link = add_query_arg( $params, $link );
				$link = esc_url( $link, '', 'db' );
				if ( ! is_admin() ) {
					if($current_user_member_level == '6'){
					$link = site_url().'/my-account/bid-projects/?bid_id=' . $record->bid_id . '&response_id=' . $record->id . '&accept=true';
					}
					elseif($current_user_member_level == '7' || $current_user_member_level == '9' || $current_user_member_level == '10'){
						$link = get_permalink() . '?bid_id=' . $record->bid_id . '&response_id=' . $record->id . '&accept=true';
					}
					else{
						$link = get_permalink() . '?bid_id=' . $record->bid_id . '&response_id=' . $record->id . '&accept=true';
					}
				}

				$row .= '<td><a class="button" href="' . $link . '">Accept &raquo;</a></td>';
			}
			$row .= '</tr>';

			$bm_user_bid_review .= $row;
		}

		$bm_user_bid_review .= <<<CONTRACTORBIDREVIEW
		</tbody>
		</table>
		</div>
		</div>
CONTRACTORBIDREVIEW;

		$query = "SELECT hidden FROM " . BM_BIDS_RESPONSES . " LEFT OUTER JOIN " . BM_BIDS . " ON " . BM_BIDS . ".bid_id=" . BM_BIDS_RESPONSES . ".bid_id WHERE " . BM_BIDS . ".bid_id = %d AND bmuser_id = %d AND hidden = %d";
		$data = array($recordId, $bm_user_id, 1);
		$query = $wpdb->prepare( $query, $data );
		$results = $wpdb->get_results( $query );

		if ( $results ) {

			$link = 'admin.php';
			$params = array('page' => 'bid_manager_dashboard', 'bid_id' => $record->bid_id, 'view_hidden' => 'true');
			$link = add_query_arg( $params, $link );
			$link = esc_url( $link, '', 'db' );
			if ( ! is_admin() ) {
				$link = get_permalink() . '?bid_id=' . $record->bid_id . '&view_hidden=true';
			}

			$bm_user_bid_review .= <<<CONTRACTORBIDREVIEW
			<p><a class="button" href="{$link}">View Hidden Bids &raquo;</a></p>
			</div>
			</div>
CONTRACTORBIDREVIEW;

			echo $bm_user_bid_review;
			exit;
		} else {
			$bm_user_bid_review .= <<<CONTRACTORBIDREVIEW
		</div>
CONTRACTORBIDREVIEW;

			echo $bm_user_bid_review;
		} // Ends hidden table display
	} else {
		$bm_user_bid_review .= <<<CONTRACTORBIDREVIEW
		<p>There are no reviews to this bid.</p>
CONTRACTORBIDREVIEW;

		$query = "SELECT hidden FROM " . BM_BIDS_RESPONSES . " LEFT OUTER JOIN " . BM_BIDS . " ON " . BM_BIDS . ".bid_id=" . BM_BIDS_RESPONSES . ".bid_id WHERE " . BM_BIDS . ".bid_id = %d AND bmuser_id = %d AND hidden = %d";
		$data = array($recordId, $bm_user_id, 1);
		$query = $wpdb->prepare( $query, $data );
		$results = $wpdb->get_results( $query );

		if ( $results ) {

			$link = 'admin.php';
			$params = array('page' => 'bid_manager_dashboard', 'bid_id' => $record->bid_id, 'view_hidden' => 'true');
			$link = add_query_arg( $params, $link );
			$link = esc_url( $link, '', 'db' );
			if ( ! is_admin() ) {
				$link = get_permalink() . '?bid_id=' . $record->bid_id . '&view_hidden=true';
			}

			$bm_user_bid_review .= <<<CONTRACTORBIDREVIEW
			<p><a class="button" href="{$link}">View Hidden Bids &raquo;</a></p>
CONTRACTORBIDREVIEW;

		}
	}


	//echo $bm_user_bid_review;
}

function bm_user_bid_active() {
	global $wpdb,$current_user;

	$activeRecord = sanitize_text_field( $_GET[ 'bmuser_bid_active' ] );

	$query = "SELECT * FROM " . BM_BIDS . " WHERE bid_id = %d";
	$data = array(
		$activeRecord
	);
	$query = $wpdb->prepare( $query, $data );
	$record = $wpdb->get_row( $query );

	if ( ! $record ) {
		echo '<div class="wrap"><h2>This is not the bid you are looking for....</h2></div>';

		return;
	}

	$bm_bid_id = $record->bid_id;
	$bm_job_name = stripslashes( $record->job_name );
	$requiredBy = $record->date_needed;
	$bm_job_street = stripslashes( $record->job_street );
	$bm_job_city = stripslashes( $record->job_city );
	$bm_job_street2 = ($record->job_street_two ? $record->job_street_two : '');
	$bm_job_state = stripslashes( $record->job_state );
	$bm_job_zip = $record->job_zip;
	$bid_notes = $record->bid_notes;
	$bm_user_file = stripslashes( $record->bmuser_bid_file );
	$bid_options = $record->bid_options;
	$bid_options = json_decode( $bid_options );

	if ( empty($bid_options) ) {
		$bid_options = array(
			'response_form' => 0,
			'show_invite'   => 0
		);
		$bid_options = json_encode( $bid_options );
		$bid_options = json_decode( $bid_options );
	}


	$requiredBy = date( 'F dS, Y', strtotime( $requiredBy ) );

	$bm_user_bid_active = <<<CONTRACTORBIDACTIVE
	
	<h1>Project Details For: {$bm_job_name}</h1>
	
	<table class="form-table">
	<tr>
	<th scope="row">Project ID#:</th>
	<td>{$bm_bid_id}</td>
	</tr>
	<tr>
	<th scope="row">Project Name:</th>
	<td>{$bm_job_name}</td>
	</tr>
	<tr>
	<th scope="row">Expiry:</th>
	<td>{$requiredBy}</td>
	</tr>
	<tr>
	<th scope="row">Street:</th>
	<td>{$bm_job_street}</td>
	</tr>
CONTRACTORBIDACTIVE;

	if ( $bm_job_street2 ) {
		$bm_user_bid_active .= <<<CONTRACTORBIDACTIVE
	<tr>
	<th scope="row">Address Cont.:</th>
	<td>{$bm_job_street2}</td>
	</tr>
CONTRACTORBIDACTIVE;
	}

	$bm_user_bid_active .= <<<CONTRACTORBIDACTIVE
	<tr>
	<th scope="row">City:</th>
	<td>{$bm_job_city}</td>
	</tr>
	<tr>
	<th scope="row">Parish:</th>
	<td>{$bm_job_state}</td>
	</tr>
	
CONTRACTORBIDACTIVE;
	if ( ! empty($bid_notes) ) {
		$bm_user_bid_active .= <<<CONTRACTORBIDACTIVE
		<tr><td colspan="2">
		<div id="bid_notes">
		<h3>About Project</h3>
		{$bid_notes}
		</div></td></tr>
CONTRACTORBIDACTIVE;
	}
	$bm_user_bid_active .= <<<CONTRACTORBIDACTIVE
	</table>
CONTRACTORBIDACTIVE;

$user_id = get_current_user_id();
					$user_rol = new WP_User( $user_id );

 $user_roles=$user_rol->roles;
 $current_user_member_level = $current_user->membership_level->ID;
	 
	if ( $bid_options->response_form == 1 && (! is_admin()) && $current_user_member_level != '7' && $current_user_member_level != '6' && $current_user_member_level != '9' && $current_user_member_level != '10') {

		$isResponseSubmitted = isset($_POST[ 'responder_bid_response' ]);

		if ( $isResponseSubmitted ) {
			bm_update_bid();
			bm_send_email();

			$content = '<p class="success">You have submitted your response.</p>';

			return $content;

		}
		
	
       $rowcount = $wpdb->get_var("SELECT count(*) FROM wp_bm_bids_responses WHERE bid_id ='".$activeRecord."' AND bider_id ='".$user_id."'");
	  if($rowcount == 0){
		  
		  $sampleFileUrl = site_url().'/wp-content/uploads/2016/07/Sample-Estimate.docx';
		  
		$bm_user_bid_active .= <<<SUPPLIERBIDRESPONSE

	<form action="" method="post" enctype="multipart/form-data">
	<fieldset>
	<table class="form-table">
	<tr>
	<td colspan="2"><h2>Please fill out the information below to respond to the project.</h2></td>
	</tr>
	<tr>
	<th scope="row"><span class="required">*</span>Client Name:</th>
	<td><input name="responder_busname" id="responder_busname" placeholder="ex: Jon's Supplying" type="text" required></td>
	</tr>
	<tr>
	<th scope="row"><span class="required">*</span>Point of Contact:</th>
	<td><input name="responder_poc" id="responder_poc" placeholder="ex: Jon Doe" type="text" required></td>
	</tr>
	<tr>
	<th scope="row"><span class="required">*</span>Address:</th>
	<td><input name="responder_address" id="responder_address" placeholder="ex: 123 Sesame St." type="text" required></td>
	</tr>
	<tr>
	<th scope="row">Address (cont.):</th>
	<td><input name="responder_address_cont" id="responder_address_cont" placeholder="ex: Unit 10A" type="text"></td>
	</tr>
	<tr>
	<th scope="row"><span class="required">*</span>City:</th>
	<td><input name="responder_city" id="responder_city" placeholder="ex: Denver" type="text" required></td>
	</tr>
	<tr>
	<th scope="row"><span class="required">*</span>Parish:</th>
	<td><input name="responder_state" id="responder_state" placeholder="ex: Colorado" type="text" required></td>
	</tr>
	
	<tr>
	<th scope="row"><span class="required">*</span>Phone:</th>
	<td><input name="responder_phone" id="responder_phone" placeholder="ex: 555.555.5555" type="phone" required></td>
	</tr>
	<tr>
	<th scope="row"><span class="required">*</span>Email:</th>
	<td><input name="responder_email" id="responder_email" placeholder="ex: jdoe@yahoo.com" type="email" required></td>
	</tr>
	<tr>
	<td colspan="2"><h2>Submit your response.</h2></td>
	</tr>
	<tr>
	<th scope="row"><span class="required">*</span>Bill Of Quantity:</th>
	<td><input name="responder_bid_file" id="responder_bid_file" type="file" required><p>Note:<a href="{$sampleFileUrl}">Download Sample File</a></p><p>This file is your quote back to the requester.  It is what they will see/read when they view your response.</p></td>
	</tr>
	<tr>
	<th scope="row"><span class="required">*</span>Total Amount Quoted:</th>
	<td><input name="quoted_total" id="quoted_total" type="text" required></td>
	</tr>
	<tr>
	<th scope="row">Special Notes:</th>
	<td><textarea cols="50" rows="10" name="responder_notes" id="responder_notes" placeholder="Type a message to the requester."></textarea></td>
	</tr>
	</table>
	</fieldset>
	<input type="hidden" name="bid_id" value="{$bm_bid_id}">
	<p><input id="submit" class="button button-primary" type="submit" name="responder_bid_response" value="Submit Response &raquo;"></p>
	</form>

SUPPLIERBIDRESPONSE;

	}
	}
	$content = $invite_desc = $add_email = '';

	if ( is_admin() || ($current_user_member_level == '6' || $current_user_member_level == '7' || $current_user_member_level == '9' || $current_user_member_level == '10')) {
		//$add_email = '<p><button id="addScnt" class="button-secondary">Add another email</button></p>';
		$bm_email_settings = BM_EMAIL_SETTINGS;
		$invite_desc = '<p>Send out an invitation for this bid to be reviewed or quoted on.  Remember, it\'s always a good idea to test it to yourself first.  And be sure to set your "<a href="' . $bm_email_settings . '">email settings</a></p>';
	}

	if ( ($bid_options->show_invite == 1 && (! is_admin())) || is_admin() || ($current_user_member_level == '6' || $current_user_member_level == '7' || $current_user_member_level == '9' || $current_user_member_level == '10') ) {
		
		
		
		 if(is_admin() || $current_user_member_level == '6' || $current_user_member_level == '7' || $current_user_member_level == '9' || $current_user_member_level == '10'){
			 
			 $content ='<form action="" method="post">';
	       $content .='<h2>Invitation Email</h2>';
       $users = $wpdb->get_results("SELECT SQL_CALC_FOUND_ROWS u.ID, u.display_name, u.user_email, UNIX_TIMESTAMP(u.user_registered) as joindate, mu.membership_id, mu.initial_payment, mu.billing_amount, mu.cycle_period, mu.cycle_number, mu.billing_limit, mu.trial_amount, mu.trial_limit, UNIX_TIMESTAMP(mu.startdate) as startdate, UNIX_TIMESTAMP(mu.enddate) as enddate, m.name as membership FROM $wpdb->users u LEFT JOIN $wpdb->pmpro_memberships_users mu ON u.ID = mu.user_id LEFT JOIN $wpdb->pmpro_membership_levels m ON mu.membership_id = m.id WHERE mu.membership_id > 0 AND mu.status = 'active' AND mu.membership_id = '8' GROUP BY u.ID ORDER BY u.user_registered DESC LIMIT 0, 15");
	   
       
         $content .='<select name="sup_invite_email"><option value="">Please Select Any</option>';
         foreach ($users as $user) {
          $content .='<option value="'.$user->user_email.'">' . $user->display_name.'</option>';
         }
        $content .='</select>';
		$content .='<select name="sup_invite_email_2"><option value="">Please Select Any</option>';
         foreach ($users as $user) {
          $content .='<option value="'.$user->user_email.'">' . $user->display_name.'</option>';
         }
        $content .='</select>';
		$content .='<select name="sup_invite_email_3"><option value="">Please Select Any</option>';
         foreach ($users as $user) {
          $content .='<option value="'.$user->user_email.'">' . $user->display_name.'</option>';
         }
        $content .='</select>';
    	$content .='<p><input class="button-primary" type="submit" name="sup_invite_submit" value="Invite &raquo;"></p>';
	     $content .='</form>';		
			 $bm_user_bid_active .= bm_responder_invite( $bm_bid_id );
		
		}
		 else{
		$bm_user_bid_active .= bm_responder_invite( $bm_bid_id );


		$content = <<<SUPINVITE
	<form action="" method="post">
	<h2>Invitation Email</h2>
	{$add_email}
	<div class="sc_email_wrap">
	<div class="sc_field_wrap">
	<p><input id="sup_invite_email" class="sup_invite_email" type="text" name="sup_invite_email" size="50" placeholder="ex. jondoe@jondoe.com" required></p>
	</div>
	</div>
	{$invite_desc}
	<p><input class="button-primary" type="submit" name="sup_invite_submit" value="Invite &raquo;"></p>
	</form>
SUPINVITE;
		 }
	}
	//echo  $content;
	//die;

	$bm_user_bid_active .= <<<CONTRACTORBIDACTIVE


	<p><a class="button button_blue download" href="{$bm_user_file}">Download original project detail for:  {$bm_job_name}</a></p>

CONTRACTORBIDACTIVE;


	echo $bm_user_bid_active . $content;
	if (is_admin()) {
		echo '<p><a class="button delete" href="' . admin_url('admin.php?page=bid_manager_dashboard&bm_action=delete_bid&bid_id=' . $bm_bid_id) . '">Delete This Bid &raquo;</a></p>';
	}
	/*elseif(in_array( 'customer', $user_roles )){
		echo '<p><a class="button delete" href="' . site_url().'/my-account/bid-projects/?bm_action=delete_bid&bid_id=' . $bm_bid_id . '">Delete This Bid &raquo;</a></p>';
		
	}*/

}

function bm_user_bid_past() {


	global $wpdb;

	$today = date( 'Y-m-d H:i:s' );

	$pastRecord = sanitize_text_field( $_GET[ 'bmuser_bid_past' ] );

	$query = "SELECT * FROM " . BM_BIDS . " WHERE bid_id = %d AND date_needed < %s";
	$data = array(
		$pastRecord,
		$today
	);
	$query = $wpdb->prepare( $query, $data );
	$results = $wpdb->get_results( $query );

	foreach ( $results as $record ) {
		$bm_bid_id = $record->bid_id;
		$bm_job_name = stripslashes( $record->job_name );
		$bm_job_street = stripslashes( $record->job_street );
		$bm_job_city = stripslashes( $record->job_city );
		$bm_job_state = stripslashes( $record->job_state );
		$bm_job_zip = $record->job_zip;
		$bm_user_file = stripslashes( $record->bmuser_bid_file );
		$date = date( 'F jS, Y', strtotime( $record->date_needed ) );
	}

	$bm_user_bid_past = <<<CONTRACTORBIDPAST
	
	<h1>Project Details For: {$bm_job_name}</h1>
	
	<table class="form-table">
	<tr>
	<th scope="row">Project ID#:</th>
	<td>{$bm_bid_id}</td>
	</tr>
	<tr>
	<th scope="row">Project Name:</th>
	<td>{$bm_job_name}</td>
	</tr>
	<tr>
	<th scope="row">Expiry:</th>
	<td>{$date}</td>
	</tr>
	<tr>
	<th scope="row">Street:</th>
	<td>{$bm_job_street}</td>
	</tr>
	<tr>
	<th scope="row">City:</th>
	<td>{$bm_job_city}</td>
	</tr>
	<tr>
	<th scope="row">Parish:</th>
	<td>{$bm_job_state}</td>
	</tr>
	</table>
	<p><a class="button button_blue download" target="_blank" href="{$bm_user_file}">Download original material list for:  {$bm_job_name}</a></p>
CONTRACTORBIDPAST;

	echo $bm_user_bid_past;
}

function bm_update_user_record() {
	global $wpdb;

	$bm_user_id = get_current_user_id();

	$content = '';

	$bm_user_business = sanitize_text_field( $_POST[ 'comp_info_0' ] );
	$bm_user_poc = sanitize_text_field( $_POST[ 'comp_info_1' ] );
	$bm_user_phone = sanitize_text_field( $_POST[ 'comp_info_2' ] );
	$bm_user_email = sanitize_text_field( $_POST[ 'comp_info_3' ] );
	$bm_user_street = sanitize_text_field( $_POST[ 'comp_info_4' ] );
	$bm_user_street2 = sanitize_text_field( $_POST[ 'comp_info_5' ] );
	$bm_user_city = sanitize_text_field( $_POST[ 'comp_info_6' ] );
	$bm_user_state = sanitize_text_field( $_POST[ 'comp_info_7' ] );
	$bm_user_zip = sanitize_text_field( $_POST[ 'comp_info_8' ] );

	$address = $bm_user_street . ', ' . $bm_user_city . ' ' . $bm_user_state . ' ' . $bm_user_zip;

	$geocode = bm_get_lat_and_lng( $address );
	if ( $geocode !== FALSE ) {
		// save $geocode[�lat�] and $geocode[�lng�] to database
		$bm_lat = $geocode[ 'lat' ];
		$bm_lng = $geocode[ 'lng' ];
	}

	$query = "UPDATE " . BM_USER . " SET bmuser_busname = '%s', bmuser_poc = '%s', bmuser_phone = '%s', bmuser_email = '%s', bmuser_street = '%s', bmuser_street_two = '%s', bmuser_city = '%s', bmuser_state = '%s', bmuser_zip = '%s', lat = '%s', lng = '%s' WHERE id = %d";
	$data = array(
		$bm_user_business,
		$bm_user_poc,
		$bm_user_phone,
		$bm_user_email,
		$bm_user_street,
		$bm_user_street2,
		$bm_user_city,
		$bm_user_state,
		$bm_user_zip,
		$bm_lat,
		$bm_lng,
		$bm_user_id
	);
	$query = $wpdb->prepare( $query, $data );
	$wpdb->get_results( $query );

	$success = '<p class="success">Success! Your company information has been updated.';

	
	
	
	if(!is_admin())
	{
		$bm_user_id = get_current_user_id();
		$user_rol = new WP_User( $bm_user_id );
		$user_roles=$user_rol->roles;
		
		
		if(in_array( 'customer', $user_roles ))
		{
			$success .= "<a href='".site_url()."/my-account/create-project/' class='button' >NEXT</a> </p>";
		}
		else
		{
			$success .= "<a href='".site_url()."/creates-a-bid/' class='next_button' >NEXT</a> </p>";
		}
	}
	
	$content .= $success;
	echo $content;

}

function bm_validate_form() {

	$bidJobName = sanitize_text_field( $_POST[ 'job_name' ] );
	$bidNeededBy = sanitize_text_field( $_POST[ 'date_needed' ] );
	$bidMaterialList = $_FILES[ 'bmuser_bid_file' ];
	$bidJobStreet = sanitize_text_field( $_POST[ 'job_street' ] );
	$bidJobStreetTwo = sanitize_text_field( $_POST[ 'job_street_two' ] );
	$bidJobCity = sanitize_text_field( $_POST[ 'job_city' ] );
	$bidJobState = sanitize_text_field( $_POST[ 'job_state' ] );
	$bidJobZip = sanitize_text_field( $_POST[ 'job_zip' ] );

	$address = $bidJobStreet . ', ' . $bidJobCity . ' ' . $bidJobState . ' ' . $bidJobZip;

	$geocode = bm_get_lat_and_lng( $address );

	$errorMaterialList = '';

	// var_dump(number_format($geocode['lat'], 7));
	if ( $geocode !== FALSE ) {
		// save $geocode[�lat�] and $geocode[�lng�] to database
		$bm_lat = $geocode[ 'lat' ];
		$bm_lng = $geocode[ 'lng' ];
	}

	if ( empty($bidJobName) ) {
		$errorBidJobName = '<div class="error"><p>You must provide a job name.</p></div>';
	}

	if ( empty($bidNeededBy) ) {
		$errorNeededBy = '<div class="error"><p>You must provide a date you need the quote by.</p></div>';
	}
	/*if ( empty($bidEndNeededBy) ) {
		$errorEndNeededBy = '<div class="error"><p>You must provide a End date you need the Stop quote by.</p></div>';
	}*/

	if ( empty($bidMaterialList[ 'name' ]) ) {
		$errorMaterialList .= '<div class="error"><p>You must provide a material list to the contractor.</p></div>';
	}

	$allowedFileType = array(
		'text/plain', // .txt
		'text/csv', // .csv
		'application/csv', // .csv alternative
		'text/comma-separated-values', // .csv alternative
		'application/zip', // .zip
		'application/x-zip-compressed',
		'application/msword', // .doc
		'application/vnd.openxmlformats-officedocument.wordprocessingml.document', // .docx
		'application/vnd.ms-excel', // .xls
		'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', // .xlsx
		'application/pdf', // .pdf
		'application/acrobat', // .pdf alternative
		'text/pdf', // .pdf alternative
		'text/x-pdf', // .pdf alternative
		'application/x-pdf' // .pdf alternative
	);

	/* $errorBidJobName = $errorNeededBy = $errorEndNeededBy = $errorMaterialList = $errorJobStreet = $errorJobCity = $errorJobState = $errorbidJobZip = $errorbidGeo = ''; */
	$errorBidJobName = $errorNeededBy = $errorMaterialList = $errorJobStreet = $errorJobCity = $errorJobState = $errorbidJobZip = $errorbidGeo = '';
	if ( ! in_array( $_FILES[ "bmuser_bid_file" ][ "type" ], $allowedFileType ) ) {
		$errorMaterialList .= '<div class="error"><p>Your file type <span style="display:none">'.$_FILES[ "bmuser_bid_file" ][ "type" ].'</span> is not supported.</p></div>';
	}

//	if ( empty($bidJobStreet) ) {
//		$errorJobStreet = '<div class="error"><p>You must provide the job street.</p></div>';
//	}
//
//	if ( empty($bidJobCity) ) {
//		$errorJobCity = '<div class="error"><p>You must provide the job city.</p></div>';
//	}
//
//	if ( empty($bidJobState) ) {
//		$errorJobState = '<div class="error"><p>You must provide the job state.</p></div>';
//	}
//
//	if ( empty($bidJobZip) ) {
//		$errorbidJobZip = '<div class="error"><p>You must provide the job zip code.</p></div>';
//	}

	// $bm_lat = (isset($bm_lat) ? $bm_lat : NULL);

	// We only want to run this error check if they actually have an active API set
	// if (get_user_meta(get_current_user_id(), 'bm_google_api_key', TRUE)) {

	// 	if ( $bm_lat == NULL || $bm_lat == 0.00000000 ) {
	// 		$errorbidGeo = '<div class="error"><p>You must provide a correct address.  Please check the address and try again.</p></div>';
	// 		echo $errorbidGeo;
	// 	}
	// }
	/*if ( ! $errorBidJobName && ! $errorNeededBy && ! $errorEndNeededBy && ! $errorMaterialList && ! $errorJobStreet && ! $errorJobCity && ! $errorJobState && ! $errorbidJobZip && ! $errorbidGeo ) {*/

	if ( ! $errorBidJobName && ! $errorNeededBy && ! $errorMaterialList && ! $errorJobStreet && ! $errorJobCity && ! $errorJobState && ! $errorbidJobZip && ! $errorbidGeo ) {
		return TRUE;
	} else {
		return array(
			$errorBidJobName,
			$errorNeededBy,
			//$errorEndNeededBy,
			$errorMaterialList,
			$errorJobStreet,
			$errorJobCity,
			$errorJobState,
			$errorbidJobZip,
			$errorbidGeo
		);
	}
}

function bm_bid_form() {

	global $wpdb,$current_user;
	
	$current_user_member_level = $current_user->membership_level->ID;

	$bm_user_id = get_current_user_id();

	// $bidJobName = $bidNeededBy = $bidJobStreet = $bidJobStreetTwo = $bidJobCity = $bidJobState = $bidJobZip = $bid_note = '';

	$bidJobName = (isset($_POST[ 'job_name' ]) ? sanitize_text_field( $_POST[ 'job_name' ] ) : '');
	$bidNeededBy = (isset($_POST[ 'date_needed' ]) ? sanitize_text_field( $_POST[ 'date_needed' ] ) : '');
	//$bidEndNeededBy = (isset($_POST[ 'end_date_needed' ]) ? sanitize_text_field( $_POST[ 'end_date_needed' ] ) : '');
	$bidJobStreet = (isset($_POST[ 'job_street' ]) ? sanitize_text_field( $_POST[ 'job_street' ] ) : '');
	$bidJobStreetTwo = (isset($_POST[ 'job_street_two' ]) ? sanitize_text_field( $_POST[ 'job_street_two' ] ) : '');
	$bidJobCity = (isset($_POST[ 'job_city' ]) ? sanitize_text_field( $_POST[ 'job_city' ] ) : '');
	$bidJobState = (isset($_POST[ 'job_state' ]) ? sanitize_text_field( $_POST[ 'job_state' ] ) : '');
	$bidJobZip = (isset($_POST[ 'job_zip' ]) ? sanitize_text_field( $_POST[ 'job_zip' ] ) : '');
	$bid_note = (isset($_POST[ 'bid_notes' ]) ? stripslashes($_POST[ 'bid_notes' ]) : '');
	$front_end_reply_form = (isset($_POST[ 'bid_form_on_page' ]) ? sanitize_text_field( $_POST[ 'bid_form_on_page' ] ) : '');
	$bid_invite = (isset($_POST[ 'bid_invite' ]) ? sanitize_text_field( $_POST[ 'bid_invite' ] ) : '');

	$nameReturn = ($bidJobName ? stripslashes( $bidJobName ) : '');
	$neededReturn = ($bidNeededBy ? $bidNeededBy : '');
	//$neededEndReturn = ($bidEndNeededBy ? $bidEndNeededBy : '');
	$streetReturn = ($bidJobStreet ? stripslashes( $bidJobStreet ) : '');
	$streetTwoReturn = ($bidJobStreetTwo ? stripslashes( $bidJobStreetTwo ) : '');
	$cityReturn = ($bidJobCity ? stripslashes( $bidJobCity ) : '');
	$stateReturn = ($bidJobState ? $bidJobState : '');
	$zipReturn = ($bidJobZip ? $bidJobZip : '');
	$frontEndFormReturn = ($front_end_reply_form ? $front_end_reply_form : '');
	$bidInviteReturn = ($bid_invite ? $bid_invite : '');

	if ( is_user_logged_in() ) {
		if ( isset($_POST[ 'new_bid' ]) ) {
			$validate = bm_validate_form();
			if ( TRUE === $validate ) {
				bm_create_bid();
			} else {
				list(
					$errorBidJobName, 
					$errorNeededBy, 
					// $errorEndNeededBy, 
					$errorMaterialList, 
					$errorJobStreet, 
					$errorJobCity, 
					$errorJobState, 
					$errorbidJobZip,
					$errorbidGeo
				) = $validate;
			}
		}


		if ( isset($_POST[ 'new_bid' ]) && $validate === TRUE ) {

			
			$user_id = get_current_user_id();
					$user_rol = new WP_User( $user_id );

 $user_roles=$user_rol->roles;
 if($current_user_member_level == '6'){
	  $bm_new_bid = site_url().'/my-account/create-project/';
	  $all_project_link = site_url().'/my-account/bid-projects/';
	  
 }
 elseif($current_user_member_level == '7' || $current_user_member_level == '9' || $current_user_member_level == '10'){
	 $bm_new_bid = get_permalink('1727');
	 $all_project_link = site_url().'/all-my-bid';
	 
 }
 else{
	 $bm_new_bid = BM_CBID;
 }

			?>
			<p class="success">Your project has been saved.<a href="<?php echo $all_project_link; ?>" class="next_button">All My Bids</a></p>
			<p><a href="<?php echo $bm_new_bid; ?>">&laquo Back to New project</a></p>
			<?php
		} else {

			?>

			<div class="wrap">

				<h1>Project Form</h1>
<link href = "https://code.jquery.com/ui/1.10.4/themes/ui-lightness/jquery-ui.css"
         rel = "stylesheet">
      
      <script src = "https://code.jquery.com/ui/1.10.4/jquery-ui.js"></script>
  <script>
  jQuery( function() {
    jQuery( "#datepicker" ).datepicker({dateFormat: 'mm/dd/yy'});
  } );
  </script>
				<form action="" method="post" enctype="multipart/form-data">
					<fieldset>
						<table class="form-table">
							<tr>
								<th scope="row"><span class="required">*</span>Project Name:</th>
								<td><input name="job_name" id="job_name" placeholder="ex: Smith Residence" type="text"
										   value="<?php echo $nameReturn; ?>" required>

									<?php

									if ( isset($errorBidJobName) ) {
										echo $errorBidJobName;
									}
									?>

								</td>
							</tr>
							<tr>
								<th scope="row"><span class="required">*</span>Expiry Date:</th>
								<td><input name="date_needed" id="datepicker" placeholder="ex: mm/dd/yyyy" type="text"
										   value="<?php echo $neededReturn; ?>" required>

									<?php

									if ( isset($errorNeededBy) ) {
										echo $errorNeededBy;
									}
									?>

								</td>
							</tr>
							<?php /* <tr>
								<th scope="row"><span class="required">*</span>End Date:</th>
								<td><input name="end_date_needed" id="date_needed" placeholder="ex: mm/dd/yyyy" type="date"
										   value="<?php echo $neededEndReturn; ?>" required>

									<?php

									if ( isset($errorEndNeededBy) ) {
										echo $errorEndNeededBy;
									}
									?>

								</td>
							</tr> */ ?>
							<tr>
								<th scope="row"><span class="required">*</span>Attach File:</th>
								<td><input name="bmuser_bid_file" id="bmuser_bid_file" type="file" required>
									<?php

									if ( isset($errorMaterialList) ) {
										echo $errorMaterialList;
									}

									?>
									<span>Accepted formats:  <strong>.TXT, .DOC, .DOCX, .XLS, .CSV, .XLSX, .PDF,
											.ZIP</strong></span>
									<p><i><strong>NB.</strong> If more than one document is being uploaded, please compress them in a zip file.</i></p>
								</td>
							</tr>  
							
							
							<tr>
								<td colspan="2" >
								<h4 style="text-align:center;">About Project(in brief)</h4>
									<?php
									$args = array(
										'media_buttons' => TRUE,
										'textarea_name' => 'bid_notes'

									);

									wp_editor( $bid_note, 'bid_notes_text', $args );
									?>
								</td>
							</tr>
							
							<tr>
								<td colspan="2"><h4 style="text-align:center;">Location</h4></td>
							</tr>
							<tr>
								<th scope="row">Address:</th>
								<td><input name="job_street" id="job_street" placeholder="ex: 123 My Street"
										   value="<?php echo $streetReturn; ?>">

									<?php

									if ( isset($errorJobStreet) ) {
										echo $errorJobStreet;
									}
									?>

								</td>
							</tr>
							<tr>
								<th scope="row">Address Cont.:</th>
								<td><input name="job_street_two" id="job_street_two" placeholder="ex: Unit #10 A"
										   value="<?php echo $streetTwoReturn; ?>"></td>
							</tr>
							<tr>
								<th scope="row">City:</th>
								<td><input name="job_city" id="job_city" placeholder="ex: Denver"
										   value="<?php echo $cityReturn; ?>">

									<?php

									if ( isset($errorJobCity) ) {
										echo $errorJobCity;
									}

									?>

								</td>
							</tr>
							<tr>
								<th scope="row">Parish:</th>
								<td><input name="job_state" id="job_state" placeholder="ex: Colorado"
										   value="<?php echo $stateReturn; ?>">

									<?php

									if ( isset($errorJobState) ) {
										echo $errorJobState;
									}
									?>

								</td>
							</tr>
							
						</table>
					</fieldset>
			<p><input id="submit" class="button button-primary" type="submit" name="new_bid" value="Submit Project &raquo;">
			</p>

			</form>
			</div>
			<?php
		}
	} else {
		echo bm_protected_content();
	}

}

function bm_user_bid_accepted() {

	global $wpdb;

	$bm_user_id = get_current_user_id();

	$recordId = (int)$_GET[ 'bid_accepted' ];
	$responseId = (int)$_GET[ 'response_id' ];

	$query = "SELECT * FROM " . BM_BIDS . " WHERE bid_id = %d AND bmuser_id = %d AND accepted_flag = %d";
	$data = array($recordId, $bm_user_id, 1);
	$query = $wpdb->prepare( $query, $data );
	$record = $wpdb->get_row( $query );


	$bm_bid_id = $record->bid_id;
	$bm_job_name = stripslashes( $record->job_name );
	$requiredBy = $record->date_needed;
	$requiredBy = strtotime( $requiredBy );
	$requiredBy = date( 'M jS, Y', $requiredBy );
	$bm_job_street = stripslashes( $record->job_street );
	$bm_job_city = stripslashes( $record->job_city );
	$bm_job_state = stripslashes( $record->job_state );
	$bm_job_zip = $record->job_zip;
	$bm_user_file = stripslashes( $record->bmuser_bid_file );
	$bm_bid_accepted = $record->accepted_flag;


	$bm_user_accepted = <<<BIDACCEPTED
	
	<div id="bid_response_bid_info" class="original_bid_info">
	<h1>Project Details For: {$bm_job_name}</h1>
	
	<table class="form-table">
	<tr>
	<th scope="row">Project ID#:</th>
	<td>{$bm_bid_id}</td>
	</tr>
	<tr>
	<th scope="row">Project Name:</th>
	<td>{$bm_job_name}</td>
	</tr>
	<tr>
	<th scope="row">Expiry:</th>
	<td>{$requiredBy}</td>
	</tr>
	<tr>
	<th scope="row">Street:</th>
	<td>{$bm_job_street}</td>
	</tr>
	<tr>
	<th scope="row">City:</th>
	<td>{$bm_job_city}</td>
	</tr>
	<tr>
	<th scope="row">Parish:</th>
	<td>{$bm_job_state}</td>
	</tr>
	</table>
	</div>
	<div id="bid_responder_responses">
    <p><a class="button button_blue download" target="_blank" href="{$bm_user_file}">Download original material list for: {$bm_job_name} &raquo;</a></p>
BIDACCEPTED;
?>
<table>
			<tr>
				<td>
					<?php echo $bm_user_accepted ?>
				</td>
			</tr>
			</table>
<?php
	/*if ( $bm_bid_accepted == 0 ) {
		?>
		<p><a class="button"
			  href="?bid_id=<?php echo $bm_bid_id; ?>&response_id=<?php echo $responseId; ?>&accept=true">Accept
				Quote &raquo;</a></p>
		<?php
	} elseif ( $bm_bid_accepted == 1 ) {

		?>

		<table>
			<tr>
				<td>
					<?php echo $bm_user_accepted ?>
				</td>
			</tr>
			<tr>
				<td>
					<p class="warning">You have already accepted this quote. Would you like to retract your acceptance?
						This will put the bid back in the active que to be bid on by other suppliers.</p>
				</td>
			</tr>
			<tr>
				<td>
					<form action="" method="post" enctype="multipart/form-data">
						<p><span class="required">*</span>Type the word "RETRACT" (all caps) in this box.</p>
						<input type="text" name="retractbid" placeholder="Type:  RETRACT" required>
						<p><span class="required">*</span>Below, type the reason for retracting the bid. This will be
							sent to the responder.</p>
						<?php

						$args = array(
							'media_buttons' => TRUE,
							'textarea_name' => 'retract_message'

						);

						wp_editor( '', 'retract_message_text', $args );

						?>
						<input class="button-primary" type="submit" name="retract_bid" value="Retract My Bid &raquo;">
					</form>
				</td>
			</tr>
		</table>
		<?php
	}*/

	if ( isset($_POST[ 'retract_bid' ]) ) {
		bm_retract_bid();
	}
}

function bm_user_dashboard() {
	global $wpdb;

	$bm_user_id = get_current_user_id();

	$today = date( 'Y-m-d H:i:s' );

	strtotime( $today );
	$content = '';
	$content .= '<div class="wrap">';

	if ( isset($_GET[ 'bid_accepted' ]) ) {
		return bm_user_bid_accepted();
	} elseif ( isset($_GET[ 'bmuser_bid_past' ]) ) {
		return bm_user_bid_past();
	} elseif ( isset($_GET[ 'bid_response' ]) ) {
		return bm_user_bid_review();
	} elseif ( isset($_GET[ 'bmuser_bid_active' ]) ) {
		return bm_user_bid_active();
	}elseif ( isset($_GET[ 'make_payment' ]) ) {
		return client_project_payment();
	}/* elseif ( isset($_GET[ 'sp_make_payment' ]) ) {
		return sp_project_payment();
	} */ elseif ( isset($_GET[ 'hide' ]) ) {
		return bm_hide_bid();
	} elseif ( isset($_GET[ 'view_hidden' ]) && $_GET[ 'view_hidden' ] == "true" ) {
		return bm_view_hidden();
	} elseif ( isset($_GET[ 'unhide' ]) && $_GET[ 'unhide' ] == "true" ) {
		return bm_unhide_bid();
	}



	if ($_GET['bm_action'] == 'delete_bid') {
		$bid_id = sanitize_text_field($_GET['bid_id']);
		if (! $bid_id ) {
			return '<div class="error"><p>To delete a bid you must have a bid id.</p></div>';
		}

		$query = "DELETE FROM " . BM_BIDS . " WHERE bid_id = %d";
		$data = array($bid_id);
		$query = $wpdb->prepare($query, $data);
		$wpdb->get_results($query);

		$content .= '<div class="notice notice-success"><p><strong>You successfully deleted bid ' . $bid_id . '</strong></p></div>';
	}


	// TODO:  Put this in it's own function and run it like the other ones above - Do the same for shortcodes.php
	//  Check to see if the quote was accepted by the requester.  If it was, save it into two tables (bm_bids and bm_bids_reponses)
	if ( isset($_GET[ 'accept' ]) && $_GET[ 'accept' ] == 'true' ) {

		$bm_bid_id = (int)$_GET[ 'bid_id' ];
		$responseId = (int)$_GET[ 'response_id' ];

		//  Run the update on the bm_bids table
		$query = "UPDATE " . BM_BIDS . " SET accepted_flag = %d WHERE bid_id = %d AND bmuser_id = %d";
		$data = array(
			1,
			$bm_bid_id,
			$bm_user_id
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


	// List active bids
	$active_bids = new WPBM_Bids();
	$content .= $active_bids->get_active_bids_table( $today );


	// List bids with responses
	$bids_with_responses = new WPBM_Bids();
	$content .= $bids_with_responses->get_bids_with_responses_table( $today );

	// List out Past Bids
	if(is_admin()){
	$past_bids = new WPBM_Bids();
	$content .= $past_bids->get_past_bids_table( $today );
    }
	// List accepted bids
	$accepted_bids = new WPBM_Bids();
	$content .= $accepted_bids->get_accepted_bids_table();
	
	$content .= '</div>';

	echo $content;
}

function bm_user_gmap() {
	global $wpdb;

	$bm_user_id = get_current_user_id();

	$query = "SELECT bmuser_busname, lat, lng FROM " . BM_USER . " WHERE id = %d";
	$data = array(
		$bm_user_id
	);
	$query = $wpdb->prepare( $query, $data );
	$results = $wpdb->get_results( $query );

	foreach ( $results as $record ) {
		$bm_user_name = $record->bmuser_busname;
		$bm_lat = $record->lat;
		$bm_lng = $record->lng;
	}

	$map = "'googleMap'";
	$title = (isset($bm_user_name) ? $bm_user_name : '');
	$load = "'load'";

	$blueBidsMarker = PLUGIN_ROOT . '/images/map_marker_blue.png';
	// $yellowBidsMarker = PLUGIN_ROOT . '/images/map_marker_yellow.png';
	$greenBidsMarker = PLUGIN_ROOT . '/images/map_marker_green.png';
	// $redBidsMarker = PLUGIN_ROOT . '/images/map_marker_red.png';

	$content = '';

	//  Query for the API key
	$key = get_user_meta( $bm_user_id, 'bm_google_api_key', TRUE );

	if ( $key ) {

		$content .= '<h2>Company location with your current active bids (does not show accepted or past due bids)</h2>';

		$content .= '<table class="wp-dash-table" border="1" bordercolor="#000" cellpadding="5" style="margin-bottom: 15px;">';
		$content .= '<thead>';
		$content .= '<tr>';
		$content .= '<th>';
		$content .= 'Your Company';
		$content .= '</th>';
		$content .= '<th>';
		$content .= 'Active Bids';
		$content .= '</th>';
		$content .= '</tr>';
		$content .= '</thead>';
		$content .= '<tbody>';
		$content .= '<tr>';
		$content .= '<td align="center">';
		$content .= '<img src="' . $greenBidsMarker . '">';
		$content .= '</td>';
		$content .= '<td align="center">';
		$content .= '<img src="' . $blueBidsMarker . '">';
		$content .= '</td>';
		$content .= '</tr>';
		$content .= '</tbody>';
		$content .= '</table>';

		$content .= <<<CONMAP
	<script
			src="https://maps.googleapis.com/maps/api/js?key={$key}">
		</script>
	<script>
	  function initialize() {
	  var myLatlng = new google.maps.LatLng({$bm_lat},{$bm_lng});
	  var mapOptions = {
	    zoom: 7,
	    center: myLatlng,
	    mapTypeId: google.maps.MapTypeId.HYBRID
	  }
	  var map = new google.maps.Map(document.getElementById({$map}), mapOptions);

	  var marker = new google.maps.Marker({
	    position: myLatlng,
	  	icon: "{$greenBidsMarker}",
	    map: map,
	    title: "{$title}"
	  });
CONMAP;


		$today = date( 'Y-m-d H:i:s' );

		strtotime( $today );

		$query = "SELECT bmuser_id, job_name, lat, lng FROM " . BM_BIDS . " WHERE date_needed > %s AND accepted_flag = %d";
		$data = array($today, 0);
		$query = $wpdb->prepare( $query, $data );
		$results = $wpdb->get_results( $query );

		foreach ( $results as $record ) {
			$conId = $record->bmuser_id;
			$bm_job_name = $record->job_name;
			$bm_lat = $record->lat;
			$bm_lng = $record->lng;
			$content .= <<<CONMAP

		  var marker = new google.maps.Marker({
		  	  icon: "{$blueBidsMarker}",
		      position: new google.maps.LatLng({$bm_lat},{$bm_lng}),
		      map: map,
		      title: "{$bm_job_name}"
		  });

		  var contractorBid{$conId} = new google.maps.Circle({
			  center: new google.maps.LatLng({$bm_lat},{$bm_lng}),
			  map: map,
			  strokeColor: "#fff600",
		  });
CONMAP;
		}


		$content .= <<<CONMAP
	}

		google.maps.event.addDomListener(window, {$load}, initialize);
	</script>
	<div id="googleMap" style="width:100%; height: 550px;"></div>
CONMAP;

		$query = "SELECT id FROM " . BM_USER . " WHERE id = %d";
		$data = array(
			$bm_user_id
		);
		$query = $wpdb->prepare( $query, $data );
		$results = $wpdb->get_results( $query );

		if ( $results ) {
			return $content;
		} else {
			$message = '<p>Please enter your <a href="' . CCINFO . '">business information</a> to show the Google Map.</p>';

			return $message;
		}

	}
}

function bm_user_info() {
	global $wpdb;

	$bm_user_id = get_current_user_id();

	$content = '';


// Check to see if the company information form has been submitted and update the record

	$isContractorInfoSubmitted = isset($_POST[ 'bmuser_company_info' ]);

	$query = "SELECT * FROM " . BM_USER;
	$results = $wpdb->get_results( $query );

	if ( $results ) {
		foreach ( $results as $record ) {
			if ( $isContractorInfoSubmitted && ($bm_user_id == $record->id) ) {
				$entryExists = TRUE;
			}
		}
	}

	if ( $isContractorInfoSubmitted ) {
		if ( $entryExists != TRUE ) {
			bm_create_user_record();
		} else if ( $entryExists == TRUE ) {
			bm_update_user_record();
		}
	}

	$query = "SELECT * FROM " . BM_USER . " WHERE id = %d";
	$data = array($bm_user_id);
	$query = $wpdb->prepare( $query, $data );
	$results = $wpdb->get_results( $query );

	$i = 0; // Sets dynamic number for input IDs

	if ( is_user_logged_in() ) {
		$content .= '<div class="wrap">';
		$content .= '<h1>Client Information</h1>';

		$content .= '<form action="" method="post" enctype="multipart/form-data">' . PHP_EOL;
		$content .= '<fieldset>' . PHP_EOL;
		$content .= '<table class="form-table">';

		if ( $results ) {
			foreach ( $results as $record ) {
				// It is VERY important to not change the order of the array below.  Doing so will change the indexes of the array and will have negative implications on saving the data
				$user_info = array(
					'Business Name'    => $record->bmuser_busname,
					'Point of Contact' => $record->bmuser_poc,
					'Phone'            => $record->bmuser_phone,
					'Email'            => $record->bmuser_email,
					'Street'           => $record->bmuser_street,
					'Street2'          => $record->bmuser_street_two,
					'City'             => $record->bmuser_city,
					'Parishes'            => $record->bmuser_state,
					
				);

				foreach ( $user_info as $k => $v ) {
					$content .= '<tr><td>' . ($k=='Business Name'?'Client Name':($k=='Parishes'?'Parish':$k)) . ': </td><td><input required="required" id="comp_info_' . $i . '" name="comp_info_' . $i . '" value="' . stripslashes( $v ) . '" type="text"></td></tr>';
					++$i;
				}
			}
		} else {

			/*
			 * Please read this comment before touching the code below.
			 */

			// It is VERY important to not change the order of the array below.  Doing so will change the indexes of the array and will have negative implications on saving the data
			$user_info = array(
				'Business Name'    => $record->bmuser_busname,
				'Point of Contact' => $record->bmuser_poc,
				'Phone'            => $record->bmuser_phone,
				'Email'            => $record->bmuser_email,
				'Street'           => $record->bmuser_street,
				'Street2'          => $record->bmuser_street_two,
				'City'             => $record->bmuser_city,
				'Parishes'            => $record->bmuser_state,
				
			);

			foreach ( $user_info as $k => $v ) {
				$content .= '<tr><td>' . ($k=='Business Name'?'Client Name':($k=='Parishes'?'Parish':$k))  . ': </td><td><input required="required" id="comp_info_' . $i . '" name="comp_info_' . $i . '" value="" type="text" placeholder="' . stripslashes( (($k=='Business Name'?'Client Name':($k=='Parishes'?'Parish':$k)) ) ) . '"></td></tr>';
				++$i;
			}
		}

		$content .= '</table>';
		$content .= '</fieldset>' . PHP_EOL;
		$content .= '<p><input id="submit" class="button button-primary" type="submit" name="bmuser_company_info" value="Update Client Info &raquo;"></p>' . PHP_EOL;
		$content .= '</form>' . PHP_EOL;

		$content .= bm_user_gmap();
		$content .= '</div>';

	} else {
		$content .= bm_protected_content();
	}

	echo $content;
}


function bm_responder_invite($bm_bid_id) {

	$content = '';

	/* Setup email */
	if ( isset($_POST[ 'sup_invite_submit' ]) && ! empty($_POST[ 'sup_invite_email' ]) ) {

		global $wpdb;

		$bm_user_id = get_current_user_id();

		$currentBid = $_GET[ 'bmuser_bid_active' ];
		$submissionDate = date( 'Y-m-d H:i:s' );

		$string = substr( str_shuffle( "0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ" ), 0, 24 );
		$hash = md5( $string );

		//  Store the info to the sc_responder_emails table to make a unique link for the responder

		$query = "INSERT INTO " . BM_EMAILS . " (id, bid_id, date, hash)" .
			"VALUES (%d, %d, %s, %s);";
		$data = array(
			'',
			$currentBid,
			$submissionDate,
			$hash
		);

		$query = $wpdb->prepare( $query, $data );
		$wpdb->get_results( $query );

		//  End data storage to sc_responder_emails table

		// Setup the invite email to the responder

		$query = "SELECT bmuser_busname FROM " . BM_USER . " WHERE id = %d";
		$data = array(
			$bm_user_id
		);

		$query = $wpdb->prepare( $query, $data );
		$results = $wpdb->get_results( $query );

		foreach ( $results as $record ) {
			$bm_user_busname = (empty($record->bmuser_busname)) ? 'WP Bid Manager' : $record->bmuser_busname;
		}

		$bm_user_busname = stripcslashes( $bm_user_busname );

		$bm_responderEmail = sanitize_text_field( $_POST[ "sup_invite_email" ] );

		$to = $bm_responderEmail; // The email the mail will be going to for certain

		// Optional emails

		if ( ! empty($_POST[ "sup_invite_email_2" ]) ) {
			$to .= ', ' . $_POST[ "sup_invite_email_2" ];
		}

		if ( ! empty($_POST[ "sup_invite_email_3" ]) ) {
			$to .= ', ' . $_POST[ "sup_invite_email_3" ];
		}

		if ( ! empty($_POST[ "sup_invite_email_4" ]) ) {
			$to .= ', ' . $_POST[ "sup_invite_email_4" ];
		}

		if ( ! empty($_POST[ "sup_invite_email_5" ]) ) {
			$to .= ', ' . $_POST[ "sup_invite_email_5" ];
		}

		if ( ! empty($_POST[ "sup_invite_email_6" ]) ) {
			$to .= ', ' . $_POST[ "sup_invite_email_6" ];
		}

		if ( ! empty($_POST[ "sup_invite_email_7" ]) ) {
			$to .= ', ' . $_POST[ "sup_invite_email_7" ];
		}

		if ( ! empty($_POST[ "sup_invite_email_8" ]) ) {
			$to .= ', ' . $_POST[ "sup_invite_email_8" ];
		}

		if ( ! empty($_POST[ "sup_invite_email_9" ]) ) {
			$to .= ', ' . $_POST[ "sup_invite_email_9" ];
		}

		if ( ! empty($_POST[ "sup_invite_email_10" ]) ) {
			$to .= ', ' . $_POST[ "sup_invite_email_10" ];
		}

		if ( ! empty($_POST[ "sup_invite_email_11" ]) ) {
			$to .= ', ' . $_POST[ "sup_invite_email_11" ];
		}
		
		
		
		$copy = stripslashes( get_user_meta( 1, 'bm_email_content', TRUE ) );
		
		$subject = stripslashes( get_user_meta( 1, 'bm_subject_line', TRUE ) );
		
		$from = stripslashes( get_user_meta( 1, 'email_from_name', TRUE ) );
		
		$email_from = stripslashes( get_user_meta( 1, 'bm_from_line', TRUE ) );

		// These ternary statements setup return values to use for if the variable is set or not set
		$subject = ($subject ? $subject : 'Invitation for Quote Response'); // The subject of the email
		$from = ($from ? 'From: ' . $from . ' <' . $email_from . '>' : 'From: ' . $bm_user_busname . ' <no-reply@wordpress.org>');

		$link_id = get_user_meta($bm_user_id, 'bm_invite_page', TRUE);

		//  Based on the ID retrieved above, we build the link and parameters to execute when a user lands on that page to review a quote request
		//$link = get_permalink( $link_id );
		
		$link = site_url().'/all-bidding-projects/?bmuser_bid_active='.$bm_bid_id;
		
		$params = array('invitation_id' => $bm_bid_id, 'hash' => $hash);
		$link = add_query_arg( $params, $link );
		$link = esc_url( $link, '', 'db' );

		//  Start message body
		$message = <<<MESSAGE
		<p style="font-family: Arial,Helvetica Neue,Helvetica,sans-serif; font-size: 14px; color: #444; margin: 0 0 15px 0; padding: 0;">Please follow the link below to sign in and review the quote request.</p>
		<p style="font-family: Arial,Helvetica Neue,Helvetica,sans-serif; font-size: 14px; color: #444; margin: 0 0 15px 0; padding: 0;"><a href="{$link}">Click here to view and respond</a>.</p>
MESSAGE;
		//  End message body

		if ( ! empty($copy) ) {
			$message = '<p style="font-family: Arial,Helvetica Neue,Helvetica,sans-serif; font-size: 14px; color: #444; margin: 0 0 15px 0; padding: 0;">' . $copy . '</p>' . $message;
		}

		$headers = array(
			'Content-Type: text/html; charset=UTF-8',
			$from
			// 'Cc: jon@supplyingcontractors.com'
		);

		wp_mail( $to, $subject, $message, $headers );

		//  End email setup
		$content .= '<p class="success">Success!  Your invitation has been sent to ' . $to . '</p>';

		$link = BM_CDBOARD;
		$params = array('bmuser_bid_active' => $currentBid);
		$link = add_query_arg( $params, $link );
		$link = esc_url( $link, '', 'db' );
        
		if(is_admin()){
		$content .= '<a class="button" href="' . $link . '">Send Another Invite &raquo;</a>';
		}
		echo $content;

	}


}





function client_project_payment(){
	$projectId = isset($_GET['make_payment']) ? $_GET['make_payment'] : 0;
	if(!$projectId){
		global $wp_query;
        $wp_query->set_404();
        status_header( 404 );
        get_template_part( 404 ); 
		exit();
	}
	?>
    <div style="float:left;padding-bottom: 24px;"  >
	    <p>If you want to show this project to service providers. Please pay <?= function_exists('wmc_get_price') ? wc_price(wmc_get_price(get_option( 'client_bid_price', 0 ))) : wc_price(get_option( 'client_bid_price', 0 ),['currency'=>'USD']) ?> as an all time fee for this project. To pay click on pay button. Thank You!</p>
		<a href="<?= chnu_get_bid_add_to_cart_url($projectId) ?>" class="btn btn-primary">Pay Now</a>
	</div>
	
	<?php
	return ;

	global $wpdb,$current_user,$wp;
    //session_start();
	$current_url = home_url(add_query_arg(array(),$wp->request));
	$current_user_member_level = $current_user->membership_level->ID;
	
	$aa = $_GET['make_payment'];
	
		$get_project_name = "select job_name from wp_bm_bids where bid_id = '".$aa."'";
	$rslt_ptoject_name = $wpdb->get_results($get_project_name);
		$paypal_payment_method = get_option( 'paypal_payment_method', '' );
		$paypal_url='';
		if($paypal_payment_method=='live_payment'){
		    $paypal_url	=	'https://www.paypal.com/cgi-bin/webscr';
		}else{
		    	$paypal_url	=	'https://www.sandbox.paypal.com/cgi-bin/webscr';
		}
	//$paypal_url	=	'https://www.sandbox.paypal.com/cgi-bin/webscr';
	//$paypal_url	=	'https://www.paypal.com/cgi-bin/webscr';
	
	$orderno = "PAYPALORDER-" . time();
	$card_orderno = time();
	$client_bid_price = get_option( 'client_bid_price', '' );
	$paypal_email_info = get_option( 'paypal_email_info', '' ); 

	?>
	<div style="float:left;padding-bottom: 24px;"  ><span>If you want to show this project to service providers. Please pay $<?php echo $client_bid_price; ?> as all time fee for this project. To pay click on pay button. Thank You!</span></div>
	<form name='paypal' action='<?php echo $paypal_url?>' method='post'>
	
	
	<div class="clearfix"></div>
	
	<input type="hidden" name="cmd" value="_cart" />
	<input type="hidden" name="upload" value="1" />
	<input type="hidden" name="item_name_1" value='<?php echo $rslt_ptoject_name[0]->job_name; ?>' />
	<input type="hidden" name="amount_1" value="<?php echo $client_bid_price; ?>" />
    <input type="hidden" name="business" value='<?php echo $paypal_email_info; ?>' />
	<input type="hidden" name="invoice" value='<?php echo $orderno?>' />
    <input type="hidden" name="on0_1" value='Description' />
    <input type="hidden" name="os0_1" value='Client Manager' />
	<input type="hidden" name="no_note" value="0" />
	<input type="hidden" name="currency_code" value='USD' />
	<input type="hidden" name="lc" value='US' />
    <input type="hidden" name="return" value="<?php echo $current_url.'/?make_payment='.$aa.'&pay_action=return&pmethod=paypal'; ?>" />
    <input type="hidden" name="cancel_return" value="<?php echo $current_url.'/?make_payment='.$aa.'&pay_action=cancel&pmethod=paypal'; ?>" />
	<input type="hidden" name="email" value="<?php echo $paypal_email_info; ?>" />
	<input type="hidden" name="cn" value="Test" />
	<input type="hidden" name="rm" value="2" />
    <input type="hidden" name="no_shipping" value="1" />
	<input type="hidden" name="custom" value="clientbidpro_<?php echo $aa;?>" />
    <input name="notify_url" value="<?php echo $current_url.'/?make_payment='.$aa.'&pay_action=ipn&pmethod=paypal'; ?>" type="hidden">
    <input type="submit" name="pay" value="Pay by Paypal">
		
	<a href="<?php echo $current_url;?>" class="pay-cancle">Cancel</a>
    </form>
<?php
	
	$data = array(
	'apiOperation' => 'CREATE_CHECKOUT_SESSION',
	'order' => array(
			'currency' => 'USD',
			'id' => $card_orderno,
			'amount' => $client_bid_price,
		),
	'interaction' => array(
			'operation' => 'PURCHASE',
	),
);
	
	
	
			$curl = curl_init();

			curl_setopt_array($curl, array(
			  CURLOPT_URL => "https://sagicorbank.gateway.mastercard.com/api/rest/version/56/merchant/CABHONTWK/session",
			  CURLOPT_RETURNTRANSFER => true,
			  CURLOPT_ENCODING => "",
			  CURLOPT_MAXREDIRS => 10,
			  CURLOPT_TIMEOUT => 0,
			  CURLOPT_FOLLOWLOCATION => true,
			  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
			  CURLOPT_CUSTOMREQUEST => "POST",
			  CURLOPT_POSTFIELDS =>json_encode($data),
			  CURLOPT_HTTPHEADER => array(
				"Authorization: Basic bWVyY2hhbnQuQ0FCSE9OVFdLOmEyZmY5ZTJiMDY5MTUxZDJlMDY5MDY0YTNlMjYyOWRk",
				"Content-Type: text/plain",
				"Cookie: TS01fb1f2f=01772feb4bc4d5c6f2f480ae58947d3794adfb5f24bb68f655aa6ef7c36c3c2160c44a509f624bbd4b4892934fa1801bfe539f8936"
			  ),
			));

			$response = curl_exec($curl);

			curl_close($curl);
			//echo $response;
			$result = json_decode($response);
		 $sessionID = $result->session->id;


?>
		<form style="margin-top: 50px;" name='card_pay' action='<?php echo '/sagicorbank_payment_gateway/?project_id='.$aa.'&cost='.$client_bid_price.'&desc='.$rslt_ptoject_name[0]->job_name.'&orderId='.$card_orderno.'&currency=USD&sessionId='.$sessionID; ?>' method='post'>
		<input type="submit" name="pay" value="Card Payment">
		
		</form>
<?php 
	
global $wpdb;
if(isset($_REQUEST['pay_error']) && $_REQUEST['pay_error'] == "card"){

			 echo $_REQUEST['result'];
}
 if(isset($_REQUEST['pay_cancel']) && $_REQUEST['pay_cancel'] == "card"){

			 echo $_REQUEST['result'];
}
if(isset($_REQUEST['pay_success']) && $_REQUEST['pay_success'] == "card"){

			// echo $_REQUEST['result'];
	$pdate  = date('Y-m-d H:i:s');
	$project_id = str_replace('clientbidpro_','', $_REQUEST['make_payment']);
			  $qry = "UPDATE client_project_payment SET txn_id = '".$_REQUEST['result']."',
                                                                            amount = '".$_REQUEST['cost']."',
                                                                            payment_status = 'success',
                                                                            payment_type = 'CARD PAY',
                                                                            pay_date = '".$pdate."'
                                                                            WHERE project_id = '".$project_id."'";
																			
		
        $wpdb->query($qry);
	echo 'Payment Successful !';
}

	
	global $wpdb;
	
	
if($_REQUEST['pay_action'] == 'return' && $_REQUEST['pmethod'] == 'paypal') {
    //echo "<pre>";print_r($_REQUEST);echo "</pre>";
	
        $usr_iid = get_current_user_id();
        $transid  = $_REQUEST['tx'];
        $amt  = $_REQUEST['amt'];
        $project_id  = $_REQUEST['cm'];
		
		//strpos('clientbidpro_', $project_id);
		$project_id = str_replace('clientbidpro_','', $project_id);
		
        $status  = $_REQUEST['st'];
        $pdate  = date('Y-m-d H:i:s');
		$payment_type = $_REQUEST['pmethod'];
		
		 $qry = "UPDATE client_project_payment SET txn_id = '".$transid."',
                                                                            amount = '".$amt."',
                                                                            payment_status = '".$status."',
                                                                            payment_type = '".$payment_type."',
                                                                            pay_date = '".$pdate."'
                                                                            WHERE project_id = '".$project_id."'";
																			
		
        $wpdb->query($qry);
		
		 $url = $current_url;
		
		wp_redirect($url);
		
	}   
}




function sp_project_payment(){
	
	global $wpdb;
    //session_start();
	$usr_iid = get_current_user_id();
	$aa = $_GET['sp_make_payment'];
	
	$get_tbl_data = "select sp_id,project_id from sp_bid_payment where sp_id = '".$usr_iid."' and project_id = '".$aa."'";
	$rslt = $wpdb->get_results($get_tbl_data);
	//print_r($rslt);
	
	$get_project_name = "select job_name from wp_bm_bids where bid_id = '".$aa."'";
	$rslt_ptoject_name = $wpdb->get_results($get_project_name);
	if(count($rslt) == 0){
	$qryv = "INSERT INTO sp_bid_payment SET sp_id = '".$usr_iid."', project_id = '".$aa."',payment_status = 'unpaid'";
																			
		
        $wpdb->query($qryv);
	
	}
	$paypal_email_info = get_option( 'paypal_email_info', '' ); 
	$paypal_payment_method = get_option( 'paypal_payment_method', '' );
			$paypal_url='';
		if($paypal_payment_method=='live_payment'){
		    $paypal_url	=	'https://www.paypal.com/cgi-bin/webscr';
		}else{
		    	$paypal_url	=	'https://www.sandbox.paypal.com/cgi-bin/webscr';
		}
	//$paypal_url	=	'https://www.sandbox.paypal.com/cgi-bin/webscr';
	//$paypal_url	=	'https://www.paypal.com/cgi-bin/webscr';
	 
	$orderno = "PAYPALORDER-" . time();
	$card_orderno = time();
	$bidder_bid_price = get_option( 'bidder_bid_price', '' );
	?>
	<div class="sp_payment">
	<form name='paypal' action='<?php echo $paypal_url?>' method='post'>
	
	<div style="float:left;padding-bottom: 24px;"  ><span>If you want to bid this project. Please pay $<?php echo $bidder_bid_price; ?> as all time fee for this bid. To pay click on pay button. Thank You!</span></div>
	<br>
	<div class="clearfix"></div>
	
	<input type="hidden" name="cmd" value="_cart" />
	<input type="hidden" name="upload" value="1" />
	<input type="hidden" name="item_name_1" value='<?php echo $rslt_ptoject_name[0]->job_name; ?>' />
	<input type="hidden" name="amount_1" value="<?php echo $bidder_bid_price; ?>" />
    <input type="hidden" name="business" value='<?php echo $paypal_email_info; ?>' />
	<input type="hidden" name="invoice" value='<?php echo $orderno?>' />
    <input type="hidden" name="on0_1" value='Description' />
    <input type="hidden" name="os0_1" value='Service Manager' />
	<input type="hidden" name="no_note" value="0" />
	<input type="hidden" name="currency_code" value='USD' />
	<input type="hidden" name="lc" value='US' />
    <input type="hidden" name="return" value="<?php echo get_the_permalink().'/?sp_make_payment='.$aa.'&pay_action=return&pmethod=paypal'; ?>" />
    <input type="hidden" name="cancel_return" value="<?php echo get_the_permalink().'/?sp_make_payment='.$aa.'&pay_action=cancel&pmethod=paypal'; ?>" />
	<input type="hidden" name="email" value="<?php echo $paypal_email_info; ?>" />
	<input type="hidden" name="cn" value="Test" />
	<input type="hidden" name="rm" value="2" />
    <input type="hidden" name="no_shipping" value="1" />
	<input type="hidden" name="custom" value="<?php echo $usr_iid; ?>_spbidpro_<?php echo $aa;?>" />
    <input name="notify_url" value="<?php echo site_url().'/?sp_make_payment='.$aa.'&pay_action=ipn&pmethod=paypal'; ?>" type="hidden">
    <input type="submit" name="pay" value="Pay by Paypal">
	<a href="<?php echo get_the_permalink();?>" class="pay-cancle">Cancel</a>
    </form>

<?php
	
	$data = array(
	'apiOperation' => 'CREATE_CHECKOUT_SESSION',
	'order' => array(
			'currency' => 'USD',
			'id' => $card_orderno,
			'amount' => $bidder_bid_price,
		),
	'interaction' => array(
			'operation' => 'PURCHASE',
	),
);
	
	
	
			$curl = curl_init();

			curl_setopt_array($curl, array(
			  CURLOPT_URL => "https://sagicorbank.gateway.mastercard.com/api/rest/version/56/merchant/CABHONTWK/session",
			  CURLOPT_RETURNTRANSFER => true,
			  CURLOPT_ENCODING => "",
			  CURLOPT_MAXREDIRS => 10,
			  CURLOPT_TIMEOUT => 0,
			  CURLOPT_FOLLOWLOCATION => true,
			  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
			  CURLOPT_CUSTOMREQUEST => "POST",
			  CURLOPT_POSTFIELDS =>json_encode($data),
			  CURLOPT_HTTPHEADER => array(
				"Authorization: Basic bWVyY2hhbnQuQ0FCSE9OVFdLOmEyZmY5ZTJiMDY5MTUxZDJlMDY5MDY0YTNlMjYyOWRk",
				"Content-Type: text/plain",
				"Cookie: TS01fb1f2f=01772feb4bc4d5c6f2f480ae58947d3794adfb5f24bb68f655aa6ef7c36c3c2160c44a509f624bbd4b4892934fa1801bfe539f8936"
			  ),
			));

			$response = curl_exec($curl);

			curl_close($curl);
			//echo $response;
			$result = json_decode($response);
		 $sessionID = $result->session->id;


?>
		<form style="margin-top: 50px;" name='card_pay' action='<?php echo '/sagicorbank_payment_gateway/sp_pay/?project_id='.$aa.'&cost='.$bidder_bid_price.'&desc='.$rslt_ptoject_name[0]->job_name.'&orderId='.$card_orderno.'&currency=USD&sessionId='.$sessionID; ?>' method='post'>
		<input type="submit" name="pay" value="Card Payment">
		
		</form>
<?php 
	
global $wpdb;
if(isset($_REQUEST['pay_error']) && $_REQUEST['pay_error'] == "card"){

			 echo $_REQUEST['result'];
}?>
<?php if(isset($_REQUEST['pay_cancel']) && $_REQUEST['pay_cancel'] == "card"){

			 echo $_REQUEST['result'];
}?>
<?php if(isset($_REQUEST['pay_success']) && $_REQUEST['pay_success'] == "card"){

			// echo $_REQUEST['result'];
	$pdate  = date('Y-m-d H:i:s');
	$project_id = str_replace('clientbidpro_','', $_REQUEST['make_payment']);
			  /*$qry = "UPDATE client_project_payment SET txn_id = '".$_REQUEST['result']."',
                                                                            amount = '".$_REQUEST['cost']."',
                                                                            payment_status = 'success',
                                                                            payment_type = 'CARD PAY',
                                                                            pay_date = '".$pdate."'
                                                                            WHERE project_id = '".$project_id."'";*/
			$qry = "UPDATE sp_bid_payment SET txn_id = '".$_REQUEST['result']."',
                                                                            amount = '".$_REQUEST['cost']."',
                                                                            payment_status = 'success',
                                                                            payment_type = 'CARD PAY',
                                                                            pay_date = '".$pdate."'
                                                                            WHERE project_id = '".$project_id[2]."' AND sp_id = '".get_current_user_id()."'";																
		
        $wpdb->query($qry);
	echo 'Payment Successful !';
}?>



	</div>
	<!--script>
	document.forms[0].submit();
	</script-->

	<?php
	
	global $wpdb;
	
	
if($_REQUEST['pay_action'] == 'return' && $_REQUEST['pmethod'] == 'paypal') {
    //echo "<pre>";print_r($_REQUEST);echo "</pre>";
	
       
        $transid  = $_REQUEST['tx'];
        $amt  = $_REQUEST['amt'];
        $project_id  = $_REQUEST['cm'];
		
		//strpos('clientbidpro_', $project_id);
		$project_id = explode('_',$project_id);
		
        $status  = $_REQUEST['st'];
        $pdate  = date('Y-m-d H:i:s');
		$payment_type = $_REQUEST['pmethod'];
		
		
		
		$qry = "UPDATE sp_bid_payment SET txn_id = '".$transid."',
                                                                            amount = '".$amt."',
                                                                            payment_status = '".$status."',
                                                                            payment_type = '".$payment_type."',
                                                                            pay_date = '".$pdate."'
                                                                            WHERE project_id = '".$project_id[2]."' AND sp_id = '".$project_id[0]."'";
																			
		
        $wpdb->query($qry);
		
		
		$url = get_the_permalink();
		wp_redirect($url);
		
	}
}