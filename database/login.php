<?php
session_start();

if(!isset($_SESSION['user']))
{
	header("Location: signin.php");
}
else
{
    session_destroy();
	unset($_SESSION['user']);
	header("Location: index.php");
}
?>