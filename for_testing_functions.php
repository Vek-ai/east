<?php
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ERROR | E_PARSE | E_CORE_ERROR | E_CORE_WARNING | E_COMPILE_ERROR | E_COMPILE_WARNING);

require 'includes/dbconn.php';
require 'includes/functions.php';

echo '<h4>Cart Contents:</h4>';
echo '<pre style="background: #f9f9f9; padding: 10px; border: 1px solid #ddd; border-radius: 5px;">';
print_r($_SESSION['cart']);
echo '</pre>';

?>
