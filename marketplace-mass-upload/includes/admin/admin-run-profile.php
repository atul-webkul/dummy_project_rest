<?php

/*
*	run csv profile admin
*/

if ( ! defined( 'ABSPATH' ) )

	exit; // Exit if accessed directly

if (isset($_POST['run_admin_csv'])) {

	if ( !empty($_POST['csv_profile']) && ( !empty($_POST['csv_product_author']) || ( isset($_POST['author_admin']) && !empty($_POST['author_admin']) ) ) ) {

		if ( isset($_POST['admin_run_upload_nonce']) && !empty( $_POST['admin_run_upload_nonce'] ) ) {

	 		if( wp_verify_nonce( $_POST['admin_run_upload_nonce'], 'admin_run_upload_action' ) ) {

	 			$author_id = ( isset($_POST['author_admin'] ) ) ? 1 : $_POST['csv_product_author'];

	 			$url = wp_upload_dir();

				$user_folder = wp_get_current_user()->user_login;

				$target_file = $url['basedir'].'/'.$user_folder.'/' . $_POST['csv_profile'];

				$row = 0;

				$csv_data = get_user_meta( $author_id, 'csv_profile_path', true);

				foreach ($csv_data as $key => $value) {
						if ($value['csv'] == $_POST['csv_profile']) {
								$img_folder = explode('.', $value['zip'])[0];
						}
				}

				if (($handle = fopen($target_file, "r")) !== FALSE) {

				    while($data = fgetcsv ($handle, 10000, ",")) {

				        foreach ($data as $final_key => $value) {

				        	$new_arr[$row][] = $value;

				        }

				        $row++;
				    }

				    fclose($handle);

				    $base_array = $new_arr[0];

				    for ($i=1; $i < count($new_arr); $i++) {

				    	for ($j=0; $j < count($base_array) ; $j++) {

				    		$result[$i][$base_array[$j]] = $new_arr[$i][$j];

				    	}

				    }

				    if (!empty($result)) {

				    	require_once(sprintf("%s/process-csv-data.php", dirname(plugin_dir_path(__FILE__))));

				    	wk_mu_process_admin_csv( $result, $author_id, $img_folder);

				    }

				}

	 		}

	 		else {

	 			echo '<div class="notice notice-error"><p>Cheati'."'".'n huh !</p></div>';

	 		}

	 	}

	 	else {

	 		echo '<div class="notice notice-error"><p>Cheati'."'".'n huh !</p></div>';

	 	}

	}

	else {

		echo '<div class="notice notice-error"><p>Select all fields.</p></div>';

	}

}

global $wpdb;

$sql =  "SELECT user_id from {$wpdb->prefix}mpsellerinfo where seller_value = 'seller'";

$result = $wpdb->get_results($sql);

$user_id = get_current_user_id();

$csv_meta = get_user_meta( $user_id, 'csv_profile_path');

?>

<div class="wk_mu_admin_wrapper">

	<form action="" method="post" enctype="multipart/form-data">

		<table class="form-table">

			<thead>

				<tr>
					<th>Fields</th>
					<th>Options</th>
				</tr>

			</thead>

			<tbody>

			<tr valign="top">

				<th scope="row" class="titledesc">

					<label for="select_admin">Post as admin</label>

				</th>

				<td>

					<span class="error">* </span><input id="select_admin" type="checkbox" name="author_admin">

					<p class="description">Check if seller will be admin itself.</p>

				</td>

			</tr>

			<tr valign="top" class="seller-select-field">

				<th scope="row" class="titledesc">

					<label for="select_seller">Select Seller</label>

				</th>

				<td>

					<span class="error">* </span><select name="csv_product_author" id="select_seller" title="Old or New">

					<option value="">Select Seller</option>

					<?php

						foreach ($result as $ke) {

						?>

						<option value="<?php echo $ke->user_id; ?>"><?php echo get_user_meta($ke->user_id, 'first_name',true).' '.get_user_meta($ke->user_id, 'last_name',true); ?></option>

						<?php

						}

					?>

					</select>

					<p class="description">Select seller to which products will be assigned (if not post as admin).</p>

				</td>

			</tr>

			<tr valign="top">

				<th scope="row" class="titledesc">

					<label for="select_profile">Select Profile</label>

				</th>

				<td>

					<span class="error">* </span><select name="csv_profile" id="select_profile" title="Old or New">

					<option value="">Select Profile</option>

					<?php

						$i = 0;

						foreach ($csv_meta as $key => $value) {

							for ($i=0; $i < count($value) ; $i++) {

								?>

								<option value="<?php echo $value[$i]['csv']; ?>"><?php echo $value[$i]['csv']; ?></option>

								<?php

							}

						}

					?>

					</select>

				</td>

			</tr>

		 	</tbody>

		 	<tfoot>

				<tr>
					<th>Fields</th>
					<th>Options</th>
				</tr>

			</tfoot>

		</table>

		<?php wp_nonce_field( 'admin_run_upload_action', 'admin_run_upload_nonce' ); ?>

	 	<p class="submit">

			<input name="run_admin_csv" class="button-primary" type="submit" value="Run Profile">

		</p>

	</form>

</div>
