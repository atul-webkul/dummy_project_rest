<?php
/**
 * Front function handler.
 */

if ( ! class_exists( 'WKMPAPI_Front_Function' ) ) {

	/**
	 * Front function class
	 */
	class WKMPAPI_Front_Function {

		/**
		 * Instance variable
		 *
		 * @var $instance
		 */
		protected static $instance = null;


		/**
		 * To create rma request.
		 */
		public function wkmpapi_create_rma_request( $request ) {
			global $wpdb;
			$data = $request->get_params();

			if ( ! empty( $data['user_id'] ) && ! empty( $data['order_id'] ) && ! empty( $data['order_status'] ) && ! empty( $data['resolution'] ) && ! empty( $data['consignment_num'] ) && ! empty( $data['items'] ) && ! empty( $data['reason'] ) && ! empty( $data['quantity'] ) ) {
				$dir                    = wp_upload_dir();
				$table_name             = $wpdb->prefix . 'mp_rma_requests';
				$customer_id            = sanitize_text_field( $data['user_id'] );
				$order_no               = sanitize_text_field( $data['order_id'] );
				$items_id               = sanitize_text_field( $data['items'] );
				$reason_ids             = sanitize_text_field( $data['reason'] );
				$quantity               = sanitize_text_field( $data['quantity'] );
				$additional_information = sanitize_text_field( $data['additional_information'] );
				$item_data              = array(
					'items'    => array( $items_id => $items_id ),
					'reason'   => array( $items_id => $reason_ids ),
					'quantity' => array( $items_id => $quantity ),
				);

				$order_status      = sanitize_text_field( $data['order_status'] );
				$resolution        = sanitize_text_field( $data['resolution'] );
				$con_num           = sanitize_text_field( $data['consignment_num'] );
				$seller_id         = get_post_field( 'post_author', $items_id );
				$image_paths       = array();
				$image_path_string = 'no image';
				$data              = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}mp_rma_requests where order_no = %s && customer_id =%s && seller_id = %s ", $order_no, $customer_id, $seller_id ), ARRAY_A );
				$status            = true;
				foreach ( $data as $val ) {
					$items = unserialize( $val['items'] );
					if ( $items_id == max( $items['items'] ) ) {
						$status = false;
					}
				}

				if ( $status ) {

					if ( ! function_exists( 'wp_handle_upload' ) ) {
						require_once ABSPATH . 'wp-admin/includes/file.php';
					}

					$upload_overrides = array( 'test_form' => false );
					$error            = '';
					if ( isset( $_FILES ) ) {
						foreach ( $_FILES as $value ) {
							if ( UPLOAD_ERR_OK == $value['error'] && is_uploaded_file( $value['tmp_name'] ) ) {
								if ( $value['size'] > 2097152 ) {
									$error = __( 'Image size is too large [<2 MB].', 'marketplace-rma' );
								} elseif ( mime_content_type( $value['tmp_name'] ) === 'image/jpeg' || 'image/png' === mime_content_type( $value['tmp_name'] ) ) {
									$temp_arr      = wp_handle_upload( $value, $upload_overrides );
									$image_paths[] = str_replace( $dir['baseurl'], '', $temp_arr['url'] );
								} else {
									$error = __( 'Only jpeg/png images are allowed.', 'marketplace-rma' );
								}
							}
						}
					}

					if ( empty( $error ) ) {
						$image_path_string = implode( ';', $image_paths );
						$sql               = $wpdb->insert(
							$table_name,
							array(
								'order_no'        => $order_no,
								'customer_id'     => $customer_id,
								'seller_id'       => $seller_id,
								'items'           => maybe_serialize( $item_data ),
								'images_path'     => $image_path_string,
								'order_status'    => $order_status,
								'resolution'      => $resolution,
								'consignment_num' => $con_num,
								'information'     => $additional_information,
							)
						);

						if ( $sql ) {
							$last_id  = $wpdb->insert_id;
							$response = array(
								'id'      => $last_id,
								'status'  => true,
								'Message' => 'Success.',

							);
						} else {
							$response = array(
								'status'  => false,
								'Message' => esc_html__( 'Please try after some time', 'wkmpapi' ),

							);
						}
					} else {
						$response = array(
							'status'  => false,
							'message' => $error,
						);
					}
				} else {
					$response = array(
						'status'  => false,
						'message' => esc_html__( 'RMA request already created!', 'wkmpapi' ),
					);
				}
			} else {
				$response = array(
					'status'  => false,
					'message' => esc_html__( 'Please provide all params', 'wkmpapi' ),
				);
			}

			return new WP_REST_Response(
				$response
			);

		}

		/**
		 * To Create RMA Reason
		 */
		public function wkmpapi_create_rma_reason( $request ) {
			global $wpdb;
			$data = $request->get_params();
			if ( ! empty( $data['seller_id'] ) && ! empty( $data['wk_rma_reason'] ) && ! empty( $data['wk_rma_status'] ) ) {
				$validate = $this->wkmpapi_validate_user( $data['seller_id'] );
				if ( $validate ) {
					$reason_data = sanitize_text_field( $data['wk_rma_reason'] );
					$status      = $data['wk_rma_status'];
					$table_name  = $wpdb->prefix . 'mp_rma_reasons';
					$wpdb->insert(
						$table_name,
						array(
							'user_id' => $data['seller_id'],
							'reason'  => $reason_data,
							'status'  => $status,
						)
					);

					$last_insert_id = $wpdb->insert_id;
					if ( $last_insert_id ) {
						$response = array(
							'Message'   => esc_html__( 'Reason Added Successfully.', 'wkmpapi' ),
							'reason_id' => $last_insert_id,
							'status'    => true,
						);
					} else {
						$response = array(
							'Message' => esc_html__( 'Some error please try after some time', 'wkmpapi' ),
							'status'  => false,
						);
					}
				} else {
					$response = array(
						'status'  => false,
						'message' => esc_html__( 'Please provide valid user', 'wkmpapi' ),
					);
				}
			} else {
				$response = array(
					'status'  => false,
					'message' => esc_html__( 'All fields required', 'wkmpapi' ),
				);
			}

			return new WP_REST_Response(
				$response
			);
		}

		/**
		 * Validate user roll.
		 *
		 * @param int    $user_id user id.
		 * @param string $roll user roll validate.
		 * @return array
		 */
		public function wkmpapi_validate_user( $user_id, $roll = 'wk_marketplace_seller' ) {
			$status = $this->wkmpapi_user_exist( $user_id );
			if ( ! empty( $status ) ) {
				if ( $this->wkmpapi_user_has_role( $user_id, $roll ) ) {
					return true;
				} else {
					return false;
				}
			} else {
				return false;
			}
		}

		/**
		 * Validate user roll
		 *
		 * @param int    $user_id user id.
		 * @param string $role_name user roll.
		 * @return void
		 */
		public function wkmpapi_user_has_role( $user_id, $role_name ) {
			$user_meta  = get_userdata( $user_id );
			$user_roles = $user_meta->roles;
			return in_array( $role_name, $user_roles );
		}


		/**
		 * Does this user exist?
		 *
		 * @param  int|string|WP_User $user_id User ID or object.
		 * @return bool                        Whether the user exists.
		 */
		function wkmpapi_user_exist( $user_id ) {

			if ( $user_id instanceof WP_User ) {
				$user_id = $user_id->ID;
			}

			return (bool) get_user_by( 'id', $user_id );
		}


		/**
		 * To Get RMA Reason
		 */
		public function wkmpapi_get_rma_reason( WP_REST_Request $request ) {
			global $wpdb;
			$data = $request->get_params();
			// $reason_id = $request->get_param( 'reason_id' );

			if ( ! empty( $data['seller_id'] ) ) {
				$seller_id = $data['seller_id'];
				$where     = "where user_id = '$seller_id' && status = 'enabled' ";
				if ( ! empty( $data['reason_id'] ) ) {
					$reason_id = $data['reason_id'];
					$where    .= " AND id='$reason_id'";
				}
				$sql = $wpdb->get_results( "select reason,id,status from {$wpdb->prefix}mp_rma_reasons " . $where, ARRAY_A );
				if ( $sql ) {
					$response = array(
						'Message' => 'Success.',
						'status'  => true,
						'data'    => $sql,
					);
				} else {
					$response = array(
						'Message' => esc_html__( 'Data not found.', 'wkmpapi' ),
						'status'  => true,
						'data'    => $sql,
					);
				}
			} else {
				$response = array(
					'Message' => esc_html__( 'All fields required.', 'wkmpapi' ),
					'status'  => false,
				);
			}

			return new WP_REST_Response(
				$response
			);

		}

		/**
		 * To update Rma_reason
		 */
		public function wkmpapi_update_rma_reason( WP_REST_Request $request ) {
			global $wpdb;
			$table_name = $wpdb->prefix . 'mp_rma_reasons';
			$data       = $request->get_params();
			$update     = array();
			if ( ! empty( $data['seller_id'] ) && ! empty( $data['reason_id'] ) ) {

				if ( ! empty( $data['wk_rma_reason'] ) && ! empty( $data['wk_rma_status'] ) ) {
					$reason = $data['wk_rma_reason'];
					$status = $data['wk_rma_status'];
					$update = array(
						'reason' => sanitize_text_field( $reason ),
						'status' => sanitize_text_field( $status ),
					);
				} elseif ( empty( $data['wk_rma_reason'] ) && ! empty( $data['wk_rma_status'] ) ) {
					$status = $data['wk_rma_status'];
					$update = array(
						'status' => sanitize_text_field( $status ),
					);
				}

				if ( ! empty( $update ) ) {
					$reason_id = sanitize_text_field( $data['reason_id'] );
					$sql       = $wpdb->update(
						$table_name,
						$update,
						array(
							'id' => $reason_id,
						)
					);

					if ( $sql ) {
						$response = array(
							'Message' => esc_html__( 'Success', 'wkmpapi' ),
							'status'  => true,
						);
					} else {
						$response = array(
							'Message' => esc_html__( 'Some error please after some time', 'wkmpapi' ),
							'status'  => true,
						);
					}
				}
			} else {
				$response = array(
					'Message' => esc_html__( 'All fields required.', 'wkmpapi' ),
					'status'  => false,
				);
			}

			return new WP_REST_Response( $response );
		}

		/**
		 * To Delete Rma Reason
		 */
		public function wkmpapi_delete_rma_reason( WP_REST_Request $request ) {
			global $wpdb;
			$data = $request->get_params();

			if ( ! empty( $data['reason_id'] ) && ! empty( $data['seller_id'] ) ) {
				$sql = $wpdb->delete(
					$wpdb->prefix . 'mp_rma_reasons',
					array(
						'id'      => $data['reason_id'],
						'user_id' => $data['seller_id'],
					)
				);
				if ( $sql ) {
					$response = array(
						'Message' => esc_html__( 'Successfully removed', 'wkmpapi' ),
						'status'  => true,
					);
				} else {
					$response = array(
						'Message' => esc_html__( 'Some error please try after some time', 'wkmpapi' ),
						'status'  => true,
					);
				}
			} else {
				$response = array(
					'Message' => esc_html__( 'All fields required', 'wkmpapi' ),
					'status'  => true,
				);
			}

			return new WP_REST_Response( $response );

		}

		/**
		 * To update RMA status
		 */
		public function wkmpapi_update_rma_status( WP_REST_Request $request ) {

			global $wpdb;
			$table  = $wpdb->prefix . 'mp_rma_requests';
			$status = $request->get_param( 'wk_update_status' );
			$rma_id = $request->get_param( 'rma_id' );
			$sql    = $wpdb->update(
				$table,
				array(
					'rma_status' => $status,
				),
				array(
					'id' => $rma_id,
				)
			);

			if ( $sql ) {
				return new WP_REST_Response(
					array(
						'Message' => 'Success.',
						'status'  => true,
					),
				);
			} else {
				return new WP_Error(
					'no_data_found',
					'No data Foound',
				);
			}
		}

		/**
		 * To Get RMA conversation
		 */
		public function wkmpapi_get_rma_conversation( WP_REST_Request $request ) {
			global $wpdb;
			$rma_id = $request->get_param( 'rma_id' );
			$sql    = $wpdb->get_results( $wpdb->prepare( "select * from {$wpdb->prefix}mp_rma_conversation where rma_id = %d", $rma_id ), ARRAY_A );
			if ( $sql ) {
				return new WP_REST_Response(
					array(
						'Message' => 'Success.',
						'status'  => true,
						'data '   => $sql,
					),
				);
			} else {
				return new WP_Error(
					'no_data_found',
					'No data Foound',
				);
			}
		}

		/**
		 * To Response conversation
		 */
		public function wkmpapi_get_response_rma_conversation( WP_REST_Request $request ) {
			global $wpdb;
			$table_name = $wpdb->prefix . 'mp_rma_conversation';
			$rma_id     = $request->get_param( 'rma_id' );
			$user_id    = $request->get_param( 'user_id' );
			$message    = sanitize_text_field( $request->get_param( 'message' ) );
			$sql        = $wpdb->insert(
				$table_name,
				array(
					'rma_id'  => $rma_id,
					'user_id' => $user_id,
					'message' => $message,
				)
			);
			if ( $sql ) {
				return new WP_REST_Response(
					array(
						'Message' => 'Success.',
						'status'  => true,
						'data '   => $sql,
					),
				);
			} else {
				return new WP_Error(
					'no_data_found',
					'No data Found',
				);
			}

		}

		public function wkmpapi_get_rma_order( WP_REST_Request $request ) {
			$data = $request->get_params();
			if ( ! empty( $data['user_id'] ) ) {
				$args            = array(
					'customer_id' => $data['user_id'],
					'limit'       => -1, // to retrieve _all_ orders by this user
				);
				$user_id         = $data['user_id'];
				$customer_orders = get_posts(
					array(
						'numberposts' => -1,
						'meta_key'    => '_customer_user',
						'orderby'     => 'date',
						'order'       => 'DESC',
						'meta_value'  => $user_id,
						'post_type'   => wc_get_order_types(),
						'post_status' => array_keys( wc_get_order_statuses() ),
					)
				);

				$order_array = array();
				foreach ( $customer_orders as $customer_order ) {
					$order_d       = wc_get_order( $customer_order );
					$order_array[] = array(
						'id'    => $order_d->get_id(),
						'label' => '#' . $order_d->get_id() . ' ' . $order_d->get_date_created()->date_i18n( 'M-d-Y' ),
					);
				}

				if ( ! empty( $order_array ) ) {
					$response = array(
						'status' => true,
						'data'   => $order_array,
					);
				} else {
					$response = array(
						'status'  => true,
						'message' => esc_html__( 'Data not found', 'wkmpapi' ),
					);
				}
			} else {
				$response = array(
					'status'  => false,
					'message' => esc_html__( 'Please provide user id', 'wkmpapi' ),
				);
			}

			return new WP_REST_Response(
				$response
			);

		}


		public function wkmpapi_get_rma_order_details( WP_REST_Request $request ) {
			$data = $request->get_params();
			if ( ! empty( $data['user_id'] ) && ! empty( $data['order_id'] ) ) {
				$order_id = (int) ! empty( $data['order_id'] ) ? $data['order_id'] : 0;
				$order    = wc_get_order( $order_id );
				if ( $order ) {
					$product_details = array();
					$order_status    = $order->get_status();
					foreach ( $order->get_items() as $item ) {
						$product_id   = $item->get_product_id();
						$variation_id = $item->get_variation_id();
						$product_id   = ! empty( $variation_id ) ? $variation_id : $product_id;
						$product_name = $item->get_name();
						$quantity     = $item->get_quantity();

						$product_details[] = array(
							'id'   => $product_id,
							'name' => $product_name,
							'qty'  => $quantity,
						);
					}
					$data = array(
						'id'           => $order_id,
						'order_status' => $order_status,
						'product'      => $product_details,
					);

					$response = array(
						'status' => true,
						'data'   => $data,
					);
				} else {
					$response = array(
						'status'  => false,
						'message' => esc_html__( 'data not found', 'wkmpapi' ),
					);
				}
			} else {
				$response = array(
					'status'  => false,
					'message' => esc_html__( 'Please provide user id', 'wkmpapi' ),
				);
			}

			return new WP_REST_Response(
				$response
			);

		}


		public function wkmpapi_get_create_rma_list( WP_REST_Request $request ) {
			$data = $request->get_params();
			if ( ! empty( $data['user_id'] ) ) {
				global $wpdb;
				$user_id = $data['user_id'];
				$result  = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}mp_rma_requests WHERE customer_id = %s", $user_id ), ARRAY_A );
				if ( ! empty( $result ) ) {
					$rma_data = array();
					foreach ( $result as $val ) {
						$rma_data[] = array(
							'rma_id'     => $val['id'],
							'order_no'   => $val['order_no'],
							'rma_status' => $val['rma_status'],
							'date'       => $val['datetime'],
						);
					}

					$response = array(
						'status' => true,
						'data'   => $rma_data,
					);
				} else {
					$response = array(
						'status'  => true,
						'message' => esc_html__( 'data not found', 'wkmpapi' ),
					);
				}
			} else {
				$response = array(
					'status'  => false,
					'message' => esc_html__( 'Please provide user id', 'wkmpapi' ),
				);
			}

			return new WP_REST_Response(
				$response
			);
		}

		public function wkmpapi_customer_cancel_request( WP_REST_Request $request ) {
			$data = $request->get_params();
			if ( ! empty( $data['user_id'] ) && ! empty( $data['rma_id'] ) ) {
				global $wpdb;
				$user_id   = $data['user_id'];
				$reason_id = $data['rma_id'];
				$status    = $data['status'];
				$data      = $wpdb->get_results( $wpdb->prepare( "select * from {$wpdb->prefix}mp_rma_requests where customer_id =%s and id =%s ", $user_id, $reason_id ), ARRAY_A );
				if ( ! empty( $data ) ) {

					$update = array( 'rma_status' => $status );
					$where  = array(
						'customer_id' => $user_id,
						'id'          => $reason_id,
					);

					$result = $wpdb->update( $wpdb->prefix . 'mp_rma_requests', $update, $where );
					if ( ! empty( $result ) ) {

						$response = array(
							'status'  => true,
							'message' => esc_html__( 'Data update successfully', 'wkmpapi' ),
						);

					} else {

						$response = array(
							'status'  => false,
							'message' => esc_html__( 'Please provide correct details', 'wkmpapi' ),
						);

					}
				} else {
					$response = array(
						'status'  => false,
						'message' => esc_html__( 'Please provide correct details', 'wkmpapi' ),
					);
				}
			} else {
				$response = array(
					'status'  => false,
					'message' => esc_html__( 'Please provide user id', 'wkmpapi' ),
				);
			}

			return new WP_REST_Response(
				$response
			);

		}


		public function wkmpapi_get_request_rma_details( WP_REST_Request $request ) {
			$data = $request->get_params();
			if ( ! empty( $data['user_id'] ) && ! empty( $data['rma_id'] ) ) {
				global $wpdb;
				$user_id = $data['user_id'];
				$rma_id  = $data['rma_id'];
				if ( ! empty( $data['type'] ) && 'seller' === $data['type'] ) {
					$data = $wpdb->get_results( $wpdb->prepare( "select * from {$wpdb->prefix}mp_rma_requests where seller_id =%s and id =%s ", $user_id, $rma_id ), ARRAY_A );
				} else {
					$data = $wpdb->get_results( $wpdb->prepare( "select * from {$wpdb->prefix}mp_rma_requests where customer_id =%s and id =%s ", $user_id, $rma_id ), ARRAY_A );
				}

				$product_data = array();
				$image        = array();
				if ( ! empty( $data ) ) {
					$url = site_url() . '/wp-content/uploads';
					foreach ( $data as $val ) {
						$user_id    = $val['customer_id'];
						$user       = get_user_by( 'id', $user_id );
						$image_path = explode( ';', $val['images_path'] );
						if ( ! empty( $image_path ) ) {
							foreach ( $image_path as $image_path ) {
								if ( ! empty( $image_path ) ) {
									$image[] = $url . $image_path;
								}
							}
						}
						$order_id     = $val['order_no'];
						$order        = wc_get_order( $order_id );
						$order_status = $order->get_status();
						$rma_details  = array(
							'order_id'               => $order_id,
							'rma_status'             => $val['rma_status'],
							'order_status'           => $order_status,
							'user_name'              => $user->first_name,
							'resolution_type'        => $val['resolution'],
							'additional_information' => $val['information'],
						);

						$product_details = unserialize( $val['items'] );
						foreach ( $product_details['items'] as $product_id ) {
							$reason         = $this->wkmpapi_get_reason( $product_details['reason'][ $product_id ] );
							$quantity       = $product_details['quantity'][ $product_id ];
							$product        = wc_get_product( $product_id );
							$title          = $product->get_title();
							$product_data[] = array(
								'product_title' => $title,
								'reason'        => $reason,
								'qty'           => $quantity,
							);
						}
					}
					$response = array(
						'rma_details'     => $rma_details,
						'product_details' => $product_data,
						'image_path'      => $image,
					);
				} else {
					$response = array(
						'status'  => false,
						'message' => esc_html__( 'Please provide correct details', 'wkmpapi' ),
					);
				}
			} else {
				$response = array(
					'status'  => false,
					'message' => esc_html__( 'Please provide user id', 'wkmpapi' ),
				);
			}

			return new WP_REST_Response(
				$response
			);
		}


		public function wkmpapi_get_rma_policy( WP_REST_Request $request ) {

			$data = get_option( 'mp_rma_policy', true );
			return new WP_REST_Response(
				array(
					'status' => true,
					'data'   => $data,
				)
			);

		}


		public function wkmpapi_get_reason( $reason_id ) {
			global $wpdb;
			$sql = $wpdb->get_row( $wpdb->prepare( "select reason from {$wpdb->prefix}mp_rma_reasons where id = %s ", $reason_id ), ARRAY_A );
			return ! empty( $sql['reason'] ) ? $sql['reason'] : '';
		}

		public function wkmpapi_get_rma_shipping_invoice( WP_REST_Request $request ) {
			$data = $request->get_params();
			if ( ! empty( $data['rma_id'] ) ) {
				$rma_id                       = $data['rma_id'];
				$address                      = get_option( 'mp_rma_address' );
				$result                       = $this->wkmp_rma_reguest_meta_data( $rma_id );
				$dir                          = wp_upload_dir();
				$wk_data                      = $this->wkmp_rma_data( $rma_id );
				$item_data                    = maybe_unserialize( $wk_data[0]->items );
				$label                        = $dir['baseurl'] . $result[0]->meta_value;
				$product_details              = array();
				$product_details['label_url'] = $label;
				$product_details['address']   = $address;
				$product_details['order_id']  = $wk_data[0]->order_no;
				$product_details['rma_id']    = $rma_id;
				foreach ( $item_data['items'] as $value ) :
					$product                              = wc_get_product( $value );
					$product_title                        = $product->get_title();
					$reason_id                            = $item_data['reason'][ $value ];
					$reason                               = $this->wkmpapi_get_reason( $reason_id );
					$qty                                  = $item_data['quantity'][ $value ];
					$product_details['product_details'][] = array(
						'product_title' => $product_title,
						'reason'        => $reason,
						'qty'           => $qty,
					);
				endforeach;

				$response = array(
					'status' => true,
					'data'   => $product_details,
				);
			} else {
				$response = array(
					'status'  => false,
					'message' => esc_html__( 'please provide valid details', 'wkmpapi' ),
				);
			}
			return new WP_REST_Response(
				$response
			);

		}

		/**
		 * Get rma shipping label data in mp_rma_request_meta table.
		 *
		 * @param int $rma_id rma id.
		 * @return object $post retun rma shipping label.
		 */
		public function wkmp_rma_reguest_meta_data( $rma_id ) {
			global $wpdb;
			$post = $wpdb->get_results( $wpdb->prepare( "select meta_value from {$wpdb->prefix}mp_rma_request_meta where meta_key = %s and rma_id = %d", 'shipping_label', $rma_id ) );

			return $post;
		}


		/**
		 * Return rma data.
		 *
		 * @param int $id table id.
		 * @return mixed $mp_data return rema request data.
		 */
		public function wkmp_rma_data( $id ) {
			global $wpdb;
			$mp_data = $wpdb->get_results( $wpdb->prepare( "select * from {$wpdb->prefix}mp_rma_requests where id = %d", $id ) );
			return $mp_data;

		}


		public function wkmpapi_set_rma_shipping_label( WP_REST_Request $request ) {
			$data = $request->get_params();

			if ( ! function_exists( 'wp_handle_upload' ) ) {
				require_once ABSPATH . 'wp-admin/includes/file.php';
			}

			if ( ! function_exists( 'wp_crop_image' ) ) {
				include ABSPATH . 'wp-admin/includes/image.php';
			}

			$user_id = $data['seller_id'];
			$upload  = array(
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
					'post_author'    => $user_id,
				);

				$attach_id   = wp_insert_attachment( $attachment, $file_loc );
				$attach_data = wp_generate_attachment_metadata( $attach_id, $file_loc );
				$attach_url  = wp_get_attachment_url( $attach_id );
				$site_url    = site_url() . '/wp-content/uploads';
				$exp         = explode( $site_url, $attach_url );
				$url         = ! empty( $exp[1] ) ? $exp[1] : '';
				wp_update_attachment_metadata( $attach_id, $attach_data );

				$paths = get_user_meta( $user_id, 'mp_rma_shipping_label_path' );
				if ( ! empty( $paths[0] ) ) {
					// $paths[0] = array( $url );
					$paths = array_merge( $paths[0], array( $url ) );
				}
				update_user_meta( $user_id, 'mp_rma_shipping_label_path', $paths );

				// do_action( 'cpm_after_upload_file', $attach_id, $attach_data, $post_id );
				return array(
					'success' => true,
					'file_id' => $attach_id,
					'url'     => $attach_url,
				);
			}

			return array(
				'success' => false,
				'error'   => $uploaded_file['error'],
			);
		}

		public function wkmpapi_get_all_shipping_label( WP_REST_Request $request ) {
			$data = $request->get_params();
			if ( ! empty( $data['seller_id'] ) ) {
				$seller_id  = $data['seller_id'];
				$site_url   = site_url() . '/wp-content/uploads';
				$paths      = get_user_meta( $seller_id, 'mp_rma_shipping_label_path', true );
				$image_path = array();
				foreach ( $paths as $val ) {
					$image_path[] = $site_url . $val;
				}
				$ras = array(
					'status' => true,
					'data'   => $image_path,
				);
			} else {
				$ras = array(
					'status' => false,
					'label'  => esc_html__( 'Please provide all details', '' ),
				);
			}
			return new WP_REST_Response(
				$ras
			);
		}

		public function wkmpapi_get_seller_manage_rma( WP_REST_Request $request ) {
			$data = $request->get_params();
			if ( ! empty( $data['seller_id'] ) ) {
				global $wpdb;
				$user_id = $data['seller_id'];
				$result  = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}mp_rma_requests WHERE seller_id = %s", $user_id ), ARRAY_A );

				if ( ! empty( $result ) ) {
					$rma_data = array();
					foreach ( $result as $val ) {
						$rma_product = $this->wkmp_get_rma_details( $user_id, $val['id'] );
						$rma_data[]  = array(
							'rma_id'        => $val['id'],
							'order_no'      => $val['order_no'],
							'rma_status'    => $val['rma_status'],
							'date'          => $val['datetime'],
							'customer_name' => ! empty( $rma_product['rma_details']['user_name'] ) ? $rma_product['rma_details']['user_name'] : '',
							'product_name'  => ! empty( $rma_product['product_details'][0]['product_title'] ) ? $rma_product['product_details'][0]['product_title'] : '',
							'reason'        => ! empty( $rma_product['product_details'][0]['qty'] ) ? $rma_product['product_details'][0]['qty'] : '',
						);
					}

					$response = array(
						'status' => true,
						'data'   => $rma_data,
					);
				} else {
					$response = array(
						'status'  => true,
						'message' => esc_html__( 'data not found', 'wkmpapi' ),
					);
				}
			} else {
				$response = array(
					'status'  => false,
					'message' => esc_html__( 'Please provide user id', 'wkmpapi' ),
				);
			}

			return new WP_REST_Response(
				$response
			);
		}

		public function wkmp_get_rma_details( $seller_id, $rma_id ) {
			global $wpdb;

				$data         = $wpdb->get_results( $wpdb->prepare( "select * from {$wpdb->prefix}mp_rma_requests where seller_id =%s and id =%s ", $seller_id, $rma_id ), ARRAY_A );
				$product_data = array();
				$image        = array();

			if ( ! empty( $data ) ) {

				$user = get_user_by( 'id', $data[0]['customer_id'] );
				$url  = site_url() . '/wp-content/uploads';
				foreach ( $data as $val ) {
					$image_path = explode( ';', $val['images_path'] );
					if ( ! empty( $image_path ) ) {
						foreach ( $image_path as $image_path ) {
							$image[] = $url . $image_path;
						}
					}
					$order_id = $val['order_no'];

					$order = wc_get_order( $order_id );
					if ( ! empty( $order ) ) {
						$order_status = $order->get_status();
						$rma_details  = array(
							'order_id'               => $order_id,
							'rma_status'             => $val['rma_status'],
							'order_status'           => $order_status,
							'user_name'              => $user->first_name,
							'resolution_type'        => $val['resolution'],
							'additional_information' => $val['information'],
						);

						$product_details = unserialize( $val['items'] );
						foreach ( $product_details['items'] as $product_id ) {
							$reason         = $this->wkmpapi_get_reason( $product_details['reason'][ $product_id ] );
							$quantity       = $product_details['quantity'][ $product_id ];
							$product        = wc_get_product( $product_id );
							$title          = $product->get_title();
							$product_data[] = array(
								'product_title' => $title,
								'reason'        => $reason,
								'qty'           => $quantity,
							);
						}
					}
				}
					$response = array(
						'rma_details'     => $rma_details,
						'product_details' => $product_data,
						'image_path'      => $image,
					);

					return $response;
			}
		}


		/**
		 * Upload Image
		 */
		public function wkmp_rma_upload_image( WP_REST_Request $request ) {
			if ( ! function_exists( 'wp_handle_upload' ) ) {
				require_once ABSPATH . 'wp-admin/includes/file.php';
			}
			$dir = wp_upload_dir();
			// for multiple file upload.
			$upload_overrides = array( 'test_form' => false );
			$files            = $_FILES['file'];
			$array            = array();
			foreach ( $files['name'] as $key => $value ) {

				$file          = array(
					'name'     => $files['name'][ $key ],
					'type'     => $files['type'][ $key ],
					'tmp_name' => $files['tmp_name'][ $key ],
					'error'    => $files['error'][ $key ],
					'size'     => $files['size'][ $key ],
				);
				$movefile      = wp_handle_upload( $file, $upload_overrides );
				$image_paths[] = str_replace( $dir['baseurl'], '', $movefile['url'] );
				$array[]       = $movefile['url'];
			}

			$responces = array(
				'status' => true,
				'data'   => array(
					'file_location' => $image_paths,
					'image_url'     => $array,
				),
			);
			return new WP_REST_Response(
				$responces
			);
		}




		/**
		 * This is a singleton page, access the single instance just using this method.
		 *
		 * @return object
		 */
		public static function get_instance() {
			if ( ! static::$instance ) {
				static::$instance = new self();
			}

			return static::$instance;
		}
	}

}


