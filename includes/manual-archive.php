<?php
// Display the manual archive page
function woa_manual_archive_page() {
    global $wpdb;

    // Handle manual archiving form submission
    if (isset($_POST['woa_manual_archive_button'])) {
        $start_date = sanitize_text_field($_POST['woa_start_date']);
        $end_date = sanitize_text_field($_POST['woa_end_date']);
        $manual_age_limit = isset($_POST['woa_manual_age_limit']) ? intval($_POST['woa_manual_age_limit']) : 12;

        // If a date range is provided, use it. Otherwise, use age limit.
        if (!empty($start_date) && !empty($end_date)) {
            woa_archive_old_orders_function($start_date, $end_date, '');
        } else {
            woa_archive_old_orders_function('', '', $manual_age_limit);
        }

        // Redirect or show success message
        add_action('admin_notices', 'woa_manual_archive_success');
    }

    ?>
    <div class="wrap">
        <h1>Manual Order Archiving</h1>

        <form method="post" action="">
            <table class="form-table">
                <tr valign="top">
                    <th scope="row">Select Date Range</th>
                    <td>
                        <label for="start_date">From:</label>
                        <input type="date" name="woa_start_date" id="start_date" value="" />
                        <label for="end_date">To:</label>
                        <input type="date" name="woa_end_date" id="end_date" value="" />
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row">Or Select Order Age</th>
                    <td>
                        <input type="number" name="woa_manual_age_limit" value="12" min="1" /> months
                    </td>
                </tr>
            </table>
            <?php submit_button('Archive Orders Now', 'primary', 'woa_manual_archive_button'); ?>
        </form>
    </div>
    <?php



// Ensure WooCommerce is active
if ( ! class_exists( 'WooCommerce' ) ) {
    die('WooCommerce is not activated.');
}

// Display the form for user input
function display_order_filter_form($error_message = '') {
    ?>
<form method="post">
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

}

// Success message after manual archive
function woa_manual_archive_success() {
    echo '<div class="notice notice-success is-dismissible"><p>Orders successfully archived.</p></div>';
}
