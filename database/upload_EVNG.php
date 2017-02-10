<?php
session_start();
include_once 'includes/dbconnect.php';
include_once 'includes/tmp_load.php';

if(!isset($_SESSION['user']))
{
	$signout = 'Sign In';
} else {
    $user_id = $_SESSION['user_id'];
    $signout = 'Sign Out';
}
///////////////////////////////////////////////////////////////////////////////
// Last page to be returned after sign in
$_SESSION['lastpage'] = 'upload_PROJ.php';

// Initiate SESSION
if(!isset($_SESSION['evt_id'])){
    $_SESSION['evt_id'] = NULL;
    // For EVNG
    $target_group = "EVNG";
    $target_file = './tmp/AGS4_new.csv';
    $_SESSION['EVNG'] = tmp_load($target_file,$target_group);
    // For EVNF
    $target_group = "EVNF";
    $target_file = './tmp/AGS4_new.csv';
    $_SESSION['EVNF'] = tmp_load($target_file,$target_group);
}
// SaveMsg
$saveMsg = $_GET['saveMsg'];

///////////////////////////////////////////////////////////////////////////////
// Load NGA Finite fault information
$tmp_file = "./Data/NGA_Finite_Fault_Info.csv";
$file_content = file_get_contents($tmp_file);
$NGA = array_map('str_getcsv', preg_split('/\r*\n+|\r+/',$file_content));
$l = sizeof($NGA);
$NGA = array_slice($NGA,1,$l);

///////////////////////////////////////////////////////////////////////////////
// Load User-Input Finite fault information
$prep_stmt = "SELECT * FROM UIFF";
$result = $mysqli->query($prep_stmt);
$i = 0;
while($row = $result->fetch_assoc()){
    $userEQ[$i] = $row;
    $i++;
}
//print_r($userEQ);
//print_r(array_unique(array_column($userEQ,'name')));
//print_r(array_keys(array_unique(array_column($userEQ,'name'))));
//$user_uq_keys = array_keys(array_unique(array_column($userEQ,'name')));
//print_r($userEQ[$user_uq_keys]);
///////////////////////////////////////////////////////////////////////////////
// Search
if($_POST['evt_name_srch'] != null)
{    
    $site_id = $_SESSION['site_id'];
    $evt_name_srch = filter_input(INPUT_POST, 'evt_name_srch', FILTER_SANITIZE_STRING);
    $evt_name = $evt_name_srch;
    $prep_stmt = "SELECT * FROM EVNG WHERE user_id='$user_id' AND site_id='$site_id' AND evt_name='$evt_name' LIMIT 1";
    $result = $mysqli->query($prep_stmt);
    $row = $result->fetch_assoc();
    $evt_id = $row['evt_id'];
    $_SESSION['evt_id'] = $evt_id;
}

///////////////////////////////////////////////////////////////////////////////
// New
if(isset($_POST['btn-new']))
{   
    // For EVNG
    unset($_SESSION['evt_id']);
    unset($evt_name);
    unset($evt_name_srch);
    $target_group = "EVNG";
    $target_file = './tmp/AGS4_new.csv';
    $_SESSION['EVNG'] = tmp_load($target_file,$target_group);
    // For EVNF
    $target_group = "EVNF";
    $target_file = './tmp/AGS4_new.csv';
    $_SESSION['EVNF'] = tmp_load($target_file,$target_group);
}

///////////////////////////////////////////////////////////////////////////////
// Save Site Information
if(isset($_POST['btn-save']))
{   
    
    $site_id = $_SESSION['site_id'];
    $evt_id = $_SESSION['evt_id'];
    
    $evt_name_srch = filter_input(INPUT_POST, 'evt_name_srch', FILTER_SANITIZE_STRING);    
    
    ////////////////////////////////////////////////////
    // data from EVNG table
    $LOCAL = $_SESSION['EVNG']; 
    $group = $LOCAL[0];
    $data = filter_input(INPUT_POST, $group, FILTER_SANITIZE_STRING, FILTER_REQUIRE_ARRAY);
    $evt_name = $data[0][0];
    $lat = $data[0][1];
    $lon = $data[0][2];
    $depth = $data[0][3];
    $mag = $data[0][4];
    $date = $data[0][5];
    $note = $data[0][6];
    $req = array($evt_name,$lat,$lon,$mag,$date);
    
    ////////////////////////////////////////////////////
    // data from EVNF table for strike, dip, and rake of first fault
    $LOCAL = $_SESSION['EVNF']; 
    $group = $LOCAL[0];
    $data = filter_input(INPUT_POST, $group, FILTER_SANITIZE_STRING, FILTER_REQUIRE_ARRAY);
    $strike = $data[0][1];
    $dip = $data[0][2];
    $rake = $data[0][3];

    ////////////////////////////////////////////////////
    // Save to EQ inventory
    
    $prep_stmt = "SELECT * FROM UIFF WHERE name='$evt_name' AND user_id='$user_id'";
    $result = $mysqli->query($prep_stmt);
    $numrows = $result->num_rows;
    
    // Update USER database if the event name is not in NGA database
    if(in_array($evt_name, array_column($NGA, 2)) == FALSE){
        $project = 'USER';
        $num_seg = sizeof($data);
        foreach($data as $value){
            $snum = $value[0];
            $sstrike = $value[1];
            $sdip = $value[2];
            $srake = $value[3];
            $slength = $value[4];
            $swidth = $value[5];
            $ulc_lat = $value[6];
            $ulc_lon = $value[7];
            $ulc_dep = $value[8];
//            print_r($value);
            // Insert data if no event name is matched
            if($numrows == 0 | $numrows < $snum){    
                $mysqli->query("INSERT INTO UIFF(user_id,project,name,mag,datetime,hlat,hlon,hdepth,num_seg,snum,sstrike,sdip,srake,slength,swidth,ulc_lat,ulc_lon,ulc_dep) VALUES('$user_id','$project','$evt_name','$mag','$date','$lat','$lon','$depth','$num_seg','$snum','$sstrike','$sdip','$srake','$slength','$swidth','$ulc_lat','$ulc_lon','$ulc_dep')");     
            // Update 
            } else {
                $mysqli->query("UPDATE UIFF SET mag='$mag', datetime='$date', hlat='$lat', hlon='$lon', hdepth='$depth', num_seg='$num_seg', snum='$snum', sstrike='$sstrike', sdip='$sdip', srake='$srake', slength='$slength', swidth='$swidth', ulc_lat='$ulc_lat', ulc_lon='$ulc_lon', ulc_dep='$ulc_dep' WHERE name='$evt_name' AND snum='$snum'");
            }
        }
        // Delete SQL rows
        if($numrows > $num_seg){
            $mysqli->query("DELETE FROM UIFF WHERE name='$evt_name' AND snum > '$num_seg'");        
        }
    }

    
    ////////////////////////////////////////////////////
    // Save to tmp SQL
    // New event    
    if($evt_name_srch == null)
    {        
        // General Information
        $prep_stmt = "SELECT * FROM EVNG WHERE user_id='$user_id' AND site_id='$site_id' AND evt_name='$evt_name'";
        $result = $mysqli->query($prep_stmt);
        if($result->num_rows > 0){            
            $saveMsg = 'Event name <i><b>'.$evt_name.'</b></i> is already used. Please use different name.';
        } else if(in_array(null,$req)){
            $saveMsg = 'Please fill in required field.';
        } else {
            if($mysqli->query("INSERT INTO EVNG(user_id,site_id,evt_name,lat,lon,depth,mag,date,note,strike,dip,rake) VALUES('$user_id','$site_id','$evt_name','$lat','$lon','$depth','$mag','$date','$note','$strike','$dip','$rake')")) {
                $prep_stmt = "SELECT evt_id FROM EVNG WHERE evt_name='$evt_name'";
                $result = $mysqli->query($prep_stmt);
                $row = $result->fetch_assoc();
                $evt_id = $row['evt_id'];
                $saveMsg = 'Event name <b><i>'.$evt_name.'</i></b> has been created.';
            }
        }
    // Update
    } else {     
        $evt_id = $_SESSION['evt_id'];
        // General Information
        $prep_stmt = "SELECT * FROM EVNG WHERE evt_id!='$evt_id' AND user_id='$user_id' AND site_id='$site_id' AND evt_name='$evt_name'";
        $result = $mysqli->query($prep_stmt);
        if($result->num_rows > 0){            
            $saveMsg = 'Event name <i><b>'.$evt_name.'</b></i> is already used. Please use different name.';
        } else if(in_array(null,$req)){
            $saveMsg = 'Please fill in required field.';
        } else {
            if($mysqli->query("UPDATE EVNG SET evt_name='$evt_name', lat='$lat', lon='$lon', depth='$depth', mag='$mag', date='$date', note='$note', strike='$strike', dip='$dip', rake='$rake' WHERE evt_id='$evt_id'")) {
                $saveMsg = 'Event name <b><i>'.$evt_name.'</i></b> has been updated.';
            }
        }
    }
    $_SESSION['evt_id'] = $evt_id;    
    
    ////////////////////////////////////////////////////////////////////////////
    // Save groups    
    // Target directory    
    $evt_name_space = str_replace(' ','%20',$evt_name);
    // Target directory
    $site_id = $_SESSION['site_id'];
    $prep_stmt = "SELECT * FROM PROJ WHERE site_id='$site_id' LIMIT 1";
    $result = $mysqli->query($prep_stmt);
    $row = $result->fetch_assoc();    
    $site_name = $row['site_name'];
    $status = $row['status'];
    if($row['status'] == 'DRAFT'){
        $target_dir = './uploads/tmp/'.$site_name;
    } else if($row['status'] == 'COMPLETE'){
        $target_dir = './uploads/sites/'.$site_name;
    }
    
    ////////////////////////////////////////////////////
    // EVNG
    $SelectedGroup = 'EVNG';
    $LOCAL = $_SESSION[$SelectedGroup];
    $group = $LOCAL[0];
    $heading = $LOCAL[1];
    $unit = $LOCAL[2];
    $type = $LOCAL[3];    
    // data from table    
    $data = filter_input(INPUT_POST, $group, FILTER_SANITIZE_STRING, FILTER_REQUIRE_ARRAY);
    
    // Check data
    if(sizeof($data) > 0){
        
        // Check empty row and remove
        $data_first = array_column($data,0);
        $idx = array_keys($data_first,'');
        for($i=0;$i<sizeof($idx);$i++) { unset($data[$idx[$i]]); }
        
        $target_file = $target_dir.'/'.$SelectedGroup.'.csv';
        if(is_file($target_file)){
            $file_content = file_get_contents($target_file);
            $current = array_map('str_getcsv', preg_split('/\r*\n+|\r+/',$file_content));            
        }
        
        $output = fopen($target_file,'w') or die("Can't open $target_file");
        // Fill in heading
        $info_comb = array(        
            array_merge(array('GROUP'),array($group)),
            array_merge(array('HEADING','EVNG_ID'),$heading),
            array_merge(array('UNIT',''),$unit),
            array_merge(array('TYPE','ID'),$type)
        );
        // Write heading
        foreach($info_comb as $value) {
            fputcsv($output, $value);
        }
        if($current != null){
            $update = 0;
            for($i=4;$i<sizeof($current);$i++){                
                if($current[$i][1] == $evt_id){ // Update
                    $update = 1;
                    foreach($data as $value) {
                        $value = array_merge(array('DATA',$evt_id),$value);
                        fputcsv($output, $value);
                    }                    
                } else { // New
                    fputcsv($output, $current[$i]);
                }
            }
            if($update == 0){
                foreach($data as $value) {
                    $value = array_merge(array('DATA',$evt_id),$value);
                    fputcsv($output, $value);
                }
            }
        } else {
            foreach($data as $value) {
                $value = array_merge(array('DATA',$evt_id),$value);
                fputcsv($output, $value);
            }
        }
        $stat = fstat($output);
        ftruncate($output, $stat['size']-1);
        fclose($output) or die("Can't close $target_file");    
    }
    
    ////////////////////////////////////////////////////
    // EVNF
    $SelectedGroup = 'EVNF';
    $LOCAL = $_SESSION[$SelectedGroup];
    $group = $LOCAL[0];
    $heading = $LOCAL[1];
    $unit = $LOCAL[2];
    $type = $LOCAL[3];
    // data from table    
    $data = filter_input(INPUT_POST, $group, FILTER_SANITIZE_STRING, FILTER_REQUIRE_ARRAY);
    
    // Check empty row and remove
    $data_first = array_column($data,0);
    $idx = array_keys($data_first,'');
    for($i=0;$i<sizeof($idx);$i++) { unset($data[$idx[$i]]); }
    
    $target_dir = $target_dir.'/'.$evt_name;
    if(is_dir($target_dir) === false){ mkdir($target_dir);}
    // Check data
    if($data[0][0]!=''){
        $target_file = $target_dir.'/'.$group.'.csv';
        $output = fopen($target_file,'w') or die("Can't open $target_file");
        // Combine info with heading
        $info_comb = array(        
            array_merge(array('GROUP'),array($group)),
            array_merge(array('HEADING','EVNG_ID'),$heading),
            array_merge(array('UNIT',''),$unit),
            array_merge(array('TYPE','ID'),$type)
        );    
        // Write csv
        foreach($info_comb as $value) {
            fputcsv($output, $value);
        }
        foreach($data as $value) {
            $value = array_merge(array('DATA',$evt_id),$value);
            fputcsv($output, $value);
        }
        $stat = fstat($output);
        ftruncate($output, $stat['size']-1);
        fclose($output) or die("Can't close $target_file");    
    }
}

///////////////////////////////////////////////////////////////////////////////
// Delete
if($_POST['btn-delete'] != null)
{   
    $evt_id = $_SESSION['evt_id'];
    unset($_SESSION['evt_id']);
    unset($_SESSION['EVNG']);
    unset($_SESSION['EVNF']);
    
    // Delete files
    $prep_stmt = "SELECT * FROM EVNG WHERE evt_id='$evt_id'";
    $result = $mysqli->query($prep_stmt);
    $row = $result->fetch_assoc();
    $evt_name = $row['evt_name'];    
    $evt_name_srch = filter_input(INPUT_POST, 'evt_name_srch', FILTER_SANITIZE_STRING);
    // tmp folder file
    $site_id = $_SESSION['site_id'];
    $prep_stmt = "SELECT * FROM PROJ WHERE site_id='$site_id' LIMIT 1";
    $result = $mysqli->query($prep_stmt);
    $row = $result->fetch_assoc();    
    $site_name = $row['site_name'];
    $status = $row['status'];
    if($row['status'] == 'DRAFT'){
        $target_dir = './uploads/tmp/'.$site_name;
    } else if($row['status'] == 'COMPLETE'){
        $target_dir = './uploads/sites/'.$site_name;
    }     
    array_map('unlink', glob("$target_dir/$evt_name/*.*"));
    rmdir($target_dir.'/'.$evt_name);
    
    // Delete row from EVNG
    $SelectedGroup = 'EVNG';
    $target_file = $target_dir.'/'.$SelectedGroup.'.csv';
    $file_content = file_get_contents($target_file);
    $current = array_map('str_getcsv', preg_split('/\r*\n+|\r+/',$file_content));
    $output = fopen($target_file,'w') or die("Can't open $target_file");
    // Fill in heading
    for($i=0;$i<4;$i++){                
        fputcsv($output, $current[$i]);
    }
    // write without selected evt
    for($i=4;$i<sizeof($current);$i++){                
        if($current[$i][1] != $evt_id){ 
            fputcsv($output, $current[$i]);
        }
    }
    $stat = fstat($output);
    ftruncate($output, $stat['size']-1);
    fclose($output) or die("Can't close $target_file");
    // Delete file if no data
    if(sizeof($current) == 5){
        array_map('unlink', glob("$target_file"));
    }    

    // Delete rows in EVNG SQL table
    if($mysqli->query("DELETE FROM EVNG WHERE evt_id='$evt_id'")) 
    {
        // Delete rows in UIFF SQL table
        $mysqli->query("DELETE FROM UIFF WHERE user_id='$user_id' AND name='$evt_name' AND project='USER'");
            
        $saveMsg = '<i><b>'.$evt_name.'</b></i> is deleted.';
    }
    
    //////////////////////////////////////////
    // Reset EVNG and EVNF
    unset($_SESSION['evt_id']);
    unset($evt_name);
    unset($evt_name_srch);
    // For EVNG
    $target_group = "EVNG";
    $target_file = './tmp/AGS4_new.csv';
    $_SESSION['EVNG'] = tmp_load($target_file,$target_group);
    // For EVNF
    $target_group = "EVNF";
    $target_file = './tmp/AGS4_new.csv';
    $_SESSION['EVNF'] = tmp_load($target_file,$target_group);    
    
    ///////////////////////////////////////////////////////////////////////////////
    // Load User-Input Finite fault information
    $prep_stmt = "SELECT * FROM UIFF";
    $result = $mysqli->query($prep_stmt);
    $i = 0;
    while($row = $result->fetch_assoc()){
        $userEQ[$i] = $row;
        $i++;
    }
}

///////////////////////////////////////////////////////////////////////////////
// Recall site Information
if(isset($_SESSION['site_id']))
{      
    $site_id = $_SESSION['site_id'];
    $prep_stmt = "SELECT * FROM PROJ WHERE site_id='$site_id' LIMIT 1";
    $result = $mysqli->query($prep_stmt);
    $row = $result->fetch_assoc();    
    $site_name = $row['site_name'];
}

///////////////////////////////////////////////////////////////////////////////
// Recall event Information
if(isset($_SESSION['evt_id']))
{      
    $evt_id = $_SESSION['evt_id'];
    $prep_stmt = "SELECT * FROM EVNG WHERE evt_id='$evt_id' LIMIT 1";
    $result = $mysqli->query($prep_stmt);
    $row = $result->fetch_assoc();
    $evt_name = $row['evt_name'];
    // Target directory
    $site_id = $_SESSION['site_id'];
    $prep_stmt = "SELECT * FROM PROJ WHERE site_id='$site_id' LIMIT 1";
    $result = $mysqli->query($prep_stmt);
    $row = $result->fetch_assoc();    
    $site_name = $row['site_name'];
    $status = $row['status'];
    if($row['status'] == 'DRAFT'){
        $target_dir = './uploads/tmp/'.$site_name;
    } else if($row['status'] == 'COMPLETE'){
        $target_dir = './uploads/sites/'.$site_name;
    }     
    
    // Load EVNG
    $target_group = "EVNG";
    $target_file = $target_dir.'/'.$target_group.'.csv';
    $LOCAL = tmp_load($target_file,$target_group);
    if(sizeof($LOCAL[6])>1){
        $i = 0;
        foreach($LOCAL[6] as $value){
            if($evt_name == $value[0]){
                $idx = $i;
            }
            $i++;
        }
        $LOCAL[6] = array($LOCAL[6][$idx]);
    }
    $_SESSION['EVNG'] = $LOCAL;    
    
    // Load EVNF
    $target_group = "EVNF";
    $target_file = $target_dir.'/'.$evt_name.'/'.$target_group.'.csv';
    $_SESSION['EVNF'] = tmp_load($target_file,$target_group);    
}

///////////////////////////////////////////////////////////////////////////////
// Select
if($_POST['search'] != null)
{    
    unset($_SESSION['EVNG'][6]);
    unset($_SESSION['EVNF'][6]);    
    unset($evt_name);
    unset($evt_name_srch);
    
    //////////////////////////////////////////
    // Reset EVNG and EVNF
    // For EVNG
    $target_group = "EVNG";
    $target_file = './tmp/AGS4_new.csv';
    $_SESSION['EVNG'] = tmp_load($target_file,$target_group);
    // For EVNF
    $target_group = "EVNF";
    $target_file = './tmp/AGS4_new.csv';
    $_SESSION['EVNF'] = tmp_load($target_file,$target_group);    
    
    //////////////////////////////////////////////
    // Load EVNG data 
    $search = filter_input(INPUT_POST, 'select', FILTER_SANITIZE_STRING);    
    // from NGA
    if(in_array($search, array_column($NGA,2))){        
        $idx = array_search($search, array_column($NGA, 2));
        $event_name = $NGA[$idx][2];        
        $mag = $NGA[$idx][3];
        $year = $NGA[$idx][5];
        $mody = sprintf('%04d', $NGA[$idx][6]);
        $hrmn = sprintf('%04d', $NGA[$idx][7]);
        $date = $year.'-'.$mody.'-'.$hrmn;
        $lat = $NGA[$idx][9];
        $lon = $NGA[$idx][10];
        $depth = $NGA[$idx][11];  
        
        // Load EVNF data     
        $num_fault = $NGA[$idx][15];
        for($i=0;$i<$num_fault;$i++){
            $seg_num = $NGA[$idx][1+$i*9+15];
            $strike = $NGA[$idx][2+$i*9+15];
            $dip = $NGA[$idx][3+$i*9+15];
            $length = $NGA[$idx][4+$i*9+15];
            $width = $NGA[$idx][5+$i*9+15];
            $rake = $NGA[$idx][6+$i*9+15];
            $lat_ulc = $NGA[$idx][7+$i*9+15];
            $lon_ulc = $NGA[$idx][8+$i*9+15];
            $depth_ulc = $NGA[$idx][9+$i*9+15];

            $_SESSION['EVNF'][6][$i][0] = $seg_num;
            $_SESSION['EVNF'][6][$i][1] = $strike;
            $_SESSION['EVNF'][6][$i][2] = $dip;
            $_SESSION['EVNF'][6][$i][3] = $rake;
            $_SESSION['EVNF'][6][$i][4] = $length;
            $_SESSION['EVNF'][6][$i][5] = $width;
            $_SESSION['EVNF'][6][$i][6] = $lat_ulc;
            $_SESSION['EVNF'][6][$i][7] = $lon_ulc;
            $_SESSION['EVNF'][6][$i][8] = $depth_ulc;
        }
    // from USER input
    } else if(in_array($search, array_column($userEQ,'name'))){
        $idx = array_search($search, array_column($userEQ, 'name'));
        $event_name = $userEQ[$idx]['name'];        
        $mag = $userEQ[$idx]['mag'];
        $date = $userEQ[$idx]['datetime'];
        $lat = $userEQ[$idx]['hlat'];
        $lon = $userEQ[$idx]['hlon'];
        $depth = $userEQ[$idx]['hdepth'];
        $note = $userEQ[$idx]['note'];
        // Load EVNF data     
        $num_fault = $userEQ[$idx]['num_seg'];
        for($i=0;$i<$num_fault;$i++){
            $seg_num = $userEQ[$idx+$i]['snum'];
            $strike = $userEQ[$idx+$i]['sstrike'];
            $dip = $userEQ[$idx+$i]['sdip'];
            $length = $userEQ[$idx+$i]['slength'];
            $width = $userEQ[$idx+$i]['swidth'];
            $rake = $userEQ[$idx+$i]['srake'];
            $lat_ulc = $userEQ[$idx+$i]['ulc_lat'];
            $lon_ulc = $userEQ[$idx+$i]['ulc_lon'];
            $depth_ulc = $userEQ[$idx+$i]['ulc_dep'];

            $_SESSION['EVNF'][6][$i][0] = $seg_num;
            $_SESSION['EVNF'][6][$i][1] = $strike;
            $_SESSION['EVNF'][6][$i][2] = $dip;
            $_SESSION['EVNF'][6][$i][3] = $rake;
            $_SESSION['EVNF'][6][$i][4] = $length;
            $_SESSION['EVNF'][6][$i][5] = $width;
            $_SESSION['EVNF'][6][$i][6] = $lat_ulc;
            $_SESSION['EVNF'][6][$i][7] = $lon_ulc;
            $_SESSION['EVNF'][6][$i][8] = $depth_ulc;
        }
    }
    
    $_SESSION['EVNG'][6][0][0] = $event_name;
    $_SESSION['EVNG'][6][0][1] = $lat;
    $_SESSION['EVNG'][6][0][2] = $lon;
    $_SESSION['EVNG'][6][0][3] = $depth;
    $_SESSION['EVNG'][6][0][4] = $mag;
    $_SESSION['EVNG'][6][0][5] = $date;
    $_SESSION['EVNG'][6][0][6] = $note;    
}

///////////////////////////////////////////////////////////////////////////////
// Previous 
if(isset($_POST['btn-previous']))
{
    header("Location: ./upload_PROJ.php");
}

///////////////////////////////////////////////////////////////////////////////
// Next 
if(isset($_POST['btn-next']))
{
    if($evt_id == null){
        $saveMsg = "Please <b>SAVE</b> or <b>SEARCH</b> first.";
    } else {
        header("Location: ./upload_FLDP.php");
    }
}
?>

<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta content="text/html; charset=utf-8" http-equiv="Content-Type" />
    <title>NGL</title>
    <!--Style-->
    <link href="css/NGL.css" rel="stylesheet" type="text/css" />
    <!--Map Content Style-->    
    <link rel="stylesheet" href="css/leaflet.css" />
	<link rel="stylesheet" href="css/MarkerCluster.css" />
    <link rel="stylesheet" href="css/MarkerCluster.Default.css" />	
    <link rel="stylesheet" href="css/leaflet.awesome-markers.css">    
    <link rel="stylesheet" href="css/ionicons-2.0.1/css/ionicons.css"><!--ionicons
http://ionicons.com-->
    <link rel="stylesheet" href="css/leaflet-panel-layers.src.css" />
    <link rel="stylesheet" href="css/colorbox.css">
    <!--Javascript script-->   
    <script src="//code.jquery.com/jquery-1.12.0.min.js"></script>
    <script src="src/jquery.colorbox-min.js"></script>
	<script src="src/leaflet.js"></script>
    <script src="src/leaflet.markercluster-src.js"></script>	
    <script src="src/leaflet-omnivore.js"></script>
    <script src="src/leaflet.awesome-markers.js"></script>     
    <script src="//cdn.jsdelivr.net/leaflet.esri/1.0.0/esri-leaflet.js"></script><!-- Load Esri Leaflet from CDN -->    
    <script src="src/leaflet-panel-layers.src.js"></script>   
    <script src="src/awe_icon.js"></script>
    <script src="src/form_php.js"></script>
    <script src="src/input_data.js"></script>
    <script>
        // For pop-up window
        var mouseX;
        var mouseY;
        $(document).mousedown(function(e){
            mouseX = e.clientX;
            mouseY = e.clientY;
            $('.comments').colorbox({
                opacity: 0,
                inline:true, 
                width:"300px",
                height:"200px",
                top:mouseY-200,
                left:mouseX,
                fixed:false,
                transition:"none"
            });
        }); 
    </script>
    <script language="JavaScript">
        function disableEnterKey(e)
        {
             var key;      
             if(window.event)
                  key = window.event.keyCode; //IE
             else
                  key = e.which; //firefox      

             return (key != 13);
        }
    </script>
</head>
    
<body OnKeyPress="return disableEnterKey(event)">
<!----------------------- Start of header -------------------------->
<?php include_once 'includes/head.html';?>

<!----------------------- Start of Body ---------------------------->
<div id="container" class="home">        
<?php 
if(isset($_SESSION['user'])){
?>
    
<!------------------------------- Menus ---------------------------->
<div id='upload' class='menus'>
<table width="100%" align="center" border="0">
    <tr class="mainmenu">        
        <td width="33%">Site Information</td>        
        <td width="33%" style="background:#3284BF">Event Information</td>
        <td width="33%">Ground Performance</td>        
    </tr>
</table>
</div>
    
<!----------------------- Map  ------------------------->        
<div id='upload' class='files'>
<table width="100%" align="center" border="0">
<!--    Map-->
    <tr><td colspan=2><div id="map_upload"></div></td></tr>
<!--    save Message-->
    <tr>
        <td align="left" width="60px"><h4>Server Message</h4></td>
        <td align="left" style="color:red"><span id="ServerMsg"><?=$saveMsg;?></span></td>
    </tr>
</table>
</div>
    
<!----------------------- Search existed data ---------------------->        
<div id='upload' class='body'>
<form method="post" id="searchForm">    
<table width="100%" align="center" border="0">
    <!--Site Name-->
    <tr>
        <td width="100px">Site Name</td>
        <td colspan=4>
            <input class="readonly" type="text" name="site_name" readonly value="<?=$site_name;?>" placeholder="Associated Site Name">
        </td>
    </tr>
    <tr>
        <td width="100px">Create a new event</td>
        <td width="400px">
            <button type="submit" name="btn-new">New</button>
        </td>
        <td width="100px">Search event name</td>
        <td width="300px">
            <select id="new_evt" name="evt_name_srch" onchange="evtlist()" style="width:100%">
            <option value="<?=$evt_name_srch?>">
                <?if($evt_name != null) echo $evt_name;?>
            </option>
            <?php
            $user_id = $_SESSION['user_id'];
            $prep_stmt = "SELECT * FROM EVNG WHERE user_id='$user_id' AND site_id='$site_id'";
            $result = $mysqli->query($prep_stmt);
            while($row = $result->fetch_assoc()){
                if($row['evt_name'] != $evt_name){
                ?>                
                    <option value="<?=$row['evt_name'];?>"><?=$row['evt_name'];?></option>
                <?}
            }?>
            </select>
        </td>
    </tr>    
</table>
<!----------------------- Search event from NGA data ---------------------->         
<table width="100%" align="center" border="0">
    <tr>
        <td><b>Select an earthquake from database</b></td>
    </tr>
    <tr>
        <td width="300px" valign="top" height="20px">
            <input type="text" name="search" id="search" placeholder="Type event name to search" value="<?php echo $search;?>" onkeyup='SearchEQ()' style="width:100%;">
        </td>
        <td rowspan=2>
            <select size=10 id="select" name="select" style="width:100%;" onchange='SelectEQ()'>
            <?php
            // NGA database
            foreach($NGA as $value){                
                $year = $value[5];
                $mody = sprintf('%04d', $value[6]);
                $hrmn = sprintf('%04d', $value[7]);
                $date = $year.'-'.$mody.'-'.$hrmn;
                echo "<option value='".$value[2]."'>";
                echo $value[0]." / ".$value[2]." / M ".$value[3]." / Year-MoDy-HrMn ".$date."</option>";
            }
            // User database
            $user_uq_keys = array_keys(array_unique(array_column($userEQ,'name')));
            foreach($user_uq_keys as $key){
                $value = $userEQ[$key];
                $date = $value['datetime'];
                echo "<option value='".$value['name']."'>";
                echo "USER / ".$value['name']." / M ".$value['mag']." / Year-MoDy-HrMn ".$date."</option>";
            }
            ?>
        </select>
        </td>
    </tr>
    <!--Event Information (pending)-->
    <tr><td valign="top"><p id="evt_info"></p></td></tr>
</table>    
</form>

<form method="post" id="myform" name="myform" enctype="multipart/form-data"> 
<div id="addGroup"></div>
<!--Input section load per test-->
<?php
                
if(isset($_SESSION['EVNG']))
{
    ?>    
<script>       
    // EVNG
    var group = <?=json_encode($_SESSION['EVNG'][0])?>;    
    var title = <?=json_encode($_SESSION['EVNG'][4])?>;
    var heading = <?=json_encode($_SESSION['EVNG'][5])?>;
    var comments = <?=json_encode($_SESSION['EVNG'][7])?>;
    var req = <?=json_encode($_SESSION['EVNG'][8])?>;
    var unit = <?=json_encode($_SESSION['EVNG'][2])?>;
    var json_data = <?=json_encode($_SESSION['EVNG'][6])?>;
    DataLoad(this.form,group,title,heading,comments,unit,json_data,req);
    EntryInput(this.form,group,heading,json_data);
    // EVNF
    var group = <?=json_encode($_SESSION['EVNF'][0])?>;    
    var title = <?=json_encode($_SESSION['EVNF'][4])?>;
    var heading = <?=json_encode($_SESSION['EVNF'][5])?>;
    var comments = <?=json_encode($_SESSION['EVNF'][7])?>;
    var req = <?=json_encode($_SESSION['EVNF'][8])?>;
    var unit = <?=json_encode($_SESSION['EVNF'][2])?>;
    var json_data = <?=json_encode($_SESSION['EVNF'][6])?>;
    DataLoad(this.form,group,title,heading,comments,unit,json_data,req);
    EntryInput(this.form,group,heading,json_data);
</script>                                                     
<!-- Save buttons    -->
<table width="100%" align="center" border="0">
    <tr>
        <td width="25%" align="left"><button type="submit" name="btn-previous">Back</button></td>
        <td colspan=2 align="center">
            <!-- For searched evt name -->
            <input type="hidden" id="evt_list" name="evt_name_srch" value="<?=$evt_name_srch?>">
            <button type="submit" name="btn-save">Save</button> 
            <input class="button-large" type="button" onClick="del_confirm('<?=$evt_name?>')" value="Delete">
            <input type="hidden" id="del_id" name="btn-delete">
        </td>
        <td width="25%" align="right"><button type="submit" name="btn-next">Next</button></td>
    </tr>
</table>
<?}?>    
</form>    
</div>
<!--Margin at bottom-->
<table>
    <tr>
        <td><p style="margin-bottom:100px"></p>
        </td>
    </tr>
</table>    
<!-- Map -->
<?php include_once 'includes/inner_map_EVNG.php';
} else {
?>
<!----------------------- Sign in ask ------------------------->
<center><h2>Please sign in first.</h2></center>
<?php }?>
<!---------------------End of Body --------------------------------->    
</div>

</body>
</html>