<?php

include('uploadcode.php'); // Include upload code Script page.

?>

<html>

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, intial-scale=1.0" />
    <title>Image Upload</title>
    <style>
        html,
        body {
            background: #ececec;
            height: 100%;
            margin: 0;
            font-family: Arial;
        }

        .main {
            height: 100%;
            display: flex;
            justify-content: center;
        }

        .main .image-box {
            width: 300px;
            margin-top: 30px;
        }

        .main h2 {
            text-align: center;
            color: #4D4D4D;
        }

        .main .tb {
            width: 100%;
            height: 40px;
            margin-bottom: 5px;
            padding-left: 5px;
        }

        .main .file_input {
            margin-top: 10px;
            margin-bottom: 10px;
        }

        .main .btn {
            width: 100%;
            height: 40px;
            border: none;
            border-radius: 3px;
            background: #27a465;
            color: #f7f7f7;
        }

        .main .msg {
            color: red;
            text-align: center;
        }
    </style>
</head>

<body>
    <!-------------------Main Content------------------------------>
    <div class="container main">
        <div class="image-box">
            <h2>Add food item to menu</h2>
            <!-- <form method="POST" name="upfrm" action="" enctype="multipart/form-data">
                <div>
                    <input type="text" placeholder="Enter food ID" name="food_ID" class="tb" />
                    <input type="text" placeholder="Enter food name" name="name" class="tb" />
                    <input type="text" placeholder="Enter food price" name="price" class="tb" />
                    <input type="text" placeholder="Enter food description" name="description" class="tb" />
                    <input type="file" name="fileImg" class="file_input" />

                    <input type="submit" value="Upload" name="btn_upload" class="btn" />


                </div>
            </form> -->

            <form method="post" enctype="multipart/form-data" class="form-horizontal">

                <table class="table table-bordered table-responsive">

                    <tr>
                        <!-- <td><label class="control-label">Username.</label></td> -->
                        <td><input class="form-control" type="text" name="user_name" placeholder="Enter Username" value="<?php echo $username; ?>" /></td>
                    </tr>

                    <tr>
                        <td><label class="control-label">Profession(Job).</label></td>
                        <td><input class="form-control" type="text" name="user_job" placeholder="Your Profession" value="<?php echo $userjob; ?>" /></td>
                    </tr>

                    <tr>
                        <td><label class="control-label">Profile Img.</label></td>
                        <td><input class="input-group" type="file" name="user_image" accept="image/*" /></td>
                    </tr>

                    <tr>
                        <td colspan="2"><button type="submit" name="btnsave" class="btn btn-default">
                                <span class="glyphicon glyphicon-save"></span> &nbsp; save
                            </button>
                        </td>
                    </tr>

                </table>

            </form>


            <div class="msg">
                <strong>
                    <?php if (isset($error)) {
                        echo $error;
                    } ?>
                </strong>

            </div>
        </div>
    </div>
</body>

</html>