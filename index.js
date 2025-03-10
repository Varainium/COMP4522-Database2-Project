// insert functionality of database here

<?php 
    // Connect to SQLite database
    $db = new SQLite3('wellness_clinic.db')

    // Check if the connection is successful
    if(!$db) {
        die("Database connection failed: " . $db->lastErrorMsg());
    }
?>