<?php
/*
	csv process
*/

if ( ! defined( 'ABSPATH' ) )

	exit; // Exit if accessed directly

function wk_mu_process_csv() {

	if (isset($_POST['run_admin_csv'])) {

		if ( !empty($_POST['csv_profile']) ) {

			if ( isset($_POST['front_run_upload_nonce']) && !empty( $_POST['front_run_upload_nonce'] ) ) {

		 		if( wp_verify_nonce( $_POST['front_run_upload_nonce'], 'front_run_upload_action' ) ) {

		 			echo '<div class="csv-success">Starting Execution...</div>';

		 			$author_id = get_current_user_id();

		 			$url = wp_upload_dir();

					$csv_data = get_user_meta( $author_id, 'csv_profile_path', true);

					foreach ($csv_data as $key => $value) {
							if ($value['csv'] == $_POST['csv_profile']) {
									$img_folder = explode('.', $value['zip'])[0];
							}
					}

					$user_folder = wp_get_current_user()->user_login;

					$target_file = $url['basedir'].'/'.$user_folder.'/' . $_POST['csv_profile'];

					$row = 0;

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

					    echo '<div class="csv-success">Please don'."'".'t close or refresh the window while importing products.</div>';

					   	$product_count = 0;

					    foreach ($result as $val) {
					    	if ($val['type'] == 'product') {
					    		$product_count++;
					    	}
					    }

					    echo '<div class="csv-success">Total '.$product_count.' Product(s) to import.</div>';

					   	session_start();

					   	$current = $product_count = 0;

					    foreach ($result as $val) {

					    	$current++;

					    	$pr_id = wk_mu_process_csv_data($val,$author_id,$current,$img_folder);

					    	if ($pr_id && $val['type'] == 'product') {

					    		$product_count++;

					    	}

					    	if ($pr_id && $val['product_type'] == 'variable') {

					    		$vari_prod[] = $pr_id;

					    	}

					    }

					    if ( isset($vari_prod) && !empty($vari_prod) ) {

						    require_once(sprintf("%s/process-variation-price.php", dirname(__FILE__)));

						    wk_mu_process_variation_price($vari_prod);

					    }

					    if (isset($_SESSION['var_pid'])) {

					    	unset($_SESSION['var_pid']);

					    }

					    if (isset($_SESSION['grp_pid'])) {

					    	unset($_SESSION['grp_pid']);

					    }

					    $current = 0;

					    foreach ($result as $val) {

					    	$current++;

					    	wk_mu_outputProgress($current, count($result));

					    }

					    echo '<div class="csv-success" style="margin-top:60px;">Total '.$product_count.' product(s) imported.</div>';

					    echo '<div class="csv-success">Finished Execution.</div>';

					}

		 		}

		 		else {

		 			echo '<div class="csv-error">Cheati'."'".'n huh !</div>';

		 		}

		 	}

		 	else {

		 		echo '<div class="csv-error">Cheati'."'".'n huh !</div>';

		 	}

		}

		else {

			echo '<div class="csv-error">Select all fields. <a href="'.site_url().'/seller/run-profile">Go Back</a></div>';

		}

	}

}

function wk_mu_process_admin_csv($result, $author_id,$img_folder) {

	echo '<div class="csv-success">Starting Execution...</div>';

	echo '<div class="csv-success">Please don'."'".'t close or refresh the window while importing products.</div>';

   	$product_count = 0;

    foreach ($result as $val) {
    	if ($val['post_type'] == 'product') {
    		$product_count++;
    	}
    }

    echo '<div class="csv-success">Total '.$product_count.' Product(s) to import.</div>';

   	session_start();

   	$current = $product_count = 0;

    foreach ($result as $val) {

    	$current++;

    	$pr_id = wk_mu_process_csv_data($val,$author_id,$current,$img_folder);

    	if ($pr_id && $val['post_type'] == 'product') {

    		$product_count++;

    	}

    	if ($pr_id && $val['product_type'] == 'variable') {

    		$vari_prod[] = $pr_id;

    	}

    }

    if ( isset($vari_prod) && !empty($vari_prod) ) {

	    require_once(sprintf("%s/process-variation-price.php", dirname(__FILE__)));

	    wk_mu_process_variation_price($vari_prod);

    }

    if (isset($_SESSION['var_pid'])) {

    	unset($_SESSION['var_pid']);

    }

    if (isset($_SESSION['grp_pid'])) {

    	unset($_SESSION['grp_pid']);

    }

    echo '<div class="csv-success" style="margin-top:60px;">Total '.$product_count.' product(s) imported.</div>';

    echo '<div class="csv-success">Finished Execution.</div>';

}

function wk_mu_process_csv_data($val,$author_id,$current,$img_folder) {

	$p_id = $val['ID'];

	$product_title = ($val['product_name']) ? $val['product_name'] : '';

	$product_sku = ($val['sku']) ? $val['sku'] : '';

	$short_desc = ($val['short_desc']) ? $val['short_desc'] : '';

	$product_desc = ($val['description']) ? $val['description'] : '';

	$product_status = ($val['product_status']) ? $val['product_status'] : '';

	$post_parent = ($val['product_parent']) ? $val['product_parent'] : 0;

	$post_type = ($val['type']) ? $val['type'] : '';

	$comment_status = ($val['comment_status']) ? $val['comment_status'] : '';

	$is_downloadable = ($val['downloadable']) ? $val['downloadable'] : '';

	$is_virtual = ($val['virtual']) ? $val['virtual'] : '';

	$visibility = ($val['visibility']) ? $val['visibility'] : '';

	$stock = ($val['stock']) ? $val['stock'] : '';

	$stock_status = ($val['stock_status']) ? $val['stock_status'] : '';

	$backorders = ($val['backorders']) ? $val['backorders'] : '';

	$manage_stock = ($val['manage_stock']) ? $val['manage_stock'] : '';

	$regu_price = ($val['regular_price']) ? $val['regular_price'] : '';

	$sale_price = ($val['sale_price']) ? $val['sale_price'] : '';

	$weight = ($val['weight']) ? $val['weight'] : '';

	$length = ($val['length']) ? $val['length'] : '';

	$width = ($val['width']) ? $val['width'] : '';

	$height = ($val['height']) ? $val['height'] : '';

	$tax_status = ($val['tax_status']) ? $val['tax_status'] : '';

	$tax_class = ($val['tax_class']) ? $val['tax_class'] : '';

	$featured = ($val['featured']) ? $val['featured'] : '';

	$sale_price_dates_from = ($val['sale_price_dates_from']) ? $val['sale_price_dates_from'] : '';

	$sale_price_dates_to = ($val['sale_price_dates_to']) ? $val['sale_price_dates_to'] : '';

	$download_limit = ($val['download_limit']) ? $val['download_limit'] : '';

	$download_expiry = ($val['download_expiry']) ? $val['download_expiry'] : '';

	$download_file = ($val['download_file']) ? $val['download_file'] : '';

	$product_url = ($val['product_url']) ? $val['product_url'] : '';

	$product_image = ($val['product_image']) ? $val['product_image'] : '';

	$gallery = ($val['gallery']) ? $val['gallery'] : '';

	$product_type = ($val['product_type']) ? $val['product_type'] : '';

	$product_cat = ($val['product_cat']) ? $val['product_cat'] : '';

	$attribute_name = ($val['attribute_name']) ? $val['attribute_name'] : '';

	$attribute_data = ($val['attribute_data']) ? $val['attribute_data'] : '';

	$attribute_single = ($val['attribute_single']) ? $val['attribute_single'] : '';

	$images_folder = $img_folder;

	$virtual = $product_type == 'virtual' ? 'yes' : 'no';

	$simple = $product_type == 'simple' ? 'yes' : 'no';

	$product_categories = explode('|', $product_cat );

	global $wpdb;

	if ( empty($product_sku) ) {

		echo '<div style="color:red">Row '.$current.' skipped. SKU should contain atleast 3 letters</div>';

		return;

	}

	if (strlen($product_sku) < 3) {

		echo '<div style="color:red">Row '.$current.' skipped. SKU should contain atleast 3 letters</div>';

		return;

	}

	$sku_data = $wpdb->get_results("select meta_value from $wpdb->postmeta where meta_key='_sku'");

	foreach( $sku_data as $d ) {

		$sku[] = $d->meta_value;

	}

	if( ! empty( $sku ) ) {

		if( in_array($product_sku,$sku) ) {

			echo '<div style="color:red">Row '.$current.' skipped. SKU already exists.</div>';

			return;

		}

	}

	if (isset($_SESSION['var_pid'])) {

		foreach ($_SESSION['var_pid'] as $key => $value) {

			foreach ($value as $k => $val) {

				if ($post_parent == $k) {

					$post_parent = $val;

				}

				else {

					echo '<div style="color:red">Row '.$current.' skipped. Parent Product not exists.</div>';

					return;

				}

			}

		}

	}

	if ($post_type == 'product_variation') {

		$product_title = 'Product #'.$post_parent.' Variation';

		$post_name = 'product-'.$post_parent.'-variation';

	}

	if ( !empty( $attribute_name ) ) {

		$product_attributes_arr[] = array (

			'name'	=> $attribute_name,
			'value'	=> $attribute_data,
			'position' => 1,
			'is_visible' => 1,
			'is_variation' => 1,
			'is_taxonomy'	=> 0

		);

	}

	else {

		$product_attributes_arr = array();

	}


	$product_attributes = array();

	if( !empty($product_attributes_arr)) {

		foreach($product_attributes_arr as $attribute) {

			if(empty($attribute['name']) || empty($attribute['value'])) {

				continue;

			}

			$rep_str = $attribute['value'];

			$rep_str = preg_replace('/\s+/', ' ', $rep_str);

			$attribute['name'] = str_replace(' ','-',$attribute['name']);

			$attribute['value'] = str_replace("|","|",$rep_str);

			if( isset($attribute['is_visible']) )

				$attribute['is_visible']=(int)$attribute['is_visible'];

			else

				$attribute['is_visible'] = 0;

			if( isset($attribute['is_variation']) )

				$attribute['is_variation'] = (int)$attribute['is_variation'];

			else

				$attribute['is_variation'] = 0;

			$attribute['is_taxonomy'] = (int)$attribute['is_taxonomy'];

			$product_attributes[str_replace(' ','-',$attribute['name'])] = $attribute;

		}

	}

	$product_data = array(

		'post_author' 	=> $author_id,

	   	'post_date'		=> '',

	   	'post_date_gmt'	=> '',

	   	'post_content'	=> $product_desc,

	   	'post_content_filtered' => $short_desc,

	   	'post_title'	=> $product_title,

	   	'post_excerpt'	=> $short_desc,

	   	'post_status'	=> $product_status,

	   	'post_type'		=> $post_type,

	   	'comment_status'=> $comment_status,

	   	'ping_status'	=> 'open',

	    'post_password'	=> '',

	    'post_name'		=> wp_strip_all_tags($product_title),

	    'to_ping'		=> '',

	    'pinged'			=> '',

	    'post_modified' 	=> '',

	    'post_modified_gmt' => '',

	    'post_parent'		=> $post_parent,

	    'menu_order'		=> '',

	    'guid'				=> ''

	);

	$postid = wp_insert_post($product_data);

	if ($post_type == 'product_variation') {

		$product_title = 'Variation #'.$postid.' of '.get_the_title($post_parent);

		$data = array(

			'ID'			=> $postid,

			'post_title'	=> $product_title

		);

		wp_update_post($data);

	}

	if ($post_type == 'product_variation') {

		update_post_meta( $postid, 'attribute_'.$attribute_name, $attribute_single );

	}

	if ($product_type == 'variable') {

		if (isset($_SESSION['var_pid'])) {

			$var_session_data = $_SESSION['var_pid'];

		}

		else {

			$var_session_data = array();

		}

		if ( !empty( $var_session_data ) ) {

			$data = $var_session_data;

		}

		else {

			$data = array();

		}

		$data[] = array(

			$p_id => $postid

		);

		$_SESSION['var_pid'] = $data;

	}

	if ($product_type == 'grouped') {

		if (isset($_SESSION['grp_pid'])) {

			$var_session_data = $_SESSION['grp_pid'];

		}

		else {

			$var_session_data = array();

		}

		if ( !empty( $var_session_data ) ) {

			$data = $var_session_data;

		}

		else {

			$data = array();

		}

		$data[] = array(

			$p_id => $postid

		);

		$_SESSION['grp_pid'] = $data;

	}

	if (isset($_SESSION['grp_pid'])) {

		foreach ($_SESSION['grp_pid'] as $key => $value) {

			foreach ($value as $k => $val) {

				if ($post_parent == $k) {

					if(!empty($val)) {

						$children = '';

						$group_id = $val;

						$args = array(
							'ID'         => $postid,
							'post_parent' => $group_id
						);

						wp_update_post($args);

						if ( ! is_array( $children ) || empty( $children ) ) {

							$transient_name = 'wc_product_children_' . $group_id;

							$children = array_filter( array_map( 'absint', (array) get_transient( $transient_name ) ) );

							if ( empty( $children ) ) {

								$args = apply_filters( 'woocommerce_grouped_children_args', array(
									'post_parent' 	=> $group_id,
									'post_type'		=> 'product',
									'orderby'		=> 'menu_order',
									'order'			=> 'ASC',
									'fields'		=> 'ids',
									'post_status'	=> 'publish',
									'numberposts'	=> -1,
								) );

								$children = get_posts( $args );

								set_transient( $transient_name, $children, DAY_IN_SECONDS * 30 );

							}

						}

					}

				}

			}

		}

	}

	$filename = $product_image;

	if (!empty($filename)) {

		if (empty($images_folder)) {
			echo '<div style="color:red">Row '.$current.' skipped. Image folder name empty.</div>';
			return;
		}

		$filetype = wp_check_filetype( basename( $filename ), null );

		$wp_upload_dir = wp_upload_dir();

		$user_folder = get_userdata($author_id)->user_login;

		$attachment = array(
			'post_author' 	 => $author_id,
			'guid'           => $wp_upload_dir['basedir'].'/'.$user_folder.'/'.$images_folder . '/' . basename( $filename ),
			'post_mime_type' => $filetype['type'],
			'post_title'     => preg_replace( '/\.[^.]+$/', '', basename( $filename ) ),
			'post_content'   => '',
			'post_status'    => $product_status
		);

		$attach_id = wp_insert_attachment( $attachment, $user_folder.'/'.$images_folder . '/' .$filename );

		require_once( ABSPATH . 'wp-admin/includes/image.php' );

		$attach_data = wp_generate_attachment_metadata( $attach_id, $wp_upload_dir['basedir'].'/'.$user_folder.'/'.$images_folder . '/' .$filename );

		wp_update_attachment_metadata( $attach_id, $attach_data );

	}

	if ( !empty( $gallery ) ) {

		$gallery_imgs = explode('|', $gallery);

		foreach ($gallery_imgs as $key => $value) {

			$filename = $value;

			$filetype = wp_check_filetype( basename( $filename ), null );

			$wp_upload_dir = wp_upload_dir();

			$user_folder = get_userdata($author_id)->user_login;

			$attachment = array(
				'post_author'	 => $author_id,
				'guid'           => $wp_upload_dir['basedir'].'/'.$user_folder.'/'.$images_folder . '/' . basename( $filename ),
				'post_mime_type' => $filetype['type'],
				'post_title'     => preg_replace( '/\.[^.]+$/', '', basename( $filename ) ),
				'post_content'   => '',
				'post_status'    => $product_status
			);

			$g_attach_id = wp_insert_attachment( $attachment, $user_folder.'/'.$images_folder . '/' .$filename );

			require_once( ABSPATH . 'wp-admin/includes/image.php' );

			$g_attach_data = wp_generate_attachment_metadata( $g_attach_id, $wp_upload_dir['basedir'].'/'.$user_folder.'/'.$images_folder . '/' .$filename );

			wp_update_attachment_metadata( $g_attach_id, $g_attach_data );

			$gallery_ids[] = $g_attach_id;
		}

		$gallery_img_ids = implode(',',array_unique($gallery_ids));

		update_post_meta( $postid, '_product_image_gallery', $gallery_img_ids );

	}

	update_post_meta( $postid, '_visibility', $visibility );

	update_post_meta( $postid, '_featured', $featured );

	update_post_meta( $postid, '_featured', $tax_status );

	$tax_class = strtolower(str_replace(' ', '-', $tax_class));

	update_post_meta( $postid, '_featured', $tax_class );

	add_post_meta( $postid, '_sku', $product_sku );

	add_post_meta( $postid, '_regular_price',$regu_price);

	if ( isset( $sale_price ) && !empty( $sale_price) ) {
		add_post_meta( $postid, '_sale_price', $sale_price );

		add_post_meta( $postid,'_price',$sale_price );
	}
	else {
		add_post_meta( $postid, '_sale_price','' );

		add_post_meta( $postid, '_price', $regu_price );
	}

	add_post_meta( $postid, '_sale_price_dates_from', $sale_price_dates_from);

	add_post_meta( $postid, '_sale_price_dates_to', $sale_price_dates_to);

	add_post_meta( $postid, '_downloadable', $is_downloadable);

	update_post_meta( $postid, '_backorders', $backorders );

	update_post_meta( $postid, '_stock_status', $stock_status );

	update_post_meta( $postid, '_stock', $stock );

	update_post_meta( $postid, '_manage_stock', $manage_stock );

	update_post_meta( $postid, '_virtual', $virtual );

	update_post_meta( $postid, '_simple', $simple );

	update_post_meta( $postid, '_downloadable_files', '' );

	update_post_meta( $postid, '_download_limit', $download_limit);

	update_post_meta( $postid, '_download_expiry', $download_expiry);

	if ( $virtual ) {
		update_post_meta( $postid, '_weight', '' );
		update_post_meta( $postid, '_length', '' );
		update_post_meta( $postid, '_width', '' );
		update_post_meta( $postid, '_height', '' );
	}
	else {

		if ( isset( $weight ) ) {
			update_post_meta($postid, '_weight', ( '' === $weight ) ? '' : wc_format_decimal( $_POST['_weight'] ) );
		}

		if ( isset( $length ) ) {
			update_post_meta($postid, '_length', ( '' === $length ) ? '' : wc_format_decimal( $_POST['_length'] ) );
		}

		if ( isset( $width ) ) {
			update_post_meta($postid, '_width', ( '' === $width ) ? '' : wc_format_decimal( $_POST['_width'] ) );
		}

		if ( isset( $height ) ) {
			update_post_meta($postid, '_height', ( '' === $height ) ? '' : wc_format_decimal( $_POST['_height'] ) );
		}

	}

	if ( $product_type == 'external' ) {

		if( isset( $product_url) ) {

			$btn_txt = 'Get Now';

			update_post_meta( $postid, '_product_url', $product_url);

			update_post_meta( $postid, '_button_text', $btn_txt);

			delete_post_meta( $postid, '_simple' );

		}

	}

	if ( $is_downloadable == 'yes' ) {

		update_post_meta( $postid, '_downloadable', $is_downloadable );

		update_post_meta( $postid, '_virtual', 'yes' );

		$dwnload_url = wc_clean( $product_url );

		$upload_file_url[ md5( $dwnload_url ) ] = array(

			'name'	=> $download_file,

			'file' => $dwnload_url

		);

		update_post_meta( $postid, '_downloadable_files', $upload_file_url );

	}
	else {

		update_post_meta( $postid, '_downloadable_files', '' );

	}

	update_post_meta( $postid, '_thumbnail_id', $attach_id );

	if( !empty( $product_attributes ) ) {

		update_post_meta( $postid, '_product_attributes', $product_attributes );

	}

	else {

		update_post_meta( $postid, '_product_attributes', array() );

	}

	if ( !empty( $product_categories ) ) {

		foreach ($product_categories as $key => $value) {

			if ($value) {

				$idObj = get_term_by( 'slug', $value, 'product_cat' );

		  		$cat_id = $idObj->term_id;

				$pro_cat_id = array( 'object_id' => $postid, 'term_taxonomy_id' => $cat_id);

				$wpdb->insert( "$wpdb->term_relationships", $pro_cat_id );

			}

		}

	}

	wp_set_object_terms( $postid, $product_type, 'product_type', false );

	return $postid;

}

function wk_mu_outputProgress($current, $total) {

	?>

	<div id="csv_progress">
	  	<div id="csv_progress_bar" style="width:<?php echo round($current / $total * 100) . "%"; ?>"></div>
	</div>

	<?php

    wk_mu_myFlush();

    sleep(1);

}

function wk_mu_myFlush() {

    echo(str_repeat(' ', 256));

    if (@ob_get_contents()) {

        @ob_end_flush();

    }

    flush();

}
