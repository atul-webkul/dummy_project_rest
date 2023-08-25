<?php

/**
 * Mention user handler in a comment on wp project manager plugin
 *
 * @author weDevs
 */
class CPM_Pro_Mention {

	/**
	 * Initialization
	 */
	public function __construct() {
		add_action( 'cpm_comment_new', array( $this, 'mention_user_in_comment' ), 10, 3 );
		add_action( 'cpm_comment_update', array( $this, 'mention_user_in_comment' ), 10, 3 );
	}

	/**
	 * Parse a comment content to collect usernames/login.
	 * 
	 * @param  string $content (This is the content of a wp comment.)
	 * 
	 * @return array $mentioned_users (Each element of mentioned_users 
	 * contains another array that contains user email and name at keys 'email'
	 * and 'name' respectively.)
	 */
	private function mine_login_names( $content ) {
		// return $content;
		$login_names = array();
		$start = strpos( $content, 'data-user=":' );
		$end = strpos( $content, ':"', $start );

		while ( $start && $end ) {
			$str = '';
			for ( $i = $start; $i < $end; $i++ ) { 
				$str .= $content[$i];
			}

			$login_names[] = str_replace( 'data-user=":', '', $str );

			$content = str_replace( $str, '', $content );
			
			$start = strpos( $content, 'data-user=":' );
			$end = strpos( $content, ':">', $start );
		}
		
		return $login_names;	
	}

	/**
	 * Get all email addresses and nicknames associated with users of
	 * the given user login names
	 *
	 * @param array $login_names 
	 * 
	 * @return array $emails
	 */
	private function get_user_details($login_names) {
		
		foreach ( $login_names as $user_login_name ) {
			$user = get_user_by( 'login', $user_login_name );
			
			$user_details[] = array(
				'id'    => $user->ID,
				'name'  =>  $user->display_name,
				'email' => $user->user_email
			);
		}

		return array_unique( $user_details, SORT_REGULAR );
	}

	/**
	 * Send emails to the mentioned users as notification
	 *
	 * @param array  $to (array of email addresses)
	 * @param string $subject (subject of email)
	 * @param string $message (contents of email)
	 * @param int    $comment_post_id 
	 * 
	 * @return void
	 */
	private function send_email( $to, $subject, $message, $comment_post_id ) {
        $blogname     = wp_specialchars_decode( get_option( 'blogname' ), ENT_QUOTES );
        $no_reply     = 'no-reply@' . preg_replace( '#^www\.#', '', strtolower( $_SERVER['SERVER_NAME'] ) );
        $content_type = 'Content-Type: text/html';
        $charset      = 'Charset: UTF-8';
        $from_email   = cpm_get_option( 'email_from', 'cpm_mails', get_option( 'admin_email' ) );
        $from         = "From: $blogname <$from_email>";
        $reply        = apply_filters( 'cpm_reply_to', $to, $comment_post_id );
        $reply_to     = "Reply-To: $no_reply";

        $headers = array(
            $reply_to,
            $content_type,
            $charset,
            $from,
        );

        return wp_mail( $to, $subject, $message, $headers );
	}

	/**
	 * Dispatch cpm_new_comment or cpm_update_comment action
	 * 
	 * @param  int $comment_id 
	 * @param  int $project_id
	 * @param  array $comment_data
	 * 
	 * @return void
	 */
	public function mention_user_in_comment( $comment_id, $project_id, $comment_data ) {
		$comment = get_comment( $comment_id );

        // log in names mentioned in a comment content
		$login_names = $this->mine_login_names( $comment->comment_content );

		// collect user to generate email content and to send emails
		$users = $this->get_user_details( $login_names );

		foreach ( $users as $user ) {
			// generate emails that will be sent to mentioned users
			$email_content = $this->make_email_from_template( $project_id, $comment, $user );

			// send email as notification to the eligible users
			$this->send_email( $user['email'], $email_content['subject'], $email_content['body'], $comment_post_id );
		}
	}

    public function make_email_from_template( $project_id, $comment, $user ) {
    	if ( cpm_get_option( 'email_url_link', 'cpm_mails' ) == 'frontend' ) {
            new CPM_Frontend_URLs();
        }

    	$file_name = 'emails/mention.php';

        $subject = sprintf( __( '[%s][%s] Mentioned on comment: %s', 'cpm-pro' ), 
        	wp_specialchars_decode( get_option( 'blogname' ), ENT_QUOTES ), 
        	get_post_field( 'post_title', $project_id ), 
        	get_post_field( 'post_title', $comment->comment_post_ID )
        );

        // cutoff at 78th character
        if ( cpm_strlen( $subject ) > 78 ) {
            $subject = substr( $subject, 0, 78 ) . '...';
        }

        $parent_post = get_post( $comment->comment_post_ID );
        $post_type = $parent_post->post_type;
        $post_title = $parent_post->post_title;


        ob_start();
        $arg = array(
            'project_id' => $project_id,
            'comment_id' => $comment->comment_ID,
            'comment_post_id' => $comment->comment_post_ID,
            'post_id' => $parent_post->ID,
            'post_type' => $parent_post->post_type,
            'post_title' => $parent_post->post_title,
            'post_parent' => $parent_post->post_parent,
            'user' => $user
        );
        cpm_load_template( $file_name, $arg );
        $body = ob_get_clean();

        return array(
        	'subject' => $subject,
        	'body' => $body,
        );
    }
}
