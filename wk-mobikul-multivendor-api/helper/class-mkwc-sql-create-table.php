<?php

/**
 * @author Webkul
 * @version 1.0.0
 * This file handles all sql table functions.
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'Mkwc_Sql_Create_Table' ) ) {
    class Mkwc_Sql_Create_Table
    {
        protected $wpdb;

        public function __construct()
        {
            global $wpdb;

            $this->wpdb = $wpdb;

            $this->mkwc_banner_sql_table();

            $this->mkwc_carousels_sql_table();
            
            $this->mkwc_notification_sql_table();
        }

        /**
         * Create banner table
         * @param
         * @return
         */
        public function mkwc_banner_sql_table()
        {
            $table_name 	= $this->wpdb->prefix . 'mp_banner';

            $table_check 	= $this->wpdb->get_var("SHOW TABLES LIKE '$table_name'");

            if( $table_check != $table_name ){
                 //table not in database. Create new table
                 $charset_collate = $this->wpdb->get_charset_collate();

                 $sql = "CREATE TABLE $table_name (
                      id int(100) NOT NULL AUTO_INCREMENT,
                      image varchar(250) NOT NULL,
                      term_id int(100) NOT NULL,
                      banner_type varchar(200) NOT NULL,
                      status varchar(200) NOT NULL,
                      UNIQUE KEY id (id)
                 ) $charset_collate;";

                 require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

                 dbDelta( $sql );
            }
        }

        /**
         * Create carousels table
         * @param
         * @return
         */
        public function mkwc_carousels_sql_table()
        {
            $table_name 	= $this->wpdb->prefix . 'mp_carousels';

            $table_check 	= $this->wpdb->get_var("SHOW TABLES LIKE '$table_name'");

            if( $table_check != $table_name ){
                //table not in database. Create new table
                $charset_collate = $this->wpdb->get_charset_collate();

                $sql = "CREATE TABLE $table_name (
                    id int(100) NOT NULL AUTO_INCREMENT,
                    title varchar(250) NOT NULL,
                    carousel_ids longtext NOT NULL,
                    type varchar(200) NOT NULL,
                    status varchar(200) NOT NULL,
                    UNIQUE KEY id (id)
                ) $charset_collate;";

                require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

                dbDelta( $sql );
            }
        }

        public function mkwc_notification_sql_table()
        {
            $table_name 	= $this->wpdb->prefix . 'mp_notification';

            $table_check 	= $this->wpdb->get_var("SHOW TABLES LIKE '$table_name'");

            if( $table_check != $table_name ){
                 //table not in database. Create new table
                 $charset_collate = $this->wpdb->get_charset_collate();

                 $sql = "CREATE TABLE $table_name (
                      id int(100) NOT NULL AUTO_INCREMENT,
                      banner varchar(250) NOT NULL,
                      term_id int(100) NOT NULL,
                      title varchar(200) NOT NULL,
                      content varchar(250) NOT NULL,
                      type varchar(250) NOT NULL,
                      status varchar(250) NOT NULL,
                      UNIQUE KEY id (id)
                 ) $charset_collate;";

                 require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

                 dbDelta( $sql );
            }
        }
    }
    new Mkwc_Sql_Create_Table();
}
