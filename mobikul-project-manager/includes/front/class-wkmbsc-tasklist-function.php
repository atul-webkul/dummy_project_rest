<?php
/**
 * Class for milestone API
 */

class Wkmbsc_Tasklist_Function {

	/**
	 * The single instance of the class.
	 *
	 * @var $instance
	 * @since 1.0.0
	 */
	protected static $instance = null;



	/**
	 * To add task list
	 */
	public function wkmbsc_add_task_list( WP_REST_Request $request ) {
		$data = $request->get_params();

		$tasklist_privacy = empty( $data['tasklist_privacy'] ) ? 'no' : $data['tasklist_privacy'];

		$postdata = array(
			'post_author'  => $data['post_author'],
			'post_parent'  => $data['project_id'],
			'post_title'   => $data['tasklist_name'],
			'post_content' => empty( $data['tasklist_detail'] ) ? '' : $data['tasklist_detail'],
			'post_type'    => 'cpm_task_list',
			'post_status'  => 'publish',
		);

		$list_id = wp_insert_post( $postdata );

		if ( $list_id ) {
			update_post_meta( $list_id, '_milestone', $data['tasklist_milestone'] );

			update_post_meta( $list_id, '_tasklist_privacy', $tasklist_privacy );

			CPM_Project::getInstance()->new_project_item( $data['project_id'], $list_id, $tasklist_privacy, 'cpm_task_list', false );

			do_action( 'cpm_tasklist_new', $list_id, $data['project_id'], $data );
		}

		if ( $list_id ) {
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
	 * To Edit Task List
	 */
	public function wkmbsc_edit_task_list( WP_REST_Request $request ) {
		$data = $request->get_params();
		if ( empty( $data['tasklist_name'] ) ) {
			return new WP_Error( 'tasklist_name', __( 'Task list name is required.', 'cpm' ) );
		}

		$tasklist_privacy = empty( $data['tasklist_privacy'] ) ? 'no' : $data['tasklist_privacy'];

		$postdata = array(
			'post_author'  => $data['post_author'],
			'post_parent'  => $data['project_id'],
			'post_title'   => $data['tasklist_name'],
			'post_content' => empty( $data['tasklist_detail'] ) ? '' : $data['tasklist_detail'],
			'post_type'    => 'cpm_task_list',
			'post_status'  => 'publish',
		);

		$postdata['ID'] = $data['list_id'];
		$list_id        = wp_update_post( $postdata );

		if ( $list_id ) {
			update_post_meta( $list_id, '_milestone', $postdata['tasklist_milestone'] );

			update_post_meta( $list_id, '_tasklist_privacy', $tasklist_privacy );

			CPM_Project::getInstance()->new_project_item( $data['project_id'], $list_id, $tasklist_privacy, 'cpm_task_list', true );

			do_action( 'cpm_tasklist_update', $list_id, $data['project_id'], $data );
		}

		if ( $list_id ) {
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
	 * Delete task list
	 */
	public function wkmbsc_delete_task_list( WP_REST_Request $request, $force = false ) {
		$task_id = $request->get_param( 'tasklist_id' );
		do_action( 'cpm_task_delete', $task_id, $force );
		// $item_id = $this->get_item_id( $task_id );
		// $this->delete_task_item( $item_id );

		CPM_Project::getInstance()->delete_project_item( $task_id );

		wp_delete_post( $task_id, $force );

		if ( '' !== $task_id ) {
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
	 **/
	public function wkmbsc_get_tasklist( $request ) {

		$args = array(
			// 'author'      => $request['author_id'],
			'post_type'   => 'cpm_task_list',
			'post_parent' => $request['project_id'],

		);

		$posts = get_posts( $args );

		$details_instance = Wkmbsc_Task_Function::get_instance();

		// return $task_detail;

		foreach ( $posts as $post ) {
			$task_detail = $details_instance->wkmbsc_get_task_details( $post->ID );
			// tasklist privacy
			$tasklist_privacy = get_post_meta( $post->ID )['_tasklist_privacy'][0];
			if ( 'on' === $tasklist_privacy ) {
				$privacy = true;
			} else {
				$privacy = false;
			}
			$post->privacy    = $privacy;
			$post->completed  = array_sum( wp_list_pluck( $task_detail, 'completed' ) );
			$post->incomplete = ceil(
				count( $task_detail ) - array_sum( wp_list_pluck( $task_detail, 'completed' ) )
			);

			$completed_task = array_sum( wp_list_pluck( $task_detail, 'completed' ) );
			$total_task     = count( $task_detail );
			$progress       = ( 100 * $completed_task ) / $total_task;
			$post->progress = is_nan( $progress ) ? 0 : round( $progress );
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
				array(
					'status'  => false,
					'message' => 'No Data Found',
				),
			);
		}
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

