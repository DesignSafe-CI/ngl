// The Javascript to compute Rjb, Rrup, and Rx;
// Coded by Pengfei Wang at UCLA on 6/28/2016;
// The main idea is: 1) Cartesian coordinate system, 2) Optimum problem,
// 3) numerical solution (Matlab--fminsearch) or approximate solution here we used. 
// 
// Note: Rjb is 2D problem, Rrup is 3D problem. The rupture plane is the x-y plane. 
// 
// Note: it is only for one fualt, if more than one, use this function to compute 
// again and again. 
//
// Note: upper left corner (ULC) means top west point ????? and because the distance is not
// so far, we can still assume it is plane not a circle
// 
// Variables
// length; // length of rupture, km
// width; // width of rupture, km
// strike; // strike of rupture, deg
// dip; // dip of rupture, deg
// lat_ulc; // latitude: upper left corner, deg
// long_ulc; // longitude: upper left corner, deg
// lat; // latitude: site, deg
// long; // longitude: site, deg
// depth; // Ztor depth, km
//
// Output
// [Rjb, Rrup, Rx]

//////////////// inpur parameters 
function Rjbrupx(length, width, strike, dip, lat_ulc, long_ulc, lat, long, depth) {

    var R = 6371.004; // radius of earth

    if (long_ulc < 0){
        long_ulc = 360 + long_ulc;
    };
    if (long < 0) {
        long = 360 + long; 
    };

    ///////////////// Cartesian coordinate system for Rjb - 2D
    var xjb = width * Math.cos(dip / 180 * Math.PI); // Rjb project plane width
    var yjb = length; // Rjb project plane length  

    var temp_x = R * Math.cos((lat + lat_ulc) / 180 * Math.PI / 2) * Math.sin((long-long_ulc) / 180 * Math.PI) ; // x axis
    var temp_y = R * Math.sin((lat-lat_ulc) / 180 * Math.PI); // y axis
    var x_site2 = temp_x * Math.cos(strike / 180 * Math.PI) - temp_y * Math.sin(strike / 180 * Math.PI); // x coordinate for site
    var y_site2 = temp_x * Math.sin(strike / 180 * Math.PI) + temp_y * Math.cos(strike / 180 * Math.PI); // y coordinate for site
    var x2 = temp_x;
    var y2 = temp_y;
    var x_s2 = x_site2;
    var y_s2 = y_site2;
    ////////////////// Cartesian coordinate system for Rrup - 3D
    var xrup = width; // Rrup plane width
    var yrup = length; // Rrup plane length
    var zrup = 0; // Rrup plane heigth

    var temp_x = R * Math.cos((lat + lat_ulc) / 180 * Math.PI / 2) * Math.sin((long-long_ulc) / 180 * Math.PI) ; // x axis
    var temp_y = R * Math.sin((lat-lat_ulc) / 180 * Math.PI); // y axis
    var x_site3 = (temp_x * Math.cos(strike / 180 * Math.PI) - temp_y * Math.sin(strike / 180 * Math.PI)) * Math.cos(dip / 180 * Math.PI) - depth * Math.sin(dip / 180 * Math.PI); // x coordinate for site
    var y_site3 = temp_x * Math.sin(strike / 180 * Math.PI) + temp_y * Math.cos(strike / 180 * Math.PI); // y coordinate for site
    var z_site3 = (temp_x * Math.cos(strike / 180 * Math.PI) - temp_y * Math.sin(strike / 180 * Math.PI)) * Math.sin(dip / 180 * Math.PI) + depth * Math.cos(dip / 180 * Math.PI); // z coordinate for site
    var x3 = temp_x;
    var y3 = temp_y;
    var z3 = 0;
    var x_s3 = x_site3;
    var y_s3 = y_site3;
    var z_s3 = z_site3;
    return fminsearch(x2, y2, x_s2, y_s2, x3, y3, z3, x_s3, y_s3, z_s3);
}

//////////////////// Numerical solution
function fminsearch(x2, y2, x_s2, y_s2, x3, y3, z3, x_s3, y_s3, z_s3) {
    if (x_s2 < 0) {
        var i = 0;
    } else if(x_s2 > x2) {
        var i = x2;
    } else {
        var i = x_s2;
    }
    if (y_s2 < 0) {
        var j = 0;
    } else if (y_s2 > y2) {
        var j = y2;
    } else {
        var j = y_s2;
    }

    var rjb = Math.sqrt(Math.pow(i - x_s2, 2) + Math.pow(j - y_s2, 2));

    if (x_s3 < 0) {
        var i = 0;
    } else if(x_s3 > x3) {
        var i = x3;
    } else {
        var i = x_s3;
    }
    if (y_s3 < 0) {
        var j = 0;
    } else if (y_s3 > y3) {
        var j = y3;
    } else {
        var j = y_s3;
    }

    var rrup = Math.sqrt(Math.pow(i - x_s3, 2) + Math.pow(j - y_s3, 2) + Math.pow(z_s3, 2));
    var rx = x_s2; 
    return [rjb, rrup, rx];
}
