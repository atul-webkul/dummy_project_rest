<?php

/**
 * @author Webkul
 * @version 1.0.0
 * This file handles featured products template at admin end.
 */

namespace WkMobikulMultivendorApi\Templates\Admin\Product;
use WkMobikulMultivendorApi\Helper;

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'Mkwc_Featured_Product_List' ) ) {
    /**
     *
     */
    class Mkwc_Featured_Product_List extends \WP_List_Table
    {
        function __construct()
        {
            parent::__construct( array(
                'singular' => 'Banner',
                'plural'   => 'Banners',
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
                'title'         => __( 'Title', 'mobikul_marketplace' ),
                'image'         => __( 'Thumbnail', 'mobikul_marketplace' ),
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

            $product_store_handler = new Helper\Mkwc_Product_store();

            $featured_products = $product_store_handler->mkwc_return_featured_product_data();

            if ( $featured_products ) {
                foreach ( $featured_products as $key => $product ) {
                    $image_url = wp_get_attachment_image_src( get_post_thumbnail_id( $product->get_id() ), 'thumbnail' );

                    $image_url = isset( $image_url[0] ) ? $image_url[0] : wc_placeholder_img_src();

                    $sort = get_post_meta( $product->get_id(), 'product_api_sort_order', true );

                    $featured_status = get_post_meta( $product->get_id(), 'product_api_status', true );

                    $data[] = [
                        'id'        => $product->get_id(),
                        'title'     => get_post_field( 'post_title', $product->get_id() ),
                        'image'     => $image_url ? "<img src={$image_url} width=100 height=auto />" : '',
                        'status'    => ucfirst( $product->get_status() ),
                        'permalink' => $product->get_permalink()
                    ];
                }
            }

            return $data;
        }

        public function column_title( $item )
        {
	    		  $actions = array(
                'edit'     => sprintf( '<a href="post.php?post=%d&action=edit">' . __( 'Edit', 'mobikul_marketplace' ) . '</a>', $item['id'] ),
                'view'    =>  sprintf('<a href="%s" target = "_blank">' . __( 'View', 'mobikul_marketplace' ) . '</a>', $item['permalink'] )
            );

            return sprintf( '%1$s %2$s', $item['title'], $this->row_actions( $actions ) );
	  		}
    }
}
