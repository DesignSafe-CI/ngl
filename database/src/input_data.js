///////////////////////////////////////////////////////////////////////////////
// Load from file for general information
///////////////////////////////////////////////////////////////////////////////
// Load from file
function DataLoad(form, GROUP, TITLE, HEAD, COMMENT, UNIT, ROWS, REQ) {
    var group_name = GROUP;
    var headings = HEAD;
    var comments = COMMENT;
    var title = TITLE;
    var unit = UNIT;
    var rows = ROWS;
    var req = REQ;
    
    // Table for title and heading
    table = document.createElement('table');        
    table.width = "100%";    
    table.id = group_name+"_title_table";
    table.style.paddingBottom = 0;
    
    var entrylength = headings.length;
    if(group_name == "OTHR" | group_name == "FLDO") entrylength = entrylength - 1;
    // Title
    var row = table.insertRow(0);
    var cell = row.insertCell(0);
    if(title[2] != '') title[1] = "<b>"+title[1]+"</b> <a class='comments' href='#"+group_name+"_title'><i class='icon ion-help-circled'></i></a><div style='display:none'><div id='"+group_name+"_title'><p style='font-size:12px'>"+title[2]+"</p></div></div>";
    cell.colSpan = 4;
    cell.innerHTML = title[1];
    if(group_name == 'LOCA' | group_name == 'SAMP'){
        cell.colSpan = entrylength+1;
    } else {
        cell.colSpan = entrylength;
    }    
    cell.className = "subhead";
    
    // File upload
    var row = table.insertRow(1);
    var cell = row.insertCell(0);    
    if(group_name == 'LOCA' | group_name == 'SAMP'){
        cell.colSpan = entrylength+1;
    } else {
        cell.colSpan = entrylength;
    }    
    cell.innerHTML = "<center><i>Fill data from file</i> <input class='inputfile' type='file' id='"+group_name+"_file' name='"+group_name+"_file' onchange=uploadFile('"+group_name+"')> <label for='"+group_name+"_file'>Choose a file</label> <input style='width:100px;border: none;background-color:#ADC3DF' id='"+group_name+"_uploadFile' readonly></center>";
    
    var ind_rows = 2;
        
    // Heading input
    var row = table.insertRow(ind_rows);
    row.id = group_name+"_entry_row";        
    for(i=0;i<entrylength;i++){
        var cell = row.insertCell(i);
        var heading = headings[i];
        if(unit[i] != "") heading = heading+" ("+unit[i]+")";
        if(req[i] == "Y") heading = heading+"<span class='error'>*</span>";
        if(comments[i] != "") heading = heading+" <a class='comments' href='#"+group_name+i+"'><i class='icon ion-help-circled'></i></a><div style='display:none'><div id='"+group_name+i+"'><p style='font-size:12px'>"+comments[i]+"</p></div></div>";
        cell.innerHTML = heading;
        if(group_name == "LOCA" | group_name == "SAMP"){
                cell.style.width = ((980-80)/entrylength-4).toFixed(0)+"px";
        } else if(group_name == 'FLDP'){
            if(i == 0) cell.style.width = "100px";
            else if(i == 1 | i == 2) cell.style.width = "70px";
            else if(i == 3) cell.style.width = "40px";
            else cell.style.width = "670px";
        } else if(group_name == 'GEOL'){
            if(i == 4) cell.style.width = "600px";
            else cell.style.width = "100px";
        } else if(group_name == 'DETL'){
            if(i == 1) cell.style.width = "900px";
            else cell.style.width = "100px";    
        } else {
            cell.style.width = ((980)/entrylength-4).toFixed(0)+"px";
        }
        cell.style.textAlign = "center";  
        cell.style.verticalAlign = "bottom";
    }
    // Add heading for LOCA and SAMP add button 
    if(group_name == "LOCA" | group_name == "SAMP"){
        var cell = row.insertCell(i);
        cell.innerHTML = "Data add<span class='error'>*</span> <a class='comments' href='#"+group_name+i+"'><i class='icon ion-help-circled'></i></a><div style='display:none'><div id='"+group_name+i+"'><p style='font-size:12px'>Click <b>ADD</b> button to add data.</p></div></div>";
        cell.width = 78;
        cell.style.textAlign = "center";  
        cell.style.verticalAlign = "bottom";
    }
    document.getElementById("addGroup").appendChild(table);       
    
    // Table for data
    table = document.createElement('table');        
    table.width = "100%";    
    table.id = group_name+"_entry_table";
    table.style.maxHeight = "300px";
    table.style.overflowY = "scroll";
    table.style.maxWidth = "1000px";
    table.style.overflowX = "scroll";
    table.style.paddingTop = 0;
    table.style.display = "block";
    
    // for cell width reference
    var table_head = document.getElementById(group_name+"_title_table");
    
    // Data input
    var inilength = rows.length;
    for(y=0;y<inilength;y++){
        var row = table.insertRow(y);
        var cells = rows[y];        
        for(i=0;i<entrylength;i++){            
            var cell = row.insertCell(i);       
            // Select for type
            if(group_name == "LOCA" & i == 3){                
                var element = document.createElement("select");
                element.style.width = '100%';
                element.name = group_name+"["+y+"]["+i+"]";
                element.id = group_name+"["+y+"]["+i+"]";                
                var option = document.createElement("option");
                option.value = cells[i];
                if(option.value == 'HDPH'){
                    option.text = 'Borehole';
                } else if(option.value == 'SCPG'){
                    option.text = 'CPT';
                } else if(option.value == 'TEPT'){
                    option.text = 'Test pit';
                } else if(option.value == 'GPVS'){
                    option.text = 'Vs test';
                }                  
                element.add(option);
                var option = document.createElement("option");
                option.text = 'Borehole';
                option.value = 'HDPH';
                element.add(option);
                var option = document.createElement("option");
                option.text = 'CPT';
                option.value = 'SCPG';
                element.add(option);
                var option = document.createElement("option");
                option.text = 'Test pit';
                option.value = 'TEPT';
                element.add(option);
                var option = document.createElement("option");
                option.text = 'Vs test';
                option.value = 'GPVS';
                element.add(option);
                cell.appendChild(element);
            } else if(group_name == "GPVS" & i == 0){
                var element = document.createElement("select");
                element.style.width = '100%';
                element.name = group_name+"["+y+"]["+i+"]";
                element.id = group_name+"["+y+"]["+i+"]";                
                var option = document.createElement("option");                
                option.value = cells[i];
                if(option.value == "GSWD"){
                    option.text = 'Surface Wave';
                } else if(option.value == "GDHL"){
                    option.text = 'Downhole-type';
                } else if(option.value == "GCHL"){
                    option.text = 'Crosshole-type';
                } else if(option.value == "GSPL"){
                    option.text = 'Suspension logging-type';
                }                
                element.add(option);
                var option = document.createElement("option");
                option.text = 'Surface Wave';
                option.value = 'GSWD';
                element.add(option);
                var option = document.createElement("option");
                option.text = 'Downhole-type';
                option.value = 'GDHL';
                element.add(option);
                var option = document.createElement("option");
                option.text = 'Crosshole-type';
                option.value = 'GCHL';
                element.add(option);
                var option = document.createElement("option");
                option.text = 'Suspension logging-type';
                option.value = 'GSPL';
                element.add(option);
                cell.appendChild(element);
                cell.style.textAlign = "center";  
                element.onchange = function(){
                    LoadSubGPVS();
                }
                // save id
                element = document.createElement('input');
                element.type = 'hidden';
                element.id = 'type_old';
                element.value = cells[i];
                cell.appendChild(element);
            } else if(group_name == "FLDP" & i == 0){                
                if(cells[i] == 'Yes'){
                    cell.innerHTML = "<input type='radio' name='"+group_name+"["+y+"]["+i+"]' value='Yes' style='width:15px' checked onchange='seis_st_active()'> Yes <input type='radio' name='"+group_name+"["+y+"]["+i+"]' value='No' style='width:15px' onchange='seis_st_deactive()'> No";
                } else if(cells[i] == 'No'){
                    cell.innerHTML = "<input type='radio' name='"+group_name+"["+y+"]["+i+"]' value='Yes' style='width:15px' onchange='seis_st_active()'> Yes <input type='radio' name='"+group_name+"["+y+"]["+i+"]' value='No' style='width:15px' checked onchange='seis_st_deactive()'> No";
                } else {
                    cell.innerHTML = "<input type='radio' name='"+group_name+"["+y+"]["+i+"]' value='Yes' style='width:15px' onchange='seis_st_active()'> Yes <input type='radio' name='"+group_name+"["+y+"]["+i+"]' value='No' style='width:15px' onchange='seis_st_deactive()'> No";
                }
            } else if(group_name == "GRMN" & i == 1){
                var element = document.createElement("select");
                element.style.width = '100%';
                element.name = group_name+"["+y+"]["+i+"]";
                element.id = group_name+"["+y+"]["+i+"]";                
                var option = document.createElement("option");                
                option.value = cells[i];
                if(option.value == "PGA"){
                    option.text = 'PGA (g)';
                } else if(option.value == "PGV"){
                    option.text = 'PGV (cm/s)';
                } else if(option.value == "PSAT02"){
                    option.text = 'PSA_T0.2 (g)';
                } else if(option.value == "PSAT10"){
                    option.text = 'PSA_T1.0 (g)';
                } else if(option.value == "PSAT30"){
                    option.text = 'PSA_T3.0 (g)';
                } else if(option.value == "CAV5"){
                    option.text = 'CAV5 (m/s)';
                } else if(option.value == "D595"){
                    option.text = 'D595 (sec)';
                }                
                element.add(option);
                var option = document.createElement("option");
                option.text = 'PGA (g)';
                option.value = 'PGA';
                element.add(option);
                var option = document.createElement("option");
                option.text = 'PGV (cm/s)';
                option.value = 'PGV';
                element.add(option);
                var option = document.createElement("option");
                option.text = 'PSA_T0.2 (g)';
                option.value = 'PSAT02';
                element.add(option);
                var option = document.createElement("option");
                option.text = 'PSA_T1.0 (g)';
                option.value = 'PSAT10';
                element.add(option);
                var option = document.createElement("option");
                option.text = 'PSA_T3.0 (g)';
                option.value = 'PSAT30';
                element.add(option);
                var option = document.createElement("option");
                option.text = 'CAV5 (m/s)';
                option.value = 'CAV5';
                element.add(option);
                var option = document.createElement("option");
                option.text = 'D595 (sec)';
                option.value = 'D595';
                element.add(option);
                cell.appendChild(element);
            } else if(group_name == "GRMN" & i == 2){
                var element = document.createElement("select");
                element.style.width = '100%';
                element.name = group_name+"["+y+"]["+i+"]";
                element.id = group_name+"["+y+"]["+i+"]";                
                var option = document.createElement("option");                
                option.value = cells[i];
                if(option.value == "Measured"){
                    option.text = 'Measured from adjacent station';
                } else if(option.value == "Interpolated"){
                    option.text = 'Interpolated from nearby stations';
                } else if(option.value == "GMM"){
                    option.text = 'Inferred from GMM';
                }                
                element.add(option);
                var option = document.createElement("option");
                option.text = 'Measured from adjacent station';
                option.value = 'Measured';
                element.add(option);
                var option = document.createElement("option");
                option.text = 'Interpolated from nearby stations';
                option.value = 'Interpolated';
                element.add(option);
                var option = document.createElement("option");
                option.text = 'Inferred from GMM';
                option.value = 'GMM';
                element.add(option);
                cell.appendChild(element);
            } else {               
                var element = document.createElement("input");
                element.type = "text";            
                element.name = group_name+"["+y+"]["+i+"]";
                element.id = group_name+"["+y+"]["+i+"]";
                element.value = cells[i];
                element.style.textAlign = "center";  
                cell.appendChild(element);                
                var cellloca = element.id;
                element.onpaste = function(cellloca){
                    if(group_name == "PROJ" | group_name == "HDPH" | group_name == "SCPG" | group_name == "TEPT" | group_name == "GPVS"){
                        fpastecell1(cellloca,group_name);
                    } else {
                        fpastecell(cellloca,group_name);
                    }
                }
            }
            
            var cell_width = table_head.rows[ind_rows].cells[i].offsetWidth;                
            cell.style.width = cell_width+"px";

//            if(group_name == "LOCA" | group_name == "SAMP"){
//                cell.style.width = ((980-80)/entrylength-4).toFixed(0)+"px";                
//            } else if(group_name == 'FLDP'){
//                if(i == 0) cell.style.width = "100px";
//                else if(i == 1 | i == 2) cell.style.width = "70px";
//                else if(i == 3) cell.style.width = "40px";
//                else cell.style.width = "670px";
//            } else {
//                cell.style.width = ((980)/entrylength-4).toFixed(0)+"px";                
//            }
            
            cell.style.textAlign = "center";
        }
        // Add button for LOCA
        if(group_name == "LOCA" & cells[0] != ''){
            // names pre-defined
            var names = rows.map(function(value,index) { return value[0]; });
            
            // Unique name?
            var name_idx = count(names,cells[0]);
            var name_num = name_idx.length;
            
            var cell = row.insertCell(i);
            cell.innerHTML = "<input class='button' type='button' value='add' id='"+group_name+"["+y+"]["+i+"]' onClick='LocaButton(\""+cells[0]+"\",\""+cells[3]+"\",\""+name_num+"\")'> <i class='icon ion-checkmark-circled' id='"+group_name+"["+y+"]["+(i+1)+"]' style='color:darkred;font-size:125%'></i>";            
            cell.width = 78;
        } else if(group_name == "LOCA" & cells[0] == ''){
            var cell = row.insertCell(i);            
            cell.innerHTML = "<input class='button' type='button' value='add' id='"+group_name+"["+y+"]["+i+"]' onClick='LocaButtonGray(\""+cells[0]+"\",\""+cells[3]+"\")'> <i class='icon ion-checkmark-circled' id='"+group_name+"["+y+"]["+(i+1)+"]' style='color:darkred;font-size:125%'></i>";            
            cell.width = 78;
        }
        // Add button for SAMP
        if(group_name == "SAMP" & cells[0] != ''){
            var names = rows.map(function(value,index) { return value[0]; });
            
            // Unique name?
            var name_idx = count(names,cells[0]);            
            var name_num = name_idx.length;
            
            var cell = row.insertCell(i); 
            var LOCA = document.getElementById('loca_id').value;
            cell.innerHTML = "<input class='button' type='button' value='add' id='"+group_name+"["+y+"]["+i+"]' onClick='SampButton(\""+cells[0]+"\",\""+LOCA+"\",\""+name_num+"\")'> <i class='icon ion-checkmark-circled' id='"+group_name+"["+y+"]["+(i+1)+"]' style='color:darkred;font-size:125%'></i>";
            cell.width = 78;
        } else if(group_name == "SAMP" & cells[0] == ''){
            var cell = row.insertCell(i); 
            var LOCA = document.getElementById('loca_id').value;
            cell.innerHTML = "<input class='button' type='button' value='add' id='"+group_name+"["+y+"]["+i+"]' onClick='SampButtonGray(\""+cells[0]+"\",\""+LOCA+"\")'> <i class='icon ion-checkmark-circled' id='"+group_name+"["+y+"]["+(i+1)+"]' style='color:darkred;font-size:125%'></i>";
            cell.width = 78;
        }
    }
    // Create tables
    document.getElementById("addGroup").appendChild(table);
    // add selected group to check existed group
    element = document.createElement('input');  
    element.type = "hidden";
    element.name = "selected_group";
    element.value = group_name;
    document.getElementById("addGroup").appendChild(element);
}
///////////////////////////////////////////////////////////////////////////////
// Load from file
function ReviewLoad(form,GROUP,TITLE,HEAD,COMMENT,UNIT,ROWS,REQ,EVT,LOCA,SAMP){
    var group_name = GROUP;    
    var headings = HEAD;
    var comments = COMMENT;
    var title = TITLE;
    var unit = UNIT;
    var rows = ROWS;
    var req = REQ;
    var evt = EVT;
    var loca = LOCA;
    var samp = SAMP;
    
    // Table for title and heading
    table = document.createElement('table');        
    table.width = "100%";    
    table.id = group_name+"_title_table";
    table.style.paddingBottom = 0;
    
    var entrylength = headings.length;
    // Title
    var row = table.insertRow(0);
    var cell = row.insertCell(0);
    if(samp != null){
        title[1] = title[1]+", Location ID: <b>"+loca+"</b>, Sample ID: <b>"+samp+"</b>";
    } else if(loca != null){
        title[1] = title[1]+", Location ID: <b>"+loca+"</b>";
    }
    if(evt != null){
        title[1] = title[1]+", Event Name: <b>"+evt+"</b>";
    }
    
    
    if(title[2] != '') title[1] = title[1]+"<a class='comments' href='#"+group_name+"_title'><i class='icon ion-help-circled'></i></a><div style='display:none'><div id='"+group_name+"_title'><p style='font-size:12px'>"+title[2]+"</p></div></div>";
    cell.innerHTML = title[1];
    cell.colSpan = entrylength;
    cell.className = "reviewhead";

    // Heading input
    var row = table.insertRow(1);
    row.id = group_name+"_entry_row";        
    for(i=0;i<entrylength;i++){
        var cell = row.insertCell(i);
        var heading = headings[i];
        if(unit[i] != "") heading = heading+" ("+unit[i]+")";
        if(req[i] == "Y") heading = heading+"<span class='error'>*</span>";
        if(comments[i] != "") heading = heading+" <a class='comments' href='#"+group_name+i+"'><i class='icon ion-help-circled'></i></a><div style='display:none'><div id='"+group_name+i+"'><p style='font-size:12px'>"+comments[i]+"</p></div></div>";
        cell.innerHTML = "<i>"+heading+"</i>";
        cell.style.width = ((980)/entrylength-4)+"px";
        cell.style.textAlign = "center";  
        cell.style.verticalAlign = "bottom";
    }
    document.getElementById("addGroup").appendChild(table);       
    
    // Table for data
    table = document.createElement('table');        
    table.width = "100%";    
    table.id = group_name+"_entry_table";
    table.style.maxHeight = "300px";
    table.style.overflowY = "scroll";
    table.style.maxWidth = "1000px";
    table.style.overflowX = "scroll";
    table.style.paddingTop = 0;
    
    // Data input
    var inilength = rows.length;
    for(y=0;y<inilength;y++){
        var row = table.insertRow(y);
        var cells = rows[y];        
        for(i=0;i<entrylength;i++){
            var cell = row.insertCell(i);            
            // Select for type
            if(group_name == "LOCA" & i == 3){                
                if(cells[i] == 'HDPH'){
                    cell.innerHTML = 'Borehole';
                } else if(cells[i] == 'SCPG'){
                    cell.innerHTML = 'CPT';
                } else if(cells[i] == 'TEPT'){
                    cell.innerHTML = 'Test pit';
                } else if(cells[i] == 'GPVS'){
                    cell.innerHTML = 'Vs test';
                }                  
            } else if(group_name == "GPVS" & i == 0){
                if(cells[i] == "GSWD"){
                    cell.innerHTML = 'Surface Wave';
                } else if(cells[i] == "GDHL"){
                    cell.innerHTML = 'Downhole-type';
                } else if(cells[i] == "GCHL"){
                    cell.innerHTML = 'Crosshole-type';
                } else if(cells[i] == "GSPL"){
                    cell.innerHTML = 'Suspension logging-type';
                }                
            } else {
                cell.innerHTML = cells[i]
            }
            cell.style.width = ((980)/entrylength-4)+"px";
            cell.style.textAlign = "center";
            cell.style.color = "blue";
        }
    }
    // Create tables
    document.getElementById("addGroup").appendChild(table);       
    // add selected group to check existed group
    element = document.createElement('input');  
    element.type = "hidden";
    element.name = "selected_group";
    element.value = group_name;
    document.getElementById("addGroup").appendChild(element);
}
//////////////////////////////////////////////////////////////////////////////////////////////
// Entry Input
function EntryInput(form,GROUP,HEAD,ROWS){
    // Table for entries
    var group_name = GROUP;
    var headings = HEAD;
    var rows = ROWS;

    var entrylength = headings.length;
    var inilength = rows.length;
    table2 = document.createElement('table');
    table2.id = group_name+"_entrynum_table";
    table2.width = "100%";    
    var row = table2.insertRow(0);
    var cell = row.insertCell(0);
    var OneRowGroup = ['PROJ','MAPF','HDPH','SCPG','TEPT','GPVS','LOCF','LABF','OTHR','EVNG','FLDP','FLDF','FLDO','GRMF'];
    if(OneRowGroup.indexOf(group_name) == -1){
        cell.innerHTML = "Number of entries: <input style='width:30px' id='"+group_name+"_entry' type='text' name='"+group_name+"_entry'> <input class='button' type='button' onClick='fentry(this.form,\""+group_name+"\")' value='Change'> <input type='hidden' id='"+group_name+"_entrylength'>";
        cell.style.width="200px";
//        // comment    
//        var row = table2.insertRow(1);
//        var cell = row.insertCell(0);
//        cell.innerHTML = "Rows are only shown up to 30 (No upper limit for data upload).";
    } else {
        cell.innerHTML = "<input type='hidden' id='"+group_name+"_entrylength'><input type='hidden' id='"+group_name+"_entry'>";
    }
    // Create table
    document.getElementById("addGroup").appendChild(table2);
    // pass entry length
    document.getElementById(group_name+"_entrylength").value = entrylength;
    // initial number of entries
    if(inilength > 1){
        document.getElementById(group_name+"_entry").value = inilength;
    }
}
///////////////////////////////////////////////////////////////////////////////
// for table entry
function fentry(form,group){      
    var group_name = group;
    var entrylength = document.getElementById(group_name+"_entrylength").value;
    var table = document.getElementById(group_name+"_entry_table");
    var table_head = document.getElementById(group_name+"_title_table");
    var row = document.getElementById(group_name+"_entry_row");
    var idx = row.rowIndex;
    var l = table.rows.length; 
    var x = document.getElementById(group_name+"_entry").value;
//    var ll = Number(Math.max(Math.min(x,30),1));
    var ll = Number(Math.max(x,1));
//    console.log(ll)
    document.getElementById(group_name+"_entry").value = ll;
    // Data input
    // Contents
    if(ll > l | x == 0){
        if(x == 0){
            for(i=l-1;i>ll-1;i--){
                table.deleteRow(i);   
            }
            table.deleteRow(0);
            l = 0;
        }
        // Add entries    
        for(i=l;i<ll;i++){            
            var y = Number(i);
            var row = table.insertRow(i);
            for(ii=0;ii<entrylength;ii++){                
                var cell = row.insertCell(ii);       
                // For group LOCA
                if(group_name == "LOCA" & ii == 3){                    
                    var element = document.createElement("select");
                    element.style.width = '100%';
                    element.name = group_name+"["+y+"]["+ii+"]";
                    element.id = group_name+"["+y+"]["+ii+"]";                
                    var option = document.createElement("option");
                    option.value = '';
                    option.text = '';
                    element.add(option);
                    var option = document.createElement("option");
                    option.text = 'Borehole';
                    option.value = 'HDPH';
                    element.add(option);
                    var option = document.createElement("option");
                    option.text = 'CPT';
                    option.value = 'SCPG';
                    element.add(option);
                    var option = document.createElement("option");
                    option.text = 'Test pit';
                    option.value = 'TEPT';
                    element.add(option);
                    var option = document.createElement("option");
                    option.text = 'Vs test';
                    option.value = 'GPVS';
                    element.add(option);
                    cell.appendChild(element);
                    cell.style.textAlign = "center";  
                } else if(group_name == "GRMN" & ii == 1){
                    var element = document.createElement("select");
                    element.style.width = '100%';
                    element.name = group_name+"["+y+"]["+ii+"]";
                    element.id = group_name+"["+y+"]["+ii+"]";                
                    var option = document.createElement("option");                
                    option.value = '';
                    option.text = '';
                    element.add(option);
                    var option = document.createElement("option");
                    option.text = 'PGA (g)';
                    option.value = 'PGA';
                    element.add(option);
                    var option = document.createElement("option");
                    option.text = 'PGV (cm/s)';
                    option.value = 'PGV';
                    element.add(option);
                    var option = document.createElement("option");
                    option.text = 'PSA_T0.2 (g)';
                    option.value = 'PSAT02';
                    element.add(option);
                    var option = document.createElement("option");
                    option.text = 'PSA_T1.0 (g)';
                    option.value = 'PSAT10';
                    element.add(option);
                    var option = document.createElement("option");
                    option.text = 'PSA_T3.0 (g)';
                    option.value = 'PSAT30';
                    element.add(option);
                    var option = document.createElement("option");
                    option.text = 'CAV5 (m/s)';
                    option.value = 'CAV5';
                    element.add(option);
                    var option = document.createElement("option");
                    option.text = 'D595 (sec)';
                    option.value = 'D595';
                    element.add(option);
                    cell.appendChild(element);
                } else if(group_name == "GRMN" & ii == 2){
                    var element = document.createElement("select");
                    element.style.width = '100%';
                    element.name = group_name+"["+y+"]["+ii+"]";
                    element.id = group_name+"["+y+"]["+ii+"]";                
                    var option = document.createElement("option");                
                    option.value = '';
                    option.text = '';
                    element.add(option);
                    var option = document.createElement("option");
                    option.text = 'Measured from adjacent station';
                    option.value = 'Measured';
                    element.add(option);
                    var option = document.createElement("option");
                    option.text = 'Interpolated from nearby stations';
                    option.value = 'Interpolated';
                    element.add(option);
                    var option = document.createElement("option");
                    option.text = 'Inferred from GMM';
                    option.value = 'GMM';
                    element.add(option);
                    cell.appendChild(element);          
                } else {                
                    var element = document.createElement("input");
                    element.type = "text";            
                    element.name = group_name+"["+y+"]["+ii+"]";    
                    element.id = group_name+"["+y+"]["+ii+"]";
                    if(group_name == "GEOL" & ii == 4) {
                        element.style.textAlign = "left";  
                    } else if (group_name == "DETL" & ii == 1) {
                        element.style.textAlign = "left";  
                    } else {
                        element.style.textAlign = "center";  
                    }
                    cell.appendChild(element);
                    var cellloca = element.id;
                    element.onpaste = function(cellloca){
                        fpastecell(cellloca,group_name);
                    }
                }
                var cell_width = table_head.rows[2].cells[ii].offsetWidth;                
                cell.style.width = cell_width+"px";
            }
            // Add button for LOCA
            if(group_name == "LOCA"){
                var cell = row.insertCell(ii);                
                cell.innerHTML = "<input class='button' style='background:gray' type='button' value='add' onClick='LocaButtonGray()'> <i class='icon ion-checkmark-circled' id='"+group_name+"["+y+"]["+ii+"]' style='color:darkred;font-size:125%'></i>";
                cell.width = 78;
            }
            // Add button for SAMP
            if(group_name == "SAMP"){
                var cell = row.insertCell(ii);
                cell.innerHTML = "<input class='button' style='background:gray' type='button' value='add' onClick='SampButtonGray()'> <i class='icon ion-checkmark-circled' id='"+group_name+"["+y+"]["+ii+"]' style='color:darkred;font-size:125%'></i>";
                cell.width = 78;
            }
        }
    } else {
        // Delete existed entries
        for(i=l-1;i>ll-1;i--){
            table.deleteRow(i);   
        }
    }
}
//////////////////////////////////////////////////////////////////////////////////////////////
// Load additional file data for site
function FileLoad(form,GROUP,HEADING,COMMENT,TMP,SITE,DATA,LOCA,SAMP){
    var group_name = GROUP; 
    var headings = HEADING;
    var comments = COMMENT;
    var name = [];
    var desc = [];
    var file = [];
    var path = [];
    var name_length = DATA.length;    
    for(i=0;i<name_length;i++){        
        name[i] = DATA[i][0];
        desc[i] = DATA[i][1];
        file[i] = DATA[i][2];
        if(LOCA == null){
            path[i] = './uploads/'+TMP+'/'+SITE+'/FILES/'+file[i];
        } else if (SAMP == null){
            path[i] = './uploads/'+TMP+'/'+SITE+'/'+LOCA+'/FILES/'+file[i];
        } else {
            path[i] = './uploads/'+TMP+'/'+SITE+'/'+LOCA+'/'+SAMP+'/FILES/'+file[i];
        }        
    }

    /////////////////////////////////////////////////////////////////
    // Table for additional files
    table3 = document.createElement('table');
    table3.width = "100%";
    table3.id = group_name+"_entry_table";
    var row = table3.insertRow(0);
    row.id = group_name+"_add_row";
    var cell = row.insertCell(0);
    cell.innerHTML = "<b>Associated files</b> <input type='hidden' id='"+group_name+"_add_num' name='"+group_name+"_add_num' value='0'>";    
    cell.colSpan = 7;

    // for loop based on number of add files
    for(i=0;i<name_length;i++){
        if(i==0 | name[i] != ''){            
            var row = table3.insertRow(i+1);
            // Name
            var cell = row.insertCell(0);
            var heading = headings[0];
            if(comments[0] != "") heading = heading+" <a class='comments' href='#"+group_name+0+"'><i class='icon ion-help-circled'></i></a><div style='display:none'><div id='"+group_name+0+"'><p style='font-size:12px'>"+comments[0]+"</p></div></div>";
            cell.innerHTML = heading;
            cell.style.width = "6%";
            var cell = row.insertCell(1);
            cell.innerHTML = "<input type='text' name='"+group_name+"_add_name["+i+"]' value='"+name[i]+"'><br>";
            cell.style.width = "9%";    

            // Description
            var cell = row.insertCell(2);
            var heading = headings[1];
            if(comments[1] != "") heading = heading+" <a class='comments' href='#"+group_name+1+"'><i class='icon ion-help-circled'></i></a><div style='display:none'><div id='"+group_name+1+"'><p style='font-size:12px'>"+comments[1]+"</p></div></div>";
            cell.innerHTML = heading;
            cell.style.width = "9%";
            cell.style.textAlign = "left";
            var cell = row.insertCell(3);
            cell.innerHTML = "<input type='text' name='"+group_name+"_add_desc["+i+"]' placeholder='Description' value='"+desc[i]+"'>";
            cell.style.width = "21%";
            
            // File upload
            var cell = row.insertCell(4);    
            cell.innerHTML = "File upload";
            cell.style.width = "8%";
            cell.style.textAlign = "right";
            var cell = row.insertCell(5);
            cell.innerHTML = "<input class='inputfile' type='file' name='"+group_name+"_add_file["+i+"]' id='"+group_name+"_add_file"+i+"' onchange=nameUpload('"+group_name+"','"+i+"')><label for='"+group_name+"_add_file"+i+"'>Choose a file</label> <input style='width:80px;border: none;background-color:#ADC3DF' id='"+group_name+"_added_file"+i+"' readonly></center>";
            cell.style.width = "22%";
            
            // Uploaded file
            var cell = row.insertCell(6);
            if(file[i] == ''){
                cell.innerHTML = "<input type='hidden' name='"+group_name+"_add_file_ex["+i+"]' value='"+file[i]+"'>";
            } else {
                cell.innerHTML = "<a href='"+path[i]+"'>"+file[i]+"</a><input type='hidden' name='"+group_name+"_add_file_ex["+i+"]' value='"+file[i]+"'>";
            }
            cell.style.width = "25%";
            cell.style.textAlign = "left";
        }
    }
    // Buttons
    var row = table3.insertRow(i+1);
    var cell = row.insertCell(0);
    cell.innerHTML = "<input class='button' type='button' onClick='add_file(this,\""+group_name+"\")' value='Add'> <input class='button' type='button' onClick='del_file(this,\""+group_name+"\")' value='Delete'>";
    cell.colSpan = 2;
    // Create table
    document.getElementById("addGroup").appendChild(table3);
}
//////////////////////////////////////////////////////////////////////////////////////////////
// Load additional file data for site
function ReviewFileLoad(form,GROUP,HEADING,COMMENT,TMP,SITE,DATA,EVT,LOCA,SAMP){
    var group_name = GROUP; 
    var headings = HEADING;
    var comments = COMMENT;
    var name = [];
    var desc = [];
    var file = [];
    var path = [];
    var name_length = DATA.length;    
    for(i=0;i<name_length;i++){
        if(group_name == 'LABF'){
            name[i] = DATA[i][1];
            desc[i] = DATA[i][2];
            file[i] = DATA[i][3];
        } else {
            name[i] = DATA[i][0];
            desc[i] = DATA[i][1];
            file[i] = DATA[i][2];
        }
        
        if(LOCA == null){
            path[i] = './uploads/'+TMP+'/'+SITE+'/FILES/'+file[i];
        } else if (SAMP == null){
            path[i] = './uploads/'+TMP+'/'+SITE+'/'+LOCA+'/FILES/'+file[i];
        } else {
            path[i] = './uploads/'+TMP+'/'+SITE+'/'+LOCA+'/'+SAMP+'/FILES/'+file[i];
        }        
    }    

    /////////////////////////////////////////////////////////////////
    // Table for additional files
    table3 = document.createElement('table');
    table3.width = "100%";
    table3.id = group_name+"_entry_table";
    var row = table3.insertRow(0);
    row.id = group_name+"_add_row";
    var cell = row.insertCell(0);
    cell.innerHTML = "<b>Associated files</b> <input type='hidden' id='"+group_name+"_add_num' name='"+group_name+"_add_num' value='0'>";    
    cell.colSpan = 7;

    // for loop based on number of add files
    for(i=0;i<name_length;i++){
        if(i==0 | name[i] != ''){            
            var row = table3.insertRow(i+1);
            // Name
            var cell = row.insertCell(0);
            if(group_name == 'LABF'){
                var heading = headings[1];
            } else {
                var heading = headings[0];
            }            
            if(comments[0] != "") heading = heading+" <a class='comments' href='#"+group_name+0+"'><i class='icon ion-help-circled'></i></a><div style='display:none'><div id='"+group_name+0+"'><p style='font-size:12px'>"+comments[0]+"</p></div></div>";
            cell.innerHTML = heading;
            cell.style.width = "6%";
            var cell = row.insertCell(1);
            cell.innerHTML = name[i];
            cell.style.width = "9%";
            cell.style.color = 'blue';

            // Description
            var cell = row.insertCell(2);
            var heading = headings[1];
            if(comments[1] != "") heading = heading+" <a class='comments' href='#"+group_name+1+"'><i class='icon ion-help-circled'></i></a><div style='display:none'><div id='"+group_name+1+"'><p style='font-size:12px'>"+comments[1]+"</p></div></div>";
            cell.innerHTML = heading;
            cell.style.width = "9%";
            cell.style.textAlign = "left";
            var cell = row.insertCell(3);
            cell.innerHTML = desc[i];
            cell.style.width = "21%";
            cell.style.color = 'blue';
            
            // Uploaded file
            var cell = row.insertCell(4);
            cell.innerHTML = "<a href='"+path[i]+"'>"+file[i]+"</a>";
            cell.style.width = "70%";
            cell.style.textAlign = "left";
            cell.style.color = 'blue';
        }
    }
    // Create table
    document.getElementById("addGroup").appendChild(table3);
}
//////////////////////////////////////////////////////////////////////////////////////////////
// Load additional file data for other lab test
function FileLoadOTHR(form,GROUP,TITLE,HEAD,COMMENT,UNIT,ROWS,REQ,TMP,SITE,LOCA,SAMP){
    var group_name = GROUP;    
    var headings = HEAD;
    var comments = COMMENT;
    var title = TITLE;
    var unit = UNIT;
    var rows = ROWS;
    var req = REQ;
    var file = [];
    var path = [];
    var name_length = rows.length;
    // File path    
    for(i=0;i<name_length;i++){        
        file[i] = rows[i][4];
        path[i] = './uploads/'+TMP+'/'+SITE+'/'+LOCA+'/'+SAMP+'/FILES/'+file[i];
    }
    /////////////////////////////////////////////
    // Table for heading
    table = document.createElement('table');        
    table.width = "100%";    
    table.style.paddingBottom = 0;    
    var entrylength = headings.length;
    entrylength = entrylength - 1;
    // Title
    var row = table.insertRow(0);
    row.id = group_name+"_add_row";
    var cell = row.insertCell(0);
    if(title[2] != '') title[1] = "<b>"+title[1]+"</b> <a class='comments' href='#"+group_name+"_title'><i class='icon ion-help-circled'></i></a><div style='display:none'><div id='"+group_name+"_title'><p style='font-size:12px'>"+title[2]+"</p></div></div>";
    cell.innerHTML = title[1];
    cell.colSpan = entrylength+2;        
    cell.className = "subhead";
    
    // data upload from file
    var row = table.insertRow(1);
    var cell = row.insertCell(0);    
    cell.colSpan = entrylength+2;
    
    cell.innerHTML = "<center><i>Fill data from file</i> <input class='inputfile' type='file' id='"+group_name+"_file' name='"+group_name+"_file' onchange=uploadFile('"+group_name+"')> <label for='"+group_name+"_file'>Choose a file</label> <input style='width:100px;border: none;background-color:#ADC3DF' id='"+group_name+"_uploadFile' readonly></center>";
    
    var ind_rows = 2;
    
    // Heading input
    var row = table.insertRow(ind_rows);

    row.id = group_name+"_entry_row";        
    for(i=0;i<entrylength;i++){
        var cell = row.insertCell(i);
        var heading = headings[i];
        if(unit[i] != "") heading = heading+" ("+unit[i]+")";
        if(req[i] == "Y") heading = heading+"<span class='error'>*</span>";
        if(comments[i] != "") heading = heading+" <a class='comments' href='#"+group_name+i+"'><i class='icon ion-help-circled'></i></a><div style='display:none'><div id='"+group_name+i+"'><p style='font-size:12px'>"+comments[i]+"</p></div></div>";
        cell.innerHTML = heading;
        cell.style.width = ((980-400)/(entrylength)-6)+"px";
        cell.style.textAlign = "center";  
        cell.style.verticalAlign = "bottom";
    }
    // Add heading for file upload
    var cell = row.insertCell(i);
    cell.innerHTML = "File Add<span class='error'>*</span> <a class='comments' href='#"+group_name+i+"'><i class='icon ion-help-circled'></i></a><div style='display:none'><div id='"+group_name+i+"'><p style='font-size:12px'>"+comments[i]+"</p></div></div>";
    cell.style.width = "200px";
    cell.style.textAlign = "center";  
    cell.style.verticalAlign = "bottom";
    // Add space
    var cell = row.insertCell(i+1);
    cell.innerHTML = '';
    cell.style.width = "200px";
    document.getElementById("addGroup").appendChild(table);       
    /////////////////////////////////////////////
    // Table for data
    table = document.createElement('table');        
    table.width = "100%";    
    table.id = group_name+"_entry_table";
    table.style.maxHeight = "300px";
    table.style.overflowY = "scroll";
    table.style.maxWidth = "1000px";
    table.style.overflowX = "scroll";
    table.style.paddingTop = 0;    
    // Data input
    var inilength = rows.length;
    for(y=0;y<inilength;y++){
        var row = table.insertRow(y);
        var cells = rows[y];        
        for(i=0;i<entrylength;i++){
            var cell = row.insertCell(i);       
            if(group_name == "OTHR" & i == 1){                
                var element = document.createElement("select");
                element.style.width = '100%';
                element.name = group_name+"["+y+"]["+i+"]";
                element.id = group_name+"["+y+"]["+i+"]";                
                var option = document.createElement("option");
                option.value = cells[i];
                if(option.value == 'CONS'){
                    option.text = 'Consolidation';
                } else if(option.value == 'TXUU'){
                    option.text = 'Triaxial-UU';
                } else if(option.value == 'TXCU'){
                    option.text = 'Triaxial-CU';
                } else if(option.value == 'TXCD'){
                    option.text = 'Triaxial-CD';
                } else if(option.value == 'TXCC'){
                    option.text = 'Triaxial-Cyclic';
                } else if(option.value == 'SSMO'){
                    option.text = 'Simple Shear-Monotonic';
                } else if(option.value == 'SSCY'){
                    option.text = 'Simple Shear-Cyclic';
                } else if(option.value == 'OTHT'){
                    option.text = 'Other';
                }
                element.add(option);
                var option = document.createElement("option");
                option.text = 'Consolidation';
                option.value = 'CONS';
                element.add(option);
                var option = document.createElement("option");
                option.text = 'Triaxial-UU';
                option.value = 'TXUU';
                element.add(option);
                var option = document.createElement("option");
                option.text = 'Triaxial-CU';
                option.value = 'TXCU';
                element.add(option);
                var option = document.createElement("option");
                option.text = 'Triaxial-CD';
                option.value = 'TXCD';
                element.add(option);
                var option = document.createElement("option");
                option.text = 'Triaxial-Cyclic';
                option.value = 'TXCC';
                element.add(option);
                var option = document.createElement("option");
                option.text = 'Simple Shear-Monotonic';
                option.value = 'SSMO';
                element.add(option);
                var option = document.createElement("option");
                option.text = 'Simple Shear-Cyclic';
                option.value = 'SSCY';
                element.add(option);
                var option = document.createElement("option");
                option.text = 'Other';
                option.value = 'OTHT';
                element.add(option);                
                cell.appendChild(element);                    
            } else {               
                var element = document.createElement("input");
                element.type = "text";            
                element.name = group_name+"["+y+"]["+i+"]";
                element.id = group_name+"["+y+"]["+i+"]";
                element.value = cells[i];
                element.style.textAlign = "center";  
                cell.appendChild(element);
                var cellloca = element.id;
                element.onpaste = function(cellloca){
                    fpastecell_file(cellloca,group_name);
                }
            }            
            cell.style.width = ((980-400)/(entrylength)-6)+"px";
            cell.style.textAlign = "center";
        }
        // File Add button for OTHR
        var cell = row.insertCell(i);         
        
        cell.innerHTML = "<input class='inputfile' type='file' name='"+group_name+"_add_file["+y+"]' id='"+group_name+"_add_file"+y+"' onchange=nameUpload('"+group_name+"','"+y+"')><label for='"+group_name+"_add_file"+y+"'>Choose a file</label> <input style='width:80px;border: none;background-color:#ADC3DF' id='"+group_name+"_added_file"+y+"' readonly></center>";
        
        cell.style.width = "200px";
        // Uploaded file
        var cell = row.insertCell(i+1);            
        cell.innerHTML = "<a href='"+path[y]+"'>"+file[y]+"</a><input type='hidden' name='"+group_name+"_add_file_ex["+y+"]' value='"+file[y]+"'>";
        cell.style.width = "200px";            
        cell.style.textAlign = "left";            
    }
    // Buttons
    var row = table.insertRow(y);
    var cell = row.insertCell(0);
    cell.innerHTML = "<input class='button' type='button' onClick='add_file_OTHR(this,\""+group_name+"\")' value='Add'> <input class='button' type='button' onClick='del_file(this,\""+group_name+"\")' value='Delete'><input type='hidden' id='"+group_name+"_add_num' name='"+group_name+"_add_num' value='0'>";
    cell.colSpan = 2;
    // Create table
    document.getElementById("addGroup").appendChild(table);
}
//////////////////////////////////////////////////////////////////////////////////////////////
// Load additional file data for event
function FileLoadFLDO(form,GROUP,TITLE,HEAD,COMMENT,UNIT,ROWS,REQ,TMP,SITE,EVT){
    var group_name = GROUP;
    var headings = HEAD;
    var comments = COMMENT;
    var title = TITLE;
    var unit = UNIT;
    var rows = ROWS;
    var req = REQ;
    var file = [];
    var path = [];
    var name_length = rows.length;
    // File path    
    for(i=0;i<name_length;i++){        
        file[i] = rows[i][5];
        path[i] = './uploads/'+TMP+'/'+SITE+'/'+EVT+'/FILES/'+file[i];        
    }
    /////////////////////////////////////////////
    // Table for heading
    table = document.createElement('table');        
    table.width = "100%";    
    table.style.paddingBottom = 0;    
    var entrylength = headings.length;
    entrylength = entrylength - 1;
    // Title
    var row = table.insertRow(0);
    row.id = group_name+"_add_row";
    var cell = row.insertCell(0);
    if(title[2] != '') title[1] = "<b>"+title[1]+"</b> <a class='comments' href='#"+group_name+"_title'><i class='icon ion-help-circled'></i></a><div style='display:none'><div id='"+group_name+"_title'><p style='font-size:12px'>"+title[2]+"</p></div></div>";
    cell.innerHTML = title[1];
    cell.colSpan = entrylength+2;        
    cell.className = "subhead";
    
    // data upload from file
    var row = table.insertRow(1);
    var cell = row.insertCell(0);    
    cell.colSpan = entrylength+2;
    
    cell.innerHTML = "<center><i>Fill data from file</i> <input class='inputfile' type='file' id='"+group_name+"_file' name='"+group_name+"_file' onchange=uploadFile('"+group_name+"')> <label for='"+group_name+"_file'>Choose a file</label> <input style='width:100px;border: none;background-color:#ADC3DF' id='"+group_name+"_uploadFile' readonly></center>";
    
    var ind_rows = 2;
    
    // Heading input
    var row = table.insertRow(ind_rows);
    row.id = group_name+"_entry_row";
    var width = new Array("130","130","70","70","250");
    for(i=0;i<entrylength;i++){
        var cell = row.insertCell(i);
        var heading = headings[i];
        if(unit[i] != "") heading = heading+" ("+unit[i]+")";
        if(req[i] == "Y") heading = heading+"<span class='error'>*</span>";
        if(comments[i] != "") heading = heading+" <a class='comments' href='#"+group_name+i+"'><i class='icon ion-help-circled'></i></a><div style='display:none'><div id='"+group_name+i+"'><p style='font-size:12px'>"+comments[i]+"</p></div></div>";
        cell.innerHTML = heading;
        cell.style.textAlign = "center";  
        cell.style.verticalAlign = "bottom";        
        cell.style.width = width[i]+"px";        
    }
    // Add heading for file upload
    var cell = row.insertCell(i);
    cell.innerHTML = "File Add <a class='comments' href='#"+group_name+i+"'><i class='icon ion-help-circled'></i></a><div style='display:none'><div id='"+group_name+i+"'><p style='font-size:12px'>"+comments[i]+"</p></div></div>";
    cell.style.width = "140px";
    cell.style.textAlign = "center";  
    cell.style.verticalAlign = "bottom";
    // Add space
    var cell = row.insertCell(i+1);
    cell.innerHTML = '';
    cell.style.width = "140px";
    document.getElementById("addGroup").appendChild(table);       
    /////////////////////////////////////////////
    // Table for data
    table = document.createElement('table');        
    table.width = "100%";    
    table.id = group_name+"_entry_table";
    table.style.maxHeight = "300px";
    table.style.overflowY = "scroll";
    table.style.maxWidth = "1000px";
    table.style.overflowX = "scroll";
    table.style.paddingTop = 0;    
    // Data input
    var inilength = rows.length;
    for(y=0;y<inilength;y++){
        var row = table.insertRow(y);
        var cells = rows[y];        
        for(i=0;i<entrylength;i++){
            var cell = row.insertCell(i);       
            if(group_name == "FLDO" && (i == 0)){
                var element = document.createElement("select");
                element.name = group_name+"["+y+"]["+i+"][]";
                element.id = group_name+"["+y+"]["+i+"]";
                element.className = 'multiselect';
                element.multiple = true;                
                var option = document.createElement("option");
                option.text = 'Measured displ.';
                option.value = 'mdsp';
                var values = cells[i].split('_');
                if(values.indexOf('mdsp') != -1) option.selected= true;
                element.add(option);
                var option = document.createElement("option");
                option.text = 'Lateral def.';
                option.value = 'ldfm';                
                if(values.indexOf('ldfm') != -1) option.selected= true;
                element.add(option);
                var option = document.createElement("option");
                option.text = 'Settlement';
                option.value = 'sttl';                
                if(values.indexOf('sttl') != -1) option.selected= true;
                element.add(option);
                var option = document.createElement("option");
                option.text = 'Sand boil';
                option.value = 'sndb';
                if(values.indexOf('sndb') != -1) option.selected= true;
                element.add(option);
                var option = document.createElement("option");
                option.text = 'Post-event def.';
                option.value = 'pedf';
                if(values.indexOf('pedf') != -1) option.selected= true;
                element.add(option);
                var option = document.createElement("option");
                option.text = 'No ground failure';
                option.value = 'ngfl';
                if(values.indexOf('ngfl') != -1) option.selected= true;
                element.add(option);
                cell.appendChild(element);       
            } else if(group_name == "FLDO" && i == 1){
                var element = document.createElement("select");
                element.style.width = '100%';
                element.name = group_name+"["+y+"]["+i+"]";
                element.id = group_name+"["+y+"]["+i+"]";                
                var option = document.createElement("option");
                option.value = cells[i];
                if(option.value == 'dspv') option.text = 'Displacement Vector Map';
                else if(option.value == 'crwt') option.text = 'Crack Width Transect';
                else if(option.value == 'phto') option.text = 'Photo';
                else if(option.value == 'ldar') option.text = 'LiDAR Image';
                else if(option.value == 'stim') option.text = 'Satellite Image';
                else if(option.value == 'geom') option.text = 'Georeferrenced Map';
                else if(option.value == 'fldn') option.text = 'Field Note';
                element.add(option);
                var option = document.createElement("option");
                option.text = 'Displacement Vector Map';
                option.value = 'dspv';
                element.add(option);
                var option = document.createElement("option");
                option.text = 'Crack Width Transect';
                option.value = 'crwt';
                element.add(option);
                var option = document.createElement("option");
                option.text = 'Photo';
                option.value = 'phto';
                element.add(option);
                var option = document.createElement("option");
                option.text = 'LiDAR Image';
                option.value = 'ldar';
                element.add(option);
                var option = document.createElement("option");
                option.text = 'Satellite Image';
                option.value = 'stim';
                element.add(option);
                var option = document.createElement("option");
                option.text = 'Georeferrenced Map';
                option.value = 'geom';
                element.add(option);
                var option = document.createElement("option");
                option.text = 'Field Note';
                option.value = 'fldn';
                element.add(option);
                cell.appendChild(element);
            } else {
                var element = document.createElement("input");
                element.type = "text";            
                element.name = group_name+"["+y+"]["+i+"]";
                element.id = group_name+"["+y+"]["+i+"]";
                element.value = cells[i];
                element.style.textAlign = "center";  
                cell.appendChild(element);
                var cellloca = element.id;
                element.onpaste = function(cellloca){
                    fpastecell_file(cellloca,group_name);
                }
            }
            if(i == 4){
                element.style.textAlign = "left";
            } else {
                element.style.textAlign = "center";
            }
            cell.style.width = width[i]+"px";            
        }
        // File Add button for FLDO
        var cell = row.insertCell(i); 
        
        cell.innerHTML = "<input class='inputfile' type='file' name='"+group_name+"_add_file["+y+"]' id='"+group_name+"_add_file"+y+"' onchange=nameUpload('"+group_name+"','"+y+"')><label for='"+group_name+"_add_file"+y+"'>Choose a file</label> <input style='width:60px;border: none;background-color:#ADC3DF' id='"+group_name+"_added_file"+y+"' readonly></center>";

        cell.style.width = "180px";
        // Uploaded file
        var cell = row.insertCell(i+1);            
        cell.innerHTML = "<a href='"+path[y]+"'>"+file[y]+"</a><input type='hidden' name='"+group_name+"_add_file_ex["+y+"]' value='"+file[y]+"'>";
        cell.style.width = "100px";
        cell.style.textAlign = "left";
    }    
    // Buttons
    var row = table.insertRow(y);
    var cell = row.insertCell(0);
    cell.innerHTML = "<input class='button' type='button' onClick='add_file_FLDO(this,\""+group_name+"\")' value='Add'> <input class='button' type='button' onClick='del_file(this,\""+group_name+"\")' value='Delete'><input type='hidden' id='"+group_name+"_add_num' name='"+group_name+"_add_num' value='0'>";
    cell.colSpan = 2;
    // Create table
    document.getElementById("addGroup").appendChild(table);
}
//////////////////////////////////////////////////////////////////////////////////////////////
// Open a new window for LOCA entry
function LocaButton(LOCA,TYPE,num){
    if(num == 1){
        var loca_id = LOCA;
        var type = TYPE;
        window.open("upload_"+type+".php?loca_id="+loca_id);
    } else {
        document.getElementById("ServerMsg").innerHTML = "Please use unique location ID.";
    }
    
}
//////////////////////////////////////////////////////////////////////////////////////////////
// Server Message for save
function LocaButtonGray(){
    document.getElementById("ServerMsg").innerHTML = "Please <b>SAVE</b> first.";
}
//////////////////////////////////////////////////////////////////////////////////////////////
// Open a new window for SAMP entry
function SampButton(SAMP,LOCA,num){    
    if(num == 1){
        var samp_id = SAMP;
        var loca_id = LOCA;
        window.open("upload_SAMP.php?samp_id="+samp_id+"&loca_id="+loca_id+"");
    } else {
        document.getElementById("ServerMsg").innerHTML = "Please use unique location ID.";
    }
}
//////////////////////////////////////////////////////////////////////////////////////////////
// Server Message for save
function SampButtonGray(){
    document.getElementById("ServerMsg").innerHTML = "Please save first.";
}
//////////////////////////////////////////////////////////////////////////////////////////////
// Date Format
function formatDate(date) {
    var d = new Date(date),
        month = '' + (d.getMonth() + 1),
        day = '' + d.getDate(),
        year = d.getFullYear();

    if (month.length < 2) month = '0' + month;
    if (day.length < 2) day = '0' + day;

    return [year, month, day].join('-');
}
///////////////////////////////////////////////////////////////////////////////
// Paste from spreadsheet
function fpastecell(paste,group){
    // Grouop name
    var group_name = group;
    var cellid = paste.target.id;
    var cellposition = [cellid.split(/\[|\]/)[1],cellid.split(/\[|\]/)[3]];
    var rowp = cellposition[0];    
    var colp = cellposition[1];    
    var data = paste.clipboardData.getData('text/plain');
    // Col length of table
    var entrylength = document.getElementById(group_name+"_entrylength").value;
    // length of entry
    var table = document.getElementById(group_name+"_entry_table");
    var l = table.rows.length;

//    if(l > 30) l = 30;    
    // number of entries
    var ll = document.getElementById(group_name+"_entry").value;
      
    var rows = data.split(/[\r\n]+/g);  
    var delim = "\t";  
    
    datapaste(group_name,rowp,colp,rows,entrylength,l,table,ll,delim);
}
///////////////////////////////////////////////////////////////////////////////
// Upload data from file
function fileUpload(group_name,rows){
    var rowp = 0;
    var colp = 0;
    // Col length of table
    var entrylength = document.getElementById(group_name+"_entrylength").value;
    // length of entry
    var table = document.getElementById(group_name+"_entry_table");
    var l = table.rows.length;

//    if(l > 30) l = 30;    
    // number of entries
    var ll = document.getElementById(group_name+"_entry").value;
    
    var delim = ",";
    var group1 = new Array('LOCA');
    var group3 = new Array('INDX','GRAT','OTHR','FLDO')
    var group4 = new Array('GPVS','GCHL')
    if(group1.indexOf(group_name)>=0){
        var num = 1;
    } else if(group3.indexOf(group_name)>=0){
        var num = 3;
    } else if(group4.indexOf(group_name)>=0){
        var num = 4;
    } else {
        var num = 2;
    }
    
    if(rows.length > 0){    
        var filegroup = Array('FLDO','OTHR');
        if(filegroup.indexOf(group_name) >= 0){
            datapaste_file(group_name,rowp,colp,rows,entrylength,l,table,ll,delim,num);
        } else {        
            datapaste(group_name,rowp,colp,rows,entrylength,l,table,ll,delim,num);
        }
    }
    
}

// Return array of string values, or NULL if CSV string not well formed.
function CSVtoArray(text) {
    var re_valid = /^\s*(?:'[^'\\]*(?:\\[\S\s][^'\\]*)*'|"[^"\\]*(?:\\[\S\s][^"\\]*)*"|[^,'"\s\\]*(?:\s+[^,'"\s\\]+)*)\s*(?:,\s*(?:'[^'\\]*(?:\\[\S\s][^'\\]*)*'|"[^"\\]*(?:\\[\S\s][^"\\]*)*"|[^,'"\s\\]*(?:\s+[^,'"\s\\]+)*)\s*)*$/;
    var re_value = /(?!\s*$)\s*(?:'([^'\\]*(?:\\[\S\s][^'\\]*)*)'|"([^"\\]*(?:\\[\S\s][^"\\]*)*)"|([^,'"\s\\]*(?:\s+[^,'"\s\\]+)*))\s*(?:,|$)/g;
    // Return NULL if input string is not well formed CSV string.
    if (!re_valid.test(text)) return null;
    var a = [];                     // Initialize array to receive values.
    text.replace(re_value, // "Walk" the string using replace with callback.
        function(m0, m1, m2, m3) {
            // Remove backslash from \' in single quoted values.
            if      (m1 !== undefined) a.push(m1.replace(/\\'/g, "'"));
            // Remove backslash from \" in double quoted values.
            else if (m2 !== undefined) a.push(m2.replace(/\\"/g, '"'));
            else if (m3 !== undefined) a.push(m3);
            return ''; // Return empty string.
        });
    // Handle special case of empty last value.
    if (/,\s*$/.test(text)) a.push('');
    return a;
}

// Limit to 30 rows (old)
//function datapaste(group_name,rowp,colp,rows,entrylength,l,table,ll,delim,num=0){
//    
//    //number of rows
//    var x = rows.length;
//    //dummy variable to indicate rows 30 for data over 30
//    var dummy = 0;
//    //Add data
//    for(y=0;y<x;y++) {
//        var yy = y+eval(rowp);
//        if(yy < l){            
//            var cells = rows[y];
//            if(delim == ','){
//                cells = CSVtoArray(cells);
//            } else {
//                cells = cells.split(delim);
//            }
//            cells = cells.slice(num,cells.length);
//            
//            //number of cells
//            var xx = cells.length;
//            for(i=0;i<xx;i++){
//                if(y == 0 & i == 0) var inival = cells[i];
//                var ii = i+eval(colp);
//                if(ii < entrylength){
//                    var targetcell = group_name+"["+yy+"]["+ii+"]";
//                    if(group_name == 'LOCA' & (ii == 6 | ii == 7)){
//                        document.getElementById(targetcell).value = formatDate(cells[i]);
//                    } else if(group_name == 'SAMP' & (ii == 5)){
//                        document.getElementById(targetcell).value = formatDate(cells[i]);
//                    } else {
//                        document.getElementById(targetcell).value = cells[i];
//                    }
//                }
//            }
//        }
//        else if(yy < 30){
//            //create row
//            var row = table.insertRow(yy); 
//            var cells = rows[y];
//            if(delim == ','){
//                cells = CSVtoArray(cells);
//            } else {
//                cells = cells.split(delim);
//            }
//            cells = cells.slice(num,cells.length);
//            //create cells
//            for(ii=0;ii<entrylength;ii++){
//                var cell = row.insertCell(ii);                
//                if(group_name == "LOCA" & ii == 3){
//                    var element = document.createElement("select");
//                    element.style.width = '100%';
//                    element.name = group_name+"["+yy+"]["+ii+"]";
//                    element.id = group_name+"["+yy+"]["+ii+"]";                
//                    var option = document.createElement("option");
//                    option.value = '';
//                    option.text = '';
//                    element.add(option);
//                    var option = document.createElement("option");
//                    option.text = 'Borehole';
//                    option.value = 'HDPH';
//                    element.add(option);
//                    var option = document.createElement("option");
//                    option.text = 'CPT';
//                    option.value = 'SCPG';
//                    element.add(option);
//                    var option = document.createElement("option");
//                    option.text = 'Test pit';
//                    option.value = 'TEPT';
//                    element.add(option);
//                    var option = document.createElement("option");
//                    option.text = 'Vs test';
//                    option.value = 'GPVS';
//                    element.add(option);
//                    cell.appendChild(element);
//                } else if(group_name == "GRMN" & ii == 1){
//                    var element = document.createElement("select");
//                    element.style.width = '100%';
//                    element.name = group_name+"["+yy+"]["+ii+"]";
//                    element.id = group_name+"["+yy+"]["+ii+"]";                
//                    var option = document.createElement("option");                
//                    option.value = '';
//                    option.text = '';
//                    element.add(option);
//                    var option = document.createElement("option");
//                    option.text = 'PGA (g)';
//                    option.value = 'PGA';
//                    element.add(option);
//                    var option = document.createElement("option");
//                    option.text = 'PGV (cm/s)';
//                    option.value = 'PGV';
//                    element.add(option);
//                    var option = document.createElement("option");
//                    option.text = 'PSA_T0.2 (g)';
//                    option.value = 'PSAT02';
//                    element.add(option);
//                    var option = document.createElement("option");
//                    option.text = 'PSA_T1.0 (g)';
//                    option.value = 'PSAT10';
//                    element.add(option);
//                    var option = document.createElement("option");
//                    option.text = 'PSA_T3.0 (g)';
//                    option.value = 'PSAT30';
//                    element.add(option);
//                    var option = document.createElement("option");
//                    option.text = 'CAV5 (m/s)';
//                    option.value = 'CAV5';
//                    element.add(option);
//                    var option = document.createElement("option");
//                    option.text = 'D595 (sec)';
//                    option.value = 'D595';
//                    element.add(option);
//                    cell.appendChild(element);
//                } else if(group_name == "GRMN" & ii == 2){
//                    var element = document.createElement("select");
//                    element.style.width = '100%';
//                    element.name = group_name+"["+yy+"]["+ii+"]";
//                    element.id = group_name+"["+yy+"]["+ii+"]";                
//                    var option = document.createElement("option");                
//                    option.value = '';
//                    option.text = '';
//                    element.add(option);
//                    var option = document.createElement("option");
//                    option.text = 'Measured from adjacent station';
//                    option.value = 'Measured';
//                    element.add(option);
//                    var option = document.createElement("option");
//                    option.text = 'Interpolated from nearby stations';
//                    option.value = 'Interpolated';
//                    element.add(option);
//                    var option = document.createElement("option");
//                    option.text = 'Inferred from GMM';
//                    option.value = 'GMM';
//                    element.add(option);
//                    cell.appendChild(element);           
//                } else {
//                    var element = document.createElement("input");
//                    element.type = "text";            
//                    element.name = group_name+"["+yy+"]["+ii+"]";    
//                    element.id = group_name+"["+yy+"]["+ii+"]";
//                    if(group_name == "GEOL" & ii == 4) {
//                        element.style.textAlign = "left";  
//                    } else if (group_name == "DETL" & ii == 1) {
//                        element.style.textAlign = "left";  
//                    } else {
//                        element.style.textAlign = "center";  
//                    }
//                    cell.appendChild(element);
//                    var cellloca = element.id;
//                    element.onpaste = function(cellloca){
//                        fpastecell(cellloca,group_name);
//                    }
//                }
//            }
//            // Add button for LOCA
//            if(group_name == "LOCA"){
//                var cell = row.insertCell(ii);                
//                cell.innerHTML = "<input class='button' style='background:gray' type='button' value='add' onClick='LocaButtonGray()'> <i class='icon ion-checkmark-circled' id='"+group_name+"["+yy+"]["+ii+"]' style='color:darkred;font-size:125%'></i>";
//                cell.width = 78;
//            }
//            // Add button for SAMP
//            if(group_name == "SAMP"){
//                var cell = row.insertCell(ii);
//                cell.innerHTML = "<input class='button' style='background:gray' type='button' value='add' onClick='SampButtonGray()'> <i class='icon ion-checkmark-circled' id='"+group_name+"["+yy+"]["+ii+"]' style='color:darkred;font-size:125%'></i>";
//                cell.width = 78;
//            }
//            //number of cells
//            var xx = cells.length;
//            for(i=0;i<xx;i++){
//                if(y == 0 & i == 0) var inival = cells[i];
//                var ii = i+eval(colp);
//                if(ii < entrylength){                    
//                    var targetcell = group_name+"["+yy+"]["+ii+"]";
//                    if(group_name == 'LOCA' & (ii == 6 | ii == 7)){
//                        document.getElementById(targetcell).value = formatDate(cells[i]);
//                    } else if(group_name == 'SAMP' & (ii == 5)){
//                        document.getElementById(targetcell).value = formatDate(cells[i]);
//                    } else {
//                        document.getElementById(targetcell).value = cells[i];
//                    }
//                }
//            }
//        }        
//        else if(yy < ll){
//            var cells = rows[y];
//            if(delim == ','){
//                cells = CSVtoArray(cells);
//            } else {
//                cells = cells.split(delim);
//            }
//            cells = cells.slice(num,cells.length);
//            //number of cells
//            var xx = cells.length;
//            for(i=0;i<xx;i++){                
//                var ii = i+eval(colp);
//                if(ii < entrylength){
//                    var targetcell = group_name+"["+yy+"]["+ii+"]";
//                    if(group_name == 'LOCA' & (ii == 6 | ii == 7)){
//                        document.getElementById(targetcell).value = formatDate(cells[i]);
//                    } else if(group_name == 'SAMP' & (ii == 5)){
//                        document.getElementById(targetcell).value = formatDate(cells[i]);
//                    } else {
//                        document.getElementById(targetcell).value = cells[i];
//                    }
//                }
//            }
//        }
//        else if(yy == 30 & dummy == 0){
//            var row = table.insertRow(yy);
//            var celldummy = [];                
//            //create hidden input
//            for(ii=0;ii<entrylength;ii++){
//                celldummy[ii] = row.insertCell(ii);
//                var element = document.createElement("input");
//                element.type = "hidden";
//                element.name = group_name+"["+yy+"]["+ii+"]";
//                element.id = group_name+"["+yy+"]["+ii+"]";
//                element.style.textAlign = "right";  
//                celldummy[ii].appendChild(element);
//                celldummy[ii].id = "celldummy["+ii+"]";
//            }
//            var cells = rows[y];
//            if(delim == ','){
//                cells = CSVtoArray(cells);
//            } else {
//                cells = cells.split(delim);
//            }
//            cells = cells.slice(num,cells.length);
//            //number of cells
//            var xx = cells.length;
//            for(i=0;i<xx;i++){
//                if(y == 0 & i == 0) var inival = cells[i];
//                var ii = i+eval(colp);
//                if(ii < entrylength){
//                    var targetcell = group_name+"["+yy+"]["+ii+"]";
//                    if(group_name == 'LOCA' & (ii == 6 | ii == 7)){
//                        document.getElementById(targetcell).value = formatDate(cells[i]);
//                    } else if(group_name == 'SAMP' & (ii == 5)){
//                        document.getElementById(targetcell).value = formatDate(cells[i]);
//                    } else {
//                        document.getElementById(targetcell).value = cells[i];
//                    }
//                }
//            }
//            dummy = 1;
//        }
//        else {           
//            for(ii=0;ii<entrylength;ii++){
//                var element = document.createElement("input");
//                element.type = "hidden";
//                element.name = group_name+"["+yy+"]["+ii+"]";
//                element.id = group_name+"["+yy+"]["+ii+"]";
//                document.getElementById("celldummy["+ii+"]").appendChild(element);
//            }
//            var cells = rows[y];
//            if(delim == ','){
//                cells = CSVtoArray(cells);
//            } else {
//                cells = cells.split(delim);
//            }
//            cells = cells.slice(num,cells.length);
//            //number of cells
//            var xx = cells.length;
//            for(i=0;i<xx;i++){
//                if(y == 0 & i == 0) var inival = cells[i];
//                var ii = i+eval(colp);
//                if(ii < entrylength){
//                    var targetcell = group_name+"["+yy+"]["+ii+"]";
//                    if(group_name == 'LOCA' & (ii == 6 | ii == 7)){
//                        document.getElementById(targetcell).value = formatDate(cells[i]);
//                    } else if(group_name == 'SAMP' & (ii == 5)){
//                        document.getElementById(targetcell).value = formatDate(cells[i]);
//                    } else {
//                        document.getElementById(targetcell).value = cells[i];
//                    }
//                }
//            }
//        }
//    }
//    setTimeout(function(){
//        var targetcell = group_name+"["+rowp+"]["+colp+"]";
//        document.getElementById(targetcell).value = inival;    
//    });    
//    // number of entries
//    if(yy+1 > ll) {
//        document.getElementById(group_name+"_entry").value = yy+1;
//    }
//}

function datapaste(group_name,rowp,colp,rows,entrylength,l,table,ll,delim,num=0){
    //number of rows
    var x = rows.length;
    //dummy variable to indicate rows 30 for data over 30
    var dummy = 0;
    //Add data
    for(y=0;y<x;y++) {
        var yy = y+eval(rowp);
        if(yy < l){            
            var cells = rows[y];
            if(delim == ','){
                cells = CSVtoArray(cells);
            } else {
                cells = cells.split(delim);
            }
            cells = cells.slice(num,cells.length);
            
            //number of cells
            var xx = cells.length;
            for(i=0;i<xx;i++){
                if(y == 0 & i == 0) var inival = cells[i];
                var ii = i+eval(colp);
                if(ii < entrylength){
                    var targetcell = group_name+"["+yy+"]["+ii+"]";
                    if(group_name == 'LOCA' & (ii == 6 | ii == 7)){
                        document.getElementById(targetcell).value = formatDate(cells[i]);
                    } else if(group_name == 'SAMP' & (ii == 5)){
                        document.getElementById(targetcell).value = formatDate(cells[i]);
                    } else {
                        document.getElementById(targetcell).value = cells[i];
                    }
                }
            }
        }
        else {
            //create row
            var row = table.insertRow(yy); 
            var cells = rows[y];
            if(delim == ','){
                cells = CSVtoArray(cells);
            } else {
                cells = cells.split(delim);
            }
            cells = cells.slice(num,cells.length);
            //create cells
            for(ii=0;ii<entrylength;ii++){
                var cell = row.insertCell(ii);                
                if(group_name == "LOCA" & ii == 3){
                    var element = document.createElement("select");
                    element.style.width = '100%';
                    element.name = group_name+"["+yy+"]["+ii+"]";
                    element.id = group_name+"["+yy+"]["+ii+"]";                
                    var option = document.createElement("option");
                    option.value = '';
                    option.text = '';
                    element.add(option);
                    var option = document.createElement("option");
                    option.text = 'Borehole';
                    option.value = 'HDPH';
                    element.add(option);
                    var option = document.createElement("option");
                    option.text = 'CPT';
                    option.value = 'SCPG';
                    element.add(option);
                    var option = document.createElement("option");
                    option.text = 'Test pit';
                    option.value = 'TEPT';
                    element.add(option);
                    var option = document.createElement("option");
                    option.text = 'Vs test';
                    option.value = 'GPVS';
                    element.add(option);
                    cell.appendChild(element);
                } else if(group_name == "GRMN" & ii == 1){
                    var element = document.createElement("select");
                    element.style.width = '100%';
                    element.name = group_name+"["+yy+"]["+ii+"]";
                    element.id = group_name+"["+yy+"]["+ii+"]";                
                    var option = document.createElement("option");                
                    option.value = '';
                    option.text = '';
                    element.add(option);
                    var option = document.createElement("option");
                    option.text = 'PGA (g)';
                    option.value = 'PGA';
                    element.add(option);
                    var option = document.createElement("option");
                    option.text = 'PGV (cm/s)';
                    option.value = 'PGV';
                    element.add(option);
                    var option = document.createElement("option");
                    option.text = 'PSA_T0.2 (g)';
                    option.value = 'PSAT02';
                    element.add(option);
                    var option = document.createElement("option");
                    option.text = 'PSA_T1.0 (g)';
                    option.value = 'PSAT10';
                    element.add(option);
                    var option = document.createElement("option");
                    option.text = 'PSA_T3.0 (g)';
                    option.value = 'PSAT30';
                    element.add(option);
                    var option = document.createElement("option");
                    option.text = 'CAV5 (m/s)';
                    option.value = 'CAV5';
                    element.add(option);
                    var option = document.createElement("option");
                    option.text = 'D595 (sec)';
                    option.value = 'D595';
                    element.add(option);
                    cell.appendChild(element);
                } else if(group_name == "GRMN" & ii == 2){
                    var element = document.createElement("select");
                    element.style.width = '100%';
                    element.name = group_name+"["+yy+"]["+ii+"]";
                    element.id = group_name+"["+yy+"]["+ii+"]";                
                    var option = document.createElement("option");                
                    option.value = '';
                    option.text = '';
                    element.add(option);
                    var option = document.createElement("option");
                    option.text = 'Measured from adjacent station';
                    option.value = 'Measured';
                    element.add(option);
                    var option = document.createElement("option");
                    option.text = 'Interpolated from nearby stations';
                    option.value = 'Interpolated';
                    element.add(option);
                    var option = document.createElement("option");
                    option.text = 'Inferred from GMM';
                    option.value = 'GMM';
                    element.add(option);
                    cell.appendChild(element);           
                } else {
                    var element = document.createElement("input");
                    element.type = "text";            
                    element.name = group_name+"["+yy+"]["+ii+"]";    
                    element.id = group_name+"["+yy+"]["+ii+"]";
                    if(group_name == "GEOL" & ii == 4) {
                        element.style.textAlign = "left";  
                    } else if (group_name == "DETL" & ii == 1) {
                        element.style.textAlign = "left";  
                    } else {
                        element.style.textAlign = "center";  
                    }
                    cell.appendChild(element);
                    var cellloca = element.id;
                    element.onpaste = function(cellloca){
                        fpastecell(cellloca,group_name);
                    }
                }
            }
            // Add button for LOCA
            if(group_name == "LOCA"){
                var cell = row.insertCell(ii);                
                cell.innerHTML = "<input class='button' style='background:gray' type='button' value='add' onClick='LocaButtonGray()'> <i class='icon ion-checkmark-circled' id='"+group_name+"["+yy+"]["+ii+"]' style='color:darkred;font-size:125%'></i>";
                cell.width = 78;
            }
            // Add button for SAMP
            if(group_name == "SAMP"){
                var cell = row.insertCell(ii);
                cell.innerHTML = "<input class='button' style='background:gray' type='button' value='add' onClick='SampButtonGray()'> <i class='icon ion-checkmark-circled' id='"+group_name+"["+yy+"]["+ii+"]' style='color:darkred;font-size:125%'></i>";
                cell.width = 78;
            }
            //number of cells
            var xx = cells.length;
            for(i=0;i<xx;i++){
                if(y == 0 & i == 0) var inival = cells[i];
                var ii = i+eval(colp);
                if(ii < entrylength){                    
                    var targetcell = group_name+"["+yy+"]["+ii+"]";
                    if(group_name == 'LOCA' & (ii == 6 | ii == 7)){
                        document.getElementById(targetcell).value = formatDate(cells[i]);
                    } else if(group_name == 'SAMP' & (ii == 5)){
                        document.getElementById(targetcell).value = formatDate(cells[i]);
                    } else {
                        document.getElementById(targetcell).value = cells[i];
                    }
                }
            }
        }        
    }
    // Expand sub-GPVS menu
    if(group_name == 'GPVS') LoadSubGPVS();

    setTimeout(function(){
        var targetcell = group_name+"["+rowp+"]["+colp+"]";
        document.getElementById(targetcell).value = inival;    
    });    
    // number of entries
    if(yy+1 > ll) {
        document.getElementById(group_name+"_entry").value = yy+1;
    }
}

///////////////////////////////////////////////////////////////////////////////
// Paste from spreadsheet for 1 row
function fpastecell1(paste,group){
    // Grouop name
    var group_name = group;
    if(group_name == "SCPT") group_name = "SCPG";
    
    var cellid = paste.target.id;    
    var cellposition = [cellid.split(/\[|\]/)[1],cellid.split(/\[|\]/)[3]];
    var rowp = cellposition[0];    
    var colp = cellposition[1];    
    var data = paste.clipboardData.getData('text/plain');
    // Col length of table
    var entrylength = document.getElementById(group_name+"_entrylength").value;
    if(group == "OTHR" | group == "FLDP") entrylength = entrylength-1;
    // length of entry
    var table = document.getElementById(group_name+"_entry_table");
//    var l = table.rows.length;    
    
    var rows = data.split(/[\r\n]+/g);     
    //number of rows
    var x = rows.length;
    
    //Add data
    for(y=0;y<x;y++) {
        var yy = y+eval(rowp);        
        if(yy < 1){            
            var cells = rows[y];
            cells = cells.split("\t");
            //number of cells
            var xx = cells.length;
            for(i=0;i<xx;i++){
                if(y == 0 & i == 0) var inival = cells[i];
                var ii = i+eval(colp);
                if(ii < entrylength){
                    var targetcell = group_name+"["+yy+"]["+ii+"]";
                    if(group_name == 'LOCA' & (ii == 6 | ii == 7)){
                        document.getElementById(targetcell).value = formatDate(cells[i]);
                    } else if(group_name == 'SAMP' & (ii == 5)){
                        document.getElementById(targetcell).value = formatDate(cells[i]);
                    } else {
                        document.getElementById(targetcell).value = cells[i];
                    }
                }
            }
        }
    }
    setTimeout(function(){
        var targetcell = group_name+"["+rowp+"]["+colp+"]";
        document.getElementById(targetcell).value = inival;    
    });    
}
///////////////////////////////////////////////////////////////////////////////
// Paste from spreadsheet for field with file input
function fpastecell_file(paste,group){
    // Grouop name
    var group_name = group;
    var cellid = paste.target.id;
    var cellposition = [cellid.split(/\[|\]/)[1],cellid.split(/\[|\]/)[3]];
    var rowp = cellposition[0];    
    var colp = cellposition[1];    
    var data = paste.clipboardData.getData('text/plain');
    // Col length of table
    var entrylength = document.getElementById(group_name+"_entrylength").value;
    // length of entry
    var table = document.getElementById(group_name+"_entry_table");
    var l = table.rows.length;
    // number of entries
    var ll = document.getElementById(group_name+"_entry").value;
    // data  
    var rows = data.split(/[\r\n]+/g);    
    
    var delim = "\t";  
    
    datapaste_file(group_name,rowp,colp,rows,entrylength,l,table,ll,delim);
}

function datapaste_file(group_name,rowp,colp,rows,entrylength,l,table,ll,delim,num=0){
    // Reduce entrylength by 1 for OTHR and FLOB
    entrylength = entrylength - 1;
    l = l - 1;
    //number of rows
    var x = rows.length;
    //Add data    
    for(y=0;y<x;y++) {
        var yy = y+eval(rowp);
//        l = 0;
        if(yy < l){            
            var cells = rows[y];
            if(delim == ','){
                cells = CSVtoArray(cells);
            } else {
                cells = cells.split(delim);
            }
            cells = cells.slice(num,cells.length);
            
            //number of cells
            var xx = cells.length;
            for(i=0;i<xx;i++){
                if(y == 0 & i == 0) var inival = cells[i];
                var ii = i+eval(colp);
                if(ii < entrylength){                    
                    if(group_name == "FLDO" && (ii == 0))
                    {
                        var targetcell = group_name+"["+yy+"]["+ii+"]";
                        var values = cells[ii].split('_');
                        var temp = document.getElementById(targetcell).options;
                        if(values.indexOf('mdsp') != -1) temp[0].selected= true;
                        if(values.indexOf('ldfm') != -1) temp[1].selected= true;
                        if(values.indexOf('sttl') != -1) temp[2].selected= true;
                        if(values.indexOf('sndb') != -1) temp[3].selected= true;
                        if(values.indexOf('pedf') != -1) temp[4].selected= true;
                        if(values.indexOf('ngfl') != -1) temp[5].selected= true;
                    }
                    else 
                    {
                        var targetcell = group_name+"["+yy+"]["+ii+"]";
                        document.getElementById(targetcell).value = cells[ii];                    
                    }                    
                }
            }
        } else {
            //create row
            var row = table.insertRow(yy); 
            var cells = rows[y];
            if(delim == ','){
                cells = CSVtoArray(cells);
            } else {
                cells = cells.split(delim);
            }
            cells = cells.slice(num,cells.length);
             
            //create cells
            for(ii=0;ii<entrylength;ii++){
                var cell = row.insertCell(ii);
                if(group_name == "FLDO" && (ii == 0)){
                    var element = document.createElement("select");
                    element.name = group_name+"["+yy+"]["+ii+"][]";
                    element.id = group_name+"["+yy+"]["+ii+"]";
                    element.className = 'multiselect';
                    element.multiple = true;                
                    var option = document.createElement("option");
                    option.text = 'Measured displ.';
                    option.value = 'mdsp';
                    var values = cells[ii].split('_');
                    if(values.indexOf('mdsp') != -1) option.selected= true;
                    element.add(option);
                    var option = document.createElement("option");
                    option.text = 'Lateral def.';
                    option.value = 'ldfm';
                    if(values.indexOf('ldfm') != -1) option.selected= true;
                    element.add(option);
                    var option = document.createElement("option");
                    option.text = 'Settlement';
                    option.value = 'sttl';
                    if(values.indexOf('sttl') != -1) option.selected= true;
                    element.add(option);
                    var option = document.createElement("option");
                    option.text = 'Sand boil';
                    option.value = 'sndb';
                    if(values.indexOf('sndb') != -1) option.selected= true;
                    element.add(option);
                    var option = document.createElement("option");
                    option.text = 'Post-event def.';
                    option.value = 'pedf';
                    if(values.indexOf('pedf') != -1) option.selected= true;
                    element.add(option);
                    var option = document.createElement("option");
                    option.text = 'No ground failure';
                    option.value = 'ngfl';
                    if(values.indexOf('ngfl') != -1) option.selected= true;
                    element.add(option);
                    cell.appendChild(element);
                } else if(group_name == "FLDO" && ii == 1){
                    var element = document.createElement("select");
                    element.style.width = '100%';
                    element.name = group_name+"["+yy+"]["+ii+"]";
                    element.id = group_name+"["+yy+"]["+ii+"]";                
                    var option = document.createElement("option");
                    option.value = '';
                    option.text = '';
                    element.add(option);
                    var option = document.createElement("option");
                    option.text = 'Displacement Vector Map';
                    option.value = 'dspv';
                    element.add(option);
                    var option = document.createElement("option");
                    option.text = 'Crack Width Transect';
                    option.value = 'crwt';
                    element.add(option);
                    var option = document.createElement("option");
                    option.text = 'Photo';
                    option.value = 'phto';
                    element.add(option);
                    var option = document.createElement("option");
                    option.text = 'LiDAR Image';
                    option.value = 'ldar';
                    element.add(option);
                    var option = document.createElement("option");
                    option.text = 'Satellite Image';
                    option.value = 'stim';
                    element.add(option);
                    var option = document.createElement("option");
                    option.text = 'Georeferrenced Map';
                    option.value = 'geom';
                    element.add(option);
                    var option = document.createElement("option");
                    option.text = 'Field Note';
                    option.value = 'fldn';
                    element.add(option);
                    cell.appendChild(element);
                } else if(group_name == "OTHR" & ii == 1){                
                    var element = document.createElement("select");
                    element.style.width = '100%';
                    element.name = group_name+"["+yy+"]["+ii+"]";
                    element.id = group_name+"["+yy+"]["+ii+"]";                
                    var option = document.createElement("option");
                    option.value = '';
                    option.text = '';
                    element.add(option);
                    var option = document.createElement("option");
                    option.text = 'Consolidation';
                    option.value = 'CONS';
                    element.add(option);
                    var option = document.createElement("option");
                    option.text = 'Triaxial-UU';
                    option.value = 'TXUU';
                    element.add(option);
                    var option = document.createElement("option");
                    option.text = 'Triaxial-CU';
                    option.value = 'TXCU';
                    element.add(option);
                    var option = document.createElement("option");
                    option.text = 'Triaxial-CD';
                    option.value = 'TXCD';
                    element.add(option);
                    var option = document.createElement("option");
                    option.text = 'Triaxial-Cyclic';
                    option.value = 'TXCC';
                    element.add(option);
                    var option = document.createElement("option");
                    option.text = 'Simple Shear-Monotonic';
                    option.value = 'SSMO';
                    element.add(option);
                    var option = document.createElement("option");
                    option.text = 'Simple Shear-Cyclic';
                    option.value = 'SSCY';
                    element.add(option);
                    var option = document.createElement("option");
                    option.text = 'Other';
                    option.value = 'OTHT';
                    element.add(option);                
                    cell.appendChild(element);
                } else {
                    var element = document.createElement("input");
                    element.type = "text";            
                    element.name = group_name+"["+yy+"]["+ii+"]";
                    element.id = group_name+"["+yy+"]["+ii+"]";
                    element.value = '';
                    element.style.textAlign = "center";  
                    cell.appendChild(element);
                    var cellloca = element.id;
                        element.onpaste = function(cellloca){
                        fpastecell_file(cellloca,group_name);
                    }
                }
            }
            if(group_name == "FLDO" && ii == 4){
                element.style.textAlign = "left";
            } else {
                element.style.textAlign = "center";
            }
            // Cell width
            if(group_name == "FLDO"){
                var width = new Array("130","130","70","70","250");
                cell.style.width = width[ii]+"px";
            }
            // File Add button for FLDO
            var cell = row.insertCell(ii); 
            cell.innerHTML = "<input class='inputfile' type='file' name='"+group_name+"_add_file["+yy+"]' id='"+group_name+"_add_file"+yy+"' onchange=nameUpload('"+group_name+"','"+yy+"')><label for='"+group_name+"_add_file"+yy+"'>Choose a file</label> <input style='width:60px;border: none;background-color:#ADC3DF' id='"+group_name+"_added_file"+yy+"' readonly></center>";
        
            cell.style.width = "180px";
            // Uploaded file
            var cell = row.insertCell(ii+1);            
            cell.innerHTML = "";
            cell.style.width = "100px";
            cell.style.textAlign = "left";

            //Update cell values
            var xx = cells.length;
            for(i=0;i<xx;i++){
                if(y == 0 & i == 0) var inival = cells[i];
                var ii = i+eval(colp);
                if(ii < entrylength){                    
                    var targetcell = group_name+"["+yy+"]["+ii+"]";
                    if(group_name == 'FLDO' & ii > 0){
                        document.getElementById(targetcell).value = cells[i];
                    } else if(group_name == 'OTHR'){
                        document.getElementById(targetcell).value = cells[i];
                    }                    
                }
            }
        }        
    }
    // for multiple selection
    
    if(group_name == 'FLDO'){
        $(function(){
            $(".multiselect").multiselect({
                header: false
            });
        });    
        $(".multiselect").multiselect('refresh');
    } 

    // inival only col > 1
    setTimeout(function(){
        var targetcell = group_name+"["+rowp+"]["+colp+"]";
        if(colp > 0){
            document.getElementById(targetcell).value = inival;    
        }        
    });    
    // number of entries
    if(yy+1 > ll) {
        document.getElementById(group_name+"_entry").value = yy+1;
    }
    
}
///////////////////////////////////////////////////////////////////////////////
// Additional file 
// Add rows
function add_file(row,group){    
    var group_name = group;
    var x = document.getElementById(group_name+"_add_num").value;    
    if($.isNumeric(x) == false){
        x = 0;
    } 
    // Add add_num + 1
    x = Number(x) + 1;
    document.getElementById(group_name+"_add_num").value = x;
    // Add one row
    var table = document.getElementById(group_name+"_entry_table");
    var idx_row = row.parentNode.parentNode.rowIndex;      
    /////////////////////////////////////////////////////////////////
    var row = table.insertRow(idx_row);    
    var cell = row.insertCell(0);
    var idx = Number(idx_row)-1;
    
    // NAME
    cell.innerHTML = "Name";
    cell.style.width = "6%";
    var cell = row.insertCell(1);
    cell.innerHTML = "<input type='text' name='"+group_name+"_add_name["+idx+"]'><br>";
    cell.style.width = "9%";
    // Description
    var cell = row.insertCell(2);
    cell.innerHTML = "Description";
    cell.style.width = "9%";
    cell.style.textAlign = "left";
    var cell = row.insertCell(3);
    cell.innerHTML = "<input type='text' name='"+group_name+"_add_desc["+idx+"]' placeholder='Description'>";
    cell.style.width = "21%";
    // File upload
    var cell = row.insertCell(4);    
    cell.innerHTML = "File upload";
    cell.style.width = "8%";
    cell.style.textAlign = "right";
    var cell = row.insertCell(5);
    
    cell.innerHTML = "<input class='inputfile' type='file' name='"+group_name+"_add_file["+idx+"]' id='"+group_name+"_add_file"+idx+"' onchange=nameUpload('"+group_name+"','"+idx+"')><label for='"+group_name+"_add_file"+idx+"'>Choose a file</label> <input style='width:60px;border: none;background-color:#ADC3DF' id='"+group_name+"_added_file"+idx+"' readonly></center>";
    
    cell.style.width = "22%";
    // Uploaded file
    var cell = row.insertCell(6);
    cell.innerHTML = "";
    cell.style.width = "25%";
    cell.style.textAlign = "left";
}
///////////////////////////////////////////////////////////////////////////////
// Additional file for group OTHR
// Add rows
function add_file_OTHR(row,group){    
    var group_name = group;
    var x = document.getElementById(group_name+"_add_num").value;    
    if($.isNumeric(x) == false){
        x = 0;
    } 
    // Add add_num + 1
    x = Number(x) + 1;
    document.getElementById(group_name+"_add_num").value = x;
    // Add one row
    var table = document.getElementById(group_name+"_entry_table");
    var idx_row = row.parentNode.parentNode.rowIndex;      
    /////////////////////////////////////////////////////////////////
    var row = table.insertRow(idx_row);    
    var idx = Number(idx_row);
    var entrylength = 4;
    for(i=0;i<entrylength;i++){
        var cell = row.insertCell(i);       
        if(group_name == "OTHR" & i == 1){                
            var element = document.createElement("select");
            element.style.width = '100%';
            element.name = group_name+"["+idx+"]["+i+"]";
            element.id = group_name+"["+idx+"]["+i+"]";                
            var option = document.createElement("option");
            option.value = '';
            option.text = '';
            element.add(option);
            var option = document.createElement("option");
            option.text = 'Consolidation';
            option.value = 'CONS';
            element.add(option);
            var option = document.createElement("option");
            option.text = 'Triaxial-UU';
            option.value = 'TXUU';
            element.add(option);
            var option = document.createElement("option");
            option.text = 'Triaxial-CU';
            option.value = 'TXCU';
            element.add(option);
            var option = document.createElement("option");
            option.text = 'Triaxial-CD';
            option.value = 'TXCD';
            element.add(option);
            var option = document.createElement("option");
            option.text = 'Triaxial-Cyclic';
            option.value = 'TXCC';
            element.add(option);
            var option = document.createElement("option");
            option.text = 'Simple Shear-Monotonic';
            option.value = 'SSMO';
            element.add(option);
            var option = document.createElement("option");
            option.text = 'Simple Shear-Cyclic';
            option.value = 'SSCY';
            element.add(option);
            var option = document.createElement("option");
            option.text = 'Other';
            option.value = 'OTHT';
            element.add(option);                
            cell.appendChild(element);                    
        } else {               
            var element = document.createElement("input");
            element.type = "text";            
            element.name = group_name+"["+idx+"]["+i+"]";
            element.id = group_name+"["+idx+"]["+i+"]";
            element.style.textAlign = "center";  
            cell.appendChild(element);
            var cellloca = element.id;
            element.onpaste = function(cellloca){
                fpastecell_file(cellloca,group_name);
            }
        }            
    cell.style.width = ((980-400)/(entrylength)-6)+"px";
    cell.style.textAlign = "center";
    }
    // File Add button for OTHR
    var cell = row.insertCell(i); 
    
    cell.innerHTML = "<input class='inputfile' type='file' name='"+group_name+"_add_file["+idx+"]' id='"+group_name+"_add_file"+idx+"' onchange=nameUpload('"+group_name+"','"+idx+"')><label for='"+group_name+"_add_file"+idx+"'>Choose a file</label> <input style='width:60px;border: none;background-color:#ADC3DF' id='"+group_name+"_added_file"+idx+"' readonly></center>";
        
    cell.style.width = "200px";            
    // Uploaded file
    var cell = row.insertCell(i+1);            
    cell.innerHTML = "";
    cell.style.width = "200px";
    cell.style.textAlign = "left";
}
///////////////////////////////////////////////////////////////////////////////
// Additional file for group FLDO
// Add rows
function add_file_FLDO(row,group){
    var group_name = group;
    var x = document.getElementById(group_name+"_add_num").value;    
    if($.isNumeric(x) == false){
        x = 0;
    } 
    // Add add_num + 1
    x = Number(x) + 1;
    document.getElementById(group_name+"_add_num").value = x;
    // Add one row
    var table = document.getElementById(group_name+"_entry_table");
    var idx_row = row.parentNode.parentNode.rowIndex;      
    /////////////////////////////////////////////////////////////////
    var row = table.insertRow(idx_row);
    var width = new Array("130","130","70","70","250");
    var idx = Number(idx_row);
    var entrylength = 5;
    for(i=0;i<entrylength;i++){
        var cell = row.insertCell(i);       
        if(group_name == "FLDO" && (i == 0)){
            var element = document.createElement("select");
            element.name = group_name+"["+idx+"]["+i+"][]";
            element.id = group_name+"["+idx+"]["+i+"]";
            element.className = 'multiselect';
            element.multiple = true;                
            var option = document.createElement("option");
            option.text = 'Measured displ.';
            option.value = 'mdsp';
            element.add(option);
            var option = document.createElement("option");
            option.text = 'Lateral def.';
            option.value = 'ldfm';                
            element.add(option);
            var option = document.createElement("option");
            option.text = 'Settlement';
            option.value = 'sttl';                
            element.add(option);
            var option = document.createElement("option");
            option.text = 'Sand boil';
            option.value = 'sndb';
            element.add(option);
            var option = document.createElement("option");
            option.text = 'Post-event def.';
            option.value = 'pedf';
            element.add(option);
            var option = document.createElement("option");
            option.text = 'No ground failure';
            option.value = 'ngfl';
            element.add(option);
            cell.appendChild(element);
        } else if(group_name == "FLDO" && i == 1){
            var element = document.createElement("select");
            element.style.width = '100%';
            element.name = group_name+"["+idx+"]["+i+"]";
            element.id = group_name+"["+idx+"]["+i+"]";                
            var option = document.createElement("option");
            option.value = '';
            option.text = '';
            element.add(option);
            var option = document.createElement("option");
            option.text = 'Displacement Vector Map';
            option.value = 'dspv';
            element.add(option);
            var option = document.createElement("option");
            option.text = 'Crack Width Transect';
            option.value = 'crwt';
            element.add(option);
            var option = document.createElement("option");
            option.text = 'Photo';
            option.value = 'phto';
            element.add(option);
            var option = document.createElement("option");
            option.text = 'LiDAR Image';
            option.value = 'ldar';
            element.add(option);
            var option = document.createElement("option");
            option.text = 'Satellite Image';
            option.value = 'stim';
            element.add(option);
            var option = document.createElement("option");
            option.text = 'Georeferrenced Map';
            option.value = 'geom';
            element.add(option);
            var option = document.createElement("option");
            option.text = 'Field Note';
            option.value = 'fldn';
            element.add(option);
            cell.appendChild(element);
        } else {
            var element = document.createElement("input");
            element.type = "text";            
            element.name = group_name+"["+idx+"]["+i+"]";
            element.id = group_name+"["+idx+"]["+i+"]";
            element.value = '';
            element.style.textAlign = "center";  
            cell.appendChild(element);
            var cellloca = element.id;
                element.onpaste = function(cellloca){
                fpastecell_file(cellloca,group_name);
            }
        }
        if(i == 4){
            element.style.textAlign = "left";
        } else {
            element.style.textAlign = "center";
        }
        cell.style.width = width[i]+"px";
    }
    // File Add button for FLDO
    var cell = row.insertCell(i); 
    
    cell.innerHTML = "<input class='inputfile' type='file' name='"+group_name+"_add_file["+idx+"]' id='"+group_name+"_add_file"+idx+"' onchange=nameUpload('"+group_name+"','"+idx+"')><label for='"+group_name+"_add_file"+idx+"'>Choose a file</label> <input style='width:60px;border: none;background-color:#ADC3DF' id='"+group_name+"_added_file"+idx+"' readonly></center>";
        
    cell.style.width = "180px";
    // Uploaded file
    var cell = row.insertCell(i+1);            
    cell.innerHTML = "";
    cell.style.width = "100px";
    cell.style.textAlign = "left";
    
    // for multiple selection
    $(function(){
        $(".multiselect").multiselect({
            header: false
        });
    });
}
///////////////////////////////////////////////////////////////////////////////
// Del rows
function del_file(row,group) {
    // Delete add_num
    var group_name = group;
    var x = document.getElementById(group_name+"_add_num").value;
    x = Number(x) - 1;
    document.getElementById(group_name+"_add_num").value = x;    
    var add_row = document.getElementById(group_name+"_add_row");
    var idx_add = add_row.rowIndex;    
    var table = document.getElementById(group_name+"_entry_table");
    var idx = row.parentNode.parentNode.rowIndex;
    if((idx-1)>idx_add){
        table.deleteRow(idx-1);
    }
    if((idx-1) == 0 & group_name == 'OTHR'){
        table.deleteRow(idx-1);
        add_file_OTHR(row,group_name);
    } else if(idx-1 == 0 & group_name == 'FLDO'){
        table.deleteRow(idx-1);
        add_file_FLDO(row,group_name);
    } 
    else if(idx-1 == 1 & group_name != 'OTHR' & group_name != 'FLDO') add_file(row,group);
}
///////////////////////////////////////////////////////////////////////////////
// Conduct Ground Motion Model
function ConductGMM(fault_info,site_info,ROWS){
    // Table for submit button
    var rows = ROWS;
    table = document.getElementById('GMMP_entry_table');
    var row = table.insertRow(1);
    var cell = row.insertCell(0);
    cell.innerHTML = "<input class='button-large' id='GMM_run' type='button' value='Run'>";
    cell.colSpan = 10;
    cell.style.textAlign = 'right';
    
    // Site info    
    var lat = Number(site_info[0][1]);
    var lon = Number(site_info[0][2]);
    // Fault segments info
    var Rjb = new Array();
    var Rrup = new Array();
    var Rx = new Array();
    var fault_length = fault_info.length;
    for(i=0;i<fault_length;i++){
        var strike = Number(fault_info[i][1]);
        var dip = Number(fault_info[i][2]);
        var rake = Number(fault_info[i][3]);
        var length = Number(fault_info[i][4]);
        var width = Number(fault_info[i][5]);
        var lat_ulc = Number(fault_info[i][6]);
        var lon_ulc = Number(fault_info[i][7]);
        var dep_ulc = Number(fault_info[i][8]);
        // Rjb and Rrup calculation
        var [rjb,rrup,rx] = Rjbrupx(length, width, strike, dip, lat_ulc, lon_ulc, lat, lon, dep_ulc);
        Rjb[i] = rjb;
        Rrup[i] = rrup;
        Rx[i] = rx;
    }
    var RJB = Math.min(Rjb).toFixed(1);
    var RRUP = Math.min(Rrup).toFixed(1);
    var RX = Math.min(Rx).toFixed(1);
    // Fill Rjb and Rrup 
    document.getElementById('GMMP[0][3]').value = RRUP;
    document.getElementById('GMMP[0][4]').value = RJB;
    document.getElementById('GMMP[0][5]').value = RX;
}
///////////////////////////////////////////////////////////////////////////////
// Ground Motion Model
function GMM(fault_info,eq_info){
//    document.getElementById('ServerMsg').value = "GMM calculation is under construction!";
//    alert("GMM calculation is under construction!");
    
    if(document.getElementById('GMMP[0][0]').value != '') var vs30 = Number(document.getElementById('GMMP[0][0]').value);
    else var vs30 = -999;
    if(document.getElementById('GMMP[0][1]').value != '') var z10 = Number(document.getElementById('GMMP[0][1]').value);
    else var z10 = -999;
    if(document.getElementById('GMMP[0][2]').value != '') var z25 = Number(document.getElementById('GMMP[0][2]').value);
    else var z25 = -999;
    var Rrup = Number(document.getElementById('GMMP[0][3]').value);
    var Rjb = Number(document.getElementById('GMMP[0][4]').value);
    var Rx = Number(document.getElementById('GMMP[0][5]').value);
    var region = document.getElementById('GMMP[0][6]').value;
    var GMM = document.getElementById('GMMP[0][7]').value;
    var BEres = Number(document.getElementById('GMMP[0][8]').value);
    var WEres = Number(document.getElementById('GMMP[0][9]').value);
    // Event info
    var m = Number(eq_info[0][4]);
    var Zhyp = Number(eq_info[0][3]);
    var dip = Number(fault_info[0][2]);
    var rake = Number(fault_info[0][3]);
    var length = Number(fault_info[0][4]);
    var width = Number(fault_info[0][5]);
    var depth = Number(fault_info[0][8]);
    // Mechanism based on rake (Ancheta et al. 2013)
    // Strike-slip
    if((rake >= -180 && rake < -150) || (rake >= -30 && rake < 30) || (rake >= 150 && rake < 180)){
        var mech = 'ss';        
    // Normal or Normal-Oblique    
    }else if((rake >= -120 && rake < -60) || (rake >= -150 && rake < -120) || (rake >= -60 && rake < -30)){
        var mech = 'nor';        
    // Reverse or Reverse-Oblique
    }else if((rake >= 60 && rake < 120) || (rake >= 30 && rake < 60) || (rake >= 120 && rake < 150)){
        var mech = 'rev';        
    }
    // Target period
    var T = [0,-1,0.2,1.0,3.0];
    // Depth to top of rupture plane. Use if Zhyp is unknown.
    var Zbot = -999;
    // Depth to bottom of rupture plane.  Use if Zhyp is unknown.
    var Zbor = -999;
    if(GMM === 'CB14'){
        var out = CB14(m,Rrup,Rjb,Rx,dip,depth,width,length,Zhyp,vs30,z25,mech,region,T,Zbot,Zbor);        
    }
    // Fill PGA, PGV, and PSA
    for(i=0;i<5;i++){
        document.getElementById('GRMN[0]['+i+']').value = out[i].toFixed(5);    
    }
}
///////////////////////////////////////////////////////////////////////////////
// Fill data from file
function uploadFile(group_name){
    var fileinput = document.getElementById(group_name+'_file');
    var inputfile = fileinput.files[0];
    document.getElementById(group_name+"_uploadFile").value = inputfile.name;
    var reader = new FileReader();
    
    reader.onload = function(e) {        
        var text = reader.result;
        var rows = text.split("\n");
        var l = rows.length;
        var ind = 'off';
        var idx1 = null;
        var idx2 = null;
        for(i=0;i<l;i++){
            var cells = rows[i].split(",");            
            if(cells[1] == group_name){
                idx1 = i;
                ind = 'on';
            } else if (ind == 'on' & cells[0] == 'GROUP'){
                idx2 = i-2;
                ind = 'off';                
            } else if (ind == 'on'){
                idx2 = i;                
            }            
        }

        if(idx1 !== null){
            var out = rows.slice(idx1+4,idx2+1);
            
            // Location ID match for HDPH, SCPG, TEPT, GPVS, GEOL, DETL, ISPT, SCPT, GSWD, GSWV, GDHL, GCHL, GSPL, SAMP
            var locaid_sp = ['HDPH', 'SCPG', 'TEPT', 'GPVS', 'GEOL', 'DETL', 'ISPT', 'SCPT', 'GSWD', 'GSWV', 'GDHL', 'GCHL', 'GSPL', 'SAMP', 'LABG', 'INDX', 'GRAT', 'OTHR'];
            var sampid_sp = ['LABG', 'INDX', 'GRAT', 'OTHR'];
            
            if(locaid_sp.indexOf(group_name)>=0){
                var loca_id = document.getElementById('loca_id').value;
                l = out.length;
                var j = 0;
                var out_temp = [];   
                for(i=0;i<l;i++){
                    var cells = out[i].split(",");
                    if(cells[1] == loca_id){
                        out_temp[j] = out[i];
                        j++;
                    }
                }
                out = out_temp;

                // Sample ID match for LABG, INDX, GRAT, OTHR
                if(sampid_sp.indexOf(group_name)>=0){
                    var samp_id = document.getElementById('samp_id').value;
                    l = out.length;
                    var j = 0;
                    var out_temp = [];   
                    for(i=0;i<l;i++){
                        var cells = out[i].split(",");
                        if(cells[2] == samp_id){
                            out_temp[j] = out[i];
                            j++;
                        }
                    }
                    out = out_temp;
                }
            }
            
            if(out.length >= 0){
                fileUpload(group_name,out);
            }
        }        
    }    
    reader.readAsText(inputfile);
}
///////////////////////////////////////////////////////////////////////////////
// Show uploaded file name
function nameUpload(group_name,i){
    var fileinput = document.getElementById(group_name+'_add_file'+i);
    var inputfile = fileinput.files[0];                
    document.getElementById(group_name+"_added_file"+i).value = inputfile.name;
}

// Count an array
function count(array,element){
  var counts = [];
    for (ii = 0; ii < array.length; ii++){
      if (array[ii] === element) {  
        counts.push(ii);
      }
    }
  return counts;
}
