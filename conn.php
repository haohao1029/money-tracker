<?php
date_default_timezone_set('Asia/Singapore');

$server = 'localhost';
$user = 'root';
$password = '';
$db = 'budgettrackingsystem';

$conn = mysqli_connect($server, $user, $password, $db);

if (mysqli_connect_errno()) {
    die('Failed to connect to MySQL: ' . mysqli_connect_error());
}
