<?php
/**
 * Plugin Name: Job Listing
 * Version: 1.0
 */

if (!defined('ABSPATH')) {
    exit;
}

register_activation_hook(__FILE__, 'job_listing_activate');

function job_listing_activate() {
    global $wpdb;   

    $charset_collate = $wpdb->get_charset_collate();

    $table_name = $wpdb->prefix . 'job_listings';
    $sql = "CREATE TABLE $table_name (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        title varchar(200) NOT NULL,
        company varchar(100) NOT NULL,
        location varchar(100) NOT NULL,
        salary varchar(100) NOT NULL,
        PRIMARY KEY (id)
    ) $charset_collate;";

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

add_action('admin_menu', 'job_listing_admin_menu');

function job_listing_admin_menu() {
    add_menu_page('Job Listing', 'Job Listing', 'manage_options', 'job-listing', 'job_listing_admin_page', 'dashicons-businessman', 30);
}

function job_listing_admin_page() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'job_listings';

    if (isset($_POST['submit_job'])) {
        $wpdb->insert($table_name, array(
            'title' => sanitize_text_field($_POST['title']),
            'company' => sanitize_text_field($_POST['company']),
            'location' => sanitize_text_field($_POST['location']),
            'salary' => sanitize_text_field($_POST['salary'])
        ));
        echo '<div class="notice notice-success"><p>Job added!</p></div>';
    }

    if (isset($_POST['delete_job'])) {
        $wpdb->delete($table_name, array('id' => intval($_POST['job_id'])));
        echo '<div class="notice notice-success"><p>Job deleted!</p></div>';
    }

    $jobs = $wpdb->get_results("SELECT * FROM $table_name ORDER BY id DESC");

    echo '<div class="wrap">';
    echo '<h1>Job Listing</h1>';

    echo '<h2>Add New Job</h2>';
    echo '<form method="post">';
    echo '<p><label>Job Title: <input type="text" name="title" required></label></p>';
    echo '<p><label>Company: <input type="text" name="company" required></label></p>';
    echo '<p><label>Location: <input type="text" name="location" required></label></p>';
    echo '<p><label>Salary: <input type="text" name="salary" required></label></p>';
    echo '<p><input type="submit" name="submit_job" value="Add Job" class="button button-primary"></p>';
    echo '</form>';

    echo '<hr>';

    echo '<h2>Current Jobs</h2>';
    if ($jobs) {
        foreach ($jobs as $job) {
            echo '<div style="border:1px solid #ddd; padding:15px; margin:10px 0;">';
            echo '<h3>' . esc_html($job->title) . '</h3>';
            echo '<p><strong>Company:</strong> ' . esc_html($job->company) . '</p>';
            echo '<p><strong>Location:</strong> ' . esc_html($job->location) . '</p>';
            echo '<p><strong>Salary:</strong> ' . esc_html($job->salary) . '</p>';
            echo '<form method="post" style="display:inline;">';
            echo '<input type="hidden" name="job_id" value="' . $job->id . '">';
            echo '<input type="submit" name="delete_job" value="Delete" class="button button-small" onclick="return confirm(\'Delete this job?\')">';
            echo '</form>';
            echo '</div>';
        }
    } else {
        echo '<p>No jobs found.</p>';
    }

    echo '</div>';
}

add_shortcode('job_listings', 'display_job_listings');

function display_job_listings() {
    global $wpdb;
    $jobs = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}job_listings ORDER BY id DESC");

    $output = '<div class="job-listings">';
    $output .= '<h2>Available Jobs</h2>';

    if ($jobs) {
        foreach ($jobs as $job) {
            $output .= '<div class="job-item">';
            $output .= '<h3>' . esc_html($job->title) . '</h3>';
            $output .= '<p><strong>Company:</strong> ' . esc_html($job->company) . '</p>';
            $output .= '<p><strong>Location:</strong> ' . esc_html($job->location) . '</p>';
            $output .= '<p><strong>Salary:</strong> ' . esc_html($job->salary) . '</p>';
            $output .= '<button class="apply-btn" onclick="showForm(' . $job->id . ')">Apply</button>';
            $output .= '</div>';
        }
    } else {
        $output .= '<p>No jobs available.</p>';
    }

    $output .= '</div>';

    $output .= '<div id="applicationForm" style="display:none; position:fixed; top:50%; left:50%; transform:translate(-50%,-50%); background:white; padding:20px; border:2px solid #333; z-index:1000;">';
    $output .= '<h3>Apply for Job</h3>';
    $output .= '<form method="post">';
    $output .= '<input type="hidden" id="job_id" name="job_id" value="">';
    $output .= '<p><label>Name: <input type="text" name="applicant_name" required></label></p>';
    $output .= '<p><label>Email: <input type="email" name="applicant_email" required></label></p>';
    $output .= '<p><label>Phone: <input type="tel" name="applicant_message" required></label></p>';
    $output .= '<p><button type="submit">Submit</button> <button type="button" onclick="hideForm()">Cancel</button></p>';
    $output .= '</form>';
    $output .= '</div>';

    return $output;
}

if (isset($_POST['applicant_name']) && isset($_POST['applicant_email']) && isset($_POST['applicant_message']) && isset($_POST['job_id'])) {
    global $wpdb;
    $wpdb->insert($wpdb->prefix . 'job_applications', array(
        'job_id' => intval($_POST['job_id']),
        'applicant_name' => sanitize_text_field($_POST['applicant_name']),
        'applicant_email' => sanitize_email($_POST['applicant_email']),
        'applicant_message' => sanitize_text_field($_POST['applicant_message'])
    ));
    echo '<script>alert("Application submitted!");</script>';
}

add_action('wp_head', 'job_listing_styles');
add_action('wp_footer', 'job_listing_scripts');

function job_listing_styles() {
    echo '<link rel="stylesheet" href="' . plugin_dir_url(__FILE__) . 'job-listing.css">';
}

function job_listing_scripts() {
    echo '<script>
    function showForm(jobId) {
        document.getElementById("job_id").value = jobId;
        document.getElementById("applicationForm").style.display = "block";
    }
    function hideForm() {
        document.getElementById("applicationForm").style.display = "none";
    }
    </script>';
}