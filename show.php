<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, intial-scale=1.0" />
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
    <link rel="stylesheet" href="">
    <title>Display menu</title>
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
                    <a href="index.html" class="pull-left visible-md visible-lg">
                        <div id="logo-img" alt="Logo image"></div>
                    </a>

                    <div class="navbar-brand">
                        <a href="index.html">
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
                            <a href="menu-categories.html">
                                <span class="glyphicon glyphicon-cutlery"></span><br class="hidden-xs" />
                                Menu
                            </a>
                        </li>
                        <li>
                            <a href="#">
                                <span class="glyphicon glyphicon-info-sign"></span><br class="hidden-xs" />
                                About
                            </a>
                        </li>
                        <li>
                            <a href="index.html">
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

    <!-------------------Main Content------------------------------>
    <div class="container main">
        <h3>Lunch/ Dinner Menu</h3>
        <div class="img-box">

            <?php
            include "DBConnect.php";

            $result = mysqli_connect($dbserver, $dbuser, $dbpassword) or die("Could not connect to database." . mysqli_error($conn));
            mysqli_select_db($result, $dbname) or die("Could not select the databse." . mysqli_error($conn));
            $image_query = mysqli_query($result, "select food_ID,name, price, image, description from food");
            while ($row = mysqli_fetch_array($image_query)) {
                // $img_name = $rows['img_name'];
                // $img_src = $rows['img_path'];
                $food_ID = $row['food_ID'];
                $name = $row['name'];
                $price = $row['price'] . 'ksh';
                $image = $row['image'];
                $description= $row['description'];
            ?>



                <div class="menu-item-tile col-md-6">
                    <div class="row">
                        <div class="col-sm-5">
                            <div class="menu-item-photo">

                                <p><strong><?php echo $food_ID; ?></strong></p>
                                <img src="<?php echo $image; ?>" alt="" title="<?php echo $name; ?>" class="img-responsive" />
                                <p><strong><?php echo $name; ?></strong></p>
                                <p><strong><?php echo $price; ?></strong></p>
                                <!-- <p><strong><?php echo $description; ?></strong></p> -->


                            </div>
                            <!-- <div class="menu-item-price">50.ksh</div> -->
                        </div>
                        <div class="menu-item-description col-sm-7">
                            <!-- <h3 class="menu-item-title">Green Tea</h3> -->
                            <!-- <p class="menu-item-title">Lorem ipsum dolor sit amet consectetur adipisicing elit. Rem delectus numquam exercitationem eveniet id, nam facilis cupiditate, vitae earum unde libero pariatur a laudantium eius explicabo, sed placeat minus minima.</p> -->
                            <p><strong><?php echo $description; ?></strong></p>
                        </div>
                    </div>
                    <hr class="visible-xs" />
                </div>

            <?php
            }
            ?>


        </div>
    </div>
    <!-- end of main content -->
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