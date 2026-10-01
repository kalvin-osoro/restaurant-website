<?php
session_start();
require_once("../DBConnect.php");

if (isset($_POST['Login'])) {
    if (empty($_POST['username']) || empty($_POST['userpassword'])) {
        header("location:../admnlogin.php?Empty= Please Fill in the Blanks");
    } else {
        $query = "select * from administrator where username='" . $_POST['username'] . "' and password='" . $_POST['userpassword'] . "'";
        $result = mysqli_query($conn, $query);

        if (mysqli_fetch_assoc($result)) {
            $_SESSION['User'] = $_POST['username'];
            header("location:../admnindex.php");
        } else {
            header("location:../admnlogin.php?Invalid= Please Enter Correct User Name and Password ");
        }
    }
} else {
    echo 'Not Working Now Guys';
}
