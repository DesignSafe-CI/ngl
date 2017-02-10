<!-------------------- PHP & Javascript for map--------------------->
<!-- Get lat lon from Site Info, Seismograph, Photo, Pene tests, Lab tests-->
<script>
// Site Info
<?php
////////////////////////////////////////////////////
// data from EVNG table
$data = $_SESSION['EVNG'][6]; 
$evt_name = $data[0][0];
$lat = $data[0][1];
$lon = $data[0][2];
$depth = $data[0][3];
$mag = $data[0][4];
$date = $data[0][5];
$note = $data[0][6];
////////////////////////////////////////////////////
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
// Marker group
var markers = [];
// Beachball Icon
var bbIcon = L.icon({
    iconUrl: 'includes/beachball.php?strike=<?php echo $strike?>&dip=<?php echo $dip?>&rake=<?php echo $rake?>',
    iconSize: [30, 30]
});
var marker = L.marker([<?php echo $lat;?>, <?php echo $lon;?>], {
    icon: bbIcon
}).addTo(map);
marker.bindPopup('<?php echo $event_name;?>');
<?}?>
</script>
