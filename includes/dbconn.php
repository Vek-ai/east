<?php
$host = "localhost";
$user = "benguetf_eastkentucky";                
$password = "O3K9-T6&{oW[";         
$dbname = "benguetf_eastkentucky";  

$conn = new mysqli($host, $user, $password, $dbname);

// Check for connection errors
if ($conn->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}