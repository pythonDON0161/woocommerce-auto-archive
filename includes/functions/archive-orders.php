<?php

// Add action hooks for handling the form submission
add_action('admin_post_archive_orders_action', 'handle_archive_orders');
add_action('admin_post_nopriv_archive_orders_action', 'handle_archive_orders');

// Output to confirm the archive orders file is loaded
echo 'Archive orders file loaded successfully.';

// Function to archive orders
function archive_orders($order_ids) {
    global $wpdb;

    // Output to confirm the archive function is triggered
    echo 'The archive function has been triggered.';

    // Validate input
    if (empty($order_ids) || !is_array($order_ids)) {
        return 'No orders selected for archiving.';
    }

    // Define the backup and archive table names
    $backup_table = "{$wpdb->prefix}wc_orders_backup";
    $archive_table = "{$wpdb->prefix}wc_order_archives";

    // Create backup table if it doesn't exist
    if ($wpdb->get_var("SHOW TABLES LIKE '$backup_table'") != $backup_table) {
        $create_backup_table = "CREATE TABLE $backup_table LIKE {$wpdb->prefix}wc_orders";
        if ($wpdb->query($create_backup_table) === false) {
            return 'Error creating backup table: ' . $wpdb->last_error;
        }
    }

    // Backup current orders into backup table
    $insert_backup = "INSERT INTO $backup_table SELECT * FROM {$wpdb->prefix}wc_orders WHERE id IN (" . implode(',', array_map('intval', $order_ids)) . ")";
    if ($wpdb->query($insert_backup) === false) {
        return 'Error backing up orders: ' . $wpdb->last_error;
    }

    // Create archive table if it doesn't exist
    if ($wpdb->get_var("SHOW TABLES LIKE '$archive_table'") != $archive_table) {
        $create_archive_table = "CREATE TABLE $archive_table LIKE {$wpdb->prefix}wc_orders";
        if ($wpdb->query($create_archive_table) === false) {
            return 'Error creating archive table: ' . $wpdb->last_error;
        }
    }

    // Move orders to archive table
    $insert_archive = "INSERT INTO $archive_table SELECT * FROM {$wpdb->prefix}wc_orders WHERE id IN (" . implode(',', array_map('intval', $order_ids)) . ")";
    if ($wpdb->query($insert_archive) === false) {
        return 'Error archiving orders: ' . $wpdb->last_error;
    }

    // Remove orders from the original table
    $delete_orders = "DELETE FROM {$wpdb->prefix}wc_orders WHERE id IN (" . implode(',', array_map('intval', $order_ids)) . ")";
    if ($wpdb->query($delete_orders) === false) {
        return 'Error deleting orders from original table: ' . $wpdb->last_error;
    }

    return 'Orders successfully archived.';
}

// Handle form submission
function handle_archive_orders() {
    // Check if the form was submitted with the required data
    if (!isset($_POST['archive_order_ids']) || empty($_POST['archive_order_ids'])) {
        wp_die('<div style="color: red;">No order IDs provided. Please try again.</div>');
    }

    // Sanitize and process the order IDs
    $order_ids = explode(',', sanitize_text_field($_POST['archive_order_ids']));

    // Perform the archiving process
    $result = archive_orders($order_ids);
    if ($result === 'Orders successfully archived.') {
        echo '<div style="color: green;">' . $result . '</div>';
    } else {
        echo '<div style="color: red;">' . $result . '</div>';
    }

    wp_die(); // Stops further execution to avoid rendering the rest of WordPress
}
