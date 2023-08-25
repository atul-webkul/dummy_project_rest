<?php
/**
 * class For Rest API.
 */

class Wkmbsc_Discussion_Function {

	/**
	 * The single instance of the class.
	 *
	 * @var $instance
	 * @since 1.0.0
	 */
	protected static $instance = null;

	/**
	 * Create Message
	 */
	public function wkmbsc_create_message( WP_REST_Request $request, $files = array() ) {
		$data       = $request->get_params();
		$message_id = $data['message_id'];
		$project_id = $data['project_id'];
		$is_update  = $message_id ? true : false;
		$files      = $data['files'];

		$message_privacy = isset( $data['message_privacy'] ) ? $data['message_privacy'] : 'no';

		$postarr = array(
			'post_author'  => $data['user_id'],
			'post_parent'  => $project_id,
			'post_title'   => $data['message_title'],
			'post_content' => $data['message_detail'],
			'post_type'    => 'cpm_message',
			'post_status'  => 'publish',
		);

		if ( $is_update ) {
			$postarr['ID'] = $message_id;
			$message_id    = wp_update_post( $postarr );
		} else {
			$message_id = wp_insert_post( $postarr );
		}

		if ( $message_id ) {
			$milestone_id = isset( $data['milestone'] ) ? (int) $data['milestone'] : 0;

			update_post_meta( $message_id, '_milestone', $milestone_id );
			update_post_meta( $message_id, '_message_privacy', $message_privacy );

			// if there is any file, update the object reference
			if ( count( $files ) > 0 ) {
				update_post_meta( $message_id, '_files', $files );

				$this->associate_file( $files, $message_id, $project_id );
			}

			if ( $is_update ) {
				CPM_Project::getInstance()->new_project_item( $project_id, $message_id, $message_privacy, 'message', true );

				do_action( 'cpm_message_update', $message_id, $project_id, $postarr );
			} else {
				CPM_Project::getInstance()->new_project_item( $project_id, $message_id, $message_privacy, 'message', false );

				do_action( 'cpm_message_update', $message_id, $project_id, $postarr );

			}
		}
		if ( $message_id ) {
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

	function associate_file( $files, $parent_id, $project_id ) {

		foreach ( $files as $file_id ) {

			// add message id as the parent
			wp_update_post(
				array(
					'ID'          => $file_id,
					'post_parent' => $parent_id,
				)
			);

			// set the _project meta in the file, so that we can find
			// attachments by project id
			update_post_meta( $file_id, '_project', $project_id );
			update_post_meta( $file_id, '_parent', $parent_id );
		}
	}


	public function wkmbsc_delete_message( WP_REST_Request $request, $force = false ) {
		$message_id = $request->get_param( 'message_id' );
		do_action( 'cpm_message_delete', $message_id, $force );

		CPM_Project::getInstance()->delete_project_item( $message_id );

		wp_delete_post( $message_id, $force );

		if ( $message_id ) {
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
	public function wkmbsc_get_discussion( WP_REST_Request $request ) {
		$args = array(
			'post_parent' => $request['task_id'],
			'post_author' => $request['post_author'],
			'post_type'   => 'cpm_message',
		);

		$posts = get_posts( $args );
		foreach ( $posts as $arg ) {
			$data      = get_userdata( $arg->post_author );
			$arg->by   = $data->display_name;
			$arg->file = ! empty( $this->get_attachments( $arg->ID ) ) ? $this->get_attachments( $arg->ID ) : '';
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
			return new WP_Error(
				'no_data_found',
				'No data Found',
			);

		}
	}

	/**
	 * Reply Message
	 */
	public function wkmbsc_reply_message( WP_REST_Request $request ) {
		// check_ajax_referer( 'cpm_nonce' );
		$posted     = $request->get_params();
		$files      = array();
		$text       = trim( $posted['cpm_message'] );
		$parent_id  = isset( $posted['parent_id'] ) ? intval( $posted['parent_id'] ) : 0;
		$project_id = isset( $posted['project_id'] ) ? intval( $posted['project_id'] ) : 0;
		if ( isset( $posted['cpm_attachment'] ) ) {
			$files = $posted['cpm_attachment'];
		}
		$data       = array(
			'comment_post_ID' => $parent_id,
			'comment_content' => $text,
			'user_id'         => $posted['user_id'],
		);
		$comment_id = $this->create( $data, $files, $posted );
		if ( $comment_id ) {
			$comment = $this->get( $comment_id );
		}
		if ( ! empty( $comment ) ) {
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


	function create( $commentdata, $files = array(), $posted ) {
		$user = apply_filters( 'cpm_comment_user', get_userdata( $posted['user_id'] ) );

		$commentdata['comment_author_IP']    = preg_replace( '/[^0-9a-fA-F:., ]/', '', $_SERVER['REMOTE_ADDR'] );
		$commentdata['comment_agent']        = substr( $_SERVER['HTTP_USER_AGENT'], 0, 254 );
		$commentdata['comment_author']       = $user->display_name;
		$commentdata['comment_author_email'] = $user->user_email;

		$comment_id = wp_insert_comment( $commentdata );

		if ( $comment_id ) {
			add_comment_meta( $comment_id, '_files', $files );
			$this->associate_file( $files, $commentdata['comment_post_ID'], $posted['project_id'] );
		}

		do_action( 'cpm_message_update', $comment_id, $posted['project_id'], $commentdata );

		return $comment_id;
	}

	function get( $comment_id ) {

		$comment = get_comment( $comment_id );

		$this->get_comment_meta( $comment_id, $comment );

		return $comment;
	}
	function get_comment_meta( $comment_id, &$comment ) {
		$files_meta = get_comment_meta( $comment_id, '_files', true );
		$files      = array();

		if ( $files_meta != '' ) {
			foreach ( $files_meta as $index => $attachment_id ) {
				$temp = $this->get_file( $attachment_id );

				if ( $temp ) {
					$files[] = $temp;
				} else {
					// delete the file from meta. may be it's deleted
					unset( $files_meta[ $index ] );
					update_comment_meta( $comment_id, '_files', $files_meta );
				}
			}
		}

		$comment->files        = $files;
		$comment->avatar       = get_avatar( $comment->comment_author_email, 96, 'mm' );
		$comment->comment_user = cpm_url_user( $comment->comment_author_email );
		$comment->edit_mode    = false;
	}
	function get_file( $attachment_id ) {
		$file = get_post( $attachment_id );

		if ( $file ) {
			$response = array(
				'id'   => $attachment_id,
				'name' => get_the_title( $attachment_id ),
				'url'  => wp_get_attachment_url( $attachment_id ),
			);

			if ( wp_attachment_is_image( $attachment_id ) ) {

				$thumb             = wp_get_attachment_image_src( $attachment_id, 'thumbnail' );
				$response['thumb'] = $thumb[0];
				$response['type']  = 'image';
			} else {
				switch ( $file->post_mime_type ) {
					case 'application/pdf':
						$response['thumb'] = plugins_url( '../assets/images/icons/adobe-reader-pdf.png', __FILE__ );
						$response['type']  = 'file';
						break;                  case 'application/msword':
						$response['thumb'] = plugins_url( '../assets/images/icons/document.png', __FILE__ );
						$response['type']  = 'file';
													break;                  case 'application/vnd.openxmlformats-officedocument.wordprocessingml.document':
							$response['thumb'] = plugins_url( '../assets/images/icons/document.png', __FILE__ );
							$response['type']  = 'file';
																				break;                  case 'application/vnd.ms-excel':
														$response['thumb'] = plugins_url( '../assets/images/icons/microsoft-excel.png', __FILE__ );
														$response['type']  = 'file';
																											break;                  case 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet':
																					$response['thumb'] = plugins_url( '../assets/images/icons/microsoft-excel.png', __FILE__ );
																					$response['type']  = 'file';
																																		break;                  case 'application/vnd.openxmlformats-officedocument.presentationml.presentation':
																												$response['thumb'] = plugins_url( '../assets/images/icons/Powerpoint 96x128.png', __FILE__ );
																												$response['type']  = 'file';
																																									break;                      case 'application/vnd.ms-powerpoint':
																																			$response['thumb'] = plugins_url( '../assets/images/icons/Powerpoint 96x128.png', __FILE__ );
																																			$response['type']  = 'file';
																																																	break;                  case 'application/zip':
																																										$response['thumb'] = plugins_url( '../assets/images/icons/zip.png', __FILE__ );
																																										$response['type']  = 'file';
																																																								break;                      default:
																																																		$response['thumb'] = wp_mime_type_icon( $file->post_mime_type );
																																																		$response['type']  = 'file';                }
			}

			return $response;
		}

		return false;
	}



	function get_attachments( $post_id ) {
		$att_list = array();

		$args = array(
			'post_type'   => 'attachment',
			'numberposts' => -1,
			'post_status' => null,
			'meta_name'   => '_parent',
			'meta_value'  => $post_id,
			'order'       => 'ASC',
		);

		$attachments = get_posts( $args );

		foreach ( $attachments as $attachment ) {

			$att_list = array(
				'id'   => $attachment->ID,
				'name' => $attachment->post_title,
				'url'  => wp_get_attachment_url( $attachment->ID ),
			);

			if ( wp_attachment_is_image( $attachment->ID ) ) {

				$thumb             = wp_get_attachment_image_src( $attachment->ID, 'thumbnail' );
				$att_list['thumb'] = $thumb[0];
				$att_list['type']  = 'image';
			} else {
				$att_list['thumb'] = wp_mime_type_icon( $attachment->post_mime_type );
				$att_list['type']  = 'file';
			}
		}

		return $att_list;
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
