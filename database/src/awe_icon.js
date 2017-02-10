// Icon for marker
function AweIcon(name) {
    if(name == 'PROJ'){
        var Icon = L.AwesomeMarkers.icon({
            icon: 'icon ion-cube',
            markerColor: 'lightgray',
            iconColor: 'black'
        });
    } else if(name == 'PROJtmp'){
        var Icon = L.AwesomeMarkers.icon({
            icon: 'icon ion-cube',
            markerColor: 'lightgray',
            iconColor: 'yellow'
        });
    } else if(name == 'HDPH'){
        var Icon = L.AwesomeMarkers.icon({
            icon: 'icon ion-arrow-down-c',
            markerColor: 'blue'
        });
    } else if(name == 'SCPG'){                
        var Icon = L.AwesomeMarkers.icon({
            icon: 'icon ion-arrow-down-c',
            markerColor: 'red'
        });
    } else if(name == 'GPVS'){
        var Icon = L.AwesomeMarkers.icon({
            icon: 'icon ion-arrow-swap',
            markerColor: 'orange'
        }); 
    } else if(name == 'TEPT'){
        var Icon = L.AwesomeMarkers.icon({
            icon: 'icon ion-arrow-down-c',
            markerColor: 'green'
        });
    } else if(name == 'ACC'){
        var Icon = L.AwesomeMarkers.icon({
            icon: 'icon ion-ios-pulse-strong',
            markerColor: 'red'
        });
    } else if(name == 'VTA'){
        var Icon = L.AwesomeMarkers.icon({
            icon: 'icon ion-ios-pulse-strong',
            markerColor: 'blue'
        });    
    } else if(name == 'FLDO'){
        var Icon = L.AwesomeMarkers.icon({
            icon: 'icon ion-eye',
            markerColor: 'black'
        }); 
    } else if(name == 'TSTph'){
        var Icon = L.AwesomeMarkers.icon({
            icon: 'icon ion-eye',
            markerColor: 'blue'
        });
    }    
    return Icon;        
}
