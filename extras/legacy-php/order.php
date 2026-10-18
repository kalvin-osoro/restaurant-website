<?php

session_start();
include('DBConnect.php');
$status = "";
if (isset($_POST['food_ID']) && $_POST['food_ID'] != "") {
    $code = $_POST['food_ID'];
    $result = mysqli_query($conn, "SELECT * FROM `food` WHERE `food_ID`='$code'");
    $row = mysqli_fetch_assoc($result);
    $name = $row['name'];
    $code = $row['food_ID'];
    $price = $row['price'];
    $image = $row['image'];
    $description = $row['description'];


    $cartArray = array(
        $code => array(
            'name' => $name,
            'food_ID' => $code,
            'price' => $price,
            'quantity' => 0,
            'image' => $image,
            'description' => $description
        )
    );

    if (empty($_SESSION["shopping_cart"])) {
        $_SESSION["shopping_cart"] = $cartArray;
        $status = "<div class='box'>Product is added to your cart!</div>";
    } else {
        $array_keys = array_keys($_SESSION["shopping_cart"]);
        if (in_array($code, $array_keys)) {
            $status = "<div class='box' style='color:red;'>
		Product is already added to your cart!</div>";
        } else {
            $_SESSION["shopping_cart"] = array_merge($_SESSION["shopping_cart"], $cartArray);
            $status = "<div class='box'>Product is added to your cart!</div>";
        }
    }
}
?>
<html>

<head>
    <title>Food Items</title>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/css/bootstrap.min.css" />
    <link rel="stylesheet" href="style.css" />
    <link rel='stylesheet' href='shopping.css' type='text/css' media='all' />
    <script src="bootstrap\js\jquery-3.5.1.js"></script>
    <script src="bootstrap\js\bootstrap.min.js"></script>

    <style>
        body {
            /* background-color: #f2f2f2; */
            background-color: royalblue;
            color: #333;
        }

        .main {
            box-shadow: 0 .125rem .25rem rgba(0, 0, 0, .075) !important;
            margin-top: 10px;
        }

        h3 {
            background-color: #f6b319;
            color: #f7f7f7;
            padding: 15px;
            border-radius: 4px;
            box-shadow: 0 1px 6px rgba(57, 73, 76, 0.35);
        }

        .img-box {
            margin-top: 20px;
        }

        .img-block {
            float: left;
            margin-right: 5px;
            text-align: center;
            color: white;
        }

        p {
            margin-top: 0;
        }

        img {
            width: 375px;
            min-height: 250px;
            margin-bottom: 10px;
            box-shadow: 0 .125rem .25rem rgba(0, 0, 0, .075) !important;
            border: 6px solid #f7f7f7;
        }
    </style>
</head>

<body>
    <header>
        <nav id="header-nav" class="navbar navbar-default">
            <div class="container">
                <div class="navbar-header">
                    <a href="index.php" class="pull-left visible-md visible-lg">
                        <div id="logo-img" alt="Logo image"></div>
                    </a>

                    <div class="navbar-brand">
                        <a href="index.php">
                            <h1>Kalvin's Restaurant</h1>
                        </a>
                        <p></p>
                    </div>

                    <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#collapsable-nav" aria-expanded="false">
                        <span class="sr-only">Toggle navigation</span>
                        <span class="icon-bar"></span>
                        <span class="icon-bar"></span>
                        <span class="icon-bar"></span>
                    </button>
                </div>

                <div id="collapsable-nav" class="collapse navbar-collapse">
                    <ul id="nav-list" class="nav navbar-nav navbar-right">
                        <li>
                            <a href="menu-categories.php">
                                <span class="glyphicon glyphicon-cutlery"></span><br class="hidden-xs" />
                                Menu
                            </a>
                        </li>
                        <li>
                            <a href="cart.php">
                                <span class="glyphicon glyphicon-shopping-cart"></span><br class="hidden-xs" />
                                Cart
                            </a>
                        </li>
                        <li>
                            <a href="index.php">
                                <span class="glyphicon glyphicon-home"></span><br class="hidden-xs" />
                                home
                            </a>
                        </li>
                        <li>
                            <a href="Register.html">
                                <span class="glyphicon glyphicon-user"></span><br class="hidden-xs" />
                                Sign up
                            </a>
                        </li>
                        <li>
                            <a href="login.php">
                                <span class="glyphicon glyphicon-log-in"></span><br class="hidden-xs" />
                                Login
                            </a>
                        </li>
                        <li>
                            <?php
                            //   session_start();

                            if (isset($_SESSION['User'])) {

                                echo '<a href="includes/logout.php?logout">
                <span class="glyphicon glyphicon-log-out"></span><br class="hidden-xs" />
                Logout
                </a>';
                                echo ' wellcome ' . $_SESSION['User'] . '<br/>';
                            } else {
                                header("location:login.php");
                            }

                            ?>
                        </li>

                        <li id="phone" class="hidden-xs">
                            <a href="tel.0743888952">
                                <span>0743888952</span>
                            </a>
                            <div>* Contact us</div>
                        </li>




                    </ul>
                    <!--#nav-list-->
                </div>
                <!--.collapse .navbar-collapse-->
            </div>
            <!--.container-->
        </nav>
        <!--#header-nav-->
    </header>
    <div style="width:700px; margin:50 auto;">

        <h2>Food Shopping Cart</h2>

        <?php
        if (!empty($_SESSION["shopping_cart"])) {
            $cart_count = count(array_keys($_SESSION["shopping_cart"]));
        ?>
            <div class="cart_div">
                <a href="cart.php" target="_blank"><span class="glyphicon glyphicon-shopping-cart">
                        <!--<img src="images/cart-icon.png" />--> Cart</span><br>
                    <div><?php echo $cart_count; ?></div>
                </a>
            </div>
        <?php
        }

        $result = mysqli_query($conn, "SELECT * FROM `food`");
        while ($row = mysqli_fetch_assoc($result)) {
            echo "<div class='product_wrapper'>
			  <form method='post' action=''>
              <input type='hidden' name='food_ID' value=" . $row['food_ID'] . " />
              <div class='food_ID'>" . $row['food_ID'] . "</div>
              <div class='image'><img src='" . $row['image'] . "' /></div>
              <div class='name'>" . $row['name'] . "</div>
              <span class='description'>" . $row['description'] . "</span>    
			 
                 <div class='price'>ksh" . $row['price'] . "</div>                 
			  <button type='submit' class='buy'>Buy Now</button>
			  </form>
		   	  </div>";
        }
        mysqli_close($conn);
        ?>

        <div style="clear:both;"></div>

        <div class="message_box" style="margin:10px 0px;">
            <?php echo $status; ?>
        </div>

        <br /><br />
        <footer class="panel-footer">
            <div class="container">
                <div class="row">
                    <section id="hours" class="col-sm-4">
                        <span>Hours</span><br />
                        Mon-Saturday 6.00am-10.00pm<br />
                        Closed on Sunday
                        <hr class="visible-xs" />
                    </section>
                    <section id="address" class="col-sm-4">
                        <span>Address</span><br />
                        Madaraka
                        <p>Delivery is free around Nairobi</p>
                        <hr class="visible-xs" />
                    </section>
                    <section id="testimonials" class="col-sm-4">
                        <p>The best Restaurant in town</p>
                        <p>Amazing food and I always come for more</p>
                        <p>The best customer service</p>
                    </section>
                </div>
                <div class="text-center">&copy; Copyright Kalvin's Restaurant 2020</div>
            </div>
        </footer>

    </div>
</body>

</html>