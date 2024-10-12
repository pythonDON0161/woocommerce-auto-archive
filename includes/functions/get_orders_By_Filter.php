<?php

// Display the manual archive page


    ?>
   
    <?php



// Display the form for user input
function display_order_filter_form($error_message = '') {
  
    ?>

   
    
    <form method="post">
        <label for="start_date">Start Date:</label>
        <input type="date" id="start_date" name="start_date"><br><br>

        <label for="end_date">End Date:</label>
        <input type="date" id="end_date" name="end_date"><br><br>

        <label for="status">Order Status:</label>
        <select id="status" name="status">
            <option value="">Any</option>
            <option value="wc-pending">Pending</option>
            <option value="wc-processing">Processing</option>
            <option value="wc-completed">Completed</option>
            <option value="wc-cancelled">Cancelled</option>
            <option value="wc-refunded">Refunded</option>
            <option value="wc-failed">Failed</option>
        </select><br><br>

        <label for="min_total">Minimum Total Amount:</label>
        <input type="number" step="0.01" id="min_total" name="min_total"><br><br>

        <label for="max_total">Maximum Total Amount:</label>
        <input type="number" step="0.01" id="max_total" name="max_total"><br><br>

        <label for="payment_method">Payment Method:</label>
        <select id="payment_method" name="payment_method">
            <option value="">Any</option>
            
            <?php   
                 //Get payment gateways from woocommerce
                    $payment_gateways = WC()->payment_gateways->get_available_payment_gateways();
                    foreach ( $payment_gateways as $gateway ) : ?>

                <option value="<?php echo esc_attr($gateway->id); ?>"><?php echo esc_html($gateway->get_title()); ?></option>
           
            <?php endforeach; ?>

        </select><br><br>

        <input type="submit" value="Filter Orders">
    </form>

    <?php
    // Display error message if provided
    if ( ! empty($error_message) ) {
        echo '<p style="color: red;">' . esc_html($error_message) . '</p>';
    }
}

// Process the form submission and generate the query
function get_filtered_orders() {
    global $wpdb;

    // Retrieve and sanitize user inputs
    $start_date = isset($_POST['start_date']) ? sanitize_text_field($_POST['start_date']) : '';
    $end_date = isset($_POST['end_date']) ? sanitize_text_field($_POST['end_date']) : '';
    $status = isset($_POST['status']) ? sanitize_text_field($_POST['status']) : '';
    $min_total = isset($_POST['min_total']) ? floatval($_POST['min_total']) : '';
    $max_total = isset($_POST['max_total']) ? floatval($_POST['max_total']) : '';
    $payment_method = isset($_POST['payment_method']) ? sanitize_text_field($_POST['payment_method']) : '';

    // Validate that at least start date or end date is provided
    if ( empty($start_date) && empty($end_date) ) {
        return [
            'results' => [],
            'query' => '',
            'error_message' => 'Please enter at least a Start Date or End Date.'
        ];
    }

    // Build the base query
    $query = "SELECT * FROM {$wpdb->prefix}wc_orders WHERE 1=1";

    // Add conditions only if user input is provided
    $query_params = [];
    if ( !empty($start_date) ) {
        $query .= " AND date_created_gmt >= %s";
        $query_params[] = $start_date;
    }
    if ( !empty($end_date) ) {
        $query .= " AND date_created_gmt <= %s";
        $query_params[] = $end_date;
    }
    if ( !empty($status) ) {
        $query .= " AND status = %s";
        $query_params[] = $status;
    }
    if ( $min_total !== '' ) {
        $query .= " AND total_amount >= %f";
        $query_params[] = $min_total;
    }
    if ( $max_total !== '' && $max_total > 0 ) {
        $query .= " AND total_amount <= %f";
        $query_params[] = $max_total;
    }
    if ( !empty($payment_method) ) {
        $query .= " AND payment_method = %s";
        $query_params[] = $payment_method;
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
        echo '<table border="1" cellpadding="5" cellspacing="0" style="background-color: white;">';
        echo '<tr>';
        foreach ($data['results'][0] as $column => $value) {
            echo '<th>' . esc_html($column) . '</th>';
        }
        echo '</tr>';

        foreach ($data['results'] as $order) {
            echo '<tr>';
            foreach ($order as $value) {
                echo '<td>' . esc_html($value) . '</td>';
            }
            echo '</tr>';
        }
        echo '</table>';
    }

    // Display the generated SQL query
    echo '<h3>Generated SQL Query:</h3>';
    echo '<pre style="background-color: #f9f9f9; padding: 10px; border: 1px solid #ddd;">' . esc_html($data['query']) . '</pre>';
}

// Main logic to display the form and handle form submission
display_order_filter_form();

if ( $_SERVER['REQUEST_METHOD'] === 'POST' ) {
    $filtered_orders_data = get_filtered_orders();
    display_results_and_query($filtered_orders_data);
}

