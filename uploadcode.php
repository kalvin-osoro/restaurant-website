<?php

/*-- we included connection files--*/
include "DBConnect.php";

/*--- we created a variables to display the error message on design page ------*/
$error = "";

if (isset($_POST["btn_upload"]) == "Upload") {
    $file_tmp = $_FILES["fileImg"]["tmp_name"];
    $file_name = $_FILES["fileImg"]["name"];

    /*image name variable that you will insert in database ---*/
    $food_ID = $_POST["food_ID"];
    $name = $_POST["name"];
    $price = $_POST["price"];
    $description = $_POST["description"];

    //image directory where actual image will be store
    $file_path = "images/" . $file_name;

    /*---------------- php textbox validation checking ------------------*/
    if ($name == "") {
        $error = "Please enter Image name.";
    }

    /*-------- now insertion of image section has start -------------*/ else {
        if (file_exists($file_path)) {
            $error = "Sorry,The <b>" . $file_name . "</b> image already exist.";
        } else {
            $result = mysqli_connect($dbserver, $dbuser, $dbpassword) or die("Connection error: " . mysqli_error($conn));
            mysqli_select_db($result, $dbname) or die("Could not Connect to Database: " . mysqli_error($conn));
            mysqli_query($result, "INSERT INTO food(food_ID,name,price,image,description)
				VALUES('$food_ID','$name','$price','$file_path','$description')") or die("image not inserted" . mysqli_error($conn));
            move_uploaded_file($file_tmp, $file_path);
            $error = "<p align=center>File " . $_FILES["fileImg"]["name"] . "" . "<br />Image saved into Table.";
        }
    }
}
?>
