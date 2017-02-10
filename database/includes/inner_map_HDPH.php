<!-------------------- PHP & Javascript for map--------------------->
<!-- Get lat lon from Site Info, Seismograph, Photo, Pene tests, Lab tests-->
<script>
    var map = L.map('map_upload');
    L.esri.basemapLayer('Topographic').addTo(map);
    // add scale
    L.control.scale().addTo(map);
    // Marker group
    var markers = [];
    // Site Info
    <?php
    // Location of the site
    $user_id = $_SESSION['user_id'];    
    $site_id = $_SESSION['site_id'];    
    $prep_stmt = "SELECT * FROM PROJ WHERE user_id='$user_id' AND site_id='$site_id'";
    $result = $mysqli->query($prep_stmt);
    $row = $result->fetch_assoc();
    $site_name = $row['site_name'];
    $lat = $row['lat'];
    $lon = $row['lon'];        
    $status = $row['status'];
    if($status == 'DRAFT'){?>
        map.setView([<?=$lat?>,<?=$lon?>],18);
        var marker = L.marker([<?php echo $lat;?>, <?php echo $lon;?>], {
            icon: AweIcon('PROJtmp')
        }).addTo(map);
        marker.bindPopup('<?=$site_name;?>');
    <?} else if($status == 'COMPLETE'){?>
        map.setView([<?=$lat?>,<?=$lon?>],18);
        var marker = L.marker([<?php echo $lat;?>, <?php echo $lon;?>], {
            icon: AweIcon('PROJ')
        }).addTo(map);
        marker.bindPopup('<?=$site_name;?>');
    <?}
    // Locations of activities
    $prep_stmt = "SELECT * FROM LOCA WHERE user_id='$user_id' AND site_id='$site_id'";
    $result = $mysqli->query($prep_stmt);
    while($row = $result->fetch_assoc()){
        $loca_id = $row['loca_id'];
        $lat = $row['lat'];
        $lon = $row['lon'];
        $type = $row['loca_type'];
        if($status == 'DRAFT'){?>
            var marker = L.marker([<?php echo $lat;?>, <?php echo $lon;?>], {
                icon: AweIcon('<?=$type?>')
            }).addTo(map);
            marker.bindPopup('<?=$loca_id;?>');    
            markers.push([<?php echo $lat;?>, <?php echo $lon;?>]);        
        <?} else if($status == 'COMPLETE'){?>
            var marker = L.marker([<?php echo $lat;?>, <?php echo $lon;?>], {
                icon: AweIcon('<?=$type?>')
            }).addTo(map);
            marker.bindPopup('<?=$loca_id;?>');    
            markers.push([<?php echo $lat;?>, <?php echo $lon;?>]);        
        <?}
    }
    // Set view to selected activity
    $loca_id = $_GET['loca_id'];
    $prep_stmt = "SELECT * FROM LOCA WHERE user_id='$user_id' AND site_id='$site_id' AND loca_id='$loca_id'";
    $result = $mysqli->query($prep_stmt);
    $row = $result->fetch_assoc();
    $lat = $row['lat'];
    $lon = $row['lon'];
    ?>
    setTimeout(function () {            
            map.setView([<?=$lat?>, <?=$lon?>]);
    }, 0);     
</script>
