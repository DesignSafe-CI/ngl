<?php
session_start();
include_once 'includes/dbconnect.php';

if(!isset($_SESSION['user']))
{
	$signout = 'Sign In';
} else {
    $signout = 'Sign Out';
}
$_SESSION['lastpage'] = 'help.php';
?>
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta content="text/html; charset=utf-8" http-equiv="Content-Type" />
    <title>NGL</title>
    <!--Style-->
    <link href="css/NGL.css" rel="stylesheet" type="text/css" />
</head>
<body>
    <!--Start of header-->
    <?php include_once 'includes/head.html';?>
    <div id="head" class="text">
        <h1>Next-Generation Liquefaction Database</h1>
    </div>
    <!--End of header-->
    
    <div id="container">
        <center>Coming soon...</center>
    </div>

</body>
</html>