<?php

error_reporting( E_ALL & ~E_DEPRECATED & ~E_NOTICE );

$host = "localhost"; // The host you want to connect to. Add port 8889 for MAMP.

$user = "sjbrande_ngl_usr"; // The database user name. 'root' for MAMP.

$password = "XXXXXXXXXXXXXXX"; // The database password. 'root' for MAMP.

$database = "sjbrande_ngl"; // The database name. 

    

$mysqli = new mysqli($host,$user,$password,$database);



/* check connection */

if (mysqli_connect_errno()) {

    printf("Connect failed: %s\n", mysqli_connect_error());

    exit();

}

if(!$mysqli->select_db($database))

{

	printf("Invalid database: %s\n", mysqli_error());

}

?>
