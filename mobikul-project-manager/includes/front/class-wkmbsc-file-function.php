<?php
/**
 * class For Rest API.
 */

class Wkmbsc_File_Function {

	/**
	 * The single instance of the class.
	 *
	 * @var $instance
	 * @since 1.0.0
	 */
	protected static $instance = null;

	/**
	 * To Create  Folder
	 */
	public function wkmbsc_create_folder( WP_REST_Request $request ) {
		$data = $request->get_params();

		$project_id  = isset( $data['project_id'] ) ? intval( $data['project_id'] ) : 0;
		$folder_name = isset( $data['name'] ) ? sanitize_text_field( $data['name'] ) : '';
		$privacy     = isset( $data['privacy'] ) ? 'yes' : 'no';
		$parent      = isset( $data['parent'] ) ? intval( $data['parent'] ) : 0;

		$response['status'] = false;
		if ( isset( $data ) ) {
			if ( $this->check_existing_folder_by_parent( $folder_name, $parent, $project_id ) ) {
				$response['status'] = false;
				$response['error']  = __( 'Folder name already exist! Please check again.', 'cpm' );
			} else {

				global $wpdb;
				$table = $wpdb->prefix . 'cpm_file_relationship';

				$postdata = array(
					'project_id' => $project_id,
					'dir_name'   => $folder_name,
					'parent_id'  => $parent,
					'is_dir'     => '1',
					'private'    => $privacy,
					'created_by' => $data['user_id'],
					'created_at' => date( 'Y-m-d H:i:s' ),
				);

				if ( $wpdb->insert( $table, $postdata ) ) {
					$response['status']  = true;
					$response['message'] = 'success';
				}
			}
		}
		if ( '' !== $data ) {
			return new WP_REST_Response(
				$response
			);
		} else {
			return new WP_Error(
				'no_data_found',
				'No data Found',
			);
		}
	}
	/**
	 * To Rename Folder
	 */
	public function wkmbsc_rename_folder( WP_REST_Request $request ) {
		$data = $request->get_params();

		$project_id  = isset( $data['project_id'] ) ? intval( $data['project_id'] ) : 0;
		$folder_name = isset( $data['name'] ) ? sanitize_text_field( $data['name'] ) : '';
		$parent      = isset( $data['parent'] ) ? intval( $data['parent'] ) : 0;
		$folder_id   = isset( $data['folderid'] ) ? intval( $data['folderid'] ) : 0;
		// $privacy     = isset( $data['privacy'] ) ? 'yes' : 'no';

		$response['status'] = false;
		if ( isset( $data ) ) {
			if ( ! $this->check_existing_folder_by_parent( $folder_name, $parent, $project_id ) ) {
				$data = array(
					'dir_name' => $data['name'],
				);
				global $wpdb;
				$table = $wpdb->prefix . 'cpm_file_relationship';
				if ( $wpdb->update( $table, $data, array( 'id' => $folder_id ) ) ) {
					$response['message'] = 'success';
					$response['status']  = true;
				}
			} else {
				$response['status'] = false;
				$response['error']  = __( 'Folder name already exist! Please check again.', 'cpm-pro' );
			}
		}

		if ( '' !== $data ) {
			return new WP_REST_Response(
				$response
			);
		} else {
			return new WP_Error(
				'no_data_found',
				'No data Found',
			);
		}

	}

	function check_existing_folder_by_parent( $folder, $parent, $project_id ) {

		global $wpdb;
		$table = $wpdb->prefix . 'cpm_file_relationship';

		$mylink = $wpdb->get_row( "SELECT * FROM $table WHERE dir_name = '$folder' AND  parent_id = $parent AND project_id = $project_id " );

		if ( empty( $mylink ) ) {
			return false;
		} else {
			return true;
		}
	}

	public function wkmbsc_delete_folder( WP_REST_Request $request ) {
		$data      = $request->get_params();
		$folder_id = isset( $data['folderid'] ) ? intval( $data['folderid'] ) : 0;

		$response['success'] = false;

		global $wpdb;
		$table = $wpdb->prefix . 'cpm_file_relationship';
		if ( $wpdb->delete( $table, array( 'id' => $folder_id ) ) ) {
			$response['message'] = 'success';
			$response['status']  = true;
		} else {
			$response['status'] = false;
			$response['error']  = __( 'There is an error while delete, please try again!', 'cpm-pro' );
		}

		if ( '' !== $data ) {
			return new WP_REST_Response(
				$response
			);
		} else {
			return new WP_Error(
				'no_data_found',
				'No data Found',
			);
		}
	}



	/**
	 * To create Doc
	 */
	public function wkmbsc_create_doc( WP_REST_Request $request ) {
		$posted              = $request->get_params();
		$files               = array();
		$project_id          = isset( $posted['project_id'] ) ? intval( $posted['project_id'] ) : 0;
		$title               = isset( $posted['title'] ) ? $posted['title'] : '';
		$content             = isset( $posted['description'] ) ? $posted['description'] : '';
		$privacy             = isset( $posted['private'] ) ? 'yes' : 'no';
		$parent              = isset( $posted['parent'] ) ? intval( $posted['parent'] ) : 0;
		$response['success'] = false;
		global $wpdb;
		$table = $wpdb->prefix . 'cpm_file_relationship';

			$data_doc = array(
				'post_author'  => $posted['user_id'],
				'post_parent'  => $project_id,
				'post_title'   => $title,
				'post_content' => $content,
				'post_type'    => 'cpm_docs',
				'post_status'  => 'publish',
			);

			$doc_id = wp_insert_post( $data_doc );

			if ( $doc_id ) {
				update_post_meta( $doc_id, '_project_uploaded', $project_id );
				update_post_meta( $doc_id, '_doc_type', '_custom_doc' );
				$table      = $wpdb->prefix . 'cpm_file_relationship';
				$created_by = $posted['user_id'];
				$user       = get_user_by( 'id', $created_by );

				$data = array(
					'project_id' => $project_id,
					'parent_id'  => $parent,
					'is_dir'     => '0',
					'private'    => $privacy,
					'post_id'    => $doc_id,
					'type'       => 'doc',
					'created_by' => $created_by,
					'created_at' => date( 'Y-m-d H:i:s' ),
				);

				$wpdb->insert( $table, $data );

				$rid = $wpdb->insert_id;
				if ( isset( $posted['cpm_attachment'] ) ) {
					$files = $posted['cpm_attachment'];

					foreach ( $files as $file_id ) {
						wp_update_post(
							array(
								'ID'          => $file_id,
								'post_parent' => $doc_id,
							)
						);
					}
				}

				$response = array(
					'status'  => true,
					'message' => 'success',
				);

			}

			return new WP_REST_Response( $response );

	}

	/**
	 * Upload File
	 */
	public function wkmbsc_upload( WP_REST_Request $request ) {
		$data = $request->get_params();

		if ( ! function_exists( 'wp_handle_upload' ) ) {
			require_once ABSPATH . 'wp-admin/includes/file.php';
		}

		if ( ! function_exists( 'wp_crop_image' ) ) {
			include ABSPATH . 'wp-admin/includes/image.php';
		}
		if ( $_FILES['cpm_attachment']['error'] > 0 ) {
			return false;
		}

		$upload = array(
			'name'     => $_FILES['cpm_attachment']['name'],
			'type'     => $_FILES['cpm_attachment']['type'],
			'tmp_name' => $_FILES['cpm_attachment']['tmp_name'],
			'error'    => $_FILES['cpm_attachment']['error'],
			'size'     => $_FILES['cpm_attachment']['size'],
		);

		$uploaded_file = wp_handle_upload( $upload, array( 'test_form' => false ) );

		if ( isset( $uploaded_file['file'] ) ) {
			$file_loc  = $uploaded_file['file'];
			$file_name = basename( $_FILES['cpm_attachment']['name'] );
			$file_type = wp_check_filetype( $file_name );

			$attachment = array(
				'post_mime_type' => $file_type['type'],
				'post_title'     => preg_replace( '/\.[^.]+$/', '', basename( $file_name ) ),
				'post_content'   => '',
				'post_status'    => 'inherit',
				'post_author'    => $data['user_id'],
			);

			$attach_id   = wp_insert_attachment( $attachment, $file_loc );
			$attach_data = wp_generate_attachment_metadata( $attach_id, $file_loc );
			$attach_url  = wp_get_attachment_url( $attach_id );

			wp_update_attachment_metadata( $attach_id, $attach_data );

			// do_action( 'cpm_after_upload_file', $attach_id, $attach_data, $post_id );
			return new WP_REST_Response(
				array(
					'success' => true,
					'file_id' => $attach_id,
					'url'     => $attach_url,
				)
			);
		}
		return new WP_REST_Response(
			array(
				'success' => false,
				'error'   => $uploaded_file['error'],
			)
		);

	}


	/**
	 * Upload files in file
	 */
	public function wkmbsc_upload_file( WP_REST_Request $request ) {
		$posted         = $request->get_params();
		$base_image_url = $posted['image_url'];
		$files          = array();
		$project_id     = isset( $posted['project_id'] ) ? intval( $posted['project_id'] ) : 0;
		$privacy        = isset( $posted['privacy'] ) ? 'yes' : 'no';
		$parent         = isset( $posted['parent'] ) ? intval( $posted['parent'] ) : 0;
		$response       = array(
			'status'  => false,
			'message' => 'complete the data',
		);

		global $wpdb;
		$table = $wpdb->prefix . 'cpm_file_relationship';
		if ( isset( $posted['cpm_attachment'] ) ) {
			$files = $posted['cpm_attachment'];

			foreach ( $files as $file_id ) {
				$file_obj   = array();
				$created_by = $posted['user_id'];
				$data       = array(
					'project_id'    => $project_id,
					'parent_id'     => $parent,
					'is_dir'        => '0',
					'type'          => 'attach',
					'private'       => $privacy,
					'attachment_id' => $file_id,
					'created_by'    => $created_by,
					'created_at'    => date( 'Y-m-d H:i:s' ),
				);

				if ( $wpdb->insert( $table, $data ) ) {
					$id = $wpdb->insert_id;

					if ( $id ) {
						$response = array(
							'status'  => true,
							'message' => 'success',
						);
					} else {
						$response = array(
							'status'  => false,
							'message' => 'No data Found',
						);

					}
				}
			}
		}
		return new WP_REST_Response( $response );
	}

	/**
	 * Get File and folder
	 */
	public function wkmbsc_get_files_folder( WP_REST_Request $request ) {
		$base_image_url      = admin_url( 'admin-ajax.php?action=cpm_file_get' );
		$posted              = $request->get_params();
		$project_id          = isset( $posted['project_id'] ) ? intval( $posted['project_id'] ) : 0;
		$parent              = isset( $posted['parent'] ) ? intval( $posted['parent'] ) : 0;
		$response['success'] = true;
		$backto              = 0;
		$comment_obj         = new CPM_Comment();

		global $wpdb;
		$table        = $wpdb->prefix . 'cpm_file_relationship';
		$sql          = "SELECT * FROM $table WHERE project_id = $project_id  AND parent_id = $parent ";
		$files_folder = $wpdb->get_results( $sql );

		if ( false === $files_folder ) {
			$response['success'] = false;
		}
		$response['folder_list'] = null;
		$pro_folder              = array();
		$pro_files               = array();

		foreach ( $files_folder as $ff ) {
			// $is_private = $ff->private;
			// if ( ! cpm_user_can_access( $project_id, 'file_view_private', $is_private ) ) {
			// continue;
			// }
			$del_edit_permit = ( $ff->created_by == $posted['user_id'] ) ? true : false;
			$user            = get_user_by( 'id', $ff->created_by );

			if ( $ff->is_dir == 1 ) {
				$folder = array(
					'id'         => $ff->id,
					'name'       => $ff->dir_name,
					'parent'     => $ff->parent_id,
					'private'    => $ff->private,
					'created_by' => $ff->created_by,
					'permission' => $del_edit_permit,
				);
				array_push( $pro_folder, $folder );
			} else {
				$title         = '';
				$content       = '';
				$doclink       = '';
				$comment_count = 0;
				$attac_data    = array();
				$file_url      = '';
				$thumb_url     = '';
				$class         = '';
				$content_type  = '';
				$post_id       = $ff->post_id;
				if ( $ff->type == 'attach' ) {

					$file     = $comment_obj->get_file( $ff->attachment_id );
					$file_url = wp_get_attachment_url( $file['id'] );
					// $file_url = sprintf( '%s&file_id=%d&project_id=%d', $base_image_url, $file['id'], $project_id );
					// $file_url = apply_filters( 'cpm_attachment_url', $file_url, $project_id, $file['id'] );
					if ( $file['type'] == 'image' ) {
						// $thumb_url = wp_get_attachment_url( $file['id'] );
						$thumb_url = wp_get_attachment_thumb_url( $file['id'] );
						// $thumb_url    = sprintf( '%s&file_id=%d&project_id=%d&type=thumb', $base_image_url, $file['id'], $project_id );
						// $thumb_url    = apply_filters( 'cpm_attachment_url_thum', $thumb_url, $project_id, $file['id'] );
						$class        = 'cpm-colorbox-img';
						$content_type = 'image';
					} else {
						$thumb_url    = $file['thumb'];
						$class        = '';
						$content_type = 'file';
					}
					$title   = $file['name'];
					$post_id = $ff->attachment_id;
				} else {
					$post          = get_post( $post_id );
					$title         = $post->post_title;
					$content       = $post->post_content;
					$doclink       = $post->post_excerpt;
					$comment_count = $post->comment_count;
					$class         = '';
					$attachments   = get_posts(
						array(
							'post_type'      => 'attachment',
							'posts_per_page' => -1,
							'post_parent'    => $ff->post_id,
							'exclude'        => get_post_thumbnail_id(),
						)
					);

					if ( $attachments ) {
						foreach ( $attachments as $attachment ) {
							$attach       = $comment_obj->get_file( $attachment->ID );
							$attac_data[] = $attach;
						}
					}
				}
				$comments = '';

				$sname = ( strlen( $title ) > $this->_files_name_show ) ? substr( $title, 0, $this->_files_name_show ) . '...' : $title;

				$file_obj = array(
					'id'            => $ff->id,
					'attachment_id' => $ff->attachment_id,
					'parent'        => $ff->parent_id,
					'private'       => $ff->private,
					'thumb'         => $thumb_url,
					'file_url'      => $file_url,
					'css_class'     => $class,
					'name'          => $sname,
					'full_name'     => $title,
					'content'       => $content,
					'content_type'  => $content_type,
					'doclink'       => $doclink,
					'attachment'    => $attac_data,
					'comments'      => $comments,
					'comment_count' => $comment_count,
					'type'          => $ff->type,
					'post_id'       => $post_id,
					'created_by'    => $ff->created_by,
					'created_name'  => $user->display_name,
					'created_at'    => cpm_get_date_without_html( $ff->created_at, true ),
					'permission'    => $del_edit_permit,
				);
				array_push( $pro_files, $file_obj );
			}
		}

		if ( $parent != 0 ) {
			$sqlcf  = " SELECT * FROM $table WHERE id = $parent  ";
			$cfinfo = $wpdb->get_row( $sqlcf );
			$backto = intval( $cfinfo->parent_id );
		}
		if ( $parent == 0 ) {
			$org_doc = $this->get_attach_other_doc( $project_id, 0 );
			if ( $org_doc ) {
				$pro_files = array_merge( $pro_files, $org_doc );
			}
		}
		if ( ! empty( $pro_folder ) ) {
			// Get Current Folder Info
			$response['folder_list'] = $pro_folder;
		}

		if ( ! empty( $pro_files ) ) {
			// Get Current Folder Info
			$response['file_list'] = $pro_files;
		}

		$response['current_folder'] = $parent;
		$response['backto']         = $backto;

		echo json_encode( $response );

		exit();
	}

	/**
	 * Get All Docs
	 */
	function get_attach_other_doc( $project_id, $offset = 0 ) {
		$args = array(
			'post_type'   => 'attachment',
			'meta_key'    => '_project',
			'meta_value'  => $project_id,
			'offset'      => $offset,
			'numberposts' => $this->_files_per_page,
		);

		$attachments    = get_posts( $args );
		$base_image_url = admin_url( 'admin-ajax.php?action=cpm_file_get' );
		$doc_list       = array();

		if ( $attachments ) {
			foreach ( $attachments as $attachment ) {
				$file      = CPM_Comment::getInstance()->get_file( $attachment->ID );
				$topic_url = '#';

				if ( ! $attachment->post_parent ) {
					$parent_id = get_post_meta( $attachment->ID, '_parent', true );
					$parent    = get_post( $parent_id );
				} else {
					$parent = get_post( $attachment->post_parent );
				}
				$post_type_object = get_post_type_object( $parent->post_type );

				if ( 'cpm_task' == $parent->post_type ) {
					$is_private = get_post_meta( $attachment->post_parent, '_task_privacy', true );

					if ( ! cpm_user_can_access_file( $project_id, 'todo_view_private', $is_private ) ) {
						continue;
					}

					$task_list = get_post( $parent->post_parent );
					$topic_url = cpm_url_single_task( $project_id, $task_list->ID, $parent->ID );
				} elseif ( 'cpm_task_list' == $parent->post_type ) {
					$is_private = get_post_meta( $attachment->post_parent, '_tasklist_privacy', true );

					if ( ! cpm_user_can_access_file( $project_id, 'todolist_view_private', $is_private ) ) {
						continue;
					}

					$topic_url = cpm_url_single_tasklist( $project_id, $parent->ID );
				} elseif ( $parent->post_type == 'cpm_message' ) {
					$is_private = get_post_meta( $attachment->post_parent, '_message_privacy', true );

					if ( ! cpm_user_can_access_file( $project_id, 'msg_view_private', $is_private ) ) {
						continue;
					}

					$topic_url = cpm_url_single_message( $project_id, $parent->ID );
				}

				$file_url     = sprintf( '%s&file_id=%d&project_id=%d', $base_image_url, $file['id'], $project_id );
				$content_type = '';
				if ( $file['type'] == 'image' ) {
					$thumb_url    = sprintf( '%s&file_id=%d&project_id=%d&type=thumb', $base_image_url, $file['id'], $project_id );
					$class        = 'cpm-colorbox-img';
					$content_type = 'image';
				} else {
					$thumb_url    = $file['thumb'];
					$class        = '';
					$content_type = 'file';
				}
				$thumb_url = apply_filters( 'cpm_attachment_url_thum', $thumb_url, $project_id, $file['id'] );
				$file_url  = apply_filters( 'cpm_attachment_url', $file_url, $project_id, $file['id'] );

				$sname   = ( strlen( $file['name'] ) > $this->_files_name_show ) ? substr( $file['name'], 0, $this->_files_name_show ) . '...' : $file['name'];
				$doc_obj = array(
					'id'            => '',
					'attachment_id' => '',
					'parent'        => '0',
					'private'       => '',
					'thumb'         => $thumb_url,
					'file_url'      => $file_url,
					'topic_url'     => $topic_url,
					'attach_text'   => __( 'Attached to ', 'cpm-pro' ) . "<a href='{$topic_url}'>{$post_type_object->labels->singular_name}</a> " . __( ' by ', 'cpm-pro' ) . cpm_url_user( $attachment->post_author ),
					'css_class'     => $class,
					'full_name'     => $file['name'],
					'name'          => $sname,
					'content'       => '',
					'content_type'  => $content_type,
					'doclink'       => '',
					'attachment'    => '',
					'comments'      => '',
					'comment_count' => get_comments_number( $parent->ID ),
					'type'          => 'regular_doc_' . $file['type'],
					'post_id'       => $attachment->ID,
					'created_by'    => '',
					'created_name'  => '',
					'created_at'    => '',
					'permission'    => '',
				);
				array_push( $doc_list, $doc_obj );
			}
		}
		return $doc_list;
	}

	/**
	 * Function For link to doc
	 */
	public function wkmbsc_link_to_doc( WP_REST_Request $request ) {

		// check_ajax_referer( 'cpm_nonce' );
		$posted     = $request->get_params();
		$files      = array();
		$project_id = isset( $posted['project_id'] ) ? intval( $posted['project_id'] ) : 0;
		$title      = isset( $posted['title'] ) ? $posted['title'] : '';
		$doc_link   = isset( $posted['doclink'] ) ? $posted['doclink'] : '';
		$content    = isset( $posted['description'] ) ? $posted['description'] : '';
		$privacy    = isset( $posted['private'] ) ? 'yes' : 'no';
		$parent     = isset( $posted['parent'] ) ? intval( $posted['parent'] ) : 0;
		global $wpdb;
		$table = $wpdb->prefix . 'cpm_file_relationship';

		// if ( cpm_user_can_access( $project_id, 'upload_file_doc' ) ) {
			$data_doc = array(
				'post_author'  => $posted['post_author'],
				'post_parent'  => $project_id,
				'post_title'   => $title,
				'post_excerpt' => $doc_link,
				'post_content' => $content,
				'post_type'    => 'cpm_docs',
				'post_status'  => 'publish',
			);

			$doc_id = wp_insert_post( $data_doc );

			if ( $doc_id ) {
				update_post_meta( $doc_id, '_project_uploaded', $project_id );
				update_post_meta( $doc_id, '_doc_type', '_google_doc' );
				$table = $wpdb->prefix . 'cpm_file_relationship';
				$user  = get_user_by( 'id', $posted['post_author'], );

				$data = array(
					'project_id' => $project_id,
					'parent_id'  => $parent,
					'is_dir'     => '0',
					'private'    => $privacy,
					'post_id'    => $doc_id,
					'type'       => 'google_doc',
					'created_by' => $posted['post_author'],
					'created_at' => date( 'Y-m-d H:i:s' ),
				);

				$ins = $wpdb->insert( $table, $data );

				$rid        = $wpdb->insert_id;
				$attac_data = array();

				$sname    = ( strlen( $title ) > 20 ) ? substr( $title, 0, 20 ) . '...' : $title;
				$file_obj = array(
					'id'            => $rid,
					'attachment_id' => '',
					'parent'        => $parent,
					'private'       => $privacy,
					'thumb'         => '',
					'file_url'      => '',
					'css_class'     => '',
					'full_name'     => $title,
					'name'          => $sname,
					'content'       => $content,
					'doclink'       => $doc_link,
					'attachment'    => $attac_data,
					'comment_count' => 0,
					'type'          => 'google_doc',
					'post_id'       => $doc_id,
					'created_by'    => $created_by,
					'created_name'  => $user->display_name,
					'created_at'    => date( 'Y-m-d H:i:s' ),
					'permission'    => true,
				);
				// $response['document'] = $file_obj;

				$response['status']  = true;
				$response['message'] = 'success';

			}
			// }

			if ( '' !== $response ) {
				return new WP_REST_Response(
					$response
				);
			} else {
				return new WP_Error(
					'no_data_found',
					'No data Found',
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
