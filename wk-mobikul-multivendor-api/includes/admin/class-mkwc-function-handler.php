<?php

/**
 * @author Webkul
 * @version 1.0.0
 * This file handles all admin end action callbacks.
 */

namespace WkMobikulMultivendorApi\Includes\Admin;

use WkMobikulMultivendorApi\Templates\Admin;
use WkMobikulMultivendorApi\Includes\Admin\Util;

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'Mkwc_Function_Handler' ) ) {
    /**
     *
     */
    class Mkwc_Function_Handler implements Util\Mkwc_Admin_Settings_interface
    {
        /**
         * Add Menu under MP menu
         */
        public function mkwc_add_dashboard_menu()
        {
            $server_template = new Admin\Mkwc_Server_Settings_Template();

            add_menu_page( __( 'Mobikul Marketplace', 'mobikul_marketplace' ), __( 'Mobikul Marketplace', 'mobikul_marketplace' ), 'edit_posts', 'mobikul-marketplace', array( $server_template, 'mkwc_manage_app_banners' ), 'dashicons-store', 55 );

            add_submenu_page( 'mobikul-marketplace', __( 'Mobikul Banner', 'mobikul_marketplace' ), __( 'Banners', 'mobikul_marketplace' ), 'edit_posts', 'mobikul-marketplace', array( $server_template, 'mkwc_manage_app_banners' ) );

            add_submenu_page( 'mobikul-marketplace', __( 'Mobikul Carousels', 'mobikul_marketplace' ), __( 'Carousels', 'mobikul_marketplace' ), 'edit_posts', 'mobikul-carousels', array( $server_template, 'mkwc_manage_app_carousels' ) );

            add_submenu_page( 'mobikul-marketplace', __( 'Mobikul Featured Products', 'mobikul_marketplace' ), __( 'Featured Products', 'mobikul_marketplace' ), 'edit_posts', 'mobikul-featured-products', array( $server_template, 'mkwc_manage_app_featured_products' ) );

            add_submenu_page( 'mobikul-marketplace', __( 'Mobikul Featured Products', 'mobikul_marketplace' ), __( 'Featured Categories', 'mobikul_marketplace' ), 'edit_posts', 'mobikul-featured-categories', array( $server_template, 'mkwc_manage_app_featured_categories' ) );

            add_submenu_page( 'mobikul-marketplace', __( 'Mobikul Notifications', 'mobikul_marketplace' ), __( 'Notifications', 'mobikul_marketplace' ), 'edit_posts', 'mobikul-notifications', array( $server_template, 'mkwc_manage_app_notifications' ) );

            add_submenu_page( 'mobikul-marketplace', __( 'Mobikul External links', 'mobikul_marketplace' ), __( 'External links', 'mobikul_marketplace' ), 'edit_posts', 'mobikul-links', array( $server_template, 'mkwc_manage_external_links' ) );

            add_submenu_page( 'mobikul-marketplace', __( 'Mobikul Settings', 'mobikul_marketplace' ), __( 'Settings', 'mobikul_marketplace' ), 'edit_posts', 'mobikul-settings', array( $server_template, 'mkwc_manage_app_settings' ) );
        }

        /**
         * Enqueue scripts at admin end.
         */
        public function mkwc_admin_script()
        {
            wp_enqueue_media();

            wp_enqueue_style( 'mkwc-admin-style', MKWC_URL . 'assets/css/admin.css' );

            wp_enqueue_script( 'mkwc-admin-js', MKWC_URL . 'assets/js/admin.js' );

            wp_enqueue_script( 'select2-js', plugins_url() . '/woocommerce/assets/js/select2/select2.min.js', array( 'jquery' ) );

            wp_enqueue_style( 'select2', plugins_url() . '/woocommerce/assets/css/select2.css' );

            wp_localize_script( 'mkwc-admin-js', 'mkwcObject', array(
                'mkwcAjaxUrl' 	=> admin_url( 'admin-ajax.php' ),
                'mkwcNonce' 		=> wp_create_nonce( 'mkwc-ajaxnonce' ),
                'placeholder'   => wc_placeholder_img_src( 'thumbnail' ),
                'keywords'	=> [
                    'uploadText'	=> __( 'Upload', 'mobikul_marketplace' ),
                    'banner'      => __( 'Banner', 'mobikul_marketplace' ),
                    'icon'        => __( 'Choose an icon','mobikul_marketplace' )
                ]
            ) );
        }

        /**
         * Register configuration fields as settings
         */
        public function mkwc_register_settings()
        {
            register_setting( 'mkwc_api_setting', 'mkwc_api_google_auth_code' );

            register_setting( 'mkwc_api_setting', 'mkwc_api_user_name' );

            register_setting( 'mkwc_api_setting', 'mkwc_api_user_key' );

            register_setting( 'apilinkssetting', 'mkwc_external_links' );

        }

        /**
         * Add ajax functions.
         */
        public function mkwc_admin_banner_ajax()
        {
            $server_template = new Admin\Mkwc_Server_Settings_Template();

            add_action( 'wp_ajax_nopriv_mkwc_select_banner_type', array( $server_template, 'mkwc_select_banner_type' ) );

            add_action( 'wp_ajax_mkwc_select_banner_type', array( $server_template, 'mkwc_select_banner_type' ) );

            add_action( 'wp_ajax_nopriv_mkwc_select_carousels_type', array( $server_template, 'mkwc_select_carousels_type' ) );

            add_action( 'wp_ajax_mkwc_select_carousels_type', array( $server_template, 'mkwc_select_carousels_type' ) );
        }

        /**
         * Save notification function.
         */
        public function mkwc_save_notification_data( $data )
        {
            new Admin\Notification\Mkwc_Save_Notification_Data( $data );
        }

        /**
         * Save banner function .
         */
        public function mkwc_save_banner_data( $data )
        {
          new Admin\Banner\Mkwc_Save_Banner_Data( $data );
        }

        /**
         * Save carousels function .
         */
        public function mkwc_save_carousels_data( $data )
        {
          new Admin\Carousels\Mkwc_Save_Carousels_Data( $data );
        }

        /**
         * Save category function.
         */
        public function mkwc_save_category_data( $data )
        {
          new Admin\Category\Mkwc_Save_Category_Data( $data );
        }

        public function mkwc_add_category_icon( $taxonomy ){
          ?>
          <div class="form-field term-icon-wrap">
      			<label><?php esc_html_e( 'Icon', 'mobikul_marketplace' ); ?></label>
      			<div id="product_cat_icon" style="float: left; margin-right: 10px;">
              <img src="<?php echo esc_url( wc_placeholder_img_src('thumbnail') ); ?>" width="60px" height="60px">
            </div>
      			<div style="line-height: 60px;">
      				<input type="hidden" id="product_cat_icon_id" name="product_cat_icon_id">
      				<button type="button" class="mkwc_upload_icon button"><?php esc_html_e( 'Upload/Add icon', 'mobikul_marketplace' ); ?></button>
      				<button type="button" class="mkwc_remove_icon button"><?php esc_html_e( 'Remove icon', 'mobikul_marketplace' ); ?></button>
      			</div>
      			<div class="clear"></div>
          </div>

          <div class="form-field term-images-wrap">
      			<label><?php esc_html_e( 'Gallery', 'mobikul_marketplace' ); ?></label>
      			<div id="product_cat_images" style="float: left; margin-right: 10px;">
              <img id="placeholder-img" src="<?php echo esc_url( wc_placeholder_img_src('thumbnail') ); ?>" width="60px" height="60px">
            </div>
      			<div style="line-height: 60px;">
      				<input type="hidden" id="product_cat_images_id" name="product_cat_images_id">
      				<button type="button" class="mkwc_upload_images button"><?php esc_html_e( 'Upload/Add images', 'mobikul_marketplace' ); ?></button>
      			</div>
      			<div class="clear"></div>
      		</div>
          <?php
        }

        public function mkwc_edit_category_icon( $term ){
          $hidden_ids = '';
          $t_id = $term->term_id;
          $image_id = get_term_meta( $t_id, 'product_cat_icon_id', true );
          $image_ids = get_term_meta( $t_id, 'product_cat_images_id', true );
          if( ! empty( $image_ids ) ){
            $image_ids = maybe_unserialize( $image_ids );
            $hidden_ids = $image_ids;
            $image_ids = explode(',', $image_ids);
          } else {
            $image_ids = wc_placeholder_img_src( 'thumbnail' );
          }
          if ( $image_id ) {
            $image_url = wp_get_attachment_image_url( $image_id, 'thumbnail' );
          } else {
            $image_url = wc_placeholder_img_src( 'thumbnail' );
          }
          ?>
          <tr class="form-field term-icon-wrap">
      			<th scope="row" valign="top">
              <label><?php esc_html_e( 'Icon','mobikul_marketplace' ); ?></label>
            </th>
      			<td>
              <div id="product_cat_icon" style="float: left; margin-right: 10px;">
                <img src="<?php echo esc_url( $image_url ); ?>" width="60px" height="60px">
              </div>
      				<div style="line-height: 60px;">
                <input type="hidden" id="product_cat_icon_id" name="product_cat_icon_id" value="<?php echo esc_attr( $image_id ) ? esc_attr( $image_id ) : ''; ?>">
        				<button type="button" class="mkwc_upload_icon button"><?php esc_html_e( 'Upload/Add icon', 'mobikul_marketplace' ); ?></button>
        				<button type="button" class="mkwc_remove_icon button"><?php esc_html_e( 'Remove icon', 'mobikul_marketplace' ); ?></button>
      				</div>
      				<div class="clear"></div>
      			</td>
          </tr>

          <tr class="form-field term-images-wrap">
      			<th scope="row" valign="top">
              <label><?php esc_html_e( 'Gallery','mobikul_marketplace' ); ?></label>
            </th>
      			<td>
              <div id="product_cat_images" style="float: left; margin-right: 10px;">
                <?php
                  if( ! empty( $image_ids ) && is_array( $image_ids ) ){
                    foreach ($image_ids as $imskey => $imsvalue) {
                      $images_url = wp_get_attachment_image_url( $imsvalue, 'thumbnail' );
                      ?>
                      <span class="image_remove">
                        <span class="image_remove_x" data-id="<?php esc_attr_e($imsvalue); ?>">x</span>
                        <img src="<?php echo esc_url( $images_url ); ?>" width="60px" height="60px" data-id="<?php esc_attr_e($imsvalue); ?>">
                      </span>
                      <?php
                    }
                  } else {
                    ?>
                    <img id="placeholder-img" src="<?php echo esc_url( $image_ids ); ?>" width="60px" height="60px">
                    <?php
                  }
                ?>
              </div>
      				<div style="line-height: 60px;">
                <input type="hidden" id="product_cat_images_id" name="product_cat_images_id" value="<?php echo esc_attr( $hidden_ids ) ? esc_attr( $hidden_ids ) : ''; ?>">
        				<button type="button" class="mkwc_upload_images button"><?php esc_html_e( 'Upload/Add images', 'mobikul_marketplace' ); ?></button>
      				</div>
      				<div class="clear"></div>
      			</td>
      		</tr>
          <?php
        }

        function mkwc_save_taxonomy_custom_meta( $term_id ) {
          if( isset( $_POST['product_cat_icon_id'] ) && ! empty( $_POST['product_cat_icon_id'] ) ){
            $image = $_POST['product_cat_icon_id'];

            update_term_meta( $term_id, 'product_cat_icon_id', $image );
          }
          if( isset( $_POST['product_cat_images_id'] ) && ! empty( $_POST['product_cat_images_id'] ) ){
            $images = $_POST['product_cat_images_id'];
            $images = maybe_serialize( $images );

            update_term_meta( $term_id, 'product_cat_images_id', $images );
          }
      }
    }
}
