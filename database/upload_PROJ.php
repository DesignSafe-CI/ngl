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
if(!isset($_SESSION['site_id'])){
    $_SESSION['site_id'] = NULL;
    // For PROJ
    $target_group = "PROJ";
    $target_file = './tmp/AGS4_new.csv';    
    $_SESSION['PROJ'] = tmp_load($target_file,$target_group);
    // For MAPF
    $target_group = "MAPF";
    $target_file = './tmp/AGS4_new.csv';
    $_SESSION['MAPF'] = tmp_load($target_file,$target_group);
    // For LOCA
    $target_group = "LOCA";
    $target_file = './tmp/AGS4_new.csv';
    $_SESSION['LOCA'] = tmp_load($target_file,$target_group);
}
// SaveMsg
$saveMsg = $_GET['saveMsg'];

///////////////////////////////////////////////////////////////////////////////
// Search
if(isset($_POST['btn-search']))
{    
    $site_name_srch = filter_input(INPUT_POST, 'site_name_srch', FILTER_SANITIZE_STRING);
    $site_name = explode(': ',$site_name_srch)[1];
    $prep_stmt = "SELECT * FROM PROJ WHERE user_id='$user_id' AND site_name='$site_name' LIMIT 1";
    $result = $mysqli->query($prep_stmt);
    $row = $result->fetch_assoc();
    $site_id = $row['site_id'];
    $_SESSION['site_id'] = $site_id;
}
///////////////////////////////////////////////////////////////////////////////
// New
if(isset($_POST['btn-new']))
{   
    $target_file = './tmp/AGS4_new.csv';
    
    // For PROJ
    unset($_SESSION['site_id']);
    unset($site_id);
    unset($site_name);
    unset($site_name_srch);
    $target_group = "PROJ";    
    $_SESSION['PROJ'] = tmp_load($target_file,$target_group);
    
    // For MAPF
    $target_group = "MAPF";
    $_SESSION['MAPF'] = tmp_load($target_file,$target_group);
    
    // For LOCA
    unset($_SESSION['loca_id']);
    unset($loca_id);
    $target_group = "LOCA";
    $_SESSION['LOCA'] = tmp_load($target_file,$target_group);
}

///////////////////////////////////////////////////////////////////////////////
// Save Site Information
if(isset($_POST['btn-save']))
{   
    
    // Site Information
    $site_name_srch = filter_input(INPUT_POST, 'site_name_srch', FILTER_SANITIZE_STRING);
    $LOCAL = $_SESSION['PROJ']; 
    $FILES = $_SESSION['MAPF'];
    $group = $LOCAL[0];
    $heading = $LOCAL[1];
    $unit = $LOCAL[2];
    $type = $LOCAL[3];

    ////////////////////////////////////////////////////
    // data from table    
    $data = filter_input(INPUT_POST, $group, FILTER_SANITIZE_STRING, FILTER_REQUIRE_ARRAY);
    
    // Check empty row and remove
    $data_first = array_column($data,0);
    $idx = array_keys($data_first,'');
    for($i=0;$i<sizeof($idx);$i++) { unset($data[$idx[$i]]); }
    
    $site_name = $data[0][0];
    $lat = $data[0][1];
    $lon = $data[0][2];
    $elev = $data[0][3];
    $geol = $data[0][4];
    $note = $data[0][5];
    $req = array($site_name,$lat,$lon);
    $status = "DRAFT";
    ////////////////////////////////////////////////////
    // Save to tmp SQL
    // New site    
    if($site_name_srch == null)
    {
        // General Information
        $prep_stmt = "SELECT * FROM PROJ WHERE user_id='$user_id' AND site_name='$site_name'";
        $result = $mysqli->query($prep_stmt);
        if($result->num_rows > 0){            
            $saveMsg = 'Site name <i><b>'.$site_name.'</b></i> is already used. Please use different name.';
            $site_name = null;
        } else if(in_array(null,$req)){
            $saveMsg = 'Please fill in required field.';
            $site_name = null;
        } else {
            if($mysqli->query("INSERT INTO PROJ(user_id,site_name,lat,lon,elev,geol,note,status) VALUES('$user_id','$site_name','$lat','$lon','$elev','$geol','$note','$status')")) {
                $prep_stmt = "SELECT site_id FROM PROJ WHERE site_name='$site_name'";
                $result = $mysqli->query($prep_stmt);
                $row = $result->fetch_assoc();
                $site_id = $row['site_id'];
                $saveMsg0 = 'Site name <b><i>'.$site_name.'</i></b> is created.';
            }
        }
    // Update
    } else {     
        $site_id = $_SESSION['site_id'];
        // General Information
        $prep_stmt = "SELECT * FROM PROJ WHERE site_id!='$site_id' AND user_id='$user_id' AND site_name='$site_name'";
        $result = $mysqli->query($prep_stmt);
        if($result->num_rows > 0){            
            $saveMsg = 'Site name <i><b>'.$site_name.'</b></i> is already used. Please use different name.';
            $site_name = null;
        } else if(in_array(null,$req)){
            $saveMsg = 'Please fill in required field.';
            $site_name = null;
        } else {
            if($mysqli->query("UPDATE PROJ SET site_name='$site_name', lat='$lat', lon='$lon', elev='$elev', geol='$geol', note='$note' WHERE site_id='$site_id'")) {
                $saveMsg0 = 'Site name <b><i>'.$site_name.'</i></b> has been updated.';
            }
        }
    }
    $_SESSION['site_id'] = $site_id;

    ////////////////////////////////////////////////////
    // Save to PROJ file        
    // Combine info with heading
    if($site_name != null){
        $info_comb = array(        
            array_merge(array('GROUP'),array($group)),
            array_merge(array('HEADING','PROJ_ID'),$heading),
            array_merge(array('UNIT',''),$unit),
            array_merge(array('TYPE','ID'),$type)
        );

        // Save file
        if($site_name_srch == null){
            $status = 'DRAFT';
            $target_dir = './uploads/tmp/'.$site_name;
            if(is_dir($target_dir) === false){ mkdir($target_dir);}
            $target_file = $target_dir.'/'.$group.'.csv';
        } else if (str_split($site_name_srch,5)[0] == "DRAFT"){
            $status = 'DRAFT';
            $target_dir = './uploads/tmp/'.$site_name;
            // change folder name if site name is updated
            if($site_name != explode(": ",$site_name_srch)[1]){
                $target_dir_old = './uploads/tmp/'.explode(": ",$site_name_srch)[1];
                rename($target_dir_old,$target_dir);                
            }
            $target_file = $target_dir.'/'.$group.'.csv';
        } else {
            $status = 'COMPLETE';
            $target_dir = './uploads/sites/'.$site_name;
            // change folder name if site name is updated
            if($site_name != explode(": ",$site_name_srch)[1]){
                print_r(explode(": ",$site_name_srch)[1]);
                $target_dir_old = './uploads/sites/'.explode(": ",$site_name_srch)[1];
                rename($target_dir_old,$target_dir);                
            }
            $target_file = $target_dir.'/'.$group.'.csv';
        }
        $output = fopen($target_file,'w') or die("Can't open $target_file");

        // Write csv
        foreach($info_comb as $value) {
            fputcsv($output, $value);
        }
        foreach($data as $value) {
            $value = array_merge(array('DATA',$site_id),$value);
            fputcsv($output, $value);
        }
        $stat = fstat($output);
        ftruncate($output, $stat['size']-1);
        fclose($output) or die("Can't close $target_file");

        ////////////////////////////////////////////////////
        // Save for MAPF    
        $group = $FILES[0];
        if($status == 'DRAFT'){
            $target_dir = './uploads/tmp/'.$site_name.'/FILES';
        } else if ($status == 'COMPLETE'){
            $target_dir = './uploads/sites/'.$site_name.'/FILES';
        }
        $name = filter_input(INPUT_POST, $group.'_add_name', FILTER_SANITIZE_STRING, FILTER_REQUIRE_ARRAY);
        $desc = filter_input(INPUT_POST, $group.'_add_desc', FILTER_SANITIZE_STRING, FILTER_REQUIRE_ARRAY);
        $filename_ex = filter_input(INPUT_POST, $group.'_add_file_ex', FILTER_SANITIZE_STRING, FILTER_REQUIRE_ARRAY);
        $filename = $_FILES[$group.'_add_file']["name"];
        $tmp_filename = $_FILES[$group.'_add_file']["tmp_name"];

        // Delete files
        for($i=0;$i<sizeof($FILES[6]);$i++){      
            if($i < sizeof($name)){            
                if($filename[$i] != null){                
                    // Delete old one
                    $target_file = $target_dir.'/'.$filename_ex[$i];
                    unlink($target_file);     
                }
            } else {
                $target_file = $target_dir.'/'.$FILES[6][$i][2];
                unlink($target_file);
            }
         }

        // Save files
        if(is_dir($target_dir) === false){ mkdir($target_dir);}
        for($i=0;$i<sizeof($name);$i++){        
            $target_file = $target_dir.'/'.$filename[$i];
            move_uploaded_file($tmp_filename[$i], $target_file);        
        }

        // Combine info with heading    
        $group = $FILES[0];
        $heading = $FILES[1];
        $unit = $FILES[2];
        $type = $FILES[3];
        $info_comb = array(        
            array_merge(array('GROUP'),array($group)),
            array_merge(array('HEADING','PROJ_ID'),$heading),
            array_merge(array('UNIT',''),$unit),
            array_merge(array('TYPE','ID'),$type)
        );
        // Save AGS file
        if(str_split($site_name_srch,5)[0] == "DRAFT" | $site_name_srch == null){        
            $target_dir = './uploads/tmp/'.$site_name;        
            if(is_dir($target_dir) === false){ mkdir($target_dir);}
            $target_file = $target_dir.'/'.$group.'.csv';
        } else {
            $target_dir = './uploads/sites/'.$site_name;
            if(is_dir($target_dir) === false){ mkdir($target_dir);}
            $target_file = $target_dir.'/'.$group.'.csv';
        }

        $output = fopen($target_file,'w') or die("Can't open $target_file");
        // Write csv
        foreach($info_comb as $value) {
            fputcsv($output, $value);
        }    
        for($i=0;$i<sizeof($name);$i++){
            if($filename[$i] == null){
                $value = array('DATA',$site_id,$name[$i],$desc[$i],$filename_ex[$i]);
            } else {                   
                $value = array('DATA',$site_id,$name[$i],$desc[$i],$filename[$i]);            
            }
            fputcsv($output, $value);
        }
        $stat = fstat($output);
        ftruncate($output, $stat['size']-1);
        fclose($output) or die("Can't close $target_file");
    }
    if($site_name != null){     
        // Location Details
        $site_id = $_SESSION['site_id'];
        $LOCAL = $_SESSION['LOCA']; 
        $group = $LOCAL[0];
        $heading = $LOCAL[1];
        $unit = $LOCAL[2];
        $type = $LOCAL[3];
        ////////////////////////////////////////////////////
        // data from table    
        $data = filter_input(INPUT_POST, $group, FILTER_SANITIZE_STRING, FILTER_REQUIRE_ARRAY);
        
        // Check empty row and remove
        $data_first = array_column($data,0);
        $idx = array_keys($data_first,'');
        for($i=0;$i<sizeof($idx);$i++) { unset($data[$idx[$i]]); }        
        
        // Check uniqueness of location ID
        $uniqueID = array_unique(array_column($data,0));
        if(sizeof($data) != sizeof($uniqueID)){
            $saveMsg = 'Please use unique ID for Location ID.';
        } else {   

            ////////////////////////////////////////////////////
            // target directory
            $prep_stmt = "SELECT * FROM PROJ WHERE site_id='$site_id' LIMIT 1";
            $result = $mysqli->query($prep_stmt);
            $row = $result->fetch_assoc();
            $site_name = $row['site_name'];
            $status = $row['status'];
            if($status == 'DRAFT'){
                $target_dir = './uploads/tmp/'.$site_name;
            } else if($status == 'COMPLETE'){
                $target_dir = './uploads/sites/'.$site_name;
            }

            ////////////////////////////////////////////////////
            // Save to SQL
            // Delete rows for LOCA
            $prep_stmt = "SELECT loca_id FROM LOCA WHERE site_id='$site_id' AND user_id='$user_id'";    
            $result = $mysqli->query($prep_stmt);        
            $i = 0;
            while($row=$result->fetch_assoc()){
                $loca_id_old[$i] = $row['loca_id'];
                $i++;
            }
            $loca_id_array = array_column($data,0);
            $i = 0;    
            if(isset($loca_id_old)){
                foreach($loca_id_old as $value){
                    if(!in_array($value,$loca_id_array)){
                        $prep_stmt = "DELETE FROM LOCA WHERE site_id='$site_id' AND user_id='$user_id' AND loca_id='$value' LIMIT 1";
                        $result = $mysqli->query($prep_stmt);
                        // Delete rows for SAMP
                        $prep_stmt = "DELETE FROM SAMP WHERE site_id='$site_id' AND user_id='$user_id' AND loca_id='$value'";
                        $result = $mysqli->query($prep_stmt);    
                        // Delete files
                        array_map('unlink', glob("$target_dir/$value/FILES/*.*"));
                        rmdir("$target_dir/$value/FILES");    
                        array_map('unlink', glob("$target_dir/$value/*.*"));
                        rmdir("$target_dir/$value");
                        $LOCA_ID_del[$i] = $value;
                        $i++;
                    }
                }
            }
            // Update and Save
            $i = 0;
            foreach($data as $value){        
                $loca_id = $value[0];            
                $lat = $value[1];
                $lon = $value[2];
                $loca_type = $value[3];
                $elev = $value[4];
                $fdepth = $value[5];
                $start = $value[6];    
                $end = $value[7];    
                $note = $value[8];
                $req = array($loca_id,$lat,$lon,$loca_type);
                if(in_array(null,$req)){
                    $LOCA_ID_req[$i] = $loca_id;
                } else {
                    // Update
                    $prep_stmt = "SELECT loca_id FROM LOCA WHERE site_id='$site_id' AND user_id='$user_id' AND loca_id='$loca_id'";
                    $result = $mysqli->query($prep_stmt);
                    if($result -> num_rows > 0)
                    {
                        if($mysqli->query("UPDATE LOCA SET user_id='$user_id', site_id='$site_id', loca_id='$loca_id', loca_type='$loca_type', lat='$lat', lon='$lon', elev='$elev', fdepth='$fdepth', start='$start', end='$end', note='$note' WHERE site_id='$site_id' AND user_id='$user_id' AND loca_id='$loca_id'")) {
                            $LOCA_ID_update[$i] = $loca_id;
                        }
                    } else {
                        // NEW
                        if($mysqli->query("INSERT INTO LOCA(user_id,site_id,loca_id,loca_type,lat,lon,elev,fdepth,start,end,note) VALUES('$user_id','$site_id','$loca_id','$loca_type','$lat','$lon','$elev','$fdepth','$start','$end','$note')")) {
                            $LOCA_ID_new[$i] = $loca_id;
                        }
                    }
                }
                $i++;
            }
            // Server message        
            if(count($LOCA_ID_req) > 0){
                $saveMsg = 'Please fill in required field for </b></i>'.implode(", ",$LOCA_ID_req).'.';
            } else {            
                if(count($LOCA_ID_new) == 1){
                    $saveMsg1 = 'Location ID <b><i>'.implode(", ",$LOCA_ID_new).'</i></b> has been created.';
                } else if(count($LOCA_ID_new) > 1){
                    $saveMsg1 = 'Location ID <b><i>'.implode(", ",$LOCA_ID_new).'</i></b> have been created.';
                }
                if(count($LOCA_ID_update) == 1){
                    $saveMsg2 = 'Location ID <b><i>'.implode(", ",$LOCA_ID_update).'</i></b> has been updated.';
                } else if(count($LOCA_ID_update) > 1){
                    $saveMsg2 = 'Location ID <b><i>'.implode(", ",$LOCA_ID_update).'</i></b> have been updated.';
                }
                if(count($LOCA_ID_del) == 1){
                    $saveMsg3 = 'Location ID <b><i>'.implode(", ",$LOCA_ID_del).'</i></b> has been deleted.';
                } else if(count($LOCA_ID_del) > 1){
                    $saveMsg3 = 'Location ID <b><i>'.implode(", ",$LOCA_ID_del).'</i></b> have been deleted.';
                }
                $saveMsg = $saveMsg0.'<br>'.$saveMsg1.'<br>'.$saveMsg2.'<br>'.$saveMsg3;
            }        

            ////////////////////////////////////////////////////
            // Save to LOCA file        

            $target_file = $target_dir.'/'.$group.'.csv';

            $output = fopen($target_file,'w') or die("Can't open $target_file");

            // Combine info with heading
            $info_comb = array(        
                array_merge(array('GROUP'),array($group)),
                array_merge(array('HEADING'),$heading),
                array_merge(array('UNIT'),$unit),
                array_merge(array('TYPE'),$type)
            );    
            // Write csv
            foreach($info_comb as $value) {
                fputcsv($output, $value);
            }
            foreach($data as $value) {
                $value = array_merge(array('DATA'),$value);
                fputcsv($output, $value);
            }
            $stat = fstat($output);
            ftruncate($output, $stat['size']-1);
            fclose($output) or die("Can't close $target_file");    
        }
    }
}

///////////////////////////////////////////////////////////////////////////////
// Delete
if($_POST['btn-delete'] != null)
{   
    $user_id = $_SESSION['user_id'];
    $site_id = $_SESSION['site_id'];
    unset($_SESSION['site_id']);
    unset($_SESSION['PROJ']);    
    unset($_SESSION['MAPF']);    
    
    // Delete files
    $prep_stmt = "SELECT * FROM PROJ WHERE site_id='$site_id'";
    $result = $mysqli->query($prep_stmt);
    $row = $result->fetch_assoc();
    $site_name = $row['site_name'];    
    $site_name_srch = filter_input(INPUT_POST, 'site_name_srch', FILTER_SANITIZE_STRING);
    // tmp folder file
    if(str_split($site_name_srch,5)[0] == "DRAFT" | $site_name_srch == null){        
        $target_dir = './uploads/tmp/'.$site_name;
    } else {
        $target_dir = './uploads/sites/'.$site_name;
    }
    Delete($target_dir);
    
    // Delete rows in SQL table
    if($mysqli->query("DELETE FROM PROJ WHERE user_id='$user_id' AND site_id='$site_id'")) 
    {        
        $saveMsg = '<i><b>'.$site_name.'</b></i> has been deleted.';
        $mysqli->query("DELETE FROM LOCA WHERE user_id='$user_id' AND site_id='$site_id'");
        $mysqli->query("DELETE FROM SAMP WHERE user_id='$user_id' AND site_id='$site_id'");            
    }    
    
    // For PROJ
    $target_group = "PROJ";
    $target_file = './tmp/AGS4_new.csv';
    $_SESSION['PROJ'] = tmp_load($target_file,$target_group);

    // For MAPF
    $target_group = "MAPF";
    $target_file = $target_dir.'/'.$target_group.'.csv';
    $_SESSION['MAPF'] = tmp_load($target_file,$target_group);    
    
    // For LOCA
    $target_group = "LOCA";
    $target_file = $target_dir.'/'.$target_group.'.csv';
    $_SESSION['LOCA'] = tmp_load($target_file,$target_group);    
}

///////////////////////////////////////////////////////////////////////////////
// Recall Site Information

if(isset($_SESSION['site_id']))
{
    // Load PROJ
    $target_group = "PROJ";    
    $site_id = $_SESSION['site_id'];
    $prep_stmt = "SELECT * FROM PROJ WHERE site_id='$site_id' LIMIT 1";
    $result = $mysqli->query($prep_stmt);
    $row = $result->fetch_assoc();
    $site_name = $row['site_name'];
    $status = $row['status'];
    if($status == 'DRAFT'){
        $target_dir = './uploads/tmp/'.$site_name;        
    } else if($status == 'COMPLETE'){
        $target_dir = './uploads/sites/'.$site_name;
    }
    $site_name_srch = $status.": ".$site_name;    
    $target_file = $target_dir.'/'.$target_group.'.csv';
    // Set session LOCAL
    $_SESSION['PROJ'] = tmp_load($target_file,$target_group);
    
    // Load MAPF
    $target_group = "MAPF";
    $target_file = $target_dir.'/'.$target_group.'.csv';
    $_SESSION['MAPF'] = tmp_load($target_file,$target_group);    
       
    // Load LOCA
    $target_group = "LOCA";    
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
    $target_file = $target_dir.'/'.$target_group.'.csv';
    // Set session LOCAL
    $_SESSION['LOCA'] = tmp_load($target_file,$target_group);    
}

/////////////////////////////////////////////////////////////////////////////////
//// Next 
if($_POST['btn-next'] != null)
{
    if($site_id == null){
        $saveMsg = "Please <b>SAVE</b> or <b>SEARCH</b> first.";
    } else {
        header("Location: ./upload_EVNG.php");
    }    
}

///////////////////////////////////////////////////////////////////////////////
// Next 
//if($_POST['btn-next'] != null)
//{   
//    $allchecked = 'Y';  
//    $user_id = $_SESSION['user_id'];
//    $site_id = $_SESSION['site_id'];
//    $prep_stmt = "SELECT * FROM LOCA WHERE user_id='$user_id' AND site_id='$site_id'";
//    $result = $mysqli->query($prep_stmt);    
//    $i = 0;
//    while($row=$result->fetch_assoc()){
//        if($row['checked'] != 'Y'){
//            $allchecked = 'N';
//            $loca_id_unchecked[$i] = $row['loca_id'];
//            $i++;
//        }
//    }    
//    if($allchecked == 'N'){
//        $saveMsg = "Please complete data add for <b>".implode(", ",$loca_id_unchecked)."</b>.";
//        $_POST['btn-next'] = null;
//    } else {
//        header("Location: ./upload_EVNG.php");
//    }
//}

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
        <td width="33%" style="background:#3284BF">Site Information</td>
        <td width="33%">Event Information</td>
        <td width="33%">Ground Performance</td>        
    </tr>
</table>
</div>
    
<!----------------------- Map  ------------------------->        
<div id='upload' class='files'>
    <input type='hidden' id='cl_lat'>
    <input type='hidden' id='cl_lon'>
    
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
    <tr>
        <td width="100px">Create a new site</td>
        <td width="400px">
            <button type="submit" name="btn-new">New</button>
        </td>
        <td width="100px">Search site name</td>
        <td width="300px">
            <select id="new_site" name="site_name_srch" onchange="sitelist()" style="width:100%">
            <option value="<?=$site_name_srch?>">
                <?if(isset($_SESSION['site_id'])) echo $status.': '.$site_name;?>
            </option>
            <?php
            $user_id = $_SESSION['user_id'];
            $prep_stmt = "SELECT * FROM PROJ WHERE user_id='$user_id'";
            $result = $mysqli->query($prep_stmt);
            while($row = $result->fetch_assoc()){
                if($row['site_name'] != $site_name){
                ?>
                    <option value="<?=$row['status'].': '.$row['site_name'];?>"><?=$row['status'].': '.$row['site_name'];?></option>
                <?}
            }?> 
            </select>            
            <input type="hidden" name="btn-search">
        </td>
    </tr>
<!--
    <tr>
        <td></td><td></td>
        <td>Fill data from file</td>
        <td>
            <input style='width:200px' type='file' id='data_file' name='data_file' onchange=uploadFile()>
            <input type='hidden' id='data_upload'>
        </td>
    </tr>
-->
</table>
</form>

<form method="post" id="myform" name="myform" enctype="multipart/form-data"> 
<div id="addGroup"></div>
<!--Input section load per test-->
<?php
                
if(isset($_SESSION['PROJ']))
{
    ?>
    
<script>       
    
    // Site information
    <?$LOCAL = $_SESSION['PROJ'];?>
    var group = <?=json_encode($LOCAL[0])?>;    
    var title = <?=json_encode($LOCAL[4])?>;
    var heading = <?=json_encode($LOCAL[5])?>;
    var comments = <?=json_encode($LOCAL[7])?>;
    var req = <?=json_encode($LOCAL[8])?>;
    var unit = <?=json_encode($LOCAL[2])?>;
    var json_data = <?=json_encode($LOCAL[6])?>;
        
    DataLoad(this.form,group,title,heading,comments,unit,json_data,req);
    document.getElementById(group+"[0][5]").style.textAlign = "left";
    EntryInput(this.form,group,heading,json_data);
    
    // for additional files    
    <?$FILES = $_SESSION['MAPF'];?>
    var tmp = <?=json_encode($status)?>;
    if(tmp == 'COMPLETE'){
        tmp = 'sites';
    } else {
        tmp = 'tmp';
    }        
    var site = document.getElementById(group+"[0][0]").value;
    var group = <?=json_encode($FILES[0])?>;
    var heading = <?=json_encode($FILES[5])?>;
    var comments = <?=json_encode($FILES[7])?>;
    var files = <?=json_encode($FILES[6])?>;
    FileLoad(this.form,group,heading,comments,tmp,site,files);
    
    // Location details
    <?$LOCAL = $_SESSION['LOCA'];?>
    var group = <?=json_encode($LOCAL[0])?>;    
    var title = <?=json_encode($LOCAL[4])?>;
    var heading = <?=json_encode($LOCAL[5])?>;
    var comments = <?=json_encode($LOCAL[7])?>;
    var req = <?=json_encode($LOCAL[8])?>;
    var unit = <?=json_encode($LOCAL[2])?>;
    var json_data = <?=json_encode($LOCAL[6])?>;
    DataLoad(this.form,group,title,heading,comments,unit,json_data,req);
    EntryInput(this.form,group,heading,json_data);
    
    <?  
    $prep_stmt = "SELECT * FROM LOCA WHERE user_id='$user_id' AND site_id='$site_id'";
    $result = $mysqli->query($prep_stmt);
    $row_length = $result->num_rows; 

    if($row_length == 0){ 
    ?>
        document.getElementsByClassName("button")[2].style.backgroundColor = 'gray';
    <?  
    } else {
        $i = 0;
        while($row = $result->fetch_assoc()){
            $loca_id = $row['loca_id'];  
    ?>
            var loca_id = <?=json_encode($loca_id)?>;
            var row_length = <?=json_encode($row_length)?>;
            var row_checked = <?=json_encode($row['checked'])?>;
            console.log(row_checked)    
            for(j=0;j<row_length;j++){
                if(document.getElementById(group+"["+j+"][0]").value == loca_id){
                   document.getElementById(group+"["+j+"][0]").readOnly = true; 
                   document.getElementById(group+"["+j+"][0]").style.backgroundColor = 'aliceblue';
                   document.getElementById(group+"["+j+"][0]").style.borderColor = 'aliceblue';
                   if(row_checked == "Y"){
                       document.getElementById(group+"["+j+"][9]").value = 'edit';
                       document.getElementById(group+"["+j+"][10]").style.color = 'green';   
                   }
                }
            }
            <?
            $i++;        
        }
    }?>
    
</script>                                                     

    <!-- Save buttons    -->
    
<table width="100%" align="center" border="0">
    <tr>
        <td width="25%"></td>
        <td colspan=2 align="center">
            <!-- For searched site name -->
            <input type="hidden" id="site_list" name="site_name_srch" value="<?=$site_name_srch?>">
            <button type="submit" name="btn-save">Save</button> 
            <input class="button-large" type="button" onClick="del_confirm('<?=$site_name?>')" value="Delete">
            <input type="hidden" id="del_id" name="btn-delete">
        </td>
        <td width="25%" align="right">
            <input class="button-large" type="button" onClick="save_confirm(<?=$_SESSION['site_id']?>)" value="Next">
            <input type="hidden" id="next_id" name="btn-next">
        </td>
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
<? include_once 'includes/inner_map_PROJ.php';?>

<?
} else {
?>
<!----------------------- Sign in ask ------------------------->
<center><h2>Please sign in first.</h2></center>
<?php }?>
<!---------------------End of Body --------------------------------->    
</div>
</body>
</html>