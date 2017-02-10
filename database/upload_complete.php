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
}
// SaveMsg
$saveMsg = $_GET['saveMsg'];

///////////////////////////////////////////////////////////////////////////////
// Function for copy
function recurse_copy($src,$dst) { 
    $dir = opendir($src); 
    @mkdir($dst); 
    while(false !== ( $file = readdir($dir)) ) { 
        if (( $file != '.' ) && ( $file != '..' )) { 
            if ( is_dir($src . '/' . $file) ) { 
                recurse_copy($src . '/' . $file,$dst . '/' . $file); 
            } 
            else { 
                copy($src . '/' . $file,$dst . '/' . $file); 
            } 
        } 
    } 
    closedir($dir); 
}
///////////////////////////////////////////////////////////////////////////////
// Previous 
if(isset($_POST['btn-previous']))
{
    header("Location: ./upload_FLDP.php");
}

///////////////////////////////////////////////////////////////////////////////
// Save Site Information
if($_POST['btn-save'] != null)
{       
    $site_id = $_SESSION['site_id'];
    // Get site name
    $prep_stmt = "SELECT * FROM PROJ WHERE user_id='$user_id' AND site_id='$site_id'";    
    $result = $mysqli->query($prep_stmt);
    $row=$result->fetch_assoc();
    $site_name = $row['site_name'];
    $status = $row['status'];
    if($status == 'DRAFT'){
        // Update status in SQL
        $prep_stmt = "UPDATE PROJ SET status='COMPLETE' WHERE user_id='$user_id' AND site_id='$site_id'";
        $result = $mysqli->query($prep_stmt);
        // Target directory to move
        $target_dir_tmp = './uploads/tmp/'.$site_name;
        $target_dir = './uploads/sites/'.$site_name;  
        rename($target_dir_tmp,$target_dir);    
    }
        
    ///////////////////////////////////////////////////////////////////////////
    // Merge file and update to 'download' folder
    ///////////////////////////////////////////////////////////////////////////
    // Open Output file 
    $target_dir = './download';
    if(is_dir($target_dir) === false){ mkdir($target_dir);}
    $target_file = $target_dir.'/'.$site_name.'.csv';
    $output = fopen($target_file,'w') or die("Can't open $target_file");
    ////// Merge data for site info//////
    $target_dir = './uploads/sites/'.$site_name;    
    // Load target fields if exists
    $fields = array();
    foreach(glob($target_dir.'/*.csv') as $file){
        $fields[] = substr($file,-8,4);
    }    
    $target_fields = array('PROJ','MAPF','LOCA','EVNG');
    foreach($target_fields as $field) {
        if(in_array($field,$fields)){
            $target_file = $target_dir.'/'.$field.'.csv';
            $file_content = file_get_contents($target_file);
            $tmp = array_map('str_getcsv', preg_split('/\r*\n+|\r+/',$file_content));
            $$field = $tmp;
        }
    }
    // Write csv
    $target_fields = array('PROJ','MAPF','LOCA','EVNG');
    foreach($target_fields as $field) {
        foreach($$field as $value) {
            fputcsv($output, $value);
        }
        fputcsv($output,array(' '));
    }
    // Copy Associated FILES    
    $target_dir_tmp = $target_dir.'/FILES';
    $target_dir = './download/'.$site_name;
    if(is_dir($target_dir) === false){ mkdir($target_dir);}
    $target_dir = './download/'.$site_name.'/FILES';
    if(is_dir($target_dir) === false){ mkdir($target_dir);}
    recurse_copy($target_dir_tmp,$target_dir);
    
    ////// Merge data for loca info //////
    $loca_size = sizeof($LOCA)-4;
    for($i=0;$i<$loca_size;$i++){
        $loca_name = $LOCA[$i+4][1];        
        $loca_type = $LOCA[$i+4][4];        

        ////// Merge data //////
        $target_dir = './uploads/sites/'.$site_name.'/'.$loca_name;
        // target fields for each loca type //
        if($loca_type == 'HDPH'){
            $target_fields = array('HDPH','LOCF','GEOL','DETL','ISPT','SAMP');
        } else if($loca_type == 'SCPG'){
            $target_fields = array('SCPG','LOCF','SCPT','SAMP');
        } else if($loca_type == 'TEPT'){
            $target_fields = array('TEPT','LOCF','GEOL','DETL','SAMP');
        } else if($loca_type == 'GPVS'){
            $target_fields = array('GPVS','LOCF','GSWD','GSWV','GDHL','GCHL','GSPL');
        }        
        // Load target field if exists
        $fields = array();
        foreach(glob($target_dir.'/*.csv') as $file){
            $fields[] = substr($file,-8,4);
        }
        foreach($target_fields as $field) {
            if(in_array($field,$fields)){
                $target_file = $target_dir.'/'.$field.'.csv';
                $file_content = file_get_contents($target_file);
                $tmp = array_map('str_getcsv', preg_split('/\r*\n+|\r+/',$file_content));
                // Array push
                if(!isset($$field)){
                    $$field = $tmp;
                } else {
                    $l = sizeof($tmp)-4;
                    for($ii=0;$ii<$l;$ii++){
                        array_push($$field,$tmp[$ii+4]);
                    }                    
                }
            }
        }        
        // Copy Associated FILES    
        $target_dir_tmp = $target_dir.'/FILES';
        if(is_dir($target_dir_tmp)){
            $target_dir = './download/'.$site_name.'/'.$loca_name;
            if(is_dir($target_dir) === false){ mkdir($target_dir);}
            $target_dir = './download/'.$site_name.'/'.$loca_name.'/FILES';        
            if(is_dir($target_dir) === false){ mkdir($target_dir);}
            recurse_copy($target_dir_tmp,$target_dir);
        }
        
        ///////////////////////
        // Save for SAMP
        if(in_array('SAMP',$fields)){
            $samp_size = sizeof($SAMP)-4;
            for($j=0;$j<$samp_size;$j++){
                $samp_name = $SAMP[$j+4][2];
                ////// Merge data //////
                $target_dir = './uploads/sites/'.$site_name.'/'.$loca_name.'/'.$samp_name;
                // target fields //
                $target_fields = array('LABG','LABF','INDX','GRAT','OTHR');
                // Load target field if exists
                $fields = array();
                foreach(glob($target_dir.'/*.csv') as $file){
                    $fields[] = substr($file,-8,4);
                }
                foreach($target_fields as $field) {
                    if(in_array($field,$fields)){
                        $target_file = $target_dir.'/'.$field.'.csv';
                        $file_content = file_get_contents($target_file);
                        $tmp = array_map('str_getcsv', preg_split('/\r*\n+|\r+/',$file_content));
                        // Array push
                        if(!isset($$field)){
                            $$field = $tmp;
                        } else {
                            $l = sizeof($tmp)-4;
                            for($ii=0;$ii<$l;$ii++){
                                array_push($$field,$tmp[$ii+4]);
                            }                    
                        }
                    }
                }                
                // Copy Associated FILES    
                $target_dir_tmp = $target_dir.'/FILES';
                if(is_dir($target_dir_tmp)){
                    $target_dir = './download/'.$site_name.'/'.$loca_name.'/'.$samp_name;
                    if(is_dir($target_dir) === false){ mkdir($target_dir);}
                    $target_dir = './download/'.$site_name.'/'.$loca_name.'/'.$samp_name.'/FILES';
                    if(is_dir($target_dir) === false){ mkdir($target_dir);}
                    recurse_copy($target_dir_tmp,$target_dir);
                }
            }
        }
    }
    // Write csv
    $target_fields = array('HDPH','SCPG','TEPT','GPVS','LOCF','GEOL','DETL','ISPT','SCPT','GSWD','GSWV','GDHL','GCHL','GSPL','SAMP','LABG','LABF','INDX','GRAT','OTHR');
    foreach($target_fields as $field) {
        if(isset($$field)){
            foreach($$field as $value) {
                fputcsv($output, $value);
            }
            fputcsv($output,array(' '));
        }        
    }    
    ///////////////////////////////////////////////////////////////////////////
    // Output file for field observation info for an event
    $evng_size = sizeof($EVNG)-4;
    for($i=0;$i<$evng_size;$i++){
        $evng_name = $EVNG[$i+4][2];
        ////// Merge data //////
        $target_dir = './uploads/sites/'.$site_name.'/'.$evng_name;
        // target fields //
        $target_fields = array('EVNF','FLDP','FLDF','FLDO','GRMN','GRMF');
        // Load target field if exists
        $fields = array();
        foreach(glob($target_dir.'/*.csv') as $file){
            $fields[] = substr($file,-8,4);
        }
        foreach($target_fields as $field) {
            if(in_array($field,$fields)){
                $target_file = $target_dir.'/'.$field.'.csv';
                $file_content = file_get_contents($target_file);
                $tmp = array_map('str_getcsv', preg_split('/\r*\n+|\r+/',$file_content));
                // Array push
                if(!isset($$field)){
                    $$field = $tmp;
                } else {
                    $l = sizeof($tmp)-4;
                    for($ii=0;$ii<$l;$ii++){
                        array_push($$field,$tmp[$ii+4]);
                    }                    
                }
            }
        }
        // Copy Associated FILES    
        $target_dir_tmp = $target_dir.'/FILES';        
        if(is_dir($target_dir_tmp)){
            $target_dir = './download/'.$site_name.'/'.$evng_name;
            if(is_dir($target_dir) === false){ mkdir($target_dir);}
            $target_dir = './download/'.$site_name.'/'.$evng_name.'/FILES';
            if(is_dir($target_dir) === false){ mkdir($target_dir);}
            recurse_copy($target_dir_tmp,$target_dir);
        }
    }
    // Write csv
    $target_fields = array('EVNF','FLDP','FLDF','FLDO','GRMN','GRMF');
    foreach($target_fields as $field) {
        if(isset($$field)){
            foreach($$field as $value) {
                fputcsv($output, $value);
            }
            fputcsv($output,array(' '));
        }        
    }
    fclose($output) or die("Can't close output file");
    
    ///////////////////////////////////////////////////////////////////////////
    // Generate sub-output files
    
    ////// Aggregated File
    $target_file = './download/'.$site_name.'.csv';
    
    ////// LOCA files
    $prep_stmt = "SELECT * FROM LOCA WHERE user_id='$user_id' AND site_id='$site_id'";
    $result = $mysqli->query($prep_stmt);
    while($row=$result->fetch_assoc()){        
        $loca_id = $row['loca_id']; // Loca id        
        if($row['loca_type'] == 'HDPH'){            
            $target_groups = array("LOCA","HDPH","LOCF","GEOL","DETL","ISPT");
        } else if($row['loca_type'] == 'SCPG'){            
            $target_groups = array("LOCA","SCPG","LOCF","SCPT");
        } else if($row['loca_type'] == 'TEPT'){            
            $target_groups = array("LOCA","TEPT","LOCF","GEOL","DETL");
        } else if($row['loca_type'] == 'GPVS'){            
            $target_groups = array("LOCA","GPVS","LOCF",'GSWD','GSWV','GDHL','GCHL','GSPL');
        }
        ///////////////////////////////////////////////////////////////////////
        ////// Create File for selected LOCA
        $target_dir = "./download/$site_name/$loca_id";
        if(is_dir($target_dir) === false){ mkdir($target_dir);}        
        $output_file = $target_dir."/$loca_id.csv";
        $output = fopen($output_file,'w') or die("Can't open $output_file");

        foreach($target_groups as $target_group){
            $FILES = tmp_load($target_file,$target_group,1);
            if($FILES[6][0][0] != null){
                // Write heading            
                $info_comb = array(        
                    array_merge(array('GROUP'),array($FILES[0])),
                    array_merge(array('HEADING'),$FILES[1]),
                    array_merge(array('UNIT'),$FILES[2]),
                    array_merge(array('TYPE'),$FILES[3])
                );
                foreach($info_comb as $value){
                    fputcsv($output,$value);
                };
            }
            // Find rows for selected LOCA
            foreach($FILES[6] as $value){
                if($value[0] == $loca_id){
                    fputcsv($output, array_merge(array('DATA'),$value));
                }
            }
            fputcsv($output, array(' '));
        }
        fclose($output) or die("Can't close output file");           
    }
    
    ////// SAMP files
    $prep_stmt = "SELECT * FROM SAMP WHERE user_id='$user_id' AND site_id='$site_id'";
    $result = $mysqli->query($prep_stmt);
    while($row=$result->fetch_assoc()){        
        $loca_id = $row['loca_id']; // Loca id
        $samp_id = $row['samp_id']; // Samp id
        $target_groups = array('SAMP','LABG','LABF','INDX','GRAT','OTHR');
        ///////////////////////////////////////////////////////////////////////
        ////// Create File for selected SAMP
        $target_dir = "./download/$site_name/$loca_id/$samp_id";
        if(is_dir($target_dir) === false){ mkdir($target_dir);}        
        $output_file = $target_dir."/$samp_id.csv";
        $output = fopen($output_file,'w') or die("Can't open $output_file");

        foreach($target_groups as $target_group){
            $FILES = tmp_load($target_file,$target_group,1);
            if($FILES[6][0][0] != null){
                // Write heading            
                $info_comb = array(        
                    array_merge(array('GROUP'),array($FILES[0])),
                    array_merge(array('HEADING'),$FILES[1]),
                    array_merge(array('UNIT'),$FILES[2]),
                    array_merge(array('TYPE'),$FILES[3])
                );
                foreach($info_comb as $value){
                    fputcsv($output,$value);
                };
            }
            // Find rows for selected SAMP
            foreach($FILES[6] as $value){
                if($value[0] == $loca_id){
                    fputcsv($output, array_merge(array('DATA'),$value));
                }
            }
            fputcsv($output, array(' '));
        }
        fclose($output) or die("Can't close output file");           
    }
    
    ////// FLDO files
    $prep_stmt = "                    
                SELECT FLDO.*, EVNG.evt_name
                FROM FLDO                
                INNER JOIN EVNG ON FLDO.evt_id = EVNG.evt_id
                WHERE FLDO.user_id='$user_id' AND FLDO.site_id='$site_id'
                ";
    $result = $mysqli->query($prep_stmt);

    while($row=$result->fetch_assoc()){
        $evt_name = $row['evt_name']; // evt name
        $evt_id = $row['evt_id']; // evt id
        $fldo_id = $row['id']; // fldo id
        $target_groups = array("FLDO");
        ///////////////////////////////////////////////////////////////////////
        ////// Create File for selected SAMP
        $target_dir = "./download/$site_name/$evt_name";
        if(is_dir($target_dir) === false){ mkdir($target_dir);}                
    
        $output_file = $target_dir."/$evt_name-$fldo_id.csv";
        $output = fopen($output_file,'w') or die("Can't open $output_file");

        foreach($target_groups as $target_group){
            $FILES = tmp_load($target_file,$target_group,1);
            if($FILES[6][0][0] != null){
                // Write heading            
                $info_comb = array(        
                    array_merge(array('GROUP'),array($FILES[0])),
                    array_merge(array('HEADING'),$FILES[1]),
                    array_merge(array('UNIT'),$FILES[2]),
                    array_merge(array('TYPE'),$FILES[3])
                );
                foreach($info_comb as $value){
                    fputcsv($output,$value);
                };
            }
            
            // Find rows for selected FLDO
            foreach($FILES[6] as $value){
                if($value[0] == $evt_id & $value[1] == $fldo_id){                    
                    fputcsv($output, array_merge(array('DATA'),$value));
                    $assocfile = $value[8];
                }
            }
            fputcsv($output, array(' '));
        }
        fclose($output) or die("Can't close output file");   
    }    
    header("Location: ./upload_success.php");
}

///////////////////////////////////////////////////////////////////////////////
// Recall Site Information
if(isset($_SESSION['site_id']))
{        
    // Site name
    $site_id = $_SESSION['site_id'];
    $prep_stmt = "SELECT * FROM PROJ WHERE site_id='$site_id' LIMIT 1";
    $result = $mysqli->query($prep_stmt);
    $row = $result->fetch_assoc();
    $site_name = $row['site_name'];
    $status = $row['status'];
    // Event name
    $prep_stmt = "SELECT * FROM EVNG WHERE site_id='$site_id'";
    $result = $mysqli->query($prep_stmt);
    $i = 0;
    while($row = $result->fetch_assoc()){
        $evt_name[$i] = $row['evt_name'];
        $i++;
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
    
</head>
    
<body>
<!----------------------- Start of header -------------------------->
<?php include_once 'includes/head.html';?>

<!----------------------- Start of Body ---------------------------->
<div id="container" class="home">        
<?php 
if(isset($_SESSION['user'])){
?>
<!----------------------- Progress Bar  ------------------------->            
<div id="myProgress">
  <div id="myBar">
    <div id="label">10%</div>
  </div>
</div>
    
<!----------------------- Map  ------------------------->        
<div id='upload' class='files'>
<table width="100%" align="center" border="0">
<!--    Map-->
    <tr><td colspan=2><div id="map_upload"></div></td></tr>
<!--    save Message-->
</table>
</div>
    
<!----------------------- Search existed data ---------------------->        
<div id='upload' class='body'>
<form method="post" id="myform" name="myform" enctype="multipart/form-data"> 
<div id="addGroup"></div>
<!--Input section load per test-->
<script>
    function GroupReview(data,evt,loca,samp){
        var group = data[0];
        var title = data[4];
        var heading = data[5];
        var comments = data[7];
        var req = data[8];
        var unit = data[2];
        var json_data = data[6];
        ReviewLoad(this.form,group,title,heading,comments,unit,json_data,req,evt,loca,samp);
    }
    function GroupReviewFile(data,tmp,site,evt,loca,samp){
        var group = data[0];
        var title = data[4];
        var heading = data[5];
        var comments = data[7];
        var req = data[8];
        var unit = data[2];
        var json_data = data[6];        
        ReviewFileLoad(this.form,group,heading,comments,tmp,site,json_data,evt,loca,samp);
    }
    
    var site = <?=json_encode($site_name)?>;
    var tmp = <?=json_encode($status)?>;
    
    <?
    if($status == 'DRAFT') $target_dir = './uploads/tmp/'.$site_name;
    else if($status == 'COMPLETE') $target_dir = './uploads/sites/'.$site_name;
    
    ///////////////////////////////////////////////////////////////////////////
    // for PROJ
    $target_group = 'PROJ';
    $target_file = $target_dir.'/'.$target_group.'.csv';
    $_SESSION[$target_group] = tmp_load($target_file,$target_group);    
    ?>
    var data = <?=json_encode($_SESSION[$target_group])?>;
    GroupReview(data);
    <?
    $target_group = 'MAPF';
    $target_file = $target_dir.'/'.$target_group.'.csv';
    $_SESSION[$target_group] = tmp_load($target_file,$target_group);    
    ?>
    var data = <?=json_encode($_SESSION[$target_group])?>;
    if(data[6][0][0] != ''){
        GroupReviewFile(data,tmp,site);
    }
    ///////////////////////////////////////////////////////////////////////////
    // for LOCA
    <?                 
    $target_group = 'LOCA';
    $target_file = $target_dir.'/'.$target_group.'.csv';
    $_SESSION[$target_group] = tmp_load($target_file,$target_group);
    ?>
    var data = <?=json_encode($_SESSION[$target_group])?>;
    GroupReview(data);
    ///////////////////////////////////////////////////////////////////////////
    // for Geotechnical and Geophysical tests
    <?
    foreach($_SESSION['LOCA'][6] as $LOCA){
        $target_sub_dir = $target_dir.'/'.$LOCA[0];
        // Main group
        $target_group = $LOCA[3];
        $target_file = $target_sub_dir.'/'.$target_group.'.csv';
        $_SESSION[$target_group] = tmp_load($target_file,$target_group);
        ?>
        var data = <?=json_encode($_SESSION[$target_group])?>;
        if(data[6][0][0] != ''){
            GroupReview(data,null,<?=json_encode($LOCA[0])?>);
        }
        <?
        // Associated files
        $target_group = 'LOCF';
        $target_file = $target_dir.'/'.$target_group.'.csv';
        $_SESSION[$target_group] = tmp_load($target_file,$target_group);    
        ?>
        var data = <?=json_encode($_SESSION[$target_group])?>;
        if(data[6][0][0] != ''){
            GroupReviewFile(data,tmp,site,null,<?=json_encode($LOCA[0])?>);
        }
        <?
        // Sub group        
        if($LOCA[3] == 'HDPH'){
            $groups = array('GEOL','DETL','ISPT','SAMP');
        } else if($LOCA[3] == 'SCPG'){
            $groups = array('SCPT','SAMP');
        } else if($LOCA[3] == 'TEPT'){
            $groups = array('GEOL','DETL','SAMP');
        } else if($LOCA[3] == 'GPVS'){
            $groups = array('GSWD','GSWV','GDHL','GCHL','GSPL');
        }
        foreach($groups as $target_group){
            $target_file = $target_sub_dir.'/'.$target_group.'.csv';
            $_SESSION[$target_group] = tmp_load($target_file,$target_group);
            ?>
            var data = <?=json_encode($_SESSION[$target_group])?>;
            if(data[6][0][0] != ''){
                GroupReview(data,null,<?=json_encode($LOCA[0])?>);
                <?
                if($target_group == 'SAMP'){
                    foreach($_SESSION['SAMP'][6] as $SAMP){                      
                    $target_sub2_dir = $target_sub_dir.'/'.$SAMP[0];
                    // Main group
                    $target_samp_group = 'LABG';
                    $target_samp_file = $target_sub2_dir.'/'.$target_samp_group.'.csv';
                    $_SESSION[$target_samp_group] = tmp_load($target_samp_file,$target_samp_group);
                    ?>
                    var data = <?=json_encode($_SESSION[$target_samp_group])?>;
                    if(data[6][0][0] != ''){
                        GroupReview(data,null,<?=json_encode($LOCA[0])?>,<?=json_encode($SAMP[0])?>);
                    }
                    <?
                    // Associated files
                    $target_samp_group = 'LABF';
                    $target_samp_file = $target_sub2_dir.'/'.$target_samp_group.'.csv';
                    $_SESSION[$target_samp_group] = tmp_load($target_samp_file,$target_samp_group);
                    ?>
                    var data = <?=json_encode($_SESSION[$target_samp_group])?>;
                    if(data[6][0][1] != ''){
                        GroupReviewFile(data,tmp,site,null,<?=json_encode($LOCA[0])?>,<?=json_encode($SAMP[0])?>);
                    }
                    <?
                    $samp_groups = array('INDX','GRAT','OTHR');
                        foreach($samp_groups as $target_samp_group){
                            $target_samp_file = $target_sub2_dir.'/'.$target_samp_group.'.csv';
                            $_SESSION[$target_samp_group] = tmp_load($target_samp_file,$target_samp_group);
                            ?>
                            var data = <?=json_encode($_SESSION[$target_samp_group])?>;
                            if(data[6][0][0] != ''){
                                GroupReview(data,null,<?=json_encode($LOCA[0])?>,<?=json_encode($SAMP[0])?>);
                            }
                        <?
                        }
                    }
                }
            ?>
            }
        <?
        }
    }
    ?>
    ///////////////////////////////////////////////////////////////////////////
    // for Event information (EVNG, EVNF)
    <?                 
    $target_group = 'EVNG';
    $target_file = $target_dir.'/'.$target_group.'.csv';
    $_SESSION[$target_group] = tmp_load($target_file,$target_group);
    ?>
    var data = <?=json_encode($_SESSION[$target_group])?>;
    if(data[6][0][0] != '') GroupReview(data);
    <?
    foreach($_SESSION['EVNG'][6] as $EVNG){
        // Main group
        $target_group = 'EVNF';
        $target_file = $target_dir.'/'.$EVNG[0].'/'.$target_group.'.csv';
        $_SESSION[$target_group] = tmp_load($target_file,$target_group);
        ?>
        var evt = <?=json_encode($EVNG[0])?>;
        var data = <?=json_encode($_SESSION[$target_group])?>;
        if(data[6][0][0] != '') GroupReview(data,evt);
        <?
        ///////////////////////////////////////////////////////////////////////////
        // for Field Observation (FLDP,FLDF,FLDO,GRMN,GRMF)        
        $target_group = 'FLDP';
        $target_file = $target_dir.'/'.$EVNG[0].'/'.$target_group.'.csv';
        if(!file_exists($target_file)) $target_file = './tmp/AGS4_new.csv';
        $_SESSION[$target_group] = tmp_load($target_file,$target_group);
        ?>
        var data = <?=json_encode($_SESSION[$target_group])?>;
        if(data[6][0][0] != '') GroupReview(data,evt);    
        <?
        $target_group = 'FLDF';
        $target_file = $target_dir.'/'.$EVNG[0].'/'.$target_group.'.csv';
        if(!file_exists($target_file)) $target_file = './tmp/AGS4_new.csv';
        $_SESSION[$target_group] = tmp_load($target_file,$target_group);
        ?>
        var data = <?=json_encode($_SESSION[$target_group])?>;
        if(data[6][0][0] != '') GroupReview(data,evt);    
        <?    
        $target_group = 'FLDO';
        $target_file = $target_dir.'/'.$EVNG[0].'/'.$target_group.'.csv';
        if(!file_exists($target_file)) $target_file = './tmp/AGS4_new.csv';
        $_SESSION[$target_group] = tmp_load($target_file,$target_group);
        ?>
        var data = <?=json_encode($_SESSION[$target_group])?>;
        if(data[6][0][0] != '') GroupReview(data,evt);    
        <?                 
        $target_group = 'GRMN';
        $target_file = $target_dir.'/'.$EVNG[0].'/'.$target_group.'.csv';
        if(!file_exists($target_file)) $target_file = './tmp/AGS4_new.csv';
        $_SESSION[$target_group] = tmp_load($target_file,$target_group);
        ?>
        var data = <?=json_encode($_SESSION[$target_group])?>;
        if(data[6][0][0] != '') GroupReview(data,evt);
        <?
        $target_group = 'GRMF';
        $target_file = $target_dir.'/'.$EVNG[0].'/'.$target_group.'.csv';
        if(!file_exists($target_file)) $target_file = './tmp/AGS4_new.csv';
        $_SESSION[$target_group] = tmp_load($target_file,$target_group);
        ?>
        var data = <?=json_encode($_SESSION[$target_group])?>;
        if(data[6][0][0] != '') GroupReviewFile(data,tmp,site,evt);
    <?}?>

</script>                                                     
<!-- Save buttons    -->
<table width="100%" align="center" border="0">
    <tr>
        <td width="25%" align="left">
            <button type="submit" name="btn-previous">Back</button>
        </td>
        <td colspan=2 align="center">
            <input class="button-large" type="button" onClick="upload_msg('<?=$site_name?>')" value="Submit">
            <input type="hidden" id="save_id" name="btn-save">            
        </td>
        <td width="25%" align="right">
        </td>
    </tr>
</table>
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
<?php include_once 'includes/inner_map_complete.php';
} else {
?>
<!----------------------- Sign in ask ------------------------->
<center><h2>Please sign in first.</h2></center>
<?php }?>
    
<!---------------------End of Body --------------------------------->    
</div>    
</body>
</html>