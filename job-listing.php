<?php

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Create database tables when plugin is activated
register_activation_hook(__FILE__, 'job_listing_activate');

function job_listing_activate() {
    global $wpdb;   

    $charset_collate = $wpdb->get_charset_collate();

    // Job listings table
    $table_name = $wpdb->prefix . 'job_listings';
    $sql = "CREATE TABLE $table_name (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        title varchar(200) NOT NULL,
        company varchar(100) NOT NULL,
        location varchar(100) NOT NULL,
        salary varchar(100) NOT NULL,
        PRIMARY KEY (id)
    ) $charset_collate;";

    // Job applications table
    $table_name2 = $wpdb->prefix . 'job_applications';
    $sql2 = "CREATE TABLE $table_name2 (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        job_id mediumint(9) NOT NULL,
        applicant_name varchar(100) NOT NULL,
        applicant_email varchar(100) NOT NULL,
        applicant_message text NOT NULL,
        date date DEFAULT CURRENT_DATE,
        PRIMARY KEY (id)
    ) $charset_collate;";

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
    dbDelta($sql2);
}