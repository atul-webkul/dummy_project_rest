<?php

/**
 * @author Webkul
 * @version 1.0.0
 * This file handles category data helper functions.
 */

namespace WkMobikulMultivendorApi\Helper;

use WkMobikulMultivendorApi\Helper;

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'Mkwc_Notification' ) ) {
	/**
	 * Category data class
	 */
	class Mkwc_Notification implements Util\Mkwc_Notification_Interface {

		protected $wpdb;

		function __construct() {
			global $wp, $wpdb;

			$this->wpdb = $wpdb;

			$this->table_name = $this->wpdb->prefix . 'mp_notification';
		}

		/**
		 * Update notification
		 *
		 * @param $notification_object
		 * @return $notification
		 */
		public function mkwc_notification_update( $request, $args ) {

			$id = isset( $args['id'] ) ? $args['id'] : '';

			$image = isset( $args['image'] ) ? $args['image'] : '';

			$title = isset( $args['title'] ) ? $args['title'] : '';

			$content = isset( $args['content'] ) ? $args['content'] : '';

			$type = isset( $args['type'] ) ? $args['type'] : '';

			$term_id = isset( $args['term_id'] ) ? $args['term_id'] : '';

			$status = isset( $args['status'] ) ? $args['status'] : '';

			$args_arr = array(
				'banner'  => $image,
				'title'   => $title,
				'content' => $content,
				'type'    => $type,
				'term_id' => $term_id,
				'status'  => $status,
			);

			$args_arr_id = array(
				'id' => $id,
			);

			if ( $request == 'update' ) {
				$check_val = $this->wpdb->update(
					$this->table_name,
					$args_arr,
					$args_arr_id
				);
			} elseif ( $request == 'add' ) {
				$check_val = $this->wpdb->insert(
					$this->table_name,
					$args_arr
				);
			}
		}

		/**
		 * get notification
		 *
		 * @param $notification_id
		 * @return $notifications
		 */
		public function mkwc_get_notification( $id = '' ) {
			$data = array();
			if ( ! empty( $id ) ) {
				$data = $this->wpdb->get_row( "SELECT * FROM $this->table_name WHERE id = '$id'" );
			} else {
				$data = $this->wpdb->get_results( "SELECT * FROM $this->table_name" );
			}
			return $data;
		}

		/**
		 * Trash notification
		 *
		 * @param $notification_id
		 * @return $tash
		 */
		public function mkwc_delete_notification( $id ) {
			$result = $this->wpdb->delete( $this->table_name, array( 'id' => $id ) );

			return $result;
		}

		/**
		 * Send notification
		 *
		 * @param $notification_id
		 * @return $alert
		 */
		public function mkwc_send_user_notification( $id, $bulk = false ) {
						$notification = $this->mkwc_get_notification( $id );

			if ( ! empty( $notification->banner ) ) {
					$banner = wp_get_attachment_url( $notification->banner, 'thumbnail' );
			} else {
					$banner = '';
			}

						$message = array(
							'title'       => $notification->title,
							'description' => $notification->content,
							'body'        => $notification->content,
							'type'        => $notification->type,
							'id_page'     => intval( $notification->term_id ),
							'image'       => $banner,
						);

						// get product name
						if ( $notification->type == 'product' ) {
							if ( get_the_title( $notification->term_id ) ) {
									$message['product_name'] = get_the_title( $notification->term_id );
							} else {
									$message['product_name'] = '';
							}
						}

						// get category name
						if ( $notification->type == 'category' ) {
								$term = get_term_by( 'id', $notification->term_id, 'product_cat', 'ARRAY_A' );
							if ( ! empty( $term ) ) {
									$message['category_name'] = $term['name'];
							} else {
									$message['category_name'] = '';
							}
						}

						if ( empty( $banner ) ) {
								unset( $message['image'] );
						}

						$result = $this->mkwc_send_notification( $message );

						return $result;
		}

		/**
		 * Send notification
		 *
		 * @param $notification_id
		 * @return $alert
		 */
		public function mkwc_send_notification( $message ) {
			$url = 'https://fcm.googleapis.com/fcm/send';

			$topic = 'woocommerce-mp';

			$fields = array(
				'to'   => '/topics/woocommerce-mp',
				'data' => $message,
			);

			$headers = array(
				'Content-Type:application/json',
				'Authorization:key=' . get_option( 'mkwc_api_google_auth_code' ),
			);

			// Open connection
			$ch = curl_init();

			curl_setopt( $ch, CURLOPT_URL, $url );

			curl_setopt( $ch, CURLOPT_POST, true );

			curl_setopt( $ch, CURLOPT_HTTPHEADER, $headers );

			curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
			// Disabling SSL Certificate support temporarly
			curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, false );

			curl_setopt( $ch, CURLOPT_POSTFIELDS, json_encode( $fields ) );
			// Execute post
			$result = curl_exec( $ch );
			if ( $result === false ) {
				die( 'Curl failed: ' . curl_error( $ch ) );
			}

			curl_close( $ch );

			$fields2 = array(
				'to'                => '/topics/' . $topic,
				'data'              => $message,
				'notification'      => $message,
				'time_to_live'      => 30,
				'delay_while_idle'  => true,
				'priority'          => 'high',
				'content_available' => true,
			);

			// Open connection
			$chh = curl_init();

			// Set the url, number of POST vars, POST data
			curl_setopt( $chh, CURLOPT_URL, $url );

			curl_setopt( $chh, CURLOPT_POST, true );

			curl_setopt( $chh, CURLOPT_HTTPHEADER, $headers );

			curl_setopt( $chh, CURLOPT_RETURNTRANSFER, true );

			// Disabling SSL Certificate support temporarly
			curl_setopt( $chh, CURLOPT_SSL_VERIFYPEER, false );

			curl_setopt( $chh, CURLOPT_POSTFIELDS, json_encode( $fields2 ) );
			// Execute post
			$result = curl_exec( $chh );
			if ( $result === false ) {
				die( 'Curl failed: ' . curl_error( $chh ) );
			}
			curl_close( $chh );
			// Close connection
			return $result;
		}
	}
}
