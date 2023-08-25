<?php
$disabled = '';
global $wpdb;
if ( isset( $_GET['user_id'] ) && !empty( $_GET['user_id'] ) ) {
    if ( ! cpm_can_manage_projects() ) {
        printf( '<h1>%s</h1>', __( 'You do not have permission to access this page', 'cpm-pro' ) );
        return;
    }

    $user_id         = intval( $_GET['user_id'] );
    $this_user       = false;
    $mytaskuser_user = get_user_by( 'id', $user_id );
    $title           = sprintf( "%s's tasks", $mytaskuser_user->display_name );
} else {
    $this_user       = true;
    $loin_user       = wp_get_current_user();
    $user_id         = $loin_user->ID;
    $mytaskuser_user = get_user_by( 'id', $user_id );
    $title           = __( 'My Tasks', 'cpm-pro' );
}

if ( isset( $_GET['tab'] ) ) {
    $ctab = sanitize_text_field( $_GET['tab'] );
} else {
    $ctab = 'overview';
}

$user_id = apply_filters( 'cpm_my_task_user_id', $user_id );									$usr_id = intval( $_GET['user_id'] );									 									 if(empty($usr_id)){										 $usr_id = get_current_user_id();									 }									 									 $user_attch_id = get_user_meta($usr_id,'wnw_vp_user_image',true);									 $feat_image_url = wp_get_attachment_url( $user_attch_id);																	$sellerImg = $wpdb->get_results("select um.meta_value,pm.meta_value from wp_usermeta um join wp_postmeta pm on um.meta_value=pm.post_id where um.user_id='$usr_id' and um.meta_key='_thumbnail_id_avatar' and pm.meta_key='_wp_attached_file'");																		if($sellerImg[0]->meta_value){									$avatarp = '<img width="64" class="avatar" src="'.site_url().'/wp-content/uploads/'.$sellerImg[0]->meta_value.'" />';									}									else if($feat_image_url != ''){										$avatarp = '<img width="64" class="avatar" src="'.$feat_image_url.'" />';									}									else{                                    $avatarp  = get_avatar( $user_id, 64, 'mm', $mytaskuser_user->display_name );									}$avatar  = get_avatar( $user_id, 64, 'mm', $mytaskuser_user->display_name );
// Check user exist
if ( !get_userdata( $user_id ) ) {
    printf( '<h1>%s</h1>', __( 'The user could not be found!', 'cpm-pro' ) );
    return;
}

if ( ! cpm_can_manage_projects() && $mytaskuser_user->ID != $user_id ) {
    printf( '<h1>%s</h1>', __( 'You do not have permission to access this page', 'cpm-pro' ) );
    return;
}

$task    = CPM_Pro_Task::getInstance();
$project = $task->get_mytasks( $user_id );
$count   = $task->mytask_count( $user_id );
$ctab  = apply_filters( 'cpm_my_task_tab', $ctab );
?>
<!-- Start -->
<div class="wrap cpm my-tasks cpm-my-tasks">
    <div class="cpm-top-bar cpm-no-padding cpm-project-header cpm-project-head">
        <div class="cpm-row cpm-no-padding cpm-border-bottom">
            <div class="cpm-project-detail ">
                <?php if ( apply_filters( 'cpm_my_task_title', true ) ) { ?>
                    <h3 class="cpm-my-task"><?php echo $avatarp . " " . $title; ?></h3>
                <?php } ?>

                <?php do_action( 'cpm_my_task_after_title', $project, $ctab ); ?>
            </div>
        </div>

        <div class="cpm-row cpm-project-group">
            <ul class="cpm-col-10 cpm-my-task-menu">

                <li  class="<?php if ( $ctab == 'overview' ) echo 'active' ?>">
                    <a href="<?php echo cpm_url_my_task() ?>" class="cpm-my-taskoverview" data-item="overview" data-user="<?php echo $user_id ?>"><?php _e( 'Overview', 'cpm-pro' ); ?><div></div></a>
                </li>
                <li class="<?php if ( $ctab == 'useractivity' ) echo 'active' ?>">
                    <a href="<?php echo cpm_url_user_activity(); ?>" class="cpm-my-taskactivity" data-item="activity" data-user="<?php echo $user_id ?>"><?php _e( 'Activity', 'cpm-pro' ); ?><div></div></a>
                </li>

                <li class="<?php if ( $ctab == 'current' ) echo 'active' ?>">
                    <a href="<?php echo cpm_url_current_task(); ?>" data-item="current" data-user="<?php echo $user_id ?>" class="cpm-my-currenttask"><?php _e( 'Current Task', 'cpm-pro' ); ?> <div ><?php echo $count[ __( 'Current', 'cpm-pro' ) ]; ?></div></a>
                </li>

                <li class="<?php if ( $ctab == 'outstanding' ) echo 'active' ?>">
                    <a href="<?php echo cpm_url_outstanding_task(); ?>" data-item="outstanding" data-user="<?php echo $user_id ?>" class="cpm-my-outstandigntask"><?php _e( 'Outstanding Task', 'cpm-pro' ); ?> <div><?php echo $count[ __( 'Outstanding', 'cpm-pro' ) ]; ?></div></a>
                </li>
                <li class="<?php if ( $ctab == 'complete' ) echo 'active' ?>">
                    <a href="<?php echo cpm_url_complete_task(); ?>" data-item="complete" data-user="<?php echo $user_id ?>"  class="cpm-my-completetask"><?php _e( 'Completed Task', 'cpm-pro' ); ?> <div ><?php echo $count[ __( 'Completed', 'cpm-pro' ) ]; ?></div></a>
                </li>
            </ul>
            <div class="cpm-col-2 cpm-sm-col-12 cpm-user-select">
                <?php					global $wpdb;
                if ( isset( $_GET['page'] ) && $_GET['page'] === 'cpm_task' ) {                    if ( is_admin() ) {                        $dropdown_users = wp_dropdown_users( array(                            'selected'         => $user_id,                            'class'            => 'cpm-mytask-switch-user',                            'echo'             => false,                            'show_option_none' => __( 'Select an User', 'cpm' )                        ) );                        $dropdown_users = str_replace( '<select', '<select data-tab="' . $ctab . '"', $dropdown_users );                        echo $dropdown_users;                    }					else{						$usr_id = get_current_user_id();										$qrry = "SELECT post.ID, role.user_id FROM ".$wpdb->prefix."posts AS post, ".$wpdb->prefix."cpm_user_role AS role WHERE post.post_author = '".$usr_id."' AND post.ID = role.project_id GROUP BY role.user_id";					$myarr = array();					$dta = $wpdb->get_results($qrry);					foreach($dta as $val){						$myarr[]=$val->user_id;					}					$users = implode(',',$myarr);															$my_users = wp_dropdown_users( array(                            'selected'         => $user_id,                            'class'            => 'cpm-mytask-switch-user',                            'echo'             => false,                            'show_option_none' => __( 'Select an User', 'cpm' ),							'include'          => $users                        ) );						$my_users = str_replace( '<select', '<select data-tab="' . $ctab . '"', $my_users );                        						echo $my_users;					}										                }
                ?>
            </div>
            <div class="clearfix"></div>
        </div>
    </div>

    <div id="cpm-mytask-page-content">
        <?php $task->get_mytask_content( $user_id, $ctab ); ?>
    </div>
</div>
