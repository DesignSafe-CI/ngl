<!-------------------- PHP & Javascript for map--------------------->
<!-- Get lat lon from Site Info, Seismograph, Photo, Pene tests, Lab tests-->
<script>    
    if(json_data != undefined){
        var lat = json_data[0][1];
        var lon = json_data[0][2];
    }
    // Map image from ESRI
    if(lat!=''){
        var map = L.map('map_upload').setView([lat, lon], 18);
    } else {        
        var map = L.map('map_upload');
    }
    L.esri.basemapLayer('Topographic').addTo(map);
    // add scale
    L.control.scale().addTo(map);
    // Marker group
    var markers = [];
    // Site Info
    <?php
    $user_id = $_SESSION['user_id'];    
    $prep_stmt = "SELECT * FROM PROJ WHERE user_id='$user_id'";
    $result = $mysqli->query($prep_stmt);
    if($result->num_rows == 1){
        $row = $result->fetch_assoc();
        $site_name = $row['site_name'];
        $lat = $row['lat'];
        $lon = $row['lon'];        
        $status = $row['status'];
        $popup = "$site_name<br><br>Latitude: $lat<br>Longitude: $lon";
        if($status == 'DRAFT'){?>
            map.setView([<?=$lat?>,<?=$lon?>],18);
            var marker = L.marker([<?php echo $lat;?>, <?php echo $lon;?>], {
                icon: AweIcon('PROJtmp')
            }).addTo(map);
            marker.bindPopup('<?=$popup;?>');
        <?} else if($status == 'COMPLETE'){?>
            map.setView([<?=$lat?>,<?=$lon?>],18);
            var marker = L.marker([<?php echo $lat;?>, <?php echo $lon;?>], {
                icon: AweIcon('PROJ')
            }).addTo(map);
            marker.bindPopup('<?=$popup;?>');
        <?}
    } else {
        while($row = $result->fetch_assoc()){
            $site_name = $row['site_name'];
            $lat = $row['lat'];
            $lon = $row['lon'];        
            $status = $row['status'];
            $popup = "$site_name<br><br>Latitude: $lat<br>Longitude: $lon";
            if($status == 'DRAFT'){?>
                var marker = L.marker([<?php echo $lat;?>, <?php echo $lon;?>], {
                    icon: AweIcon('PROJtmp')
                }).addTo(map);
                marker.bindPopup('<?=$popup;?>');    
                markers.push([<?php echo $lat;?>, <?php echo $lon;?>]);        
            <?} else if($status == 'COMPLETE'){?>
                var marker = L.marker([<?php echo $lat;?>, <?php echo $lon;?>], {
                    icon: AweIcon('PROJ')
                }).addTo(map);
                marker.bindPopup('<?=$popup;?>');    
                markers.push([<?php echo $lat;?>, <?php echo $lon;?>]);        
            <?}
        }
    }?>
    // Fit bounds
    if(markers.length > 1){
        var latlngbounds = new L.latLngBounds(markers);
        setTimeout(function () {
            map.fitBounds(latlngbounds,{padding: [20,20]});
            if(lat!=""){
                map.setView([lat, lon]);
            }
        }, 0);
    } 
</script>
