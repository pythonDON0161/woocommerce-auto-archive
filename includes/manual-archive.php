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
      //  add_action('admin_notices', 'woa_manual_archive_success');
    }

    ?>
    <div class="wrap">
        <h1>Manual Order Archiving</h1>

        <form method="post" action=" ">
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



// Display the form for user input
function display_order_filter_form($error_message = '') {
  
    ?>

   
    <style>
     
        .form-row {
            display: flex;
            flex-wrap: wrap;
            gap: 10px; /* Space between fields */
            margin-bottom: 15px; /* Space below each row */
        }
        .form-row label {
            flex: 1; /* Take equal space */
            min-width: 150px; /* Minimum width for labels */
        }
        .form-row input, 
        .form-row select {
            flex: 2; /* Take more space */
        }

        table{width:100%;}

    </style>
    
    <form method="post" >
     
        <div class="form-row">
            <label for="start_date">Start Date:</label>
            <input type="date" id="start_date" name="start_date">
            
            <label for="end_date">End Date:</label>
            <input type="date" id="end_date" name="end_date">
        </div>

        <div class="form-row">
            <label for="status">Order Status:</label>
            <select id="status" name="status">
                <option value="">Any</option>
                <option value="wc-pending">Pending</option>
                <option value="wc-processing">Processing</option>
                <option value="wc-completed">Completed</option>
                <option value="wc-cancelled">Cancelled</option>
                <option value="wc-refunded">Refunded</option>
                <option value="wc-failed">Failed</option>
            </select>
            
            <label for="min_total">Minimum Total Amount:</label>
            <input type="number" step="0.01" id="min_total" name="min_total">
            
            <label for="max_total">Maximum Total Amount:</label>
            <input type="number" step="0.01" id="max_total" name="max_total">
        </div>

        <div class="form-row">
            <label for="payment_method">Payment Method:</label>
            <select id="payment_method" name="payment_method">
                <option value="">Any</option>
                <?php 
                $payment_gateways = WC()->payment_gateways->get_available_payment_gateways();
                foreach ( $payment_gateways as $gateway ) : 
                ?>
                    <option value="<?php echo esc_attr($gateway->id); ?>"><?php echo esc_html($gateway->get_title()); ?></option>
                <?php endforeach; ?>
            </select>
            
            <label for="product_filter">Filter by Product:</label>
            <select id="product_filter" name="product_filter">
                <option value="">Any</option>
                <option value="contains">Contains Product(s)</option>
                <option value="not_contains">Does Not Contain Product(s)</option>
            </select>
            <label for="product_ids">Select Product(s):</label>
            <select id="product_ids" name="product_ids[]" multiple>
                <?php 
                
                $products = wc_get_products(array(
                    'limit' => -1, // Get all products
                    'status' => 'publish', // Only published products
                ));
                foreach ($products as $product) : ?>
                    <option value="<?php echo esc_attr($product->get_id()); ?>"><?php echo esc_html($product->get_name()); ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-row">
            <input type="submit" value="Filter Orders">
        </div>
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

        echo '<div id="archive-message"></div>';
        echo '<table border="1" cellpadding="5" cellspacing="0" style="background-color: white;">';
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
    }  

    // Add "Archive Orders Now" button at the bottom
    echo '<form method="post" id="archive-orders-form" style="margin-top: 20px;">';
    //echo '<input type="hidden" name="action" value="archive_orders_action">';
    echo '<input type="hidden" name="archive_order_ids" value="' . esc_attr(implode(',', wp_list_pluck($data['results'], 'order_id'))) . '">';
    echo '<input type="submit" id="archive-orders-btn" name="archive_orders" value="Archive Orders Now"' . (empty($data['results']) ? ' disabled' : '') . '>';
    echo '</form>';


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
/*function woa_manual_archive_success() {
    echo '<div class="notice notice-success is-dismissible"><p>Orders successfully archived.</p></div>';
}
*/