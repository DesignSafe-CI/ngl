<?php
session_start();
include_once 'includes/dbconnect.php';

if(!isset($_SESSION['user']))
{
	$signout = 'Sign In';
} else {
    $signout = 'Sign Out';
}
$_SESSION['lastpage'] = 'index.php';
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
    
</head>
<body>
    <!--Start of header-->
    <?php include_once 'includes/head.html';?>
    <div id="head" class="text">
        <h1>Next-Generation Liquefaction Database</h1>
    </div>
    <!--End of header-->
    
    <div id="container" class="home">
        <h2><center>Number of Global Users</center></h2>
        
        <!-- Global Usage Map -->
        <div id="map_user"></div>    
        <script>
        // Load map        
        // Map image from ESRI
        var map = L.map('map_user').setView([0,-90], 1);
        map.options.maxZoom = 5;
        map.options.minZoom = 1;
        L.esri.basemapLayer('Topographic').addTo(map);
        // add scale
        L.control.scale().addTo(map)
        
        // Icon for marker
        function AweIcon() {
            var Icon = L.AwesomeMarkers.icon({
                icon: 'icon ion-cube',
                markerColor: 'lightgray',
                iconColor: 'black'
            });
            return Icon;        
        }
        
        //Marker cluster group
        var markers = L.markerClusterGroup({
            maxClusterRadius: 20,
            spiderfyDistanceMultiplier: 2,
            showCoverageOnHover: false
        });      
            
        // Get lat lon from country code in database
        <?php
        $prep_stmt = "SELECT country, region FROM members";
        $result = $mysqli->query($prep_stmt);
        while($row = $result->fetch_assoc()){
            $address = $row["region"].'+'.$row["country"];
            $geocode_stats = file_get_contents("http://maps.googleapis.com/maps/api/geocode/json?address=".$address."&sensor=false");
            $output = json_decode($geocode_stats);
            $latlon = $output->results[0]->geometry->location;
            $lat = $latlon->lat;
            $lon = $latlon->lng;
            ?>            
            var marker = L.marker([<?php echo $lat?>, <?php echo $lon?>], {
                icon: AweIcon()
            });
            markers.addLayer(marker);            
            <?php 
        }
        ?>  
        map.addLayer(markers);
    </script>                
    <!-- End of Global Usage Map -->
   
    <h2><center>User statistics</center></h2>
        <p>Coming soon...</p>
    </div>
    <!-- END pageBody -->
</body>
</html>