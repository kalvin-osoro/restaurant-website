<?php
require_once("../DBConnect.php");
$user_name = $_POST['user_name'];
$user_email = $_POST['user_email'];
$user_password = $_POST['user_password'];

$sql = "INSERT INTO customer(username, email, password) VALUES ('$user_name', '$user_email', '$user_password')";

// if (mysqli_query($conn, $sql)){

//     echo "Inserted successfully";
// }
// else{
//     echo "Not inserted".mysqli_error($conn);
// }
mysqli_query($conn, $sql);

header("Location: ../Register.php?signup=success");

?>