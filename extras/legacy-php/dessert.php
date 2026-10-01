<!DOCTYPE html>
<html lang="en">
  <head>
    <title>Restaurant</title>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <link
      rel="stylesheet"
      href="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/css/bootstrap.min.css"
    />
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
              <a href="index.php"><h1>Kalvin's Restaurant</h1></a>
              <p></p>
            </div>

            <button
              type="button"
              class="navbar-toggle collapsed"
              data-toggle="collapse"
              data-target="#collapsable-nav"
              aria-expanded="false"
            >
              <span class="sr-only">Toggle navigation</span>
              <span class="icon-bar"></span>
              <span class="icon-bar"></span>
              <span class="icon-bar"></span>
            </button>
          </div>

          <div id="collapsable-nav" class="collapse navbar-collapse">
            <ul id="nav-list" class="nav navbar-nav navbar-right">
              <li class="active">
                <a href="menu-categories.php">
                  <span class="glyphicon glyphicon-cutlery"></span
                  ><br class="hidden-xs" />
                  Menu
                </a>
              </li>
              <li>
                <a href="#">
                  <span class="glyphicon glyphicon-info-sign"></span
                  ><br class="hidden-xs" />
                  About
                </a>
              </li>
              <li>
                <a href="index.php">
                  <span class="glyphicon glyphicon-home"></span
                  ><br class="hidden-xs" />
                  home
                </a>
              </li>
              <li>
                <a href="login.php">
                  <span class="glyphicon glyphicon-log-in"></span
                  ><br class="hidden-xs" />
                  Login
                </a>
              </li>
              <li>
                <a href="Register.html">
                  <span class="glyphicon glyphicon-user"></span
                  ><br class="hidden-xs" />
                  Sign up
                </a>
              </li>
              <li id="phone" class="hidden-xs">
                <a href="tel.0743888952">
                  <span>0743888952</span>
                </a>
                <div>* contact us</div>
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
      <h2 id="menu-categories-title" class="text-center">Dessert</h2>
      <section class="row">
        <div class="menu-item-tile col-md-4">
          <div class="row">
            <div class="col-sm-5">
              <div class="menu-item-photo">
                <div>01</div>
                <img
                  src="images/choco-cake.jpg"
                  alt="Item"
                  class="img-responsive"
                  width="250"
                  height="150"
                />
              </div>
              <div class="menu-item-price">150.ksh</div>
            </div>
            <div class="menu-item-description col-sm-7">
              <h3 class="menu-item-title">Chocolate cupcake</h3>
            </div>
          </div>
          <hr class="visible-xs" />
        </div>

        <div class="menu-item-tile col-md-4">
          <div class="row">
            <div class="col-sm-5">
              <div class="menu-item-photo">
                <div>02</div>
                <img
                  src="images/ice-cream-cookie.jpg"
                  alt="Item"
                  class="img-responsive"
                  width="250"
                  height="150"
                />
              </div>
              <div class="menu-item-price">100.ksh</div>
            </div>
            <div class="menu-item-description col-sm-7">
              <h3 class="menu-item-title">Ice cream on cookie</h3>
            </div>
          </div>
          <hr class="visible-xs" />
        </div>

        <div class="menu-item-tile col-md-4">
          <div class="row">
            <div class="col-sm-5">
              <div class="menu-item-photo">
                <div>03</div>
                <img
                  src="images/oreo.jpg"
                  alt="Item"
                  class="img-responsive"
                  width="250"
                  height="150"
                />
              </div>
              <div class="menu-item-price">200.ksh</div>
            </div>
            <div class="menu-item-description col-sm-7">
              <h3 class="menu-item-title">oreo milkshake</h3>
            </div>
          </div>
          <hr class="visible-xs" />
        </div>
      </section>
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
