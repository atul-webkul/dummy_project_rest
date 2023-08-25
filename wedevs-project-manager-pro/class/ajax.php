<?php

/**
 * Description of ajax
 *
 * @author tareq
 */
class CPM_Pro_Ajax extends CPM_Ajax {
	private static $_instance;

	public static function getInstance() {
        if ( is_null( self::$_instance ) ) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    public function __construct() {
    	add_action( 'wp_ajax_cpm_get_events', array( $this, 'get_events' ) );
    	add_action( 'wp_ajax_cpm_get_user_events', array( $this, 'get_user_events' ) );
        add_action( 'wp_ajax_cpm_project_duplicate', array( $this, 'project_duplicate' ) );
        add_action( 'wp_ajax_get_mytask_content', array( $this, 'get_mytask_content' ) );
        add_action( 'wp_ajax_user_line_graph', array( $this, 'get_user_line_graph' ) );
        add_action( 'wp_ajax_get_user_activity', array( $this, 'get_user_activity' ) );
        add_action( 'wp_ajax_all_search', array( $this, 'all_search' ) );
        add_action( 'wp_ajax_search_client', array( $this, 'search_client' ) );
    }

    /**
     * Search clients via ajax
     *
     * @return void
     */
    function search_client() {
        $user =  isset( $_POST['user'] ) ? sanitize_text_field( $_POST['user'] ) : '';

        $args = array(
            'search'         => "*".$user."*",
            'search_columns' => array(  'display_name' ),
        );

        add_action( 'pre_user_query', array( $this, 'pre_user_query' ) );

        $user_query = new WP_User_Query( $args );

        $projects_id = array();
        foreach ( $user_query->results as $user ) {
            $projects_id[] = $user->project_id;
        }

        $items = array();

        if ( ! count( $projects_id ) ) {
            $items[] = array(
                'label' => '<div class="cpm-all-search-item"><strong>' . __( 'No project found !', 'cpm-pro' ) . '</strong></div>',
            );

            $search_details = json_encode( $items );
            wp_send_json_success( $search_details );
        }

        $post_query = new WP_Query( array(
            'post_type'      => 'cpm_project',
            'post_status'    => 'publish',
            'post__in'       => $projects_id,
            'posts_per_page' => -1,
                ) );

        foreach ( $post_query->posts as $post ) {
            $url      = cpm_url_project_overview( $post->ID );
            $category = __( 'Project: ', 'cpm-pro' );
            $items[]  = array(
                'label' => '<div class="cpm-all-search-item"><a href="' . $url . '"><strong>' . $category . '</strong>' . $post->post_title . '</a></div>',
            );
        }

        $search_details = json_encode( $items );
        wp_send_json_success( $search_details );
    }

    function all_search() {

        $project_id  = isset( $_POST['project_id'] ) ? sanitize_text_field( $_POST['project_id'] ) : false;
        $item        = trim( $_POST['item'] );

        if ( ! $project_id ) {
            $args  = array(
                'post_type'      => array( 'cpm_project', 'cpm_task_list', 'cpm_task', 'cpm_message', 'cpm_milestone' ),
                'post_status'    => 'publish',
                'posts_per_page' => '-1',
                's'              => $item,
            );

            $args  = apply_filters( 'cpm_all_project_search_query_arg', $args, $item );
            $query = new WP_Query( $args );
            $posts = $query->posts;
        }

        if ( $project_id ) {
            $args  = array(
                'post_type'      => array( 'cpm_task_list', 'cpm_message', 'cpm_milestone' ),
                'post_status'    => 'publish',
                'post_parent'    => $project_id,
                'posts_per_page' => '-1',
                's'              => $item,
            );
            $query = new WP_Query( $args );

            global $wpdb;
            $sql = "SELECT * FROM $wpdb->posts AS p
                LEFT JOIN $wpdb->posts AS tl ON p.ID=tl.post_parent
                LEFT JOIN $wpdb->posts AS tk ON tl.ID=tk.post_parent
                WHERE
                p.post_type='cpm_project' AND p.post_status='publish' AND p.ID = $project_id
                AND  tl.post_type='cpm_task_list' AND tk.post_type='cpm_task'
                AND  tl.post_status='publish' AND tk.post_status='publish'
                AND ( tk.post_title like '%$item%' OR tk.post_content like '%$item%' )";

            $posts = $wpdb->get_results( $sql );
            $posts = array_merge( $query->posts, $posts );
        }

        $url   = array();
        $items = array();
        foreach ( $posts as $key => $post ) {

            if ( $post->post_type == 'cpm_project' ) {
                $project_id = $post->ID;
            } else {
                $project_id = $post->post_parent;
            }

            //check manage capability and users role in individual project
            if ( ! cpm_can_manage_projects( $user->ID ) && ! cpm_get_role_in_project( $project_id ) ) {
                continue;
            }

            switch ( $post->post_type ) {
                case 'cpm_project':

                    $url      = cpm_url_project_overview( $post->ID );
                    $category = __( 'Project: ', 'cpm-pro' );
                    $items[]  = array(
                        'label' => '<div class="cpm-all-search-item"><a href="' . $url . '"><strong>' . $category . '</strong>' . $post->post_title . '</a></div>',
                    );
                    break;

                case 'cpm_task_list':
                    $is_private = get_post_meta( $post->ID, '_tasklist_privacy', true );

                    if ( $is_private == 'yes' && ! cpm_user_can_access( $post->post_parent, 'tdolist_view_private' ) ) {
                        continue;
                    }

                    $url      = cpm_url_single_tasklist( $post->post_parent, $post->ID );
                    $category = __( 'Task List: ', 'cpm-pro' );
                    $items[]  = array(
                        'label' => '<div class="cpm-all-search-item"><a href="' . $url . '"><strong>' . $category . '</strong>' . $post->post_title . '</a></div>',
                    );
                    break;

                case 'cpm_task':
                    $is_private = get_post_meta( $post->ID, '_task_privacy', true );
                    if( $is_private == 'yes' && ! cpm_user_can_access( $post->post_parent, 'todo_view_private' ) ) {
                        continue;
                    }

                    $task_list = get_post( $post->post_parent );
                    $url       = cpm_url_single_task( $task_list->post_parent, $post->post_parent, $post->ID );
                    $category  = __( 'Task: ', 'cpm-pro' );
                    $items[]   = array(
                        'label' => '<div class="cpm-all-search-item"><a href="' . $url . '"><strong>' . $category . '</strong>' . $post->post_title . '</a></div>',
                    );
                    break;

                case 'cpm_message':
                    $is_private = get_post_meta( $post->ID, '_message_privacy', true );

                    if( $is_private == 'yes' && ! cpm_user_can_access( $post->post_parent, 'msg_view_private' ) ) {
                        continue;
                    }

                    $url      = cpm_url_single_message( $post->post_parent, $post->ID );
                    $category = __( 'Message: ', 'cpm-pro' );
                    $items[]  = array(
                        'label' => '<div class="cpm-all-search-item"><a href="' . $url . '"><strong>' . $category . '</strong>' . $post->post_title . '</a></div>',
                    );
                    break;

                case 'cpm_milestone':
                    $is_private = get_post_meta( $post->ID, '_milestone_privacy', true );
                    if( $is_private == 'yes' && ! cpm_user_can_access( $post->post_parent, 'milestone_view_private' ) ) {
                        continue;
                    }

                    $url      = cpm_url_milestone_index( $post->post_parent );
                    $category = __( 'Milestone: ', 'cpm-pro' );
                    $items[]  = array(
                        'label' => '<div class="cpm-all-search-item"><a href="' . $url . '"><strong>' . $category . '</strong>' . $post->post_title . '</a></div>',
                    );
                    break;
            }
        }

        if ( ! count( $items ) ) {
            $items[] = array(
                'label' => '<div class="cpm-all-search-item"><strong>' . __( 'No item found !', 'cpm-pro' ) . '</strong></div>',
            );
        }
        $search_details = json_encode( $items );

        wp_send_json_success( $search_details );
    }

    function get_events() {

        $events = CPM_Pro_Calendar::getInstance()->get_events();
        // var_dump($events); die();

        if ( $events ) {
            echo json_encode( $events );
        } else {
            echo json_encode( array(
                'success' => false
            ) );
        }
        exit;
    }

    function get_user_events() {
        $user_id = sanitize_text_field( $_POST['user_id'] ) ;
        $events = CPM_Pro_Calendar::getInstance()->get_user_events($user_id);


        if ( $events ) {
            echo json_encode( $events );
        } else {
            echo json_encode( array(
                'success' => false
            ) );
        }
        exit;
    }

    function project_duplicate() {
        if ( ! wp_verify_nonce( $_POST['_nonce'], 'cpm_nonce' ) ) {
            wp_send_json_error( __( 'Are you cheating?', 'cpm-pro' ) );
        }

        if ( isset( $_POST['project_id'] ) ) {
            $project_id = $_POST['project_id'];
        } else {
            wp_send_json_error( __( 'Project ID required', 'cpm-pro' ) );
        }

        CPM_Pro_Duplicate::getInstance()->create_duplicate( $project_id );

        wp_send_json_success( array(
            'url' => $_POST['url']
        ) );
    }

    function  my_task_acivity($uid){
        $task    = CPM_Pro_Task::getInstance();
        $content = $task->user_activity($uid);
        die();
    }


    function  get_mytask_content(){

        if ( ! wp_verify_nonce( $_POST['_wpnonce'], 'cpm_nonce' ) ) {
            wp_send_json_error( __( 'Are you cheating?', 'cpm-pro' ) );
        }

        $tab = $_POST['tab_act'] ;
        $user = $_POST['user'] ;
        $task    = CPM_Pro_Task::getInstance();

        $content = '';
        if( $tab == 'overview' ) {
            $content =  $task->user_overview($user);
        } else if( $tab == 'activity' ) {
            $this->my_task_acivity($user) ;
        }else {
            $project = $task->my_task_current_tab( $tab );
            $task_list  =  $task->current_user_task( $user, $tab ) ;
            $content = $task->taskhtmloutput( $task_list , $user) ;
        }

        die();

    }

    function  get_user_line_graph(){
         if ( ! wp_verify_nonce( $_POST['_wpnonce'], 'cpm_nonce' ) ) {
            wp_send_json_error( __( 'Are you cheating?', 'cpm-pro' ) );
        }

        $range = $_POST['range'] ;
        $user = $_POST['user'] ;
        $task    = CPM_Pro_Task::getInstance();
        ?> 
            <script>
            console.log('ttt', <?php echo $range; ?>);
            </script>
        <?php
        $content = $task->mytask_line_graph($user, $range) ;

        die();
    }


    function get_user_activity(){
        $user_id = isset( $_GET['user_id'] ) ? intval( $_GET['user_id'] ) : 0;
        $offset = isset( $_GET['offset'] ) ? intval( $_GET['offset'] ) : 0;
        $activities = CPM_Pro_Task::getInstance()->get_user_activity( $user_id, $offset );

        if ( $activities ) {
            echo json_encode( array(
                'success' => true,
                'content' => cpm_user_activity_html( $activities, $user_id ),
                'count' => count( $activities )
            ) );
        } else {
            echo json_encode( array(
                'success' => false
            ) );
        }
        exit;


    }

}


