<?php
session_start();
// If logged in, go to index.php
if(isset($_SESSION['user'])!="")
{
	header("Location: index.php");
}
include_once 'includes/dbconnect.php'; // connect to MySQL database

// New data fillin to mysql 
if(isset($_POST['btn-signup']))
{
	// Sanitizing 
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    
    // select password (user_pass) from sql
    $prep_stmt = "SELECT first_name, user_pass FROM members WHERE email = '$email' LIMIT 1";
    $result = $mysqli->query($prep_stmt);
    $row = $result->fetch_assoc();
    
    if ($result->num_rows == 0) {
        $registerMsg = 'E-mail is not registered.';
    } else {
        $to      = $email; // Send email to our user
        $subject = 'NGL Database Password'; // Give the email a subject
        $firstname = $row["first_name"];
        $password = $row["user_pass"];
        $registerMsg = $password;
        $message = 
"Dear $firstname,
    
Your password is $password.
                
Please sign in again.
http://uclageo.com/NGL/database/index.php

Thank you,

NGL Database team";
        $headers = 'From:ngl@uclageo.com' . "\r\n"; // Set from headers
        if(mail($to, $subject, $message, $headers)){ // Send our email
            header("Location: forgot_pw_success.php");        
        }
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
    
    <div id="input-form">
        <form method="post">
        <table align="center" width="300px" border="0">
            <tr>
            <td><span class="error">Enter e-mail address.</span></td>
            </tr>
            <tr>
                <td><input type="email" name="email" required value="<?php echo htmlspecialchars($_POST['email']);?>"></td>
            </tr>
        </table>
    </div>
    <div id="input-form">
        <table align="center" width="200px" border="0">
            <td class="error" colspan=2><?php echo $registerMsg;?></td>
            <tr>
                <td align="center" colspan=2><button type="submit" name="btn-signup">Submit</button></td>
            </tr>
            <tr>
                <td align="center"><a href=register.php>New User</a></td>
                <td align="center" width="50%"><a href=signin.php>Sign In</a></td>
                
            </tr>
        </table>
        </form>
    </div>    
</body>
</html>