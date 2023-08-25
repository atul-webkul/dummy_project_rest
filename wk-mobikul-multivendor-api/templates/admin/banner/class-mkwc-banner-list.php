<?php

/**
 * @author Webkul
 * @version 1.0.0
 * This file handles banner list template at admin end.
 */

namespace WkMobikulMultivendorApi\Templates\Admin\Banner;
use WkMobikulMultivendorApi\Helper;

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'Mkwc_Banner_List' ) ) {
    /**
     *
     */
    class Mkwc_Banner_List extends \WP_List_Table
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

            $product_store_handler = new Helper\Mkwc_Product_store();

            $banners = $product_store_handler->mkwc_return_homepage_banners();
            if ( $banners ) {
                foreach ( $banners as $key => $banner ) {
                    $img_url = isset( $banner->image ) ? wp_get_attachment_url( $banner->image ) : wc_placeholder_img_src();

                    $image_url = isset( $img_url ) ? $img_url : '';

                    if ( $banner->banner_type == 'category' ) {
                        $title = get_the_category_by_ID( $banner->term_id );
                    } else if( $banner->banner_type == 'image' ){ 
                        $title = __('Banner', 'mobikul_marketplace' );
                    } else {
                        $title = get_post_field( 'post_title', $banner->term_id );
                    }

                    $data[] = [
                        'id'      => $banner->id,
                        'title'   => $title,
                        'image'   => "<img src={$image_url} width=100 height=auto />",
                        'type'    => ucfirst( $banner->banner_type ),
                        'status'  => ucfirst( $banner->status ),
                    ];
                }
            }

            return $data;
        }

        public function column_title( $item )
        {
	    		  $actions = array(
                'edit'     => sprintf( '<a href="admin.php?page=mobikul-marketplace&action=edit&banner_id=%d">' . __( 'Edit', 'mobikul_marketplace' ) . '</a>', $item['id'] ),
                'trash'    => sprintf('<a href="admin.php?page=mobikul-marketplace&action=trash&banner_id=%d">' . __( 'Trash', 'mobikul_marketplace' ) . '</a>', $item['id'] )
            );

            return sprintf( '%1$s %2$s', $item['title'], $this->row_actions( $actions ) );
	  		}

        public function column_cb( $item )
        {
      			return sprintf( '<input type="checkbox" id="banner_%d"name="banner_id[]" value="%d" />', $item['id'], $item['id'] );
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
                if ( isset( $_GET['banner_id'] ) ) {
                    $product_store_handler = new Helper\Mkwc_Product_store();

                    if ( is_array( $_GET['banner_id'] ) ){
                        foreach ( $_GET['banner_id'] as $id ) {
                        		if ( ! empty( $id ) ) {
                        				$mkwc_result = $product_store_handler->mkwc_delete_homepage_banners( $id );
                        		}
                        }
                        if( $mkwc_result ){
                        		echo '<div class="notice notice-success is-dismissible"><p><strong>';

                            echo __( 'Banners Deleted Successfully', 'mobikul_marketplace' ).'</strong></p>';

                            echo '</div>';
                        } else {
                        		echo '<div class="notice notice-error is-dismissible"><p><strong>';

                            echo __( 'An error has occurred.', 'mobikul_marketplace' ).'</strong></p>';

                            echo '</div>';
                        }
                    } else {
                        if ( ! empty ( $_GET['banner_id'] ) ) {
                            $mkwc_result = $product_store_handler->mkwc_delete_homepage_banners( $_GET['banner_id'] );

                            if( $mkwc_result ) {
                            		echo '<div class="notice notice-success is-dismissible"><p><strong>';

                            		echo __( 'Banner Deleted Successfully', 'mobikul_marketplace' ).'</strong></p>';

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
