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
$_SESSION['lastpage'] = 'map.php';

function PROJ_F($result)
{
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
        
        unset($evt_name);
        unset($evt_id);
        unset($mag);
        
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
            $inst[$idx[0]] = $value[3];
        }
        
        $target_group = "FLDO";
        $FILES = tmp_load($target_file,$target_group,1);
        $obs_len = sizeof($FILES[6]);
        
//        $ii = 0;
//        foreach($FILES[6] as $value){
//            $idx = array_keys($evt_id,$value[0]);  
//        
//            $desc_fp[$idx[0]] = $value[2];            
//        }
        ///////////////////////////////////////////////////////////////////////
        ////// Popup window description 
        // General info
        $desc = "
        <table width='400px'>
            <tr><td colspan=2><h3>".$site_name."</h3></td></tr>
            <tr><td width='180px'>Latitude</td><td>$lat</td></tr>
            <tr><td>Longitude</td><td>$lon</td></tr>
            <tr><td>Elevation</td><td>".$elev."</td></tr>
            <tr><td>Surface Geology</td><td>".$geol."</td></tr>
            <tr><td colspan=2>Note: ".$note."</td></tr>";
        // Downloads
        $desc = $desc."
            <tr><td colspan=2><h4>Downloads</h4></td></tr>
            <tr><td>Data</td><td>assoc. files</td></tr>
            <tr><td valign='top'><a href='$target_file'>$site_name.csv</a></td>";        
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
            $desc = $desc."<td valign='top'>$assoc_file</td>";
        }
        $desc = $desc."</tr>";
        
        // EQ and ground motion info
        $ii = 0;
        foreach($evt_id as $id){
//            if($note[$ii] != null){
                $desc = $desc."
                <tr><td colspan=2><h4>M $mag[$ii] $evt_name[$ii]</h4></td></tr>
                <tr><td>PGA (g)</td><td>$pga[$ii]</td></tr>
                <tr><td>PGV (cm/s)</td><td>$pgv[$ii]</td></tr>
                <tr><td>Ground motion?</td><td>$inst[$ii]</td></tr>
                <tr><td># of field performance data</td><td>$obs_len</td></tr>";
                
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
                    $desc = $desc."<tr><td valign='top'>assoc. files (IM)</td>
                        <td>$assoc_file</td></tr>";
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
                    $desc = $desc."<tr><td valign='top'>assoc. files (Field Perf.)</td>
                        <td>$assoc_file</td></tr>";
                }                
//            }
            $ii++;
        }
        $desc = $desc."</table>";

        $DATA[$i] = array($lat,$lon,$desc);
        $i++;
    }
    return $DATA;
}

function LOCA_F($result)
{
    $DATA = null;
    $i = 0;
    while($row=$result->fetch_assoc()){
        $site_id = $row['site_id']; // Site id        
        $loca_id = $row['loca_id']; // Loca id        
        $lat = $row['lat'];
        $lon = $row['lon'];
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
        $desc = 
        "
        <table width='400px'>
            <tr><td colspan=2><h3>".$row['loca_id']." ($type)</h3></td></tr>
            <tr><td width='180px'>Latitude (deg)</td><td>$lat</td>
            <tr><td>Longitude (deg)</td><td>$lon</td>
            <tr><td>Elevation (m)</td><td>".$row['elev']."</td>
            <tr><td>Limit of Investigation (m)</td><td>".$row['fdepth']."</td>
            <tr><td>Activity Start Date</td><td>".$row['start']."</td>
            <tr><td>Activity End Date</td><td>".$row['end']."</td>
            <tr><td colspan=2>Note: ".$row['note']."</td>
            <tr><td colspan=2><h4>Downloads</h4></td></tr>
            <tr><td colspan=2><b>Borehole information</b></td></tr>
            <tr><td>Data</td><td>assoc. files</td></tr>
            <tr><td valign='top'><a href='$output_file'>$loca_id.csv</a> <input type='button' value='plot' onclick=bhplot(['$loca_type','$i'])><input type='hidden' id='".$loca_type."_".$i."' value='$output_file'></td>
        ";
        if($assoc_file != null){
            $desc = $desc."<td valign='top'>$assoc_file</td>";
        }
        $desc = $desc."</tr></table>";
        $DATA[$i] = array($lat,$lon,$desc,$site_id,$loca_id);
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

function SAMP_F($result,$data)
{
    $desc = 
    "<table width='400px'>
        <tr><td colspan=2><b>Lab test information</b></td></tr>
        <tr><td width='180px'>Data</td><td>assoc. files</td></tr>
    ";
    
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
        $desc = $desc.
            "<tr><td valign='top'><a href='$output_file'>$samp_id.csv</a></td>";
        if($assoc_file_samp != null){
            $desc = $desc."<td valign='top'>$assoc_file_samp</td>";                
        }
        $desc = $desc."</tr>";
    }    
    // Find description
    $i = 0;
    foreach($data as $value){
        if($value[3] == $site_id & $value[4] == $loca_id){
            $idx = $i;
        }
        $i++;
    }
    $data[$idx][2] = $data[$idx][2].$desc;
    $desc = $desc."</table>";
    return $data;
}

function EVNG_F($result)
{
    $DATA = null;
    $i = 0;
    while($row=$result->fetch_assoc()){
        $lat = $row['lat'];
        $lon = $row['lon'];
        $strike = $row['strike'];
        $dip = $row['dip'];
        $rake = $row['rake'];
        $desc = 
        "<table width='400px'>
            <tr><td colspan=2><h3>".$row['evt_name']."</h3></td></tr>
            <tr><td width='180px'>Moment Magnitude</td><td>".$row['mag']."</td>
            <tr><td>Epicenter Latitude</td><td>$lat</td>
            <tr><td>Epicenter Longitude</td><td>$lon</td>
            <tr><td>Hypocenter Depth</td><td>".$row['depth']."</td>
            <tr><td>Date (YEAR-MoDy-HrMn) </td><td>".$row['date']."</td>                    
        </table>";
        $DATA[$i] = array($lat,$lon,$desc,$strike,$dip,$rake);            
        $i++;
    }
    return $DATA;
}

function FLDO_F($result)
{
    $DATA = null;
    $i = 0;
    while($row=$result->fetch_assoc()){
        $site_name = $row['site_name']; // Site name
        $evt_name = $row['evt_name']; // event name
        $evt_id = $row['evt_id']; // event id
        $fldo_id = $row['id']; // Field performance id
        $lat = $row['lat'];
        $lon = $row['lon'];
        // Observation
        $mdsp = $row['mdsp'];   
        $ldfm = $row['ldfm'];   
        $sttl = $row['sttl'];   
        $sndb = $row['sndb'];   
        $pedf = $row['pedf'];   
        $s_type = $row['s_type'];   // Observation source type

        $assocfile = $row['file'];        
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
        $desc = 
        "<table width='400px'>
            <tr><td colspan=2><h3>by M".$row['mag']." ".$row['evt_name']."</h3></td></tr>
            <tr><td width='180px'>Latitude</td><td>$lat</td></tr>
            <tr><td>Longitude</td><td>$lon</td></tr>
            <tr><td>Observation type</td><td>$s_type</td></tr>
            <tr><td>Observations</td><td>$obs</td></tr>
            <tr><td colspan=2>Note: ".$row['note']."</td></tr>     
            <tr><td colspan=2><h4>Downloads</h4></td><tr>
            <tr><td>Data (.csv format)</td><td>assoc. files</td><tr>
            <tr><td valign='top'><a href='$output_file'>$evt_name-$fldo_id.csv</a></td>";
        if($assoc_file != null){
            $desc = $desc."<td valign='top'>$assoc_file</td>";                
        }
        $desc = $desc."</tr></table>";
        $DATA[$i] = array($lat,$lon,$desc);
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
    if($instr == 'Yes') $instrsql = "AND GRMN.rec='Measured'"; else $instrsql = "";
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
        
    // EVNG    
    $group = 'EVNG';
    $inner = innerjoin($group,'PROJ','site_id').' '.innerjoin($group,'GRMN','evt_id').' '.innerjoin($group,'FLDO','site_id');
    $prep_stmt = "
        SELECT $group.* 
        FROM $group $inner
        WHERE PROJ.status='COMPLETE' $mdspsql $ldfmsql $sttlsql $sndbsql $pedfsql $s_typesql $evtsql $Msql[0] $Msql[1] $PGAsql[0] $PGAsql[1] $PGVsql[0] $PGVsql[1] $instrsql
        GROUP BY $group.evt_id";
    $result = $mysqli->query($prep_stmt);
    if($result->num_rows > 0) $EVNG = EVNG_F($result);
    
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
    // EVNG
    $prep_stmt = "                    
                SELECT EVNG.*
                FROM EVNG
                INNER JOIN PROJ ON EVNG.site_id = PROJ.site_id
                WHERE PROJ.status='COMPLETE'
                ";
    $result = $mysqli->query($prep_stmt);
    if($result->num_rows > 0) $EVNG = EVNG_F($result);
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

    <!--Query section-->
    <table>
    <tr><td style="vertical-align:top">        
    <div id="map_query">
        <form method="post">
        <table width='240px'>
        <!-------- Field Performance -------->
        <tr>
            <td colspan=2><b>Field Performance</b></td>
        </tr>
        <tr>
            <td>
                <input type='checkbox' name='mdsp' value='mdsp' <?if($mdsp=='mdsp') echo('checked')?> > Measured Disp.
            </td>
            <td>
                <input type='checkbox' name='ldfm' value='ldfm' <?if($ldfm=='ldfm') echo('checked')?> > Lateral Def.
            </td>
        </tr>
        <tr>
            <td>
                <input type='checkbox' name='sttl' value='sttl' <?if($sttl=='sttl') echo('checked')?> > Settlement
            </td>
            <td>
                <input type='checkbox' name='sndb' value='sndb' <?if($sndb=='sndb') echo('checked')?> > Sand Boil
            </td>
        </tr>
        <tr>
            <td>
                <input type='checkbox' name='pedf' value='pedf' <?if($pedf=='pedf') echo('checked')?> > Post-event def.
            </td>
        </tr>
        <!-------- Observation Type -------->
        <tr>
            <td colspan=2><b>Observation Type</b></td>
        </tr>
        <tr>
            <td>
                <input type='radio' name='s_type' value='note' <?if($s_type=='note') echo('checked')?> > Field Note
            </td>
            <td>
                <input type='radio' name='s_type' value='fmap' <?if($s_type=='fmap') echo('checked')?> > Field Mapping
            </td>
        </tr>
        <tr>
            <td>
                <input type='radio' name='s_type' value='phto' <?if($s_type=='phto') echo('checked')?> > Recon. Photo
            </td>
            <td>
                <input type='radio' name='s_type' value='stim' <?if($s_type=='stim') echo('checked')?> > Satel. Image
            </td>            
        </tr>
        <tr>
            <td>
                <input type='radio' name='s_type' value='rprp' <?if($s_type=='rprp') echo('checked')?> > Repair Report
            </td>
            <td>                
                <input type='radio' name='s_type' value='othr' <?if($s_type=='othr') echo('checked')?> > Other
            </td>
        </tr>        
        <!-------- Observation Type -------->
        <tr>
            <td colspan=2><b>Earthquake</b></td>
        </tr>        
        <tr>
            <td>Event Name<br>
            <select name="evt_name_srch" style="width:100%">
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
            <td>Magnitude<br>
                <input type='text' name='M[0]' value='<?=$M[0]?>' style="width:40px"> - <input type='text' name='M[1]' value='<?=$M[1]?>' style="width:40px">
            </td>
        </tr>
        <tr>
            <td colspan=2><b>Ground Motion</b></td>
        </tr>            
        <tr>
            <td colspan=2>
                <input type='checkbox' name='instr' value='Yes' <?if($instr=='Yes') echo('checked')?> > Measured Ground Motion
            </td>
        </tr>
        <tr>
            <td>PGA (g)<br>
                <input type='text' name='PGA[0]' value='<?=$PGA[0]?>' style="width:40px"> - <input type='text' name='PGA[1]' value='<?=$PGA[1]?>' style="width:40px">
            </td>
            <td>PGV (cm/s)<br>
                <input type='text' name='PGV[0]' value='<?=$PGV[0]?>' style="width:40px"> - <input type='text' name='PGV[1]' value='<?=$PGV[1]?>' style="width:40px">
            </td>
        </tr>
        <tr>
            <td align="center" style="color:red" height='21px' colspan=2><span id="ServerMsg"><?=$ServerMsg;?> </span></td>
        </tr>
        <tr>
            <td colspan=2><center><button type="submit" name="btn-reset">Reset</button> <button type="submit" name="btn-filter">Submit</button></center></td>
        </tr>
        </table>
        </form>
    </div>
    </td>
        
    <td>
    <div id="map_database"></div>    
    </td></tr>
    </table>               
    <!--Script for Map-->
    <script>
        
        /////////////////////////////////////////
        // function for Icon in legend
        function iconByName(name) {
            if(name == 'EVNG'){
                var icon = "<img src='includes/beachball.php?strike=0&dip=45&rake=30' width='18' height='18'  />"
            } else {
                var icon = "<img src='css/images/legend_"+name+".png' width='12' height='18'  />"
            }
	       return icon
        }
        /////////////////////////////////////////
        // Layer function
        function panelLayer(DATA,TYPE){            
            
            //Marker cluster group
            var markers = L.markerClusterGroup({
                maxClusterRadius: 20,
                spiderfyDistanceMultiplier: 2,
                showCoverageOnHover: false
            });

            var jsonobj = {
                "type": "FeatureCollection",
                "features": []
            }
            for(i=0;i<DATA.length;i++){
                var lat = DATA[i][0];
                var lon = DATA[i][1];
                var desc = DATA[i][2];
                jsonobj.features[i] = {
                    "type": "Feature",
                    "geometry": {
                        "type": "Point",
                        "coordinates": [lon, lat]
                    },
                    "properties": {
                        "desc": desc,
                        "Type": TYPE
                    }
                }
            }

            var geoj = L.geoJson(jsonobj, {
                pointToLayer: function(feature,latlng){
                    var marker = L.marker(latlng, {
                        icon: AweIcon(feature.properties.Type)
                    });
                    // Sign in required
                    <?php if(isset($_SESSION['user'])){?>
                    var customOptions =
                    {
                        'maxHeight': '400',
                        'maxWidth': '500'
                    }                    
                    marker.bindPopup(feature.properties.desc,customOptions);
                    <?}?>
                    markers.addLayer(marker);
                    return markers
                }
            });
            
            return geoj
        }
        /////////////////////////////////////////
        // Layer function for event
        function panelLayerEvent(DATA){            
            var jsonobj = {
                "type": "FeatureCollection",
                "features": []
            }
            for(i=0;i<DATA.length;i++){
                var lat = DATA[i][0];
                var lon = DATA[i][1];
                var desc = DATA[i][2];
                var strike = DATA[i][3];
                var dip = DATA[i][4];
                var rake = DATA[i][5];
                jsonobj.features[i] = {
                    "type": "Feature",
                    "geometry": {
                        "type": "Point",
                        "coordinates": [lon, lat]
                    },
                    "properties": {
                        "desc": desc,
                        "strike": strike,
                        "dip": dip,
                        "rake": rake
                    }
                }
            }
            var geoj = L.geoJson(jsonobj, {
                pointToLayer: function(feature,latlng){
                    // Beachball Icon
                    var bbIcon = L.icon({
                        iconUrl: 'includes/beachball.php?strike='+feature.properties.strike+'&dip='+feature.properties.dip+'&rake='+feature.properties.rake,
                        iconSize: [30, 30]
                    });
                    var marker = L.marker(latlng, {
                        icon: bbIcon,
                        rotationAngle: -90
                    });
                    // Sign in required
                    <?php if(isset($_SESSION['user'])){?>
                    marker.bindPopup(feature.properties.desc);
                    <?}?>
                    return marker
                }
            });
            // Fault plane
            var polygon = L.polygon([
                [51.509, -0.08],
                [51.503, -0.06],
                [51.51, -0.047]
            ])

            return geoj
        }

        // Map image from ESRI
        var map = L.map('map_database',{
                attributionControl: false,
            }).setView([0,45], 2),
            osmLayer = new L.esri.basemapLayer('Topographic',{detectRetina: true});
        map.addLayer(osmLayer);
        // add scale
        L.control.scale().addTo(map);
        
        // Base layers
        var baseLayers = [
            {
                name: "Topographic Map",
                layer: osmLayer
            },
            {	
                name: "Terrain Map",
                layer: L.esri.basemapLayer('Terrain',{detectRetina: true})
            },
            {
                name: "Imagery Map",
                layer: L.esri.basemapLayer('Imagery',{detectRetina: true})
            }            
        ];
        
        
        // Index of layer
        var k = 0;
        /////////////////////////////////////////
        // Site information (PROJ)        
        var group = 'PROJ';
        var DATA = <?=json_encode($PROJ)?>;        
        if(DATA != null){            
            var LAYER = panelLayer(DATA,group);
            var overLayers = [];            
            overLayers[k] = 
            {
                group: "General description",
                layers: [{
                    active: true,
                    name: "Site",
                    icon: iconByName(group),
                    layer: LAYER
                }]
            };
        }
        /////////////////////////////////////////
        // Test Activity information (LOCA)        
        var kk = 0;
        var j = 0;
        <?
        foreach($LOCA as $loca){
        ?>
            var group = <?=json_encode($loca)?>;
            if(group == 'HDPH') var NAME = 'Borehole';
            else if(group == 'SCPG') var NAME = 'CPT';
            else if(group == 'TEPT') var NAME = 'Test pit';
            else if(group == 'GPVS') var NAME = 'Geophysical test (Vs)';
            var DATA = <?=json_encode($$loca)?>;
            if(DATA != null){
                // Initiate LOCA layers
                if(kk == 0){
                    k++;
                    overLayers[k] = 
                    {
                        group: "Geotechnical / Geophysical tests info",
                        layers: []
                    };
                    kk = 1;
                }
                var LAYER = panelLayer(DATA,group);
                overLayers[k].layers[j] = {
                    active: true,
                    name: NAME,
                    icon: iconByName(group),
                    layer: LAYER
                }
                j++;
            }
        <?}?> 
        /////////////////////////////////////////
        // Earthquake information (EVNG)
        var group = 'EVNG';
        var DATA = <?=json_encode($EVNG)?>;
        if(DATA != null){
            k++;
            overLayers[k] = 
            {
                group: "Event Information",
                layers: []
            };
            
            var LAYER = panelLayerEvent(DATA);        
            overLayers[k].layers[1] = 
            {
                active: true,
                name: "Event",
                icon: iconByName(group),
                layer: LAYER
            }
            // Need to include script to visualize fault polygons
            
        }
        /////////////////////////////////////////
        // Field observation (FLDO)
        var group = 'FLDO';
        var DATA = <?=json_encode($FLDO)?>;
        if(DATA != null){
            k++;
            var LAYER = panelLayer(DATA,group);  
            overLayers[k] = 
            {
                group: "Field Observation",
                layers: [{
                    active: true,
                    name: "Observation",
                    icon: iconByName(group),
                    layer: LAYER
                }]
            }
        }
        var panelLayers = new L.Control.PanelLayers(baseLayers,overLayers);
        map.addControl(panelLayers);
        
//        // Fit bounds
//        if(markers.length > 1){
//            var latlngbounds = new L.latLngBounds(markers);
//            setTimeout(function () {
//                map.fitBounds(latlngbounds,{padding: [20,20]});
//                if(lat!=""){
//                    map.setView([lat, lon]);
//                }
//            }, 0);
//        }

    </script>  

</body>

</html>
