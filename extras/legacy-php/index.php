<?php

require_once("DBConnect.php");
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <title>Restaurant</title>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/css/bootstrap.min.css" />
  <link rel="stylesheet" href="style.css" />
  <script src="bootstrap\js\jquery-3.5.1.js"></script>
  <script src="bootstrap\js\bootstrap.min.js"></script>
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
              session_start();

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

  <div id="call-btn" class="visible-xs">
    <a href="tel:0743888952" class="btn">
      <span class="glyphicon glyphicon-earphone"></span>
      0743888952
    </a>
  </div>
  <div id="xs-deliver" class="text-center visible-xs">* We Deliver</div>

  <div id="main-content" class="container">
    <div class="jumbotron">
      <img src="images/home.jpg" alt="Picture of Restaurant" class="img-responsive visible-xs" />
    </div>

    <div id="home-tiles" class="row">
      <div class="col-md-4" col-sm-6 col-xs-12>
        <a href="menu-categories.php">
          <div id="menu-tile"><span>menu</span></div>
        </a>
      </div>
      <div class="col-md-4 col-sm-6 col-xs-12">
        <a href="dessert.php">
          <div id="specials-tile"><span>Dessert</span></div>
        </a>
      </div>
      <div class="col-md-4 col-sm-6 col-xs-12">
        <a href="https://goo.gl/maps/tx162utHrrE6P7or8" target="_blank">
          <div id="map-tile">
            <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3988.7801959200206!2d36.8133253147541!3d-1.3070589990466848!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x182f112e9eff4827%3A0x17a918597484c8ea!2sStrathmore%20University!5e0!3m2!1sen!2ske!4v1601115366432!5m2!1sen!2ske" width="100%" height="250" frameborder="0" style="border: 0" allowfullscreen="" aria-hidden="false" tabindex="0"></iframe>
            <span>map</span>
          </div>
        </a>
      </div>
    </div>
    <!--end of #home-tiles-->
  </div>
  <!--end of #main-content-->

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