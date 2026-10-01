<?php
require_once("../DBConnect.php");
?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Users</title>
    <style>
        table {
            border-collapse: collapse;
            width: 70%;
            color: royalblue;
            font-family: monospace;
            font-size: 25px;
            text-align: left;
        }

        th {
            background-color: royalblue;
            color: white;
        }

        tr:nth-child(even) {
            background-color: #f2f2f2;
        }
    </style>
</head>

<body>
    <table>
        <tr>
            <th>Id</th>
            <th>Username</th>
            <th>email</th>
            <th>Password</th>
        </tr>
        <?php

        $sql = "SELECT chefID, chef_Uname, email, password from chef";
        $result = $conn->query($sql);

        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                echo "<tr><td>" . $row['chefID'] . "</td><td>" . $row['chef_Uname'] . "</td><td>" . $row['email'] . "</td><td>" . $row['password'] . " </td></tr>";
            }
            echo "</table";
        } else {
            echo "no data";
        }
        $conn->close();

        ?>
    </table>
</body>

</html>