<?php

	/*--- We have created a variables to display error message ------*/
	$error = "";

	if (isset($_POST["btn_delete"]))
	{
	
		/*-- we included connection files--*/
		include "connection.php";

		$image_id = $_POST["img-id"];
		
		$result = mysqli_connect($dbserver, $dbuser, $dbpassword) or die("Connection error: ". mysqli_error($conn));
		mysqli_select_db($result, $dbname) or die("Could not Connect to Database: ". mysqli_error($conn));
		$res=mysqli_query($result,"SELECT img_path FROM food");
		$row=mysqli_fetch_array($res);
	
		if($food_ID == ""){
			$error = "Please enter the Image id.";
		}
		else
		{
			$result = mysqli_connect($dbserver, $dbuser, $dbpassword) or die("Connection error: ". mysqli_error($conn));
			mysqli_select_db($result, $dbname) or die("Could not Connect to Database: ". mysqli_error($conn));
			mysqli_query($result,"delete from food where img_id = '" . $food_ID ."'") or die("Could not Connect to table: ". mysqli_error($conn));
			
			//this code will delete image from folder
			unlink($row['img_path']);
			$error = "<p align=center>File ".$row["img_path"].""."<br />Image has been deleted from table.</p>";
		}
	}

	?>