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
// Save Site Information
if(isset($_POST['btn-save']))
{   
    $site_id = $_SESSION['site_id'];
    $loca_id = $_GET['loca_id'];
    $samp_id = $_GET['samp_id'];
    $prep_stmt = "SELECT * FROM PROJ WHERE site_id='$site_id' LIMIT 1";
    $result = $mysqli->query($prep_stmt);
    $row = $result->fetch_assoc();
    $site_name = $row['site_name'];
    $status = $row['status'];
    ////////////////////////////////////////////////////////////////////////////
    // Save for LABF
    $FILES = $_SESSION['LABF'];
    $group = $FILES[0];
    if($status == 'DRAFT'){
        $target_dir = './uploads/tmp/'.$site_name.'/'.$loca_id.'/'.$samp_id.'/FILES';
    } else if ($status == 'COMPLETE'){
        $target_dir = './uploads/sites/'.$site_name.'/'.$loca_id.'/'.$samp_id.'/FILES';
    }
    if(is_dir($target_dir) === false){ mkdir($target_dir);}
    $name = filter_input(INPUT_POST, $group.'_add_name', FILTER_SANITIZE_STRING, FILTER_REQUIRE_ARRAY);    
    $desc = filter_input(INPUT_POST, $group.'_add_desc', FILTER_SANITIZE_STRING, FILTER_REQUIRE_ARRAY);
    $filename_ex = filter_input(INPUT_POST, $group.'_add_file_ex', FILTER_SANITIZE_STRING, FILTER_REQUIRE_ARRAY);
    $filename = $_FILES[$group.'_add_file']["name"];
    $tmp_filename = $_FILES[$group.'_add_file']["tmp_name"];
    /////////////////
    // Delete files
    if($name[0] == ''){
        // Delete old one
        $target_file = $target_dir.'/'.$FILES[6][0][2];
        unlink($target_file);
    } else {
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
    }
    // Save files    
    for($i=0;$i<sizeof($name);$i++){        
        $target_file = $target_dir.'/'.$filename[$i];
        move_uploaded_file($tmp_filename[$i], $target_file);        
    }    
    /////////////////
    // Save AGS file
    // Combine info with heading    
    $group = $FILES[0];
    $heading = $FILES[1];
    $unit = $FILES[2];
    $type = $FILES[3];
    $info_comb = array(        
        array_merge(array('GROUP'),array($group)),
        array_merge(array('HEADING','LOCA_ID','SAMP_ID'),$heading),
        array_merge(array('UNIT','',''),$unit),
        array_merge(array('TYPE','ID','ID'),$type)
    );    
    if($status == 'DRAFT'){
        $target_dir = './uploads/tmp/'.$site_name.'/'.$loca_id.'/'.$samp_id;
    } else if ($status == 'COMPLETE'){
        $target_dir = './uploads/sites/'.$site_name.'/'.$loca_id.'/'.$samp_id;
    }
    if(is_dir($target_dir) === false){ mkdir($target_dir);}
    $target_file = $target_dir.'/'.$group.'.csv';
    $output = fopen($target_file,'w') or die("Can't open $target_file");
    // Write csv
    foreach($info_comb as $value) {
        fputcsv($output, $value);
    }    
    for($i=0;$i<sizeof($name);$i++){
        if($filename[$i] == null){
            $value = array('DATA',$loca_id,$samp_id,$name[$i],$desc[$i],$filename_ex[$i]);
        } else {                   
            $value = array('DATA',$loca_id,$samp_id,$name[$i],$desc[$i],$filename[$i]);            
        }
        fputcsv($output, $value);
    }
    $stat = fstat($output);
    ftruncate($output, $stat['size']-1);
    fclose($output) or die("Can't close $target_file");
    // Delete AGS file for no data input
    if($name[0] == '') unlink($target_file);
    ////////////////////////////////////////////////////////////////////////////
    // Save groups    
    $GROUPS = array('LABG','INDX','GRAT');
    $j = 0;
    foreach($GROUPS as $SelectedGroup){
        $LOCAL = $_SESSION[$SelectedGroup];
        $group = $LOCAL[0];
        $heading = $LOCAL[1];
        $unit = $LOCAL[2];
        $type = $LOCAL[3];
        $title = $LOCAL[4];
        // data from table    
        $data = filter_input(INPUT_POST, $group, FILTER_SANITIZE_STRING, FILTER_REQUIRE_ARRAY);
        
        // Check empty row and remove
        $data_first = array_column($data,0);
        $idx = array_keys($data_first,'');
        for($i=0;$i<sizeof($idx);$i++) { unset($data[$idx[$i]]); }
        
        // Check data
        if($data[0][0]!=''){
            $target_file = $target_dir.'/'.$group.'.csv';
            $output = fopen($target_file,'w') or die("Can't open $target_file");
            // Combine info with heading
            $info_comb = array(        
                array_merge(array('GROUP'),array($group)),
                array_merge(array('HEADING','LOCA_ID','SAMP_ID'),$heading),
                array_merge(array('UNIT','',''),$unit),
                array_merge(array('TYPE','ID','ID'),$type)
            );    
            // Write csv
            foreach($info_comb as $value) {
                fputcsv($output, $value);
            }
            foreach($data as $value) {
                $value = array_merge(array('DATA',$loca_id,$samp_id),$value);
                fputcsv($output, $value);
            }
            $stat = fstat($output);
            ftruncate($output, $stat['size']-1);
            fclose($output) or die("Can't close $target_file");
            $saved_group[$j] = $title[1];
        }
        $j++;
    }
    
    ////////////////////////////////////////////////////////////////////////////
    // Save for OTHR
    $LOCAL = $_SESSION['OTHR'];
    $group = $LOCAL[0];
    $heading = $LOCAL[1];
    $unit = $LOCAL[2];
    $type = $LOCAL[3];
    $title = $LOCAL[4];
    if($status == 'DRAFT'){
        $target_dir = './uploads/tmp/'.$site_name.'/'.$loca_id.'/'.$samp_id.'/FILES';
    } else if ($status == 'COMPLETE'){
        $target_dir = './uploads/sites/'.$site_name.'/'.$loca_id.'/'.$samp_id.'/FILES';
    }
    if(is_dir($target_dir) === false){ mkdir($target_dir);}
    // data from table    
    $data = filter_input(INPUT_POST, $group, FILTER_SANITIZE_STRING, FILTER_REQUIRE_ARRAY);
    
    // Check empty row and remove
    $data_first = array_column($data,0);
    $idx = array_keys($data_first,'');
    for($i=0;$i<sizeof($idx);$i++) { unset($data[$idx[$i]]); }
    
    // File save for OTHR
    $filename_ex = filter_input(INPUT_POST, $group.'_add_file_ex', FILTER_SANITIZE_STRING, FILTER_REQUIRE_ARRAY);
    $filename = $_FILES[$group.'_add_file']["name"];            
    $tmp_filename = $_FILES[$group.'_add_file']["tmp_name"];    
    
    /////////////////
    // Delete files
    if($data[0][0] == ''){
        // Delete old one
        $target_file = $target_dir.'/'.$LOCAL[6][0][4];
        unlink($target_file);
    } else {
        for($i=0;$i<sizeof($LOCAL[6]);$i++){      
            if($i < sizeof($data)){            
                if($filename[$i] != null){                
                    // Delete old one
                    $target_file = $target_dir.'/'.$filename_ex[$i];                
                    unlink($target_file);
                }
            } else {
                $target_file = $target_dir.'/'.$LOCAL[6][$i][4];
                unlink($target_file);
            }
        }
    }
    // Save files    
    for($i=0;$i<sizeof($data);$i++){
        $target_file = $target_dir.'/'.$filename[$i];
        move_uploaded_file($tmp_filename[$i], $target_file);        
    }
    
    // Save AGS file
    if($status == 'DRAFT'){
        $target_dir = './uploads/tmp/'.$site_name.'/'.$loca_id.'/'.$samp_id;
    } else if ($status == 'COMPLETE'){
        $target_dir = './uploads/sites/'.$site_name.'/'.$loca_id.'/'.$samp_id;
    }
    if($data[0][0]!=''){
        $target_file = $target_dir.'/'.$group.'.csv';
        $output = fopen($target_file,'w') or die("Can't open $target_file");
        // Combine info with heading
        $info_comb = array(        
            array_merge(array('GROUP'),array($group)),
            array_merge(array('HEADING','LOCA_ID','SAMP_ID'),$heading),
            array_merge(array('UNIT','',''),$unit),
            array_merge(array('TYPE','ID','ID'),$type)
        );    
        // Write csv
        foreach($info_comb as $value) {
            fputcsv($output, $value);
        }
        $i = 0;
        foreach($data as $value) {
            if($filename[$i] == null){
                $value = array_merge(array('DATA',$loca_id,$samp_id),$value,array($filename_ex[$i]));
            } else {
                $value = array_merge(array('DATA',$loca_id,$samp_id),$value,array($filename[$i]));
            }            
            fputcsv($output, $value);
            $i++;
        }
        $stat = fstat($output);
        ftruncate($output, $stat['size']-1);
        fclose($output) or die("Can't close $target_file");    
        $saved_group[$j] = $title[1];
    } else {
        $target_file = $target_dir.'/'.$group.'.csv';
        unlink($target_file); // Delete AGS file for no data input
    }
    //////////////////////////////////////////////////////////////
    // Update SAMP
    // Check
    $prep_stmt = "UPDATE SAMP SET checked='Y' WHERE user_id='$user_id' AND site_id='$site_id' AND loca_id='$loca_id' AND samp_id='$samp_id'";
    $result = $mysqli->query($prep_stmt);
    // Average Bulk Density, Water Content, Plasticity Index for Sample
    $LOCAL = $_SESSION['INDX'];
    $group = $LOCAL[0];
    // data from table    
    $data = filter_input(INPUT_POST, $group, FILTER_SANITIZE_STRING, FILTER_REQUIRE_ARRAY);
    $i = 0;
    foreach($data as $value){
        $density[$i] = $value[1];
        $wc[$i] = $value[4];
        $ll[$i] = $value[5];
        $pi[$i] = $value[7];
        $i++;
    }
    $avg_density = array_sum($density)/count($density);
    $avg_wc = array_sum($wc)/count($wc);
    $avg_ll = array_sum($ll)/count($ll);
    $avg_pi = array_sum($pi)/count($pi);
    // Sieve analysis
    $LOCAL = $_SESSION['GRAT'];
    $group = $LOCAL[0];
    // data from table    
    $data = filter_input(INPUT_POST, $group, FILTER_SANITIZE_STRING, FILTER_REQUIRE_ARRAY);
    if($data[0][0] != null) $sieve='Y';
    // Other test
    $LOCAL = $_SESSION['OTHR'];
    $group = $LOCAL[0];
    // data from table    
    $data = filter_input(INPUT_POST, $group, FILTER_SANITIZE_STRING, FILTER_REQUIRE_ARRAY);
    $other = $data[0][1];
    // Update SQL SAMP
    $prep_stmt = "UPDATE SAMP SET sieve='$sieve', density='$avg_density', wc='$avg_wc', ll='$avg_ll', pi='$avg_pi', other='$other' WHERE user_id='$user_id' AND site_id='$site_id' AND loca_id='$loca_id' AND samp_id='$samp_id'";
    $result = $mysqli->query($prep_stmt);
    ///////////////////////////////////////////////////////////////////////////
    // Server messagge
    $saveMsg = 'Groups <b><i>'.implode(", ",$saved_group).'</i></b> have been saved.';
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
    // Location ID
    $loca_id = $_GET['loca_id'];
    // Sample ID
    $samp_id = $_GET['samp_id'];
    // Target directory
    if($row['status'] == 'DRAFT'){
        $target_dir = './uploads/tmp/'.$site_name.'/'.$loca_id.'/'.$samp_id;
    } else if($row['status'] == 'COMPLETE'){
        $target_dir = './uploads/sites/'.$site_name.'/'.$loca_id.'/'.$samp_id;
    }    
    // Load LABF
    $target_group = "LABF";
    $target_file = $target_dir.'/'.$target_group.'.csv';
    $FILES = tmp_load_samp($target_file,$target_group,$loca_id,$samp_id);
    $_SESSION['LABF'] = $FILES;
    // Load OTHR
    $target_group = "OTHR";
    $target_file = $target_dir.'/'.$target_group.'.csv';
    $_SESSION['OTHR'] = tmp_load_samp($target_file,$target_group,$loca_id,$samp_id);
    // Set session for GROUPS
    $GROUPS = array('LABG','INDX','GRAT');
    foreach($GROUPS as $SelectedGroup){
        $target_group = $SelectedGroup;
        $target_file = $target_dir.'/'.$target_group.'.csv';
        $_SESSION[$SelectedGroup] = tmp_load_samp($target_file,$target_group,$loca_id,$samp_id);
    }
}
///////////////////////////////////////////////////////////////////////////////
// Close
if($_POST['btn-close'] != null)
{ 
    // Reload parent window
    echo "<script>window.opener.location.reload(false);</script>";
    // Close window
    echo "<script>window.close();</script>";
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
        <td width="25%">Site Information</td>
        <td width="25%" style="background:#3284BF">Location Details</td>
        <td width="25%">Event Information</td>
        <td width="25%">Ground Performance</td>        
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
        <td align="left" style="color:red"><?=$saveMsg;?></td>
    </tr>
</table>
</div>
    
<!----------------------- Search existed data ---------------------->        
<div id='upload' class='body'>
<table width="100%" align="center" border="0">
    <!--Site Name-->
    <tr>
        <td width="100px">Site Name</td>
        <td>
            <input class="readonly" type="text" name="site_name" readonly value="<?=$site_name;?>" placeholder="Associated Site Name">
        </td>
    </tr>
    <!--Location ID-->
    <tr>
        <td width="100px">Location ID</td>
        <td>
            <input class="readonly" type="text" name="loca_id" id="loca_id" readonly value="<?=$loca_id;?>" placeholder="Location ID">
        </td>
    </tr>
    <!--Sample ID-->
    <tr>
        <td width="100px">Sample ID</td>
        <td>
            <input class="readonly" type="text" name="samp_id" id="samp_id" readonly value="<?=$samp_id;?>" placeholder="Sample ID">
        </td>
    </tr>
</table>

<form method="post" id="myform" name="myform" enctype="multipart/form-data"> 
<div id="addGroup"></div>
<!--Input section load per test-->
<?php
                
if(isset($_SESSION['HDPH']))
{
    ?>    
<script>    
    ///////////////////////////////////////////////////////////////////////////
    // for LABG
    <?$SelectedGroup = 'LABG';?>
    var group = <?=json_encode($_SESSION[$SelectedGroup][0])?>;    
    var title = <?=json_encode($_SESSION[$SelectedGroup][4])?>;
    var heading = <?=json_encode($_SESSION[$SelectedGroup][5])?>;
    var comments = <?=json_encode($_SESSION[$SelectedGroup][7])?>;
    var req = <?=json_encode($_SESSION[$SelectedGroup][8])?>;
    var unit = <?=json_encode($_SESSION[$SelectedGroup][2])?>;
    var json_data = <?=json_encode($_SESSION[$SelectedGroup][6])?>;
    DataLoad(this.form,group,title,heading,comments,unit,json_data,req);
    EntryInput(this.form,group,heading,json_data);    
    ///////////////////////////////////////////////////////////////////////////
    // for LABF
    var group = <?=json_encode($FILES[0])?>;
    var tmp = <?=json_encode($status)?>;
    if(tmp == 'COMPLETE') tmp = 'sites';
    else tmp = 'tmp';
    var site = <?=json_encode($site_name)?>;
    var loca = <?=json_encode($loca_id)?>;    
    var samp = <?=json_encode($samp_id)?>;
    var group = <?=json_encode($FILES[0])?>;
    var heading = <?=json_encode($FILES[5])?>;
    var comments = <?=json_encode($FILES[7])?>;
    var files = <?=json_encode($FILES[6])?>;
    FileLoad(this.form,group,heading,comments,tmp,site,files,loca,samp);   
    ///////////////////////////////////////////////////////////////////////////
    // for INDX and GRAT
    <?$SelectedGroup = 'INDX';?>
    var group = <?=json_encode($_SESSION[$SelectedGroup][0])?>;    
    var title = <?=json_encode($_SESSION[$SelectedGroup][4])?>;
    var heading = <?=json_encode($_SESSION[$SelectedGroup][5])?>;
    var comments = <?=json_encode($_SESSION[$SelectedGroup][7])?>;
    var req = <?=json_encode($_SESSION[$SelectedGroup][8])?>;
    var unit = <?=json_encode($_SESSION[$SelectedGroup][2])?>;
    var json_data = <?=json_encode($_SESSION[$SelectedGroup][6])?>;        
    DataLoad(this.form,group,title,heading,comments,unit,json_data,req);
    EntryInput(this.form,group,heading,json_data);
    <?$SelectedGroup = 'GRAT';?>
    var group = <?=json_encode($_SESSION[$SelectedGroup][0])?>;    
    var title = <?=json_encode($_SESSION[$SelectedGroup][4])?>;
    var heading = <?=json_encode($_SESSION[$SelectedGroup][5])?>;
    var comments = <?=json_encode($_SESSION[$SelectedGroup][7])?>;
    var req = <?=json_encode($_SESSION[$SelectedGroup][8])?>;
    var unit = <?=json_encode($_SESSION[$SelectedGroup][2])?>;
    var json_data = <?=json_encode($_SESSION[$SelectedGroup][6])?>;        
    DataLoad(this.form,group,title,heading,comments,unit,json_data,req);
    EntryInput(this.form,group,heading,json_data);
    ///////////////////////////////////////////////////////////////////////////
    // for OTHR
    <?$SelectedGroup = 'OTHR';?>
    var group = <?=json_encode($_SESSION[$SelectedGroup][0])?>;    
    var title = <?=json_encode($_SESSION[$SelectedGroup][4])?>;
    var heading = <?=json_encode($_SESSION[$SelectedGroup][5])?>;
    var comments = <?=json_encode($_SESSION[$SelectedGroup][7])?>;
    var req = <?=json_encode($_SESSION[$SelectedGroup][8])?>;
    var unit = <?=json_encode($_SESSION[$SelectedGroup][2])?>;
    var json_data = <?=json_encode($_SESSION[$SelectedGroup][6])?>;
    FileLoadOTHR(this.form,group,title,heading,comments,unit,json_data,req,tmp,site,loca,samp);
    EntryInput(this.form,group,heading,json_data);
    
</script>                                                     
<!-- Save buttons    -->
<table width="100%" align="center" border="0">
    <tr>
        <td width="25%" align="left"></td>
        <td colspan=2 align="center">
            <button type="submit" name="btn-save">Save</button>
            <input class="button-large" type="button" onClick="close_confirm('<?=$saved_group?>')" value="Close">
            <input type="hidden" id="close_id" name="btn-close">
        </td>
        <td width="25%" align="right"></td>
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
<?php include_once 'includes/inner_map_HDPH.php';
} else {
?>
<!----------------------- Sign in ask ------------------------->
<center><h2>Please sign in first.</h2></center>
<?php }?>
<!---------------------End of Body --------------------------------->    
</div>
</body>
</html>