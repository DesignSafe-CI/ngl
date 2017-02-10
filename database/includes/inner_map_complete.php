<!-------------------- PHP & Javascript for map--------------------->
<!-- Get lat lon from Site Info, Seismograph, Photo, Pene tests, Lab tests-->
<script>
// Map image from ESRI    
var map = L.map('map_upload');
L.esri.basemapLayer('Topographic').addTo(map);
// add scale
L.control.scale().addTo(map);

//Marker cluster group
var markers = L.markerClusterGroup({
    maxClusterRadius: 10,
    spiderfyDistanceMultiplier: 2,
    showCoverageOnHover: false
});  
// Marker for bounds
var marker_bounds = [];    

///////////////////////////////////////////////////////////////////////////////
// Site
<?php
/////////////////////////////////////////////////
// data from PROJ table
$data = $_SESSION['PROJ'][6]; 
$site_name = $data[0][0];
$lat = $data[0][1];
$lon = $data[0][2];
$note = $data[0][5];
if(strlen($lat) > 0){
?>  
    map.setView([<?=$lat?>,<?=$lon?>],18);    
    var marker = L.marker([<?=$lat;?>, <?=$lon;?>], {
        icon: AweIcon('PROJ')
    });
    marker.bindPopup('<?php echo $site_name;?>');
    markers.addLayer(marker);
    marker_bounds.push([<?=$lat;?>, <?=$lon;?>]);
<?}?>
    
///////////////////////////////////////////////////////////////////////////////
// Beach Ball
<?php
/////////////////////////////////////////////////
// data from EVNG SQL table
$user_id = $_SESSION['user_id'];
$site_id = $_SESSION['site_id'];
$prep_stmt = "SELECT * FROM EVNG WHERE user_id='$user_id' and site_id='$site_id'";
$result = $mysqli->query($prep_stmt);
while($row = $result->fetch_assoc()){
    $evt_name = $row['evt_name'];
    $mag = $row['mag'];
    $date = $row['date'];
    $lat = $row['lat'];
    $lon = $row['lon'];
    $depth = $row['depth'];
    $strike = $row['strike'];
    $dip = $row['dip'];
    $rake = $row['rake'];
    $date = $row['date'];
    $note = $row['note'];
    if(strlen($lat) > 0){
        ?>
        // Beachball Icon
        var bbIcon = L.icon({
            iconUrl: 'includes/beachball.php?strike=<?=$strike?>&dip=<?=$dip?>&rake=<?=$rake?>',
            iconSize: [30, 30]
        });
        var marker = L.marker([<?php echo $lat;?>, <?php echo $lon;?>], {
            icon: bbIcon,
            rotationAngle: -90
        }).addTo(map);
        marker.bindPopup('<?php echo $event_name;?>');
        marker_bounds.push([<?=$lat;?>, <?=$lon;?>]);
<?  }    
}?>
    

///////////////////////////////////////////////////////////////////////////////
// Location
<?php
/////////////////////////////////////////////////
// data from LOCA table
$data = $_SESSION['LOCA'][6]; 
foreach($data as $value){    
    $loca_name = $value[0];
    $lat = $value[1];
    $lon = $value[2];
    $type = $value[3];
    if(strlen($lat) > 0){
    ?>
    var marker = L.marker([<?php echo $lat;?>, <?php echo $lon;?>], {
        icon: AweIcon('<?=$type;?>')
    });
    marker.bindPopup('<?php echo $loca_name;?>');
    markers.addLayer(marker);
    <?}
}?>
      
///////////////////////////////////////////////////////////////////////////////
// Observation
<?php
/////////////////////////////////////////////////
// data from FLOB table
$data = $_SESSION['FLDO'][6];

foreach($data as $value){
    $lat = $value[2];
    $lon = $value[3];
    $note = $value[4];
    $file = $value[5];
    if(strlen($lat) > 0){
    ?>
    var marker = L.marker([<?php echo $lat;?>, <?php echo $lon;?>], {
        icon: AweIcon('FLDO')
    });
    marker.bindPopup('<?php echo $note.'<br>'.$file;?>');
    markers.addLayer(marker);
    <?}
}?>
map.addLayer(markers);
var latlngbounds = new L.latLngBounds(marker_bounds);
setTimeout(function () {
    map.fitBounds(latlngbounds,{padding: [20,20]});
}, 0);
    
</script>
