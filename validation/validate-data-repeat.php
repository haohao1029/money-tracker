<?php
if (!isset($_POST['data'])) {
    exit();
}

include '../conn.php';

$data = mysqli_real_escape_string($conn, $_POST['data']);
$attribute = strtolower($_POST['attribute']);
$userID = mysqli_real_escape_string($conn, $_POST['user_id']);
$dataRepeat = false;

if ($data == '' || strlen(str_replace(' ', '', $data)) <= 0) {
    die('empty');
}

if ($attribute == 'username') { // check if username already taken
    $sql = "SELECT * FROM user WHERE user_name = '$data'";
}

$results = mysqli_query($conn, $sql);

if (mysqli_num_rows($results) > 0) { // if repeat check if it's the same user
    if ($userID == '') {
        $dataRepeat = true;
    } else {
        $row = mysqli_fetch_array($results);
        if ($row['user_id'] !== $userID) {
            $dataRepeat = true;
        }
    }
}

if ($dataRepeat) { // print error message when data is repeated
    echo 'This ' . $attribute . ' is already registered.';
} else { // print passed when no repeat
    echo 'passed';
}
