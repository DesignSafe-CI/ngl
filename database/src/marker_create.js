function markercreate(data_source,px){
    var markers = L.markerClusterGroup({
//        iconCreateFunction: function (cluster) {
//            var markers = cluster.getAllChildMarkers();
//            var html = '<div class="circle">' + markers.length + '</div>';
//            return L.divIcon({ html: html, className: 'mycluster', iconSize: L.point(32, 32) });
//        },
        maxClusterRadius: px,
        spiderfyDistanceMultiplier: 2,
        showCoverageOnHover: false
    });

    // Define Icons
    var CPTIcon = L.AwesomeMarkers.icon({
        icon: 'icon ion-arrow-down-c',
        markerColor: 'red'
    });
    var SPTIcon = L.AwesomeMarkers.icon({            
        icon: 'icon ion-arrow-down-c',
        markerColor: 'blue'
    });
    var TRXIcon = L.AwesomeMarkers.icon({            
        icon: 'icon ion-cube',
        markerColor: 'blue'
    });
    var ACCIcon = L.AwesomeMarkers.icon({
        icon: 'icon ion-ios-pulse',
        markerColor: 'black'
    });
    var VTAIcon = L.AwesomeMarkers.icon({            
        icon: 'icon ion-ios-pulse-strong',
        markerColor: 'black'
    });
    var PHTIcon = L.AwesomeMarkers.icon({            
        icon: 'icon ion-eye',
        markerColor: 'black'
    });
    var Icon = L.AwesomeMarkers.icon({
        markerColor: 'white'
    });

    // CSV parse
    var runLayer = omnivore.csv(data_source)            
        .on('ready', function() {
            map.fitBounds(runLayer.getBounds());
            runLayer.eachLayer(function(layer) {     
                // CSV name
                var title = layer.feature.properties.Name
                // Test type
                var ttype = layer.feature.properties.Type
                // Date
                var date = layer.feature.properties.Date
                // Description 
                var desc = layer.feature.properties.desc
                // Description for popup window
                var popup = '<b>' + title + '</b><br>' + desc
                // CSV placemark latitude 
                var lat = layer._latlng.lat
                // CSV placemark longitude 
                var lng = layer._latlng.lng 
                var latlng = new L.LatLng(lat,lng)
                // Create marker
                if (ttype == 'CPT') {                        
                    var marker = L.marker(latlng, {icon: CPTIcon});
                } else if (ttype == 'SPT') {
                    var marker = L.marker(latlng, {icon: SPTIcon});
                } else if (ttype == 'TRX') {
                    var marker = L.marker(latlng, {icon: TRXIcon});
                } else if (ttype == 'ACC') {
                    var marker = L.marker(latlng, {icon: ACCIcon});
                } else if (ttype == 'VTA') {
                    var marker = L.marker(latlng, {icon: VTAIcon});    
                } else if (ttype == 'Photo') {
                    var marker = L.marker(latlng, {icon: PHTIcon});
                } else {
                    var marker = L.marker(latlng, {icon: Icon});
                }
                // Pop up window
                marker.bindPopup(popup);
                // Cluster markers
                markers.addLayer(marker);
            });
        })             
    return (markers);	
};
