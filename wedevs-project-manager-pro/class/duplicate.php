<?php

class CPM_Pro_Duplicate {

    private static $_instance;

    public static function getInstance() {
        if ( !self::$_instance ) {
            self::$_instance = new CPM_Pro_Duplicate();
        }

        return self::$_instance;
    }

    function fatch_projcet_data( $project_id ) {

        $args = array(
            'post_parent' => $project_id,
            'post_type'   => array('cpm_message', 'cpm_task_list', 'cpm_milestone'),
            'post_status' => 'publish',
            'order'       => 'ASC',
            'orderby'     => 'ID',
            'numberposts' => -1
        );

        $prev_pro_data = get_children( $args );

        $prev_pro_data[$project_id] = get_post( $project_id );

        return $prev_pro_data;
    }

    function create_duplicate( $project_id ) {

        //Get all data post type project, message, task_list, milestone
        $prev_pro_data = $this->fatch_projcet_data( $project_id );

        $new_pro_arg = $this->fill_array( $prev_pro_data[$project_id] );

        //create duplicate new project
        $new_pro_id = $this->insert_duplicate( $new_pro_arg, $project_id );

        if ( !$new_pro_id ) {
            wp_send_json_error( 'Unknown Error', 'cpm-pro' );
        }

        //remove project post type from data array
        unset( $prev_pro_data[$project_id] );

        foreach ($prev_pro_data as $prev_post_id => $post_obj) {
            if( $post_obj->post_type == 'cpm_milestone' ) {
                $args = $this->fill_array( $post_obj, $new_pro_id );
                $project_items = $this->get_project_item($project_id, 'milestone', $post_obj->ID );
                //Insert message, task list and milestone
                $new_milestone_id[$post_obj->ID] = $this->insert_duplicate( $args, $post_obj->ID );
                foreach ($project_items as $project_item ) {
                    $this->set_project_item(array(
                        'project_id'    =>  $new_pro_id,
                        'item_type'     => $project_item->item_type,
                        'object_id'     => $new_milestone_id[$post_obj->ID],
                        'parent' => $new_pro_id,
                        'complete_date' => $project_item->complete_date,
                        'complete_status' => $project_item->complete_status,
                        'private' => $project_item->private,

                    ));
                }

                unset( $prev_pro_data[$prev_post_id] );
            }
        }

        foreach ( $prev_pro_data as $prev_post_id => $post_obj ) {
            $args = $this->fill_array( $post_obj, $new_pro_id );

            $new_milestone_id = isset( $new_milestone_id ) ? $new_milestone_id : array();

            //Insert message, task list and milestone
            $new_task_list_id = $this->insert_duplicate( $args, $post_obj->ID, $new_milestone_id );



            //If post type task list then fatch task and insert duplicate
            if ( $post_obj->post_type == 'cpm_task_list' ) {

                $project_items = $this->get_project_item($project_id, 'cpm_task_list', $post_obj->ID );

                foreach ( $project_items as $project_item) {

                    $this->set_project_item(array(
                        'project_id'    =>  $new_pro_id,
                        'item_type'     => $project_item->item_type,
                        'object_id'     => $new_task_list_id,
                        'parent' => $new_pro_id,
                        'complete_date' => $project_item->complete_date,
                        'complete_status' => $project_item->complete_status,
                        'private' => $project_item->private,

                    ));
                }

                $task = array(
                    'post_parent' => $post_obj->ID,
                    'post_type'   => 'cpm_task',
                    'post_status' => 'publish',
                    'order'       => 'ASC',
                    'orderby'     => 'ID'
                );

                $task_data = get_children( $task );

                // $this->insert_duplicate_task( $task_data, $new_task_list_id );
                // 
                foreach ($task_data as $pro_task_post_id => $post_obj) {

                    $project_items = $this->get_project_item($project_id, 'cpm_task', $post_obj->ID );

                    $args = $this->fill_array( $post_obj, $new_task_list_id );
                    $new_task_id =  $this->insert_duplicate( $args, $post_obj->ID );

                    foreach ( $project_items as $project_item) {

                        $item_id = $this->set_project_item(array(
                            'project_id'    =>  $new_pro_id,
                            'item_type'     => $project_item->item_type,
                            'object_id'     => $new_task_id,
                            'parent'        => $new_task_list_id,
                            'complete_date' => $project_item->complete_date,
                            'complete_status' => $project_item->complete_status,
                            'private' => $project_item->private,

                        ));

                        $task_items = $this->get_task_item($project_item->id);

                        foreach ($task_items as $task_item ) {
                            $this->insert_task_item($item_id, $task_item->user_id,  $task_item->start, $task_item->due);
                        }
                    }

                    

                   /**
                   * @since 1.4
                   */
                    do_action( 'cpm_task_duplicate_after', $post_obj->ID, $new_task_id  );
                }
            }
        }

        /**
         * @since 1.4
         */
        do_action( 'cpm_project_duplicate', $project_id, $new_pro_id );

        return $new_pro_id;
    }

    function fill_array( $post_obj, $new_post_id = '' ) {
        $args = array(
            'post_parent'    => $new_post_id,
            'comment_status' => $post_obj->comment_status,
            'ping_status'    => $post_obj->ping_status,
            'post_author'    => $post_obj->post_author,
            'post_content'   => $post_obj->post_content,
            'post_name'      => $post_obj->post_name,
            'post_status'    => 'publish',
            'post_title'     => $post_obj->post_title,
            'post_type'      => $post_obj->post_type,
            'menu_order'     => $post_obj->menu_order,
        );

        return $args;
    }

    function insert_duplicate_task( $task_data, $new_task_list_id ) {

        foreach ($task_data as $pro_task_post_id => $post_obj) {
           $args = $this->fill_array( $post_obj, $new_task_list_id );
           $new_task_id =  $this->insert_duplicate( $args, $post_obj->ID );

           /**
           * @since 1.4
           */
           do_action( 'cpm_task_duplicate_after', $post_obj->ID, $new_task_id  );
        }
    }



    function insert_duplicate( $args, $project_id, $new_milestone_id=array() ) {
        global $wpdb;

        /*
         * insert the post by wp_insert_post() function
         */
        $new_post_id = wp_insert_post( $args );

        /*
         * get all current post terms ad set them to the new post draft
         */
        $taxonomies = get_object_taxonomies( $args['post_type'] ); // returns array of taxonomy names for post type, ex array("category", "post_tag");
        foreach ($taxonomies as $taxonomy) {
            $post_terms = wp_get_object_terms( $project_id, $taxonomy, array('fields' => 'slugs') );
            wp_set_object_terms( $new_post_id, $post_terms, $taxonomy, false );
        }

        /*
         * duplicate all post meta
         */
        $post_meta_infos = $wpdb->get_results( "SELECT meta_key, meta_value FROM $wpdb->postmeta WHERE post_id=$project_id" );

        if ( count( $post_meta_infos ) != 0 ) {
            $sql_query = "INSERT INTO $wpdb->postmeta (post_id, meta_key, meta_value) ";

            foreach ($post_meta_infos as $meta_info) {
                $meta_key = $meta_info->meta_key;
                $meta_value = addslashes( $meta_info->meta_value );
                if( $meta_key == '_milestone' && ( $args['post_type'] == 'cpm_task_list' || $args['post_type'] == 'cpm_message' ) ) {

                    $meta_info->meta_value = isset( $new_milestone_id[$meta_info->meta_value] ) ? $new_milestone_id[$meta_info->meta_value] : '';
                    $meta_value = addslashes( $meta_info->meta_value );
                }
                $sql_query_sel[] = "SELECT $new_post_id, '$meta_key', '$meta_value'";
            }

            $sql_query.= implode( " UNION ALL ", $sql_query_sel );
            $wpdb->query( $sql_query );
        }

        if( $args['post_type'] == 'cpm_project' ) {

            $get_all_users = CPM_Project::getInstance()->get_users( $project_id );

            if ( is_array( $get_all_users ) && count( $get_all_users ) ) {
                foreach ($get_all_users as $user) {
                    CPM_Project::getInstance()->insert_user( $new_post_id, $user['id'], $user['role'] );
                }
            }
        }

        return $new_post_id;
    }

    function get_task_item ( $task_id ){
        global $wpdb;
        $table = $wpdb->prefix . 'cpm_tasks';
        $sql = "SELECT * FROM $table WHERE item_id=$task_id";
        return  $wpdb->get_results( $sql );
    }


    function insert_task_item ( $new_insert_id,  $assigned, $start_date, $due_date ) {
        global $wpdb;
        $table = $wpdb->prefix . 'cpm_tasks';

        $data = array (
            'item_id' => $new_insert_id,
            'user_id' => $assigned,
            'start'   => isset( $start_date ) ? $start_date : '0000-00-00 00:00:00',
            'due'     => $due_date ? $due_date : '0000-00-00 00:00:00',
        );
        $wpdb->insert( $table, $data, array ( '%d', '%d', '%s', '%s' ) );
    }

    function get_project_item ( $project_id, $item_type, $object_id ) {
        global $wpdb;
        $table = $wpdb->prefix . 'cpm_project_items';
        $sql = "SELECT * FROM $table WHERE project_id=$project_id AND item_type='$item_type' AND object_id= $object_id";
        return $wpdb->get_results( $sql );
    }

    function set_project_item ( $item ) {
        global $wpdb;
        $table = $wpdb->prefix . 'cpm_project_items';
        $data = array (
            'project_id' => $item['project_id'],
            'item_type' => $item['item_type'],
            'object_id' => $item['object_id'],
            'parent' => $item['parent'],
            'complete_date' => $item['complete_date'],
            'complete_status' => $item['complete_status'],
            'private' => isset ($item['private'])? $item['private']: 0,
            
        );
        $wpdb->insert( $table, $data, array ( '%d', '%s', '%d', '%d', '%s', '%d', '%d' ) );
        return $wpdb->insert_id;
    }

}