<?php

/**
 * @author Webkul
 * @version 1.0.0
 * This file handles category list template at admin end.
 */

namespace WkMobikulMultivendorApi\Templates\Admin\Category;
use WkMobikulMultivendorApi\Helper;

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'Mkwc_Category_List' ) ) {
    /**
     *
     */
    class Mkwc_Category_List extends \WP_List_Table
    {
        function __construct()
        {
            parent::__construct( array(
                'singular' => 'Category',
                'plural'   => 'Categories',
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
                'category_id'   => __( 'Category ID', 'mobikul_marketplace' ),
                'image'         => __( 'Image', 'mobikul_marketplace' ),
                'sort_order'    => __( 'Sort Order', 'mobikul_marketplace' ),
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
         * @return Arraysort_order
         */
        public function get_sortable_columns()
        {
            return array(
                'sort_order' => array( 'sort_order', false ),

                'status'     => array( 'status', true )
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
                case 'category_id':
                case 'image':
                case 'sort_order':
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
            $orderby = 'sort_order';

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

            $featured_categories = $product_store_handler->mkwc_return_featured_category_data();
            if ( $featured_categories ) {
                $featured_categories = $product_store_handler->mkwc_prepare_admin_featured_categories( $featured_categories );
            } else {
                $featured_categories = [];
            }
            if ( $featured_categories ) {
                foreach ( $featured_categories as $ckey => $cvalue ) {
                    $image_url = $cvalue['image'] ? $cvalue['image'] : wc_placeholder_img_src();;

                    $title = isset( $cvalue['name'] ) ? $cvalue['name'] : '';

                    $sort_order = get_term_meta( $cvalue['id'], 'api_featured_sort_order', true );

          					$status = get_term_meta( $cvalue['id'], 'api_featured_category', true );

                    $data[] = [
                        'category_id' => $cvalue['id'],
                        'image'       => "<img src={$image_url} width=100 height=auto />",
                        'sort_order'  => $sort_order,
                        'status'      => $status,
                    ];
                }
            }
            return $data;
        }

        public function column_category_id( $item )
        {
	    		  $actions = array(
                'edit'     => sprintf( '<a href="admin.php?page=mobikul-featured-categories&action=edit&category_id=%d">' . __( 'Edit', 'mobikul_marketplace' ) . '</a>', $item['category_id'] ),
                'trash'    => sprintf('<a href="admin.php?page=mobikul-featured-categories&action=trash&category_id=%d">' . __( 'Trash', 'mobikul_marketplace' ) . '</a>', $item['category_id'] )
            );

            return sprintf( '%1$s %2$s', $item['category_id'], $this->row_actions( $actions ) );
	  		}

        public function column_cb( $item )
        {
      			return sprintf( '<input type="checkbox" id="category_%d" name="category_id[]" value="%d" />', $item['category_id'], $item['category_id'] );
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
                if ( isset( $_GET['category_id'] ) ) {
                    if ( is_array( $_GET['category_id'] ) ){
                        foreach ( $_GET['category_id'] as $id ) {
                        		if ( ! empty( $id ) ) {
                                delete_term_meta( $id, 'api_featured_sort_order' );

                                delete_term_meta( $id, 'api_featured_category' );
                        		}
                        }
                    		echo '<div class="notice notice-success is-dismissible"><p><strong>';

                        echo __( 'Categories Deleted Successfully', 'mobikul_marketplace' ).'</strong></p>';

                        echo '</div>';
                    } else {
                        if ( ! empty ( $_GET['category_id'] ) ) {
                            delete_term_meta( $_GET['category_id'], 'api_featured_sort_order' );

                            delete_term_meta( $_GET['category_id'], 'api_featured_category' );

                        		echo '<div class="notice notice-success is-dismissible"><p><strong>';

                        		echo __( 'Category Deleted Successfully', 'mobikul_marketplace' ).'</strong></p>';

                            echo '</div>';
                        }
                    }
                }
            }
        }
    }
}
