<?php
/**
 * Plugin Name: WP Project Manager Pro - Gantt Chart
 * Plugin URI: https://wedevs.com/products/plugins/wp-project-manager-pro/gantt-chart/
 * Description: Gantt chart add-on for WP Project Manager
 * Author: weDevs
 * Author URI: https://wedevs.com
 * Version: 1.3
 * License: GPL2
 */

/**
 * Copyright (c) 2016 weDevs Team (email: info@wedevs.com). All rights reserved.
 *
 * Released under the GPL license
 * http://www.opensource.org/licenses/gpl-license.php
 *
 * This is an add-on for WordPress
 * http://wordpress.org/
 *
 * **********************************************************************
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 * **********************************************************************
 */

// don't call the file directly
if ( !defined( 'ABSPATH' ) ) exit;

if ( is_admin() ) {
    require_once dirname( __FILE__ ) . '/lib/wedevs-updater.php';

    new WeDevs_Plugin_Update_Checker( plugin_basename( __FILE__ ), 'cpm-gantt-chart' );
}

/**
 * Gantt Chart main class
 *
 * @package CPM
 */
class CPM_Gantt_Chart {

    private $version = '1.3';

    function __construct() {

        add_filter( 'cpm_project_nav_links', array( $this, 'project_nav_link' ), 10, 2 );
        add_filter( 'cpm_tab_file', array( $this, 'file_tab' ), 10, 5 );
        add_action( 'wp_enqueue_scripts', array( $this, 'scripts' ) );
        add_action( 'admin_enqueue_scripts', array( $this, 'scripts' ) );
        add_action( 'wp_ajax_update_task_date', array( $this, 'update_task_date' ) );
        add_action( 'wp_ajax_delete_link', array( $this, 'delete_link' ) );
        add_action( 'wp_ajax_update_link', array( $this, 'update_link' ) );
        add_action( 'cpmf_project_tab', array( $this, 'frontend_url' ), 10, 3 );
        //add_action( 'cpm_task_column', array( $this, 'gant_task_single_after' ), 10, 5 );
        add_action( 'wp_ajax_gantt_new_task', array( $this, 'gantt_new_task' ) );
        add_action( 'wp_ajax_user_access_task', array( $this, 'checkuseraccesstask' ) );
        add_action( 'plugins_loaded', array( $this, 'load_textdomain' ) );

    }

    /**
     * Load plugin textdomain
     *
     * @since 1.2
     */
    function load_textdomain() {
        load_plugin_textdomain( 'gant', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
    }

    function checkuseraccesstask($project_id, $task_id) {
        if(!cpm_user_can_delete_edit( $project_id, $task_id, true )){
            echo "not accessable!";
        }
    }

    function gantt_new_task() {
        check_ajax_referer('gant_nonce');

        $list_id = isset( $_POST['list_id'] ) ? intval( $_POST['list_id'] ) : false;

        CPM_Task::getInstance()->add_task( $list_id, $_POST );

        wp_send_json_success();
    }

    function gant_task_single_after( $task, $project_id, $list_id, $single, $completed ) {
        // var_dump($task) ;

        $progress = round( get_post_meta( $task->task_id, '_completed', true ), 2 )*100;
        if ( $progress != 1 ) {
            ?>
            <span class="gant-task-progressccc"><?php echo "[{$progress}%]"; ?></span>
            <?php
        }
    }

    function delete_link() {
        check_ajax_referer('gant_nonce');

        $source_id = $_POST['source_id'];
        $target_id = $_POST['target_id'];

        $ids = get_post_meta( $source_id, '_link', true );
        foreach ( $ids as $key => $id ) {
            if ( $id == $target_id ) {
                unset( $ids[$key] );
            }
        }

        update_post_meta( $source_id, '_link', $target_ids );
        wp_send_json_success();
    }

    function update_link() {
        check_ajax_referer('gant_nonce');
        $source_id = $_POST['source_id'];
        $target_id = $_POST['target_id'];

        $target_ids = get_post_meta( $source_id, '_link', true );

        if ( empty( $target_ids ) ) {
            $target_ids = array( $target_id );
        } else {
            array_push( $target_ids , $target_id );
        }

        update_post_meta( $source_id, '_link', $target_ids );
        wp_send_json_success();
    }

    function frontend_url( $project_id, $tab, $action ) {
        if ( $tab == 'chart' ) {
            require_once dirname (__FILE__) . '/views/index.php';
        }
    }

    function update_task_date() {
        /*task task_list*/
        check_ajax_referer('gant_nonce');
        $id = $_POST['id'];

        $due_date = date( 'Y-m-d h:i:s', strtotime( $_POST['end_date'] . '- 1 day' ) );

        $start_date = date( 'Y-m-d h:i:s', strtotime( $_POST['start_date'] ) );
        $progress = $_POST['progress'];

        $post_type = get_post_type( $id );
        if ( $post_type == 'cpm_task' && $progress == 1 ) {
            CPM_Task::getInstance()->mark_complete( $id );
        }

        if ( $post_type == 'cpm_task_list' && $progress == 1 ) {
            CPM_Task::getInstance()->mark_complete( $id );
        }

        update_post_meta( $id, '_start', $start_date );
        update_post_meta( $id, '_due', $due_date );
        update_post_meta( $id, '_completed', $progress );

        wp_send_json_success();
    }


    function scripts() {
        $suffix  = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';

        if ( !isset( $_GET['tab'] ) || !isset( $_GET['action'] ) ) {
            return;
        }

        if ( $_GET['tab'] == 'chart' && $_GET['action'] == 'index' ) {

            wp_enqueue_script( 'dhtml_chart', plugins_url( 'assets/js/gantt-library' . $suffix . '.js', __FILE__ ), array('jquery'), false, true );
            wp_enqueue_script( 'gant', plugins_url( 'assets/js/gant' . $suffix . '.js', __FILE__ ), array('jquery'), false, true );
            wp_localize_script( 'gant', 'gantts', array(
                'ajaxurl'           => admin_url( 'admin-ajax.php' ),
                'nonce'             => wp_create_nonce( 'gant_nonce' ),
                'enable_start_date' => cpm_get_option('task_start_field'),
                'start_date'        => __('Start Date', 'gant'),
                'end_date'          => __('End Date', 'gant'),
                'assign_to'         => __('Assign to', 'gant'),
                'private'           => __('Private', 'gant'),
                'make_private'      => __('Make Private', 'gant'),
                'task_name'         => __('Task Name', 'gant'),
                'assign'            => __('Assigned', 'gant'),
                'add_task'          => __('Add Task', 'gant'),
                'message_empty'     => __('Please input task tile', 'gant')
            ));

            wp_enqueue_style('dhtml_style', plugins_url( 'assets/css/dhtmlxgantt.css', __FILE__ ), false, false, false  );
        }

        wp_enqueue_style('gant_style', plugins_url( 'assets/css/gant.css', __FILE__ )  );

    }

    function project_nav_link( $links, $project_id ) {
        $links[__( 'Gantt Chart', 'gant' )] = array('url' => $this->gant_url( $project_id ), 'count' => '', 'class' => 'ganttchat cpm-sm-col-12' );

        return $links;
    }

    function gant_url( $project_id ) {
        if ( is_admin() ) {
            $url = sprintf( '%s?page=cpm_projects&tab=chart&action=index&pid=%d', admin_url( 'admin.php' ), $project_id );
        } else {

            $page_id = cpm_get_option('project');
            $url = add_query_arg( array(
                'project_id' => $project_id,
                'tab'        => 'chart',
                'action'     => 'index'
            ), get_permalink( $page_id ) );
        }

        return apply_filters( 'cpm_url_gantt_chart', $url, $project_id );
    }

    function file_tab( $file, $project_id, $page, $tab, $action  ) {

        if ( $tab == 'chart' ) {
            $file = dirname( __FILE__ ) . '/views/index.php';
        }

        return $file;
    }
}

new CPM_Gantt_Chart();

/**
 * [gant_date_duration description]
 *
 * @param  string $start_date
 * @param  string $end_date
 * @return int
 */
function gant_date_duration( $start_date, $end_date ) {
    $start_date = date( 'Y-m-d', strtotime( $start_date ) );
    $end_date = date( 'Y-m-d', strtotime( $end_date ) );

    $start_ts = strtotime($start_date);
    $end_ts = strtotime($end_date);
    $diff = $end_ts - $start_ts;
    $duration = ($diff / 86400) + 1;

    return $duration;
}