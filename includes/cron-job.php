<?php
// Hook for cron job scheduling
add_action('woa_archive_old_orders', 'woa_archive_old_orders_function');

// Update WP-Cron schedule based on user settings
function woa_update_cron_schedule() {
    $frequency = get_option('woa_cron_frequency', 'daily');

    // Clear existing cron job
    $timestamp = wp_next_scheduled('woa_archive_old_orders');
    if ($timestamp) {
        wp_unschedule_event($timestamp, 'woa_archive_old_orders');
    }

    // Schedule new cron job
    if (!wp_next_scheduled('woa_archive_old_orders')) {
        wp_schedule_event(time(), $frequency, 'woa_archive_old_orders');
    }
}

// The function to archive old orders
function woa_archive_old_orders_function() {
    global $wpdb;

    // Get the user-defined age limit
    $age_limit = get_option('woa_order_age_limit', 12); // Default to 12 months
    $cutoff_date = date('Y-m-d H:i:s', strtotime("-$age_limit months"));

    // Select old orders based on the age limit
    $orders = $wpdb->get_results("
        SELECT ID, post_type, post_content FROM {$wpdb->prefix}posts
        WHERE post_type IN ('shop_order', 'shop_order_refund') 
        AND post_date < '$cutoff_date'
    ");

    // Example logic to archive orders and comments (more detailed logic can be added)
    foreach ($orders as $order) {
        // Archive the order and comments logic...
    }
}
