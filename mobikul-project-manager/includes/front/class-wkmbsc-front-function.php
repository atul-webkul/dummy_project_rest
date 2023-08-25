<?php
/**
 * class For Rest API.
 */

class Wkmbsc_Front_Function {

	/**
	 * The single instance of the class.
	 *
	 * @var $instance
	 * @since 1.0.0
	 */
	protected static $instance = null;

	/**
	 * To Create Project
	 */
	public function wkmbsc_create_project( WP_REST_Request $request ) {

		$data = $request->get_params();

		$project    = array(
			'post_title'   => $data['project_name'],
			'post_content' => isset( $data['project_description'] ) ? $data['project_description'] : '',
			'post_type'    => 'cpm_project',
			'post_status'  => 'publish',
			'post_author'  => $data['author_id'],
		);
		$project_id = wp_insert_post( $project );

		$project_cat = isset( $data['project_cat'] ) ? $data['project_cat'] : '';
		wp_set_post_terms( $project_id, $project_cat, 'cpm_project_category', false );
		$this->insert_user( $project_id, $data['author_id'], 'manager' );
		$this->insert_user( $project_id, $data['user_id'], $data['role'] );

		update_post_meta( $project_id, '_project_archive', 'no' );
		update_post_meta( $project_id, '_project_active', 'yes' );
		$settings = $this->settings_user_permission();
		update_post_meta( $project_id, '_settings', $settings );
		do_action( 'cpm_project_new', $project_id, $data, $request );

		return new WP_REST_Response(
			array(
				'Message' => 'Success.',
				'status'  => true,
			),
		);

	}

	function insert_user( $project_id, $user_id, $role ) {
		global $wpdb;
		$table  = $wpdb->prefix . 'cpm_user_role';
		$data   = array(
			'project_id' => $project_id,
			'user_id'    => $user_id,
			'role'       => $role,
		);
		$format = array( '%d', '%d', '%s' );
		$wpdb->insert( $table, $data, $format );
	}

	function settings_user_permission() {
		$labels = cpm_settings_label();
		print_r( $labels );

		foreach ( $labels as $section => $name ) {
			foreach ( $name as $key => $field ) {
				$settings['co_worker'][ $key ] = 'yes';
				$settings['client'][ $key ]    = 'yes';
			}
		}

		$client_permission = array(
			'msg_view_private'       => 'no',
			'tdolist_view_private'   => 'no',
			'todo_view_private'      => 'no',
			'create_milestone'       => 'no',
			'milestone_view_private' => 'no',
		);

		foreach ( $client_permission as $name => $premisson ) {
			$settings['client'][ $name ] = $premisson;
		}

		return $settings;
	}


	public function wkmbsc_get_projects( WP_REST_Request $request, $count = -1, $order_by = array() ) {

		$pagenum          = isset( $_GET['pagenum'] ) ? absint( $_GET['pagenum'] ) : 1;
		$limit            = ( $count == '-1' ) ? intval( cpm_get_option( 'pagination', 'cpm_general' ) ) : $count;
		$offset           = ( $pagenum - 1 ) * $limit;
		$filters          = $_GET;
		$project_category = isset( $filters['project_cat'] ) ? $filters['project_cat'] : 0;

		$args = array(
			'post_type'      => 'cpm_project',
			'posts_per_page' => $limit,
			'offset'         => $offset,
		);
		if ( ! empty( $order_by ) ) {
			$args['orderby'] = $order_by[0];
			$args['order']   = $order_by[1];
		}

		// Add Filtering
		if ( $project_category != 0 && $project_category != '-1' ) {
			$args['tax_query'][] = array(
				'taxonomy' => 'cpm_project_category',
				'field'    => 'term_id',
				'terms'    => array( $project_category ),
				'operator' => 'IN',
			);
		}

		if ( isset( $_GET['page'] ) && $_GET['page'] == 'cpm_projects' && isset( $_GET['status'] ) ) {
			if ( $_GET['status'] == 'archive' ) {

				$args['meta_query'] = array(
					array(
						'key'     => '_project_active',
						'value'   => 'no',
						'compare' => '=',
					),
				);
			} elseif ( $_GET['status'] == 'active' ) {
				$args['meta_query'] = array(
					array(
						'key'     => '_project_active',
						'value'   => 'yes',
						'compare' => '=',
					),
				);
			} elseif ( $_GET['status'] == 'all' ) {
				$args['meta_query'] = '';
			}
		} else {
			$args['meta_query'] = array(
				array(
					'key'     => '_project_active',
					'value'   => 'yes',
					'compare' => '=',
				),
			);
		}

		/*
		if ( cpm_can_manage_projects() === false ) {
		add_filter( 'posts_join', array( $this, 'jonin_user_role_table' ) );
		add_filter( 'posts_where', array( $this, 'get_project_where_user_role' ), 10, 2 );
		} */
		if ( cpm_can_manage_projects() === false ) {

			add_filter( 'posts_join', array( $this, 'jonin_user_role_table' ) );
			add_filter( 'posts_where', array( $this, 'get_project_where_user_role' ), 10, 3 );
		}
		/*
		elseif( cpm_can_manage_projects() === true && !is_admin() ){
		add_filter( 'posts_join', array($this, 'jonin_user_role_table') );
		add_filter( 'posts_where', array($this, 'get_project_where_user_role_can'), 10, 3 );
		} */

		// $args = apply_filters( 'cpm_get_projects_argument', $args );

		$projects       = new WP_Query( $args );        // echo $projects->request;
		$total_projects = $projects->found_posts;
		$post           = $projects->posts;
		$post_data      = array();
		foreach ( $post as $vals ) {
			$post_data[] = array(
				'ID'                    => $vals->ID,
				'post_author'           => $vals->post_author,
				'post_date'             => $vals->post_date,
				'post_date_gmt'         => $vals->post_date_gmt,
				'post_content'          => $vals->post_content,
				'post_title'            => $vals->post_title,
				'post_excerpt'          => $vals->post_excerpt,
				'post_status'           => $vals->comment_status,
				'comment_status'        => $vals->comment_status,
				'ping_status'           => $vals->ping_status,
				'post_password'         => $vals->post_password,
				'post_name'             => $vals->post_name,
				'to_ping'               => $vals->to_ping,
				'pinged'                => $vals->pinged,
				'post_modified'         => $vals->post_modified,
				'post_modified_gmt'     => $vals->post_modified_gmt,
				'post_content_filtered' => $vals->post_content_filtered,
				'post_parent'           => $vals->post_parent,
				'guid'                  => $vals->guid,
				'menu_order'            => $vals->menu_order,
				'post_type'             => $vals->post_type,
				'post_mime_type'        => $vals->post_mime_type,
				'comment_count'         => $vals->comment_count,
				'filter'                => $vals->filter,
				'info'                  => $this->get_info( $vals->ID ),
				'users'                 => $this->get_users( $vals ),
				// 'due'                   => printf( '%s %s - %s', human_time_diff( time(), $due ), $string, cpm_get_date( $milestone->due_date ) ),
			);
			// $demo = array();
			// foreach ( $post_data as &$project ) {

			// $project['info']  = $this->get_info( $project->ID );
			// $project['users'] = $this->get_users( $project );

			// }
		}

		return new WP_REST_Response(
			array(
				'status' => true,
				'data'   => $post_data,
			)
		);

	}


	function jonin_user_role_table( $join ) {
		global $wp_query, $wpdb;

		$table = $wpdb->prefix . 'cpm_user_role';
		$join .= "LEFT JOIN $table ON $wpdb->posts.ID = $table.project_id";

		return $join;
	}

	function get_project_where_user_role( $where, $wp_query, $user_id = 0 ) {
		global $wpdb;

		$table = $wpdb->prefix . 'cpm_user_role';

		if ( absint( $user_id ) ) {
			$user_id = $user_id;
		} else {
			$user_id = $_GET['user_id'];
		}

		$project_where = " AND $table.user_id = $user_id";
		$where        .= apply_filters( 'cpm_get_projects_where', $project_where, $table, $where, $wp_query, $user_id );
		return $where;
	}

	function get_info( $project_id ) {
		global $wpdb;

		$ret = wp_cache_get( 'cpm_project_info_' . $project_id );

		if ( false === $ret ) {
			// get discussions
			$sql       = "SELECT ID, comment_count FROM $wpdb->posts WHERE `post_type` = '%s' AND `post_status` = 'publish' AND `post_parent` IN (%s);";
			$sql_files = "SELECT COUNT(ID) FROM $wpdb->posts p INNER JOIN $wpdb->postmeta m ON (p.ID = m.post_id) WHERE p.post_type = 'attachment' AND (p.post_status = 'publish' OR p.post_status = 'inherit') AND ( (m.meta_key = '_project' AND CAST(m.meta_value AS CHAR) = '$project_id') )";

			$discussions = $wpdb->get_results( sprintf( $sql, 'cpm_message', $project_id ) );
			$todolists   = $wpdb->get_results( sprintf( $sql, 'cpm_task_list', $project_id ) );
			$milestones  = $wpdb->get_results( sprintf( $sql, 'cpm_milestone', $project_id ) );
			$todos       = $todolists ? $wpdb->get_results( sprintf( $sql, 'cpm_task', implode( ', ', wp_list_pluck( $todolists, 'ID' ) ) ) ) : array();
			$files       = $wpdb->get_var( $sql_files );

			// for promodule files

			$discussion_comment = wp_list_pluck( $discussions, 'comment_count' );
			$todolist_comment   = wp_list_pluck( $todolists, 'comment_count' );
			$todo_comment       = $todolists ? wp_list_pluck( $todos, 'comment_count' ) : array();
			$milestone          = wp_list_pluck( $milestones, 'ID' );

			$total_comment = array_sum( $discussion_comment ) + array_sum( $todolist_comment ) + array_sum( $todo_comment );

			$ret             = new stdClass();
			$ret->discussion = count( $discussions );
			$ret->todolist   = count( $todolists );
			$sticky          = get_option( 'sticky_posts' );
			if ( ! empty( $sticky ) ) {
				$sticky                    = implode( ',', $sticky );
				$sql_pin_list              = "SELECT ID FROM $wpdb->posts WHERE `post_type` = 'cpm_task_list' AND `post_status` = 'publish' AND `post_parent` = $project_id  AND ID IN( $sticky );";
				$pin_todolists             = $wpdb->get_results( $sql_pin_list );
				$ret->pin_list             = count( $pin_todolists );
				$ret->todolist_without_pin = ( $ret->todolist - $ret->pin_list );
			} else {
				$ret->pin_list = 0;
			}
			$ret->todolist_without_pin = ( $ret->todolist - $ret->pin_list );
			$ret->todos                = count( $todos );
			$ret->comments             = $total_comment;
			$ret->files                = (int) $files;
			$ret->milestone            = count( $milestone );
			$ret->total_attach_doc     = $ret->files;
			$ret->files                = apply_filters( 'cpm_project_total_files', $ret->files, $project_id );

			wp_cache_set( 'cpm_project_info_' . $project_id, $ret );
		}

		return $ret;
	}

	function get_users( $project ) {
		global $wpdb;

		if ( is_object( $project ) ) {
			$project_id = $project->ID;
		} else {
			$project_id = $project;
		}

		$user_list = array();
		$table     = $wpdb->prefix . 'cpm_user_role';
		$query     = "SELECT user_id, role FROM {$table} WHERE project_id = %d AND component = ''";

		$query = apply_filters( 'cpm_get_users_query', $query, $project, $table );

		$project_users = $wpdb->get_results( $wpdb->prepare( $query, $project_id ) );

		$project_users = apply_filters( 'cpm_get_users', $project_users, $project, $table );

		if ( $project_users ) {
			foreach ( $project_users as $row ) {
				$user = get_user_by( 'id', $row->user_id );
				// print_r($user) ;
				if ( ! is_wp_error( $user ) && $user ) {

					$u = array(
						'id'         => $user->ID,
						'email'      => $user->user_email,
						'name'       => $user->display_name,
						'login_name' => $user->user_login,
						'role'       => $row->role,
						'avatar'     => get_avatar( $user->ID, 96, 'mm' ),
						'avatar_url' => get_avatar_url( $user->ID, array( 'default' => 'mm' ) ),
						'user_url'   => cpm_url_user( $user->ID, true, 48, $user ),
					);

					array_push( $user_list, $u );
				}
			}
		}

		return $user_list;
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
