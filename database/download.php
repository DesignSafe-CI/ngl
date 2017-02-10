<?php
session_start();
include_once 'includes/dbconnect.php';
include_once 'includes/tmp_load.php';

if(!isset($_SESSION['user']))
{
	$signout = 'Sign In';
} else {
    $signout = 'Sign Out';
}
$_SESSION['lastpage'] = 'download.php';

function PROJ_F($result){
    $DATA = null;
    $i = 0;
    while($row=$result->fetch_assoc()){
        $site_name = $row['site_name'];
        $lat = $row['lat'];
        $lon = $row['lon'];
        $elev = $row['elev'];
        $geol = $row['geol'];
        $note = $row['note'];
        
        ///////////////////////////////////////////////////////////////////////
        ////// Aggregated File
        $target_dir = './download';
        $target_file = $target_dir.'/'.$site_name.'.csv';
        
        ////// Associated files        
        $MAPF = tmp_load($target_file,'MAPF');
        $FLDF = tmp_load($target_file,'FLDF');
        $GRMF = tmp_load($target_file,'GRMF');

        ///////////////////////////////////////////////////////////////////////
        ////// Access Event, Ground motion, Field performance information
        $target_file = './download/'.$site_name.'.csv';
        $target_group = "EVNG";
        $FILES = tmp_load($target_file,$target_group,1);
        $ii = 0;
        foreach($FILES[6] as $value){            
            if($value[0] != null){
                $evt_name[$ii] = $value[1];
                $evt_id[$ii] = $value[0];
                $mag[$ii] = $value[5];
            }
            $ii++;
        }
        $target_group = "GRMN";
        $FILES = tmp_load($target_file,$target_group,1);
        $ii = 0;
        foreach($FILES[6] as $value){
            $idx = array_keys($evt_id,$value[0]);
            if($value[2] == 'PGA'){
                $pga[$idx[0]] = $value[1];
            } else if($value[2] == 'PGV'){
                $pgv[$idx[0]] = $value[1];
            }
        }
        $target_group = "FLDP";
        $FILES = tmp_load($target_file,$target_group,1);
        $ii = 0;
        foreach($FILES[6] as $value){
            $idx = array_keys($evt_id,$value[0]);
            $inst[$idx[0]] = $value[1];
            $desc_fp[$idx[0]] = $value[2];            
        }
        ///////////////////////////////////////////////////////////////////////
        ////// Table description 
        // EQ and ground motion info
        $assoc_file_GRMF = '';
        $assoc_file_FLDF = '';
        $desc_fldp = '';
        $ii = 0;
        foreach($evt_id as $id){
            if($note[$ii] != null){
                $desc_fldp = $desc_fldp."
                <b>Field performance by M $mag[$ii] $evt_name[$ii]</b><br>
                PGA = $pga[$ii]g, PGV = $pgv[$ii] cm/s, $desc_fp[$ii]<br>";
                
                // GRMF
                $assoc_file = "";
                foreach($GRMF[6] as $value){            
                    if($value[2] != null){
                        $target_dir = './download/'.$site_name.'/'.$evt_name[$ii];
                        $file = "$target_dir/FILES/$value[2]";
                        $$assoc_file = "<a href='$file'>$value[2]</a><br>";
                    }
                }
                if($assoc_file != null){
                    $assoc_file_GRMF = assoc_file_GRMF."$assoc_file<br>";
                }
                // FLDF
                $assoc_file = "";
                foreach($FLDF[6] as $value){            
                    if($value[2] != null){
                        $target_dir = './download/'.$site_name.'/'.$evt_name[$ii];
                        $file = "$target_dir/FILES/$value[2]";
                        $$assoc_file = "<a href='$file'>$value[2]</a><br>";
                    }
                }
                if($assoc_file != null){
                    $assoc_file_FLDF = assoc_file_FLDF."$assoc_file<br>";
                }                
            }
            $ii++;
        }        
        
        // General info
        $cell1 = "<b>".$site_name."</b>";
        $cell2 = "Site information (complete data). $note<br>$desc_fldp";
        $cell3 = "<a href='$target_file'>$site_name.csv</a>";
        // MAPF
        $assoc_file = "";
        foreach($MAPF[6] as $value){            
            if($value[2] != null){
                $target_dir = './download/'.$site_name;
                $file = "$target_dir/FILES/$value[2]";
                $$assoc_file = "<a href='$file'>$value[2]</a><br>";  
            } 
        }
        if($assoc_file != null){
            $cell4 = "$assoc_file<br>$assoc_file_GRMF<br>$assoc_file_FLDF";
        }

        $DATA[$i] = array($cell1, $cell2, $cell3, $cell4);
        $i++;
    }
    return $DATA;
}

function LOCA_F($result){
    $DATA = null;
    $i = 0;
    while($row=$result->fetch_assoc()){
        $site_id = $row['site_id']; // Site id        
        $loca_id = $row['loca_id']; // Loca id        
        $note = $row['note'];
        $site_name = $row['site_name']; // Site name
        
        ///////////////////////////////////////////////////////////////////////
        ////// Aggregated File
        $target_file = './download/'.$site_name.'.csv';        
        
        ////// Activity Type
        $loca_type = $row['loca_type'];
        if($loca_type == 'HDPH'){
            $type = 'Borehole';            
        } else if($loca_type == 'SCPG'){
            $type = 'Cone Penetration Test';            
        } else if($loca_type == 'TEPT'){
            $type = 'Test Pit';            
        } else if($loca_type == 'GPVS'){
            $target_group = "GPVS";
            $FILES = tmp_load($target_file,$target_group,1);
            $GPVS_type = $FILES[6][0][3];
            if($GPVS_type == 'GSWD'){
                $type = 'Vs: Surface wave';
            } else if($GPVS_type == 'GDHL'){
                $type = 'Vs: Downhole-type';
            } else if($GPVS_type == 'GCHL'){
                $type = 'Vs: Crosshole-type';
            } else if($GPVS_type == 'GSPL'){
                $type = 'Vs: Suspension logging-type';
            }
        }
        ///////////////////////////////////////////////////////////////////////    
        ////// Associated files        
        $target_group = "LOCF";
        $FILES = tmp_load_loca($target_file,$target_group,$loca_id);
        $assoc_file = "";
        foreach($FILES[6] as $value){
            if($value[2] != null){
                $target_dir = "./download/$site_name/$loca_id";
                $file = "$target_dir/FILES/$value[2]";
                $assoc_file = "<a href='$file'>$value[2]</a><br>";  
            } 
        }
        
        ///////////////////////////////////////////////////////////////////////
        ////// Output file
        $output_file = "./download/$site_name/$loca_id/$loca_id.csv";        
        // $output_png_file will be updated. 
        $output_png_file = "./download/$site_name/$loca_id/$loca_id.png";        
                
        // Box        
        $cell1 = "<b>$site_name / $loca_id</b>";
        $cell2 = "$type. $note";
        $cell3 = "<a href='$output_file'>$loca_id.csv</a> <input type='button' value='plot' onclick=bhplot(['$loca_type','$i'])><input type='hidden' id='".$loca_type."_".$i."' value='$output_file'>";
        $cell4 = $assoc_file;
        $DATA[$i] = array($cell1,$cell2,$cell3,$cell4);
        $i++;
    }
    return $DATA;
}

// Array key search function
function search($array, $key, $value)
{
    $results = array();
    if (is_array($array)) {
        if (isset($array[$key]) && $array[$key] == $value) {
            $results[] = $array;
        }
        foreach ($array as $subarray) {
            $results = array_merge($results, search($subarray, $key, $value));
        }
    }
    return $results;
}

function SAMP_F($result,$data){
    $j = 4;
    // Lab test associated files    
    while($row=$result->fetch_assoc()){        
        // target match
        $site_name = $row['site_name']; // Site name
        $site_id = $row['site_id'];        // Site id
        $loca_id = $row['loca_id']; // Loca id
        $samp_id = $row['samp_id']; // Samp id

        ///////////////////////////////////////////////////////////////////////
        ////// Aggregated File
        $target_file = './download/'.$site_name.'.csv';

        ///////////////////////////////////////////////////////////////////////    
        ////// Associated files
        $target_group = "LABF";
        $FILES = tmp_load_samp($target_file,$target_group,$loca_id,$samp_id);
        $assoc_file_samp = "";
        foreach($FILES[6] as $value){
            if($value[2] != null){
                $target_dir = "./download/$site_name/$loca_id/$samp_id";
                $file = "$target_dir/FILES/$value[2]";
                $assoc_file_samp = "<a href='$file'>$value[2]</a><br>";  
            }
        }
        
        ///////////////////////////////////////////////////////////////////////
        ////// Output file
        $output_file = "./download/$site_name/$loca_id/$samp_id/$samp_id.csv";        
        
        // Add Lab test description        
        $cell1 = "<b>$site_name / $loca_id / $samp_id<b>";
        $cell2 = "Lab test information.";
        $cell3 = "<a href='$output_file'>$samp_id.csv</a>";
        if($assoc_file_samp != null){
            $cell4 = "$assoc_file_samp";
        }
        // Find description    
        $i = 0;
        foreach($data as $value){        
            if($value[0] == "<b>$site_name / $loca_id</b>"){
                $idx = $i;            
            }
            $i++;
        }    
        $data[$idx][$j][0] = $cell1;
        $data[$idx][$j][1] = $cell2;
        $data[$idx][$j][2] = $cell3;
        $data[$idx][$j][3] = $cell4;
        $j++;
    }
    return $data;
}

function FLDO_F($result){
    $DATA = null;
    $i = 0;
    while($row=$result->fetch_assoc()){
        $site_name = $row['site_name']; // Site name
        $evt_name = $row['evt_name']; // event name
        $evt_id = $row['evt_id']; // event id
        $fldo_id = $row['id']; // Field performance id
        // Observation
        $mdsp = $row['mdsp'];   
        $ldfm = $row['ldfm'];   
        $sttl = $row['sttl'];   
        $sndb = $row['sndb'];   
        $pedf = $row['pedf'];
        $s_type = $row['s_type'];   // Observation source type
        $xyz = $row['x'].' / '.$row['y'].' / '.$row['z'];  // displacement vector
        $note = $row['note'];   // Description
        
        ///////////////////////////////////////////////////////////////////////
        ////// Aggregated File
        $target_file = './download/'.$site_name.'.csv';
        
        // Associated files        
        if($assocfile != null){
            $target_dir = "./download/$site_name/$evt_name";
            $file = "$target_dir/FILES/$assocfile";
            $assoc_file = "<a href='$file'>$assocfile</a><br>";  
        }
        
        ///////////////////////////////////////////////////////////////////////
        ////// Output file
        $output_file = "./download/$site_name/$evt_name/$evt_name-$fldo_id.csv";        
        
        // Observation type
        if($s_type == 'note') $s_type = 'Field note';
        else if ($s_type == 'fmap') $s_type = 'Field Mapping';
        else if ($s_type == 'phto') $s_type = 'Reconnaissance photo';
        else if ($s_type == 'stim') $s_type = 'Satellite image';
        else if ($s_type == 'rprp') $s_type = 'Repair report';
        else if ($s_type == 'othr') $s_type = 'Other';
        
        // Observation features
        $obs = null;
        if($mdsp == 'Yes' & $obs == null) $obs = 'Measured displacement';
        elseif($mdsp == 'Yes' & $obs != null) $obs = $obs.' / Measured displacement';
        if($ldfm == 'Yes' & $obs == null) $obs = 'Lateral deformation';
        elseif($ldfm == 'Yes' & $obs != null) $obs = $obs.' / Lateral deformation';
        if($sttl == 'Yes' & $obs == null) $obs = 'Settlement';
        elseif($sttl == 'Yes' & $obs != null) $obs = $obs.' / Settlement';
        if($sndb == 'Yes' & $obs == null) $obs = 'Sand boil';
        elseif($sndb == 'Yes' & $obs != null) $obs = $obs.' / Sand boil';
        if($pedf == 'Yes' & $obs == null) $obs = 'Post-event deformation';
        elseif($pedf == 'Yes' & $obs != null) $obs = $obs.' / Post-event deformation';
            
        // Box        
        $cell1 = "<b>$site_name / $evt_name</b>";
        $cell2 = "Observation type: $s_type<br>Observation: $obs<br>Measured disp (x / y / z) (m): $xyz<br>Description: $note";
        $cell3 = "<a href='$output_file'>$evt_name-$fldo_id.csv</a>";
        $cell4 = $assoc_file;

        $DATA[$i] = array($cell1,$cell2,$cell3,$cell4);
        $i++;
    }
    return $DATA;
}

if(isset($_POST['btn-filter']))
{    
    $mdsp = filter_input(INPUT_POST, 'mdsp', FILTER_SANITIZE_STRING);
    $ldfm = filter_input(INPUT_POST, 'ldfm', FILTER_SANITIZE_STRING);
    $sttl = filter_input(INPUT_POST, 'sttl', FILTER_SANITIZE_STRING);
    $sndb = filter_input(INPUT_POST, 'sndb', FILTER_SANITIZE_STRING);
    $pedf = filter_input(INPUT_POST, 'pedf', FILTER_SANITIZE_STRING);
    
    $s_type = filter_input(INPUT_POST, 's_type', FILTER_SANITIZE_STRING);
    
    $evt_name_srch = filter_input(INPUT_POST, 'evt_name_srch', FILTER_SANITIZE_STRING);
    $M = filter_input(INPUT_POST, 'M', FILTER_SANITIZE_STRING, FILTER_REQUIRE_ARRAY);
    $PGA = filter_input(INPUT_POST, 'PGA', FILTER_SANITIZE_STRING, FILTER_REQUIRE_ARRAY);
    $PGV = filter_input(INPUT_POST, 'PGV', FILTER_SANITIZE_STRING, FILTER_REQUIRE_ARRAY);
    $instr = filter_input(INPUT_POST, 'instr', FILTER_SANITIZE_STRING);
    
    if($mdsp == '') $mdspsql = ""; else $mdspsql = "AND FLDO.mdsp='Yes'";
    if($ldfm == '') $ldfmpsql = ""; else $ldfmsql = "AND FLDO.ldfm='Yes'";
    if($sttl == '') $sttlpsql = ""; else $sttlsql = "AND FLDO.sttl='Yes'";
    if($sndb == '') $sndbsql = ""; else $sndbsql = "AND FLDO.sndb='Yes'";
    if($pedf == '') $pedfsql = ""; else $pedfsql = "AND FLDO.pedf='Yes'";
    if($s_type == '') $s_typesql = ""; else $s_typesql = "AND FLDO.s_type='$s_type'";
    if($PGA[0] == '') $PGAsql[0] = ""; else $PGAsql[0] = "AND GRMN.pga>='$PGA[0]'";
    if($PGA[1] == '') $PGAsql[1] = ""; else $PGAsql[1] = "AND GRMN.pga<='$PGA[1]'";
    if($PGV[0] == '') $PGVsql[0] = ""; else $PGVsql[0] = "AND GRMN.pgv>='$PGV[0]'";
    if($PGV[1] == '') $PGVsql[1] = ""; else $PGVsql[1] = "AND GRMN.pgv<='$PGV[1]'";
    if($instr == 'Yes') $instrsql = "AND GRMN.rec='Yes'"; else $instrsql = "";
    if($M[0] == '') $Msql[0] = ""; else $Msql[0] = "AND EVNG.mag>='$M[0]'";
    if($M[1] == '') $Msql[1] = ""; else $Msql[1] = "AND EVNG.mag<='$M[1]'";
    if($evt_name_srch == '') $evtsql = ""; else $evtsql = "AND EVNG.evt_name='$evt_name_srch'";
    
    // SQL Table joint
    function innerjoin($group,$target,$id){
        $innsql = "INNER JOIN $target ON $group.$id = $target.$id";
        return $innsql;
    }
    // SQL Table joint for SAMP
    function innerjoinSAMP($group,$target){
        $innsql = "INNER JOIN $target ON $group.site_id = $target.site_id AND $group.loca_id = $target.loca_id";
        return $innsql;
    }
    
    // PROJ
    $group = 'PROJ';
    $inner = innerjoin($group,'GRMN','site_id').' '.innerjoin($group,'EVNG','site_id').' '.innerjoin($group,'FLDO','site_id');
    $prep_stmt = "
        SELECT PROJ.*
        FROM PROJ $inner
        WHERE PROJ.status='COMPLETE' $mdspsql $ldfmsql $sttlsql $sndbsql $pedfsql $s_typesql $evtsql $Msql[0] $Msql[1] $PGAsql[0] $PGAsql[1] $PGVsql[0] $PGVsql[1] $instrsql 
        GROUP BY PROJ.site_id";
    $result = $mysqli->query($prep_stmt);
    if($result->num_rows > 0){
      $PROJ = PROJ_F($result);  
    } else {
      $ServerMsg = "No data encountered.";
    }

    // LOCA
    $LOCA = array('HDPH','SCPG','TEPT','GPVS');
    foreach($LOCA as $loca){
        $group = 'LOCA';
        $inner = innerjoin($group,'PROJ','site_id').' '.innerjoin($group,'GRMN','site_id').' '.innerjoin($group,'EVNG','site_id').' '.innerjoin($group,'FLDO','site_id');
        $prep_stmt = "
            SELECT $group.*, PROJ.site_name
            FROM $group $inner
            WHERE PROJ.status='COMPLETE' AND $group.loca_type='$loca' $mdspsql $ldfmsql $sttlsql $sndbsql $pedfsql $s_typesql $evtsql $Msql[0] $Msql[1] $PGAsql[0] $PGAsql[1] $PGVsql[0] $PGVsql[1] $instrsql 
            GROUP BY $group.id
            ";
        $result = $mysqli->query($prep_stmt);
        if($result->num_rows > 0) $$loca = LOCA_F($result);

        // SAMP
        $group = 'SAMP';
        $inner = innerjoin($group,'PROJ','site_id').' '.innerjoinSAMP($group,'LOCA').' '.innerjoin($group,'GRMN','site_id').' '.innerjoin($group,'EVNG','site_id').' '.innerjoin($group,'FLDO','site_id');
        $prep_stmt = "
            SELECT $group.*, PROJ.site_name 
            FROM $group $inner
            WHERE PROJ.status='COMPLETE' AND LOCA.loca_type='$loca' $mdspsql $ldfmsql $sttlsql $sndbsql $pedfsql $s_typesql $evtsql $Msql[0] $Msql[1] $PGAsql[0] $PGAsql[1] $PGVsql[0] $PGVsql[1] $instrsql 
            GROUP BY $group.id";
        $result = $mysqli->query($prep_stmt);
        if($result->num_rows > 0) $$loca = SAMP_F($result,$$loca);        
    }
           
    // FLDO
    $group = 'FLDO';
    $inner = innerjoin($group,'PROJ','site_id').' '.innerjoin($group,'GRMN','evt_id').' '.innerjoin($group,'EVNG','evt_id');
    $prep_stmt = "
        SELECT $group.*, EVNG.evt_name, EVNG.mag, PROJ.site_name
        FROM $group $inner
        WHERE PROJ.status='COMPLETE' $mdspsql $ldfmsql $sttlsql $sndbsql $pedfsql $s_typesql $evtsql $Msql[0] $Msql[1] $PGAsql[0] $PGAsql[1] $PGVsql[0] $PGVsql[1] $instrsql 
        GROUP BY $group.id";
    $result = $mysqli->query($prep_stmt);
    if($result->num_rows > 0) $FLDO = FLDO_F($result);
    
} 

if(!isset($_POST['btn-filter']))
{
    
    // PROJ
    $prep_stmt = "
    SELECT PROJ.*
    FROM PROJ 
    INNER JOIN EVNG ON PROJ.site_id = EVNG.site_id
    INNER JOIN GRMN ON EVNG.evt_id = GRMN.evt_id
    WHERE PROJ.status='COMPLETE'
    GROUP BY PROJ.site_id";
    $result = $mysqli->query($prep_stmt);
    if($result->num_rows > 0) $PROJ = PROJ_F($result);
    
    // LOCA
    $LOCA = array('HDPH','SCPG','TEPT','GPVS');            
    foreach($LOCA as $loca){
        $prep_stmt = "                    
                SELECT LOCA.*, PROJ.site_name
                FROM LOCA
                INNER JOIN PROJ ON LOCA.site_id = PROJ.site_id
                WHERE PROJ.status='COMPLETE' AND LOCA.loca_type='$loca'
                ";
        $result = $mysqli->query($prep_stmt);
        if($result->num_rows > 0) $$loca = LOCA_F($result);
        
        // SAMP
        $prep_stmt = "
            SELECT SAMP.*, PROJ.site_name 
            FROM SAMP
            INNER JOIN PROJ ON SAMP.site_id = PROJ.site_id INNER JOIN LOCA ON LOCA.site_id = SAMP.site_id AND LOCA.loca_id = SAMP.loca_id
            WHERE PROJ.status='COMPLETE' AND LOCA.loca_type='$loca'
            ";
        $result = $mysqli->query($prep_stmt);
        if($result->num_rows > 0) $$loca = SAMP_F($result,$$loca);        
    }

    // FLDO
    $prep_stmt = "                    
                SELECT FLDO.*, EVNG.evt_name, EVNG.mag, PROJ.site_name
                FROM FLDO
                INNER JOIN PROJ ON FLDO.site_id = PROJ.site_id
                INNER JOIN EVNG ON FLDO.evt_id = EVNG.evt_id
                WHERE PROJ.status='COMPLETE'
                ";
    $result = $mysqli->query($prep_stmt);
    if($result->num_rows > 0) $FLDO = FLDO_F($result);
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
    <link rel="stylesheet" href="css/ionicons-2.0.1/css/ionicons.css"><!--ionicons http://ionicons.com-->
    <link rel="stylesheet" href="css/leaflet-panel-layers.src.css" />
    <!--Javascript script-->    
	<script src="src/leaflet.js"></script>
    <script src="src/leaflet.markercluster-src.js"></script>	
    <script src="src/leaflet-omnivore.js"></script>
    <script src="src/leaflet.awesome-markers.js"></script>     
    <script src="//cdn.jsdelivr.net/leaflet.esri/1.0.0/esri-leaflet.js"></script><!-- Load Esri Leaflet from CDN -->    
    <script src="src/leaflet-panel-layers.src.js"></script>      
    <script src="src/awe_icon.js"></script>
    <!--    Plotly-->
    <script src="https://cdn.plot.ly/plotly-latest.min.js"></script>
    <script src="src/plotprofiles.js"></script>
</head>

<body>
<!--Start of header-->
<?php include_once 'includes/head.html';?>
<!--End of header-->    
<!----------------------- Start of Body ---------------------------->
<div id="container" class="home">
<?php 
if(isset($_SESSION['user'])){
?>
        
    <!--Query section-->
    <div id="table_query">
        <form method="post">         
        <table width='1000px' border=0>
<!--        <tr><td colspan=3><center><h3>Search</h3></center></td></tr>-->
        <!-------- Field Performance -------->
        <tr>            
            <td colspan=2 width='330px'><b><center>Field Performance</center></b></td>
            <td colspan=2 width='330px'><b><center>Earthquake</center></b></td>
            <td colspan=2 width='330px'><b><center>Ground Motion</center></b></td>
            
        </tr>
        <tr>            
            <td><input type='checkbox' name='mdsp' value='mdsp' <?if($mdsp=='mdsp') echo('checked')?> > Measured Disp.</td>
            <td><input type='checkbox' name='ldfm' value='ldfm' <?if($ldfm=='ldfm') echo('checked')?> > Lateral Def.</td>
            
            <td width='100px'>Event Name</td>
            <td width='170px'>
                <select name="evt_name_srch" style="width:80%">
                <option value="<?=$evt_name_srch?>">
                    <?if($evt_name_srch != null) echo $evt_name_srch;?>
                </option>
                <?php
                $user_id = $_SESSION['user_id'];
                $prep_stmt = "SELECT * FROM EVNG GROUP BY evt_name";
                $result = $mysqli->query($prep_stmt);
                while($row = $result->fetch_assoc()){
                    ?>                
                        <option value="<?=$row['evt_name'];?>"><?=$row['evt_name'];?></option>
                    <?
                }?>
                </select>            
            </td>            
            
            <td colspan=2><input type='checkbox' name='instr' value='instr' <?if($instr=='Yes') echo('checked')?> > Measured Ground Motion</td>
            
        </tr>
        <tr>
            <td><input type='checkbox' name='sttl' value='sttl' <?if($sttl=='sttl') echo('checked')?> > Settlement</td>
            <td><input type='checkbox' name='sndb' value='sndb' <?if($sndb=='sndb') echo('checked')?> > Sand Boil</td>
            
            <td>Magnitude</td>
            <td><input type='text' name='M[0]' value='<?=$M[0]?>' style="width:40px"> - <input type='text' name='M[1]' value='<?=$M[1]?>' style="width:40px"></td>
            
            <td width='100px'>PGA (g)</td>
            <td width='170px'><input type='text' name='PGA[0]' value='<?=$PGA[0]?>' style="width:40px"> - <input type='text' name='PGA[1]' value='<?=$PGA[1]?>' style="width:40px"></td>
        </tr>
        <tr>
            <td><input type='checkbox' name='pedf' value='pedf' <?if($pedf=='pedf') echo('checked')?> > Post-event def.</td>
            <td></td>
            
            <td></td>
            <td></td>
            
            <td>PGV (cm/s)</td>
            <td><input type='text' name='PGV[0]' value='<?=$PGV[0]?>' style="width:40px"> - <input type='text' name='PGV[1]' value='<?=$PGV[1]?>' style="width:40px"></td>
        </tr>        
        <tr><td align="center" style="color:red" colspan=7 height='16px'><span id="ServerMsg"><?=$ServerMsg;?></span></td></tr>
        <tr><td colspan=7><center><button type="submit" name="btn-reset">Reset</button> <button type="submit" name="btn-filter">Submit</button></center></td>
        </tr>
        </table>
        </form>
    </div>
    <br>
    <div id="table_output">
        <table width='100%' id="table_download" border=0>
            <tr><th>Name</th><th>Description</th><th>File</th><th>Assoc. Files</th></tr>        
        </table>    
    </div>
    
    <!--Script for Table-->
    <script>        
        var table = document.getElementById('table_download');        
        var k = 1;
        /////////////////////////////////////////
        // Site information (PROJ)        
        var group = 'PROJ';
        var PROJ = <?=json_encode($PROJ)?>;
        var l = PROJ.length;
        for(i=0;i<l;i++){
            row = table.insertRow(k);
            k++;
            for(j=0;j<4;j++){
                cell = row.insertCell(j);
                cell.innerHTML = PROJ[i][j];
                cell.style.verticalAlign = 'top';
                if(j==0) cell.width = '20%';
                else if(j==1) cell.width = '40%';
                else if(j==2) cell.width = '20%';
                else if(j==3) cell.width = '20%';
            }        
        
            /////////////////////////////////////////
            // Test Activity information (LOCA)        
            <?
            foreach($LOCA as $loca){
            ?>
                var group = <?=json_encode($loca)?>;
                if(group == 'HDPH') var NAME = 'Borehole';
                else if(group == 'SCPG') var NAME = 'CPT';
                else if(group == 'TEPT') var NAME = 'Test pit';
                else if(group == 'GPVS') var NAME = 'Geophysical test (Vs)';
                var LOCA = <?=json_encode($$loca)?>;
                if(LOCA == null){
                    var ll = 0;
                } else {
                    var ll = LOCA.length;
                }
                
                for(ii=0;ii<ll;ii++){
                    row = table.insertRow(k);
                    k++;
                    for(j=0;j<4;j++){                        
                        cell = row.insertCell(j);
                        cell.innerHTML = LOCA[ii][j];
                        cell.style.verticalAlign = 'top';
                        if(j==0) cell.width = '20%';
                        else if(j==1) cell.width = '40%';
                        else if(j==2) cell.width = '20%';
                        else if(j==3) cell.width = '20%';
                    }
                    var lll = LOCA[ii].length;                    
                    if(lll > 4){
                        for(iii=4;iii<lll;iii++){
                            row = table.insertRow(k);
                            k++;
                            for(j=0;j<4;j++){                        
                                cell = row.insertCell(j);
                                cell.innerHTML = LOCA[ii][iii][j];
                                cell.style.verticalAlign = 'top';
                                if(j==0) cell.width = '20%';
                                else if(j==1) cell.width = '40%';
                                else if(j==2) cell.width = '20%';
                                else if(j==3) cell.width = '20%';
                            }
                        }
                    } 
                }
            <?}?>
            /////////////////////////////////////////
            // Field observation (FLDO)
            var group = 'FLDO';
            var FLDO = <?=json_encode($FLDO)?>;
            var ll = FLDO.length;
            for(ii=0;ii<ll;ii++){
                row = table.insertRow(k);
                k++;
                for(j=0;j<4;j++){
                    cell = row.insertCell(j);
                    cell.innerHTML = FLDO[ii][j];
                    cell.style.verticalAlign = 'top';
                    if(j==0) cell.width = '20%';
                    else if(j==1) cell.width = '40%';
                    else if(j==2) cell.width = '20%';
                    else if(j==3) cell.width = '20%';
                }
            }
        }        
    </script>
<?} else {?>
<!----------------------- Sign in ask ------------------------->
<center><h2>Please sign in first.</h2></center>
<?}?>    
</div>
</body>

</html>
