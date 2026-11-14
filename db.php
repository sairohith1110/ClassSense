<?php

$host = "localhost";
$username = "root";
$password = "";
$database = "classsense";

$conn = mysqli_connect($host, $username, $password, $database);

if (!$conn) {
    die("Database connection failed.");
}