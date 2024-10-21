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

   
        <h1>Manual Order Archiving</h1>

  <div class="container-fluid mb-5">
        <div class="row" > 
         <!-- HEADER CARDS -->
        <div class="col-xl-3 col-md-6 mb-4">
                            <div class="card border-left-primary shadow h-100 py-2">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                                DATABASE SIZE</div>
                                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo "123kb"; ?></div>
                                        </div>
                                        <div class="col-auto">
                                            <i class="fas fa-calendar fa-2x text-gray-300"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
            </div>
        <!-- HEADER CARDS -->

              <!-- HEADER CARDS -->
              <div class="col-xl-3 col-md-6 mb-4">
                            <div class="card border-left-primary shadow h-100 py-2">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                               NO. OF ORDERS</div>
                                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo "1290"; ?></div>
                                        </div>
                                        <div class="col-auto">
                                            <i class="fas fa-calendar fa-2x text-gray-300"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
            </div>
        <!-- HEADER CARDS -->


               <!-- HEADER CARDS -->
               <div class="col-xl-3 col-md-6 mb-4">
                            <div class="card border-left-primary shadow h-100 py-2">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                                NO. OF ARCHIVED ORDERS</div>
                                            <div class="h5 mb-0 font-weight-bold text-gray-800"> <?php echo "350";?></div>
                                        </div>
                                        <div class="col-auto">
                                            <i class="fas fa-calendar fa-2x text-gray-300"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
            </div>
        <!-- HEADER CARDS -->

           <!-- HEADER CARDS -->
           <div class="col-xl-3 col-md-6 mb-4">
                            <div class="card border-left-primary shadow h-100 py-2">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                                Earnings (Monthly)</div>
                                            <div class="h5 mb-0 font-weight-bold text-gray-800">$40,000</div>
                                        </div>
                                        <div class="col-auto">
                                            <i class="fas fa-calendar fa-2x text-gray-300"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
            </div>
        <!-- HEADER CARDS -->

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

    <div class="row">

     <div class="card shadow mb-4 col-lg-12 col-md-12" >

        <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Filter Options</h6>
         </div>

     <div class="card-body">

     <!-- Form Options -->
    <form method="post" id="filter-orders-form" >

    <!-- Hidden field to specify the action -->
    <input type="hidden" name="action" value="get_filtered_orders">
     
     <div class="form-row">
         <label for="start_date">Start Date:</label>
         <input type="date" id="start_date" class="form-control form-control-sm" name="start_date">
         
         <label for="end_date">End Date:</label>
         <input type="date" id="end_date" class="form-control form-control-sm" name="end_date">
     </div>

     <div class="form-row">
         <label for="status">Order Status:</label>
         <select id="status" name="status"  class="form-control form-control-md">
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

                <!-- Submit button -->
                <div class="form-row">
                    <input type="submit" value="Filter Orders">
                </div>
                <!-- Submit button -->


 </form>
             </div>
        </div>
    </div>

    
<div class="row">
<div class="card shadow mb-4 col-lg-12 col-md-12" >

<div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Results:</h6>
 </div>

<div class="card-body"> 
<div id="archive-message">
<div class="table-responsive">
<div id="dataTable_wrapper" class="dataTables_wrapper dt-bootstrap4">
    <div class="row">
        <div class="col-sm-12 col-md-6">
            <div class="dataTables_length" id="dataTable_length">
                <label>Show <select name="dataTable_length" aria-controls="dataTable" class="custom-select custom-select-sm form-control form-control-sm">
                    <option value="10">10</option>
                    <option value="25">25</option>
                    <option value="50">50</option>
                    <option value="100">100</option>
                </select> entries</label>
                
                </div>
                </div>
                <div class="col-sm-12 col-md-6">
                    <div id="dataTable_filter" class="dataTables_filter">
                        <label>Search:<input type="search" class="form-control form-control-sm" placeholder="" aria-controls="dataTable"></label></div>
                    </div></div>
                    <div class="row">
                        <div class="col-sm-12">
                            <table class="table table-bordered dataTable" id="dataTable" width="100%" cellspacing="0" role="grid" aria-describedby="dataTable_info" style="width: 100%;">
                                    <thead>
                                        <tr>
                                            <th>Name</th>
                                            <th>Order No.</th>
                                            <th>Date</th>
                                            <th>Status</th>
                                            <th>Total</th>
                                        </tr>
                                    </thead>
                                    
                                    <tbody>
                                        
                                                
                                    </tbody>
                                </table></div></div>
                                <div class="row">
                                    <div class="col-sm-12 col-md-5">
                                        <div class="dataTables_info" id="dataTable_info" role="status" aria-live="polite">Showing 1 to 10 of 57 entries</div></div><div class="col-sm-12 col-md-7"><div class="dataTables_paginate paging_simple_numbers" id="dataTable_paginate"><ul class="pagination"><li class="paginate_button page-item previous disabled" id="dataTable_previous"><a href="#" aria-controls="dataTable" data-dt-idx="0" tabindex="0" class="page-link">Previous</a></li><li class="paginate_button page-item active"><a href="#" aria-controls="dataTable" data-dt-idx="1" tabindex="0" class="page-link">1</a></li><li class="paginate_button page-item "><a href="#" aria-controls="dataTable" data-dt-idx="2" tabindex="0" class="page-link">2</a></li><li class="paginate_button page-item "><a href="#" aria-controls="dataTable" data-dt-idx="3" tabindex="0" class="page-link">3</a></li><li class="paginate_button page-item "><a href="#" aria-controls="dataTable" data-dt-idx="4" tabindex="0" class="page-link">4</a></li><li class="paginate_button page-item "><a href="#" aria-controls="dataTable" data-dt-idx="5" tabindex="0" class="page-link">5</a></li><li class="paginate_button page-item "><a href="#" aria-controls="dataTable" data-dt-idx="6" tabindex="0" class="page-link">6</a></li><li class="paginate_button page-item next" id="dataTable_next"><a href="#" aria-controls="dataTable" data-dt-idx="7" tabindex="0" class="page-link">Next</a></li></ul></div></div></div></div>
                            </div>
</div>
</div>
</div>
</div>

    </div>

   
    <?php
    // Display error message if provided
    if ( ! empty($error_message) ) {
        echo '<p style="color: red;">' . esc_html($error_message) . '</p>';
    }
}

display_order_filter_form();



}

// Success message after manual archive
/*function woa_manual_archive_success() {
    echo '<div class="notice notice-success is-dismissible"><p>Orders successfully archived.</p></div>';
}
*/