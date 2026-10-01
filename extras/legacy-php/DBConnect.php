<?php

$dbserver = 'localhost';
$dbuser = 'root';
$dbpassword = "";
$dbname = "kalvin's restaurant";

$conn = mysqli_connect($dbserver, $dbuser, $dbpassword, $dbname);

// if ($conn) {

//     echo "Connected successfully";

// }
// else{
//     echo "Did not connect".mysqli_connect_error();
    
// }
if(!$conn)
    {
        die(' Please Check Your Connection'.mysqli_error($con));
    }

?>
