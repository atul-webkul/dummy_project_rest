<?php

/**
 * Calendar
 *
 * @author Tareq Hasan (http://tareq.weDevs.com)
 */
class CPM_Pro_Calendar {

    private static $_instance;

    public function __construct() {

    }

    public static function getInstance() {
        if ( !self::$_instance ) {
            self::$_instance = new CPM_Pro_Calendar();
        }

        return self::$_instance;
    }

    function get_events() {
        $projects = CPM_Project::getInstance()->get_all_project();
        unset($projects['total_projects']);

        if ( cpm_get_option( 'task_start_field', 'cpm_general' ) == 'on' ) {
            $enable_start = true;
        } else {
            $enable_start = false;
        }

        $events = array();
        if ( $projects ) {

            foreach ($projects as $project) {
                $project_id = $project->ID;

                if ( absint( $_POST['project_id'] ) && $project_id != absint( $_POST['project_id'] ) ) {
                    continue;
                }

                //Get Milestones
                $milestones = CPM_Milestone::getInstance()->get_by_project( $project_id );
                if ( $milestones ) {
                    foreach ($milestones as $milestone) {
                        $milestone_date = empty( $milestone->due_date ) ? date("Y-m-d", strtotime( $milestone->post_date ) ) : date("Y-m-d", strtotime( $milestone->due_date ) );
                        //Milestone Event
                        $events[] = array(
                            'id'        => $milestone->ID,
                            'title'     => $milestone->post_title,
                            'start'     => $milestone_date,
                            'url'       => cpm_url_milestone_index( $project_id ),
                            'color'     => '#32b1c8',
                            'className' => ($milestone->completed == 1) ? 'milestone competed' : 'milestone'
                        );
                    }
                }

                //Get Tasks
                if ( cpm_user_can_access( $project_id, 'tdolist_view_private' ) ) {
                    $task_lists = CPM_Task::getInstance()->get_task_lists( $project_id, FALSE , 0, TRUE, TRUE);
                } else {
                    $task_lists = CPM_Task::getInstance()->get_task_lists( $project_id, TRUE, 0, TRUE, TRUE );
                }

                if ( $task_lists ) {
                    $task_lists['lists'] = isset( $task_lists['lists'] ) ? $task_lists['lists'] : array();

                    foreach ( $task_lists['lists'] as $task_list ) {
                        $tasks = CPM_Task::getInstance()->get_tasks_by_access_role( $task_list->ID, $project_id );

                        foreach ($tasks as $task) {
                            $image = '';
                            if ( is_array( $task->assigned_to ) ) {
                                foreach ( $task->assigned_to as $key => $user_id ) {
                                    $image .= get_avatar( $user_id, 16, 'mm' );
                                }
                            } else {
                                $image .= get_avatar( $task->assigned_to, 16, 'mm' );
                            }

                            if ( empty( $task->due_date ) ) {
                                $css_class_name = 'cpm-calender-todo cpm-task-running';
                            } else if ( date( 'Y-m-d', time() ) < date( 'Y-m-d', strtotime( $task->due_date ) ) ) {
                                $css_class_name = 'cpm-calender-todo cpm-task-running';
                            } else if (  date( 'Y-m-d', time() ) > date( 'Y-m-d', strtotime( $task->due_date ) ) ) {
                                $css_class_name = 'cpm-calender-todo cpm-expire-task';
                            }

                            if ( !empty( $task->due_date ) ) {
                                $due_date =  date( "Y-m-d", strtotime( $task->due_date . ' +1 day' ) );
                            }else if ( empty( $task->due_date ) && !empty( $task->start_date )) {
                                $due_date =  date( "Y-m-d", strtotime( $task->start_date . ' +1 day' ) );
                            }else {
                                $due_date =  date( "Y-m-d", strtotime( $task->post_date . ' +1 day' ) );
                            }

                            //Tasks Event
                            if ( $enable_start ) {

                                if ( isset( $task->start_date ) && !empty( $task->start_date ) ) {
                                    $start_date = $task->start_date;
                                } else {
                                    $start_date = $task->post_date;
                                }

                                



                                $events[] = array(
                                    'id'              => $task->ID,
                                    'img'             => ($task->assigned_to == -1) ? '' : $image,
                                    'title'           => $task->post_title,
                                    'start'           => $start_date,
                                    'end'             => $due_date,
                                    'complete_status' => ($task->completed == 1 ) ? 'yes' : 'no',
                                    'url'             => cpm_url_single_task( $project_id, $task_list->ID, $task->ID ),
                                    'color'           => 'transparent',
                                    'textColor'       => '#c86432',
                                    'className'       => $css_class_name
                                );

                            } else {

                                $events[] = array(
                                    'id'              => $task->ID,
                                    'img'             => ($task->assigned_to == -1) ? '' : $image,
                                    'title'           => $task->post_title,
                                    'start'           => $due_date,
                                    'complete_status' => ($task->completed == 1 ) ? 'yes' : 'no',
                                    'url'             => cpm_url_single_task( $project_id, $task_list->ID, $task->ID ),
                                    'color'           => 'transparent',
                                    'textColor'       => '#c86432',
                                    'className'       => $css_class_name
                                );
                            }
                        }
                    }
                }
            }
        }

        return $events;
    }


    function get_user_events($user_id) {
        $projects = CPM_Project::getInstance()->get_projects();
        unset($projects['total_projects']);

        if ( cpm_get_option( 'task_start_field', 'cpm_general' ) == 'on' ) {
            $enable_start = true;
        } else {
            $enable_start = false;
        }


        $events = array();
        if ( $projects ) {

                $post_project = ( isset($_POST['project_id']) ? sanitize_text_field($_POST['project_id']) : 0 ) ;

                $task_lists =  CPM_Pro_Task::getInstance()->get_user_all_task($user_id,  $post_project);

                if ( $task_lists ) {

                        foreach ($task_lists as $task) {

                            $image = '';
                            if ( is_array( $task->assigned_to ) ) {
                                foreach ( $task->assigned_to as $key => $user_id ) {
                                    $image .= get_avatar( $user_id, 16, 'mm' );
                                }
                            } else {
                                $image .= get_avatar( $task->assigned_to, 16, 'mm' );
                            }

                            if ( empty( $task->due_date ) ) {
                                $css_class_name = 'cpm-calender-todo cpm-task-running';
                            } else if ( date( 'Y-m-d', time() ) < date( 'Y-m-d', strtotime( $task->due_date ) ) ) {
                                $css_class_name = 'cpm-calender-todo cpm-task-running';
                            } else if (  date( 'Y-m-d', time() ) > date( 'Y-m-d', strtotime( $task->due_date ) ) ) {
                                $css_class_name = 'cpm-calender-todo cpm-expire-task';
                            }

                            if ( !empty( $task->due_date ) ) {
                                $due_date =  date( "Y-m-d", strtotime( $task->due_date . ' +1 day' ) );
                            }else if ( empty( $task->due_date ) && !empty( $task->start_date )) {
                                $due_date =  date( "Y-m-d", strtotime( $task->start_date . ' +1 day' ) );
                            }else {
                                $due_date =  date( "Y-m-d", strtotime( $task->post_date . ' +1 day' ) );
                            }
                            //Tasks Event
                            if ( $enable_start ) {
                                if ( isset( $task->start_date ) && !empty( $task->start_date ) ) {
                                    $start_date = date( "Y-m-d", strtotime( $task->start_date ) );
                                } else {
                                    $start_date = date("Y-m-d", strtotime( $task->post_date ) );
                                }


                                $events[] = array(
                                    'id'              => $task->task_id,
                                    'img'             => ($task->assigned_to == -1) ? '' : $image,
                                    'title'           => $task->task,
                                    'start'           => $start_date,
                                    'end'             => $due_date,
                                    'complete_status' => ($task->completed == 1 ) ? 'yes' : 'no',
                                    'url'             => cpm_url_single_task( $task->project_id, $task->task_list_id,   $task->task_id ),
                                    'color'           => 'transparent',
                                    'textColor'       => '#c86432',
                                    'className'       => $css_class_name
                                );

                            } else {
                                $events[] = array(
                                    'id'              => $task->task_id,
                                    'img'             => ($task->assigned_to == -1) ? '' : $image,
                                    'title'           => $task->task,
                                    'start'           => $task->due_date,
                                    'complete_status' => ($task->completed == 1 ) ? 'yes' : 'no',
                                    'url'             => cpm_url_single_task( $task->project_id, $task->task_list_id, $task->task_id ),
                                    'color'           => 'transparent',
                                    'textColor'       => '#c86432',
                                    'className'       => $css_class_name
                                );
                            }
                        }

                }
                // end



        }

        return $events;
    }

}
