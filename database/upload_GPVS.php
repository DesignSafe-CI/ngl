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
    $loca_id2 = filter_input(INPUT_POST, 'loca_id2', FILTER_SANITIZE_STRING);
    $loca_id3 = filter_input(INPUT_POST, 'loca_id3', FILTER_SANITIZE_STRING);
    $prep_stmt = "SELECT * FROM PROJ WHERE site_id='$site_id' LIMIT 1";
    $result = $mysqli->query($prep_stmt);
    $row = $result->fetch_assoc();
    $site_name = $row['site_name'];
    $status = $row['status'];
    ////////////////////////////////////////////////////////////////////////////
    // Create directory
    if($status == 'DRAFT'){
        $target_dir = './uploads/tmp/'.$site_name.'/'.$loca_id;
    } else if ($status == 'COMPLETE'){
        $target_dir = './uploads/sites/'.$site_name.'/'.$loca_id;
    }
    if(is_dir($target_dir) === false){ mkdir($target_dir);}

    if($status == 'DRAFT'){
        $target_dir = './uploads/tmp/'.$site_name.'/'.$loca_id.'/FILES';
    } else if ($status == 'COMPLETE'){
        $target_dir = './uploads/sites/'.$site_name.'/'.$loca_id.'/FILES';
    }
    if(is_dir($target_dir) === false){ mkdir($target_dir);}

    ////////////////////////////////////////////////////////////////////////////
    // Save for LOCF
    $FILES = $_SESSION['LOCF'];
    $group = $FILES[0];
    if($status == 'DRAFT'){
        $target_dir = './uploads/tmp/'.$site_name.'/'.$loca_id.'/FILES';
    } else if ($status == 'COMPLETE'){
        $target_dir = './uploads/sites/'.$site_name.'/'.$loca_id.'/FILES';
    }
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
        if(is_file($target_file)) unlink($target_file);
    } else {
        for($i=0;$i<sizeof($FILES[6]);$i++){      
            if($i < sizeof($name)){            
                if($filename[$i] != null){                
                    // Delete old one
                    $target_file = $target_dir.'/'.$filename_ex[$i];
                    if($filename_ex[$i] != null) unlink($target_file);     
                }
            } else {
                $target_file = $target_dir.'/'.$FILES[6][$i][2];
                if($FILES[6][$i][2] != null) unlink($target_file);
            }
        }     
    }
    // Save files    
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
        array_merge(array('HEADING','LOCA_ID'),$heading),
        array_merge(array('UNIT',''),$unit),
        array_merge(array('TYPE','ID'),$type)
    );
    // Save AGS file
    if($status == 'DRAFT'){
        $target_dir = './uploads/tmp/'.$site_name.'/'.$loca_id;
    } else if ($status == 'COMPLETE'){
        $target_dir = './uploads/sites/'.$site_name.'/'.$loca_id;
    }
    $target_file = $target_dir.'/'.$group.'.csv';
    $output = fopen($target_file,'w') or die("Can't open $target_file");
    // Write csv
    foreach($info_comb as $value) {
        fputcsv($output, $value);
    }    
    for($i=0;$i<sizeof($name);$i++){
        if($filename[$i] == null){
            $value = array('DATA',$loca_id,$name[$i],$desc[$i],$filename_ex[$i]);
        } else {                   
            $value = array('DATA',$loca_id,$name[$i],$desc[$i],$filename[$i]);            
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
    $GROUPS = array('GPVS','GSWD','GSWV','GDHL','GCHL','GSPL');
    $j = 0;
    foreach($GROUPS as $SelectedGroup){
        $LOCAL = $_SESSION[$SelectedGroup];
        
        $group = $LOCAL[0];
        $heading = $LOCAL[1];
        $unit = $LOCAL[2];
        $type = $LOCAL[3];
        $title = $LOCAL[4];
        ////////////////////////////////////////////////////
        // data from table    
        $data = filter_input(INPUT_POST, $group, FILTER_SANITIZE_STRING, FILTER_REQUIRE_ARRAY);
        
        ////////////////////////////////////////////////////    
        // Check data
        if(sizeof($data) > 0){
            // Check empty row and remove
            $data_first = array_column($data,0);
            $idx = array_keys($data_first,'');
            for($i=0;$i<sizeof($idx);$i++) { unset($data[$idx[$i]]); }

            $target_file = $target_dir.'/'.$group.'.csv';
            $output = fopen($target_file,'w') or die("Can't open $target_file");
            if($group == 'GPVS' | $group == 'GCHL'){
                // Combine info with heading
                $info_comb = array(        
                    array_merge(array('GROUP'),array($group)),
                    array_merge(array('HEADING','LOCA_ID','LOCA_ID2','LOCA_ID3'),$heading),
                    array_merge(array('UNIT','','',''),$unit),
                    array_merge(array('TYPE','ID','ID','ID'),$type)
                );
                foreach($info_comb as $value) {
                    fputcsv($output, $value);
                }
                foreach($data as $value) {
                    $value = array_merge(array('DATA',$loca_id,$loca_id2,$loca_id3),$value);     
                    
                    fputcsv($output, $value);
                }
            } else {
                // Combine info with heading
                $info_comb = array(        
                    array_merge(array('GROUP'),array($group)),
                    array_merge(array('HEADING','LOCA_ID'),$heading),
                    array_merge(array('UNIT',''),$unit),
                    array_merge(array('TYPE','ID'),$type)
                );
                // Write csv
                foreach($info_comb as $value) {
                    fputcsv($output, $value);
                }
                foreach($data as $value) {
                    $value = array_merge(array('DATA',$loca_id),$value);
                    fputcsv($output, $value);
                }
            }            
            $stat = fstat($output);
            ftruncate($output, $stat['size']-1);
            fclose($output) or die("Can't close $target_file");
            $saved_group[$j] = $title[1];
        }
        $j++;
    }    
    // Checked on LOCA
    $prep_stmt = "UPDATE LOCA SET checked='Y' where user_id='$user_id' AND site_id='$site_id' AND (loca_id='$loca_id' OR loca_id='$loca_id2' OR loca_id='$loca_id3')";
    $result = $mysqli->query($prep_stmt);
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
    // Target directory
    if($row['status'] == 'DRAFT'){
        $target_dir = './uploads/tmp/'.$site_name.'/'.$loca_id;
    } else if($row['status'] == 'COMPLETE'){
        $target_dir = './uploads/sites/'.$site_name.'/'.$loca_id;
    }    
    // Load LOCF
    $target_group = "LOCF";
    $target_file = $target_dir.'/'.$target_group.'.csv';
    $FILES = tmp_load_loca($target_file,$target_group,$loca_id);
    $_SESSION['LOCF'] = $FILES;
    
    // Set session for GROUPS
    $target_group = 'GPVS';
    $target_file = $target_dir.'/'.$target_group.'.csv';
    $_SESSION['GPVS'] = tmp_load_loca($target_file,$target_group,$loca_id);
    
    if(file_exists($target_file)){
        $file_content = file_get_contents($target_file);    
    } else {
        $target_file = './tmp/AGS4_new.csv';
        $file_content = file_get_contents($target_file);
    }
        
    $tmp = array_map('str_getcsv', preg_split('/\r*\n+|\r+/',$file_content));
    // Set session for GROUPS
    $GROUPS = array('GSWD','GSWV','GDHL','GCHL','GSPL');
    foreach($GROUPS as $SelectedGroup){
        $target_group = $SelectedGroup;
        $target_file = $target_dir.'/'.$target_group.'.csv';
        $_SESSION[$SelectedGroup] = tmp_load_loca($target_file,$target_group,$loca_id);
    }
    // Saved group
//    print_r($tmp);
    $target_group = $tmp[4][4];
    if($target_group == 'GCHL'){
        $loca_id2 = $tmp[4][2];
        $loca_id3 = $tmp[4][3];
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
        <td align="left" style="color:red"><span id="ServerMsg"><?=$saveMsg;?></span></td>
    </tr>
</table>
</div>
    
<!----------------------- Search existed data ---------------------->        
<div id='upload' class='body'>
<form method="post" id="myform" name="myform" enctype="multipart/form-data"> 
<table id="headTable" width="100%" align="center" border="0">
    <!--Site Name-->
    <tr>
        <td width="100px">Site Name</td>
        <td colspan=7>
            <input class="readonly" type="text" name="site_name" readonly value="<?=$site_name;?>" placeholder="Associated Site Name">
        </td>
    </tr>
    <!--Location ID-->
    <tr>
        <td width="100px">Location ID 1</td>
        <td>
            <input class="readonly" type="text" name="loca_id" id="loca_id" readonly value="<?=$loca_id;?>" placeholder="Location ID">
        </td>        
        <td width="100px">Location ID 2</td>
        <td>
            <input class="readonly" type="text" name="loca_id2" id="loca_id2" readonly value="<?=$loca_id2;?>" placeholder="Location ID 2">
        </td>
        <td>
            <select id="sel_loca_id2" onchange="SelLocaId('sel_loca_id2')" style="width:100%">
            <option value="<?=$loca_id2?>"><?=$loca_id2?></option>
            <?php
            $user_id = $_SESSION['user_id'];
            $site_id = $_SESSION['site_id'];
            $prep_stmt = "SELECT * FROM LOCA WHERE user_id='$user_id' AND site_id='$site_id'";
            $result = $mysqli->query($prep_stmt);
            while($row = $result->fetch_assoc()){
                if($row['loca_id'] != $loca_id2){
                ?>
                <option value="<?=$row['loca_id'];?>"><?=$row['loca_id'];?></option>
                <?}
            }?>
            </select>     
        </td>
        <td width="100px">Location ID 3</td>
        <td>
            <input class="readonly" type="text" name="loca_id3" id="loca_id3" readonly value="<?=$loca_id3;?>" placeholder="Location ID 3">
        </td>
        <td>
            <select id="sel_loca_id3" onchange="SelLocaId('sel_loca_id3')" style="width:100%">
            <option value="<?=$loca_id3?>"><?=$loca_id3?></option>
            <?php
            $user_id = $_SESSION['user_id'];
            $site_id = $_SESSION['site_id'];
            $prep_stmt = "SELECT * FROM LOCA WHERE user_id='$user_id' AND site_id='$site_id'";
            $result = $mysqli->query($prep_stmt);
            while($row = $result->fetch_assoc()){
                if($row['loca_id'] != $loca_id3){
                ?>
                <option value="<?=$row['loca_id'];?>"><?=$row['loca_id'];?></option>
                <?}
            }?>
            </select>     
        </td>
    </tr>
</table>

<div id="addGroup"></div>
<!--Input section load per test-->
<?php
                
if(isset($_SESSION['GPVS']))
{
    ?>    
<script>  
    // GPVS
    var group = <?=json_encode($_SESSION['GPVS'][0])?>;    
    var title = <?=json_encode($_SESSION['GPVS'][4])?>;
    var heading = <?=json_encode($_SESSION['GPVS'][5])?>;
    var comments = <?=json_encode($_SESSION['GPVS'][7])?>;
    var req = <?=json_encode($_SESSION['GPVS'][8])?>;
    var unit = <?=json_encode($_SESSION['GPVS'][2])?>;
    var json_data = <?=json_encode($_SESSION['GPVS'][6])?>;
    DataLoad(this.form,group,title,heading,comments,unit,json_data,req);
    EntryInput(this.form,group,heading,json_data);
    // for additional files    
    var group = <?=json_encode($FILES[0])?>;
    var tmp = <?=json_encode($status)?>;
    if(tmp == 'COMPLETE') tmp = 'sites';
    else tmp = 'tmp';       
    var site = <?=json_encode($site_name)?>;
    var loca = <?=json_encode($loca_id)?>;    
    var group = <?=json_encode($FILES[0])?>;
    var heading = <?=json_encode($FILES[5])?>;
    var comments = <?=json_encode($FILES[7])?>;
    var files = <?=json_encode($FILES[6])?>;
    FileLoad(this.form,group,heading,comments,tmp,site,files,loca);
    // Saved group    
    var group = <?=json_encode($_SESSION[$target_group][0])?>;
    if(group != null){
        var title = <?=json_encode($_SESSION[$target_group][4])?>;
        var heading = <?=json_encode($_SESSION[$target_group][5])?>;
        var comments = <?=json_encode($_SESSION[$target_group][7])?>;
        var req = <?=json_encode($_SESSION[$target_group][8])?>;
        var unit = <?=json_encode($_SESSION[$target_group][2])?>;
        var json_data = <?=json_encode($_SESSION[$target_group][6])?>;
        DataLoad(this.form,group,title,heading,comments,unit,json_data,req);
        EntryInput(this.form,group,heading,json_data);  
        if(group == 'GSWD'){
            var group = 'GSWV';
            var title = <?=json_encode($_SESSION['GSWV'][4])?>;
            var heading = <?=json_encode($_SESSION['GSWV'][5])?>;
            var comments = <?=json_encode($_SESSION['GSWV'][7])?>;
            var req = <?=json_encode($_SESSION['GSWV'][8])?>;
            var unit = <?=json_encode($_SESSION['GSWV'][2])?>;
            var json_data = <?=json_encode($_SESSION['GSWV'][6])?>;
            DataLoad(this.form,group,title,heading,comments,unit,json_data,req);
            EntryInput(this.form,group,heading,json_data);  
        }
    }
    
    function LoadSubGPVS(){        
        var sel_group = document.getElementById('GPVS[0][0]').value;
        // delete table for previous group        
        if(document.getElementById('type_old').value != ''){
            // Main table
            var sel_group_old = document.getElementById('type_old').value;
            var tbl = document.getElementById(sel_group_old+'_title_table');
            tbl.parentNode.removeChild(tbl);
            var tbl = document.getElementById(sel_group_old+'_entry_table');
            tbl.parentNode.removeChild(tbl);            
            var tbl = document.getElementById(sel_group_old+'_entrynum_table');
            tbl.parentNode.removeChild(tbl);
            if(sel_group_old == 'GSWD'){
                // Main table
                var sel_group_old = 'GSWV';
                var tbl = document.getElementById(sel_group_old+'_title_table');
                tbl.parentNode.removeChild(tbl);
                var tbl = document.getElementById(sel_group_old+'_entry_table');
                tbl.parentNode.removeChild(tbl);                
                var tbl = document.getElementById(sel_group_old+'_entrynum_table');
                tbl.parentNode.removeChild(tbl);
            }                
        }
        <?foreach($GROUPS as $SelectedGroup){?>
            var group = <?=json_encode($_SESSION[$SelectedGroup][0])?>;
            if(sel_group == group){   
                var title = <?=json_encode($_SESSION[$SelectedGroup][4])?>;
                var heading = <?=json_encode($_SESSION[$SelectedGroup][5])?>;
                var comments = <?=json_encode($_SESSION[$SelectedGroup][7])?>;
                var req = <?=json_encode($_SESSION[$SelectedGroup][8])?>;
                var unit = <?=json_encode($_SESSION[$SelectedGroup][2])?>;
                var json_data = <?=json_encode($_SESSION[$SelectedGroup][6])?>;
                DataLoad(this.form,group,title,heading,comments,unit,json_data,req);
                EntryInput(this.form,group,heading,json_data);  
            }
            if(sel_group == 'GSWD' & group == 'GSWV'){
                var title = <?=json_encode($_SESSION[$SelectedGroup][4])?>;
                var heading = <?=json_encode($_SESSION[$SelectedGroup][5])?>;
                var comments = <?=json_encode($_SESSION[$SelectedGroup][7])?>;
                var req = <?=json_encode($_SESSION[$SelectedGroup][8])?>;
                var unit = <?=json_encode($_SESSION[$SelectedGroup][2])?>;
                var json_data = <?=json_encode($_SESSION[$SelectedGroup][6])?>;
                DataLoad(this.form,group,title,heading,comments,unit,json_data,req);
                EntryInput(this.form,group,heading,json_data);  
            }        
        <?}?>
        document.getElementById('type_old').value = sel_group;             
    }
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