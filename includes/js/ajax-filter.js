jQuery(document).ready(function ($) {
    $('#filter-orders-form').on('submit', function (e) {
        e.preventDefault(); // Prevent the form from submitting normally

        var formData = $(this).serialize(); // Serialize the form data

$.ajax({
    type: 'POST',
    url: ajaxurl, // Ensure this points to the correct AJAX URL
    data: $(this).serialize() + '&action=get_filtered_orders', 

    success: function(response) {
        try {
            const data = JSON.parse(response);
            const tableBody = document.querySelector('#dataTable tbody');
            tableBody.innerHTML = ''; // Clear existing rows
           // console.log(data.orders);
            
            if (data.orders && data.orders.length > 0) {
                data.orders.forEach(order => {
                    const row = document.createElement('tr');
                    console.log(order);
                    row.innerHTML = `
                        <td>${order.order_number}</td>
                        <td>${order.date}</td>
                        <td>${order.customer_name}</td>
                        <td>${order.order_status}</td>
                        <td>${order.payment_method}</td>
                        <td>${order.value}</td>
                    `;
                    tableBody.appendChild(row); // Append new row
                });
            } else {
                // Optionally handle no orders found
                const row = document.createElement('tr');
                row.innerHTML = '<td colspan="6" class="text-center">No orders found.</td>';
                tableBody.appendChild(row);
            }
        } catch (e) {
            console.error('Error parsing response:', e);
        }
    },
    error: function(xhr, status, error) {
        console.error('AJAX Error:', status, error);
    }
});
    });
});
