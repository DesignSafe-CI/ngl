<?php
session_start();
include_once 'includes/dbconnect.php'; // connect to MySQL database

$lastpage = $_SESSION['lastpage'];
// Check username / password and head to last page
if(isset($_POST['btn-login']))
{
    // Sanitize and validate the data passed in
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $upass = filter_input(INPUT_POST, 'user_pass', FILTER_SANITIZE_STRING);
    
    // Search email and match email 
    $prep_stmt = "SELECT user_id, first_name, user_pass, num_visit FROM members WHERE email = '$email' LIMIT 1";
    $result = $mysqli->query($prep_stmt);
    $row = $result->fetch_assoc();
    if ($result->num_rows == 1 && $row["user_pass"] == $upass) {
        $_SESSION["user"] = $row["first_name"]; // Session on with first name
        $_SESSION["user_id"] = $row["user_id"]; // Session on with user_id
        // Count number of visit
        $new_vist = $row["num_visit"]+1;        
        $prep_stmt = "UPDATE members SET num_visit='$new_visit' WHERE email = '$email'";
        $mysqli->query($prep_stmt);
        
        header("Location: $lastpage");        
        $result->close();
    } else {
        $invalidErr = "Invalid e-mail / password!";
        $result->close();
    }
}
?>
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>NGL</title>
    <link rel="stylesheet" href="css/NGL.css" type="text/css" />
</head>
<body>
    <!--Start of header-->
    <?php include_once 'includes/head.html';?>
    <div id="head" class="text">
        <h1>Next-Generation Liquefaction Database</h1>
    </div>
    <!--End of header-->
    
    <div id="container" class="index">
<!--        <h3>Notice:</h3>-->
        <p>The Next-Generation Liquefaction (NGL) Database has been developed for researchers/engineers to access liquefaction case history data shared by other researchers/engineers, and to share their own data in return. The NGL project vision, objective, main organizations, and recent activities can be found in the project main page (<a href="http://uclageo.com/NGL/">uclageo.com/NGL</a>).</p>
        <p>To access the database the user may need to register below.</p>
        <p>Please contact us for further assistance.<br>
        <a href="mailto:ngl@uclageo.com?Subject=NGL Database" target="_top">ngl@uclageo.com</a></p>
        <p align="right"><i>Last updated: Mar 9, 2016</i></p>
    </div>
    
    <form method="post">
        <div id="index">        
            <table align="center" width="300px" border="0">            
            <tr>
                <td align="left">E-mail:</td>
                <td><input type="text" name="email" required /></td>                
            </tr>
            <tr>
                <td align="left">Password:</td>
                <td><input type="password" name="user_pass" required /></td>                
            </tr>
            </table>
        </div>
        <div id="input-form">
            <table align="center" width="250px" border="0">
                <tr>
                <td colspan=2 class="error"><?php echo $invalidErr;?></td>
            </tr>
                <tr>
                <td colspan=2 align="center"><button type="submit" name="btn-login">Sign In</button></td>
            </tr>
            <tr>
                <td align="center" width="50%"><a href="register.php">New User</a></td>
                <td align="center" width="50%"><a href="forgot_pw.php">Forgot Password?</a></td>
            </tr>
            </table>
        </form>
    </div>
    
</body>
</html>