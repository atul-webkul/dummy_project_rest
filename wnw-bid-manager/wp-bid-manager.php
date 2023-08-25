<?php
/*
Plugin Name: WNW Bid Manager
Plugin URI: http://wp-bid-manager.com
Description: WordPress bid management system.  Create and manage bids. Then get quotes for those bids by sending them via email from the dashboard.
Version: 1.2.4
Author: WP Bid Manager
Author URI: http://wp-bid-manager.com
License: GPL2
*/

// No direct access allowed.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
// Setup constants to be used throught the plugin
define( "BM_SITE_URL", get_bloginfo( "url" ) );
define( "BM_CDBOARD", BM_SITE_URL . '/wp-admin/admin.php?page=bid_manager_dashboard' );
define( "BM_CINFO", BM_SITE_URL . '/wp-admin/admin.php?page=company_information' );
define( "BM_CBID", BM_SITE_URL . '/wp-admin/admin.php?page=new_bid' );
define( "BM_EMAIL_SETTINGS", BM_SITE_URL . '/wp-admin/admin.php?page=bm_email_settings/' );
define( "BM_REPORTING", BM_SITE_URL . '/wp-admin/admin.php?page=bm_report' );
define( "BM_BMSETTINGS", BM_SITE_URL . '/wp-admin/admin.php?page=bm_settings' );

define( "PLUGIN_ROOT", plugins_url( '/', __FILE__ ) ); // Plugin root folder

if ( ! defined( "BM_BIDS" ) ) {

	global $table_prefix;

	define( "BM_BIDS", $table_prefix . 'bm_bids' );
	define( "BM_USER", $table_prefix . 'bm_user' );
	define( "BM_RESPONDERS", $table_prefix . 'bm_responder' );
	define( "BM_BIDS_RESPONSES", $table_prefix . 'bm_bids_responses' );
	define( "BM_EMAILS", $table_prefix . 'bm_responder_emails' );
	define( "BM_NOTIFICATIONS", $table_prefix . 'bm_notifications' );
	define( "BM_USERMETA", $table_prefix . 'usermeta' );
	define( "BM_OPTIONS", $table_prefix . 'options' );

}

// Load all the files that are necessary for the plugin
require_once('includes/bm_user.php');
require_once('includes/bm_responder.php');
require_once('includes/reports.php');
require_once('includes/dbtables.php');
require_once('includes/ajax.php');
require_once('includes/shortcodes.php');
require_once('includes/notifications.php');
require_once('includes/classes/bids.class.php');

function bid_manager_menu() {
	$managing_user = get_option( 'bm_free_user' );
	add_options_page( 'Bid Manager', 'Bid Manager', 'manage_options', 'bid-manager-settings', 'bm_options' );

	if ($managing_user && $managing_user == get_current_user_id()) {
		add_menu_page( 'Bid Manager', 'Bid Manager', 'read', 'bid_manager', 'bm_main', 'dashicons-media-text', 3 );
		add_submenu_page( 'bid_manager', 'Dashboard', 'Dashboard', 'read', 'bid_manager_dashboard', 'bm_dashboard' );
		add_submenu_page( 'bid_manager', 'New Bid', 'New Bid', 'read', 'new_bid', 'bm_new_bid' );
		add_submenu_page( 'bid_manager', 'Company Information', 'Company Information', 'read', 'company_information', 'bm_company_info' );
		add_submenu_page( 'bid_manager', 'Reports', 'Reports', 'read', 'bm_report', 'bm_reports' );
		add_submenu_page( 'bid_manager', 'Emails', 'Email Settings', 'read', 'bm_email_settings', 'bm_user_email_settings' );
		add_submenu_page( 'bid_manager', 'BM Settings', 'BM Settings', 'read', 'bm_settings', 'bm_settings' );
		add_submenu_page( 'bid_manager', 'Support', 'Support', 'read', 'bm_support', 'bm_support' );
	}
}

function bm_support() {
	?>
	<div class="wrap">
		<h1>Bid Manager Support</h1>
		<p>We love to help!  And, we love to hear your ideas and feedback.  If you need support or have a suggestion please contact us at <a href="mailto:suppcontractors@gmail.com">suppcontractors@gmail.com</a></p>
		<h2>Shortcode Usage</h2>
		<p>You can display your bids on your website.  To do so, simply use the shortcode <strong>[bm-bid-display]</strong></p>
		<p>From there, you can use a few shortcode attributes to turn on/off bid displays.  For example:</p>
		<ul>
			<li>[bm-bid-display] - This will show all active bids only.  By default, the shortcode only displays active bids.</li>
			<li>[bm-bid-display with_responses="TRUE"] - This will display all active bids AND bids with responses.</li>
			<li>[bm-bid-display active_bids="FALSE" with_responses="TRUE"] - This will NOT show active bids and will only show the bids with responses.</li>
			<li>[bm-bid-display past_bids="TRUE"] - This will show all active bids and past bids (because active bids was not turned off like above).</li>
			<li>[bm-bid-display loggedin="TRUE"] - This will display all active bids ONLY for a logged in user of the website.</li>
		</ul>
		<h3>You can add as many or few shortcode attribues as you would like.  The only one that is set to "TRUE" by default is active bids.  The complete list of attributes are:</h3>
		<ul>
			<li><strong>loggedin</strong>: default FALSE</li>
			<li><strong>accepted_bids</strong>:  default FALSE</li>
			<li><strong>past_bids</strong>: default FALSE</li>
			<li><strong>with_responses</strong>: default FALSE</li>
			<li><strong>active_bids</strong>: default TRUE</li>
		</ul>
	</div>
	<?php
}

function bm_options() {
	?>
	<div class="wrap">
	<?php

	if ( isset($_POST[ 'bm_options_submit' ]) ) {
		$user = $_POST[ 'author' ];
		update_option( 'bm_free_user', $user );
		?>
		<p class="success">You have successfully chosen a managing user.  If the "Bid Manager" menu item did not appear on the left, please refresh the page.</p>
		<?php
	}

	$managing_user = get_option( 'bm_free_user' );
	if ( $managing_user == FALSE ) {
		$managing_user = FALSE;
	}


	?>
	<h2>Select Managing User</h2>
	<p>This version is free. You can manage bids with one user. Please select that user below. Once selected, this user
		cannot be changed.</p>
	<?php
	if ( $managing_user ) {
		?>
		<p>You have chosen <strong><?php echo get_the_author_meta('display_name', $managing_user ); ?></strong> to manage your website bids. To change or add more users, you will need to upgrade to our paid version coming soon.</p>
		<?php
	} else {
		?>
		<form action="" method="post" enctype="multipart/form-data">
			<p><?php wp_dropdown_users( array('name' => 'author', 'selected' => $managing_user) ); ?></p>
			<p><input class="button-primary" type="submit" name="bm_options_submit" value="Update Bid Manager Settings">
			</p>
		</form>
		</div>
		<?php
	}
}

//  Geo spatial code for finding out radius on addresses for bid requesters and responders

/**
 * Geocode service response
 * @param string $address - e.g. 123 Main St, Denver, CO 80221
 */
function bm_get_lat_and_lng($address) {

	if ( ! $address ) {
		return FALSE;
	}

	$id = get_current_user_id();

	//  Query for the API key
	$key = get_user_meta( $id, 'bm_google_api_key', TRUE );

	$address = str_replace( " ", "+", urlencode( $address ) );

	// sample URL: https://maps.googleapis.com/maps/api/geocode/json?address=122+Flinders+St,+Darlinghurst,+NSW,+Australia&sensor=false&key=AIzaSyDjtX-Q1FYasO0wcQKqrOktFLghekf9Uns

	$details_url = "https://maps.googleapis.com/maps/api/geocode/json?address={$address}&sensor=false&key={$key}";

	$ch = curl_init();
	curl_setopt( $ch, CURLOPT_URL, $details_url );
	curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1 );
	$response = json_decode( curl_exec( $ch ), TRUE );

	// If Status Code is ZERO_RESULTS, OVER_QUERY_LIMIT, REQUEST_DENIED or INVALID_REQUEST
	if ( $response[ 'status' ] != 'OK' ) {
		return FALSE;
	}

	$geometry = $response[ 'results' ][ 0 ][ 'geometry' ];

	$lat = $geometry[ 'location' ][ 'lat' ];
	$lng = $geometry[ 'location' ][ 'lng' ];

	$array = array(
		'lat'           => $lat,
		'lng'           => $lng,
		'location_type' => $geometry[ 'location_type' ],
	);

	return $array;
}

//  Scramble the file names for bid request/response file uploads and put them on the server
function bm_handle_file_upload($key, $upload_path) {

	$upload_path = rtrim( $upload_path, '/' ) . '/';

	if ( ! isset($_FILES[ $key ]) ) {
		return FALSE;
	}

	$file = $_FILES[ $key ];

	if ( empty($file[ 'name' ]) ) {
		return FALSE;
	}

	// We need the PATH, for moving / saving files
	$base_path = wp_upload_dir();
	$base_url = $base_path[ 'baseurl' ] . '/' . $upload_path;
	$base_path = $base_path[ 'basedir' ] . '/' . $upload_path;

	$pathinfo = pathinfo( $file[ 'name' ] );
	$ext = $pathinfo[ 'extension' ];

	$salt = '1234SomeRandomPatternOfLettersAndNumbers!!!$&#$';

	// Get the name of the file.  But we only care a little, because we want to make it unique / random
	$name = basename( $file[ 'name' ] );
$name = time();
	// Create the random file name
	$name = md5( $name . $salt ) . '.' . $ext;

	// Assign the PATH to move the file to
	$path = $base_path . $name;
	// Set up the URL to view the file
	$url = $base_url . $name;

	if ( move_uploaded_file( $file[ 'tmp_name' ], $path ) ) {
		return $url;
	} else {
		// Move failed. Possible duplicate?
		return "The upload failed";
	}

}


// This function ties into the admin_init() to load the necessary javascript and CSS files
function bm_head() {

	/*
	* Styles
	*/
	// Load Styles
	wp_enqueue_style( 'bm-dashboard-style', PLUGIN_ROOT . '/css/style.css' );
	wp_enqueue_style( 'jquery-ui-style', PLUGIN_ROOT . '/css/jquery-ui.css' );

	/*
	 * Scripts
	 */
	// Load Scripts
	wp_enqueue_script( 'jquery' );
	wp_enqueue_script( 'jquery-ui' );
	wp_enqueue_script( 'jquery-ui-datepicker' );
	wp_enqueue_script( 'bm-datatables', PLUGIN_ROOT . '/javascript/jquery.dataTables.js' );
	wp_register_script( 'bm-common-scripts', PLUGIN_ROOT . '/javascript/bm.common.js', array('jquery') );
	wp_localize_script( 'bm-common-scripts', 'ajax_object',
		array('ajax_url' => admin_url( 'admin-ajax.php' )) );
	wp_enqueue_script( 'bm-common-scripts' );

	/*
	 * If the bid manager has previously never been assigned any settings lets assign some now
	 */
	$settings = get_option('bid_manager_settings');

	// If they do not have any settings then write the default settings to the database
	if ( ! $settings ) {
		$bm_settings = array(
			'email_smtp_notification' => TRUE,
			'email_footer_notification' => TRUE
		);
		$bm_settings = json_encode($bm_settings);
		add_option('bid_manager_settings', $bm_settings);
	}
}

add_action( 'admin_enqueue_scripts', 'bm_head' );

add_action( 'wp_ajax_notification_actions', 'bm_hide_notes' );
add_action( 'wp_ajax_nopriv_notification_actions', 'bm_hide_notes' );

// Bid Manager Main screen

function bm_main() {

	$info = BM_CINFO;
	$settings = BM_BMSETTINGS;
	$email_settings = BM_EMAIL_SETTINGS;
	$new_bid = BM_CBID;
	$bm_dashboard = BM_CDBOARD;


	?>
	<div class="wrap">
		<table>
			<tr>
				<td>
					<?php
					sc_show_notifications();
					?>
				</td>
			</tr>
			<tr>
				<td>
					<h1>WP Bid Manager</h1>

					<p>Here is a list of things to do in order to get up and running smoothly.</p>
					<ol>
						<li>Enter your <a href="<?php echo $info ?>">company information</a>. You must enter your info
							to use
							the system. It identifies you and is also necessary when using the quote request option.
						</li>
						<li>Manage your <a href="<?php echo $settings ?>">settings</a>. Set your page for the
							[bm-invite]
							shortcode to get quote responses and enter your API key for Google Maps.
						</li>
						<li>Configure your <a href="<?php echo $email_settings ?>">email settings</a>. This allows you
							to
							customize the from, subject line, and the body of the email template for quote requests.
						</li>
						<li>Create a <a href="<?php echo $new_bid ?>">new bid</a>. You are able to create a real bid
							that you
							would like to manage or send out for a quote.
						</li>
						<li>Keep track of all of your bids in <a href="<?php echo $bm_dashboard ?>">the bid manager
								dashboard</a>.
						</li>
					</ol>
				</td>
			</tr>
		</table>
	</div>
	<?php
}

// Bid Manager Dashboard

function bm_dashboard() {
	bm_user_dashboard();
}

function bm_dashboard_short_code() {
	if(is_user_logged_in()){
	bm_user_dashboard();
	}
	else{
		echo "please login to add company info";
	}
}
add_shortcode('bm_dashboard','bm_dashboard_short_code');


// Company Information

function bm_company_info() {
	bm_user_info();
}

function bm_company_info_short_code() {
	if(is_user_logged_in()){
		bm_user_info();
	}
	else{
		$myaccount_page_id = get_option( 'woocommerce_myaccount_page_id' );
		if ( $myaccount_page_id ) {
		  $myaccount_page_url = get_permalink( $myaccount_page_id );
			echo "<p class='create_bid_text_contant'>Please <a href='".$myaccount_page_url."'>login</a> to add company info.</p>";
		}
	}
}
add_shortcode('company_info','bm_company_info_short_code');

// New Bid

function bm_new_bid() {
	bm_bid_form();
}


function bm_bid_form_short_code() {
	if(is_user_logged_in()){
	bm_bid_form();
	}
	else{
		echo "please login to add company info";
	}
}
add_shortcode('bm_bid_form','bm_bid_form_short_code');


function user_bids_short_code() {
	if(is_user_logged_in()){
		$date = date('Y-m-d H:i:s');
		$active_bids = new WPBM_Bids();
	echo $active_bids->get_active_bids_table($date);
	}
	else{
		echo "please login to add company info";
	}
}
add_shortcode('user_bids_short_code','user_bids_short_code');







// Reports

function bm_reports() {
	bm_report_controller();
}

// Allows the user to customize their emails to the bid responder

/**
 *
 */
function bm_user_email_settings() {

	$id = get_current_user_id();

	if ( isset($_POST[ 'bm_admin_email_settings' ]) ) {
		$email_subject = sanitize_text_field( $_POST[ 'subject_line' ] );
		$email_from = sanitize_text_field( $_POST[ 'email_from' ] );
		$email_content = $_POST[ 'bm_email_body_content' ];
		$email_from_name = sanitize_text_field( $_POST[ 'email_from_name' ] );

		//  Add the subject line
		update_user_meta( $id, 'bm_subject_line', $email_subject );

		//  Add the from line
		update_user_meta( $id, 'bm_from_line', $email_from );

		//  Add the email body
		update_user_meta( $id, 'bm_email_content', $email_content );

		//  Add the from name line
		update_user_meta( $id, 'email_from_name', $email_from_name );

	}

	// Pull the information from the database if it is there and populate the form for the returning user
	$copy = stripslashes( get_user_meta( $id, 'bm_email_content', TRUE ) );
	$subject = stripslashes( get_user_meta( $id, 'bm_subject_line', TRUE ) );
	$from = stripslashes( get_user_meta( $id, 'email_from_name', TRUE ) );
	$email_from = stripslashes( get_user_meta( $id, 'bm_from_line', TRUE ) );

	?>
	<div class="wrap">
		<form id="admin_email_settings" action="" method="post">
			<table id="email_body_editor">
				<tbody>
				<tr>
					<td>
						<h1>Email Configuration and Setup</h1>

						<p>These settings will allow you to customize the email that is sent to the person you want a
							quote
							from.</p>

						<div>
							<label for="email_from_name">From Name</label>
							<input id="email_from_name" value="<?php echo $from ?>" name="email_from_name"
								   placeholder="Ex: Your Company"/>

							<p>This defaults to the company name if left blank.</p>
						</div>
						<div>
							<label for="subject_line">Subject Line</label>
							<input id="subject_line" value="<?php echo $subject ?>" name="subject_line"
								   placeholder="Ex: You are receiving this email from..."/>

							<p>This defaults to "Invitation for Quote Response" if left blank.</p>
						</div>
						<div>
							<label for="email_from">From Email</label>
							<input id="email_from" value="<?php echo $email_from ?>" name="email_from"
								   placeholder="Ex: abc123@gmail.com" type="email" required/>

							<p>This defaults to "no-reply@wordpress.org" if left blank.</p>
						</div>
					</td>
				</tr>
				<tr>
					<td>
						<p>Email Body</p>
					</td>
				</tr>
				<tr>
					<td>
						<?php
						$args = array(
							'media_buttons' => FALSE,
							'textarea_name' => 'bm_email_body_content'
						);
						wp_editor( html_entity_decode( $copy ), 'email_body', $args );

						$bm_settings = get_option('bid_manager_settings');
						$bm_settings = json_decode($bm_settings);

						if ($bm_settings->email_smtp_notification == TRUE || $bm_settings->email_footer_notification == TRUE) {
							?>
							<h2>Configuration notes:</h2>
							<?php
						}

						if ($bm_settings->email_smtp_notification == TRUE) {
							?>
							<p id="note_wrapper_1" class="bm_note"><span style="float: right; padding-left: 50px;"><label for="smtp_notification">Dismiss</label> <input id="smtp_notification" class="hide_notification_checkbox" type="checkbox"></span>
								&ndash; WordPress does not send
								mail via SMTP by default. For this reason, the email may or may not end up in your spam/junk
								folder. We absolutely recommend this plugin to configure SMTP so your mail does not go to
								anybody's spam/junk: <a href="https://wordpress.org/plugins/wp-mail-smtp/" target="_blank">WP
									Mail SMTP</a></p>
							<?php
						}

						if ($bm_settings->email_footer_notification == TRUE) {
							?>
							<p class="bm_note"><span style="float: right; padding-left: 50px;"><label for="email_footer">Dismiss</label> <input id="email_footer" class="hide_notification_checkbox" type="checkbox"></span> &ndash; The text
								in the box below will appear <strong><em>after</em></strong> your custom message. It is
								system
								text and is mandatory. Feel free to use the customize box above to add anything prior to
								this
								text. In addition, it is always a good idea to test an email to yourself first, before
								sending
								it out.</p>
							<?php
						}

						?>

						<p class="bm_example"><em>"Please follow the link below to sign in and review the quote request.<br><br><a>Click
									here to view and respond</a>."</em></p>
					</td>
				</tr>
				</tbody>
			</table>
			<input class="button-primary" type="submit" name="bm_admin_email_settings" value="Save &raquo;"/>
		</form>
	</div>
	<?php

}

function bm_settings() {
	$id = get_current_user_id();

	$content = '';

	if ( $_GET[ 'page' ] == 'bm_settings' ) {

		$content .= '<div class="wrap">';
		if ( isset($_GET[ 'message' ]) == 'bm_settings_updated' ) {
			$content .= '<p class="success">Your settings have been saved.</p>';
		}
		$content .= '<h1>Bid Management Settings</h1>';
		$content .= '<h2>Invitation Details Page</h2>';
		$content .= '<p>Put this shortcode on the page you select below: <strong>[bm-invite]</strong></p>';
		$content .= '<p>If you don\'t put this shortcode on the page you select, you will not have a page for the bid information to be displayed for whoever you want to get a response from.  This <strong>MUST</strong> be done in order to send email requests to people for quote responses.</p>';

		$link_id = get_user_meta( $id, 'bm_invite_page', TRUE );

		$defaults = array(
			'depth'                 => 0,
			'child_of'              => 0,
			'selected'              => $link_id,
			'echo'                  => 0,
			'name'                  => 'page_id',
			'id'                    => NULL, // string
			'class'                 => NULL, // string
			'show_option_none'      => 'Please select a page', // string
			'show_option_no_change' => NULL, // string
			'option_none_value'     => NULL, // string
		);

		$content .= '
    <form id="invite_scode_page" method="post" action="">

    <p>' . wp_dropdown_pages( $defaults ) . '</p>
    <h2>Google Maps API Key</h2>';

		$key = get_user_meta( $id, 'bm_google_api_key', TRUE );
		$allow_response_actions = get_option('bm_bid_response_actions');

		if (!$allow_response_actions) {
			update_option('bm_bid_response_actions', 0);
		}

		$allow_response_actions = get_option('bm_bid_response_actions');

		$bid_response_actions_yes = $bid_response_actions_no = '';
		if ($allow_response_actions == 1) {
			$bid_response_actions_yes =  'checked';
		}

		if ($allow_response_actions == 0) {
			$bid_response_actions_no = 'checked';
		}


		$content .= '<p><input id="google_maps_api" class="" type="text" value="' . $key . '" name="google_maps_api" size="50"></p>
                    <p>This will put a Google Map with your bids pinned to it at the bottom of your <a href="' . BM_CINFO . '">company information page</a>.</p>
                    <p>If you need help getting a Google Maps API key, you can <a target="_blank" href="https://developers.google.com/maps/documentation/javascript/get-api-key?hl=en">get started here</a>.</p>
                    <h2>Client Bid Price</h2>
					<p>You may specify the cost (in '.get_woocommerce_currency(). ') of each bid below:</p>
					<p>
					    <strong>'.get_woocommerce_currency(). ' $   </strong><input 
						    type="text" 
							title="Please enter a number for the client bid price"
							pattern="^\d*\.{0,1}\d{0,2}$" 
							name="client_bid_price" 
							value="'.get_option( 'client_bid_price', 0 ).'" 
						/>
					</p>
					<p></p>
					<div class="switch-field">
					
                    <h3 class="switch-title">Allow Bid Response View Actions on Front</h3>
                    <p>If selected, this will allow visitors/users on the front end to take action on bids with responses.  It is advised to keep this set to "No" unless you know what you are doing.</p>
                    <input id="bid_actions_yes" type="radio" name="bid_response_actions" value="1" ' . $bid_response_actions_yes . '>
                    <label for="bid_actions_yes">Yes</label>
                    <input id="bid_actions_no" type="radio" name="bid_response_actions" value="0" ' . $bid_response_actions_no . '>
                    <label for="bid_actions_no">No</label>
                    </div>

                    <p><input class="button-primary" type="submit" value="Submit &raquo;" name="bm_settings_save" /></p>
                    </form></div>';


		if ( isset($_POST[ 'bm_settings_save' ]) ) {

			// Write the permalink ID to the database if page selected
			if ( $_POST[ 'page_id' ] ) {
				update_user_meta( $id, 'bm_invite_page', $_POST[ 'page_id' ] );
			}



			if ( empty($_POST[ 'google_maps_api' ]) ) {
				$_POST[ 'google_maps_api' ] = '';
			}

			update_user_meta( $id, 'bm_google_api_key', $_POST[ 'google_maps_api' ] );



			if (isset($_POST['bid_response_actions'])) {
				update_option('bm_bid_response_actions', $_POST['bid_response_actions']);
			}

			if (isset($_POST['client_bid_price'])) {
				update_option('client_bid_price', floatval($_POST['client_bid_price']));
			}

			print('<script>window.location.href="admin.php?page=bm_settings&message=bm_settings_updated"</script>');

//            $link = 'admin.php';
//            $params = array( 'page' => 'bm_settings', 'message' => 'bm_settings_updated' );
//            $link = add_query_arg( $params, $link );
//            $link = esc_url($link, '', 'db');
//            wp_redirect($link);
//            exit();
		} else {
			echo $content;
		}

	}

}


function bm_activate() {


	// Make the directories
	$upload = wp_upload_dir();
	$upload_dir = $upload[ 'basedir' ];
	$upload_dir = $upload_dir . '/bid_requests';  // Bid requests file folder
	if ( ! is_dir( $upload_dir ) ) {
		mkdir( $upload_dir, 0700 );
	}


	$upload = wp_upload_dir();
	//print_r($upload);
	$upload_dir = $upload[ 'basedir' ];
	$upload_dir = $upload_dir . '/bid_responses';  //  Bid responses file folder
	if ( ! is_dir( $upload_dir ) ) {
		mkdir( $upload_dir, 0700 );
	}

	//Check to see if we need to write the tables to the DB
	bm_user_check();
	bm_email_check();
	bm_responder_check();
	bm_bids_check();
	bm_responses_check();
	bm_notifications_check();

	// This injects the notification to the bm_notifications table
	bm_notice_injection();  // Turn this on if there is a notification to be run in the notifications.php file

}

register_activation_hook( __FILE__, 'bm_activate' );

function bm_plugin_version() {
	require_once('includes/updates.php');

	/*
	 * We initially want to setup a version number.  Either inserting it or updating it.  Then we can do some checks against it.
	 */

	$current_version = (float)1.21;  //  Set my version # - previous was 1.17
	$option = 'bm_plugin_version';

	//  Find if the option exists
	$db_version = (float)get_option( $option );

	if ( ! $db_version ) { // If the option does not exist, run all the updates and write it
		bm_update_02162016();
		bm_update_08112017();
		bm_update_08312017();
		add_option( $option, $current_version );

	}

	if ( $db_version < 1.20 ) {
		bm_update_02162016();
		bm_update_08112017();
		update_option( $option, $current_version );
	}

	if ( $db_version < $current_version ) {
		bm_update_08312017();
		update_option( $option, $current_version );
	}

}

register_deactivation_hook(__FILE__, 'bm_uninstall_process');
function bm_uninstall_process() {
	if ( ! current_user_can( 'activate_plugins' ) ) {
		return;
	}

	$today = date("Y-m-d H:i:s");
	$expire = '2017-11-27 16:01:55';
	$send_survey = TRUE;
	if ($today > $expire ) {
		$send_survey = FALSE;
	}

	if ($send_survey == TRUE) {

		$plugin = isset( $_REQUEST['plugin'] ) ? $_REQUEST['plugin'] : '';
		check_admin_referer( "deactivate-plugin_{$plugin}" );

		# Uncomment the following line to see the function in action
//	exit( var_dump( $_GET ) );

		$user = wp_get_current_user();

		$user_email = $user->data->user_email;

		$link = 'http://wp-bid-manager.com/wp-bid-manager-plugin-uninstall-survey/?user_email=' . $user_email;

		$message = <<<EMAIL
	<p>We are sorry to see you go.  We hope that you may be interested in taking a brief, 60 second survey, to help us better the product.  In return, we will put you into a monthly drawing to receive a $50 Visa gift card.</p>
	<p>Simply <a href="{$link}">Follow this link</a> to our survey page.  <strong>It's that easy!</strong></p>
	<p>Thank you,<br>WP Bid Manager Team</p>
EMAIL;


		// $from = 'From: WP Bid Manager <no-reply@mrwpress.com>';

		$headers = array(
			'Content-Type: text/html; charset=UTF-8'
			// $from
			// 'Cc: somebody@rezdox.com'
		);
		// https://blog.jetbrains.com/idea/2008/01/using-local-history-to-restore-deleted-files/ - Delete the damn plugin and don't have a backup???....

		wp_mail($user_email, 'WP Bid Manager: We are sorry to see you go', $message, $headers);
	}



}

function bm_main_settings() {
	$settings = get_option('bid_manager_settings');
	$settings = json_decode($settings);
	$settings = (array)$settings;

	return $settings;
}

function bm_save_main_settings($new_settings) {
	if ( ! is_array($new_settings) ) {
		return '<div class="error"><p>You must provide an array.</p></div>';
	}

	$current = bm_main_settings();

	$new = array_merge($current, $new_settings);

	$new_settings = json_encode($new);
	update_option('bid_manager_settings', $new_settings);
}

/*
 * The following is where we tie into actions/hooks/filters, etc to harness WordPress native functionality
 */

//  Creates the admin menu on the left hand navigation
add_action( 'admin_menu', 'bid_manager_menu' );

// Tap into the admin_init() so we can enque/register/deregister any styles or scripts
add_action( 'admin_init', 'bm_head' );
add_action( 'admin_init', 'bm_plugin_version' );