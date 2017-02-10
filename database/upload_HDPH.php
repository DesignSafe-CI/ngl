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
    $prep_stmt = "SELECT * FROM PROJ WHERE site_id='$site_id' LIMIT 1";
    $result = $mysqli->query($prep_stmt);
    $row = $result->fetch_assoc();
    $site_name = $row['site_name'];
    $status = $row['status'];
    
    if($status == 'DRAFT'){
        $target_dir = './uploads/tmp/'.$site_name.'/'.$loca_id;
    } else if ($status == 'COMPLETE'){
        $target_dir = './uploads/sites/'.$site_name.'/'.$loca_id;
    }
    if(is_dir($target_dir) === false){ mkdir($target_dir);}
    
    ////////////////////////////////////////////////////////////////////////////
    // Save for SQL SAMP
    $data = filter_input(INPUT_POST, 'SAMP', FILTER_SANITIZE_STRING, FILTER_REQUIRE_ARRAY);
    
    // Delete rows
    $prep_stmt = "SELECT samp_id FROM SAMP WHERE site_id='$site_id' AND user_id='$user_id' AND loca_id='$loca_id'";
    $result = $mysqli->query($prep_stmt);
    $samp_id_array = array_column($data,0);
    $i = 0;
    while($row=$result->fetch_assoc()){
        $samp_id_old[$i] = $row['samp_id'];        
        $i++;
    }
    $i = 0;
    if(isset($samp_id_old)){
        foreach($samp_id_old as $value){
            if(!in_array($value,$samp_id_array)){
                $prep_stmt = "DELETE FROM SAMP WHERE site_id='$site_id' AND user_id='$user_id' AND loca_id='$loca_id' AND samp_id='$value' LIMIT 1";
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
        $samp_id = $value[0];            
        // Update
        $prep_stmt = "SELECT samp_id FROM SAMP WHERE site_id='$site_id' AND user_id='$user_id' AND loca_id='$loca_id' AND samp_id='$samp_id'";
        $result = $mysqli->query($prep_stmt);
        if($result -> num_rows > 0)
        {
            if($mysqli->query("UPDATE SAMP SET user_id='$user_id', site_id='$site_id', loca_id='$loca_id', samp_id='$samp_id' WHERE site_id='$site_id' AND user_id='$user_id' AND loca_id='$loca_id' AND samp_id='$samp_id'")) {
                $SAMP_ID_update[$i] = $samp_id;
            }
        } else {
            // NEW
            if($samp_id != ''){
                if($mysqli->query("INSERT INTO SAMP(user_id,site_id,loca_id,samp_id) VALUES('$user_id','$site_id','$loca_id','$samp_id')")) {
                    $SAMP_ID_new[$i] = $samp_id;
                }
            }            
        }
        $i++;
    }    
    ////////////////////////////////////////////////////////////////////////////
    // Save for LOCF
    $FILES = $_SESSION['LOCF'];
    $group = $FILES[0];
    
    if($status == 'DRAFT'){
        $target_dir = './uploads/tmp/'.$site_name.'/'.$loca_id.'/FILES';
    } else if ($status == 'COMPLETE'){
        $target_dir = './uploads/sites/'.$site_name.'/'.$loca_id.'/FILES';
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
        if(is_file($target_file)) unlink($target_file);
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
    ////////////////
    // Save AGS file
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
    $GROUPS = array('HDPH','GEOL','DETL','ISPT');
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

        // Check empty row and remove
        $data_first = array_column($data,0);
        $idx = array_keys($data_first,'');
        for($i=0;$i<sizeof($idx);$i++) { unset($data[$idx[$i]]); }
        ////////////////////////////////////////////////////    
        // Check data
        if($data[0][0]!=''){
            $target_file = $target_dir.'/'.$group.'.csv';
            $output = fopen($target_file,'w') or die("Can't open $target_file");
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
            $stat = fstat($output);
            ftruncate($output, $stat['size']-1);
            fclose($output) or die("Can't close $target_file");    
            $saved_group[$j] = $title[1];
        }
        $j++;
    }

    // SAMP
    $LOCAL = $_SESSION['SAMP'];
    $group = $LOCAL[0];
    $heading = $LOCAL[1];
    $unit = $LOCAL[2];
    $type = $LOCAL[3];
    $title = $LOCAL[4];
    ////////////////////////////////////////////////////
    // data from table    
    $data = filter_input(INPUT_POST, $group, FILTER_SANITIZE_STRING, FILTER_REQUIRE_ARRAY);
    
    // Check empty row and remove
    $data_first = array_column($data,0);
    $idx = array_keys($data_first,'');
    for($i=0;$i<sizeof($idx);$i++) { unset($data[$idx[$i]]); }
    
    // Do not save if SAMP ID is not unique. 
    $uniqueID = array_unique(array_column($data,0));
    if(sizeof($data) != sizeof($uniqueID)){
        $saveMsg = 'Please use unique ID for Sample ID.';
    } else {   
        ////////////////////////////////////////////////////    
        // Check data
        if($data[0][0]!=''){
            $target_file = $target_dir.'/'.$group.'.csv';
            $output = fopen($target_file,'w') or die("Can't open $target_file");
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
            $stat = fstat($output);
            ftruncate($output, $stat['size']-1);
            fclose($output) or die("Can't close $target_file");    
            $saved_group[$j] = $title[1];
        }   
    
        // Checked on LOCA
        $prep_stmt = "UPDATE LOCA SET checked='Y' where user_id='$user_id' AND site_id='$site_id' AND loca_id='$loca_id'";
        $result = $mysqli->query($prep_stmt);
        // Server messagge
        $saveMsg = 'Groups <b><i>'.implode(", ",$saved_group).'</i></b> have been saved.';
    }
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
    $GROUPS = array('HDPH','GEOL','DETL','ISPT','SAMP');
    foreach($GROUPS as $SelectedGroup){
        $target_group = $SelectedGroup;
        $target_file = $target_dir.'/'.$target_group.'.csv';
        $_SESSION[$SelectedGroup] = tmp_load_loca($target_file,$target_group,$loca_id);
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
</table>

<form method="post" id="myform" name="myform" enctype="multipart/form-data"> 
<div id="addGroup"></div>
<!--Input section load per test-->
<?php
                
if(isset($_SESSION['HDPH']))
{
    ?>    
<script>    
    // for HDPH
    <?$SelectedGroup='HDPH';?>
    var group = <?=json_encode($_SESSION[$SelectedGroup][0])?>;    
    var title = <?=json_encode($_SESSION[$SelectedGroup][4])?>;
    var heading = <?=json_encode($_SESSION[$SelectedGroup][5])?>;
    var comments = <?=json_encode($_SESSION[$SelectedGroup][7])?>;
    var req = <?=json_encode($_SESSION[$SelectedGroup][8])?>;
    var unit = <?=json_encode($_SESSION[$SelectedGroup][2])?>;
    var json_data = <?=json_encode($_SESSION[$SelectedGroup][6])?>;
    DataLoad(this.form,group,title,heading,comments,unit,json_data,req);
    EntryInput(this.form,group,heading,json_data);
    
    // for LOCF (additional files)
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
    
    // for GEOL
    <?$SelectedGroup='GEOL';?>
    var group = <?=json_encode($_SESSION[$SelectedGroup][0])?>;    
    var title = <?=json_encode($_SESSION[$SelectedGroup][4])?>;
    var heading = <?=json_encode($_SESSION[$SelectedGroup][5])?>;
    var comments = <?=json_encode($_SESSION[$SelectedGroup][7])?>;
    var req = <?=json_encode($_SESSION[$SelectedGroup][8])?>;
    var unit = <?=json_encode($_SESSION[$SelectedGroup][2])?>;
    var json_data = <?=json_encode($_SESSION[$SelectedGroup][6])?>;
    DataLoad(this.form,group,title,heading,comments,unit,json_data,req);
    EntryInput(this.form,group,heading,json_data);  
    // title width    
    document.getElementById(group+'_title_table').rows[2].cells[0].style.width = '100px';
    document.getElementById(group+'_title_table').rows[2].cells[1].style.width = '100px';
    document.getElementById(group+'_title_table').rows[2].cells[2].style.width = '100px';
    document.getElementById(group+'_title_table').rows[2].cells[3].style.width = '100px';
    document.getElementById(group+'_title_table').rows[2].cells[4].style.width = '600px';
    // entry table length
    var ent_len = document.getElementById(group+"_entry_table").rows.length;
    for(var i=0;i<ent_len;i++){
        document.getElementById(group+'_entry_table').rows[i].cells[0].style.width = '100px';
        document.getElementById(group+'_entry_table').rows[i].cells[1].style.width = '100px';
        document.getElementById(group+'_entry_table').rows[i].cells[2].style.width = '100px';
        document.getElementById(group+'_entry_table').rows[i].cells[3].style.width = '100px';
        document.getElementById(group+'_entry_table').rows[i].cells[4].style.width = '600px';
        document.getElementById(group+'['+i+'][4]').style.textAlign = "left";      
    }

    // for DETL
    <?$SelectedGroup='DETL';?>
    var group = <?=json_encode($_SESSION[$SelectedGroup][0])?>;    
    var title = <?=json_encode($_SESSION[$SelectedGroup][4])?>;
    var heading = <?=json_encode($_SESSION[$SelectedGroup][5])?>;
    var comments = <?=json_encode($_SESSION[$SelectedGroup][7])?>;
    var req = <?=json_encode($_SESSION[$SelectedGroup][8])?>;
    var unit = <?=json_encode($_SESSION[$SelectedGroup][2])?>;
    var json_data = <?=json_encode($_SESSION[$SelectedGroup][6])?>;
    DataLoad(this.form,group,title,heading,comments,unit,json_data,req);
    EntryInput(this.form,group,heading,json_data);  
    // title width    
    document.getElementById(group+'_title_table').rows[2].cells[0].style.width = '100px';
    document.getElementById(group+'_title_table').rows[2].cells[1].style.width = '900px';
    // entry table length
    var ent_len = document.getElementById(group+"_entry_table").rows.length;
    for(var i=0;i<ent_len;i++){
        document.getElementById(group+'_entry_table').rows[i].cells[0].style.width = '100px';
        document.getElementById(group+'_entry_table').rows[i].cells[1].style.width = '900px';
        document.getElementById(group+'['+i+'][1]').style.textAlign = "left";      
    }
    
    // for ISPT
    <?$SelectedGroup='ISPT';?>
    var group = <?=json_encode($_SESSION[$SelectedGroup][0])?>;    
    var title = <?=json_encode($_SESSION[$SelectedGroup][4])?>;
    var heading = <?=json_encode($_SESSION[$SelectedGroup][5])?>;
    var comments = <?=json_encode($_SESSION[$SelectedGroup][7])?>;
    var req = <?=json_encode($_SESSION[$SelectedGroup][8])?>;
    var unit = <?=json_encode($_SESSION[$SelectedGroup][2])?>;
    var json_data = <?=json_encode($_SESSION[$SelectedGroup][6])?>;
    DataLoad(this.form,group,title,heading,comments,unit,json_data,req);
    EntryInput(this.form,group,heading,json_data);  

    // for SAMP
    <?$SelectedGroup='SAMP';?>
    var group = <?=json_encode($_SESSION[$SelectedGroup][0])?>;    
    var title = <?=json_encode($_SESSION[$SelectedGroup][4])?>;
    var heading = <?=json_encode($_SESSION[$SelectedGroup][5])?>;
    var comments = <?=json_encode($_SESSION[$SelectedGroup][7])?>;
    var req = <?=json_encode($_SESSION[$SelectedGroup][8])?>;
    var unit = <?=json_encode($_SESSION[$SelectedGroup][2])?>;
    var json_data = <?=json_encode($_SESSION[$SelectedGroup][6])?>;
    DataLoad(this.form,group,title,heading,comments,unit,json_data,req);
    EntryInput(this.form,group,heading,json_data);  
    <?
    $prep_stmt = "SELECT * FROM SAMP WHERE user_id='$user_id' AND site_id='$site_id' AND loca_id='$loca_id'";
    $result = $mysqli->query($prep_stmt);
    $row_length = $result->num_rows; 
    if($result->num_rows == 0){
        ?>
        document.getElementsByClassName("button")[5].style.backgroundColor = 'gray';
        <?
    } else {
        $i = 0;
        while($row = $result->fetch_assoc()){
            $samp_id = $row['samp_id'];  
            ?>
            var samp_id = <?=json_encode($samp_id)?>;
            var row_length = <?=json_encode($row_length)?>;
            var row_checked = <?=json_encode($row['checked'])?>;
            for(j=0;j<row_length;j++){
                if(document.getElementById(group+"["+j+"][0]").value == samp_id){
                    document.getElementById(group+"["+j+"][0]").readOnly = true; 
                    document.getElementById(group+"["+j+"][0]").style.backgroundColor = 'aliceblue';
                    document.getElementById(group+"["+j+"][0]").style.borderColor = 'aliceblue';
                    if(row_checked == "Y"){
                        document.getElementById(group+"["+j+"][8]").value = 'edit';
                        document.getElementById(group+"["+j+"][9]").style.color = 'green';   
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