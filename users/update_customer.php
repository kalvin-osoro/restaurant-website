<?php
require_once("../DBConnect.php");
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update customer table</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/css/bootstrap.min.css" />
</head>

<body style="padding-top: 100px;">
    <div class="container">

        <?php
        if (isset($_POST['updateButton'])) {
            $keyValue = $_POST['keyToUpdate'];
            $newusername = $_POST['New-username'];
            $newEmail = $_POST['New-email'];
            $newPassword = $_POST['New-password'];

            //check if record exists
            $check = mysqli_query($conn, "select * from customer where cust_ID = '$keyValue' ")
                or die("not selected" . mysqli_error($conn));
            if (mysqli_num_rows($check) > 0) {
                //means record found 
                $updateRow = mysqli_query($conn, "UPDATE customer set username = '$newusername', email = '$newEmail', password = '$newPassword' where cust_ID = '$keyValue'");
        ?>

                <div class="alert alert-success">
                    <p>Record update successful</p>
                </div>

            <?php } else { ?>

                <div class="alert alert-warning">
                    <p>Record not found!</p>
                </div>
        <?php   }
        }

        ?>
        <form action="" method="post" role="form">
            <div class="row">
                <div class="col-md-3">
                    <div class="form-group">
                        <input type="text" name="keyToUpdate" placeholder="Enter the key" class="form-control">
                    </div>

                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <input type="text" name="New-username" placeholder="Enter the new username" class="form-control">
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <input type="text" name="New-email" placeholder="Enter the new email " class="form-control">
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <input type="text" name="New-password" placeholder="Enter the new password" class="form-control">
                    </div>

                    <div></div>

                </div>
            </div>
            <div class="row">
                <div class="col-md-2">
                    <div class="form-group">
                        <input type="submit" name="updateButton" value="Update" class="btn btn-success">
                    </div>
                </div>
            </div>

        </form>
    </div>

</body>

</html>