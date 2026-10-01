<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Sign Up Form</title>

    <link rel="stylesheet" href="login.css" />
    <style>
      .wrapper {
        width: 320px;
        height: 480px;
      }
    </style>
  </head>
  <body>
    <a href="admnindex.php"><h2>Kalvin's Restaurant</h2></a>
    <div class="wrapper">
      <img src="images/avatar.png" alt="" class="avatar" />
      <h1>Add admin</h1>

      <form action="includes/admnconf.php" method="POST" class="contact-form">
        <div class="input-fields">
          <label for="name">Name</label>
          <input
            type="text"
            id="name"
            name="user_name"
            class="input"
            placeholder="username"
          />

          <label for="mail">Email</label>
          <input
            type="email"
            id="mail"
            name="user_email"
            class="input"
            placeholder="email"
          />

          <label for="password">Password</label>
          <input
            type="password"
            id="password"
            name="user_password"
            class="input"
            placeholder="password"
          />

          <label for="c-password">confirm password</label>
          <input
            type="password"
            id="c-password"
            name="confirmpassword"
            class="input"
            placeholder="password"
          /><br />

          <button type="submit" name="register" class="btn">Sign Up</button>
          <!-- <input type="submit" name="register" value="REGISTER"> -->

          <p>
            already a member?
            <span><a href="admnlogin.php" id="spn">login</a></span>
          </p>
        </div>
      </form>
    </div>
  </body>
</html>
