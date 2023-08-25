<?php
/**
 * Class for milestone API
 */

class Wkmbsc_Task_Function {

	/**
	 * The single instance of the class.
	 *
	 * @var $instance
	 * @since 1.0.0
	 */
	protected static $instance = null;

	/**
	 * To Create Task
	 */
	public function wkmbsc_create_task( WP_REST_Request $request, $task_id = null ) {
		$postdata = $request->get_params();
		$list_id  = $postdata['list_id'];

		$is_update = $task_id ? true : false;

		$files        = isset( $postdata['cpm_attachment'] ) ? $postdata['cpm_attachment'] : array();
		$task_privacy = isset( $postdata['task_privacy'] ) ? $postdata['task_privacy'] : 'no';

		$task_title = trim( $postdata['task_title'] );
		$content    = empty( $postdata['task_text'] ) ? '' : trim( $postdata['task_text'] );
		// print_r($postdata[ 'task_assign' ]) ;  exit() ;
		$assigned = isset( $postdata['task_assign'] ) ? $postdata['task_assign'] : array( '-1' );
		// $due          = empty( $postdata['task_due'] ) ? '' : cpm_date2mysql( $postdata['task_due'] );
		$due   = empty( $postdata['task_due'] ) ? '' : cpm_to_mysql_date( $postdata['task_due'] );
		$start = empty( $postdata['task_start'] ) ? '' : cpm_to_mysql_date( $postdata['task_start'] );

		$data = array(
			'post_author'  => $postdata['author_id'],
			'post_parent'  => $postdata['list_id'],
			'post_title'   => $task_title,
			'post_content' => $content,
			'post_type'    => 'cpm_task',
			'post_status'  => 'publish',
		);

		$data = apply_filters( 'cpm_task_params', $data );

		if ( $task_id ) {
			$data['ID'] = $task_id;
			$task_id    = wp_update_post( $data );
		} else {
			$task_id = wp_insert_post( $data );
		}

		if ( $task_id ) {
			$this->assign_user( $task_id, $assigned );
			update_post_meta( $task_id, '_due', $due );

			if ( cpm_get_option( 'task_start_field', 'cpm_general' ) == 'on' ) {
				update_post_meta( $task_id, '_start', $start );
			} else {
				update_post_meta( $task_id, '_start', '' );
			}

			update_post_meta( $task_id, '_task_privacy', $task_privacy );

			// initially mark as uncomplete
			if ( ! $is_update ) {
				update_post_meta( $task_id, '_completed', 0 );
			}

			// if there is any file, update the object reference
			if ( count( $files ) > 0 ) {
				$comment_obj = CPM_Comment::getInstance();

				foreach ( $files as $file_id ) {
					$comment_obj->associate_file( $file_id, $task_id );
				}
			}

			$data['assigned_users'] = empty( $postdata['task_assign'] ) ? array( '-1' ) : $postdata['task_assign'];

			if ( $is_update ) {
				$this->new_task_project_item( $list_id, $task_id, $assigned, $task_privacy, $is_update );
				do_action( 'cpm_task_update', $list_id, $task_id, $data );
			} else {
				$this->new_task_project_item( $list_id, $task_id, $assigned, $task_privacy, $is_update );
				do_action( 'cpm_task_new', $list_id, $task_id, $data );
			}
		}

		if ( $task_id ) {
			return new WP_REST_Response(
				array(
					'Message' => 'Success.',
					'status'  => true,
				),
			);
		} else {

			return new WP_Error(
				'no_data_found',
				'No data Found',
			);

		}

	}

	/**
	 * Update Task
	 */
	public function wkmbsc_update_task( WP_REST_Request $request ) {
		$task_id = $request->get_param( 'task_id' );
		$this->wkmbsc_create_task( $request, $task_id );

		$response['status']  = true;
		$response['message'] = 'sucessfully Updated';

		return new WP_REST_Response( $response );

	}

	/**
	 * Delete a single task
	 *
	 * @param int  $task_id
	 * @param bool $force
	 */
	public function wkmbsc_delete_task( WP_REST_Request $request, $force = false ) {
		$task_id = $request->get_param( 'task_id' );
		do_action( 'cpm_task_delete', $task_id, $force );
		$item_id = $this->get_item_id( $task_id );
		$this->delete_task_item( $item_id );

		CPM_Project::getInstance()->delete_project_item( $task_id );

		wp_delete_post( $task_id, $force );

		if ( $item_id ) {
			return new WP_REST_Response(
				array(
					'Message' => 'Success.',
					'status'  => true,
				),
			);
		} else {

			return new WP_Error(
				'no_data_found',
				'No data Found',
			);

		}
	}

	/**
	 * Get Task list
	 */
	public function wkmbsc_get_task( WP_REST_Request $request ) {
		try {

			$list_id = $request['tasklist_id'];

			$completed = $_GET['completed'];
			// $comment_count = get_post( $list_id )->comment_count;

			$args = array(
				'post_parent' => $list_id,
				'post_type'   => 'cpm_task',
				'post_status' => 'publish',
				'order'       => 'ASC',
				'orderby'     => 'menu_order',
			);

			$tasks = new WP_Query( $args );

			foreach ( $tasks->posts as $key => $task ) {
				$assigned_to = get_post_meta( $task->ID, '_assigned' );

				foreach ( $assigned_to as $key => $assigned_ids ) {
					$user_details[ $key ] = get_userdata( $assigned_ids )->display_name;

				}
				$task->assigned_to = $user_details;
				$task->completed   = intval( get_post_meta( $task->ID, '_completed', true ) );
			}

			$data = $tasks->posts;

			// if($completed == 1) {
			// $data= array_filter($data, function($task) {
			// return $task->completed == 1;
			// });
			// } elseif($completed == 0) {
			// $data = array_filter($data, function($task) {
			// return $task->completed == 0;
			// });
			// } else {
			// $data = $tasks->posts;
			// }

			$final_data = $data;

			$response['status']  = true;
			$response['Message'] = 'Succcess';
			$response['list']    = $final_data;

			if ( ! empty( $final_data ) ) {
				return new WP_REST_Response( $response );
			} else {
				return new WP_REST_Response(
					array(
						'status'  => false,
						'message' => 'No Data Found',
					)
				);
			}
		} catch ( \Exception $e ) {
			return $e;
		}
		// $args  = array(
		// 'post_parent' => $request['tasklist_id'],
		// 'post_type'   => 'cpm_task',
		// );
		// $posts = get_posts( $args );

		// foreach ( $posts as  $p_id ) {
		// $meta = get_post_meta( $p_id->ID, $key = '_assigned', $single = false );

			// $author_obj = get_user_by('id', $meta_id);

		// $assigned[$key] = array(
		// 'ID' => $author_obj->ID,
		// 'Name' => $author_obj->display_name,
		// 'Email' => $author_obj->user_email
		// );
		// }

		// print_r($meta);
		// if ( ! empty( $posts ) ) {
		// return new WP_REST_Response(
		// array(
		// 'Message' => 'Success.',
		// 'status'  => true,
		// 'list'    => $posts,
		// ),
		// );
		// } else {
		// return new WP_REST_Response(
		// array(
		// 'status'  => false,
		// 'message' => 'No Data Found',
		// ),
		// );
		// }
	}

	/**
	 * Mark tasklist complete
	 */
	public function wkmbsc_mark_task_complete( WP_REST_Request $request, $call_do_action = true ) {
		$task_id = $request->get_param( 'task_id' );
		$user_id = $request->get_param( 'user_id' );
		// PATCH: DON'T MARK OPEN TASK AS COMPLETE
		if ( get_post_meta( $task_id, '_completed', true ) == '1' ) {
			return new WP_Error(
				'no_data_found',
				'No data Found',
			);
		}
		update_post_meta( $task_id, '_completed', 1 );
		update_post_meta( $task_id, '_completed_by', $user_id );
		update_post_meta( $task_id, '_completed_on', current_time( 'mysql' ) );
		CPM_Project::getInstance()->new_project_item_complete_date( $task_id, current_time( 'mysql' ) );
		if ( $call_do_action ) {
			do_action( 'cpm_task_complete', $task_id );
		}
		if ( ! empty( $task_id ) ) {
			return new WP_REST_Response(
				array(
					'Message' => 'Success.',
					'status'  => true,
				),
			);
		}

	}

	/**
	 * Mark Task Uncomplete
	 */
	public function wkmbsc_mark_task_uncomplete( WP_REST_Request $request, $call_do_action = true ) {
		$task_id = $request->get_param( 'task_id' );
		$user_id = $request->get_param( 'user_id' );
		// PATCH: DON'T MARK OPEN TASK AS COMPLETE
		if ( get_post_meta( $task_id, '_completed', true ) == '0' ) {
			return new WP_Error(
				'no_data_found',
				'No data Found',
			);
		}
		update_post_meta( $task_id, '_completed', 0 );
		update_post_meta( $task_id, '_completed_on', current_time( 'mysql' ) );
		CPM_Project::getInstance()->new_project_item_complete_open( $task_id );
		if ( $call_do_action ) {
			do_action( 'cpm_task_open', $task_id );
		}
		if ( ! empty( $task_id ) ) {
			return new WP_REST_Response(
				array(
					'Message' => 'Success.',
					'status'  => true,
				),
			);
		}
	}

	/**
	 * To add Comment
	 */
	public function wkmbsc_add_comment( WP_REST_Request $request, $files = array() ) {
		// check_ajax_referer( 'cpm_nonce' );
		$posted     = $request->get_params();
		$files      = array();
		$text       = trim( $posted['cpm_message'] );
		$parent_id  = isset( $posted['parent_id'] ) ? intval( $posted['parent_id'] ) : 0;
		$project_id = isset( $posted['project_id'] ) ? intval( $posted['project_id'] ) : 0;
		if ( isset( $posted['cpm_attachment'] ) ) {
			$files = $posted['cpm_attachment'];
		}
		$data        = array(
			'comment_post_ID' => $parent_id,
			'comment_content' => $text,
			'user_id'         => $posted['user_id'],
		);
		$comment_obj = CPM_Comment::getInstance();
		$comment_id  = $comment_obj->create( $data, $files );
		if ( $comment_id ) {
			$comment = $comment_obj->get( $comment_id );
			echo json_encode(
				array(
					'success' => true,
					'message' => 'comment added succesfully',
				// 'placeholder' => __( 'Add a comment...', 'cpm' ),
				// 'content'     => cpm_show_comment( $comment, $project_id ),
				)
			);
		}

		exit;

	}

	/**
	 * To Get Task Details
	 */
	public function wkmbsc_get_task_details( $list_id = null ) {

		// $list_id = $_GET['list_id'];

		$comment_count = get_post( $list_id )->comment_count;

		$args = array(
			'post_parent' => $list_id,
			'post_type'   => 'cpm_task',
			'post_status' => 'publish',
			'order'       => 'ASC',
			'orderby'     => 'menu_order',
		);

		$tasks = new WP_Query( $args );

		foreach ( $tasks->posts as $key => $task ) {
			$this->set_task_meta( $task );
		}

		$data = $tasks->posts;
		return $data;

		// $response['status'] = true;
		// $response['list'] = array(
		// 'total'         => count( $data ),
		// 'completed'     => array_sum( wp_list_pluck( $tasks, 'completed' ) ),
		// 'incomplete'    => ceil(
		// count( $data ) - array_sum( wp_list_pluck( $tasks, 'completed' ) )
		// ),
		// 'comment_count' => (int) $comment_count,
		// );

		// return new WP_REST_Response( $response );
	}

	/**
	 * Delete Task item
	 */
	function delete_task_item( $item_id ) {
		global $wpdb;

		$table     = $wpdb->prefix . 'cpm_tasks';
		$object_id = apply_filters( 'cpm_delete_project_item_task_data', $item_id );

		do_action( 'cpm_before_delete_new_project_task_item', $item_id );

		$delete = $wpdb->delete( $table, array( 'item_id' => $item_id ), array( '%d' ) );

		do_action( 'cpm_before_delete_new_project_task_item', $item_id );

	}

	/**
	 * New task item
	 */
	function new_task_project_item( $list_id, $task_id, $assign, $private, $update = 0 ) {
		global $wpdb;
		$list = get_post( $list_id );

		$task  = $this->get_task( $task_id );
		$table = $wpdb->prefix . 'cpm_tasks';

		$item_id = $this->get_item_id( $task_id );
		// $delete    = true;
		$completed = '0000-00-00 00:00:00';
		if ( $update ) {
			// $delete    = $this->delete_task_item( $item_id );
			$completed = $task->completed ? $task->completed_on : '0000-00-00 00:00:00';

			CPM_Project::getInstance()->new_project_item( $list->post_parent, $task_id, $private, 'cpm_task', true, $completed, $task->completed, $list_id );
			$new_insert_id = $this->get_item_id( $task_id );
		} else {

			CPM_Project::getInstance()->new_project_item( $list->post_parent, $task_id, $private, 'cpm_task', false, $completed, 0, $list_id );

			$new_insert_id = isset( $wpdb->insert_id ) ? $wpdb->insert_id : 0;
		}

		if ( $new_insert_id ) {

			if ( count( $assign ) ) {
				foreach ( $assign as $assigned ) {

					if ( $new_insert_id ) {
						$data = array(
							'item_id' => $new_insert_id,
							'user_id' => $assigned,
							'start'   => isset( $task->start_date ) && $task->start_date ? $task->start_date : $task->post_date,
							'due'     => $task->due_date ? $task->due_date : '0000-00-00 00:00:00',
						);
						$wpdb->insert( $table, $data, array( '%d', '%d', '%s', '%s' ) );
					}
				}
			} else {

				if ( $new_insert_id ) {

					$data = array(
						'item_id' => $new_insert_id,
						'user_id' => -1,
						'start'   => isset( $task->start_date ) && $task->start_date ? $task->start_date : $task->post_date,
						'due'     => $task->due_date ? $task->due_date : '0000-00-00 00:00:00',
					);

					$wpdb->insert( $table, $data, array( '%d', '%d', '%s', '%s' ) );
				}
			}
		}
	}

	/**
	 * Get Task
	 */
	function get_task( $task_id ) {
		$task = get_post( $task_id );
		$this->set_task_meta( $task );

		return $task;
	}


	/**
	 * To Get item ID
	 */
	function get_item_id( $task_id ) {
		global $wpdb;

		$table = $wpdb->prefix . 'cpm_project_items';
		return $wpdb->get_var( $wpdb->prepare( "SELECT id FROM {$table} WHERE object_id = '%d'", $task_id ) );
	}


	/**
	 * Set meta data
	 */
	function set_task_meta( $task ) {
		$task->completed    = intval( get_post_meta( $task->ID, '_completed', true ) );
		$task->completed_by = get_post_meta( $task->ID, '_completed_by', true );
		$task->completed_on = get_post_meta( $task->ID, '_completed_on', true );
		$task->assigned_to  = get_post_meta( $task->ID, '_assigned' );
		// $assigned_to = get_post_meta( $task->ID, '_assigned' );

		// foreach ( $assigned_to as $key => $assigned_ids ) {
		// $user_details[ $key ] = get_userdata( $assigned_ids )->display_name;

		// }
		// $task->assigned_to = $user_details;

		$task->due_date     = get_post_meta( $task->ID, '_due', true );
		$task->start_date   = get_post_meta( $task->ID, '_start', true );
		$task->task_privacy = get_post_meta( $task->ID, '_task_privacy', true );
		$task->comments     = $this->get_task_comments( $task->ID );
		$task->post_content = cpm_get_content( $task->post_content );
		$task->edit_mode    = false;

		$task = apply_filters( 'cpm_set_task_meta', $task );
	}

	/**
	 * Get TAsk Comment
	 */
	function get_task_comments( $task_id ) {
		$task_comments = $this->get_comments( $task_id );

		foreach ( $task_comments as $key => $comment ) {
			$comment->comment_content = do_shortcode( $comment->comment_content );
		}

		return $task_comments;
	}

	/**
	 * Get All comments
	 */
	function get_comments( $post_id, $order = 'ASC' ) {

		$comments = get_comments(
			array(
				'post_id' => $post_id,
				'order'   => $order,
			)
		);

		// prepare comment attachments
		if ( $comments ) {
			foreach ( $comments as $key => $comment ) {
				$this->get_comment_meta( $comment->comment_ID, $comments[ $key ] );
			}
		}

		return $comments;
	}

	/**
	 * Assign user
	 */
	function assign_user( $task_id, $assigned ) {
		delete_post_meta( $task_id, '_assigned' );
		if ( ! is_array( $assigned ) ) {
			$assigned = explode( ',', $assigned );
		}
		foreach ( $assigned as $key => $user_id ) {
			add_post_meta( $task_id, '_assigned', $user_id );
		}
	}


	/**
	 * Get TAsk Comment
	 */
	public function wk_get_task_comments() {

		global $wpdb;

		$post_id = $_GET['post_id'];
		$query   = "SELECT * FROM wp_comments WHERE comment_post_ID = $post_id AND comment_type = 'comment'";
		$result  = $wpdb->get_results( $wpdb->prepare( $query ) );

		$data = array();
		foreach ( $result as $key => $value ) {

			$comment[ $key ] = get_comment( $value->comment_ID );
			$res             = $this->get_comment_meta( $value->comment_ID, $comment );
			$data[ $key ]    = array(
				'ID'              => $value->comment_ID,
				'comment_author'  => $value->comment_author,
				'comment_date'    => $value->comment_date,
				'comment_content' => strip_tags( $value->comment_content ),
				'file'            => ! empty( $res ) ? $res : array(),
			);
		}

		$response['status'] = true;
		$response['data']   = $data;

		echo json_encode( $response );
	}

	/**
	 * Get Comment Meta
	 */
	function get_comment_meta( $comment_id, &$comment ) {

		$meta_handler = Wkmbsc_Discussion_Function::get_instance();
		$files_meta   = get_comment_meta( $comment_id, '_files', true );
		$files        = array();

		foreach ( $files_meta as $index => $attachment_id ) {
			$temp[ $index ] = $meta_handler->get_file( $attachment_id );
		}
		return $temp;
	}

	/**
	 * To Get Chart Data
	 */
	public function wkmbsc_get_chart_details() {
		global $wpdb;

		$project_id = $_GET['project_id'];

		$chart_transient = 'cpm_chart_data_' . $project_id;
		$chart_date      = get_transient( $chart_transient );

		if ( $chart_date === false ) {
			$where          = $wpdb->prepare( "WHERE comment_post_ID = ' % d' AND DATE(comment_date) >= ' % s' AND DATE(comment_date) <= ' % s'", $project_id, $start_date, $end_date );
			$sql            = "SELECT * FROM {$wpdb->comments} $where  ";
			$total_activity = $wpdb->get_results( $sql );

			$csql  = "SELECT  * FROM {$wpdb->posts}
                    WHERE DATE(post_date) >= '{$start_date}'
                    AND DATE(post_date) <= '{$end_date}'
                    AND post_parent IN (SELECT ID FROM {$wpdb->posts} WHERE post_parent = '{$project_id}' ) ";
			$todos = $wpdb->get_results( $csql );

			$response['date_list'] = array();
			$response['todos']     = array();

			foreach ( $total_activity as $activity ) {
				$date = date( 'M d', strtotime( $activity->comment_date ) );

				if ( ! isset( $response['date_list'][ $date ] ) ) {
					$response['date_list'][ $date ] = 1;
				} else {

					$response['date_list'][ $date ] += 1;
				}
			}

			foreach ( $todos as $to_do ) {
				$tdate = date( 'M d', strtotime( $to_do->post_date ) );

				if ( ! isset( $response['todos'][ $tdate ] ) ) {
					$response['todos'][ $tdate ] = 1;
				} else {
					$response['todos'][ $tdate ] += 1;
				}
			}

			$data_transient = $response;
			set_transient( $chart_transient, $data_transient, DAY_IN_SECONDS );
		} else {
			$response = $chart_date;
		}

		$result['status'] = true;
		$result['list']   = $response;

		return new WP_REST_Response( $result );

	}

	/**Get All Activity */
	public function wkmbsc_get_all_activity() {
		try {
			global $wpdb;

			$project_id = $_GET['project_id'];

			$response = $wpdb->get_results( "SELECT * FROM wp_comments WHERE comment_post_id = $project_id AND comment_type = 'cpm_activity' order by comment_ID DESC" );

			foreach ( $response as $key => $value ) {

				$value_comment_content  = do_shortcode( $value->comment_content );
				$value->comment_content = stripslashes( $this->cpm_custom_do_shortcode( $value_comment_content ) );
			}

			if ( ! empty( $response ) ) {
				$result['status'] = true;
				$result['list']   = $response;
			} else {
				$result['status'] = false;
				$result['list']   = array();
			}

			return new WP_REST_Response( $result );
		} catch ( \Exception $e ) {
			return $e;
		}
	}


	function cpm_custom_do_shortcode( $content ) {
		global $shortcode_tags;

		if ( false === strpos( $content, '[' ) ) {
			return $content;
		}

		if ( empty( $shortcode_tags ) || ! is_array( $shortcode_tags ) ) {
			return $content;
		}

		$pattern = get_shortcode_regex();
		$sdf     = preg_replace_callback( "/$pattern/s", 'cpm_custom_do_shortcode_tag', $content );
		return $sdf;
	}
	/**
	 * Main WKMBSC_File_Handler Instance.
	 *
	 * Ensures only one instance of WKMBSC_File_Handler is loaded or can be loaded.
	 *
	 * @return Main instance.
	 * @since 1.0.0
	 * @static
	 */
	public static function get_instance() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}
		return self::$instance;
	}
}

