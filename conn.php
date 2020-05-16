<?php
date_default_timezone_set('Asia/Singapore');

$server = 'blonze2d5mrbmcgf.cbetxkdyhwsb.us-east-1.rds.amazonaws.com';
$user = 'jy2ad26zr47dteaa';
$password = 'skeg34imstzx8bkw';
$db = 'ldnf8kvqpg2lom99';

$conn = mysqli_connect($server, $user, $password, $db);

if (mysqli_connect_errno()) {
    die('Failed to connect to MySQL: ' . mysqli_connect_error());
}
