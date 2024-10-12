<?php
// Register the settings
add_action('admin_init', 'woa_register_settings');
function woa_register_settings() {
    register_setting('woa_settings_group', 'woa_order_age_limit');
    register_setting('woa_settings_group', 'woa_cron_frequency');
}

 /*************** Total number of orders counter **************/




/***************   Count orders in woocommerce       ************** */

// Display the settings page
function woa_settings_page() {
    global $wpdb;

    // Get saved options
    $age_limit = get_option('woa_order_age_limit', 12); // Default: 12 months
    $cron_frequency = get_option('woa_cron_frequency', 'daily'); // Default: daily

    // Get count of orders older than selected age limit
// Get count of orders older than the selected age limit
$cutoff_date = date('Y-m-d H:i:s', strtotime("-$age_limit months"));
$order_count = $wpdb->get_var("
    SELECT COUNT(*) FROM {$wpdb->prefix}wc_orders
    WHERE date_created < '$cutoff_date'
");


      // Get total count of all orders
  // Get total count of all orders
  $total_order_count = $wpdb->get_var("
  SELECT COUNT(*) FROM {$wpdb->prefix}wc_orders
");

    ?>


    <div class="wrap">
        <h1>WooCommerce Order Archiver Settings</h1>
        <form method="post" action="options.php">
            <?php settings_fields('woa_settings_group'); ?>
            <?php do_settings_sections('woa_settings_group'); ?>

            

            <table class="form-table">

            <tr valign="top">
                    <th scope="row">Total Number of Orders</th>
                    <td>
                        <p><strong> <?php echo  $total_order_count; ?></strong> total orders in WooCommerce.</p>
                    </td>
                </tr>


                <tr valign="top">
                    <th scope="row">Archive Orders Older Than</th>
                    <td>
                        <input type="number" name="woa_order_age_limit" value="<?php echo esc_attr($age_limit); ?>" min="1" /> months
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row">Cron Job Frequency</th>
                    <td>
                        <select name="woa_cron_frequency">
                            <option value="daily" <?php selected($cron_frequency, 'daily'); ?>>Daily</option>
                            <option value="weekly" <?php selected($cron_frequency, 'weekly'); ?>>Weekly</option>
                            <option value="monthly" <?php selected($cron_frequency, 'monthly'); ?>>Monthly</option>
                        </select>
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row">Orders to be Archived</th>
                    <td>
                        <p><strong><?php echo $order_count; ?></strong> orders older than <?php echo $age_limit; ?> months will be archived.</p>
                    </td>
                </tr>
             
            </table>
            
            <?php submit_button(); ?>
        </form>
    </div>
    <?php
}
