<?php

/**
 * @author Webkul
 * @version 1.0.0
 * This file handles carousels list template at admin end.
 */

namespace WkMobikulMultivendorApi\Templates\Admin\Carousels;
use WkMobikulMultivendorApi\Helper;

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'Mkwc_Carousels_List' ) ) {
    /**
     *
     */
    class Mkwc_Carousels_List extends \WP_List_Table
    {
        function __construct()
        {
            parent::__construct( array(
                'singular' => 'carousels',
                'plural'   => 'carouselss',
                'ajax'     => false
            ) );
        }

        /**
         * Prepare the items for the table to process
         */
        public function prepare_items()
        {
            $columns = $this->get_columns();
            $hidden = $this->get_hidden_columns();
            $this->process_bulk_action();
            $sortable = $this->get_sortable_columns();
            $data = $this->table_data();
            usort( $data, array( &$this, 'sort_data' ) );
            $per_page = get_option( 'posts_per_page' );
            $current_page = $this->get_pagenum();
            $total_items = count( $data );
            $this->set_pagination_args( array(
                'total_items' => $total_items,
                'per_page'    => $per_page
            ) );
            $data = array_slice( $data,( ( $current_page - 1 ) * $per_page ),$per_page );
            $this->_column_headers = array( $columns, $hidden, $sortable );
            $this->items = $data;
        }

        /**
         * @return Array
         */
        public function get_columns()
        {
            $columns = array(
                'cb'         	  => '<input type="checkbox" />',
                'sorticon'      => '<span class="woocommerce-help-tip"></span>',
                'title'         => __( 'Title', 'mobikul_marketplace' ),
                'image'         => __( 'Image', 'mobikul_marketplace' ),
                'type'          => __( 'Type', 'mobikul_marketplace' ),
                'status'        => __( 'Status', 'mobikul_marketplace' ),
            );
            return $columns;
        }

        /**
         * @return Array
         */
        public function get_hidden_columns()
        {
            return array();
        }

        /**
         * Define the sortable columns
         *
         * @return Array
         */
        public function get_sortable_columns()
        {
            return array(
                'title' => array(
                    'title',
                    false,
                )
            );
        }

        /**
         * @param  Array $item        Data
         * @param  String $column_name - Current column name
         * @return Mixed
         */
        public function column_default( $item, $column_name )
        {
            switch( $column_name ) {
                case 'title':
                case 'image':
                case 'type':
                case 'status':
                    return $item[ $column_name ];
                default:
                    return print_r( $item, true ) ;
            }
        }

        /**
         * @return Mixed
         */
        private function sort_data( $a, $b )
        {
            $orderby = 'title';
            $order = 'asc';

            if ( ! empty( $_GET['orderby'] ) ) {
                $orderby = $_GET['orderby'];
            }

            if ( ! empty( $_GET['order'] ) ) {
                $order = $_GET['order'];
            }

            $result = strcmp( $a[$orderby], $b[$orderby] );

            if ( $order === 'asc' ) {
                return $result;
            }
        }

        /**
         * Get table data from database
         */
        public function table_data()
        {
            $data = [];
            $img_tag = $cat_img_tag = $pro_img_tag = $imgs_tag = '';
            $product_store_handler = new Helper\Mkwc_Product_store();

            $carouselss = $product_store_handler->mkwc_return_homepage_carousels();

            if ( $carouselss ) {
                foreach ( $carouselss as $key => $carousels ) {
                    $img_tag = '';
                    $carousel_ids = isset( $carousels->carousel_ids ) ? maybe_unserialize( $carousels->carousel_ids ) : [];
                    if( is_array( $carousel_ids ) ){
                        if( $carousels->type === 'categories' ){
                            foreach ( $carousel_ids as $catkey => $catvalue ) {
                                $cat_thumb_id = get_term_meta( $catvalue, 'thumbnail_id', true );
                                $cat_img = wp_get_attachment_image_src( $cat_thumb_id, 'thumbnail' );
                                if( ! empty( $cat_img ) ){
                                    $cat_img_tag .= '<img src="'.esc_url($cat_img[0]).'"  title="'.get_the_category_by_ID($catvalue).'" width=30 height=30 style="margin:5px;border:1px solid #ccc;"/>';
                                }
                            }
                            $img_tag = $cat_img_tag;
                        } else if( $carousels->type === 'products' ){
                            $pro_img_tag = '';
                            foreach ( $carousel_ids as $prokey => $provalue ) {
                                $pro_img =  wp_get_attachment_image_src( get_post_thumbnail_id( $provalue ), 'thumbnail' );
                                $pro_img_tag .= '<img src="'.esc_url($pro_img[0]).'" title="'.get_the_title($provalue).'" width=30 height=30 style="margin:5px;border:1px solid #ccc;"/>';
                            }
                            $img_tag = $pro_img_tag;
                        } else if( $carousels->type === 'images' ){
                            foreach ( $carousel_ids as $catkey => $catvalue ) {
                                $cat_img =  wp_get_attachment_image_src( $catvalue, 'thumbnail' );
                                if( $cat_img ){
                                    $imgs_tag .= '<img src="'.esc_url($cat_img[0]).'" width=30 height=30 style="margin:5px;border:1px solid #ccc;" />';
                                }
                            }
                            $img_tag = $imgs_tag;
                        }
                    }

                    $data[] = [
                        'id'      => $carousels->id,
                        'title'   => $carousels->title,
                        'image'   => $img_tag ? $img_tag : '-',
                        'type'    => ucfirst( $carousels->type ),
                        'status'  => ucfirst( $carousels->status ),
                    ];
                }
            }

            return $data;
        }

        public function column_title( $item )
        {
                $actions = array(
                'edit'     => sprintf( '<a href="admin.php?page=mobikul-carousels&action=edit&carousels_id=%d">' . __( 'Edit', 'mobikul_marketplace' ) . '</a>', $item['id'] ),
                'trash'    => sprintf('<a href="admin.php?page=mobikul-carousels&action=trash&carousels_id=%d">' . __( 'Trash', 'mobikul_marketplace' ) . '</a>', $item['id'] )
            );

            return sprintf( '%1$s %2$s', $item['title'], $this->row_actions( $actions ) );
	  		}

        public function column_cb( $item )
        {
      			return sprintf( '<input type="checkbox" id="carousels_%d"name="carousels_id[]" value="%d" />', $item['id'], $item['id'] );
  			}

        public function column_sorticon( $value='' )
        {
            return '<span class="mkwc-sort-icon"></span>';
        }

        public function get_bulk_actions()
        {
            $actions = array(
                'trash'    => __( 'Trash', 'mobikul_marketplace' ),
            );

            return $actions;
        }

        public function process_bulk_action()
        {
            if ( 'trash' === $this->current_action() ) {
                if ( isset( $_GET['carousels_id'] ) ) {
                    $product_store_handler = new Helper\Mkwc_Product_store();

                    if ( is_array( $_GET['carousels_id'] ) ){
                        foreach ( $_GET['carousels_id'] as $id ) {
                        		if ( ! empty( $id ) ) {
                                    $mkwc_result = $product_store_handler->mkwc_delete_homepage_carousels( $id );
                        		}
                        }
                        if( $mkwc_result ){
                        		echo '<div class="notice notice-success is-dismissible"><p><strong>';

                            echo __( 'Carousels Deleted Successfully', 'mobikul_marketplace' ).'</strong></p>';

                            echo '</div>';
                        } else {
                        		echo '<div class="notice notice-error is-dismissible"><p><strong>';

                            echo __( 'An error has occurred.', 'mobikul_marketplace' ).'</strong></p>';

                            echo '</div>';
                        }
                    } else {
                        if ( ! empty ( $_GET['carousels_id'] ) ) {
                            $mkwc_result = $product_store_handler->mkwc_delete_homepage_carousels( $_GET['carousels_id'] );

                            if( $mkwc_result ) {
                            		echo '<div class="notice notice-success is-dismissible"><p><strong>';

                            		echo __( 'Carousels Deleted Successfully', 'mobikul_marketplace' ).'</strong></p>';

                                echo '</div>';
                            } else {
                            		echo '<div class="notice notice-error is-dismissible"><p><strong>';

                                echo __( 'An error has occurred.', 'mobikul_marketplace' ).'</strong></p>';

                                echo '</div>';
                            }
                        }
                    }
                }
            }
        }
    }
}
