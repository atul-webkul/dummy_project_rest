<?php

/**
 * @author Webkul
 * @version 1.0.0
 * This file handles all front functions
 */

namespace WkMobikulMultivendorApi\Includes\Front;

use WkMobikulMultivendorApi\Includes\Front\Util;
use WkMobikulMultivendorApi\Factory;
use WkMobikulMultivendorApi\Helper;
use WkMobikulMultivendorApi\Includes\Front\membership;

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'Mkwc_Front_Function_Handler' ) ) {
	/**
	 *
	 */
	class Mkwc_Front_Function_Handler implements Util\Mkwc_Front_Functions_interface {

		public function mkwc_register_routes() {
			/*******Homepage function endpoints*/
			$this->mkwc_homepage_api_endpoints();

			/*******User function endpoints*/
			$this->mkwc_user_api_endpoints();

			/*******Category endpoints*/
			$this->mkwc_category_api_endpoints();

			/*******Product endpoints*/
			$this->mkwc_product_api_endpoints();

			/*******Seller API Endpoints*/
			$this->mkwc_seller_api_endpoints();

			/*******Country API Endpoints*/
			$this->mkwc_countries_api_endpoints();

			/*******Cart API Endpoints*/
			$this->mkwc_cart_api_endpoints();

			/*******Checkout API Endpoints*/
			$this->mkwc_checkout_api_endpoints();

			/*******Media upload API Endpoints*/
			$this->mkwc_media_upload_endpoints();

			/*******External links API Endpoints*/
			$this->mkwc_external_links_endpoints();

			/*******Notifications API Endpoints*/
			$this->mkwc_notifications_endpoints();
		}

		/**
		 * Register homepage endpoints
		 */
		function mkwc_homepage_api_endpoints() {
			register_rest_route(
				MKWC_NAMESPACE,
				Homepage\Mkwc_Homepage::$base,
				array(
					'methods'  => \WP_REST_Server::READABLE,
					'callback' => array( Factory\Mkwc_Factory::mkwc_get_api_object( 'WkMobikulMultivendorApi\Includes\Front\Homepage\Mkwc_Homepage' ), 'mkwc_get_homepage' ),
				)
			);
		}

		/**
		 * Image Upload endpoints
		 */
		function mkwc_media_upload_endpoints() {
			register_rest_route(
				MKWC_NAMESPACE,
				Media\Mkwc_Media_Upload::$base . '/upload',
				array(
					'methods'  => \WP_REST_Server::CREATABLE,
					'callback' => array( Factory\Mkwc_Factory::mkwc_get_api_object( 'WkMobikulMultivendorApi\Includes\Front\Media\Mkwc_Media_Upload' ), 'mkwc_media_upload' ),
				)
			);
		}

		/**
		 * External link endpoints
		 */
		function mkwc_external_links_endpoints() {
			register_rest_route(
				MKWC_NAMESPACE,
				Externallinks\Mkwc_External_Links::$base,
				array(
					'methods'  => \WP_REST_Server::READABLE,
					'callback' => array( Factory\Mkwc_Factory::mkwc_get_api_object( 'WkMobikulMultivendorApi\Includes\Front\Externallinks\Mkwc_External_Links' ), 'mkwc_get_external_links' ),
				)
			);
		}

		/**
		 * Notifications endpoints
		 */
		function mkwc_notifications_endpoints() {
			register_rest_route(
				MKWC_NAMESPACE,
				Notifications\Mkwc_Notifications::$base,
				array(
					'methods'  => \WP_REST_Server::READABLE,
					'callback' => array( Factory\Mkwc_Factory::mkwc_get_api_object( 'WkMobikulMultivendorApi\Includes\Front\Notifications\Mkwc_Notifications' ), 'mkwc_get_notifications' ),
				)
			);
		}

		/**
		 * Register user related endpoints
		 */
		public function mkwc_user_api_endpoints() {
			register_rest_route(
				MKWC_NAMESPACE,
				Users\Mkwc_Users::$base . '/create',
				array(
					'methods'  => \WP_REST_Server::CREATABLE,
					'callback' => array( Factory\Mkwc_Factory::mkwc_get_api_object( 'WkMobikulMultivendorApi\Includes\Front\Users\Mkwc_Users' ), 'mkwc_create_user' ),
				)
			);

			register_rest_route(
				MKWC_NAMESPACE,
				Users\Mkwc_Users::$base . '/checkurl',
				array(
					'methods'  => \WP_REST_Server::READABLE,
					'callback' => array( Factory\Mkwc_Factory::mkwc_get_api_object( 'WkMobikulMultivendorApi\Includes\Front\Users\Mkwc_Users' ), 'mkwc_check_shop_url' ),
				)
			);

			register_rest_route(
				MKWC_NAMESPACE,
				Users\Mkwc_Users::$base . '/(?P<user_id>\d+)',
				array(
					'methods'  => \WP_REST_Server::READABLE,
					'callback' => array( Factory\Mkwc_Factory::mkwc_get_api_object( 'WkMobikulMultivendorApi\Includes\Front\Users\Mkwc_Users' ), 'mkwc_get_user' ),
				)
			);

			register_rest_route(
				MKWC_NAMESPACE,
				Users\Mkwc_Users::$base . '/orders/(?P<user_id>\d+)',
				array(
					'methods'  => \WP_REST_Server::READABLE,
					'callback' => array( Factory\Mkwc_Factory::mkwc_get_api_object( 'WkMobikulMultivendorApi\Includes\Front\Users\Mkwc_Users' ), 'mkwc_get_user_orders' ),
				)
			);

			register_rest_route(
				MKWC_NAMESPACE,
				Users\Mkwc_Users::$base . '/reorder',
				array(
					'methods'  => \WP_REST_Server::CREATABLE,
					'callback' => array( Factory\Mkwc_Factory::mkwc_get_api_object( 'WkMobikulMultivendorApi\Includes\Front\Users\Mkwc_Users' ), 'mkwc_user_reorder' ),
				)
			);

			register_rest_route(
				MKWC_NAMESPACE,
				Users\Mkwc_Users::$base . '/order/(?P<order_id>\d+)',
				array(
					'methods'  => \WP_REST_Server::READABLE,
					'callback' => array( Factory\Mkwc_Factory::mkwc_get_api_object( 'WkMobikulMultivendorApi\Includes\Front\Users\Mkwc_Users' ), 'mkwc_get_user_order' ),
				)
			);

			register_rest_route(
				MKWC_NAMESPACE,
				Users\Mkwc_Users::$base . '/login',
				array(
					'methods'  => \WP_REST_Server::CREATABLE,
					'callback' => array( Factory\Mkwc_Factory::mkwc_get_api_object( 'WkMobikulMultivendorApi\Includes\Front\Users\Mkwc_Users' ), 'mkwc_user_login' ),
				)
			);

			register_rest_route(
				MKWC_NAMESPACE,
				Users\Mkwc_Users::$base . '/social/login',
				array(
					'methods'  => \WP_REST_Server::CREATABLE,
					'callback' => array( Factory\Mkwc_Factory::mkwc_get_api_object( 'WkMobikulMultivendorApi\Includes\Front\Users\Mkwc_Users' ), 'mkwc_user_social_login' ),
				)
			);

			register_rest_route(
				MKWC_NAMESPACE,
				Users\Mkwc_Users::$base . '/logout/(?P<user_id>\d+)',
				array(
					'methods'  => \WP_REST_Server::READABLE,
					'callback' => array( Factory\Mkwc_Factory::mkwc_get_api_object( 'WkMobikulMultivendorApi\Includes\Front\Users\Mkwc_Users' ), 'mkwc_user_logout' ),
				)
			);

			register_rest_route(
				MKWC_NAMESPACE,
				Users\Mkwc_Users::$base . '/(?P<user_id>[a-zA-Z0-9-]+)/address',
				array(
					'methods'  => \WP_REST_Server::CREATABLE,
					'callback' => array( Factory\Mkwc_Factory::mkwc_get_api_object( 'WkMobikulMultivendorApi\Includes\Front\Users\Mkwc_Users' ), 'mkwc_set_user_address' ),
				)
			);

			register_rest_route(
				MKWC_NAMESPACE,
				Users\Mkwc_Users::$base . '/edit-profile',
				array(
					'methods'  => \WP_REST_Server::CREATABLE,
					'callback' => array( Factory\Mkwc_Factory::mkwc_get_api_object( 'WkMobikulMultivendorApi\Includes\Front\Users\Mkwc_Users' ), 'mkwc_set_user_profile_banner' ),
				)
			);

			register_rest_route(
				MKWC_NAMESPACE,
				Users\Mkwc_Users::$base . '/(?P<user_id>\d+)/edit-account',
				array(
					'methods'  => \WP_REST_Server::EDITABLE,
					'callback' => array( Factory\Mkwc_Factory::mkwc_get_api_object( 'WkMobikulMultivendorApi\Includes\Front\Users\Mkwc_Users' ), 'mkwc_edit_user_account' ),
				)
			);

			register_rest_route(
				MKWC_NAMESPACE,
				Users\Mkwc_Users::$base . '/(?P<user_id>\d+)/downloads',
				array(
					'methods'  => \WP_REST_Server::READABLE,
					'callback' => array( Factory\Mkwc_Factory::mkwc_get_api_object( 'WkMobikulMultivendorApi\Includes\Front\Users\Mkwc_Users' ), 'mkwc_user_downloads' ),
				)
			);

			register_rest_route(
				MKWC_NAMESPACE,
				Users\Mkwc_Users::$base . '/forgot-password',
				array(
					'methods'  => \WP_REST_Server::CREATABLE,
					'callback' => array( Factory\Mkwc_Factory::mkwc_get_api_object( 'WkMobikulMultivendorApi\Includes\Front\Users\Mkwc_Users' ), 'mkwc_user_forgot_password' ),
				)
			);

			register_rest_route(
				MKWC_NAMESPACE,
				Users\Mkwc_Users::$base . '/(?P<user_id>\d+)/reviews',
				array(
					'methods'  => \WP_REST_Server::READABLE,
					'callback' => array( Factory\Mkwc_Factory::mkwc_get_api_object( 'WkMobikulMultivendorApi\Includes\Front\Users\Mkwc_Users' ), 'mkwc_user_reviews' ),
				)
			);

			register_rest_route(
				MKWC_NAMESPACE,
				Users\Mkwc_Users::$base . '/delete-user',
				array(
					'methods'  => \WP_REST_Server::CREATABLE,
					'callback' => array( Factory\Mkwc_Factory::mkwc_get_api_object( 'WkMobikulMultivendorApi\Includes\Front\Users\Mkwc_Users' ), 'mkwc_delete_user' ),
				)
			);

			register_rest_route(
				MKWC_NAMESPACE,
				Users\Mkwc_Users::$base . '/(?P<user_id>\d+)/become-seller',
				array(
					'methods'  => \WP_REST_Server::CREATABLE,
					'callback' => array( Factory\Mkwc_Factory::mkwc_get_api_object( 'WkMobikulMultivendorApi\Includes\Front\Users\Mkwc_Users' ), 'mkwc_make_user_as_seller' ),
				)
			);
		}

		/**
		 * Register category api endpoints
		 */
		public function mkwc_category_api_endpoints() {
			register_rest_route(
				MKWC_NAMESPACE,
				Category\Mkwc_Category::$base . '/(?P<category_id>\d+)',
				array(
					'methods'  => \WP_REST_Server::READABLE,
					'callback' => array( Factory\Mkwc_Factory::mkwc_get_api_object( 'WkMobikulMultivendorApi\Includes\Front\Category\Mkwc_Category' ), 'mkwc_get_category' ),
				)
			);

			register_rest_route(
				MKWC_NAMESPACE,
				Category\Mkwc_Category::$base . '/products/(?P<category_id>\d+)',
				array(
					'methods'             => \WP_REST_Server::READABLE,
					'callback'            => array( Factory\Mkwc_Factory::mkwc_get_api_object( 'WkMobikulMultivendorApi\Includes\Front\Category\Mkwc_Category' ), 'mkwc_get_category_products' ),
					'permission_callback' => array( $this, 'mkwc_check_width_product_apis' ),
				)
			);
		}

		/**
		 * Register product API Endpoints
		 */
		public function mkwc_product_api_endpoints() {
			register_rest_route(
				MKWC_NAMESPACE,
				Products\Mkwc_Products::$base,
				array(
					'methods'             => \WP_REST_Server::READABLE,
					'callback'            => array( Factory\Mkwc_Factory::mkwc_get_api_object( 'WkMobikulMultivendorApi\Includes\Front\Products\Mkwc_Products' ), 'mkwc_get_products' ),
					'permission_callback' => array( $this, 'mkwc_check_width_product_apis' ),
				)
			);

			/** get single product */
			register_rest_route(
				MKWC_NAMESPACE,
				Products\Mkwc_Products::$base . '/(?P<product_id>\d+)',
				array(
					'methods'             => \WP_REST_Server::READABLE,
					'callback'            => array( Factory\Mkwc_Factory::mkwc_get_api_object( 'WkMobikulMultivendorApi\Includes\Front\Products\Mkwc_Products' ), 'mkwc_get_product' ),
					'permission_callback' => array( $this, 'mkwc_check_width_product_apis' ),
				)
			);

			register_rest_route(
				MKWC_NAMESPACE,
				Products\Mkwc_Products::$base . '/search',
				array(
					'methods'             => \WP_REST_Server::READABLE,
					'callback'            => array( Factory\Mkwc_Factory::mkwc_get_api_object( 'WkMobikulMultivendorApi\Includes\Front\Products\Mkwc_Products' ), 'mkwc_get_search_product' ),
					'permission_callback' => array( $this, 'mkwc_check_width_product_apis' ),
				)
			);

			register_rest_route(
				MKWC_NAMESPACE,
				Products\Mkwc_Products::$base . '/search/suggestions',
				array(
					'methods'             => \WP_REST_Server::READABLE,
					'callback'            => array( Factory\Mkwc_Factory::mkwc_get_api_object( 'WkMobikulMultivendorApi\Includes\Front\Products\Mkwc_Products' ), 'mkwc_get_search_suggestion_product' ),
					'permission_callback' => array( $this, 'mkwc_check_width_product_apis' ),
				)
			);

			register_rest_route(
				MKWC_NAMESPACE,
				Products\Mkwc_Products::$base . '/(?P<product_id>\d+)/reviews',
				array(
					'methods'  => \WP_REST_Server::READABLE,
					'callback' => array( Factory\Mkwc_Factory::mkwc_get_api_object( 'WkMobikulMultivendorApi\Includes\Front\Products\Mkwc_Products' ), 'mkwc_get_product_reviews' ),
				)
			);

			register_rest_route(
				MKWC_NAMESPACE,
				Products\Mkwc_Products::$base . '/(?P<product_id>\d+)/reviews',
				array(
					'methods'  => \WP_REST_Server::CREATABLE,
					'callback' => array( Factory\Mkwc_Factory::mkwc_get_api_object( 'WkMobikulMultivendorApi\Includes\Front\Products\Mkwc_Products' ), 'mkwc_set_product_review' ),
				)
			);
		}

		/**
		 * Register Seller API Endpoints
		 */
		public function mkwc_seller_api_endpoints() {
			register_rest_route(
				MKWC_NAMESPACE,
				Seller\Mkwc_Dashboard::$base,
				array(
					'methods'             => \WP_REST_Server::READABLE,
					'callback'            => array( Factory\Mkwc_Factory::mkwc_get_api_object( 'WkMobikulMultivendorApi\Includes\Front\Seller\Mkwc_Dashboard' ), 'mkwc_get_dashboard' ),
					'permission_callback' => array( $this, 'mkwc_check_seller_id' ),
				)
			);

			register_rest_route(
				MKWC_NAMESPACE,
				Seller\Mkwc_Reviews::$base . '/(?P<seller_id>\d+)/reviews',
				array(
					'methods'  => \WP_REST_Server::CREATABLE,
					'callback' => array( Factory\Mkwc_Factory::mkwc_get_api_object( 'WkMobikulMultivendorApi\Includes\Front\Seller\Mkwc_Reviews' ), 'mkwc_set_seller_review' ),
				)
			);

			register_rest_route(
				MKWC_NAMESPACE,
				Seller\Mkwc_Transactions::$base,
				array(
					'methods'             => \WP_REST_Server::READABLE,
					'callback'            => array( Factory\Mkwc_Factory::mkwc_get_api_object( 'WkMobikulMultivendorApi\Includes\Front\Seller\Mkwc_Transactions' ), 'mkwc_get_transactions' ),
					'permission_callback' => array( $this, 'mkwc_check_seller_id' ),
				)
			);

			register_rest_route(
				MKWC_NAMESPACE,
				Seller\Mkwc_Transactions::$base . '/(?P<transaction_id>\d+)',
				array(
					'methods'             => \WP_REST_Server::READABLE,
					'callback'            => array( Factory\Mkwc_Factory::mkwc_get_api_object( 'WkMobikulMultivendorApi\Includes\Front\Seller\Mkwc_Transactions' ), 'mkwc_get_transaction_by_id' ),
					'permission_callback' => array( $this, 'mkwc_check_seller_id' ),
				)
			);

			register_rest_route(
				MKWC_NAMESPACE,
				Seller\Mkwc_Product_List::$base . '/list',
				array(
					'methods'             => \WP_REST_Server::READABLE,
					'callback'            => array( Factory\Mkwc_Factory::mkwc_get_api_object( 'WkMobikulMultivendorApi\Includes\Front\Seller\Mkwc_Product_List' ), 'mkwc_get_product_list' ),
					'permission_callback' => array( $this, 'mkwc_check_seller_id' ),
				)
			);

			register_rest_route(
				MKWC_NAMESPACE,
				Seller\Mkwc_Seller_Orders::$base . '/list',
				array(
					'methods'             => \WP_REST_Server::READABLE,
					'callback'            => array( Factory\Mkwc_Factory::mkwc_get_api_object( 'WkMobikulMultivendorApi\Includes\Front\Seller\Mkwc_Seller_Orders' ), 'mkwc_get_seller_orders_list' ),
					'permission_callback' => array( $this, 'mkwc_check_seller_id' ),
				)
			);

			register_rest_route(
				MKWC_NAMESPACE,
				Seller\Mkwc_Seller_Orders::$base . '/(?P<order_id>\d+)',
				array(
					'methods'             => \WP_REST_Server::READABLE,
					'callback'            => array( Factory\Mkwc_Factory::mkwc_get_api_object( 'WkMobikulMultivendorApi\Includes\Front\Seller\Mkwc_Seller_Orders' ), 'mkwc_get_order_by_id' ),
					'permission_callback' => array( $this, 'mkwc_check_seller_id' ),
				)
			);

			register_rest_route(
				MKWC_NAMESPACE,
				Seller\Mkwc_Seller_Orders::$base . '/updateOrder',
				array(
					'methods'  => \WP_REST_Server::CREATABLE,
					'callback' => array( Factory\Mkwc_Factory::mkwc_get_api_object( 'WkMobikulMultivendorApi\Includes\Front\Seller\Mkwc_Seller_Orders' ), 'mkwc_update_order_status' ),
				// 'permission_callback' => array( $this, 'mkwc_check_seller_id' ),
				)
			);

			register_rest_route(
				MKWC_NAMESPACE,
				Seller\Mkwc_Seller_Notifications::$base,
				array(
					'methods'             => \WP_REST_Server::READABLE,
					'callback'            => array( Factory\Mkwc_Factory::mkwc_get_api_object( 'WkMobikulMultivendorApi\Includes\Front\Seller\Mkwc_Seller_Notifications' ), 'mkwc_get_seller_notifications' ),
					'permission_callback' => array( $this, 'mkwc_check_seller_id' ),
				)
			);

			register_rest_route(
				MKWC_NAMESPACE,
				Seller\Mkwc_Seller_Shop_Followers::$base . '/list',
				array(
					'methods'             => \WP_REST_Server::READABLE,
					'callback'            => array( Factory\Mkwc_Factory::mkwc_get_api_object( 'WkMobikulMultivendorApi\Includes\Front\Seller\Mkwc_Seller_Shop_Followers' ), 'mkwc_get_seller_shop_followers' ),
					'permission_callback' => array( $this, 'mkwc_check_seller_id' ),
				)
			);

			register_rest_route(
				MKWC_NAMESPACE,
				Seller\Mkwc_Seller_Ask_Admin::$base . '/list',
				array(
					'methods'             => \WP_REST_Server::READABLE,
					'callback'            => array( Factory\Mkwc_Factory::mkwc_get_api_object( 'WkMobikulMultivendorApi\Includes\Front\Seller\Mkwc_Seller_Ask_Admin' ), 'mkwc_get_seller_asked_queries' ),
					'permission_callback' => array( $this, 'mkwc_check_seller_id' ),
				)
			);

			register_rest_route(
				MKWC_NAMESPACE,
				Seller\Mkwc_Seller_Ask_Admin::$base . '/ask',
				array(
					'methods'             => \WP_REST_Server::CREATABLE,
					'callback'            => array( Factory\Mkwc_Factory::mkwc_get_api_object( 'WkMobikulMultivendorApi\Includes\Front\Seller\Mkwc_Seller_Ask_Admin' ), 'mkwc_seller_send_query' ),
					'permission_callback' => array( $this, 'mkwc_check_seller_id' ),
				)
			);

			register_rest_route(
				MKWC_NAMESPACE,
				Seller\Mkwc_Seller_Shop_Details::$base,
				array(
					'methods'  => \WP_REST_Server::READABLE,
					'callback' => array( Factory\Mkwc_Factory::mkwc_get_api_object( 'WkMobikulMultivendorApi\Includes\Front\Seller\Mkwc_Seller_Shop_Details' ), 'mkwc_get_seller_shop_details' ),
				)
			);

			register_rest_route(
				MKWC_NAMESPACE,
				Seller\Mkwc_Seller_Edit_Profile::$base,
				array(
					'methods'             => \WP_REST_Server::CREATABLE,
					'callback'            => array( Factory\Mkwc_Factory::mkwc_get_api_object( 'WkMobikulMultivendorApi\Includes\Front\Seller\Mkwc_Seller_Edit_Profile' ), 'mkwc_seller_edit_profile' ),
					'permission_callback' => array( $this, 'mkwc_check_seller_id' ),
				)
			);

			register_rest_route(
				MKWC_NAMESPACE,
				Seller\Mkwc_Seller_Edit_Profile::$base . '/get',
				array(
					'methods'             => \WP_REST_Server::READABLE,
					'callback'            => array( Factory\Mkwc_Factory::mkwc_get_api_object( 'WkMobikulMultivendorApi\Includes\Front\Seller\Mkwc_Seller_Edit_Profile' ), 'mkwc_seller_get_edit_profile' ),
					'permission_callback' => array( $this, 'mkwc_check_seller_id' ),
				)
			);

			register_rest_route(
				MKWC_NAMESPACE,
				Seller\Mkwc_Seller_List::$base,
				array(
					'methods'  => \WP_REST_Server::READABLE,
					'callback' => array( Factory\Mkwc_Factory::mkwc_get_api_object( 'WkMobikulMultivendorApi\Includes\Front\Seller\Mkwc_Seller_List' ), 'mkwc_seller_list' ),
				)
			);

			register_rest_route(
				MKWC_NAMESPACE,
				Seller\Mkwc_Seller_Shop_Collection::$base,
				array(
					'methods'             => \WP_REST_Server::READABLE,
					'callback'            => array( Factory\Mkwc_Factory::mkwc_get_api_object( 'WkMobikulMultivendorApi\Includes\Front\Seller\Mkwc_Seller_Shop_Collection' ), 'mkwc_get_seller_shop_collection' ),
					'permission_callback' => array( $this, 'mkwc_check_seller_id' ),
				)
			);

			register_rest_route(
				MKWC_NAMESPACE,
				Seller\Mkwc_Seller_Product::$base . '/add',
				array(
					'methods'             => \WP_REST_Server::READABLE,
					'callback'            => array( Factory\Mkwc_Factory::mkwc_get_api_object( 'WkMobikulMultivendorApi\Includes\Front\Seller\Mkwc_Seller_Product' ), 'mkwc_get_product_prerequisites' ),
					'permission_callback' => array( $this, 'mkwc_check_seller_id' ),
				)
			);

			register_rest_route(
				MKWC_NAMESPACE,
				Seller\Mkwc_Seller_Product::$base . '/add',
				array(
					'methods'  => \WP_REST_Server::CREATABLE,
					'callback' => array( Factory\Mkwc_Factory::mkwc_get_api_object( 'WkMobikulMultivendorApi\Includes\Front\Seller\Mkwc_Seller_Product' ), 'mkwc_add_product' ),
				)
			);

			register_rest_route(
				MKWC_NAMESPACE,
				Seller\Mkwc_Seller_Product::$base . '/edit/(?P<product_id>\d+)',
				array(
					'methods'             => \WP_REST_Server::READABLE,
					'callback'            => array( Factory\Mkwc_Factory::mkwc_get_api_object( 'WkMobikulMultivendorApi\Includes\Front\Seller\Mkwc_Seller_Product' ), 'mkwc_get_product_edit_data' ),
					'permission_callback' => array( $this, 'mkwc_check_seller_id' ),
				)
			);

			register_rest_route(
				MKWC_NAMESPACE,
				Seller\Mkwc_Seller_Product::$base . '/edit/(?P<product_id>\d+)',
				array(
					'methods'             => \WP_REST_Server::CREATABLE,
					'callback'            => array( Factory\Mkwc_Factory::mkwc_get_api_object( 'WkMobikulMultivendorApi\Includes\Front\Seller\Mkwc_Seller_Product' ), 'mkwc_save_product_edit_data' ),
					'permission_callback' => array( $this, 'mkwc_check_seller_id' ),
				)
			);

			register_rest_route(
				MKWC_NAMESPACE,
				Seller\Mkwc_Seller_Product::$base . '/delete',
				array(
					'methods'             => \WP_REST_Server::CREATABLE,
					'callback'            => array( Factory\Mkwc_Factory::mkwc_get_api_object( 'WkMobikulMultivendorApi\Includes\Front\Seller\Mkwc_Seller_Product' ), 'mkwc_delete_seller_product' ),
					'permission_callback' => array( $this, 'mkwc_check_seller_id' ),
				)
			);

			register_rest_route(
				MKWC_NAMESPACE,
				Seller\Mkwc_Seller_Product::$base . '/sku',
				array(
					'methods'  => \WP_REST_Server::CREATABLE,
					'callback' => array( Factory\Mkwc_Factory::mkwc_get_api_object( 'WkMobikulMultivendorApi\Includes\Front\Seller\Mkwc_Seller_Product' ), 'mkwc_check_product_sku' ),
				)
			);

			register_rest_route(
				MKWC_NAMESPACE,
				Seller\Mkwc_Seller_Product::$base . '/search',
				array(
					'methods'             => \WP_REST_Server::READABLE,
					'callback'            => array( Factory\Mkwc_Factory::mkwc_get_api_object( 'WkMobikulMultivendorApi\Includes\Front\Seller\Mkwc_Seller_Product' ), 'mkwc_get_linked_products' ),
					'permission_callback' => array( $this, 'mkwc_check_seller_id' ),
				)
			);
		}


		/**
		 * Country API Endpoints
		 */
		public function mkwc_countries_api_endpoints() {
			register_rest_route(
				MKWC_NAMESPACE,
				Countries\Mkwc_Countries::$base,
				array(
					'methods'  => \WP_REST_Server::READABLE,
					'callback' => array( Factory\Mkwc_Factory::mkwc_get_api_object( 'WkMobikulMultivendorApi\Includes\Front\Countries\Mkwc_Countries' ), 'mkwc_get_countries' ),
				)
			);
		}

		/**
		 * Cart Api Endpoints
		 */
		public function mkwc_cart_api_endpoints() {
			register_rest_route(
				MKWC_NAMESPACE,
				Cart\Mkwc_Cart::$base,
				array(
					'methods'  => \WP_REST_Server::CREATABLE,
					'callback' => array( Factory\Mkwc_Factory::mkwc_get_api_object( 'WkMobikulMultivendorApi\Includes\Front\Cart\Mkwc_Cart' ), 'mkwc_cart' ),
				)
			);

			register_rest_route(
				MKWC_NAMESPACE,
				Cart\Mkwc_Cart_Update::$base,
				array(
					'methods'  => \WP_REST_Server::EDITABLE,
					'callback' => array( Factory\Mkwc_Factory::mkwc_get_api_object( 'WkMobikulMultivendorApi\Includes\Front\Cart\Mkwc_Cart_Update' ), 'mkwc_cart_update' ),
				)
			);

			register_rest_route(
				MKWC_NAMESPACE,
				Cart\Mkwc_Cart_Get::$base,
				array(
					'methods'  => \WP_REST_Server::READABLE,
					'callback' => array( Factory\Mkwc_Factory::mkwc_get_api_object( 'WkMobikulMultivendorApi\Includes\Front\Cart\Mkwc_Cart_Get' ), 'mkwc_cart_get' ),
				)
			);

			register_rest_route(
				MKWC_NAMESPACE,
				Cart\Mkwc_Cart_Get::$base . '/shipping_methods',
				array(
					'methods'  => \WP_REST_Server::READABLE,
					'callback' => array( Factory\Mkwc_Factory::mkwc_get_api_object( 'WkMobikulMultivendorApi\Includes\Front\Cart\Mkwc_Cart_Get' ), 'mkwc_cart_get_shipping_methods' ),
				)
			);

			register_rest_route(
				MKWC_NAMESPACE,
				Cart\Mkwc_Cart_Set_Shipping_Method::$base,
				array(
					'methods'  => \WP_REST_Server::CREATABLE,
					'callback' => array( Factory\Mkwc_Factory::mkwc_get_api_object( 'WkMobikulMultivendorApi\Includes\Front\Cart\Mkwc_Cart_Set_Shipping_Method' ), 'mkwc_cart_set_shipping_method' ),
				)
			);

			register_rest_route(
				MKWC_NAMESPACE,
				Cart\Mkwc_Cart_Set_Payment_Method::$base,
				array(
					'methods'  => \WP_REST_Server::CREATABLE,
					'callback' => array( Factory\Mkwc_Factory::mkwc_get_api_object( 'WkMobikulMultivendorApi\Includes\Front\Cart\Mkwc_Cart_Set_Payment_Method' ), 'mkwc_cart_set_payment_method' ),
				)
			);

			register_rest_route(
				MKWC_NAMESPACE,
				Cart\Mkwc_Cart_Empty::$base,
				array(
					'methods'  => \WP_REST_Server::CREATABLE,
					'callback' => array( Factory\Mkwc_Factory::mkwc_get_api_object( 'WkMobikulMultivendorApi\Includes\Front\Cart\Mkwc_Cart_Empty' ), 'mkwc_cart_empty' ),
				)
			);

			register_rest_route(
				MKWC_NAMESPACE,
				Cart\Mkwc_Cart::$base . '/offline',
				array(
					'methods'  => \WP_REST_Server::CREATABLE,
					'callback' => array( Factory\Mkwc_Factory::mkwc_get_api_object( 'WkMobikulMultivendorApi\Includes\Front\Cart\Mkwc_Cart' ), 'mkwc_cart_offline' ),
				)
			);
		}

		/**
		 * Checkout API Endpoints
		 */
		public function mkwc_checkout_api_endpoints() {
			register_rest_route(
				MKWC_NAMESPACE,
				Checkout\Mkwc_Checkout::$base . '/order',
				array(
					'methods'             => \WP_REST_Server::CREATABLE,
					'callback'            => array( Factory\Mkwc_Factory::mkwc_get_api_object( 'WkMobikulMultivendorApi\Includes\Front\Checkout\Mkwc_Checkout' ), 'mkwc_place_order' ),
					'permission_callback' => array( $this, 'mkwc_check_customer_id' ),
				)
			);

			register_rest_route(
				MKWC_NAMESPACE,
				Checkout\Mkwc_Checkout::$base . '/payment-methods',
				array(
					'methods'  => \WP_REST_Server::READABLE,
					'callback' => array( Factory\Mkwc_Factory::mkwc_get_api_object( 'WkMobikulMultivendorApi\Includes\Front\Checkout\Mkwc_Checkout' ), 'mkwc_payment_methods' ),
				)
			);

			/**
			 *  Membership
			 */

			register_rest_route(
				MKWC_NAMESPACE,
				'/membership',
				array(
					'methods'  => \WP_REST_Server::READABLE,
					'callback' => array( $this, 'wkmp_get_membership_list' ),
				)
			);

			/**
			 *  Membership
			 */

			register_rest_route(
				MKWC_NAMESPACE,
				'/checkout-membership',
				array(
					'methods'  => \WP_REST_Server::READABLE,
					'callback' => array( $this, 'wkmp_get_level_details' ),
				)
			);

			register_rest_route(
				MKWC_NAMESPACE,
				'/custom-design',
				array(
					'methods'  => \WP_REST_Server::CREATABLE,
					'callback' => array( $this, 'wkmp_create_custom_design' ),
				)
			);

			register_rest_route(
				MKWC_NAMESPACE,
				'/service-provider',
				array(
					'methods'  => \WP_REST_Server::READABLE,
					'callback' => array( $this, 'wkmp_service_provider' ),
				)
			);


			register_rest_route(
				MKWC_NAMESPACE,
				'/membership-country',
				array(
					'methods'  => \WP_REST_Server::READABLE,
					'callback' => array( $this, 'wkmp_pro_membership_country' ),
				)
			);

		}

		/**
		 * Get all services provider.
		 *
		 * @param object $param api param.
		 * @return void
		 */
		public function wkmp_service_provider( $param ) {
			$response_handler = new \WP_REST_Response();
			$object           = new membership\Mkwc_User_Membership( $param );
			$result           = $object->wkmp_get_all_services_provider_list( $param );
			$response_handler->set_data( $result );
			return $response_handler;
		}



		/**
		 * Create a custom design according to client.
		 *
		 * @param object $param object data.
		 * @return void
		 */
		public function wkmp_create_custom_design( $param ) {
			$response_handler = new \WP_REST_Response();
			$object           = new membership\Mkwc_User_Membership( $param );
			$result           = $object->wkmp_create_custom_design_according_to_user( $param );
			$response_handler->set_data( $result );
			return $response_handler;
		}

		/** member ship */
		public function wkmp_get_membership_list( $param ) {
			$response_handler = new \WP_REST_Response();
			$object           = new membership\Mkwc_User_Membership( $param );
			$result           = $object->wkmp_get_membership_list( $param );
			$response_handler->set_data( $result );
			return $response_handler;
		}


		public function wkmp_get_level_details( $param ) {
			$response_handler = new \WP_REST_Response();
			$object           = new membership\Mkwc_User_Membership( $param );
			$result           = $object->wkmp_get_level_details( $param );
			$response_handler->set_data( $result );
			return $response_handler;
		}

		public function wkmp_pro_membership_country() {
			global $pmpro_countries;
			$response_handler = new \WP_REST_Response();
			$result = array(
				'status' => true,
				'data'   => $pmpro_countries
			);
			$response_handler->set_data( $result );
			return $response_handler;
		}


		/**
		 * Check product api requests have width param
		 */
		public function mkwc_check_width_product_apis() {
			$width = isset( $_GET['width'] ) ? intval( $_GET['width'] ) : '';
			if ( ! $width ) {
				return new \WP_Error( 'json_incomplete_parameters', __( 'Sorry, you must provide all parameters.', 'mobikul_marketplace' ), array( 'status' => 400 ) );
			} else {
				return true;
			}
		}




		/**
		 * Check seller api requests have seller id
		 */
		public function mkwc_check_seller_id() {
			$id = isset( $_GET['seller_id'] ) ? intval( $_GET['seller_id'] ) : '';

			if ( ! $id ) {
				return new \WP_Error( 'json_incomplete_parameters', __( 'Sorry, you must provide all parameters.', 'mobikul_marketplace' ), array( 'status' => 400 ) );
			} elseif ( $id ) {
				$seller_data_handler = new Helper\Mkwc_Seller_Data();
				$is_seller           = $seller_data_handler->mkwc_check_seller_by_id( $id );

				if ( $is_seller ) {
					return true;
				} else {
					return new \WP_Error( 'rest_invalid_seller_id', __( 'Invalid seller ID.', 'mobikul_marketplace' ), array( 'status' => 404 ) );
				}
			} else {
				return true;
			}
		}


		/**
		 * Check place order api requests have customer id
		 */
		public function mkwc_check_customer_id( $data ) {
			$data = is_object( $data ) ? $data->get_param( 'order' ) : $data;

			$customer_id = isset( $data['customer_id'] ) ? intval( $data['customer_id'] ) : '';

			$guest_id = isset( $data['guest_id'] ) ? $data['guest_id'] : '';

			$id = ! empty( $customer_id ) ? $customer_id : $guest_id;

			if ( ! $id ) {
				return new \WP_Error( 'json_incomplete_parameters', __( 'Sorry, you must provide all parameters.', 'mobikul_marketplace' ), array( 'status' => 400 ) );
			} else {
				return true;
			}
		}
	}
}
