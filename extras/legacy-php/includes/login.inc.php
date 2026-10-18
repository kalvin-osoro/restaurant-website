<?php
session_start();
require_once("../DBConnect.php");

$error = '';

if (isset($_POST['submit'])) {

     if (empty($_POST['username']) || empty($_POST['userpassword'])) {
          $error = "Username or password is invalid";
     } else {
          $username_login = $_POST['username'];
          $password_login = $_POST['userpassword'];

          $query = "SELECT * FROM customer WHERE username='$username_login' AND password='$password_login'";
          $query_run = mysqli_query($conn, $query);


          if (mysqli_fetch_array($query_run)) {
               $_SESSION['user_name'] = $username_login;
               header('Location: ../index.php');
          } else {
               $_SESSION['status'] = "username / Password is Invalid";
               header('Location: ../login.php');
          }
     }
}
