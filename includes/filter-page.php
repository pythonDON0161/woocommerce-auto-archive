<?php


// Display the form for user input
function woa_filter__page() {
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

        <input type="submit" value="Filter Orders">
    </form>
    <?php
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

    // Build the base query
    $query = "SELECT * FROM {$wpdb->prefix}wc_orders WHERE 1=1";

    // Add conditions based on user input
    if ( !empty($start_date) ) {
        $query .= $wpdb->prepare(" AND date_created_gmt >= %s", $start_date);
    }
    if ( !empty($end_date) ) {
        $query .= $wpdb->prepare(" AND date_created_gmt <= %s", $end_date);
    }
    if ( !empty($status) ) {
        $query .= $wpdb->prepare(" AND status = %s", $status);
    }
    if ( $min_total !== '' ) {
        $query .= $wpdb->prepare(" AND total_amount >= %f", $min_total);
    }
    if ( $max_total !== '' ) {
        $query .= $wpdb->prepare(" AND total_amount <= %f", $max_total);
    }

    // Execute the query
    $results = $wpdb->get_results($query);

    return $results;
}

// Display the results in an HTML table
function display_results_table($orders) {
    if ( empty($orders) ) {
        echo '<p>No orders found matching the criteria.</p>';
        return;
    }

    echo '<table border="1" cellpadding="5" cellspacing="0" style="background-color: white;">';
    echo '<tr>';
    foreach ($orders[0] as $column => $value) {
        echo '<th>' . esc_html($column) . '</th>';
    }
    echo '</tr>';

    foreach ($orders as $order) {
        echo '<tr>';
        foreach ($order as $value) {
            echo '<td>' . esc_html($value) . '</td>';
        }
        echo '</tr>';
    }
    echo '</table>';
}

// Main logic to display the form and handle form submission

woa_filter__page();

if ( $_SERVER['REQUEST_METHOD'] === 'POST' ) {
    $filtered_orders = get_filtered_orders();
    display_results_table($filtered_orders);
}
