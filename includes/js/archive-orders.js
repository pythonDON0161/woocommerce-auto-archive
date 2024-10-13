jQuery(document).ready(function ($) {


    $(document).on('submit', '#archive-orders-form', function (e) {
        e.preventDefault(); // Prevent the default form submission
    console.log('Archive Orders script loaded.');

    //$('#archive-orders-btn').on('click', function () {
        var orderIds = $('input[name="archive_order_ids"]').val();
        console.log('Button clicked. Order IDs:', orderIds);

        // Check if orderIds is empty
       /* if (!orderIds) {
            $('#archive-message').html('<div class="notice notice-error is-dismissible"><p>No orders selected for archiving.</p></div>');
            return;
        }*/

        $.ajax({
            url: archiveOrdersAjax.ajax_url,
            type: 'POST',
            data: {
                action: 'archive_orders_action',
                nonce: archiveOrdersAjax.nonce,
                archive_order_ids: orderIds
            },
            success: function (response) {
                console.log('AJAX Success:', response);
                console.log(response); 
                $('#archive-message').html('<div class="notice notice-success is-dismissible"><p>' + response.data + '</p></div>');
                // Optionally, refresh the results or handle UI updates here
            },
            error: function (xhr, status, error,response) {
                console.log('AJAX Error:', error);
                console.log(response,status); 
                $('#archive-message').html('<div class="notice notice-error is-dismissible"><p>' + error + '</p></div>');
            }
        });
    //});

})}) ;
