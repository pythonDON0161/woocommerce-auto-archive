<?php 

global $wpdb;

// Get the database name
$database_name = $wpdb->dbname;

// Table name
$table_name = "{$wpdb->prefix}wc_orders";

// Query to get the size of the wc_orders table
$query = "
    SELECT 
        table_name AS `Table`, 
        ROUND(((data_length + index_length) / 1024 / 1024), 2) AS `Size in MB`
    FROM information_schema.TABLES 
    WHERE table_schema = '$database_name' 
    AND table_name = '$table_name';
";

$result = $wpdb->get_row($query);

if ($result) {
    echo 'Size of wc_orders Table: ' . $result->{'Size in MB'} . ' MB';
} else {
    echo 'Table wc_orders not found.';
}
