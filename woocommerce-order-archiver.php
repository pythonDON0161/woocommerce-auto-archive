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
        'WooCommerce Order Archiver',
        'Order Archiver',
        'manage_options',
        'order-archiver',
        'woa_settings_page',
        'dashicons-archive',
        20
    );

    add_submenu_page(
    'order-archiver', 
    'Manual Archive', 
    'Manual Archive',
    'manage_options', 
    'woa-manual-archive', 
    'woa_manual_archive_page'
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

// Include necessary files
require_once plugin_dir_path(__FILE__) . 'includes/settings-page.php';
require_once plugin_dir_path(__FILE__) . 'includes/manual-archive.php';
/*require_once plugin_dir_path(__FILE__) . 'includes/filter-page.php';*/

require_once plugin_dir_path(__FILE__) . 'includes/functions/archive-orders.php'; // Adjust the path as necessary
require_once plugin_dir_path(__FILE__) . 'includes/functions/enque_js_files.php'; //add ajax fucntionality etc 
require_once plugin_dir_path(__FILE__) . 'includes/cron-job.php';
require_once plugin_dir_path(__FILE__) . 'includes/database-handler.php';  // Optional if you want to manage DB separately
