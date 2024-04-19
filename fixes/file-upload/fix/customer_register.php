<?php
session_start();
include("functions/functions.php");
include("includes/db.php");
global $con;
if (isset($_POST['register'])) {
    $ip = getIp();
    $c_name = mysqli_real_escape_string($con, $_POST['c_name']);
    $c_email = mysqli_real_escape_string($con, $_POST['c_email']);
    $c_pass = mysqli_real_escape_string($con, $_POST['c_pass']);
    $c_passport = mysqli_real_escape_string($con, $_POST['c_passport']);
    $c_country = mysqli_real_escape_string($con, $_POST['c_country']);
    $c_city = mysqli_real_escape_string($con, $_POST['c_city']);
    $c_contact = mysqli_real_escape_string($con, $_POST['c_contact']);
    $c_address = mysqli_real_escape_string($con, $_POST['c_address']);

    // File information
    $uploaded_name = $_FILES[ 'c_image' ][ 'name' ];
    $uploaded_ext  = substr( $uploaded_name, strrpos( $uploaded_name, '.' ) + 1);
    $uploaded_size = $_FILES[ 'c_image' ][ 'size' ];
    $uploaded_type = $_FILES[ 'c_image' ][ 'type' ];
    $uploaded_tmp  = $_FILES[ 'c_image' ][ 'tmp_name' ];

    // Where are we going to be writing to?
    $target_path   = "customer/customer_images/";
    //$target_file   = basename( $uploaded_name, '.' . $uploaded_ext ) . '-';
    $target_file   =  md5( uniqid() . $uploaded_name ) . '.' . $uploaded_ext;
    $temp_file     = ( ( ini_get( 'upload_tmp_dir' ) == '' ) ? ( sys_get_temp_dir() ) : ( ini_get( 'upload_tmp_dir' ) ) );
    $temp_file    .= DIRECTORY_SEPARATOR . md5( uniqid() . $uploaded_name ) . '.' . $uploaded_ext;

    // Is it an image?
    if( ( strtolower( $uploaded_ext ) == 'jpg' || strtolower( $uploaded_ext ) == 'jpeg' || strtolower( $uploaded_ext ) == 'png' ) &&
        ( $uploaded_type == 'image/jpeg' || $uploaded_type == 'image/png' ) &&
        getimagesize( $uploaded_tmp ) ) {

        // Strip any metadata, by re-encoding image (Note, using php-Imagick is recommended over php-GD)
        if( $uploaded_type == 'image/jpeg' ) {
            $img = imagecreatefromjpeg( $uploaded_tmp );
            imagejpeg( $img, $temp_file, 100);
        }
        else {
            $img = imagecreatefrompng( $uploaded_tmp );
            imagepng( $img, $temp_file, 9);
        }
        imagedestroy( $img );

        // Can we move the file to the web root from the temp folder?
        if( rename( $temp_file, ( getcwd() . DIRECTORY_SEPARATOR . $target_path . $target_file ) ) ) {
            // Yes!

            $query = $con->prepare("INSERT INTO customers (customer_ip,customer_name,customer_email,customer_pass,c_passport,customer_country,customer_city,customer_contact,customer_address,customer_image) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

            $query->bind_param('ssssssssss', $ip, $c_name, $c_email, $c_pass, $c_passport,$c_country, $c_city, $c_contact, $c_address, $uploaded_name);
            $query->execute();

            $sel_car_query = $con->prepare("SELECT * FROM cart WHERE ip_add=?");
            $sel_car_query->bind_param('s', $ip);
            $sel_car_query->execute();
        
            $run_cart = $sel_car_query->get_result();
        
            $check_cart = mysqli_num_rows($run_cart);
        
            if ($check_cart == 0) {
                $_SESSION['customer_email'] = $c_email;
                echo "<script>alert('Account has been created successfully. Thanks!')</script>";
                echo "<script>window.open('customer/my_account.php','_self')</script>";
            } else {
                $_SESSION['customer_email'] = $c_email;
                echo "<script>alert('Account has been created successfully. Thanks!')</script>";
                echo "<script>window.open('checkout.php','_self')</script>";
            }
        }
        else {
            // No
            echo '<pre>Your image was not uploaded.</pre>';
        }

        // Delete any temp files
        if( file_exists( $temp_file ) )
            unlink( $temp_file );
    }
    else {
        // Invalid file
        echo '<pre>Your image was not uploaded. We can only accept JPEG or PNG images.</pre>';
    } 
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Travel Bird : Register</title>
    <link rel="stylesheet" href="styles/style.css" media="all">
    <style type="text/css">
        #fixm {
            font-size: 15px;
            padding: 4px;
            font-family: arial;
        }

        #fixi {
            font-size: 14px;
            font-family: arial;
        }

        #btn {
            font-family: arial;
            font-size: 14px;
            background-color: #4CAF50; /* Green */
            border: none;
            color: white;
            padding: 10px 10px;
            text-align: center;
            text-decoration: none;
            display: inline-block;
            font-size: 16px;
            margin: 4px 2px;
            transition-duration: 0.4s;
            cursor: pointer;
            background-color: #32E875;
            color: black;
        }

        #btn:hover {
            background-color: #4CAF50;
            color: white;
        }
    </style>
</head>
<body>
    <!--Main container starts here-->
    <div class="main_wrapper">
        <!--Header starts here-->
        <?php include 'includes/header.php'; ?>
        <!--Header ends here-->
        <!--Navbar starts here-->
        <?php include 'includes/navbar.php'; ?>
        <!--Navbar ends here-->
        <!--Content starts here-->
        <div class="content_wrapper">
            <!--left-sidebar starts-->
            <?php include "includes/left-sidebar.php"; ?>
            <!--left-sidebar ends-->
            <div id="content_area">
                <?php cart(); ?>
                <div id="shopping_cart">
                        <span style="float: right;font-size: 18px;padding: 5px;line-height: 40px;">Welcome Guest! <b
                                    style="color: yellow;">Shopping Cart-</b> Total Items: <?php total_items(); ?> Total Price: <?php total_price(); ?> <a
                                    href="cart.php" style="color: yellow;">Go to Cart</a></b></span>
                </div>
                <form action="customer_register.php" method="post" enctype="multipart/form-data">
                    <table align="center" width="750" style="margin-top: 20px;">
                        <tr align="center">
                            <td colspan="6">
                                <h2 style="margin-bottom: 15px; font-family: Cambria;">Create an Account</h2>
                            </td>
                        </tr>
                        <tr>
                            <td align="right" id="fixm">Your Name:</td>
                            <td><input id="fixi" type="text" name="c_name" required=""></td>
                        </tr>
                        <tr>
                            <td align="right" id="fixm">Your Email:</td>
                            <td><input type="email" name="c_email" required=""></td>
                        </tr>
                        <tr>
                            <td align="right" id="fixm">Your Password:</td>
                            <td><input id="fixi" type="password" name="c_pass" required=""></td>
                        </tr>
                        <tr>
                            <td align="right" id="fixm">Your Passport ID:</td>
                            <td><input type="text" name="c_passport" required=""></td>
                        </tr>
                        <tr>
                            <td align="right" id="fixm">Your Image:</td>
                            <td><input id="fixi" type="file" name="c_image" required=""></td>
                        </tr>
                        <tr>
                            <td align="right" id="fixm">Your Country:</td>
                            <td>
                                <select name="c_country" required="">Select a
                                    <option>Bangladesh</option>
                                    <option>India</option>
                                    <option>Japan</option>
                                    <option>China</option>
                                    <option>Russia</option>
                                    <option>Portugal</option>
                                    <option>England</option>
                                    <option>Brazil</option>
                                    <option>Spain</option>
                                    <option>France</option>
                                    <option>Switzerland</option>
                                    <option>Croatia</option>
                                    <option>Argentina</option>
                                </select>
                            </td>
                        </tr>
                        <tr>
                            <td align="right" id="fixm">Your City:</td>
                            <td><input id="fixi" type="text" name="c_city" required=""></td>
                        </tr>
                        <tr>
                            <td align="right" id="fixm">Your Contact:</td>
                            <td><input id="fixi" type="text" name="c_contact" required=""></td>
                        </tr>
                        <tr>
                            <td align="right" id="fixm">Your Address:</td>
                            <td><input id="fixi" type="text" name="c_address" required=""></td>
                        </tr>
                        <tr align="center">
                            <td colspan="6"><input id="btn" type="submit" name="register" value="Create Account">
                            </td>
                        </tr>
                    </table>
                </form>
            </div>
        </div>
        <!--Content ends here-->
        <!--footer starts-->
        <?php include "includes/footer.php"; ?>
        <!--footer ends-->
    </div>
    <!--Main container ends here-->
</body>
</html>
