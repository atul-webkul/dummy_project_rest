<?php
/**
 * Class for milestone API
 */

class Wkmbsc_Milestone_Function {

	/**
	 * The single instance of the class.
	 *
	 * @var $instance
	 * @since 1.0.0
	 */
	protected static $instance = null;


	/**
	 * Create MileStone
	 */
	public function wkmbsc_create_milestone( WP_REST_Request $request ) {

		$data              = $request->get_params();
		$milestone_privacy = isset( $data['milestone_privacy'] ) ? $data['milestone_privacy'] : 'no';

		$postdata = array(
			'post_author'  => $data['post_author'],
			'post_parent'  => $data['project_id'],
			'post_title'   => $data['milestone_name'],
			'post_content' => $data['milestone_detail'],
			'post_type'    => 'cpm_milestone',
			'post_status'  => 'publish',
		);

		$milestone_id = wp_insert_post( $postdata );
		update_post_meta( $milestone_id, '_completed', 0 ); // open initially

		if ( $milestone_id ) {
			$data['milestone_due'] = isset( $data['milestone_due'] ) && ! empty( $data['milestone_due'] ) ? cpm_date2mysql( $data['milestone_due'] ) : current_time( 'mysql' );
			update_post_meta( $milestone_id, '_due', $data['milestone_due'] );
			update_post_meta( $milestone_id, '_milestone_privacy', $milestone_privacy );
			CPM_Project::getInstance()->new_project_item( $data['project_id'], $milestone_id, $milestone_privacy, 'milestone', false );
			do_action( 'cpm_milestone_new', $milestone_id, $data['project_id'], $data );
		}

		if ( $milestone_id ) {
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
	 * To Get MileStone
	 */
	public function wkmbsc_get_milestone( WP_REST_Request $request ) {
		$milestone_id = $request->get_param( 'milestone_id' );
		$milestone    = get_post( $milestone_id );
		$this->set_meta( $milestone );
		$milestone->task= $this->get_tasklist_by_milestone((int)$milestone_id );

		if ( '' !== $milestone_id ) {
			return new WP_REST_Response(
				array(
					'Message'   => 'Success.',
					'status'    => true,
					'milestone' => $milestone,
				),
			);
		} else {

			return new WP_Error(
				'no_data_found',
				'No data Found',
			);

		}

	}

	function set_meta( &$milestone ) {
		$milestone->due_date     = get_post_meta( $milestone->ID, '_due', true );
		$milestone->completed    = get_post_meta( $milestone->ID, '_completed', true );
		$milestone->completed_on = get_post_meta( $milestone->ID, '_completed_on', true );
		$milestone->private      = get_post_meta( $milestone->ID, '_milestone_privacy', true );
	}

	/**
	 * To mark milestone as complete
	 */
	public function wkmbsc_mark_milestone_complete( WP_REST_Request $request ) {
		$milestone_id = $request->get_param( 'milestone_id' );
		update_post_meta( $milestone_id, '_completed', 1 );
		update_post_meta( $milestone_id, '_completed_on', current_time( 'mysql' ) );
		CPM_Project::getInstance()->new_project_item_complete_date( $milestone_id, current_time( 'mysql' ) );
		do_action( 'cpm_milestone_complete', $milestone_id );
		if ( '' !== $milestone_id ) {
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
	 * To Delete Milestone
	 */
	public function wkmbsc_delete_milestone( WP_REST_Request $request, $force = false ) {
		$milestone_id = $request->get_param( 'milestone_id' );
		do_action( 'cpm_milestone_delete', $milestone_id, $force );

		CPM_Project::getInstance()->delete_project_item( $milestone_id );

		wp_delete_post( $milestone_id, $force );

		if ( '' !== $milestone_id ) {
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
	 * To Mark Milestone as open
	 */
	public function wkmbsc_mark_milestone_open( WP_REST_Request $request ) {
		$milestone_id = $request->get_param( 'milestone_id' );
		update_post_meta( $milestone_id, '_completed', 0 );
		update_post_meta( $milestone_id, '_completed_on', current_time( 'mysql' ) );
		CPM_Project::getInstance()->new_project_item_complete_open( $milestone_id );
		do_action( 'cpm_milestone_open', $milestone_id );
		if ( '' !== $milestone_id ) {
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
	 * To Update milestone
	 */
	public function wkmbsc_update_milestone( WP_REST_Request $request ) {
		$data = $request->get_params();

		$milestone_privacy = isset( $data['milestone_privacy'] ) ? $data['milestone_privacy'] : 'no';

		$postdata       = array(
			'post_author'  => $data['post_author'],
			'post_parent'  => $data['project_id'],
			'post_title'   => $data['milestone_name'],
			'post_content' => $data['milestone_detail'],
			'post_type'    => 'cpm_milestone',
			'post_status'  => 'publish',
		);
		$postdata['ID'] = $data['milestone_id'];
		$milestone_id   = wp_update_post( $postdata );

		if ( $milestone_id ) {
			$data['milestone_due'] = isset( $data['milestone_due'] ) && ! empty( $data['milestone_due'] ) ? cpm_date2mysql( $data['milestone_due'] ) : current_time( 'mysql' );
			update_post_meta( $milestone_id, '_due', $data['milestone_due'] );
			update_post_meta( $milestone_id, '_milestone_privacy', $milestone_privacy );

			CPM_Project::getInstance()->new_project_item( $data['project_id'], $milestone_id, $milestone_privacy, 'milestone', true );

			do_action( 'cpm_milestone_update', $milestone_id, $data['project_id'], $data );
		}

		if ( $milestone_id ) {
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
	 * Get All Milestone
	 **/
	public function wkmbsc_get_all_milestone() {

		$args  = array(
			'author'      => $_GET['author_id'],
			'post_type'   => 'cpm_milestone',
			'post_parent' => $_GET['project_id'],
		);
		$posts = get_posts( $args );

		foreach ( $posts as  $key => $post ) {


			// Due date
			$due     = strtotime( array_pop( get_post_meta( $post->ID )['_due'] ) );
			$is_left = cpm_is_left( time(), $due );
			$string  = ( $is_left == true ) ? __( 'left', 'cpm' ) : __( 'late', 'cpm' );

			// Milestone Status
			$milestone_completed = (int) array_pop( get_post_meta( $post->ID )['_completed'] );
			if ( $milestone_completed ) {
				$class = 'complete';
			} else {
				$class = ( $is_left == true ) ? 'Upcoming Milestone' : 'Late Milestone';
			}

			// Milestone Privacy
			$milestone_privacy = (int) array_pop( get_post_meta( $post->ID )['_milestone_privacy'] );
			if ( $milestone_privacy == 'yes' ) {
				$privacy = true;
			} else {
				$privacy = false;
			}

			$post->post_content     = strip_tags( $post->post_content );
			$post->due              = human_time_diff( time(), $due ) . ' ' . $string . ' - ' . strip_tags( cpm_get_date( $due ) );
			$post->milestone_status = $class;
			$post->privacy          = $privacy;
			$post->task =	$this->get_tasklist_by_milestone($post->ID );

		}

		if ( ! empty( $posts ) ) {
			return new WP_REST_Response(
				array(
					'Message' => 'Success.',
					'status'  => true,
					'list'    => $posts,
				),
			);
		} else {
			return new WP_REST_Response(
				array( 'status' => false ),
				200,
			);

		}

	}


	/**
	 * Get all tasks based on a milestone
	 *
	 * @param int $list_id
	 * @return object object array of the result set
	 */
	function get_tasklist_by_milestone( $milestone_id, $privacy = false ) {

		$args = array(
			'post_type'   => 'cpm_task_list',
			'numberposts' => -1,
			'order'       => 'ASC',
			'orderby'     => 'menu_order',
		);

		$args['meta_query'][] = array(
			'key'   => '_milestone',
			'value' => $milestone_id,
		);

		if ( $privacy === false ) {
			$args['meta_query'][] = array(
				'key'     => '_tasklist_privacy',
				'value'   => 'yes',
				'compare' => '!=',
			);
		}

		$tasklists = get_posts( $args );
		foreach ( $tasklists  as $key => $list ) {
			$list->completed = intval(get_post_meta( $list->ID, '_completed', true )) ;
			// $this->set_list_meta( $list );
		}

		return $tasklists;
	}

	/**
	 * Set meta info for a task list
	 *
	 * @param object $task_list
	 */
	function set_list_meta( &$task_list ) {
		$task_list->due_date                = get_post_meta( $task_list->ID, '_due', true );
		$task_list->milestone               = get_post_meta( $task_list->ID, '_milestone', true );
		$task_list->private                 = get_post_meta( $task_list->ID, '_tasklist_privacy', true );
		$task_list->pin_list                = is_sticky( $task_list->ID ) ? true : false;
		$task_list->edit_mode               = false;
		$task_list->show_task_form          = false;
		// $task_list->can_del_edit            = cpm_user_can_delete_edit( $task_list->post_parent, $task_list );
		// $task_list->count_completed_tasks   = $this->count_completed_tasks( $task_list->ID );
		// $task_list->count_incompleted_tasks = $this->count_incompleted_tasks( $task_list->ID );
		$comments                           = wp_count_comments( $task_list->ID );
		$task_list->count_comments          = $comments->approved;
		$task_list->tasks                   = array();
		return $task_list;
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

