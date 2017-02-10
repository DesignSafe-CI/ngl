<?php
////////////////////////////////////////////////////////////////////////////////////
// Plot focal mechanism 
// Focal mechanism Matlab code source
// http://www.ceri.memphis.edu/people/olboyd/Software/Software.html
// PHPlot
// http://www.phplot.com

require 'phplot/phplot.php';
$plot = new PHPlot_truecolor(50,50);
// File format
$plot->SetFileFormat('gif');
// center
$cen = 100;

// Strike, dip, rake
$strike1 = $_GET["strike"] - 90;
$dip1 = $_GET["dip"];
$rake1 = $_GET["rake"];
//$strike1 = 20-90;
//$dip1 = 70;
//$rake1 = 40;

# Disable auto-output:
$plot->SetPrintImage(0);
////////////////////////////////////////////////////////////////////////////////////
// Circle
$th = range(0,2*pi(),0.01);
$data_circle = array();
foreach($th as $value){
    $xunit = cos($value)*90;
    $yunit = sin($value)*90;
    $data_circle[] = array('',$xunit+$cen,$yunit+$cen);
}

////////////////////////////////////////////////////////////////////////////////////
// Radial circle1
if($rake1 >= 180){
    $rake1 = $rake1-180;  
    $P = 2;
} else if($rake1 < 0){
    $rake1 = $rake1+180;
    $P = 2;    
} else {
    $P = 1;
}
$phi = range(0,pi(),0.01);
if($dip1 >= 90) $dip1 = 89.9999;
$d = 90 - $dip1;
$m = 90;
$data1 = array();
foreach($phi as $value){
    $rho = sqrt(pow($d,2)/(pow(sin($value),2) + pow(cos($value),2) * pow($d/$m,2)));
    $theta = $value + deg2rad($strike1);    
    $xx = $rho*cos($theta);
    $yy = $rho*sin($theta);
    $X1[] = $xx+$cen;
    $Y1[] = -$yy+$cen;
    
    $data1[] = array('',$xx+$cen,-$yy+$cen);
}

////////////////////////////////////////////////////////////////////////////////////
// Radial circle2
$z = deg2rad($strike1+90);
$z2 = deg2rad($dip1);
$z3 = deg2rad($rake1);
// slick vector in plane 1 
$sl1 = -cos($z3)*cos($z)-sin($z3)*sin($z)*cos($z2);
$sl2 = cos($z3)*sin($z)-sin($z3)*cos($z)*cos($z2);
$sl3 = sin($z3)*sin($z2);
// strike
if ($sl3 < 0){
    $sl1_t = -$sl1;
    $sl2_t = -$sl2;
    $sl3_t = -$sl3;
} else {
    $sl1_t = $sl1;
    $sl2_t = $sl2;
    $sl3_t = $sl3;
}
$strike2 = rad2deg(atan2($sl1_t,$sl2_t));
$strike2 = $strike2-90;

// dip
$x = sqrt($sl2_t*$sl2_t + $sl1_t*$sl1_t);
$dip2 = rad2deg(atan2($x,$sl3_t));
if($dip2 >= 90) $dip2 = 89.9999;
// rake
$n1 = sin($z)*sin($z2);
$n2 = cos($z)*sin($z2);
$n3 = cos($z2);
$h1 = -$sl2;
$h2 = $sl1;
$z = $h1*$n1 + $h2*$n2;
$z = $z/sqrt($h1*$h1 + $h2*$h2);
$z = acos($z);
if($sl3 > 0){
    $rake2 = rad2deg($z);
} else {
    $rake2 = -rad2deg($z);
}
$d = 90 - $dip2;
$m = 90;
$data2 = array();
foreach($phi as $value){
    $rho = sqrt(pow($d,2)/(pow(sin($value),2) + pow(cos($value),2) * pow($d/$m,2)));
    $theta = $value + deg2rad($strike2);
    $xx = $rho*cos($theta);
    $yy = $rho*sin($theta);
    $X2[] = $xx+$cen;
    $Y2[] = -$yy+$cen;
    
    $data2[] = array('',$xx+$cen,-$yy+$cen);
}

////////////////////////////////////////////////////////////////////////////////////
// Fill color
$inc = 1;
if($P == 1){
    # range for red
    $lo1 = $strike1-180;
    $hi1 = $strike2;
    if($lo1 > $hi1) $inc = -$inc;
    $th1 = range($lo1,$hi1,$inc);
    $th2 = range($strike2+180,$strike1,-$inc);
    foreach($th1 as $value){
        $Xs1[] = (90*cos(-deg2rad($value))+$cen);
        $Ys1[] = -(90*sin(-deg2rad($value))+$cen);
    }
    foreach($th2 as $value){
        $Xs2[] = (90*cos(-deg2rad($value))+$cen);
        $Ys2[] = -(90*sin(-deg2rad($value))+$cen);
    }
    # range for white
    $thw1 = range($hi1,$strike1,$inc);
    $thw2 = range($strike1+180,$strike2+180,-$inc);
    foreach($thw1 as $value){
        $Xsw1[] = (90*cos(-deg2rad($value))+$cen);
        $Ysw1[] = -(90*sin(-deg2rad($value))+$cen);
    }
    foreach($thw2 as $value){
        $Xsw2[] = (90*cos(-deg2rad($value))+$cen);
        $Ysw2[] = -(90*sin(-deg2rad($value))+$cen);
    }
    
    
} else {
    # range for red
    $hi1 = $strike1-180;
    $lo1 = $strike2-180;
    if($lo1 > $hi1) $inc = -$inc;
    $th1 = range($hi1,$lo1,-$inc);
    $th2 = range($strike2,$strike1,-$inc);
    foreach($th1 as $value){
        $Xs1[] = (90*cos(-deg2rad($value))+$cen);
        $Ys1[] = -(90*sin(-deg2rad($value))+$cen);
    }
    foreach($th2 as $value){
        $Xs2[] = (90*cos(-deg2rad($value))+$cen);
        $Ys2[] = -(90*sin(-deg2rad($value))+$cen);
    }
    # range for white
    $thw1 = range($lo1,$strike1,$inc);
    $thw2 = range($strike1+180,$strike2,-$inc);
    foreach($thw1 as $value){
        $Xsw1[] = (90*cos(-deg2rad($value))+$cen);
        $Ysw1[] = -(90*sin(-deg2rad($value))+$cen);
    }
    foreach($thw2 as $value){
        $Xsw2[] = (90*cos(-deg2rad($value))+$cen);
        $Ysw2[] = -(90*sin(-deg2rad($value))+$cen);
    }
}

if($P == 1){
    $X3 = array_merge($X2,$Xs2,$X1,$Xs1);
    $Y3 = array_merge($Y2,$Ys2,$Y1,$Ys1);
    $X4 = array_merge($X1,$Xsw2,$X2,$Xsw1);
    $Y4 = array_merge($Y1,$Ysw2,$Y2,$Ysw1);
    $X5 = array_merge($X1,$X2);
    $Y5 = array_merge($Y1,$Y2);        
} else {
    $X3 = array_merge($X1,$Xs1,$X2,$Xs2);
    $Y3 = array_merge($Y1,$Ys1,$Y2,$Ys2);
    $X4 = array_merge($X2,$Xsw1,$X1,$Xsw2);
    $Y4 = array_merge($Y2,$Ysw1,$Y1,$Ysw2);
    $X5 = array_merge($X1,$Xs1,$X2,$Xs2);
    $Y5 = array_merge($Y1,$Ys1,$Y2,$Ys2);
}

$l3 = sizeof($X3)-1;
// Red color
for($i = 0; $i <= $l3; $i++){    
    $data3[] = array('',$X3[$i],$Y3[$i]);
}

// White color
$l4 = sizeof($X4)-1;
for($i = 0; $i <= $l4; $i++){    
    $data4[] = array('',$X4[$i],$Y4[$i]);
}
// Additional red color
$l5 = sizeof($X5)-1;
for($i = 0; $i <= $l5; $i++){    
    $data5[] = array('',$X5[$i],$Y5[$i]);
}



////////////////////////////////////////////////////////////////////////////////////
// Outer space
$th3 = range(0+90,360+90,1);
foreach($th3 as $value){
    $Xcircle[] = -90*cos(-deg2rad($value))+$cen;
    $Ycircle[] = (90*sin(-deg2rad($value))+$cen);    
}
$Xsquare = array($cen,2*$cen,2*$cen,0,0,$cen);
$Ysquare = array(0,0,2*$cen,2*$cen,0,0);
$Xout = array_merge($Xsquare,$Xcircle);
$Yout = array_merge($Ysquare,$Ycircle);

for($i = 0; $i <= (sizeof($Xout)-1); $i++){    
    $data6[] = array('',$Xout[$i],$Yout[$i]);
}


# axes
$plot->SetXTickPos('none');
$plot->SetXTickLabelPos('none');
$plot->SetXDataLabelPos('none');
$plot->SetYTickPos('none');
$plot->SetYTickLabelPos('none');
$plot->SetPlotBorderType('none');
$plot->SetDrawXGrid(False);
$plot->SetDrawYGrid(False);
$plot->SetDrawXAxis(False);
$plot->SetDrawYAxis(False);
$plot->SetMarginsPixels(0, 0, 0, 0); 

# SetTransparentColor
$plot->SetBackgroundColor('white');
$plot->SetTransparentColor('yellow');

# Set plot area
$plot->SetPlotAreaWorld(0,0,2*$cen,2*$cen);

# Fill red
$plot->SetPlotType('area');
$plot->SetDataValues($data3);
$plot->SetDataType('data-data');
$plot->SetDataColors('red');
$plot->DrawGraph();

if($P == 1 & $strike2 > -180 & $strike2 < 0 & $strike1 > 0){
    # Fill white for normal fault
    $plot->SetPlotType('area');
    $plot->SetDataValues($data4);
    $plot->SetDataType('data-data');
    $plot->SetDataColors('white');
    $plot->DrawGraph();    
    
    # Fill additional red
    $plot->SetPlotType('area');
    $plot->SetDataValues($data5);
    $plot->SetDataType('data-data');
    $plot->SetDataColors('red');
    $plot->DrawGraph();
} else if($P == 2){
    if($strike2 < -180 | $strike2 > 0){
        # Fill white for normal fault
        $plot->SetPlotType('area');
        $plot->SetDataValues($data4);
        $plot->SetDataType('data-data');
        $plot->SetDataColors('white');
        $plot->DrawGraph();
    }
}
//print_r($strike1);
    
# Plot for plane1
$plot->SetPlotType('lines');
$plot->SetDataValues($data1);
$plot->SetDataType('data-data');
$plot->SetDataColors('red');
$plot->DrawGraph();

# Plot for plane2
$plot->SetPlotType('lines');
$plot->SetDataValues($data2);
$plot->SetDataType('data-data');
$plot->SetDataColors('red');
$plot->DrawGraph();

# Fill outer space
$plot->SetPlotType('area');
$plot->SetDataValues($data6);
$plot->SetDataType('data-data');
$plot->SetDataColors('yellow');
$plot->DrawGraph();

# Plot for circle
$plot->SetPlotType('lines');
$plot->SetDataValues($data_circle);
$plot->SetDataType('data-data');
$plot->SetDataColors('black');
$plot->DrawGraph();
    
# Output the image now:
$plot->PrintImage();
?>
<!--<html><body></body></html>-->