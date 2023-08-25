<?php
/**
 * Plugin Name: WP Project Manager Pro
 * Plugin URI: https://wedevs.com/wp-project-manager-pro/
 * Description: WordPress Project Management plugin. Manage your projects and tasks, get things done.
 * Author: weDevs
 * Author URI: https://wedevs.com
 * Version: 1.6.7
 * Text Domain: cpm-pro
 * License: GPL2
 */
/**
 * Copyright (c) 2017 weDevs (email: info@wedevs.com). All rights reserved.
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
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * The Pro Class
 */
class WeDevs_CPM_Pro {

    /**
     * @var The single instance of the class
     * @since 0.1
     */
    protected static $_instance = null;

    /**
     * Plugin version
     *
     * @var string
     */
    public $version = '1.6.7';

    /**
     * @var CPM_Router $router
     */
    public $pro_router;
    public $report;
    public $ajax;
    public $admin;
    public $updates;

    function __construct() {
        if ( ! class_exists( 'WeDevs_CPM' ) ) {
            if ( ! current_user_can( 'manage_options' ) ) {
                return;
            }

            add_action( 'admin_notices', array( $this, 'activation_notice' ) );
            add_action( 'wp_ajax_cpm_pro_install_cpm', array( $this, 'install_cpm' ) );
            return;
        }

        $this->define_constants();

        spl_autoload_register( array( $this, 'autoload' ) );

        $this->includes();

        $this->instantiate();

        $this->init_actions();
        $this->init_filters();
        $this->plugins_loaded();
    }

    /**
     * Main CPM Instance
     *
     * @since 1.1
     * @static
     * @see cpm()
     * @return CPMRP - Main instance
     */
    public static function instance() {
        if ( is_null( self::$_instance ) ) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    /**
     * Dokan main plugin activation notice
     *
     * @since 1.6.4
     *
     * @return void
     */
    public function activation_notice() {
        ?>
        <div class="updated" id="cpm-pro-installer-notice" style="padding: 1em; position: relative;">
            <h2><?php _e( 'Your Project Manager Pro is almost ready!', 'cpm-pro' ); ?></h2>

            <?php
                $plugin_file = basename( dirname( __FILE__ ) ) . '/cpm-pro.php';
                $core_plugin_file = 'wedevs-project-manager/cpm.php';
            ?>
            <a href="<?php echo wp_nonce_url( 'plugins.php?action=deactivate&amp;plugin=' . $plugin_file . '&amp;plugin_status=all&amp;paged=1&amp;s=', 'deactivate-plugin_' . $plugin_file ); ?>" class="notice-dismiss" style="text-decoration: none;" title="<?php _e( 'Dismiss this notice', 'cpm-pro' ); ?>"></a>

            <?php if ( file_exists( WP_PLUGIN_DIR . '/' . $core_plugin_file ) && is_plugin_inactive( 'wedevs-project-manager' ) ): ?>
                <p><?php echo sprintf( __( 'You just need to activate the <strong>%s</strong> to make it functional.', 'cpm-pro' ), 'WP Project Manager plugin' ); ?></p>
                <p>
                    <a class="button button-primary" href="<?php echo wp_nonce_url( 'plugins.php?action=activate&amp;plugin=' . $core_plugin_file . '&amp;plugin_status=all&amp;paged=1&amp;s=', 'activate-plugin_' . $core_plugin_file ); ?>"  title="<?php _e( 'Activate this plugin', 'cpm-pro' ); ?>"><?php _e( 'Activate', 'cpm-pro' ); ?></a>
                </p>
            <?php else: ?>
                <p><?php echo sprintf( __( "You just need to install the %sCore Plugin%s to make it functional.", "cpm-pro" ), '<a target="_blank" href="https://wordpress.org/plugins/wedevs-project-manager/">', '</a>' ); ?></p>

                <p>
                    <button id="cpm-pro-installer" class="button"><?php _e( 'Install Now', 'cpm-pro' ); ?></button>
                </p>
            <?php endif ?>
        </div>

        <script type="text/javascript">
            (function ($) {
                $('#cpm-pro-installer-notice #cpm-pro-installer').click( function (e) {
                    e.preventDefault();
                    $(this).addClass('install-now updating-message');
                    $(this).text('<?php echo esc_js( 'Installing...', 'cpm-pro' ); ?>');

                    var data = {
                        action: 'cpm_pro_install_cpm',
                        _wpnonce: '<?php echo wp_create_nonce('cpm-pro-installer-nonce'); ?>'
                    };

                    $.post( ajaxurl, data, function ( response ) {
                        if (response.success) {
                            $('#cpm-pro-installer-notice #cpm-pro-installer').attr('disabled', 'disabled');
                            $('#cpm-pro-installer-notice #cpm-pro-installer').removeClass('install-now updating-message');
                            $('#cpm-pro-installer-notice #cpm-pro-installer').text('<?php echo esc_js( 'Installed', 'cpm-pro' ); ?>');
                            window.location.reload();
                        }
                    });
                });
            })(jQuery);
        </script>
        <?php
    }


    /**
     * Load plugin textdomain
     *
     * @since 1.6.6
     */
    public function load_textdomain() {
        load_plugin_textdomain( 'cpm-pro', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
    }

    /**
    * Install dokan lite
    *
    * @since 1.6.4
    *
    * @return void
    **/
    public function install_cpm() {
        if ( ! isset( $_REQUEST['_wpnonce'] ) || ! wp_verify_nonce( $_REQUEST['_wpnonce'], 'cpm-pro-installer-nonce' ) ) {
            wp_send_json_error( __( 'Error: Nonce verification failed', 'cpm-pro' ) );
        }

        include_once ABSPATH . 'wp-admin/includes/plugin-install.php';
        include_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';

        $plugin = 'wedevs-project-manager';
        $api    = plugins_api( 'plugin_information', array( 'slug' => $plugin, 'fields' => array( 'sections' => false ) ) );

        $upgrader = new Plugin_Upgrader( new WP_Ajax_Upgrader_Skin() );
        $result   = $upgrader->install( $api->download_link );
        activate_plugin( 'wedevs-project-manager/cpm.php' );

        wp_send_json_success();
    }

    /**
    * Includes all files
    *
    * @since 1.6.4
    *
    * @return void
    **/
    public function includes() {
        include_once CPM_PRO_PATH . '/includes/functions.php';

        if ( ! class_exists( 'CPM_Frontend' ) ) {
            require_once dirname( __FILE__ ) . '/frontend/frontend.php';
        }

        if ( ! class_exists( 'CPM_Pro_Files' ) ) {
            require_once dirname( __FILE__ ) . '/class/files.php';
        }
    }

    /**
    * Init all actions
    *
    * @since 2.6.4
    *
    * @return void
    **/
    public function init_actions() {
        add_action( 'cpm_admin_menu', array( $this, 'admin_menu' ) );
        add_action( 'cpm_admin_scripts', array( $this, 'pro_admin_scripts' ) );

        //add_action( 'plugins_loaded', array( $this, 'load_textdomain' ), 10 );

        add_action( 'plugins_loaded', array( $this, 'plugins_loaded' ) );
        add_action( 'cpm_new_project_client_field', array( $this, 'new_project_client_field' ), 10, 2 );
        add_action( 'cpm_update_project_client_field', array( $this, 'update_project_client_field' ), 10, 2 );
        add_action( 'cpm_milestone_form', array( $this, 'milestone_form' ), 10, 2 );
        add_action( 'cpm_message_privicy_field', array( $this, 'message_privicy_field' ), 10, 2 );

        add_action( 'cpm_tasklist_form', array( $this, 'tasklist_form' ), 10, 2 );
        add_action( 'cpm_task_new_form', array( $this, 'task_new_form' ), 10, 3 );

        add_action( 'cpm_tab_file', array( $this, 'include_file' ), 10, 5 );
        

        add_action( 'cpm_filter_project', array( $this, 'filter_project' ) );
        add_action( 'cpm_inside_project_filter', array( $this, 'inside_project_filter' ) );
        add_action( 'before-cpm-task-components', array( $this, 'before_cpm_task_components' ) );
    }

    /**
     * Init all filters
     *
     * @since  2.6.4
     *
     * @return void
     */
    public function init_filters() {
        add_filter( 'cpm_settings_field_general', array( $this, 'settings' ) );

        add_filter( 'cpm_project_total_files', array( $this, 'count_project_file' ), 10, 2 );
        add_filter( 'todo_list_form', array( $this, 'todo_list_form' ) );
        add_filter( 'todo_list_text_editor', array( $this, 'todo_list_text_editor' ) );

        add_filter( 'tdolist_view_private', array( $this, 'tdolist_view_private' ), 10, 2 );
        add_filter( 'create_todolist', array( $this, 'create_todolist' ), 10, 2 );

        add_filter( 'todo_view_private', array( $this, 'todo_view_private' ), 10, 2 );
        add_filter( 'create_todo', array( $this, 'create_todo' ), 10, 2 );

        add_filter( 'cpm_get_task', array( $this, 'cpm_get_task' ), 10, 2 );
        add_filter( 'cpm_get_tasklist', array( $this, 'get_tasklist' ), 10, 2 );

        add_filter( 'task_start_field', array( $this, 'task_start_field' ), 10, 2 );
    }

    function task_start_field( $boolean ) {
        $start_field = cpm_get_option( 'task_start_field', 'cpm_general' );

        return $start_field == 'on' ? true : false;
    }

    function cpm_get_task( $query, $privacy ) {
        if ( ! $privacy ) {
            $privacy_query = array (
                array (
                    'key'     => '_task_privacy',
                    'value'   => 'yes',
                    'compare' => '!='
                ),
            );

            if ( array_key_exists( 'meta_query', $query ) ) {
                $query['meta_query'] = array_merge( $query['meta_query'], $privacy_query );
            } else {
                $query['meta_query'] = $privacy_query;
            }
        }

        return $query;
    }

    function get_tasklist( $query, $privacy ) {

        if ( $privacy  ) {
            $query[ 'meta_query' ] = array (
                array (
                    'key'     => '_tasklist_privacy',
                    'value'   => 'on',
                    'compare' => '!='
                ),
            );
        }

        return $query;
    }

    function create_todo( $boolean, $project_id ) {
        return cpm_user_can_access( $project_id, 'create_todo' );
    }

    function todo_view_private( $boolean, $project_id ) {
        return cpm_user_can_access( $project_id, 'todo_view_private' );
    }

    function create_todolist( $boolean, $project_id ) {
        return cpm_user_can_access( $project_id, 'create_todolist' );
    }

    function tdolist_view_private( $boolean, $project_id ) {
        return cpm_user_can_access( $project_id, 'tdolist_view_private' );
    }

    function todo_list_form( $mixin ) {
        array_push( $mixin, 'CPM_List_Form_Minxin' );

        return $mixin;
    }

    function todo_list_text_editor( $mixin ) {
        array_push( $mixin, 'CPM_Text_Editor_Mixin' );

        return $mixin;
    }

    function before_cpm_task_components() {
        wp_enqueue_script( 'cpm-pro-task-components', plugins_url( 'assets/js/task-components.js', __FILE__ ), array(), false, true );
    }

    function inside_project_filter( $project ) {
        ?>
        <div class="cpm-single-project-search-wrap">
            <input type="text" data-project_id="<?php echo $project->ID; ?>" placeholder="<?php _e( 'Search...', 'cpm-pro' ); ?>" id="cpm-single-project-search">
        </div>
        <?php
    }

    function filter_project() {
        cpm_project_filters();
    }

    function count_project_file( $total_file, $project_id ) {
        global $wpdb;
        $table        = $wpdb->prefix . 'cpm_file_relationship';
        $sql          = "SELECT  count(id) as total_file FROM $table WHERE project_id = $project_id AND is_dir != 1  ";
        $total = $wpdb->get_row( $sql )->total_file;

        $total_file = $total_file + $total ;

        return $total_file;
    }

    function settings( $settings ) {
        $settings[] = array(
            'name'    => 'task_start_field',
            'label'   => __( 'Task start date', 'cpm-pro' ),
            'type'    => 'checkbox',
            'default' => 'off',
            'desc'    => __( 'Enable task start date field' )
        );

        $settings[] = array(
            'name'  => 'logo',
            'label' => __( 'Logo', 'cpm-pro' ),
            'type'  => 'file'
        );
        $settings[] = array(
            'name'    => 'daily_digest',
            'label'   => __( 'Daily Digest', 'cpm-pro' ),
            'type'    => 'checkbox',
            'default' => 'on',
            'desc'    => __( 'Enable Daily Digest', 'cpm-pro' )
        );

        return $settings;
    }

    function include_file( $file, $project_id, $page, $tab, $action ) {
        switch ( $page ) {
            case 'cpm_projects':

                switch ( $tab ) {
                    case 'settings':

                        $file = CPM_PRO_PATH . '/views/project/settings.php';
                        break;

                    case 'files':
                        $file = CPM_PRO_PATH . '/views/files/index.php';
                        break;
                }
                break;

            case 'cpm_calendar':
                $file = CPM_PRO_PATH . '/views/calendar/index.php';
                break;
            case 'cpm_reports':
                switch ( $action ) {
                    case 'download_csv':
                        $file = CPM_PRO_PATH . '/views/report/export_csv.php';
                        break;
                    case 'advancereport':
                        $file = CPM_PRO_PATH . '/views/report/advance_report.php';
                        break;

                    default:
                        $file = CPM_PRO_PATH . '/views/report/index.php';
                        break;
                }

                break;
            case 'cpm_progress':
                $file = CPM_PRO_PATH . '/views/progress/progress.php';
                break;
        }

        return $file;
    }

    /**
     * Load pro css style
     *
     * @return void
     */
    public function pro_admin_scripts() {
        wp_enqueue_style( 'cpm-pro-style', plugins_url( 'assets/css/pro-style.css', __FILE__ ) );
        wp_enqueue_style( 'cpm-pro-tinymce-mention', plugins_url( 'assets/css/tinymce-mention.css', __FILE__ ) );
    }

    /**
     * Load my task scripts
     *
     * @return void
     */
    static function my_task_scripts() {
        cpm()->admin_scripts();
        wp_enqueue_script( 'cpm-vue' );
        wp_enqueue_script( 'cpm_admin' );

        wp_enqueue_script( 'cpm-toastr' );
        wp_enqueue_style( 'cpm-toastr' );

        wp_enqueue_script( 'cpm_mytask', plugins_url( 'assets/js/mytask.js', __FILE__ ), array( 'jquery' ), false, true );
        wp_enqueue_style( 'cpm-pro-style', plugins_url( 'assets/css/pro-style.css', __FILE__ ) );
        // For calender
        wp_enqueue_script( 'fullcalendar', plugins_url( 'assets/js/fullcalendar.min.js', __FILE__ ), array( 'jquery' ), false, true );
        wp_enqueue_script( 'fullcalendar-language', plugins_url( 'assets/js/lang-all.js', __FILE__ ), array( 'fullcalendar' ), false, true );
        wp_enqueue_style( 'fullcalendar', plugins_url( 'assets/css/fullcalendar.css', __FILE__ ) );
        wp_localize_script( 'cpm_admin', 'CPM_Front_Vars', array( 'is_admin' => is_admin() ) );
    }

    /**
     * Load calendar scripts
     *
     * @return void
     */
    public static function calender_scripts() {

        cpm()->admin_scripts();
        wp_enqueue_script( 'cpm-vue' );
        wp_enqueue_script( 'cpm_admin' );

        wp_enqueue_script( 'fullcalendar', plugins_url( 'assets/js/fullcalendar.min.js', __FILE__ ), array( 'jquery' ), false, true );
        wp_enqueue_script( 'fullcalendar-language', plugins_url( 'assets/js/lang-all.js', __FILE__ ), array( 'fullcalendar' ), false, true );
        wp_enqueue_style( 'fullcalendar', plugins_url( 'assets/css/fullcalendar.css', __FILE__ ) );
    }

    /**
     * Load calendar scripts
     *
     * @return void
     */
    static function report_scripts() {

        cpm()->admin_scripts();
        wp_enqueue_script( 'cpm_admin' );

        wp_enqueue_script( 'jquery-ui-core' );
        wp_enqueue_script( 'jquery-ui-datepicker' );
        wp_enqueue_script( 'cpm-vuejs', CPM_PRO_URL . '/assets/js/vue.min.js', '', time(), FALSE );
        wp_enqueue_script( 'report', CPM_PRO_URL . '/assets/js/report.js', array( 'jquery' ), false, true );
        wp_enqueue_script( 'report-vue', CPM_PRO_URL . '/assets/js/report-vue.js', array( 'jquery' ), false, true );
        wp_localize_script( 'report', 'CPM_Vars', array(
            'ajaxurl' => admin_url( 'admin-ajax.php' ),
            'nonce'   => wp_create_nonce( 'cpm_nonce' ),
            'message' => cpm_message(),
        ) );
        wp_enqueue_style( 'jquery-ui', CPM_URL . '/assets/css/jquery-ui-1.9.1.custom.css' );
        wp_enqueue_style( 'cpm_admin', CPM_URL . '/assets/css/admin.css' );
    }

    /**
     * Load progress scripts
     *
     * @return void
     */
    static function progress_scripts() {
        cpm()->admin_scripts();
        wp_enqueue_script( 'cpm-vue' );
        wp_enqueue_script( 'cpm_admin' );
    }

    /**
     * Define cpmrp Constants
     *
     * @since 1.1
     * @return type
     */
    public function define_constants() {
        $this->define( 'CPM_PRO', true );
        $this->define( 'CPM_PRO_PATH', dirname( __FILE__ ) );
        $this->define( 'CPM_PRO_URL', plugins_url( '', __FILE__ ) );
    }

    /**
     * Define constant if not already set
     *
     * @since 1.1
     *
     * @param  string $name
     * @param  string|bool $value
     * @return type
     */
    public function define( $name, $value ) {
        if ( ! defined( $name ) ) {
            define( $name, $value );
        }
    }

    /**
     * Deactivation actions
     *
     * @since 1.1
     *
     * @return void
     */
    public static function deactivate() {
        wp_clear_scheduled_hook( 'cpm_daily_digest' );
    }

    /**
     * Run actions on `plugins_loaded` hook
     *
     * @since 1.1
     *
     * @return void
     */
    public function plugins_loaded() {

        if ( cpm_get_option( 'daily_digest', 'cpm_general' ) == 'off' ) {
            return;
        }
        CPM_Pro_Digest::getInstance();
    }

    public static function cpm_enable_daily_digest() {
        if ( !class_exists('CPM_Upgrade')){
            return;
        }
        CPM_Upgrade::getInstance()->plugin_upgrades();
        wp_schedule_event( time(), 'daily', 'cpm_daily_digest' );
        
    }

    public function instantiate() {
        $this->load_textdomain();
        $this->report = CPM_Pro_Report::getInstance();
        $this->ajax   = CPM_Pro_Ajax::getInstance();

        CPM_Pro_Task::getInstance();
        new CPM_Pro_Mention();

        if ( is_admin() ) {
            $this->admin  = new CPM_Pro_Admin();
            $this->updates = new CPM_Pro_Updates();
        }

        $this->pro_router = CPM_Pro_Router::instance();

        // Load CPM ERP module and active it

        $this->pro_router-> cpm_pro_integrate();

        cpmerp_init();
    }

    /**
     * Autoload class files on demand
     *
     * @param string $class requested class name
     */
    public function autoload( $class ) {

        $name = explode( '_', $class );

        if ( isset( $name[2] ) ) {
            $class_name = strtolower( $name[2] );
            $filename   = dirname( __FILE__ ) . '/class/' . $class_name . '.php';
            if ( file_exists( $filename ) ) {
                require_once $filename;
            }
        }
    }

    public function task_new_form($project_id, $task_id = 0 ) {
        // if ( cpm_user_can_access( $project_id, 'todo_view_private' ) ) {
            ?>
            <div class="cpm-make-privacy" v-if="todo_view_private">
                <label>

                    <input type="checkbox" v-model="task_privacy" value="yes" name="task_privacy">
                    <?php _e( 'Private', 'cpm-pro' ); ?>
                </label>
            </div>
            <?php
        // }
    }

    public function tasklist_form() {
        ?>
        <div class="item cpm-make-privacy" v-if="tdolist_view_private">
            <input id="cpm-privat-checkbox" v-model="tasklist_privacy" type="checkbox"  value="on" name="tasklist_privacy">
            <label for="cpm-privat-checkbox"><?php _e( 'Private', 'cpm-pro' ); ?></label>

        </div>
        <?php
    }

    public function message_privicy_field( $project_id, $message ) {

        if ( cpm_user_can_access( $project_id, 'msg_view_private' ) ) {
            ?>
            <div class="cpm-make-privacy">
                <label>
                    <?php
                    $message_id = isset( $message->ID ) ? $message->ID : '';
                    $check_val  = get_post_meta( $message_id, '_message_privacy', true );
                    $check_val  = empty( $check_val ) ? '' : $check_val;
                    ?>
                    <input type="checkbox" <?php checked( 'yes', $check_val ); ?> value="yes" name="message_privacy">
                    <?php _e( 'Private', 'cpm-pro' ); ?>
                </label>
            </div>
            <?php
        }
    }

    public function milestone_form( $project_id, $milestone ) {

        if ( cpm_user_can_access( $project_id, 'milestone_view_private' ) ) {
            ?>
            <div class="cpm-make-privacy">
                <label>
                    <?php
                    $milestone_ID = isset( $milestone->ID ) ? $milestone->ID : '';
                    $check_val    = get_post_meta( $milestone_ID, '_milestone_privacy', true );
                    $check_val    = empty( $check_val ) ? '' : $check_val;
                    ?>
                    <input type="checkbox" <?php checked( 'yes', $check_val ); ?> value="yes" name="milestone_privacy">
                    <?php _e( 'Private', 'cpm-pro' ); ?>
                </label>
            </div>
            <?php
        }
    }

    public function update_project_client_field( $array, $name ) {
        ?>
        <td>
            <input type="radio" <?php checked( 'client', $array['role'] ); ?> id="cpm-client-<?php echo $name; ?>" name="role[<?php echo $array['id']; ?>]" value="client">
            <label for="cpm-client-<?php echo $name; ?>"><?php _e( 'Client', 'cpm-pro' ); ?></label>
        </td>
        <?php
    }

    public function new_project_client_field( $user_id, $name ) {
        ?>
        <td>

            <input type="radio" id="cpm-client-<?php echo $name; ?>" name="role[<?php echo $user_id; ?>]" value="client">
            <label for="cpm-client-<?php echo $name; ?>"><?php _e( 'Client', 'cpm-pro' ); ?></label>
        </td>

        <?php
    }

    public function admin_menu( $capability ) {

        $capability   = 'read'; //minimum level: subscriber
        $cpm          = cpm();
        $uid          = wp_get_current_user()->ID;
        $count_task   = CPM_Pro_Task::getInstance()->mytask_count( $uid );
        $current_task = isset( $count_task['Current'] ) ? $count_task['Current'] : 0;
        $outstanding  = isset( $count_task['Outstanding'] ) ? $count_task['Outstanding'] : 0;
        $active_task  = $current_task + $outstanding;

        $mytask_text = __( 'My Tasks', 'cpm-pro' );

        if ( $active_task ) {
            $mytask_text = sprintf( __( 'My Tasks %s', 'cpm-pro' ), '<span class="awaiting-mod count-1"><span class="pending-count">' . $active_task . '</span></span>' );
        }

        //$hook = add_menu_page( __( 'Project Manager', 'cpm-pro' ), __( 'Project Manager', 'cpm-pro' ), $capability, 'cpm_projects', array($cpm, 'admin_page_handler'), 'dashicons-networking', 3 );
        //add_submenu_page( 'cpm_projects', __( 'Projects', 'cpm-pro' ), __( 'Projects', 'cpm-pro' ), $capability, 'cpm_projects', array($cpm, 'admin_page_handler') );
        $hook_my_task  = add_submenu_page( 'cpm_projects', __( 'My Tasks', 'cpm-pro' ), $mytask_text, $capability, 'cpm_task', array( $this, 'my_task' ) );
        $hook_calender = add_submenu_page( 'cpm_projects', __( 'Calendar', 'cpm-pro' ), __( 'Calendar', 'cpm-pro' ), $capability, 'cpm_calendar', array( $cpm, 'admin_page_handler' ) );


        if ( cpm_can_manage_projects() ) {
            $hook_reports = add_submenu_page( 'cpm_projects', __( 'Reports', 'cpm-pro' ), __( 'Reports', 'cpm-pro' ), $capability, 'cpm_reports', array( $cpm, 'admin_page_handler' ) );
            add_action( 'admin_print_styles-' . $hook_reports, array( $this, 'report_scripts' ) );

            $hook_progress = add_submenu_page( 'cpm_projects', __( 'Progress', 'cpm-pro' ), __( 'Progress', 'cpm-pro' ), $capability, 'cpm_progress', array( $cpm, 'admin_page_handler' ) );
            add_action( 'admin_print_styles-' . $hook_progress, array( $this, 'progress_scripts' ) );
        }

        //add_submenu_page( 'cpm_projects', __( 'Add-ons', 'cpm-pro' ), __( 'Add-ons', 'cpm-pro' ), 'manage_options', 'cpm_addons', array($cpm, 'admin_page_addons') );
        add_action( 'admin_print_styles-' . $hook_my_task, array( $this, 'my_task_scripts' ) );
        add_action( 'admin_print_styles-' . $hook_calender, array( $this, 'calender_scripts' ) );
    }

    /**
     * Render my tasks page
     *
     * @since 0.5
     * @return void
     */
    public function my_task() {
        $this->pro_router->my_task();
    }

    /**
     * Create Frontend Page if they not exist
     *
     * @since  1.4.3
     *
     * @return void
     */
    public static function create_pages() {

        if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
            return;
        }

        $page_data = array(
            'post_status'    => 'publish',
            'post_author'    => 1,
            'comment_status' => 'close',
            'ping_status'    => 'close',
            'post_type'      => 'page',
            'post_parent'    => 0,
        );

        // Create Project Page
        $cpm_pages = get_option( 'cpm_page' );

        if ( ! $cpm_pages['project'] ) {

            $page_title = __( 'Projects', 'cpm-pro' );

            $page_data['post_title']   = $page_title;
            $page_data['post_content'] = "[cpm]";

            $e = wp_insert_post( $page_data, true );

            if ( ! is_wp_error( $e ) ) {
                $cpm_pages['project'] = $e;
            }
        }

        // Create My Task page
        if ( ! $cpm_pages['my_task'] ) {
            $page_title                = __( 'My Tasks', 'cpm-pro' );
            $page_data['post_title']   = $page_title;
            $page_data['post_content'] = "[cpm_my_task]";

            $e = wp_insert_post( $page_data, true );

            if ( ! is_wp_error( $e ) ) {
                $cpm_pages['my_task'] = $e;
            }
        }

        // Create My Calender page
        if ( ! $cpm_pages['calendar'] ) {

            $page_title                = __( 'My Calender', 'cpm-pro' );
            $page_data['post_title']   = $page_title;
            $page_data['post_content'] = "[cpm_calendar]";

            $e = wp_insert_post( $page_data, true );

            if ( ! is_wp_error( $e ) ) {
                $cpm_pages['calendar'] = $e;
            }
        }

        update_option( 'cpm_page', $cpm_pages );
    }

}

/**
 * Returns the main instance.
 *
 * @since  1.1
 * @return WeDevs_CPM
 */
function cpmpro() {
    return WeDevs_CPM_Pro::instance();
}

/**
* CPM load function
*
* @since 0.0.1
*
* @return void
**/
function cpm_pro_load() {
    cpmpro();
}

add_action( 'plugins_loaded', 'cpm_pro_load', 10 );

register_activation_hook( __FILE__, array( 'WeDevs_CPM_Pro', 'create_pages' ) );
register_activation_hook( __FILE__, array( 'WeDevs_CPM_Pro', 'cpm_enable_daily_digest' ) );
register_deactivation_hook( __FILE__, array( 'WeDevs_CPM_Pro', 'deactivate' ) );
