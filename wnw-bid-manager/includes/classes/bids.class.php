<?php
class WPBM_Bids {

    function get_past_bids_table($date) {
        global $wpdb;
        $content = '';
        $query   = "
        SELECT 
            b.bid_id, 
            b.job_name, 
            b.job_street, 
            b.job_city, 
            b.job_state, 
            b.job_zip, 
            b.date_needed,
            CASE 
               WHEN cpp.pay_date='0000-00-00 00:00:00' OR cpp.payment_status='unpaid' THEN NULL
               ELSE cpp.pay_date
            END as date_paid
        FROM " . BM_BIDS . " b 
        LEFT JOIN client_project_payment as cpp ON b.bid_id = cpp.project_id
        WHERE b.date_needed < %s AND b.accepted_flag = %d";
        $data    = array(
            $date,
            0
        );
        $query   = $wpdb->prepare( $query, $data );
        $results = $wpdb->get_results( $query );

        if ( $results ) {
            $content .= '<div class="archived_bids blue_table">';

            $content .= '<h2>Expiry Projects Submitted</h2>';

            $content .= '<div class="responsive_table_v"><table id="conPastBids" class="blue_table" border="1" bordercolor="#000" cellpadding="5" width="100%">';
            $content .= '<thead>';
            $content .= '<tr>';
            $content .= '<th>';
            $content .= 'Bid Id';
            $content .= '</th>';
            $content .= '<th>';
            $content .= 'Job';
            $content .= '</th>';
            $content .= '<th>';
            $content .= 'Street';
            $content .= '</th>';
            $content .= '<th>';
            $content .= 'City';
            $content .= '</th>';
            $content .= '<th>';
            $content .= 'Parish';
            $content .= '</th>';
            $content .= '<th>';
            $content .= 'Bid Required By';
            $content .= '</th>';
            $content .= '<th>';
            $content .= 'Payments';
            $content .= '</th>';
            $content .= '<th>';
            $content .= 'Take Action';
            $content .= '</th>';
            $content .= '</tr>';
            $content .= '</thead>';
            $content .= '<tbody>';

            foreach ( $results as $record ) {

                $link = 'admin.php';
                $params = array( 'bmuser_bid_past' => $record->bid_id );
                $link = add_query_arg( $params );
                $link = esc_url($link, '', 'db');

                // $arr_params = array( 'bmuser_bid_past' => $record->bid_id );
                $content .= '<tr>';
                $content .= '<td>' . $record->bid_id . '</td>';
                $content .= '<td>' . stripslashes( $record->job_name ) . '</td>';
                $content .= '<td>' . $record->job_street . '</td>';
                $content .= '<td>' . $record->job_city . '</td>';
                $content .= '<td>' . $record->job_state . '</td>';
            
                $content .= '<td>' . date( 'F jS, Y', strtotime( $record->date_needed ) ) . '</td>';
                $content .= '<td>' . ($record->date_paid == null ? '' :  'Paid at '.date('F jS, Y H:i:s',strtotime($record->date_paid )) )  . '</td>';
                $content .= '<td><a class="button-primary" href="' . $link . '">View Bid &raquo;</a></td>';
                $content .= '</tr>';
            }

            $content .= '</tbody>';
            $content .= '</table></div>';

            $content .= '</div>';

        } else {
            $content .= '';
        }

        return $content;
    }

    function get_accepted_bids_table() {
        global $wpdb,$current_user;
		
		$current_user_member_level = $current_user->membership_level->ID;
		
		
        $content = '';
		
		$user_id = get_current_user_id();
		$user_rol = new WP_User( $user_id );

        $user_roles=$user_rol->roles;
 
 
 
 
		
		if(is_admin()){
		    $query   = "
                SELECT 
                    br.* ,
                    b.*,
                    CASE 
                        WHEN cpp.pay_date='0000-00-00 00:00:00' OR cpp.payment_status='unpaid' THEN NULL
                        ELSE cpp.pay_date
                    END as date_paid
                FROM 
                " . BM_BIDS_RESPONSES . " br
                LEFT OUTER JOIN 
                " . BM_BIDS . " b ON b.bid_id=br.bid_id 
                LEFT JOIN 
                    client_project_payment as cpp ON b.bid_id = cpp.project_id
                WHERE bid_accepted = %d";
            $data    = array(
                1
            );
            $query   = $wpdb->prepare( $query, $data );
            $results = $wpdb->get_results( $query );
		}
        //$current_user_member_level == '6' || $current_user_member_level == '7' || $current_user_member_level == '9' || $current_user_member_level == '10'
		elseif(in_array(intval($current_user_member_level),[6,7,9,10])){
            // *,
            // bd.bmuser_id 
			$query   = "
                SELECT 
                    br.*,
                    b.*,
                    CASE 
                        WHEN cpp.pay_date='0000-00-00 00:00:00' OR cpp.payment_status='unpaid' THEN NULL
                        ELSE cpp.pay_date
                    END as date_paid
                FROM 
                " . BM_BIDS_RESPONSES . " br
                LEFT OUTER JOIN 
                " . BM_BIDS . " as b ON b.bid_id=br.bid_id 
                LEFT JOIN 
                    client_project_payment as cpp ON b.bid_id = cpp.project_id
                WHERE bid_accepted = %d AND b.bmuser_id = %d ";
            $data    = array(
                1,
		    	$user_id
            );
            $query   = $wpdb->prepare( $query, $data );
            $results = $wpdb->get_results( $query );
			
		}
		
        if ( $results ) {
			
			$colors = array_column($results, 'bmuser_id');
			if (is_admin()){
			
            $content .= '<div class="contractors_bids_accepted blue_table">';

            $content .= '<h2>Accepted Bids</h2>';

            $content .= '<div class="responsive_table_v"><table id="conAccepted" class="blue_table" border="1" bordercolor="#000" cellpadding="5" width="100%">';
            $content .= '<thead>';
            $content .= '<tr>';
            $content .= '<th>';
            $content .= 'Bid Id';
            $content .= '</th>';
            $content .= '<th>';
            $content .= 'Name';
            $content .= '</th>';
            $content .= '<th>';
            $content .= 'Job';
            $content .= '</th>';
            $content .= '<th>';
            $content .= 'Street';
            $content .= '</th>';
            $content .= '<th>';
            $content .= 'City';
            $content .= '</th>';
            $content .= '<th>';
            $content .= 'Parish';
            $content .= '</th>';
        
            $content .= '<th>';
            $content .= 'Bid Required By';
            $content .= '</th>';
            $content .= '<th>';
            $content .= 'Quote Total';
            $content .= '</th>';
			

            $content .= '<th>';
            $content .= 'Payments';
            $content .= '</th>';

            $content .= '<th>';
            $content .= 'Take Action';
            $content .= '</th>';
		
            $content .= '</tr>';
            $content .= '</thead>';
            $content .= '<tbody>';

            foreach ( $results as $record ) {

                $link = get_permalink();
                $params = array( 'bid_accepted' => $record->bid_id, 'response_id' => $record->id );
                $link = add_query_arg( $params, $link );
                if (is_admin()) {
                    $link = 'admin.php';
                    $params = array( 'page' => 'bid_manager_dashboard', 'bid_accepted' => $record->bid_id, 'response_id' => $record->id );
                    $link = add_query_arg( $params, $link );
                    $link = esc_url($link, '', 'db');
                }

                // $arr_params = array( 'bid_accepted' => $record->bid_id, 'responder_id' => $record->responder_id );
                $content .= '<tr>';
                $content .= '<td>' . $record->bid_id . '</td>';
                $content .= '<td>' . stripslashes( $record->responder_busname ) . '</td>';
                $content .= '<td>' . stripslashes( $record->job_name ) . '</td>';
                $content .= '<td>' . $record->job_street . '</td>';
                $content .= '<td>' . $record->job_city . '</td>';
                $content .= '<td>' . $record->job_state . '</td>';
                
                $content .= '<td>' . date( 'F jS, Y', strtotime( $record->date_needed ) ) . '</td>';
                $content .= '<td>$ ' . number_format( $record->quoted_total, 2 ) . '</td>';
                $content .= '<td>' . ($record->date_paid == null ? '' :  'Paid at '.date('F jS, Y H:i:s',strtotime($record->date_paid )) )  . '</td>';
				
				$querysp   = "SELECT bmuser_id FROM " . BM_BIDS . " WHERE bid_id = %d";
				$datasp    = array(
					$record->bid_id
				);
				$querysp   = $wpdb->prepare( $querysp, $datasp );
				$resultssp = $wpdb->get_row( $querysp );
				//print_r($resultssp);
				
                $content .= '<td>';
                if($resultssp->bmuser_id == $user_id){
					$content .= '<a class="button-primary" href="' . $link . '">View Bid &raquo;</a></td>';
				}
                $content .= '</td>';
				
                $content .= '</tr>';
            }

            $content .= '</tbody>';
            $content .= '</table></div>';

            $content .= '</div>';
           

        }
		
		
		elseif($current_user_member_level == '6' ||$current_user_member_level == '7' || $current_user_member_level == '9' || $current_user_member_level == '10'){
		if (in_array($user_id, $colors)){
			
            $content .= '<div class="contractors_bids_accepted blue_table">';

            $content .= '<h2>Bids You Have Accepted</h2>';

            $content .= '<div class="responsive_table_v"><table id="conAccepted" class="blue_table" border="1" bordercolor="#000" cellpadding="5" width="100%">';
            $content .= '<thead>';
            $content .= '<tr>';
            $content .= '<th>';
            $content .= 'Bid Id';
            $content .= '</th>';
            $content .= '<th>';
            $content .= 'Name';
            $content .= '</th>';
            $content .= '<th>';
            $content .= 'Job';
            $content .= '</th>';
            $content .= '<th>';
            $content .= 'Street';
            $content .= '</th>';
            $content .= '<th>';
            $content .= 'City';
            $content .= '</th>';
            $content .= '<th>';
            $content .= 'Parish';
            $content .= '</th>';
            
            $content .= '<th>';
            $content .= 'Bid Required By';
            $content .= '</th>';
            $content .= '<th>';
            $content .= 'Quote Total';
            $content .= '</th>';
            $content .= '<th>';
            $content .= 'Payments';
            $content .= '</th>';
			
            if($current_user_member_level == '6' ||$current_user_member_level == '7' || $current_user_member_level == '9' || $current_user_member_level == '10'){
            $content .= '<th>';
            $content .= 'Download Quote File';
            $content .= '</th>';
			}
            $content .= '</tr>';
            $content .= '</thead>';
            $content .= '<tbody>';

            foreach ( $results as $record ) {

                $link = get_permalink();
                $params = array( 'bid_accepted' => $record->bid_id, 'response_id' => $record->id );
                $link = add_query_arg( $params, $link );
                if (is_admin()) {
                    $link = 'admin.php';
                    $params = array( 'page' => 'bid_manager_dashboard', 'bid_accepted' => $record->bid_id, 'response_id' => $record->id );
                    $link = add_query_arg( $params, $link );
                    $link = esc_url($link, '', 'db');
                }

                // $arr_params = array( 'bid_accepted' => $record->bid_id, 'responder_id' => $record->responder_id );
                $content .= '<tr>';
                $content .= '<td>' . $record->bid_id . '</td>';
                $content .= '<td>' . stripslashes( $record->responder_busname ) . '</td>';
                $content .= '<td>' . stripslashes( $record->job_name ) . '</td>';
                $content .= '<td>' . $record->job_street . '</td>';
                $content .= '<td>' . $record->job_city . '</td>';
                $content .= '<td>' . $record->job_state . '</td>';
                
                $content .= '<td>' . date( 'F jS, Y', strtotime( $record->date_needed ) ) . '</td>';
                $content .= '<td>$ ' . number_format( $record->quoted_total, 2 ) . '</td>';
                $content .= '<td>' . ($record->date_paid == null ? '' :  'Paid at '.date('F jS, Y H:i:s',strtotime($record->date_paid )) )  . '</td>';
				//if(!in_array( 'customer', $user_roles )){
                $content .= '<td><a class="button-primary" href="' . $record->bmuser_bid_file . '">Download File &raquo;</a></td>';
				//}
                $content .= '</tr>';
            }

            $content .= '</tbody>';
            $content .= '</table></div>';

            $content .= '</div>';
            

        }
		}
		
		
		
		
		
		
	}		else {
            $content .= '';
        }

        return $content;
    }
	
	
	
	 function sp_accepted_bids_table() {
        global $wpdb;
		
		
		
		
        $content = '';
		
		$user_id = get_current_user_id();
		$user_rol = new WP_User( $user_id );

        $user_roles=$user_rol->roles;
	
	    $results = $wpdb->get_results("
            select 
                wb.bid_id,
                b.job_name,
                wb.quoted_total,
                CASE 
                    WHEN cpp.pay_date='0000-00-00 00:00:00' OR cpp.payment_status='unpaid' THEN NULL
                    ELSE cpp.pay_date
                END as date_paid
            from 
                `wp_bm_bids_responses` as wb 
            INNER JOIN  
                wp_bm_bids as b ON wb.bid_id = b.bid_id 
            LEFT JOIN 
                client_project_payment as cpp ON b.bid_id = cpp.project_id
            where wb.bider_id ='".$user_id."' and wb.bid_accepted= 1");
 
 
		if ( $results ) {
			
		
			
            $content .= '<div class="contractors_bids_accepted blue_table">';

            $content .= '<h2>Your Accepted Project(s)</h2>';

            $content .= '<div class="responsive_table_v"><table id="conAccepted" class="blue_table" border="1" bordercolor="#000" cellpadding="5" width="100%">';
            $content .= '<thead>';
            $content .= '<tr>';
            $content .= '<th>';
            $content .= 'Bid Id';
            $content .= '</th>';
            $content .= '<th>';
            $content .= 'Name';
            $content .= '</th>';
             $content .= '<th>';
            $content .= 'Quote Total';
            $content .= '</th>';

            $content .= '<th>';
            $content .= 'Payments';
            $content .= '</th>';
			
            $content .= '<th>';
            $content .= 'View Project';
            $content .= '</th>';
			
            $content .= '</tr>';
            $content .= '</thead>';
            $content .= '<tbody>';

            foreach ( $results as $record ) {

                
                
              
               

              
                $content .= '<tr>';
                $content .= '<td>' . $record->bid_id . '</td>';
               
                $content .= '<td>' . stripslashes( $record->job_name ) . '</td>';
               
				$content .= '<td> $' . number_format( $record->quoted_total,2 ) . '</td>';
                $content .= '<td>' . ($record->date_paid == null ? '' :  'Paid at '.date('F jS, Y H:i:s',strtotime($record->date_paid )) )  . '</td>';
				 
                $content .= '<td><a class="button-primary" href="' .site_url().'/all-bidding-projects/?bmuser_bid_active='.$record->bid_id . '">View Project</a></td>';
				
                $content .= '</tr>';
            }

            $content .= '</tbody>';
            $content .= '</table></div>';

            $content .= '</div>';
           

		
	}		else {
            $content .= '';
        }

        return $content;
    }
	
	
	

    function get_bids_with_responses_table($date) {
        global $wpdb,$current_user;
		$current_user_member_level = $current_user->membership_level->ID;
		$user_idvp = get_current_user_id();
		$user_rolvp = new WP_User( $user_idvp );
		$user_rolesvp=$user_rolvp->roles;
					
        $content = '';
		/* if(is_admin()){
        $query   = "SELECT * FROM " . BM_BIDS . " WHERE date_needed > %s AND accepted_flag = %d AND has_response > %d";
        $data    = array(
            $date,
            0,
            0
        );
        $query   = $wpdb->prepare( $query, $data );
        $results = $wpdb->get_results( $query );
		} */
		if($current_user_member_level == '6' ||$current_user_member_level == '7' || $current_user_member_level == '9' || $current_user_member_level == '10'){
		    $query   = "
                SELECT 
                    b.*,
                    CASE 
                        WHEN cpp.pay_date='0000-00-00 00:00:00' OR cpp.payment_status='unpaid' THEN NULL
                        ELSE cpp.pay_date
                    END as date_paid
                FROM 
                " . BM_BIDS . " b
                LEFT JOIN client_project_payment as cpp ON b.bid_id = cpp.project_id
                WHERE date_needed > %s AND accepted_flag = %d AND has_response > %d AND bmuser_id = %d";
            $data    = array(
                $date,
                0,
                0,
		    	$user_idvp
            );
            $query   = $wpdb->prepare( $query, $data );
            $results = $wpdb->get_results( $query );	
			
			
		}
		else{
			$query   = "
                SELECT 
                    b.*,
                    CASE 
                        WHEN cpp.pay_date='0000-00-00 00:00:00' OR cpp.payment_status='unpaid' THEN NULL
                        ELSE cpp.pay_date
                    END as date_paid
                FROM 
                " . BM_BIDS . " b 
                LEFT JOIN client_project_payment as cpp ON b.bid_id = cpp.project_id
                WHERE date_needed > %s AND accepted_flag = %d AND has_response > %d";
            $data    = array(
                $date,
                0,
                0
            );
            $query   = $wpdb->prepare( $query, $data );
            $results = $wpdb->get_results( $query );
		}

        if ( $results ) {
            $content .= '<div class="responder_responses blue_table">';

            $content .= '<h2>Projects With Responses (not accepted)</h2>';

            $content .= '<div class="responsive_table_v"><table id="conSupResponse" class="blue_table" border="1" bordercolor="#000" cellpadding="5" width="100%">';
            $content .= '<thead>';
            $content .= '<tr>';
            $content .= '<th>';
            $content .= 'Bid Id';
            $content .= '</th>';
            $content .= '<th>';
            $content .= 'Job Name';
            $content .= '</th>';
            $content .= '<th>';
            $content .= 'Street';
            $content .= '</th>';
            $content .= '<th>';
            $content .= 'City';
            $content .= '</th>';
            $content .= '<th>';
            $content .= 'Parish';
            $content .= '</th>';
            
            $content .= '<th>';
            $content .= 'Bid Required By';
            $content .= '</th>';
            $content .= '<th>';
            $content .= '# of Bids';
            $content .= '</th>';

            $content .= '<th>';
            $content .= 'Payments';
            $content .= '</th>';

            $content .= '<th>';
            $content .= 'Take Action';
            $content .= '</th>';
            $content .= '</tr>';
            $content .= '</thead>';
            $content .= '<tbody>';

            foreach ( $results as $record ) {
                // $arr_params = array( 'bid_response' => $record->bid_id );
                $content .= '<tr>';
                $content .= '<td>' . $record->bid_id . '</td>';
                $content .= '<td>' . stripslashes( $record->job_name ) . '</td>';
                $content .= '<td>' . $record->job_street . '</td>';
                $content .= '<td>' . $record->job_city . '</td>';
                $content .= '<td>' . $record->job_state . '</td>';
               
                $content .= '<td>' . date( 'F jS, Y', strtotime( $record->date_needed ) ) . '</td>';
                $content .= '<td>' . $record->has_response . '</td>';
                $content .= '<td>' . ($record->date_paid == null ? '' :  'Paid at '.date('F jS, Y H:i:s',strtotime($record->date_paid )) )  . '</td>';

                if (is_admin()) {
                    $content .= '<td><a class="button-primary" href="' . BM_CDBOARD . '&amp;bid_response=' . $record->bid_id . '">View Bid &raquo;</a></td>';
                }
				elseif(is_user_logged_in()){
					$user_id = get_current_user_id();
					$user_rol = new WP_User( $user_id );

					$user_roles=$user_rol->roles;
					if(in_array( 'customer', $user_roles ))
                    {
					    $content .= '<td><a class="button-primary" href="'.site_url().'/my-account/bid-projects/?bid_response=' . $record->bid_id . '">View Bid &raquo;</a></td>';
				    }
				    elseif(in_array( 'serviceproviders', $user_roles )){
					    $content .= '<td><a class="button-primary" href="' . get_permalink() . '?bid_response=' . $record->bid_id . '">View Bid &raquo;</a></td>';
				    }
				}
				else {
                    $content .= '<td><a class="button-primary" href="' . get_permalink() . '?bid_response=' . $record->bid_id . '">View Bid &raquo;</a></td>';
                }
                $content .= '</tr>';
            }

            $content .= '</tbody>';
            $content .= '</table></div>';

            $content .= '</div>';

        } else {
            $content .= '';
        }

        return $content;
    }

    function get_active_bids_table($date) {
		
		//echo $date;
		
		//die;
        global $wpdb,$current_user;
		$usr_iid = get_current_user_id();
		$user_rolv = new WP_User( $usr_iid );

        $user_rolesv=$user_rolv->roles;
        $content = '';
		
	
		$current_user_member_level = $current_user->membership_level->ID;
		
		if(is_admin()){
            $query = "
                SELECT 
                    b.*,
                    CASE 
                        WHEN cpp.pay_date='0000-00-00 00:00:00' OR cpp.payment_status='unpaid' THEN NULL
                        ELSE cpp.pay_date
                    END as date_paid
                FROM " . BM_BIDS . " b 
                LEFT JOIN client_project_payment as cpp ON b.bid_id = cpp.project_id
                WHERE date_needed > %s AND accepted_flag = %d";
		    $data = array(
                $date,
                0
            );
		}
		
		elseif($current_user_member_level == '6' || $current_user_member_level == '7' || $current_user_member_level == '9' || $current_user_member_level == '10'){
            $query = "
                SELECT 
                    b.*,
                    CASE 
                        WHEN cpp.pay_date='0000-00-00 00:00:00' OR cpp.payment_status='unpaid' THEN NULL
                        ELSE cpp.pay_date
                    END as date_paid
                FROM " . BM_BIDS . " b 
                LEFT JOIN client_project_payment as cpp ON b.bid_id = cpp.project_id
                WHERE date_needed > %s AND accepted_flag = %d AND bmuser_id = %d";
					$data = array(
					$date,
					0,
					$usr_iid
				);
		}
		else{
			
			$query = "
                SELECT 
                    b.*,
                    CASE 
                        WHEN cpp.pay_date='0000-00-00 00:00:00' OR cpp.payment_status='unpaid' THEN NULL
                        ELSE cpp.pay_date
                    END as date_paid
                FROM " . BM_BIDS . " as bb 
                LEFT JOIN client_project_payment as cpp ON bb.bid_id = cpp.project_id 
                WHERE bb.date_needed > %s AND bb.accepted_flag = %d AND cpp.payment_status NOT LIKE 'unpaid' ";
			$data = array(
            $date,
            0
        );
		}
		
		
        
        $query = $wpdb->prepare( $query, $data );
		/* print_r($query);
		exit; */
        $results = $wpdb->get_results( $query );

        if ( sanitize_text_field( isset($_GET[ 'message' ]) ) == "bid_saved" ) {
            $content .= '<p class="success">Bid saved successfully! &ndash; Click the <em>"View Bid &raquo;"</em> action to invite suppliers to respond.</p>';
        }


        if ( $results ) {
           // $content .= '<h1>Bid Manager Dashboard</h1>';

            $content .= '<div class="active_bids blue_table">';
            $content .= '<h2>Active Projects</h2>';

            $content .= '<div class="responsive_table_v"><table id="conActiveBids" class="blue_table" border="1" bordercolor="#000" cellpadding="5" width="100%">';
            $content .= '<thead>';
            $content .= '<tr>';
            $content .= '<th>';
            $content .= 'Project Id';
            $content .= '</th>';
            $content .= '<th>';
            $content .= 'Project Name';
            $content .= '</th>';
            $content .= '<th>';
            $content .= 'Street';
            $content .= '</th>';
            $content .= '<th>';
            $content .= 'City';
            $content .= '</th>';
            $content .= '<th>';
            $content .= 'Parish';
            $content .= '</th>';
            
            $content .= '<th>';
            $content .= 'Expiry';
            $content .= '</th>';

            $content .= '<th>';
            $content .= 'Payments';
            $content .= '</th>';

            $content .= '<th>';
            $content .= 'Take Action';
            $content .= '</th>';
            $content .= '</tr>';
            $content .= '</thead>';
            $content .= '<tbody>';

            foreach ( $results as $record ) {
                $content .= '<tr>';
                $content .= '<td>' . $record->bid_id . '</td>';
                $content .= '<td>' . stripslashes( $record->job_name ) . '</td>';
                $content .= '<td>' . $record->job_street . '</td>';
                $content .= '<td>' . $record->job_city . '</td>';
                $content .= '<td>' . $record->job_state . '</td>';
             
                $content .= '<td>' . date( 'F jS, Y', strtotime( $record->date_needed ) ) . '</td>';
                $content .= '<td>' . ($record->date_paid == null ? '' :  'Paid at '.date('F jS, Y H:i:s',strtotime($record->date_paid )) )  . '</td>';
                if (is_admin()) {
                    $content .= '<td><a class="button-primary" href="' . BM_CDBOARD . '&amp;bmuser_bid_active=' . $record->bid_id . '">View Project &raquo;</a></td>';
                }
                elseif(is_user_logged_in()){
					
					$user_id = get_current_user_id();
					$user_rol = new WP_User( $user_id );

					$user_roles=$user_rol->roles;
				
					
				
				if($current_user_member_level == '6' || $current_user_member_level == '7' || $current_user_member_level == '9' || $current_user_member_level == '10')
					{
						$queryx = "SELECT payment_status FROM client_project_payment WHERE client_id = %d AND project_id = %d";
							$datax = array(
							$user_id,
							$record->bid_id
						);
						$queryx = $wpdb->prepare( $queryx, $datax );
						$resultsx = $wpdb->get_row( $queryx );
						if($resultsx->payment_status == 'unpaid'){
							if($current_user_member_level == '6'){							
							$content .= '<td><a class="button-primary" href="'.get_permalink().'/bid-projects/?make_payment=' . $record->bid_id . '">Make Payment &raquo;</a></td>';
							}
							else{
								$content .= '<td><a class="button-primary" href="'.get_permalink().'?make_payment=' . $record->bid_id . '">Make Payment &raquo;</a></td>';
							}
						
						}else{
							if($current_user_member_level == '6'){
							$content .= '<td><a class="button-primary" href="'.get_permalink().'bid-projects/?bmuser_bid_active=' . $record->bid_id . '">View Project &raquo;</a></td>';
							}
							else{
								$content .= '<td><a class="button-primary" href="'.get_permalink().'?bmuser_bid_active=' . $record->bid_id . '">View Project &raquo;</a></td>';
							}
							
						}
				}
				
				elseif($current_user_member_level == '8'){
					echo $mmbr->membership_id;
					$queryxx = "SELECT payment_status FROM sp_bid_payment WHERE sp_id = %d AND project_id = %d";
					$dataxx = array(
					$user_id,
					$record->bid_id
				    );
					$queryxx = $wpdb->prepare( $queryxx, $dataxx );
					$resultsxx = $wpdb->get_row( $queryxx );
					if($resultsxx->payment_status == 'Completed'){
						
						$content .= '<td><a class="button-primary" href="' . get_permalink() . '?bmuser_bid_active=' . $record->bid_id . '">View Project &raquo;</a></td>';
					}
						else{
							
						$content .= '<td><a class="button-primary" href="' . get_permalink() . '?sp_make_payment=' . $record->bid_id . '">Make Payment &raquo;</a></td>';
					}
				}
					
				}
				else {
                    $content .= '<td><a class="button-primary" href="' . get_permalink() . '?bmuser_bid_active=' . $record->bid_id . '">View Project &raquo;</a></td>';
                }
                $content .= '</tr>';
            }

            $content .= '</tbody>';
            $content .= '</table></div>';

            $content .= '</div>';

        } else {
			$content .= "<div class='clas-for-btn'>";
            $content .= '<p class="para-first">There are no active projects.</p>';
            if (is_admin()) {
                $content .= '<p class="para-sec"><a class="button" href="' . BM_CBID . '">Create A Project &raquo</a></p>';
            }
			elseif ($current_user_member_level == '6' || $current_user_member_level == '7' || $current_user_member_level == '9' || $current_user_member_level == '10') {
				if($current_user_member_level == '6'){
                $content .= '<p class="para-sec"><a class="next_button" href="'.site_url().'/my-account/create-project/">Create A Project &raquo</a></p>';
				}
				elseif($current_user_member_level == '7' || $current_user_member_level == '9' || $current_user_member_level == '10'){
					$content .= '<p class="para-sec"><a class="next_button" href="'.site_url().'/creates-a-bid/">Create A Project &raquo</a></p>';
				}
            }
			$content .= "</div>";
        }

        return $content;
    }
}