<?php
$host = "localhost";
$user = "root"; // default for XAMPP
$pass = "";     // default for XAMPP
$dbname = "central_supply";

$conn = new mysqli($host, $user, $pass, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$conn->set_charset("utf8");
?>