<?php

session_start();
include('orderupload.php'); // Include upload code Script page.

$status = "";
if (isset($_POST['action']) && $_POST['action'] == "remove") {
  if (!empty($_SESSION["shopping_cart"])) {
    foreach ($_SESSION["shopping_cart"] as $key => $value) {
      if ($_POST["food_ID"] == $key) {
        unset($_SESSION["shopping_cart"][$key]);
        $status = "<div class='box' style='color:red;'>
		        Product is removed from your cart!</div>";
      }
      if (empty($_SESSION["shopping_cart"]))
        unset($_SESSION["shopping_cart"]);
    }
  }
}

if (isset($_POST['action']) && $_POST['action'] == "change") {
  foreach ($_SESSION["shopping_cart"] as &$value) {
    if ($value['food_ID'] === $_POST["food_ID"]) {
      $value['quantity'] = $_POST["quantity"];
      break; // Stop the loop after we've found the product
    }
  }
}
?>
<html>

<!-- <head>
    <title>Food shopping Cart</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/css/bootstrap.min.css" />
  <link rel="stylesheet" href="style.css" />
  <script src="bootstrap\js\jquery-3.5.1.js"></script>
  <script src="bootstrap\js\bootstrap.min.js"></script>
   <link rel='stylesheet' href='shopping.css' type='text/css' media='all' />
</head> -->

<head>
  <title>Food shopping Cart</title>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/css/bootstrap.min.css" />
  <link rel="stylesheet" href="style.css" />
  <link rel='stylesheet' href='shopping.css' type='text/css' media='all' />
  <script src="bootstrap\js\jquery-3.5.1.js"></script>
  <script src="bootstrap\js\bootstrap.min.js"></script>
  <style>
    .cala{
      color: black;
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
              // session_start();

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
        <a href="cart.php">
          <img src="images/cart-icon.png" /> Cart
          <span><?php echo $cart_count; ?></span></a>
      </div>
    <?php
    }
    ?>

    <div class="cart">
      <?php
      if (isset($_SESSION["shopping_cart"])) {
        $total_price = 0;
      ?>
        <table class="table">
          <tbody>
            <tr>
              <td></td>
              <td class="cala">ITEM NAME</td>
              <td class="cala">QUANTITY</td>
              <td class="cala">UNIT PRICE</td>
              <td class="cala">ITEMS TOTAL</td>
            </tr>
            <?php
            foreach ($_SESSION["shopping_cart"] as $product) {
            ?>
              <tr>
                <td><img src='<?php echo $product["image"]; ?>' width="50" height="40" /></td>
                <td><?php echo $product["name"]; ?><br />
                  <form method='post' action=''>
                    <input type='hidden' name='food_ID' value="<?php echo $product["food_ID"]; ?>" />
                    <input type='hidden' name='action' value="remove" />
                    <button type='submit' class='remove'>Remove Item</button>
                  </form>
                </td>
                <td>
                  <form method='post' action=''>
                    <input type='hidden' name='food_ID' value="<?php echo $product["food_ID"]; ?>" />
                    <input type='hidden' name='action' value="change" />
                    <select name='quantity' class='quantity' onChange="this.form.submit()">
                      <option <?php if ($product["quantity"] == 1) echo "selected"; ?> value="1" class="cala">1</option>
                      <option <?php if ($product["quantity"] == 2) echo "selected"; ?> value="2" class="cala">2</option>
                      <option <?php if ($product["quantity"] == 3) echo "selected"; ?> value="3" class="cala">3</option>
                      <option <?php if ($product["quantity"] == 4) echo "selected"; ?> value="4" class="cala">4</option>
                      <option <?php if ($product["quantity"] == 5) echo "selected"; ?> value="5" class="cala">5</option>
                    </select>
                  </form>
                </td>
                <td><?php echo "ksh" . $product["price"]; ?></td>
                <td><?php echo "ksh" . $product["price"] * $product["quantity"]; ?></td>
              </tr>
            <?php
              $total_price += ($product["price"] * $product["quantity"]);
            }
            ?>
            <tr>
              <td colspan="5" align="right">
                <strong>TOTAL: <?php echo "ksh" . $total_price; ?></strong>
              </td>
            </tr>
          </tbody>
        </table>
        <center>
          <!-- <h1><button type='submit' class='remove'>Chek out</button></h1> -->
         <a href="#"> <input type="submit" value="order" name="btn_upload" class="btn cala" /></a>
        </center>
      <?php
      } else {
        echo "<h3>Your cart is empty!</h3>";
      }
      ?>
    </div>

    <div style="clear:both;"></div>

    <div class="message_box" style="margin:10px 0px;">
      <?php echo $status; ?>
    </div>


    <br /><br />

  </div>

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
</body>

</html>