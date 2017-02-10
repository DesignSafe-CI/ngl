<?php

// uclageo.com server connect 
error_reporting( E_ALL & ~E_DEPRECATED & ~E_NOTICE );
$servername = "localhost"; // The host you want to connect to.
$username = "XXXXXXXXXXXXXX"; // The database user name.
$password = "YYYYYYYYYYYYYY"; // The database password.
$dbase = "ZZZZZZZZZZZZZZZZ"; // The database name. 

// Create connection
$conn = new mysqli($servername, $username, $password, $dbase);
// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
} 

//// Output table
//$table = 'members';
//$file = 'export';
//$result = mysqli_query("SHOW COLUMNS FROM ".$table."");
//$i = 0;
//if (mysqli_num_rows($result) > 0) {
//while ($row = mysqli_fetch_assoc($result)) {
//$csv_output .= $row['Field']."; ";
//$i++;
//}
//}
//$csv_output .= "\n";
//$values = mysqli_query("SELECT * FROM ".$table."");
//while ($rowr = mysqli_fetch_row($values)) {
//for ($j=0;$j<$i;$j++) {
//$csv_output .= $rowr[$j]."; ";
//}
//$csv_output .= "\n";
//}
//$filename = $file."_".date("Y-m-d_H-i",time());
//header("Content-type: application/vnd.ms-excel");
//header("Content-disposition: csv" . date("Y-m-d") . ".csv");
//header("Content-disposition: filename=".$filename.".csv");
//print $csv_output;
//exit;

//// Create database
//$conn = new mysqli($servername, $username, $password);
//$sql = "CREATE DATABASE ngldatabase";
//if ($conn->query($sql) === TRUE) {
//    echo "Database created successfully";
//} else {
//    echo "Error creating database: " . $conn->error;
//}

//// sql to create table members
//$sql = "CREATE TABLE members (
//user_id INT(6) PRIMARY KEY AUTO_INCREMENT, 
//first_name VARCHAR(30),
//last_name VARCHAR(30),
//email VARCHAR(30),
//reg_date TIMESTAMP,
//organ VARCHAR(30),
//country VARCHAR(30),
//region VARCHAR(30),
//zip VARCHAR(10),
//user_pass VARCHAR(30),
//num_visit INT(6),
//num_download INT(6),
//num_upload INT(6)
//)";

// sql to create table site information
$sql = "CREATE TABLE PROJ (
site_id INT(6) PRIMARY KEY AUTO_INCREMENT, 
user_id INT(6),
site_name VARCHAR(50),
lat FLOAT(10),
lon FLOAT(10),
elev VARCHAR(10),
geol VARCHAR(20),
note VARCHAR(1000),
status VARCHAR(10)
)";

//// sql to create table location details
//$sql = "CREATE TABLE LOCA (
//id INT(6) PRIMARY KEY AUTO_INCREMENT, 
//user_id INT(6),
//site_id INT(6),
//loca_id VARCHAR(20),
//loca_type VARCHAR(10),
//lat FLOAT(10),
//lon FLOAT(10),
//elev VARCHAR(10),
//fdepth VARCHAR(10),
//start VARCHAR(20),
//end VARCHAR(20),
//note VARCHAR(1000),
//checked VARCHAR(1)
//)";

//// sql to create table lab test information
//$sql = "CREATE TABLE SAMP (
//id INT(6) PRIMARY KEY AUTO_INCREMENT, 
//user_id INT(6),
//site_id INT(6), 
//loca_id VARCHAR(20),
//samp_id VARCHAR(20),
//sieve VARCHAR(5),
//density VARCHAR(10),
//wc VARCHAR(10),
//ll VARCHAR(10),
//pi VARCHAR(10),
//other VARCHAR(20),
//checked VARCHAR(1)
//)";

//// sql to create table event information
//$sql = "CREATE TABLE EVNG (
//evt_id INT(6) PRIMARY KEY AUTO_INCREMENT, 
//user_id INT(6),
//site_id INT(6),
//evt_name VARCHAR(50),
//mag FLOAT(4),
//date VARCHAR(20),
//lat FLOAT(10),
//lon FLOAT(10),
//depth VARCHAR(10),
//strike VARCHAR(4),
//dip VARCHAR(4),
//rake VARCHAR(4),
//note VARCHAR(1000)
//)";

//// sql to create table Field Observation
//$sql = "CREATE TABLE FLDO (
//id INT(6) PRIMARY KEY AUTO_INCREMENT, 
//user_id INT(6),
//site_id INT(6), 
//evt_id INT(6),
//lat FLOAT(10),
//lon FLOAT(10),
//gf VARCHAR(10),
//lqmani VARCHAR(10),
//source VARCHAR(30),
//note VARCHAR(1000),
//filename VARCHAR(256)
//)";

//// sql to create table Ground Motion
//$sql = "CREATE TABLE GRMN (
//id INT(6) PRIMARY KEY AUTO_INCREMENT, 
//user_id INT(6),
//site_id INT(6), 
//evt_id INT(6),
//pga FLOAT(8),
//pgv FLOAT(8),
//sa02 FLOAT(8),
//sa10 FLOAT(8),
//sa30 FLOAT(8),
//cav5 FLOAT(8),
//d595 FLOAT(8),
//rec VARCHAR(3)
//)";

//// sql to delete table
//$sql = "DROP TABLE members";
//$sql = "DROP TABLE PROJ";
//$sql = "DROP TABLE LOCA";
//$sql = "DROP TABLE SAMP";
//$sql = "DROP TABLE EVNG";
//$sql = "DROP TABLE FLOB";
//$sql = "DROP TABLE GRMN";

if ($conn->query($sql) === TRUE) {
    echo "Table members created successfully";
} else {
    echo "Error creating table: " . $conn->error;
}

$conn->close();
?>
