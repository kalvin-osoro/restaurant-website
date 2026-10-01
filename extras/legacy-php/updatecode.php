<?php

/*-- we included connection files--*/
include "DBConnect.php";

/*--- we created a variables to display the error message on design page ------*/
$error = "";

// if(isset($_GET["edit_id"]) && !empty($_GET["edit_id"])){
//     $result = mysqli_connect($dbserver, $dbuser, $dbpassword) or die("Connection error: " . mysqli_error($conn));
//     $id = $_GET["edit_id"];
//     $stmt_edit = $result->prepare("select * from food where food_id=")
// }


if (isset($_POST["btn_update"]) == "Edit") {
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
    
    if ($food_ID == "") {
        $error = "Please enter food ID.";
    }

    // /*-------- now insertion of image section has start -------------*/ else {
        // if (file_exists($file_path)) {
        //     $error = "Sorry,The <b>" . $file_name . "</b> image already exist.";
        // } 
        else {

            // mysqli_select_db($result, $dbname) or die("Could not Connect to Database: " . mysqli_error($conn));
            mysqli_query($conn, "UPDATE  food SET name ='$name', price = '$price', image = '$file_path', description = '$description'            
			WHERE food_ID = '$food_ID'") or die("Record not updated  " . mysqli_error($conn));
            move_uploaded_file($file_tmp, $file_path);
            $error = "<p align=center>File " . $_FILES["fileImg"]["name"] . "" . "<br />Image saved into Table.";
        }
    }
// }
