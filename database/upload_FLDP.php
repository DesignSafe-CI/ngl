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
    // Site name
    $site_id = $_SESSION['site_id'];
    $prep_stmt = "SELECT * FROM PROJ WHERE site_id='$site_id' LIMIT 1";
    $result = $mysqli->query($prep_stmt);
    $row = $result->fetch_assoc();
    $site_name = $row['site_name'];
    $site_lat = $row['lat'];
    $site_lon = $row['lon'];
    $status = $row['status'];
    if($status == 'DRAFT'){
        $target_dir = './uploads/tmp/'.$site_name;
    } else if ($status == 'COMPLETE'){
        $target_dir = './uploads/sites/'.$site_name;
    }
    
    // Event name
    $evt_id = $_SESSION['evt_id'];
    $prep_stmt = "SELECT * FROM EVNG WHERE evt_id='$evt_id' LIMIT 1";
    $result = $mysqli->query($prep_stmt);
    $row = $result->fetch_assoc();
    $evt_name = $row['evt_name'];
    $target_dir = $target_dir.'/'.$evt_name;
    
    ////////////////////////////////////////////////////////////////////////////
    // Save for SQL FLDO
    
    // Delete rows
    $data = filter_input(INPUT_POST, 'FLDO', FILTER_SANITIZE_STRING, FILTER_REQUIRE_ARRAY);
    $data_file = filter_input(INPUT_POST, 'FLDO_add_file_ex', FILTER_SANITIZE_STRING, FILTER_REQUIRE_ARRAY);
    $data_length = sizeof($data);    
    $prep_stmt = "SELECT * FROM FLDO WHERE user_id='$user_id' AND site_id='$site_id' AND evt_id='$evt_id'";
    $result = $mysqli->query($prep_stmt);    
    $i = 0;
    while($row=$result->fetch_assoc()){
        $id[$i] = $row['id'];
        if($i >= $data_length){
            $mysqli->query("DELETE FROM FLDO WHERE user_id='$user_id' AND site_id='$site_id' AND evt_id='$evt_id' AND id='$id[$i]' LIMIT 1");
        }
        $i++;
    }
    
    // Update and Save
    for($i=0;$i<$data_length;$i++){        
        $value = $data[$i];
        $obs = null;
        $mdsp = null; $ldfm = null; $sttl = null; $sndb = null; $pedf = null;
        foreach($value[0] as $sub_value){            
            if($obs == null) $obs = $sub_value;
            else $obs = $obs.'_'.$sub_value;                
            if($sub_value == 'mdsp') $mdsp = 'Yes';
            else if($sub_value == 'ldfm') $ldfm = 'Yes';
            else if($sub_value == 'sttl') $sttl = 'Yes';
            else if($sub_value == 'sndb') $sndb = 'Yes';
            else if($sub_value == 'pedf') $pedf = 'Yes';
        }
        
        $s_type = $value[1];
        $lat = $value[2];
        if($lat == null) $lat = $site_lat;
        $lon = $value[3];
        if($lon == null) $lon = $site_lon;
        $note = $value[4];
        
        // Array for FLDO file
        $FLDO[$i] = array($obs,$s_type,$lat,$lon,$note);        
        
        $filename = $data_file[$i];
        if($id[$i] != null){
            // Update
            $prep_stmt = "SELECT id FROM FLDO WHERE user_id='$user_id' AND site_id='$site_id' AND evt_id='$evt_id' AND id='$id[$i]'";
            $result = $mysqli->query($prep_stmt);
            if($result -> num_rows > 0)
            {
                $mysqli->query("UPDATE FLDO SET mdsp='$mdsp', ldfm='$ldfm', sttl='$sttl', sndb='$sndb', pedf='$pedf', s_type='$s_type', lat='$lat', lon='$lon', note='$note', filename='$filename' WHERE user_id='$user_id' AND site_id='$site_id' AND evt_id='$evt_id' AND id='$id[$i]'");
            }
        } else {
            $mysqli->query("INSERT INTO FLDO(user_id,site_id,evt_id,lat,lon,mdsp,ldfm,sttl,sndb,pedf,s_type,note,filename) VALUES('$user_id','$site_id','$evt_id','$lat','$lon','$mdsp','$ldfm','$sttl','$sndb','$pedf','$s_type','$note','$filename')");
            
            $prep_stmt = "SELECT id FROM FLDO WHERE user_id='$user_id' AND site_id='$site_id' AND evt_id='$evt_id' AND lat='$lat' AND lon='$lon' AND s_type='$s_type' AND note='$note' LIMIT 1";
            
            $result = $mysqli->query($prep_stmt);    
            $row=$result->fetch_assoc();
            $id[$i] = $row['id'];        
        }
    }

    ////////////////////////////////////////////////////////////////////////////
    // Save for SQL GRMN
    $data = filter_input(INPUT_POST, 'GRMN', FILTER_SANITIZE_STRING, FILTER_REQUIRE_ARRAY);
    foreach($data as $value){        
        if($value[1] == 'PGA'){
            $pga = $value[0];
        } else if ($value[1] == 'PGV'){
            $pgv = $value[0];
        } else if ($value[1] == 'PSAT02'){
            $sa02 = $value[0];
        } else if ($value[1] == 'PSAT10'){
            $sa10 = $value[0];
        } else if ($value[1] == 'PSAT30'){
            $sa30 = $value[0];  
        } else if ($value[1] == 'CAV5'){
            $cav5 = $value[0]; 
        } else if ($value[1] == 'D595'){
            $d595 = $value[0];
        }        
    }
    
    $rec = $data[0][2];
    $prep_stmt = "SELECT * FROM GRMN WHERE user_id='$user_id' AND site_id='$site_id' AND evt_id='$evt_id'";
    $result = $mysqli->query($prep_stmt);
    // Update
    if($result -> num_rows > 0)
    {
        $mysqli->query("UPDATE GRMN SET pga='$pga', pgv='$pgv', sa02='$sa02',sa10='$sa10', sa30='$sa30', cav5='$cav5', d595='$d595', rec='$rec' WHERE user_id='$user_id' AND site_id='$site_id' AND evt_id='$evt_id'");
    } else {
        // NEW
        $mysqli->query("INSERT INTO GRMN(user_id,site_id,evt_id,pga,pgv,sa02,sa10,sa30,cav5,d595,rec) VALUES('$user_id','$site_id','$evt_id','$pga','$pgv','$sa02','$sa10','$sa30','$cav5','$d595','$rec')");
    }

    ////////////////////////////////////////////////////////////////////////////
    // Save for FLDF and GRMF: Associate files (GRMF)
    $GROUPS = array('GRMF');
    foreach($GROUPS as $s_group){
        $FILES = $_SESSION[$s_group];
        $group = $FILES[0];
        $target_dir_file = $target_dir.'/FILES';
        if(is_dir($target_dir_file) === false){ mkdir($target_dir_file);}
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
                    $target_file = $target_dir_file.'/'.$filename_ex[$i];
                    unlink($target_file);     
                }
            } else {
                $target_file = $target_dir_file.'/'.$FILES[6][$i][2];
                unlink($target_file);
            }
        }        
        // Save files    
        for($i=0;$i<sizeof($name);$i++){        
            $target_file = $target_dir_file.'/'.$filename[$i];
            move_uploaded_file($tmp_filename[$i], $target_file);        
        }    
        // Combine info with heading    
        $group = $FILES[0];
        $heading = $FILES[1];
        $unit = $FILES[2];
        $type = $FILES[3];
        $info_comb = array(        
            array_merge(array('GROUP'),array($group)),
            array_merge(array('HEADING','EVNG_ID'),$heading),
            array_merge(array('UNIT',''),$unit),
            array_merge(array('TYPE','ID'),$type)
        );

        // Save AGS file    
        $target_file = $target_dir.'/'.$group.'.csv';
        $output = fopen($target_file,'w') or die("Can't open $target_file");
        // Write csv
        foreach($info_comb as $value) {
            fputcsv($output, $value);
        }    
        for($i=0;$i<sizeof($name);$i++){
            if($filename[$i] == null){
                $value = array('DATA',$evt_id,$name[$i],$desc[$i],$filename_ex[$i]);
            } else {                   
                $value = array('DATA',$evt_id,$name[$i],$desc[$i],$filename[$i]);            
            }
            fputcsv($output, $value);
        }
        $stat = fstat($output);
        ftruncate($output, $stat['size']-1);
        fclose($output) or die("Can't close $target_file");
    }

    ////////////////////////////////////////////////////////////////////////////
    // Save groups of FLDP and GRMN (only GRMN)
    $GROUPS = array('GRMN');
    foreach($GROUPS as $s_group){
        $LOCAL = $_SESSION[$s_group];
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
            $saved_group[0] = $title[1];
        }
    }
    ////////////////////////////////////////////////////////////////////////////
    // Save for FLDO
    $LOCAL = $_SESSION['FLDO'];
    $group = $LOCAL[0];
    $heading = $LOCAL[1];
    $unit = $LOCAL[2];
    $type = $LOCAL[3];
    $title = $LOCAL[4];
    // data from table    
    $data = $FLDO;
    // File save for OTHR
    $filename_ex = filter_input(INPUT_POST, $group.'_add_file_ex', FILTER_SANITIZE_STRING, FILTER_REQUIRE_ARRAY);
    $filename = $_FILES[$group.'_add_file']["name"];            
    $tmp_filename = $_FILES[$group.'_add_file']["tmp_name"];
    // Delete files
    for($i=0;$i<sizeof($LOCAL[6]);$i++){
        if($i < sizeof($data)){            
            if($filename[$i] != null){                
                // Delete old one
                $target_file = $target_dir_file.'/'.$filename_ex[$i];
                if($filename_ex[$i] != null) unlink($target_file);
            }
        } else {
            $target_file = $target_dir_file.'/'.$LOCAL[6][$i][5];
            if(is_null($LOCAL[6][$i][5])==TRUE){
                unlink($target_file);
            }            
        }
     }        
    // Save files    
    for($i=0;$i<sizeof($data);$i++){
        $target_file = $target_dir_file.'/'.$filename[$i];
        move_uploaded_file($tmp_filename[$i], $target_file);        
    }
    
    // Save AGS file
    if($data[0][0]!=''){
        $target_file = $target_dir.'/'.$group.'.csv';
        $output = fopen($target_file,'w') or die("Can't open $target_file");
        // Combine info with heading
        $info_comb = array(        
            array_merge(array('GROUP'),array($group)),
            array_merge(array('HEADING','EVNG_ID','FLDO_ID'),$heading),
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
                $value = array_merge(array('DATA',$evt_id,$id[$i]),$value,array($filename_ex[$i]));
            } else {
                $value = array_merge(array('DATA',$evt_id,$id[$i]),$value,array($filename[$i]));
            }            
            fputcsv($output, $value);
            $i++;
        }
        $stat = fstat($output);
        ftruncate($output, $stat['size']-1);
        fclose($output) or die("Can't close $target_file");    
        $saved_group[1] = $title[1];
    }
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
    // Event name
    $evt_id = $_SESSION['evt_id'];
    $prep_stmt = "SELECT * FROM EVNG WHERE evt_id='$evt_id' LIMIT 1";
    $result = $mysqli->query($prep_stmt);
    $row = $result->fetch_assoc();
    $evt_name = $row['evt_name'];
    // Target directory
    if($status == 'DRAFT'){
        $target_dir = './uploads/tmp/'.$site_name.'/'.$evt_name;
    } else if($status == 'COMPLETE'){
        $target_dir = './uploads/sites/'.$site_name.'/'.$evt_name;
    }    
    // Set session for GROUPS
    $GROUPS = array('FLDP','FLDF','FLDO','GRMN','GRMF');
    foreach($GROUPS as $SelectedGroup){
        $target_group = $SelectedGroup;
        $target_file = $target_dir.'/'.$target_group.'.csv';
        $_SESSION[$SelectedGroup] = tmp_load($target_file,$target_group);
    }
}
///////////////////////////////////////////////////////////////////////////////
// Previous 
if(isset($_POST['btn-previous']))
{
    header("Location: ./upload_EVNG.php");
}
///////////////////////////////////////////////////////////////////////////////
// Next 
if($_POST['btn-next'] != null)
{
    if($_SESSION['FLDO'][6][0][0] == null){
        $saveMsg = "Please <b>SAVE</b> first.";
    } else {
        header("Location: ./upload_complete.php");
    }    
}

?>
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta content="text/html; charset=utf-8" http-equiv="Content-Type" />
    <title>NGL</title>
    <!--Style-->
    <link rel="stylesheet" href="css/NGL.css" type="text/css" />
    <link rel="stylesheet" href="//ajax.googleapis.com/ajax/libs/jqueryui/1.10.2/themes/ui-lightness/jquery-ui.css" type="text/css" />
    <link rel="stylesheet" href="css/jquery.multiselect.css" type="text/css" /> <!-- for Multiple selection -->
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
    <script src="//ajax.googleapis.com/ajax/libs/jqueryui/1.10.2/jquery-ui.min.js"></script>
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
    <script src="src/Rjbrupx.js"></script><!-- Rjb and Rrup calculation -->
    <script src="src/CB14.js"></script><!-- Campbell & Bozorgnia GMM -->
    <script src="src/jquery.multiselect.js"></script><!-- Multiple selection -->
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
    <script type="text/javascript">
        // for multiple selection
        $(function(){
            $(".multiselect").multiselect({
                header: false,
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
        <td width="33%">Event Information</td>
        <td width="33%" style="background:#3284BF">Ground Performance</td>        
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
        <td width="100px">Event Name</td>
        <td>
            <input class="readonly" type="text" name="evt_name" id="evt_name" readonly value="<?=$evt_name;?>" placeholder="Associated Event Name">
        </td>
    </tr>
</table>

<form method="post" id="myform" name="myform" enctype="multipart/form-data"> 
<div id="addGroup"></div>
<!--Input section load per test-->
<?php
                
if(isset($_SESSION['FLDO']))
{
    ?>    
<script>
    var tmp = <?=json_encode($status)?>;
    if(tmp == 'COMPLETE'){
        tmp = 'sites';
    } else {
        tmp = 'tmp';
    }   
    var site = <?=json_encode($site_name)?>;
    var evt = <?=json_encode($evt_name)?>;    
    ///////////////////////////////////////////////////////////////////////////
    // for FLDO
    <?$SelectedGroup = 'FLDO';?>
    var group = <?=json_encode($_SESSION[$SelectedGroup][0])?>;    
    var title = <?=json_encode($_SESSION[$SelectedGroup][4])?>;
    var heading = <?=json_encode($_SESSION[$SelectedGroup][5])?>;
    var comments = <?=json_encode($_SESSION[$SelectedGroup][7])?>;
    var req = <?=json_encode($_SESSION[$SelectedGroup][8])?>;
    var unit = <?=json_encode($_SESSION[$SelectedGroup][2])?>;
    var json_data = <?=json_encode($_SESSION[$SelectedGroup][6])?>;  
    FileLoadFLDO(this.form,group,title,heading,comments,unit,json_data,req,tmp,site,evt);
    
    var l = json_data.length;
    for(j=0;j<l;j++){
        if(json_data[j][1] == 'dspv' | json_data[j][1] == 'crwt' | json_data[j][1] == 'ldar' | json_data[j][1] == 'stim' | json_data[j][1] == 'geom'){
            for(i=2;i<4;i++){
                document.getElementById('FLDO[0]['+i+']').readOnly = true;
                document.getElementById('FLDO[0]['+i+']').className = 'readonly';
            }
        }
    }
    
    EntryInput(this.form,group,heading,json_data);
    
    ///////////////////////////////////////////////////////////////////////////
    // for GRMN
    <?$SelectedGroup = 'GRMN';?>
    var group = <?=json_encode($_SESSION[$SelectedGroup][0])?>;    
    var title = <?=json_encode($_SESSION[$SelectedGroup][4])?>;
    var heading = <?=json_encode($_SESSION[$SelectedGroup][5])?>;
    var comments = <?=json_encode($_SESSION[$SelectedGroup][7])?>;
    var req = <?=json_encode($_SESSION[$SelectedGroup][8])?>;
    var unit = <?=json_encode($_SESSION[$SelectedGroup][2])?>;
    var json_data = <?=json_encode($_SESSION[$SelectedGroup][6])?>;
    DataLoad(this.form,group,title,heading,comments,unit,json_data,req);
        
    // Table for GMM button
    table = document.createElement('table');        
    table.width = "100%";    
//    table.style.display = "block";
    table.style.paddingTop = 0;
    table.style.paddingBottom = 0;
    var row = table.insertRow(0);
    var cell = row.insertCell(0);
    cell.innerHTML = "<input class='button-large' id='GMM_run' type='button' value='GMM Run'>";
    cell.colSpan = 7;
    cell.style.textAlign = 'right';
    document.getElementById("addGroup").appendChild(table);
    
    EntryInput(this.form,group,heading,json_data);
    ///////////////////////////////////////////////////////////////////////////
    // for additional files, GRMF
    <?$SelectedGroup = 'GRMF';?>
    var group = <?=json_encode($_SESSION[$SelectedGroup][0])?>;
    var heading = <?=json_encode($_SESSION[$SelectedGroup][5])?>;
    var comments = <?=json_encode($_SESSION[$SelectedGroup][7])?>;
    var files = <?=json_encode($_SESSION[$SelectedGroup][6])?>;
    FileLoad(this.form,group,heading,comments,tmp,site,files,evt);
    
    ///////////////////////////////////////////////////////////////////////////    
    // For multiple select
    $(document).ready(function(){
        $("#FLDO[0][0]").multiselect();
    });
    
</script>                                                     
<!-- Save buttons    -->
<table width="100%" align="center" border="0">
    <tr>
        <td width="25%" align="left">
            <button type="submit" name="btn-previous">Back</button>
        </td>
        <td colspan=2 align="center">
            <button type="submit" name="btn-save">Save</button>
        </td>
        <td width="25%" align="right">
            <input class="button-large" type="button" onClick="save_confirm('<?=$_SESSION['FLDO'][6][0][0]?>')" value="Review">
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
<?php include_once 'includes/inner_map_FLDO.php';
} else {
?>
<!----------------------- Sign in ask ------------------------->
<center><h2>Please sign in first.</h2></center>
<?php }?>
<!---------------------End of Body --------------------------------->    
</div>
</body>
</html>