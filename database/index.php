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
    <script src="src/awe_icon.js"></script>    
</head>
<body>
    <!--Start of header-->
    <?php include_once 'includes/head.html';?>
    <div id="head" class="text">
        <h1>Next-Generation Liquefaction Database</h1>
    </div>
    <!--End of header-->
    
    <div id="container" class="home" style="min-height:700px">
        <h2><center>Number of Sites</center></h2>
        
        <!-- Number of sites -->
        <div id="map_user"></div>    
        <script>
        // Load map        
        // Map image from ESRI
        var map = L.map('map_user').setView([0,-90], 1);
        L.esri.basemapLayer('Topographic').addTo(map);
        // add scale
        L.control.scale().addTo(map)
        
        //Marker cluster group
        var markers = L.markerClusterGroup({
            maxClusterRadius: 20,
            spiderfyDistanceMultiplier: 2,
            showCoverageOnHover: false
        });      
            
        // Get lat lon from country code in database
        <?php
        $prep_stmt = "SELECT lat, lon FROM PROJ WHERE status='COMPLETE'";
        $result = $mysqli->query($prep_stmt);
        while($row = $result->fetch_assoc()){
            $lat = $row['lat'];
            $lon = $row['lon'];
            ?>            
            var marker = L.marker([<?php echo $lat?>, <?php echo $lon?>], {
                icon: AweIcon('PROJ')
            });
            markers.addLayer(marker);            
            <?php 
        }
        ?>  
        map.addLayer(markers);
    </script>                
    <!-- End of Global Usage Map -->
    
        <div id="container" class="sidebar">
        <h4>Data Statistics</h4>
            <table align="center" width="200px" border="0">
                <tr>
                    <td width="150px">Sites</td>
                    <td><?php
                        $prep_stmt = "
                        SELECT site_id
                        FROM PROJ
                        WHERE status='COMPLETE'
                        ";
                        $result = $mysqli->query($prep_stmt);                        
                        echo $result->num_rows;?></td>                    
                </tr>
                <tr>
                    <td colspan=2><b>Penetration/Geophysical tests</b></td>
                </tr>
                <tr>
                    <td>Borehole (Boring, SPT)</td>
                    <td><?php
                        $prep_stmt = "
                        SELECT A.loca_id
                        FROM LOCA A
                        INNER JOIN PROJ B ON A.site_id = B.site_id
                        WHERE B.status='COMPLETE' AND A.loca_type='HDPH'
                        ";                        
                        $result = $mysqli->query($prep_stmt);                        
                        echo $result->num_rows;?></td>                
                </tr>
                <tr>
                    <td>CPT</td>
                    <td><?php
                        $prep_stmt = "
                        SELECT A.loca_id
                        FROM LOCA A
                        INNER JOIN PROJ B ON A.site_id = B.site_id
                        WHERE B.status='COMPLETE' AND A.loca_type='SCPG'
                        ";
                        $result = $mysqli->query($prep_stmt);                        
                        echo $result->num_rows;?></td>                
                </tr>
                <tr>
                    <td>Test pit</td>
                    <td><?php
                        $prep_stmt = "
                        SELECT A.loca_id
                        FROM LOCA A
                        INNER JOIN PROJ B ON A.site_id = B.site_id
                        WHERE B.status='COMPLETE' AND A.loca_type='TEPT'
                        ";                        
                        $result = $mysqli->query($prep_stmt);                        
                        echo $result->num_rows;?></td>                
                </tr>
                <tr>
                    <td>Geophysical test (Vs)</td>
                    <td><?php
                        $prep_stmt = "
                        SELECT A.loca_id
                        FROM LOCA A
                        INNER JOIN PROJ B ON A.site_id = B.site_id
                        WHERE B.status='COMPLETE' AND A.loca_type='GPVS'
                        ";
                        $result = $mysqli->query($prep_stmt);                        
                        echo $result->num_rows;?></td>                
                </tr>
                <tr>
                    <td colspan=2><b>Lab tests</b></td>
                </tr>
                <tr>
                    <td>Sieve</td>
                    <td><?php
                        $prep_stmt = "
                        SELECT A.samp_id
                        FROM SAMP A
                        INNER JOIN PROJ B ON A.site_id = B.site_id
                        WHERE B.status='COMPLETE' AND A.sieve IS NOT NULL
                        ";
                        $result = $mysqli->query($prep_stmt);                        
                        echo $result->num_rows;?></td>
                </tr>
                <tr>
                    <td>Atterberg limit</td>
                    <td><?php
                        $prep_stmt = "
                        SELECT A.samp_id
                        FROM SAMP A
                        INNER JOIN PROJ B ON A.site_id = B.site_id
                        WHERE B.status='COMPLETE' AND A.pi IS NOT NULL
                        ";
                        $result = $mysqli->query($prep_stmt);                        
                        echo $result->num_rows;?></td>
                </tr>                
                <tr>
                    <td>Consolidation</td>
                    <td><?php
                        $prep_stmt = "
                        SELECT A.samp_id
                        FROM SAMP A
                        INNER JOIN PROJ B ON A.site_id = B.site_id
                        WHERE B.status='COMPLETE' AND A.other='CONS'
                        ";                        
                        $result = $mysqli->query($prep_stmt);                        
                        echo $result->num_rows;?></td>
                </tr>
                <tr>
                    <td>Triaxial (monotonic)</td>
                    <td><?php
                        $prep_stmt = "
                        SELECT A.samp_id
                        FROM SAMP A
                        INNER JOIN PROJ B ON A.site_id = B.site_id
                        WHERE B.status='COMPLETE' AND (A.other='TXUU' OR A.other='TXCU' OR A.other='TXCD')
                        ";
                        $result = $mysqli->query($prep_stmt);                        
                        echo $result->num_rows;?></td>
                </tr>
                <tr>
                    <td>Triaxial (cyclic)</td>
                    <td><?php
                        $prep_stmt = "
                        SELECT A.samp_id
                        FROM SAMP A
                        INNER JOIN PROJ B ON A.site_id = B.site_id
                        WHERE B.status='COMPLETE' AND A.other='TXCC'
                        ";                        
                        $result = $mysqli->query($prep_stmt);                        
                        echo $result->num_rows;?></td>
                </tr>
                <tr>
                    <td>Others</td>
                    <td><?php
                        $prep_stmt = "
                        SELECT A.samp_id
                        FROM SAMP A
                        INNER JOIN PROJ B ON A.site_id = B.site_id
                        WHERE B.status='COMPLETE' AND (A.other='SSMO' OR A.other='SSCY' OR A.other='OTHT')
                        ";                        
                        $result = $mysqli->query($prep_stmt);                        
                        echo $result->num_rows;?></td>
                </tr>                
            </table>
        </div>
        <h2><center></center></h2>
        <p>NGL Database is a community database where engineers/researchers can download and upload case histories of liquefaction and ground deformation. Please refer to <a href="http://uclageo.com/NGL/"><i>NGL project</i></a> site for further documentation, recent activity, and project organization. The documentation how to navigate, download, and upload data in this database can be found in <a href="help.php"><b>Documentation/Help</b></a>.</p>
        <p>This database platform is now under development, so possibly contains incorrect figures and errors. Please contact to <a href="mailto:ngl@uclageo.com?Subject=NGL Database" target="_top">ngl@uclageo.com</a> for any comments.</p>
        <h4>Contributors</h4>
        <p>NGL team acknowledge all contributors supporting the NGL database. Following are major contributors (sites more than 5):</p>
        <table width=700px border="0">
        <?php
            $prep_stmt = "SELECT organ FROM members";
            $result = $mysqli->query($prep_stmt);
            $i = 0;
            while($row = $result->fetch_assoc()){
                $organ[$i] = $row['organ'];
                $i++;
            }
            $unique_organ = array_unique($organ);
            $i = 0;
            foreach($unique_organ as $value){
                $num[$i] = sizeof(array_search($value,$organ));
                if($num[$i] >= 5){
                    ?><tr>
                        <td><?=$value?></td>
                        <td><?=$num[$i]?> sites</td>            
                    </tr>
                <?}
            }?>
        </table>     
    </div>
    <div id="sponsors">
        We acknowledge our sponsors.
        <br>
        <center>
        <table>
            <tr>
            <td><img height="100px" src="images/PEER-web-logo_100.png" alt="PEER logo"></td>        
            <td><img height="100px" src="images/CT_logo.gif" alt="CT logo"></td>
            <td><img height="80px" src="images/Southwest_Research_Institute_Logo.jpg" alt="SWRI logo"></td>
            <td><img height="80px" src="images/500px-US-NuclearRegulatoryCommission-Logo.svg.png" alt="NRC logo"></td>
            </tr>
        </table>
        </center>
    </div>
    
    <!-- END pageBody -->
</body>
</html>