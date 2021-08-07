<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Food</title>
</head>

<body>
    <div class="container">
        <form action="processFood.php" method="POST" enctype="multipart/form-data">

            <label for="">foodID</label>
            <input type="text" name="foodID" id="foodID"><br><br>

            <label for="">Food item</label>
            <input type="text" name="fooditem" id="fooditem"><br><br>

            <label for="">Food Price</label>
            <input type="number" name="foodprice" id="foodprice"><br><br>

            <label for="">Upload image</label>
            <input type="file" name="foodimage" id="food_image"><br><br>

            <input type="submit" value="addfood" name="submitimage">
        </form>
    </div>



</body>

</html>