<?php
/**
 * Route Handler
 */
class Wkmbsc_Route_Handler {

	/**
	 * The single instance of the class.
	 *
	 * @var $instance
	 * @since 1.0.0
	 */
	protected static $instance = null;

	public function wkmbsc_restapi_init() {

		$function_handler   = Wkmbsc_Front_Function::get_instance();
		$milestone_handler  = Wkmbsc_Milestone_Function::get_instance();
		$tasklist_handler   = Wkmbsc_Tasklist_Function::get_instance();
		$task_handler       = Wkmbsc_Task_Function::get_instance();
		$discussion_handler = Wkmbsc_Discussion_Function::get_instance();
		$file_handler       = Wkmbsc_File_Function::get_instance();

		// To create project
		register_rest_route(
			'mobikul/v1',
			'/create-project/',
			array(
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => array( $function_handler, 'wkmbsc_create_project' ),
				'permission_callback' => function () {
					return true;
				},

			)
		);

		// To create MileStone
		register_rest_route(
			'mobikul/v1',
			'/create-milestone/',
			array(
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => array( $milestone_handler, 'wkmbsc_create_milestone' ),
				'permission_callback' => function () {
					return true;
				},
				'args'                => array(
					'post_author'       => array(
						'required' => true,
					),
					'project_id'        => array(
						'required' => true,
					),
					'milestone_name'    => array(
						'required' => true,
					),
					'milestone_detail'  => array(
						'required' => true,
					),
					'milestone_privacy' => array(
						'required' => true,
					),
					'milestone_due'     => array(
						'required' => true,
					),
				),
			)
		);

		// To create MileStone
		register_rest_route(
			'mobikul/v1',
			'/get-milestone/',
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( $milestone_handler, 'wkmbsc_get_milestone' ),
				'permission_callback' => function () {
					return true;
				},
				'args'                => array(
					'milestone_id' => array(
						'required' => true,
					),
				),

			)
		);

		// To  mark_complete MileStone
		register_rest_route(
			'mobikul/v1',
			'/mark-milestone-complete/',
			array(
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => array( $milestone_handler, 'wkmbsc_mark_milestone_complete' ),
				'permission_callback' => function () {
					return true;
				},
				'args'                => array(
					'milestone_id' => array(
						'required' => true,
					),
				),

			)
		);

		// To  mark_open MileStone
		register_rest_route(
			'mobikul/v1',
			'/mark-milestone-open/',
			array(
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => array( $milestone_handler, 'wkmbsc_mark_milestone_open' ),
				'permission_callback' => function () {
					return true;
				},
				'args'                => array(
					'milestone_id' => array(
						'required' => true,
					),
				),

			)
		);

		// To  delete MileStone
		register_rest_route(
			'mobikul/v1',
			'/delete-milestone/',
			array(
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => array( $milestone_handler, 'wkmbsc_delete_milestone' ),
				'permission_callback' => function () {
					return true;
				},
				'args'                => array(
					'milestone_id' => array(
						'required' => true,
					),
				),

			)
		);

		/**
		 * To Update Milestone
		 */
		register_rest_route(
			'mobikul/v1',
			'/update-milestone/',
			array(
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => array( $milestone_handler, 'wkmbsc_update_milestone' ),
				'permission_callback' => function () {
					return true;
				},
				'args'                => array(
					'milestone_id'      => array(
						'required' => true,
					),
					'post_author'       => array(
						'required' => true,
					),
					'project_id'        => array(
						'required' => true,
					),
					'milestone_name'    => array(
						'required' => true,
					),
					'milestone_detail'  => array(
						'required' => true,
					),
					'milestone_privacy' => array(
						'required' => true,
					),
					'milestone_due'     => array(
						'required' => true,
					),
				),

			)
		);

		// To add task list
		register_rest_route(
			'mobikul/v1',
			'/add-task-list/',
			array(
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => array( $tasklist_handler, 'wkmbsc_add_task_list' ),
				'permission_callback' => function () {
					return true;
				},
				'args'                => array(
					'project_id'       => array(
						'required' => true,
					),
					'tasklist_name'    => array(
						'required' => true,
					),
					'tasklist_privacy' => array(
						'required' => true,
					),
					'post_author'      => array(
						'required' => true,
					),
				),

			)
		);

		/**
		 * To edit task list
		 */
		register_rest_route(
			'mobikul/v1',
			'/edit-task-list/',
			array(
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => array( $tasklist_handler, 'wkmbsc_edit_task_list' ),
				'permission_callback' => function () {
					return true;
				},
				'args'                => array(
					'project_id'       => array(
						'required' => true,
					),
					'tasklist_name'    => array(
						'required' => true,
					),
					'tasklist_privacy' => array(
						'required' => true,
					),
					'post_author'      => array(
						'required' => true,
					),
					'list_id'          => array(
						'required' => true,
					),
				),

			)
		);

		/**
		 * To Delete task list
		 */
		register_rest_route(
			'mobikul/v1',
			'/delete-task-list/',
			array(
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => array( $tasklist_handler, 'wkmbsc_delete_task_list' ),
				'permission_callback' => function () {
					return true;
				},
				'args'                => array(
					'tasklist_id' => array(
						'required' => true,
					),
				),

			)
		);

		/**
		 * To Get tasklist
		 */
		register_rest_route(
			'mobikul/v1',
			'/get-task-list/(?P<author_id>\d+)/(?P<project_id>\d+)',
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( $tasklist_handler, 'wkmbsc_get_tasklist' ),
				'permission_callback' => function () {
					return true;
				},
				'args'                => array(
					'author_id' => array(
						'required' => true,
					),
				),

			)
		);

		/**
		 * To Create Folder
		 */
		register_rest_route(
			'mobikul/v1',
			'/create-folder/',
			array(
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => array( $file_handler, 'wkmbsc_create_folder' ),
				'permission_callback' => function () {
					return true;
				},
				'args'                => array(
					'project_id' => array(
						'required' => true,
					),
				),

			)
		);

		/**
		 * To Rename Folder
		 */
		register_rest_route(
			'mobikul/v1',
			'/rename-folder/',
			array(
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => array( $file_handler, 'wkmbsc_rename_folder' ),
				'permission_callback' => function () {
					return true;
				},
				'args'                => array(
					'project_id' => array(
						'required' => true,
					),
				),

			)
		);

		/**
		 * To Delete Folder
		 */
		register_rest_route(
			'mobikul/v1',
			'/delete-folder/',
			array(
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => array( $file_handler, 'wkmbsc_delete_folder' ),
				'permission_callback' => function () {
					return true;
				},
				'args'                => array(
					'folderid' => array(
						'required' => true,
					),
				),

			)
		);

		/**
		 * To Upload File
		 */
		register_rest_route(
			'mobikul/v1',
			'/upload/',
			array(
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => array( $file_handler, 'wkmbsc_upload' ),
				'permission_callback' => function () {
					return true;
				},
				// 'args'                => array(
				// 'folderid' => array(
				// 'required' => true,
				// ),
				// ),
			)
		);

		/**
		 * To Create Doc
		 */
		register_rest_route(
			'mobikul/v1',
			'/create-doc/',
			array(
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => array( $file_handler, 'wkmbsc_create_doc' ),
				'permission_callback' => function () {
					return true;
				},
				// 'args'                => array(
				// 'folderid' => array(
				// 'required' => true,
				// ),
				// ),

			)
		);

		/**
		 * To Create Task
		 */
		register_rest_route(
			'mobikul/v1',
			'/create-task/',
			array(
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => array( $task_handler, 'wkmbsc_create_task' ),
				'permission_callback' => function () {
					return true;
				},
				// 'args'                => array(
				// 'folderid' => array(
				// 'required' => true,
				// ),
				// ),

			)
		);

		/**
		 * To Update Task
		 */
		register_rest_route(
			'mobikul/v1',
			'/update-task/',
			array(
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => array( $task_handler, 'wkmbsc_update_task' ),
				'permission_callback' => function () {
					return true;
				},
				// 'args'                => array(
				// 'folderid' => array(
				// 'required' => true,
				// ),
				// ),
			)
		);

		/**
		 * To Delete Task
		 */
		register_rest_route(
			'mobikul/v1',
			'/delete-task/',
			array(
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => array( $task_handler, 'wkmbsc_delete_task' ),
				'permission_callback' => function () {
					return true;
				},
				// 'args'                => array(
				// 'folderid' => array(
				// 'required' => true,
				// ),
				// ),
			)
		);

		/**
		 * To Get tasklist
		 */
		register_rest_route(
			'mobikul/v1',
			'/get-task/(?P<tasklist_id>\d+)',
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( $task_handler, 'wkmbsc_get_task' ),
				'permission_callback' => function () {
					return true;
				},
				'args'                => array(
					'tasklist_id' => array(
						'required' => true,
					),
				),
			)
		);

		/**
		 * Mark Task Complete
		 */
		register_rest_route(
			'mobikul/v1',
			'/mark-task-complete/',
			array(
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => array( $task_handler, 'wkmbsc_mark_task_complete' ),
				'permission_callback' => function () {
					return true;
				},
				'args'                => array(
					'task_id' => array(
						'required' => true,
					),
				),
			)
		);

		/**
		 * Mark Task Uncomplete
		 */
		register_rest_route(
			'mobikul/v1',
			'/mark-task-uncomplete/',
			array(
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => array( $task_handler, 'wkmbsc_mark_task_uncomplete' ),
				'permission_callback' => function () {
					return true;
				},
				'args'                => array(
					'task_id' => array(
						'required' => true,
					),
				),
			)
		);

		/**
		 * Add Comment
		 */
		register_rest_route(
			'mobikul/v1',
			'/add-comment/',
			array(
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => array( $task_handler, 'wkmbsc_add_comment' ),
				'permission_callback' => function () {
					return true;
				},
				'args'                => array(
					'project_id' => array(
						'required' => true,
					),
				),
			)
		);

		/**
		 * Get Comment
		 */
		register_rest_route(
			'mobikul/v1',
			'/get-comment/',
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( $task_handler, 'wk_get_task_comments' ),
				'permission_callback' => function () {
					return true;
				},
			)
		);

		/**
		 * Create Discussion
		 */
		register_rest_route(
			'mobikul/v1',
			'/add-message/',
			array(
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => array( $discussion_handler, 'wkmbsc_create_message' ),
				'permission_callback' => function () {
					return true;
				},
				'args'                => array(
					'project_id' => array(
						'required' => true,
					),
				),
			)
		);

		/**
		 * Delete Discussion
		 */
		register_rest_route(
			'mobikul/v1',
			'/delete-message/',
			array(
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => array( $discussion_handler, 'wkmbsc_delete_message' ),
				'permission_callback' => function () {
					return true;
				},
				'args'                => array(
					'message_id' => array(
						'required' => true,
					),
				),
			)
		);

		/**
		 * To Get Discussion
		 */
		register_rest_route(
			'mobikul/v1',
			'/get-discussion/(?P<task_id>\d+)/(?P<post_author>\d+)',
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( $discussion_handler, 'wkmbsc_get_discussion' ),
				'permission_callback' => function () {
					return true;
				},
				'args'                => array(
					'task_id' => array(
						'required' => true,
					),
				),
			)
		);

		/**
		 * Reply Comment
		 */
		register_rest_route(
			'mobikul/v1',
			'/reply-message/',
			array(
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => array( $discussion_handler, 'wkmbsc_reply_message' ),
				'permission_callback' => function () {
					return true;
				},
				'args'                => array(
					'project_id' => array(
						'required' => true,
					),
				),
			)
		);

		/**
		 * To Upload File
		 */
		register_rest_route(
			'mobikul/v1',
			'/upload-file/',
			array(
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => array( $file_handler, 'wkmbsc_upload_file' ),
				'permission_callback' => function () {
					return true;
				},
				// 'args'                => array(
				// 'folderid' => array(
				// 'required' => true,
				// ),
				// ),
			)
		);

		/**
		 * To Get  File and folder
		 */
		register_rest_route(
			'mobikul/v1',
			'/get-file-folder/(?P<project_id>\d+)/(?P<user_id>\d+)/(?P<parent_id>\d+)',
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( $file_handler, 'wkmbsc_get_files_folder' ),
				'permission_callback' => function () {
					return true;
				},
				// 'args'                => array(
				// 'folderid' => array(
				// 'required' => true,
				// ),
				// ),
			)
		);

				/**
		 * To Get Projects
		 */
		register_rest_route(
			'mobikul/v1',
			'/get-projects/',
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( $function_handler, 'wkmbsc_get_projects' ),
				'permission_callback' => function () {
					return true;
				},
				// 'args'                => array(
				// 'folderid' => array(
				// 'required' => true,
				// ),
				// ),
			)
		);

						/**
		 * To Get all milestone
		 */
		register_rest_route(
			'mobikul/v1',
			'/get-all-milestone/',
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( $milestone_handler, 'wkmbsc_get_all_milestone' ),
				'permission_callback' => function () {
					return true;
				},
				// 'args'                => array(
				// 'folderid' => array(
				// 'required' => true,
				// ),
				// ),
			)
		);

		/**
		 * To create link  to doc
		 */
		register_rest_route(
			'mobikul/v1',
			'/create-link-doc/',
			array(
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => array( $file_handler, 'wkmbsc_link_to_doc' ),
				'permission_callback' => function () {
					return true;
				},
				// 'args'                => array(
				// 'folderid' => array(
				// 'required' => true,
				// ),
				// ),
			)
		);

		/**
		 * To get task  details
		 */
		register_rest_route(
			'mobikul/v1',
			'/get-task-details/',
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( $task_handler, 'wkmbsc_get_task_details' ),
				'permission_callback' => function () {
					return true;
				},
				// 'args'                => array(
				// 'folderid' => array(
				// 'required' => true,
				// ),
				// ),
			)
		);

		/**
		 * To get chart details
		 */
		register_rest_route(
			'mobikul/v1',
			'/get-chart-details/',
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( $task_handler, 'wkmbsc_get_chart_details' ),
				'permission_callback' => function () {
					return true;
				},
				// 'args'                => array(
				// 'folderid' => array(
				// 'required' => true,
				// ),
				// ),
			)
		);

		/**
		 * To get All activity
		 */
		register_rest_route(
			'mobikul/v1',
			'/get-all-activity/',
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( $task_handler, 'wkmbsc_get_all_activity' ),
				'permission_callback' => function () {
					return true;
				},
				// 'args'                => array(
				// 'folderid' => array(
				// 'required' => true,
				// ),
				// ),
			)
		);

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
