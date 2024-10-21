<?php
/**
 * Plugin Name: WooCommerce Order Archiver
 * Description: A plugin to archive WooCommerce orders older than a selected age to custom database tables.
 * Version: 1.0
 * Author: Your Name
 * Text Domain: woocommerce-order-archiver
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}


// Activation hook to set up cron
register_activation_hook(__FILE__, 'woa_activate_plugin');
function woa_activate_plugin() {
    woa_update_cron_schedule(); // Schedule cron job on activation
}

// Deactivation hook to clear cron
register_deactivation_hook(__FILE__, 'woa_deactivate_plugin');
function woa_deactivate_plugin() {
    $timestamp = wp_next_scheduled('woa_archive_old_orders');
    if ($timestamp) {
        wp_unschedule_event($timestamp, 'woa_archive_old_orders');
    }
}


/************** START BY BUILDING MENU ********************/

// Add admin menu
add_action('admin_menu', 'woa_register_admin_page');

function woa_register_admin_page() {
    add_menu_page(
        'WooCommerce Order Archiver',  // Page title that appears in the browser tab
        'Order Archiver',              // Menu title shown in the WordPress admin sidebar
        'manage_options',              // Capability required to access this menu
        'order-archiver',              // Slug for the menu page URL  
        'woa_settings_page',           // Function to display the content of the page
        'dashicons-archive',           // Icon displayed in the admin sidebar 
        20                             // Position in the admin menu 
    ); 

    add_submenu_page(
    'order-archiver',                  // Slug of the parent menu item
    'Manual Archive',                  // Page title shown in the browser tab for the submenu
    'Manual Archive',                  // Title of the submenu shown in the admin sidebar
    'manage_options',                  // Capability required to access this submenu 
    'woa-manual-archive',              // Slug for the submenu page URL
    'woa_manual_archive_page'          // Function to display the content of the submenu page
);

/*
add_submenu_page(
    'order-archiver', 
    'Filter', 
    'Filter',
    'manage_options', 
    'woa-filter-archive', 
    'woa_filter__page'
);
*/

}

function woa_enqueue_individual_styles($hook) {
    // Only enqueue the styles if we're on the plugin's admin pages
    if ($hook === 'toplevel_page_order-archiver' || $hook ==='order-archiver_page_woa-manual-archive') {
        wp_enqueue_style('sb-admin-2', plugin_dir_url(__FILE__) . 'assets/css/sb-admin-2.css', array(), '1.0.0', 'all');
        wp_enqueue_style('sb-admin-2-min', plugin_dir_url(__FILE__) . 'assets/css/sb-admin-2.min.css', array(), '1.0.0', 'all');
    }
}
add_action('admin_enqueue_scripts', 'woa_enqueue_individual_styles');





// Include necessary files
require_once plugin_dir_path(__FILE__) . 'includes/settings-page.php';
require_once plugin_dir_path(__FILE__) . 'includes/manual-archive.php';
/*require_once plugin_dir_path(__FILE__) . 'includes/filter-page.php';*/
require_once plugin_dir_path(__FILE__) . 'includes/functions/archive-orders.php';  // Adjust the path as necessary
require_once plugin_dir_path(__FILE__) . 'includes/functions/enque_js_files.php';  //add ajax functionality etc 
require_once plugin_dir_path(__FILE__) . 'includes/functions/get_filtered_orders.php'; 

require_once plugin_dir_path(__FILE__) . 'includes/cron-job.php';
require_once plugin_dir_path(__FILE__) . 'includes/database-handler.php';  // Optional if you want to manage DB separately

