<?php
$host = "localhost";
$user = "benguetf_eastkentucky";                
$password = "O3K9-T6&{oW[";         
$dbname = "benguetf_eastkentucky";  

$google_auth_client_id = '1036649042415-rikd0a70bflr3l1h2jsjsvd4np4aigae.apps.googleusercontent.com';

$conn = new mysqli($host, $user, $password, $dbname);

// Check for connection errors
if ($conn->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}