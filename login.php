<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Login form with session</title>
  <link rel="stylesheet" href="login.css">




  <!-- <a href="Register.html">register</a> -->

</head>

<body>
  <a href="index.php">
    <h2>Kalvin's Restaurant</h2>
  </a>
  <!-- <h2>Kalvin's Restaurant</h2>   -->
  <div class="wrapper">
    <img src="images/avatar.png" alt="" class="avatar">
    <h1>Login Here</h1>

    <?php
    if (@$_GET['Empty'] == true) {
    ?>
      <div class="alert-light text-danger text-center py-3"><?php echo $_GET['Empty'] ?></div>
    <?php
    }
    ?>


    <?php
    if (@$_GET['Invalid'] == true) {
    ?>
      <div class="alert-light text-danger text-center py-3"><?php echo $_GET['Invalid'] ?></div>
    <?php
    }
    ?>

    <form action="includes/process.php" method="POST" class="contact-form">
      <div class="input-fields">
        <label for="name">username</label>
        <input type="text" id="name" name="username" class="input" placeholder="enter username or email"><br>


        <label for="password">Password</label>
        <input type="password" id="password" name="userpassword" class="input" placeholder="enter password" /><br>
        <a href="forgot password?" id="fp">Forgot password?</a><br>

        <button type="submit" class="btn" name="Login">login</button>

        <p>Not a member yet? <span><a href="Register.php" id="spn">Register</a></span></p>
      </div>


    </form>
  </div>

  </div>

</body>

</html>