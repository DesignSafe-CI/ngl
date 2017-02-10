//// Disable Enter key 

// Shortlist of Earthquake database by event name
function SearchEQ(){
    var text = document.getElementById('search').value;
    var options = document.getElementById('select').options;
    for (var i = 0; i < options.length; i++) {
        var option = options[i]; 
        option.hidden = false;
        var optionText = option.value; 
        var lowerOptionText = optionText.toLowerCase();
        var lowerText = text.toLowerCase(); 
        var regex = new RegExp("^" + text, "i");
        var match = optionText.match(regex); 
        var contains = lowerOptionText.indexOf(lowerText) != -1;
        if (!match && !contains) {
            option.hidden = true;
        }
    }
}
// Select option
function SelectEQ(){
    var select = document.getElementById('select').value;
    document.getElementById('search').value = select;
    document.getElementById("searchForm").submit();
}
// Location ID select for crosshole method
function SelLocaId(locaid){
    if(document.getElementById('GCHL_entry_table') != null){
        var x = document.getElementById(locaid).value;
        document.getElementById(locaid.substring(4)).value = x;
        document.getElementById("ServerMsg").innerHTML = "";
    } else {
        document.getElementById("ServerMsg").innerHTML = "Location ID 2 and 3 are only used for <b>Crosshole-type</b> test.";
    }    
}
// confirm window for site delete
function del_confirm(site){    
    if(site == ''){
        document.getElementById("ServerMsg").innerHTML = "Please select target to delete first.";
    } else if (confirm("Are you sure? All data associated with the "+site+" will be deleted.") == true) {
        document.getElementById("del_id").value = site;
        document.getElementById("myform").submit();
    }
}
// confirm window for window close
function close_confirm(x){
    if(x == ''){        
        if (confirm("Are you sure? All data unsaved will be lost.") == true) {
            document.getElementById("close_id").value = 'dummy';
            document.getElementById("myform").submit();
        }
    } else {
        document.getElementById("close_id").value = 'dummy';
        document.getElementById("myform").submit();
    }
}
// message to save first
function save_confirm(site_id){
    document.getElementById("next_id").value = site_id;
    if(site_id != undefined){
        document.getElementById("myform").submit();
    } else {
        document.getElementById("ServerMsg").innerHTML = "Please <b>SAVE</b> first.";
    }
}
// message for upload complete
function upload_msg(site_name){
    document.getElementById("save_id").value = site_name;
    document.getElementById("myform").submit();
}
// for GMPE select option   
function gmpelist(){
    var x = document.getElementById("new_gmpe").value;
    document.getElementById("gmpe").value = x;    
}
// for laboratory type option   
function lab_typelist(){
    var x = document.querySelector('input[name="type"]:checked').value
    if(x == "others_lab"){
        document.getElementById("type_list").value = null;
    } else {
        document.getElementById("type_list").value = x;
    }
}
// for lab tests select option   
function lablist(){
    var x = document.getElementById("new_lab").value;
    document.getElementById("lab_list").value = x;    
}
// for event select option   
function evtlist(){
    var x = document.getElementById("new_evt").value;
    document.getElementById("evt_list").value = x;
    document.getElementById("search").value = null;
    document.getElementById("searchForm").submit();
}
// for ground failure site select option   
function sitelist(){
    var x = document.getElementById("new_site").value;
    document.getElementById("site_list").value = x;
    document.getElementById("searchForm").submit();
}
// for FLDP lat lon dep activation and deactivation
function seis_st_active(){
    for(var i=1;i<4;i++){
        document.getElementById('FLDP[0]['+i+']').readOnly = false;
        document.getElementById('FLDP[0]['+i+']').className = "";
    }    
}
function seis_st_deactive(){
    for(var i=1;i<4;i++){
        document.getElementById('FLDP[0]['+i+']').readOnly = true;
        document.getElementById('FLDP[0]['+i+']').value = '';
        document.getElementById('FLDP[0]['+i+']').className = "readonly";
    }    
}
