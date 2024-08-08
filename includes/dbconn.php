<?php
$host = "localhost";
$user = "benguetf_eastkentucky";         
$password = "O3K9-T6&{oW[";  
$dbname = "benguetf_eastkentucky";  

$conn = mysqli_connect($host, $user, $password, $dbname);
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}
?>