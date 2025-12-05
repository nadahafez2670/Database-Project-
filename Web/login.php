<?php
session_start();
include "db.php";

$email = $_POST['email'];
$password = $_POST['password'];

$sql = "SELECT * FROM customers WHERE email = '$email'";
$result = mysqli_query($conn, $sql);

if (mysqli_num_rows($result) == 1) {
    $row = mysqli_fetch_assoc($result);
    if (password_verify($password, $row['password'])) {
        $_SESSION['user_id'] = $row['customer_id'];
        $_SESSION['user_name'] = $row['f_name']; 
        header("Location: index.php"); 
        exit();
    } else {
        header("Location: Log.html?error=wrong_pass");
        exit();
    }
} else {
    header("Location: Log.html?error=not_found");
    exit();
}
?>