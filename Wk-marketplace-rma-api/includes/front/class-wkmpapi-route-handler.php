<?php
/**
 * Api route handler.
 */

if ( ! class_exists( 'WKMPAPI_Route_Handler' ) ) {


	class WKMPAPI_Route_Handler {

		/**
		 * Instance variable
		 *
		 * @var $instance
		 */
		protected static $instance = null;

		/**
		 * Add Custom Field
		 */
		public function wkmpapi_create_route() {

			$front_function_handler = WKMPAPI_Front_Function::get_instance();
			// To Create RMA Request
			register_rest_route(
				'mobikul/v1',
				'/add-rma-request/',
				array(
					'methods'             => WP_REST_Server::CREATABLE,
					'callback'            => array( $front_function_handler, 'wkmpapi_create_rma_request' ),
					'permission_callback' => function () {
						return true;
					},
				)
			);

			register_rest_route(
				'mobikul/v1',
				'/get-create-rma-list/',
				array(
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => array( $front_function_handler, 'wkmpapi_get_create_rma_list' ),
					'permission_callback' => function () {
							return true;
					},
				)
			);

			// To Create RMA Reason
			register_rest_route(
				'mobikul/v1',
				'/add-rma-reason/',
				array(
					'methods'             => WP_REST_Server::CREATABLE,
					'callback'            => array( $front_function_handler, 'wkmpapi_create_rma_reason' ),
					'permission_callback' => function () {
							return true;
					},
				)
			);

			// To Get RMA Reason
			register_rest_route(
				'mobikul/v1',
				'/get-rma-reason/',
				array(
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => array( $front_function_handler, 'wkmpapi_get_rma_reason' ),
					'permission_callback' => function () {
							return true;
					},
				)
			);

			// To Update RMA Reason
			register_rest_route(
				'mobikul/v1',
				'/update-rma-reason/',
				array(
					'methods'             => WP_REST_Server::CREATABLE,
					'callback'            => array( $front_function_handler, 'wkmpapi_update_rma_reason' ),
					'permission_callback' => function () {
							return true;
					},
				)
			);

			// To Delete RMA Reason
			register_rest_route(
				'mobikul/v1',
				'/delete-rma-reason/',
				array(
					'methods'             => WP_REST_Server::CREATABLE,
					'callback'            => array( $front_function_handler, 'wkmpapi_delete_rma_reason' ),
					'permission_callback' => function () {
							return true;
					},
				)
			);

			// To Update RMA status
			register_rest_route(
				'mobikul/v1',
				'/update-rma-status/',
				array(
					'methods'             => WP_REST_Server::CREATABLE,
					'callback'            => array( $front_function_handler, 'wkmpapi_update_rma_status' ),
					'permission_callback' => function () {
							return true;
					},
				)
			);

			// To Get RMA Conversation
			register_rest_route(
				'mobikul/v1',
				'/get-rma-conversation/',
				array(
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => array( $front_function_handler, 'wkmpapi_get_rma_conversation' ),
					'permission_callback' => function () {
							return true;
					},
				)
			);

			// To response RMA Conversation
			register_rest_route(
				'mobikul/v1',
				'/response-rma-conversation/',
				array(
					'methods'             => WP_REST_Server::CREATABLE,
					'callback'            => array( $front_function_handler, 'wkmpapi_get_response_rma_conversation' ),
					'permission_callback' => function () {
							return true;
					},
				)
			);

			// To response RMA Conversation
			register_rest_route(
				'mobikul/v1',
				'/rma-order/',
				array(
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => array( $front_function_handler, 'wkmpapi_get_rma_order' ),
					'permission_callback' => function () {
							return true;
					},
				)
			);

			// To response RMA Conversation
			register_rest_route(
				'mobikul/v1',
				'/rma-order-details/',
				array(
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => array( $front_function_handler, 'wkmpapi_get_rma_order_details' ),
					'permission_callback' => function () {
							return true;
					},
				)
			);

			// To response RMA Conversation
			register_rest_route(
				'mobikul/v1',
				'/rma-request-cancel/',
				array(
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => array( $front_function_handler, 'wkmpapi_customer_cancel_request' ),
					'permission_callback' => function () {
							return true;
					},
				)
			);

			// To response RMA Conversation
			register_rest_route(
				'mobikul/v1',
				'/request-rma-details/',
				array(
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => array( $front_function_handler, 'wkmpapi_get_request_rma_details' ),
					'permission_callback' => function () {
							return true;
					},
				)
			);

			// To response RMA Conversation
			register_rest_route(
				'mobikul/v1',
				'/get-rma-policy/',
				array(
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => array( $front_function_handler, 'wkmpapi_get_rma_policy' ),
					'permission_callback' => function () {
							return true;
					},
				)
			);

						// To response RMA Conversation
			register_rest_route(
				'mobikul/v1',
				'/get-rma-shipping-invoice/',
				array(
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => array( $front_function_handler, 'wkmpapi_get_rma_shipping_invoice' ),
					'permission_callback' => function () {
							return true;
					},
				)
			);

			// To response RMA Conversation
			register_rest_route(
				'mobikul/v1',
				'/set-rma-shipping-label/',
				array(
					'methods'             => WP_REST_Server::CREATABLE,
					'callback'            => array( $front_function_handler, 'wkmpapi_set_rma_shipping_label' ),
					'permission_callback' => function () {
							return true;
					},
				)
			);

			register_rest_route(
				'mobikul/v1',
				'/get-all-shipping-label/',
				array(
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => array( $front_function_handler, 'wkmpapi_get_all_shipping_label' ),
					'permission_callback' => function () {
							return true;
					},
				)
			);

			register_rest_route(
				'mobikul/v1',
				'/get-seller-manage-rma/',
				array(
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => array( $front_function_handler, 'wkmpapi_get_seller_manage_rma' ),
					'permission_callback' => function () {
							return true;
					},
				)
			);

			// To Upload Image
			register_rest_route(
				'mobikul/v1',
				'/upload_image/',
				array(
					'methods'             => WP_REST_Server::CREATABLE,
					'callback'            => array( $this, 'wkmp_rma_upload_image' ),
					'permission_callback' => function () {
							return true;
					},
				)
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

