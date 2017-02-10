<?php    
///////////////////////////////////////////////////////////////////////////////
// Load file
function tmp_load($target_file,$target_group,$rn=0)
{    
    ////////////////////////////////////////////////////////////////////////////
    // Load template file
    $tmp_file = "./tmp/AGS4_template.csv";
    $file_content = file_get_contents($tmp_file);
    $tmp = array_map('str_getcsv', preg_split('/\r*\n+|\r+/',$file_content));
    $l = sizeof($tmp);
    for($i=0;$i<$l;$i++){        
        // index for GROUP
        if(in_array($target_group,$tmp[$i])){
            $group = $tmp[$i][1];
            $heading = $tmp[$i+1];
            $unit = $tmp[$i+2];
            $type = $tmp[$i+3];
            $group_desc = $tmp[$i+4];
            $title = $tmp[$i+5];
            $comment = $tmp[$i+6];
            $req = $tmp[$i+7];
            // effective length
            if($rn == 0){
                if($target_group == 'LOCA'){
                    $rn = 1;
                } else if($target_group == 'GPVS' | $target_group == 'GCHL'){
                    $rn = 4;
                } else if($target_group == 'FLDO'){
                    $rn = 3;
                } else {
                    $rn = 2;
                }
            }
            
            $eff_l = count(array_filter($heading))-$rn;
            $heading = array_slice($heading,$rn,$eff_l);
            $unit = array_slice($unit,$rn,$eff_l);
            $type = array_slice($type,$rn,$eff_l);
            $group_desc = array_slice($group_desc,$rn,$eff_l);
            $comment = array_slice($comment,$rn,$eff_l);
            $req = array_slice($req,$rn,$eff_l);
        }
    }    
    ////////////////////////////////////////////////////////////////////////////
    // Load target file
    
    // Local subset
    $local_group = $group;
    $local_heading = $heading;
    $local_unit = $unit;
    $local_type = $type;
    $local_title = $title;
    $local_desc = $group_desc;
    $local_comment = $comment;
    $local_req = $req;
    
    // Data
    if(file_exists($target_file)){
        $file_content = file_get_contents($target_file);    
    } else {
        $target_file = './tmp/AGS4_new.csv';
        $file_content = file_get_contents($target_file);
    }
    
    $tmp = array_map('str_getcsv', preg_split('/\r*\n+|\r+/',$file_content));
    $l = sizeof($tmp);
    $ind = 0;
    for($i=0;$i<$l;$i++){        
        if($group == $tmp[$i][1]){
            $idx = $i;
            $j = 0;
            while($ind == 0){
                if($tmp[$j+$i][0] == '' | $tmp[$j+$i][0] == ' '){
                    $ind = 1;
                } else {
                    $j++;
                }
            }
            $idx2 = $i+$j;
        }
    }
    $local_data = [];
    $data = array_slice($tmp,$idx,$idx2-$idx);
    // size of cols
    $lcol = sizeof($local_heading);
    $idx3 = [];
    for($i=0;$i<$lcol;$i++){
        if($data[1] != null){
            $idx3[$i] = array_search($local_heading[$i],$data[1]);
        } else {
            $idx3[$i] = null;
        }
    }        
    // size of rows
    $lrow = sizeof($data);
    for($ii=4;$ii<$lrow;$ii++){
        for($i=0;$i<$lcol;$i++){            
            // Assign value
            if($idx3[$i] != ''){
                $local_data[$ii-4][$i] = $data[$ii][$idx3[$i]];
            } else {
                $local_data[$ii-4][$i] = "";
            }
        }
    }
    $LOCAL = array($local_group,$local_heading,$local_unit,$local_type,$local_title,$local_desc,$local_data,$local_comment,$local_req);    
    return $LOCAL;
}

///////////////////////////////////////////////////////////////////////////////
// Load for target LOCA_ID
function tmp_load_loca($target_file,$target_group,$loca_id,$rn=0)
{
    ////////////////////////////////////////////////////////////////////////////
    // Load template file
    $tmp_file = "./tmp/AGS4_template.csv";
    $file_content = file_get_contents($tmp_file);
    $tmp = array_map('str_getcsv', preg_split('/\r*\n+|\r+/',$file_content));
    $l = sizeof($tmp);
    for($i=0;$i<$l;$i++){        
        // index for GROUP
        if(in_array($target_group,$tmp[$i])){
            $group = $tmp[$i][1];
            $heading = $tmp[$i+1];
            $unit = $tmp[$i+2];
            $type = $tmp[$i+3];
            $group_desc = $tmp[$i+4];
            $title = $tmp[$i+5];
            $comment = $tmp[$i+6];
            $req = $tmp[$i+7];
            // effective length
            if($rn == 0){
                if($target_group == 'GPVS' | $target_group == 'GCHL'){
                    $rn = 4;
                } else {
                    $rn = 2;
                }
            }
            $eff_l = count(array_filter($heading))-$rn;
            $heading = array_slice($heading,$rn,$eff_l);
            $unit = array_slice($unit,$rn,$eff_l);
            $type = array_slice($type,$rn,$eff_l);
            $group_desc = array_slice($group_desc,$rn,$eff_l);
            $comment = array_slice($comment,$rn,$eff_l);
            $req = array_slice($req,$rn,$eff_l);
        }
    }
    ////////////////////////////////////////////////////////////////////////////
    // Load target file    
    // Local subset
    $local_group = $group;
    $local_heading = $heading;
    $local_unit = $unit;
    $local_type = $type;
    $local_title = $title;
    $local_desc = $group_desc;
    $local_comment = $comment;  
    $local_req = $req;
    // Data
    $local_data = [];
    if(file_exists($target_file)){
        $file_content = file_get_contents($target_file);
    } else {
        $target_file = './tmp/AGS4_new.csv';
        $file_content = file_get_contents($target_file);
        $waive = 1;
    }
    
    $tmp = array_map('str_getcsv', preg_split('/\r*\n+|\r+/',$file_content));    
    // Slice array for GROUP 
    $l = sizeof($tmp);
    $ind = 0;
    for($i=0;$i<$l;$i++){        
        if($group == $tmp[$i][1]){
            $idx = $i;
            $j = 0;
            while($ind == 0){
                if($tmp[$j+$i][0] == '' | $tmp[$j+$i][0] == ' ') $ind = 1;
                else $j++;
            }
            $idx2 = $i+$j;
        }
    }
    $data = array_slice($tmp,$idx,$idx2-$idx);    
    // Slice array for LOCA_ID
    $l = sizeof($data);
    $j = 0;
    $idx = [];
    for($i=0;$i<$l;$i++){        
        if($loca_id == $data[$i][1]){
            $idx[$j] = $i;
            $j++;
        }
    }
    if($waive == 1)
    {
        $data2 = $data[4];
    } else {
        if(sizeof($idx) == 0){
            if(sizeof($data[4]==0)){
                $data2 = [];
            } else {
                $data2 = array_fill(0,sizeof($data[4]),'');
            }
        } else {
            $i = 0;
            foreach($idx as $value){            
                $data2[$i] = $data[$value];
                $i++;
            }        
        }
    }
    // size of cols
    $lcol = sizeof($local_heading);
    $idx3 = [];
    for($i=0;$i<$lcol;$i++){
        if($data[1] != "") $idx3[$i] = array_search($local_heading[$i],$data[1]);
    }        
    
    // size of rows
    if($waive == 1 | sizeof($idx) == 0) $lrow = 1;
    else $lrow = sizeof($data2);
    for($ii=0;$ii<$lrow;$ii++){
        for($i=0;$i<$lcol;$i++){            
            // Assign value
            if($idx3[$i] != '' && $waive != 1){
                $local_data[$ii][$i] = $data2[$ii][$idx3[$i]];
            } else {
                $local_data[$ii][$i] = "";
            }
        }
    }
    
    $LOCAL = array($local_group,$local_heading,$local_unit,$local_type,$local_title,$local_desc,$local_data,$local_comment,$local_req);
    return $LOCAL;
}

///////////////////////////////////////////////////////////////////////////////
// Load for target LOCA_ID
function tmp_load_locaid($target_file,$target_group,$loca_id)
{
    ////////////////////////////////////////////////////////////////////////////
    // Load template file
    $tmp_file = "./tmp/AGS4_template.csv";
    $file_content = file_get_contents($tmp_file);
    $tmp = array_map('str_getcsv', preg_split('/\r*\n+|\r+/',$file_content));
    $l = sizeof($tmp);
    for($i=0;$i<$l;$i++){        
        // index for GROUP
        if(in_array($target_group,$tmp[$i])){
            $group = $tmp[$i][1];
            $heading = $tmp[$i+1];
            $unit = $tmp[$i+2];
            $type = $tmp[$i+3];
            $group_desc = $tmp[$i+4];
            $title = $tmp[$i+5][1];
            $comment = $tmp[$i+6];
            // effective length
            if($target_group == 'GPVS' | $target_group == 'GCHL'){
                $rn = 4;
            } else {
                $rn = 2;
            }
            $eff_l = count(array_filter($heading))-$rn;
            $heading = array_slice($heading,$rn,$eff_l);
            $unit = array_slice($unit,$rn,$eff_l);
            $type = array_slice($type,$rn,$eff_l);
            $group_desc = array_slice($group_desc,$rn,$eff_l);
            $comment = array_slice($comment,$rn,$eff_l);
        }
    }
    ////////////////////////////////////////////////////////////////////////////
    // Load target file    
    // Local subset
    $local_group = $group;
    $local_heading = $heading;
    $local_unit = $unit;
    $local_type = $type;
    $local_title = $title;
    $local_desc = $group_desc;
    $local_comment = $comment;    
    // Data
    $local_data = [];
    if(file_exists($target_file)){
        $file_content = file_get_contents($target_file);    
    } else {
        $target_file = './tmp/AGS4_new.csv';
        $file_content = file_get_contents($target_file);
        $waive = 1;
    }
    $tmp = array_map('str_getcsv', preg_split('/\r*\n+|\r+/',$file_content));    
    // Slice array for GROUP 
    $l = sizeof($tmp);
    $ind = 0;
    for($i=0;$i<$l;$i++){        
        if($group == $tmp[$i][1]){
            $idx = $i;
            $j = 0;
            while($ind == 0){
                if($tmp[$j+$i][0] == '') $ind = 1;
                else $j++;
            }
            $idx2 = $i+$j;
        }
    }
    $data = array_slice($tmp,$idx,$idx2-$idx);    
    // Slice array for LOCA_ID
    $l = sizeof($data);
    $j = 0;
    $idx = [];
    for($i=0;$i<$l;$i++){        
        if($loca_id == $data[$i][1]){
            $idx[$j] = $i;
            $j++;
        }
    }
    if($waive == 1)
    {
        $data2 = $data[4];
    } else {
        if(sizeof($idx) == 0){
            $data2 = array_fill(0,sizeof($data[4]),'');
        } else {
            $i = 0;
            foreach($idx as $value){            
                $data2[$i] = $data[$value];
                $i++;
            }        
        }
    }
    // size of cols
    $lcol = sizeof($local_heading);
    $idx3 = [];
    for($i=0;$i<$lcol;$i++){
        $idx3[$i] = array_search($local_heading[$i],$data[1]);
    }        
    // size of rows
    if($waive == 1 | sizeof($idx) == 0) $lrow = 1;
    else $lrow = sizeof($data2);
    for($ii=0;$ii<$lrow;$ii++){
        for($i=0;$i<$lcol;$i++){            
            // Assign value
            if($idx3[$i] != '' && $waive != 1){
                $local_data[$ii][$i] = $data2[$ii][$idx3[$i]];
            } else {
                $local_data[$ii][$i] = "";
            }
        }
    }
    
    $LOCAL = array($local_group,$local_heading,$local_unit,$local_type,$local_title,$local_desc,$local_data,$local_comment);
    return $LOCAL;
}

///////////////////////////////////////////////////////////////////////////////
// Load for target LOCA_ID and SAMP_ID
function tmp_load_samp($target_file,$target_group,$loca_id,$samp_id)
{
    ////////////////////////////////////////////////////////////////////////////
    // Load template file
    $tmp_file = "./tmp/AGS4_template.csv";
    $file_content = file_get_contents($tmp_file);
    $tmp = array_map('str_getcsv', preg_split('/\r*\n+|\r+/',$file_content));
    $l = sizeof($tmp);
    for($i=0;$i<$l;$i++){        
        // index for GROUP
        if(in_array($target_group,$tmp[$i])){
            $group = $tmp[$i][1];
            $heading = $tmp[$i+1];
            $unit = $tmp[$i+2];
            $type = $tmp[$i+3];
            $group_desc = $tmp[$i+4];
            $title = $tmp[$i+5];
            $comment = $tmp[$i+6];
            $req = $tmp[$i+7];
            // effective length
            $rn = 3;
            $eff_l = count(array_filter($heading))-$rn;
            $heading = array_slice($heading,$rn,$eff_l);
            $unit = array_slice($unit,$rn,$eff_l);
            $type = array_slice($type,$rn,$eff_l);
            $group_desc = array_slice($group_desc,$rn,$eff_l);
            $comment = array_slice($comment,$rn,$eff_l);
            $req = array_slice($req,$rn,$eff_l);
        }
    }
    ////////////////////////////////////////////////////////////////////////////
    // Load target file    
    // Local subset
    $local_group = $group;
    $local_heading = $heading;
    $local_unit = $unit;
    $local_type = $type;
    $local_title = $title;
    $local_desc = $group_desc;
    $local_comment = $comment;
    $local_req = $req;
    // Data
    $local_data = [];
    if(file_exists($target_file)){
        $file_content = file_get_contents($target_file);    
    } else {
        $target_file = './tmp/AGS4_new.csv';
        $file_content = file_get_contents($target_file);
        $waive = 1;
    }
    
    $tmp = array_map('str_getcsv', preg_split('/\r*\n+|\r+/',$file_content));    
    // Slice array for GROUP 
    $l = sizeof($tmp);
    $ind = 0;
    for($i=0;$i<$l;$i++){        
        if($group == $tmp[$i][1]){
            $idx = $i;
            $j = 0;
            while($ind == 0){
                if($tmp[$j+$i][0] == '' | $tmp[$j+$i][0] == ' ') $ind = 1;
                else $j++;
            }
            $idx2 = $i+$j;
        }
    }
    $data = array_slice($tmp,$idx,$idx2-$idx);    
    // Slice array for LOCA_ID and SAMP_ID
    $l = sizeof($data);
    $j = 0;
    $idx = [];
    for($i=0;$i<$l;$i++){        
        if($loca_id == $data[$i][1] & $samp_id == $data[$i][2]){
            $idx[$j] = $i;
            $j++;
        }
    }
    if($waive == 1)
    {
        $data2 = $data[4];
    } else {
        if(sizeof($idx) == 0){
            if(sizeof($data[4]==0)){
                $data2 = [];
            } else {
                $data2 = array_fill(0,sizeof($data[4]),'');
            }
        } else {
            $i = 0;
            foreach($idx as $value){            
                $data2[$i] = $data[$value];
                $i++;
            }        
        }
    }
    // size of cols
    $lcol = sizeof($local_heading);
    $idx3 = [];
    for($i=0;$i<$lcol;$i++){
        if($data[1] != "") $idx3[$i] = array_search($local_heading[$i],$data[1]);
    }
            
    // size of rows
    if($waive == 1 | sizeof($idx) == 0) $lrow = 1;
    else $lrow = sizeof($data2);
    for($ii=0;$ii<$lrow;$ii++){
        for($i=0;$i<$lcol;$i++){            
            // Assign value
            if($idx3[$i] != '' && $waive != 1){
                $local_data[$ii][$i] = $data2[$ii][$idx3[$i]];
            } else {
                $local_data[$ii][$i] = "";
            }
        }
    }
    
    $LOCAL = array($local_group,$local_heading,$local_unit,$local_type,$local_title,$local_desc,$local_data,$local_comment,$local_req);
    return $LOCAL;
}

///////////////////////////////////////////////////////////////////////////////
// File load function
function file_load($target_file,$target_group)
{    
    $group = $target_group;
    ////////////////////////////////////////////////////////////////////////////
    // Load target file
    if(file_exists($target_file)){
        $file_content = file_get_contents($target_file);
    } else {
        $target_file = './tmp/AGS4_new.csv';
        $file_content = file_get_contents($target_file);
    }
    
    $tmp = array_map('str_getcsv', preg_split('/\r*\n+|\r+/',$file_content));
    $l = sizeof($tmp);
    $ind = 0;
    for($i=0;$i<$l;$i++){        
        if($group == $tmp[$i][1]){
            $idx = $i;
            $j = 0;
            while($ind == 0){
                if($tmp[$j+$i][0] == ''){
                    $ind = 1;
                } else {
                    $j++;
                }
            }
            $idx2 = $i+$j;
        }
    }
    // Local subset
    $local_group = $group;
    $local_heading = $heading;
    $local_unit = $unit;
    $local_type = $type;
    $local_title = $title;
    $local_desc = $group_desc;
    $local_heading = array("LOCA_ID","FILE_FSET","FILE_FSET2");
    
    // Data
    $local_data = [];
    $data = array_slice($tmp,$idx,$idx2-$idx);
    // size of cols
    $lcol = sizeof($local_heading);        
    $idx3 = [];
    for($i=0;$i<$lcol;$i++){
        $idx3[$i] = array_search($local_heading[$i],$data[1]);
    }

    // size of rows
    $lrow = sizeof($data);
    for($ii=4;$ii<$lrow;$ii++){
        for($i=0;$i<$lcol;$i++){            
            // Assign value
            if($idx3[$i] != ''){
                $local_data[$ii-4][$i] = $data[$ii][$idx3[$i]];
            } else {
                $local_data[$ii-4][$i] = "";
            }
        }
    }
    $FILES = array($local_heading,$local_data);    
    return $FILES;
}

///////////////////////////////////////////////////////////////////////////////
// Delete function
function Delete($path)
{
    if (is_dir($path) === true)
    {
        $files = array_diff(scandir($path), array('.', '..'));
        foreach ($files as $file)
        {
            Delete(realpath($path) . '/' . $file);
        }
        return rmdir($path);
    }
    else if (is_file($path) === true)
    {
        return unlink($path);
    }
    return false;
}

///////////////////////////////////////////////////////////////////////////////
// Array_column function
if (! function_exists('array_column')) {
    function array_column(array $input, $columnKey, $indexKey = null) {
        $array = array();
        foreach ($input as $value) {
            if ( ! isset($value[$columnKey])) {
                trigger_error("Key \"$columnKey\" does not exist in array");
                return false;
            }
            if (is_null($indexKey)) {
                $array[] = $value[$columnKey];
            }
            else {
                if ( ! isset($value[$indexKey])) {
                    trigger_error("Key \"$indexKey\" does not exist in array");
                    return false;
                }
                if ( ! is_scalar($value[$indexKey])) {
                    trigger_error("Key \"$indexKey\" does not contain scalar value");
                    return false;
                }
                $array[$value[$indexKey]] = $value[$columnKey];
            }
        }
        return $array;
    }
}
?>