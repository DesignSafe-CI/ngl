<!-------------------- PHP & Javascript for map--------------------->
<!-- Get lat lon from Site Info, Seismograph, Photo, Pene tests, Lab tests-->
<script>
///////////////////////////////////////////////////////////////////////////////
// Beach Ball
<?php
/////////////////////////////////////////////////
// data from EVNG table
$data = $_SESSION['EVNG'][6]; 
$evt_name = $data[0][0];
$lat = $data[0][1];
$lon = $data[0][2];
$depth = $data[0][3];
$mag = $data[0][4];
$date = $data[0][5];
$note = $data[0][6];
/////////////////////////////////////////////////
// data from EVNF table for strike, dip, and rake of first fault
$data = $_SESSION['EVNF'][6];
$strike = $data[0][1];
$dip = $data[0][2];
$rake = $data[0][3];
if(strlen($lat) > 0){
?>    
// Map image from ESRI
var map = L.map('map_upload').setView([<?php echo $lat;?>, <?php echo $lon;?>], 8);
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
// Beachball Icon
var bbIcon = L.icon({
    iconUrl: 'includes/beachball.php?strike=<?php echo $strike?>&dip=<?php echo $dip?>&rake=<?php echo $rake?>',
    iconSize: [30, 30]
});
// Event marker    
var marker = L.marker([<?php echo $lat;?>, <?php echo $lon;?>], {
    icon: bbIcon,
    rotationAngle: -90
}).addTo(map);
marker.bindPopup('<?php echo $event_name;?>');
marker_bounds.push([<?=$lat;?>, <?=$lon;?>]);    
<?}?>
    
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
var marker = L.marker([<?php echo $lat;?>, <?php echo $lon;?>], {
    icon: AweIcon('PROJ')
}).addTo(map);
marker.bindPopup('<?php echo $site_name;?>');
marker_bounds.push([<?=$lat;?>, <?=$lon;?>]);        
<?}?>
    
///////////////////////////////////////////////////////////////////////////////
// Observation
<?php
/////////////////////////////////////////////////
// data from FLDO table
$data = $_SESSION['FLDO'][6];
foreach($data as $value){
    $lat = $value[2];
    $lon = $value[3];
    $s_type = $value[1];
    $note = $value[4];
    
    // Source type
    if($s_type == 'dspv') $s_type = 'Displacement Vector Map';
    else if ($s_type == 'crwt') $s_type = 'Crack Width Transect';
    else if ($s_type == 'phto') $s_type = 'Photo';
    else if ($s_type == 'ldar') $s_type = 'LiDAR Image';
    else if ($s_type == 'stim') $s_type = 'Satellite Image';
    else if ($s_type == 'geom') $s_type = 'Georeferrenced Map';
    else if ($s_type == 'fldn') $s_type = 'Field Note';
    
    if(strlen($lat) > 0){
    ?>
    var marker = L.marker([<?php echo $lat;?>, <?php echo $lon;?>], {
        icon: AweIcon('FLDO')
    });
    marker.bindPopup('<?php echo $s_type.'<br>'.$note;?>');
    markers.addLayer(marker);
    <?}
}?>
map.addLayer(markers);
/////////////////////////////////////////////////
// Fit map to bounds
var latlngbounds = new L.latLngBounds(marker_bounds);
setTimeout(function () {
    map.fitBounds(latlngbounds,{padding: [20,20]});
}, 0);    
// Lat lon input from map
 map.on('click', function(e) {
//    var cl_lat = e.latlng.lat;
//    var cl_lon = e.latlng.lng;
//    document.getElementById('cl_lat').value=cl_lat;
//    document.getElementById('cl_lon').value=cl_lon;     
//    return([cl_lat,cl_lon]);
    alert("Lat, Lon : " + e.latlng.lat + ", " + e.latlng.lng)        
});
</script>
