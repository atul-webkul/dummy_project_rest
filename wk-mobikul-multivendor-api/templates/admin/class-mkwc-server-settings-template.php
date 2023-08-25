<?php

/**
 * @author Webkul
 * @version 1.0.0
 * This file handles server settings template at admin end.
 */

namespace WkMobikulMultivendorApi\Templates\Admin;

use WkMobikulMultivendorApi\Templates\Admin\Util;
use WkMobikulMultivendorApi\Templates\Admin\Banner;
use WkMobikulMultivendorApi\Templates\Admin\Carousels;
use WkMobikulMultivendorApi\Templates\Admin\Product;
use WkMobikulMultivendorApi\Templates\Admin\Notification;
use WkMobikulMultivendorApi\Templates\Admin\Configuration;
use WkMobikulMultivendorApi\Helper;

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'Mkwc_Server_Settings_Template' ) ) {
    /**
     *
     */
    class Mkwc_Server_Settings_Template implements Util\Mkwc_Server_Settings_Template_Interface
    {
        /**
         * Manage banners admin menu
         */
        public function mkwc_manage_app_banners()
        {
            if ( isset( $_GET['action'] ) && ( 'add' == $_GET['action'] || 'edit' == $_GET['action'] ) ) {
                $banner_obj = new Banner\Mkwc_Banner();

                $banner_obj->mkwc_add_banner_form();
            } else {
                $banner_obj = new Banner\Mkwc_Banner_List();

                ?>
                <div class="wrap">

                    <h1 class="wp-heading-inline"><?php echo __( 'App Banners', 'mobikul_marketplace' ); ?></h1>

                    <a href="<?php echo admin_url( 'admin.php?page=mobikul-marketplace&action=add' ) ?>" class="page-title-action"><?php echo __( 'Add Banner', 'mobikul_marketplace' ); ?></a>

                    <p class="description"><?php echo __( 'List of added banners for app homepage.', 'mobikul_marketplace' ); ?></p>

                    <form method="GET">
                        <input type="hidden" name="page" value="<?php echo $_REQUEST['page'] ?>" />
                        <?php

                        $banner_obj->prepare_items();

                        $banner_obj->display();

                        ?>
                    </form>

                </div>
                <?php
            }
        }

        /**
         * Manage carousels admin menu
         */
        public function mkwc_manage_app_carousels()
        {
            if ( isset( $_GET['action'] ) && ( 'add' == $_GET['action'] || 'edit' == $_GET['action'] ) ) {
                $banner_obj = new Carousels\Mkwc_Carousels();

                $banner_obj->mkwc_add_Carousels_form();
            } else {
                $banner_obj = new Carousels\Mkwc_Carousels_List();

                ?>
                <div class="wrap">

                    <h1 class="wp-heading-inline"><?php echo __( 'App Banners', 'mobikul_marketplace' ); ?></h1>

                    <a href="<?php echo admin_url( 'admin.php?page=mobikul-carousels&action=add' ) ?>" class="page-title-action"><?php echo __( 'Add Carousel', 'mobikul_marketplace' ); ?></a>

                    <p class="description"><?php echo __( 'List of added carousels for app homepage.', 'mobikul_marketplace' ); ?></p>

                    <form method="GET">
                        <input type="hidden" name="page" value="<?php echo $_REQUEST['page'] ?>" />
                        <?php

                        $banner_obj->prepare_items();

                        $banner_obj->display();

                        ?>
                    </form>

                </div>
                <?php
            }
        }
        
        /**
         * Manage featured products admin menu
         */
        public function mkwc_manage_app_featured_products()
        {
            if (  isset( $_GET['action'] ) && ( 'add' == $_GET['action'] || 'edit' == $_GET['action'] )  ) {
              // code...
            } else {
                $featured_product_obj = new Product\Mkwc_Featured_Product_List();

                ?>
                <div class="wrap">

                    <h1 class="wp-heading-inline"><?php echo __( 'App Featured Products', 'mobikul_marketplace' ); ?></h1>

                    <a href="<?php echo admin_url( 'edit.php?post_type=product' ) ?>" class="page-title-action"><?php echo __( 'Add Product', 'mobikul_marketplace' ); ?></a>

                    <p class="description"><?php echo __( 'List of products added as featured for app homepage.', 'mobikul_marketplace' ); ?></p>

                    <form method="GET">
                        <?php

                        $featured_product_obj->prepare_items();

                        $featured_product_obj->display();

                        ?>
                    </form>

                </div>
                <?php
            }
        }

        /**
         * Manage featured categories admin menu
         */
        public function mkwc_manage_app_featured_categories()
        {
            if ( isset( $_GET['action'] ) && ( 'add' == $_GET['action'] || 'edit' == $_GET['action'] ) ) {
                $banner_obj = new Category\Mkwc_Category();

                $banner_obj->mkwc_add_category_form();
            } else {
                $banner_obj = new Category\Mkwc_Category_List();

                ?>
                <div class="wrap">

                    <h1 class="wp-heading-inline"><?php echo __( 'App Category', 'mobikul_marketplace' ); ?></h1>

                    <a href="<?php echo admin_url( 'admin.php?page=mobikul-featured-categories&action=add' ) ?>" class="page-title-action"><?php echo __( 'Add Featured Category', 'mobikul_marketplace' ); ?></a>

                    <p class="description"><?php echo __( 'List of added category for app homepage.', 'mobikul_marketplace' ); ?></p>

                    <form method="GET">
                        <input type="hidden" name="page" value="<?php echo $_REQUEST['page'] ?>" />
                        <?php

                        $banner_obj->prepare_items();

                        $banner_obj->display();

                        ?>
                    </form>

                </div>
                <?php
            }
        }

        /**
         * Manage notifications admin menu
         */
        public function mkwc_manage_app_notifications()
        {
            if ( isset( $_GET['action'] ) && ( 'add' == $_GET['action'] || 'edit' == $_GET['action'] ) ) {
                $banner_obj = new Notification\Mkwc_Notification();

                $banner_obj->mkwc_add_notification_form();
            } else {
                $banner_obj = new Notification\Mkwc_notification_List();

                ?>
                <div class="wrap">

                    <h1 class="wp-heading-inline"><?php echo __( 'App Notification', 'mobikul_marketplace' ); ?></h1>

                    <a href="<?php echo admin_url( 'admin.php?page=mobikul-notifications&action=add' ) ?>" class="page-title-action"><?php echo __( 'Add notification', 'mobikul_marketplace' ); ?></a>

                    <p class="description"><?php echo __( 'List of added notifications for app homepage.', 'mobikul_marketplace' ); ?></p>

                    <form method="GET">
                        <input type="hidden" name="page" value="<?php echo $_REQUEST['page'] ?>" />
                        <?php

                        $banner_obj->prepare_items();

                        $banner_obj->display();

                        ?>
                    </form>

                </div>
                <?php
            }
        }

        /**
        * Manage external links admin menu
        */
       public function mkwc_manage_external_links()
       {
           $links_obj = new Externallinks\Mkwc_External_links();

           $links_obj->mkwc_external_links();
       }
       
        /**
         * Manage settings admin menu
         */
        public function mkwc_manage_app_settings()
        {
            $setting_obj = new Configuration\Mkwc_Configuration();

            $setting_obj->mkwc_configuration_form();
        }

        /**
        * Manage banner type
        */
        public function mkwc_select_banner_type()
        {
            global $wpdb;

            $banner_type = isset( $_POST['type'] ) ? $_POST['type'] : '';

            $banner_selected = isset( $_POST['selected'] ) ? $_POST['selected'] : '';

            if( $banner_type == 'product' ){
                $post_table = $wpdb->prefix.'posts';

                $products = $wpdb->get_results("SELECT ID, post_title FROM $post_table WHERE post_status = 'publish' AND post_type = 'product'");
                if( ! empty( $products ) ){
                    foreach ( $products as $key => $value ) {
                      ?>
                        <option value="<?php echo $value->ID; ?>" <?php if ( $banner_selected && $banner_selected == $value->ID ) {
                          echo 'selected="selected"';
                        } ?>>
                            <?php echo $value->post_title ? html_entity_decode( $value->post_title ) : 'No title'; ?>
                        </option>
                      <?php
                    }
                }
            } else if( $banner_type == 'category' ) {
                $product_categories = get_terms( 'product_cat', array( 'hide_empty' => false, 'parent' => 0 ) );

                if( ! empty( $product_categories ) ){
                    foreach( $product_categories as $value ){
                      ?>
                        <option value="<?php echo $value->term_id; ?>" <?php if ( $banner_selected && $banner_selected == $value->term_id ) { echo 'selected="selected"';} ?>>
                            <?php echo $value->name ? html_entity_decode( $value->name ) : 'No title'; ?>
                        </option>
                      <?php
                    }
                }
            } else if( $banner_type == 'image' ){
                echo 'image_only';
            } else {
                ?>
                <option value="">
                    <?php echo __( '-- Select --', 'mobikul_marketplace' ); ?>
                </option>
                <?php
            }
            die();
        }

        /**
        * Manage carousel type
        */
        public function mkwc_select_carousels_type()
        {
            global $wpdb;

            $banner_type = isset( $_POST['type'] ) ? $_POST['type'] : '';

            $banner_selected = isset( $_POST['selected'] ) ? $_POST['selected'] : '';

            if( $banner_type == 'products' ){
                $post_table = $wpdb->prefix.'posts';

                $products = $wpdb->get_results("SELECT ID, post_title FROM $post_table WHERE post_status = 'publish' AND post_type = 'product'");
                if( ! empty( $products ) ){
                    foreach ( $products as $key => $value ) {
                      ?>
                        <option value="<?php echo $value->ID; ?>" <?php if ( $banner_selected && $banner_selected == $value->ID ) {
                          echo 'selected="selected"';
                        } ?>>
                            <?php echo $value->post_title ? html_entity_decode( $value->post_title ) : 'No title'; ?>
                        </option>
                      <?php
                    }
                }
            } else if( $banner_type == 'categories' ) {
                $product_categories = get_terms( 'product_cat', array( 'hide_empty' => false, 'parent' => 0 ) );

                if( ! empty( $product_categories ) ){
                    foreach( $product_categories as $value ){
                      ?>
                        <option value="<?php echo $value->term_id; ?>" <?php if ( $banner_selected && $banner_selected == $value->term_id ) { echo 'selected="selected"';} ?>>
                            <?php echo $value->name ? html_entity_decode( $value->name ) : 'No title'; ?>
                        </option>
                      <?php
                    }
                }
            } else if( $banner_type == 'images' ){
                echo 'images_only';
            } else {
                ?>
                <option value="">
                    <?php echo __( '-- Select --', 'mobikul_marketplace' ); ?>
                </option>
                <?php
            }
            die();
        }
    }
}
