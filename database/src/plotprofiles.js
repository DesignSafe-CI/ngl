///////////////////////////////////////////////////////////////////////////////
// Plot data
function bhplot([type,i]){ 
    // Open a new window
    newWindow = window.open("graph.html", "Profile"+i, "height=850,width=650,status=yes,toolbar=no,menubar=no,location=no",false);
    
    // Source file
    var link = document.getElementById(type+'_'+i).value;
    
    newWindow.link = link;
    newWindow.type = type;
    
}
