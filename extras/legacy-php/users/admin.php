<?php
require_once("../DBConnect.php");
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Users</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/css/bootstrap.min.css" />

</head>

<body style="padding-top: 100px;">


    <div class="container">
        <?php
        $fetchQuery = mysqli_query($conn, "select * from administrator") or die("could nt fetch" . mysqli_error($conn));
        ?>
        <div class="container">
            <?php
            if (isset($_POST['submitDeleteBtn'])) {
                $key = $_POST['keyToDelete'];

                //check if the record exists
                $check = mysqli_query($conn, "select * from administrator where owner_ID = '$key' ") or die("not found" . mysqli_error($conn));
                if (mysqli_num_rows($check) > 0) {
                    //means record found

                    $queryDelete = mysqli_query($conn, "DELETE from administrator where owner_ID = '$key'")
                        or die("not deleted" . mysqli_error($conn)); ?>

                    <div class="alert alert-success">
                        <p>Record deleted!!</p>
                    </div>

                <?php
                    header('Location:admin.php');
                } else {
                    //give warning that record doesn't exist
                ?>

                    <div class="alert alert-warning">
                        <p>Record doesn't exist</p>
                    </div>
            <?php }
            }
            ?>
        </div>

        <table class="table table-condensed table-bordered">
            <tr>
                <th>Id</th>
                <th>Username</th>
                <th>email</th>
                <th>Password</th>
                <th>Select</th>
                <th>Delete</th>
            </tr>
            <?php

            $sql = "SELECT owner_ID, username, email, password from customer";
            $result = $conn->query($sql);

           $sr = 1;
            while ($row = $result->fetch_assoc()) { ?>
                <tr>
                    <form action="" method="post" role="form">
                        <td><?php echo $row['owner_ID']; ?></td>
                        <td><?php echo $row['username']; ?></td>
                        <td><?php echo $row['email']; ?></td>
                        <td><?php echo $row['password']; ?></td>
                        <td>
                            <input type="checkbox" name="keyToDelete" value="<?php echo $row['owner_ID']; ?>" required>
                        </td>
                        <td>
                            <input type="submit" name="submitDeleteBtn" class="btn btn-info">
                        </td>
                    </form>
                </tr>

            <?php $sr++;
            }
            ?>

        </table>
    </div>
</body>

</html>