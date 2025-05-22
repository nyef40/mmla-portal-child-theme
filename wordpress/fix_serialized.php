<?php
// Connect to the database
$mysqli = new mysqli('localhost', 'root', 'root', 'wordpress');

if ($mysqli->connect_error) {
    die('Connect Error (' . $mysqli->connect_errno . ') ' . $mysqli->connect_error);
}

// Function to safely update serialized data
function replace_serialized_url($serialized_string, $search_url, $replace_url) {
    // First, check if this is actually serialized data
    if (!is_serialized($serialized_string)) {
        return str_replace($search_url, $replace_url, $serialized_string);
    }
    
    $unserialized = unserialize($serialized_string);
    
    if ($unserialized === false) {
        return $serialized_string;
    }
    
    $unserialized = replace_urls_recursive($unserialized, $search_url, $replace_url);
    
    return serialize($unserialized);
}

// Helper function to check if a string is serialized
function is_serialized($data) {
    if (!is_string($data)) return false;
    $data = trim($data);
    if ('N;' == $data) return true;
    if (!preg_match('/^([adObis]):/', $data, $badions)) return false;
    switch ($badions[1]) {
        case 'a' :
        case 'O' :
        case 's' :
            if (preg_match("/^{$badions[1]}:[0-9]+:.*[;}]\$/s", $data))
                return true;
            break;
        case 'b' :
        case 'i' :
        case 'd' :
            if (preg_match("/^{$badions[1]}:[0-9.E-]+;\$/", $data))
                return true;
            break;
    }
    return false;
}

// Recursively replace URLs in arrays and objects
function replace_urls_recursive($data, $search_url, $replace_url) {
    if (is_string($data)) {
        return str_replace($search_url, $replace_url, $data);
    }
    
    if (is_array($data)) {
        foreach ($data as $key => $value) {
            $data[$key] = replace_urls_recursive($value, $search_url, $replace_url);
        }
    }
    
    if (is_object($data)) {
        foreach (get_object_vars($data) as $key => $value) {
            $data->$key = replace_urls_recursive($value, $search_url, $replace_url);
        }
    }
    
    return $data;
}

// Update serialized data in wp_options
$tables_to_check = ['wp_options', 'wp_postmeta'];
$search_url = 'https://mobilemedicalla.com';
$replace_url = 'http://localhost:8080';

foreach ($tables_to_check as $table) {
    echo "Processing table: $table\n";
    
    // Get all rows that might contain serialized data
    $result = $mysqli->query("SELECT * FROM $table WHERE option_value LIKE '%$search_url%' OR meta_value LIKE '%$search_url%'");
    
    if (!$result) {
        echo "Error querying $table: " . $mysqli->error . "\n";
        continue;
    }
    
    while ($row = $result->fetch_assoc()) {
        if ($table == 'wp_options') {
            $id_field = 'option_id';
            $value_field = 'option_value';
            $id = $row['option_id'];
            $value = $row['option_value'];
        } else {
            $id_field = 'meta_id';
            $value_field = 'meta_value';
            $id = $row['meta_id'];
            $value = $row['meta_value'];
        }
        
        // Replace URLs in the value
        $new_value = replace_serialized_url($value, $search_url, $replace_url);
        $new_value = replace_serialized_url($new_value, str_replace('https://', 'http://', $search_url), $replace_url);
        
        if ($new_value !== $value) {
            // Update the database
            $stmt = $mysqli->prepare("UPDATE $table SET $value_field = ? WHERE $id_field = ?");
            $stmt->bind_param('si', $new_value, $id);
            $stmt->execute();
            echo "Updated $table ID $id\n";
        }
    }
    
    $result->free();
}

$mysqli->close();
echo "Done!\n";
