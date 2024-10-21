<?php

// Process the form submission and generate the query
function get_filtered_orders() {

    global $wpdb;
     // Check user capabilities if needed
     if (!current_user_can('manage_options')) {
        wp_send_json_error('Permission denied');
        return;
    }
    
    // Retrieve and sanitize user inputs
    $start_date = isset($_POST['start_date']) ? sanitize_text_field($_POST['start_date']) : '';
    $end_date = isset($_POST['end_date']) ? sanitize_text_field($_POST['end_date']) : '';
    $status = isset($_POST['status']) ? sanitize_text_field($_POST['status']) : '';
    $min_total = isset($_POST['min_total']) ? floatval($_POST['min_total']) : '';
    $max_total = isset($_POST['max_total']) ? floatval($_POST['max_total']) : '';
    $payment_method = isset($_POST['payment_method']) ? sanitize_text_field($_POST['payment_method']) : '';
    $product_filter = isset($_POST['product_filter']) ? sanitize_text_field($_POST['product_filter']) : '';
    $product_ids = isset($_POST['product_ids']) ? array_map('intval', $_POST['product_ids']) : [];

    // Validate that at least start date or end date is provided
    if ( empty($start_date) && empty($end_date) ) {
        return [
            'results' => [],
            'query' => '',
            'error_message' => 'Please enter at least a Start Date or End Date.'
        ];
    }

    // Build the base query
    $query = "
        SELECT 
            o.id AS order_id,
            o.date_created_gmt,
            CONCAT(a.first_name, ' ', a.last_name) AS customer_name,
            o.status,
            o.payment_method,
            o.total_amount
        FROM 
            {$wpdb->prefix}wc_orders o
        LEFT JOIN 
            {$wpdb->prefix}wc_order_addresses a ON o.id = a.order_id
        LEFT JOIN 
            {$wpdb->prefix}wc_order_product_lookup p ON o.id = p.order_id
        WHERE 
            1=1
    ";

    // Add conditions only if user input is provided
    $query_params = [];
    if ( !empty($start_date) ) {
        $query .= " AND o.date_created_gmt >= %s";
        $query_params[] = $start_date;
    }
    if ( !empty($end_date) ) {
        $query .= " AND o.date_created_gmt <= %s";
        $query_params[] = $end_date;
    }
    if ( !empty($status) ) {
        $query .= " AND o.status = %s";
        $query_params[] = $status;
    }
    if ( $min_total !== '' ) {
        $query .= " AND o.total_amount >= %f";
        $query_params[] = $min_total;
    }
    if ( $max_total !== '' && $max_total > 0 ) {
        $query .= " AND o.total_amount <= %f";
        $query_params[] = $max_total;
    }
    if ( !empty($payment_method) ) {
        $query .= " AND o.payment_method = %s";
        $query_params[] = $payment_method;
    }
    if ( !empty($product_filter) && !empty($product_ids) ) {
        if ($product_filter === 'contains') {
            $query .= " AND p.product_id IN (" . implode(',', array_map('intval', $product_ids)) . ")";
        } elseif ($product_filter === 'not_contains') {
            $query .= " AND p.product_id NOT IN (" . implode(',', array_map('intval', $product_ids)) . ")";
        }
    }

    // Prepare the query
    if ( !empty($query_params) ) {
        $prepared_query = $wpdb->prepare($query, ...$query_params);
    } else {
        $prepared_query = $query; // No parameters to prepare, just use the base query
    }

    // Execute the query
    $results = $wpdb->get_results($prepared_query);

    // Return the results, the query, and an empty error message
    return [
        'results' => $results,
        'query' => $prepared_query,
        'error_message' => ''
    ];
}



// Display the results and query used
function display_results_and_query($data) {
    // Check for errors first
    if ( ! empty($data['error_message']) ) {
        echo '<p style="color: red;">' . esc_html($data['error_message']) . '</p>';
        return; // Exit the function to prevent further output
    }

    if ( empty($data['results']) ) {
        echo '<p>No orders found matching the criteria.</p>';
    } else {

        // Display results in a table

      //  echo '<div id="archive-message"></div>';
        echo '<div class="card shadow mb-4">';
        echo' <div class="card-header py-3"><h6 class="m-0 font-weight-bold text-primary">Filtered Results</h6></div>';
        echo '<div class="card-body">';
        echo '<div class="table-responsive"><table border="1" cellpadding="5" cellspacing="0" style="background-color: white;">';
        echo '<thead> Filter Results </thead> ';
        echo '<tr>';
        echo '<th>Order Number</th>';
        echo '<th>Date</th>';
        echo '<th>Customer Name</th>';
        echo '<th>Order Status</th>';
        echo '<th>Payment Method</th>';
        echo '<th>Value</th>';
        echo '</tr>';

        foreach ($data['results'] as $order) {
            echo '<tr>';
            echo '<td>' . esc_html($order->order_id) . '</td>';
            echo '<td>' . esc_html(date('F j, Y', strtotime($order->date_created_gmt))) . '</td>';
            echo '<td>' . esc_html($order->customer_name) . '</td>';
            echo '<td>' . esc_html(ucfirst(str_replace('wc-', '', $order->status))) . '</td>'; // Format status to remove "wc-" prefix and capitalize first letter
            echo '<td>' . esc_html($order->payment_method) . '</td>';
            echo '<td>' . esc_html(number_format($order->total_amount, 2)) . '</td>';
            echo '</tr>';
        }
        echo '</table>';
        echo '</div>';
        echo '</div>';
        echo '</div>';
    }  

    // Add "Archive Orders Now" button at the bottom
    echo '<form method="post" id="archive-orders-form" style="margin-top: 20px;">';
    //echo '<input type="hidden" name="action" value="archive_orders_action">';
    echo '<input type="hidden" name="archive_order_ids" value="' . esc_attr(implode(',', wp_list_pluck($data['results'], 'order_id'))) . '">';
    echo '<input type="submit" id="archive-orders-btn" name="archive_orders" value="Archive Orders Now"' . (empty($data['results']) ? ' disabled' : '') . '>';
    echo '</form>';


}

add_action('wp_ajax_get_filtered_orders', 'get_filtered_orders');

function handle_ajax_get_filtered_orders() {
    // Call your function to get the filtered orders
    $data = get_filtered_orders(); 
    
    if (isset($data['error_message']) && !empty($data['error_message'])) {
        echo json_encode(['error' => $data['error_message']]); // Return error message as JSON
    } else {
        ob_start(); // Start output buffering
        display_results_and_query($data); // Display results in the response
        $html = ob_get_clean(); // Get the output and clean the buffer
        echo json_encode(['html' => $html]); // Return HTML as JSON
    }
    
    wp_die(); // Required to properly end the Ajax request
}

add_action('wp_ajax_get_filtered_orders', 'handle_ajax_get_filtered_orders');
add_action('wp_ajax_nopriv_get_filtered_orders', 'handle_ajax_get_filtered_orders'); // If you want to allow non-logged-in users (optional)
