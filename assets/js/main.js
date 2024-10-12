// Example script to update order count dynamically based on user input
document.addEventListener('DOMContentLoaded', function() {
    const ageInput = document.querySelector('input[name="woa_order_age_limit"]');
    const countDisplay = document.querySelector('p strong');

    if (ageInput) {
        ageInput.addEventListener('change', function() {
            // Ajax logic to update order count (you'll need to set up an Ajax handler in PHP)
            // Example: send Ajax request to update order count based on the selected age
        });
    }
});
