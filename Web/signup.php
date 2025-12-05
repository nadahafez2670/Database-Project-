<?php
include "db.php";

$fname = $_POST['fname'];
$lname = $_POST['lname'];
$email = $_POST['email'];
$password = $_POST['password'];
$address = $_POST['address'];


$phone1 = $_POST['phone1'];
$phone2 = $_POST['phone2']; 


$checkSql = "SELECT customer_id FROM customers WHERE email = '$email'";
$checkResult = mysqli_query($conn, $checkSql);

if (mysqli_num_rows($checkResult) > 0) {
    header("Location: signup.html?error=exists");
    exit();
}

$hashedPassword = password_hash($password, PASSWORD_DEFAULT);

      
$sql = "INSERT INTO customers (f_name, l_name, email, password, address)
        VALUES ('$fname', '$lname', '$email', '$hashedPassword', '$address')";

if (mysqli_query($conn, $sql)) {
    
    $new_customer_id = mysqli_insert_id($conn);

    $sqlPhone1 = "INSERT INTO customer_phones (customer_id, phone_number) VALUES ('$new_customer_id', '$phone1')";
    mysqli_query($conn, $sqlPhone1);

    if (!empty($phone2)) {
        $sqlPhone2 = "INSERT INTO customer_phones (customer_id, phone_number) VALUES ('$new_customer_id', '$phone2')";
        mysqli_query($conn, $sqlPhone2);
    }

    
    header("Location: Log.html?success=created");
    exit();
} else {
    echo "Error: " . mysqli_error($conn);
}
?>