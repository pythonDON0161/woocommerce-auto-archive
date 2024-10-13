<?php

function enqueue_archive_orders_script() {
    // Register the script
    wp_register_script('archive-orders-script', plugin_dir_URL(__DIR__). 'js/archive-orders.js', array('jquery'), null, true);
    
    // Pass AJAX URL and nonce to the script
    wp_localize_script('archive-orders-script', 'archiveOrdersAjax', array(
        'ajax_url' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('archive_orders_nonce')
    ));
    
    // Enqueue the script
    wp_enqueue_script('archive-orders-script');
}
add_action('admin_enqueue_scripts', 'enqueue_archive_orders_script');