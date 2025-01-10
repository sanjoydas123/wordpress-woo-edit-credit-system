<?php
if (!defined('ABSPATH')) exit; // Exit if accessed directly

class DB_Tables
{

    // Create Tables
    public function create_tables()
    {
        $this->create_credit_table();
        $this->create_edit_requests_table();
    }

    // Video Edit Credits Table
    private function create_credit_table()
    {
        global $wpdb;
        $table_name = $wpdb->prefix . 'video_edit_credits';
        $charset_collate = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE $table_name (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            user_id mediumint(9) NOT NULL,
            order_id mediumint(9) NOT NULL,
            product_id mediumint(9) NOT NULL,
            credits int(11) NOT NULL,
            remaining_credits int(11) NOT NULL,
            created_at datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
            PRIMARY KEY (id)
        ) $charset_collate;";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }

    // Edit Requests Table
    private function create_edit_requests_table()
    {
        global $wpdb;
        $table_name = $wpdb->prefix . 'edit_requests';
        $charset_collate = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE $table_name (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            user_id mediumint(9) NOT NULL,
            order_id mediumint(9) NOT NULL,
            project_name text NOT NULL,
            footage_link text NOT NULL,
            status varchar(50) DEFAULT 'Pending' NOT NULL,
            created_at datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
            PRIMARY KEY (id)
        ) $charset_collate;";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }

    // Deactivation: Cleanup (optional)
    public function deactivate()
    {
        // global $wpdb;
        // $table_name = $wpdb->prefix . 'video_edit_credits';
        // $sql = "DROP TABLE IF EXISTS $table_name";
        // $wpdb->query($sql);

        // $table_name = $wpdb->prefix . 'edit_requests';
        // $sql = "DROP TABLE IF EXISTS $table_name";
        // $wpdb->query($sql);
    }
}
