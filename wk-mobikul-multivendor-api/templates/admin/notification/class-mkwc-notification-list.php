<?php

/**
 * @author Webkul
 * @version 1.0.0
 * This file handles banner list template at admin end.
 */

namespace WkMobikulMultivendorApi\Templates\Admin\Notification;
use WkMobikulMultivendorApi\Helper;

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'Mkwc_Notification_List' ) ) {
    /**
     *
     */
    class Mkwc_Notification_List extends \WP_List_Table
    {
        protected $notification_data;

        function __construct()
        {
            parent::__construct( array(
                'singular' => 'Notification',
                'plural'   => 'Notifications',
                'ajax'     => false
            ) );

            $this->notification_data = new Helper\Mkwc_Notification();
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
                'title'         => __( 'Title', 'mobikul_marketplace' ),
                'banner'        => __( 'Banner', 'mobikul_marketplace' ),
                'content'       => __( 'Content', 'mobikul_marketplace' ),
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
                case 'banner':
                case 'content':
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

            $order   = 'asc';
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

            $notification = $this->notification_data->mkwc_get_notification();
            if ( $notification ) {
                foreach ( $notification as $nkey => $nvalue ) {
                    $id = isset( $nvalue->id ) ? $nvalue->id : '';

                    $title     = isset( $nvalue->title ) ? $nvalue->title : '';

                    $img_url   = isset( $nvalue->banner ) ? wp_get_attachment_url( $nvalue->banner ) : wc_placeholder_img_src();

                    $content   = isset( $nvalue->content ) ? $nvalue->content : '';

                    $type      = isset( $nvalue->type ) ? $nvalue->type : '';

                    $status    = isset( $nvalue->status ) ? $nvalue->status : '';
                    $data[] = [
                        'id'      => $id,
                        'title'   => $title,
                        'banner'  => "<img src={$img_url} width=100 height=auto />",
                        'content' => $content,
                        'type'    => ucfirst( $type ),
                        'status'  => ucfirst( $status )
                    ];
                }
            }
            return $data;
        }

        public function column_title( $item )
        {
	    		  $actions = array(
                'edit'     => sprintf( '<a href="admin.php?page=mobikul-notifications&action=edit&id=%d">' . __( 'Edit', 'mobikul_marketplace' ) . '</a>', $item['id'] ),
                'trash'    => sprintf('<a href="admin.php?page=mobikul-notifications&action=trash&id=%d">' . __( 'Trash', 'mobikul_marketplace' ) . '</a>', $item['id'] ),
                'send_notification'    => sprintf('<a href="admin.php?page=mobikul-notifications&action=send_notification&id=%s">' . __( 'Send Notification', 'mobikul_marketplace' ) . '</a>',$item['id'])
            );
            return sprintf( '%1$s %2$s', $item['title'], $this->row_actions( $actions ) );
	  		}

        public function column_cb( $item )
        {
      			return sprintf( '<input type="checkbox" id="id_%d" name="id[]" value="%d" />', $item['id'], $item['id'] );
  			}

        public function get_bulk_actions()
        {
            $actions = array(
	          		'trash'      => __( 'Trash', 'mobikul_marketplace' ),
	          		'bulk_send'  => __( 'Send Bulk Notifications', 'mobikul_marketplace' )
	      		);
	      		return $actions;
	    	}

        public function process_bulk_action()
        {
            if ( 'trash' === $this->current_action() ) {
                if ( isset( $_GET['id'] ) ) {
                    if ( is_array( $_GET['id'] ) ){
                        foreach ( $_GET['id'] as $id ) {
                        		if ( ! empty( $id ) ) {
                                    $mkwc_result = $this->notification_data->mkwc_delete_notification( $id );
                        		}
                        }
                        if( $mkwc_result ){
                        		echo '<div class="notice notice-success is-dismissible"><p><strong>';

                            echo __( 'Notifications deleted successfully.', 'mobikul_marketplace' ).'</strong></p>';

                            echo '</div>';
                        } else {
                        		echo '<div class="notice notice-error is-dismissible"><p><strong>';

                            echo __( 'An error has occurred.', 'mobikul_marketplace' ).'</strong></p>';

                            echo '</div>';
                        }
                    } else {
                        if ( ! empty ( $_GET['id'] ) ) {
                            $mkwc_result = $this->notification_data->mkwc_delete_notification( $_GET['id'] );

                            if( $mkwc_result ) {
                            		echo '<div class="notice notice-success is-dismissible"><p><strong>';

                            		echo __( 'Notification deleted successfully.', 'mobikul_marketplace' ).'</strong></p>';

                                echo '</div>';
                            } else {
                            		echo '<div class="notice notice-error is-dismissible"><p><strong>';

                                echo __( 'An error has occurred.', 'mobikul_marketplace' ).'</strong></p>';

                                echo '</div>';
                            }
                        }
                    }
                }
            } else if( 'bulk_send' === $this->current_action() ) {
      				 	$count = 0;
      					if ( is_array( $_GET['id'] ) ) {
      							foreach ( $_GET['id'] as $id ) {
        								$id = ( int ) $id;

        								if ( $id > 0 ) {
          									$response = $this->notification_data->mkwc_send_user_notification( $id );

          									$message = json_decode($response);
          									if( isset ( $message->message_id ) ) {
                                $count++;
          									}
        								}
      							}
      							if( $count > 0 ) {
        								echo "<div class='notice notice-success'>";
        								echo "<p><strong>".esc_html($count)."</strong> ".__( 'Notifications are sent.', 'mobikul_marketplace')."</p>";
        								echo "</div>";
      							} else {
        								echo "<div class='notice notice-error'>";
        								echo "<p>".__( 'Error in sending notifications.', 'mobikul_marketplace')."</p>";
        								echo "</div>";
      							}
      				 }
                }  else if ( 'send_notification' === $this->current_action() ) {
                    $id = $_GET['id'];

                    $id = ( int ) $id;

                    if ( $id > 0 ) {
                        $response = $this->notification_data->mkwc_send_user_notification( $id );

                        $message = json_decode($response);
                        if( isset( $message->message_id ) && $message->message_id ) {
                            echo "<div class='notice notice-success'>";
                            echo "<p>".__('Notification sent.','mobikul_marketplace')."</p>";
                            echo "</div>";
                        } else {
                            echo "<div class='notice notice-error'>";
                            echo "<p>". __( 'Error in sending notifications.', 'mobikul_marketplace') ."</p>";
                            echo "</div>";
                        }
                    }
                }
        }
    }
}
