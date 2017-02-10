// the code is for Campbell and Bozorgnia Ground Motion Model.
// it is coded by Pengfei Wang at UCLA on 6/29/2016. 
// 
// Variable
//m: Magnitude, moment magnitude
//Rrup: Rrup distance, km
//Rjb: Rjb distance, km
//Rx: Rx distance, km
//dip: dip angle, deg
//depth: Ztor depth, km
//width: width of fault, km
//length: length of fault, km
//Frv: 1 for reverse fault, 0 for others
//Fnm: 1 for normal fault, 0 for others
//Vs30: average velocity for upper 30m soil layer, m/s
//z25: depth of velocity equals to 2.5 km/s, km
//Sj: regional site effect, 1 for Japan, 0 for others
//Zhyp: depth of hypcenter, km
//T: array of interested periodse, sec. 0 for PGA, -1 for PGV
//
//Output: PGA, PSA in g unit, PGV in cm/s

///////////////// input parameters
function CB14(m,Rrup,Rjb,Rx,dip,depth,width,length,Zhyp,Vs30,z25,mech,region,T,Zbot,Zbor){
    if(width == -999 && Zbot != -999){    // if width is unknown
        width = Math.min(Math.sqrt(Math.pow(10, (m-4.07)/0.98)), (Zbot-depth)/Math.sin(dip/180*Math.PI));
    }
        
    if(Zhyp == -999){    // if Zhyp is unknown
        if(Zbor != -999){  
            if(m<6.75){
                var fzm = -4.317 + 0.984*m;
            } else{
                var fzm = 2.325;
            } 
            if(dip>40){
                var fzdip = 0;
            } else{
                var fzdip = 0.0445*(dip-40);
            }
            var deltaZ = Math.exp(Math.min((fzm + fzdip), (Math.log(0.9*(Zbor-depth)))));
            Zhyp = depth + deltaZ;
        } else if(depth != -999 && dip != -999){
            Zhyp = depth + 0.5*width*Math.sin(dip/180*Math.PI);
        } else{
            Zhyp = 9;
        }
    }
        
    var A1100 = -999;
    if(z25 === -999){
        if(region == 'Japan'){
            z25 = Math.exp(5.359 - 1.102 * Math.log(Vs30)); // equation for Japan
            var z25a = Math.exp(5.359 - 1.102 * Math.log(1100)); // equation for Japan
        } else {
            z25 = Math.exp(7.089 - 1.144 * Math.log(Vs30)); // equation for California and other regions        
            var z25a = Math.exp(7.089 - 1.144 * Math.log(1100)); // equation for California and other regions
        }
    } else {
        var z25a = z25;
    }
    // Fault mechanism
    if(mech == 'ss'){
        var Frv = 0;
        var Fnm = 0;
    } else if (mech == 'nor'){
        var Frv = 0;
        var Fnm = 1;
    } else if (mech == 'rev'){
        var Frv = 1;
        var Fnm = 0;
    }
    // Region
    if(region == 'Japan') var Sj = 1;
    else var Sj = 0;
    
    var period = [0.010,0.020,0.030,0.050,0.075,0.10,0.15,0.20,0.25,0.30,0.40,0.50,0.75,1.0,1.5,2.0,3.0,4.0,5.0,7.5,10.0,0,-1];
    var idx_t = new Array();
    for (var i=0; i<T.length; i++){
        idx_t[i] = period.indexOf(T[i]); 
    }
     
    var c = 1.88; // coefficient
    var n = 1.18; // power coefficient
    /// the coefficient for T = 1.0 s
    var c0	= [-4.365,-4.348,-4.024,-3.479,-3.293,-3.666,-4.866,-5.411,-5.962,-6.403,-7.566,-8.379,-9.841,-11.011,-12.469,-12.969,-13.306,-14.02,-14.558,-15.509,-15.975,-4.416,-2.895];
    var c1	= [0.977,0.976,0.931,0.887,0.902,0.993,1.267,1.366,1.458,1.528,1.739,1.872,2.021,2.180,2.270,2.271,2.150,2.132,2.116,2.223,2.132,0.984,1.510];
    var c2 = [0.533,0.549,0.628,0.674,0.726,0.698,0.510,0.447,0.274,0.193,-0.020,-0.121,-0.042,-0.069,0.047,0.149,0.368,0.726,1.027,0.169,0.367,0.537,0.270];
    var c3 = [-1.485,-1.488,-1.494,-1.388,-1.469,-1.572,-1.669,-1.750,-1.711,-1.770,-1.594,-1.577,-1.757,-1.707,-1.621,-1.512,-1.315,-1.506,-1.721,-0.756,-0.800,-1.499,-1.299];
    var c4 = [-0.499,-0.501,-0.517,-0.615,-0.596,-0.536,-0.490,-0.451,-0.404,-0.321,-0.426,-0.440,-0.443,-0.527,-0.630,-0.768,-0.890,-0.885,-0.878,-1.077,-1.282,-0.496,-0.453];
    var c5 = [-2.773,-2.772,-2.782,-2.791,-2.745,-2.633,-2.458,-2.421,-2.392,-2.376,-2.303,-2.296,-2.232,-2.158,-2.063,-2.104,-2.051,-1.986,-2.021,-2.179,-2.244,-2.773,-2.466];
    var c6	= [0.248,0.247,0.246,0.240,0.227,0.210,0.183,0.182,0.189,0.195,0.185,0.186,0.186,0.169,0.158,0.15,0.148,0.135,0.140,0.178,0.194,0.248,0.204];
    var c7	= [6.753,6.502,6.291,6.317,6.861,7.294,8.031,8.385,7.534,6.990,7.012,6.902,5.522,5.650,5.795,6.632,6.759,7.978,8.538,8.468,6.564,6.768,5.837];
    var c8	= [0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0];
    var c9	= [-0.214,-0.208,-0.213,-0.244,-0.266,-0.229,-0.211,-0.163,-0.150,-0.131,-0.159,-0.153,-0.090,-0.105,-0.058,-0.028,0,0,0,0,0,-0.212,-0.168];
    var c10	= [0.720,0.730,0.759,0.826,0.815,0.831,0.749,0.764,0.716,0.737,0.738,0.718,0.795,0.556,0.480,0.401,0.206,0.105,0,0,0,0.720,0.305];
    var c11	= [1.094,1.149,1.290,1.449,1.535,1.615,1.877,2.069,2.205,2.306,2.398,2.355,1.995,1.447,0.330,-0.514,-0.848,-0.793,-0.748,-0.664,-0.576,1.090,1.713];
    var c12	= [2.191,2.189,2.164,2.138,2.446,2.969,3.544,3.707,3.343,3.334,3.544,3.016,2.616,2.470,2.108,1.327,0.601,0.568,0.356,0.075,-0.027,2.186,2.602];
    var c13=[1.416,1.453,1.476,1.549,1.772,1.916,2.161,2.465,2.766,3.011,3.203,3.333,3.054,2.562,1.453,0.657,0.367,0.306,0.268,0.374,0.297,1.420,2.457];
    var c14	= [-0.0070,-0.0167,-0.0422,-0.0663,-0.0794,-0.0294,0.0642,0.0968,0.1441,0.1597,0.1410,0.1474,0.1764,0.2593,0.2881,0.3112,0.3478,0.3747,0.3382,0.3754,0.3506,-0.0064,0.1060];
    var c15=[-0.207,-0.199,-0.202,-0.339,-0.404,-0.416,-0.407,-0.311,-0.172,-0.084,0.085,0.233,0.411,0.479,0.566,0.562,0.534,0.522,0.477,0.321,0.174,-0.202,0.332];
    var c16	= [0.390,0.387,0.378,0.295,0.322,0.384,0.417,0.404,0.466,0.528,0.540,0.638,0.776,0.771,0.748,0.763,0.686,0.691,0.670,0.757,0.621,0.393,0.585];
    var c17	= [0.0981,0.1009,0.1095,0.1226,0.1165,0.0998,0.0760,0.0571,0.0437,0.0323,0.0209,0.0092,-0.0082,-0.0131,-0.0187,-0.0258,-0.0311,-0.0413,-0.0281,-0.0205,0.0009,0.0977,0.0517];
    var c18	= [0.0334,0.0327,0.0331,0.0270,0.0288,0.0325,0.0388,0.0437,0.0463,0.0508,0.0432,0.0405,0.0420,0.0426,0.0380,0.0252,0.0236,0.0102,0.0034,0.0050,0.0099,0.0333,0.0327];
    var c19	= [0.00755,0.00759,0.00790,0.00803,0.00811,0.00744,0.00716,0.00688,0.00556,0.00458,0.00401,0.00388,0.00420,0.00409,0.00424,0.00448,0.00345,0.00603,0.00805,0.00280,0.00458,0.00757,0.00613];
    var c20	= [-0.0055,-0.0055,-0.0057,-0.0063,-0.0070,-0.0073,-0.0069,-0.0060,-0.0055,-0.0049,-0.0037,-0.0027,-0.0016,-0.0006,0,0,0,0,0,0,0,-0.0055,-0.0017];
    var deltac20 =  [0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0];
    var Dc20_JI = [-0.0035,-0.0035,-0.0034,-0.0037,-0.0037,-0.0034,-0.0030,-0.0031,-0.0033,-0.0035,-0.0034,-0.0034,-0.0032,-0.0030,-0.0019,-0.0005,0,0,0,0,0,-0.0035,-0.0006];
    var Dc20_CH = [0.0036,0.0036,0.0037,0.0040,0.0039,0.0042,0.0042,0.0041,0.0036,0.0031,0.0028,0.0025,0.0016,0.0006,0,0,0,0,0,0,0,0.0036,0.0017];
    if(region == 'Japan' | region == "Italy"){
        deltac20 = Dc20_JI;
    } else if(region == 'eastern China'){
        deltac20 = Dc20_CH;
    }
    var a2 =[0.168,0.166,0.167,0.173,0.198,0.174,0.198,0.204,0.185,0.164,0.160,0.184,0.216,0.596,0.596,0.596,0.596,0.596,0.596,0.596,0.596,0.167,0.596];
    var h1	= [0.242,0.244,0.246,0.251,0.260,0.259,0.254,0.237,0.206,0.210,0.226,0.217,0.154,0.117,0.117,0.117,0.117,0.117,0.117,0.117,0.117,0.241,0.117];
    var h2	= [1.471,1.467,1.467,1.449,1.435,1.449,1.461,1.484,1.581,1.586,1.544,1.554,1.626,1.616,1.616,1.616,1.616,1.616,1.616,1.616,1.616,1.474,1.616];
    var h3	= [-0.714,-0.711,-0.713,-0.701,-0.695,-0.708,-0.715,-0.721,-0.787,-0.795,-0.770,-0.770,-0.780,-0.733,-0.733,-0.733,-0.733,-0.733,-0.733,-0.733,-0.733,-0.715,-0.733];
    var h4	= [1.000,1.000,1.000,1.000,1.000,1.000,1.000,1.000,1.000,1.000,1.000,1.000,1.000,1.000,1.000,1.000,1.000,1.000,1.000,1.000,1.000,1.000,1.000];
    var h5	= [-0.336,-0.339,-0.338,-0.338,-0.347,-0.391,-0.449,-0.393,-0.339,-0.447,-0.525,-0.407,-0.371,-0.128,-0.128,-0.128,-0.128,-0.128,-0.128,-0.128,-0.128,-0.337,-0.128];
    var h6	= [-0.270,-0.263,-0.259,-0.263,-0.219,-0.201,-0.099,-0.198,-0.210,-0.121,-0.086,-0.281,-0.285,-0.756,-0.756,-0.756,-0.756,-0.756,-0.756,-0.756,-0.756,-0.270,-0.756];
    var k1 = [865,865,908,1054,1086,1032,878,748,654,587,503,457,410,400,400,400,400,400,400,400,400,865,400];
    var k2=[-1.186,-1.219,-1.273,-1.346,-1.471,-1.624,-1.931,-2.188,-2.381,-2.518,-2.657,-2.669,-2.401,-1.955,-1.025,-0.299,0.000,0.000,0.000,0.000,0.000,-1.186,-1.955];
    var k3	= [1.839,1.840,1.841,1.843,1.845,1.847,1.852,1.856,1.861,1.865,1.874,1.883,1.906,1.929,1.974,2.019,2.110,2.200,2.291,2.517,2.744,1.839,1.929];
    var phi1 =  [0.734,0.738,0.747,0.777,0.782,0.769,0.769,0.761,0.744,0.727,0.690,0.663,0.606,0.579,0.541,0.529,0.527,0.521,0.502,0.457,0.441,0.734,0.655];
    var phi2 = [0.492,0.496,0.503,0.520,0.535,0.543,0.543,0.552,0.545,0.568,0.593,0.611,0.633,0.628,0.603,0.588,0.578,0.559,0.551,0.546,0.543,0.492,0.494];
    var t1 = [0.404,0.417,0.446,0.508,0.504,0.445,0.382,0.339,0.340,0.340,0.356,0.379,0.430,0.470,0.497,0.499,0.500,0.543,0.534,0.523,0.466,0.409,0.317];
    var t2 = [0.325,0.326,0.344,0.377,0.418,0.426,0.387,0.338,0.316,0.300,0.264,0.263,0.326,0.353,0.399,0.400,0.417,0.393,0.421,0.438,0.438,0.322,0.297];
    var flnAF = [0.300,0.300,0.300,0.300,0.300,0.300,0.300,0.300,0.300,0.300,0.300,0.300,0.300,0.300,0.300,0.300,0.300,0.300,0.300,0.300,0.300,0.300,0.300];
    var rlnPGA_lnY = [1.000,0.998,0.986,0.938,0.887,0.870,0.876,0.870,0.850,0.819,0.743,0.684,0.562,0.467,0.364,0.298,0.234,0.202,0.184,0.176,0.154,1.000,0.684];


    ///////////////// Magnitude Term

    function F_mag(m, c0, c1, c2, c3, c4) {
        if (m <= 4.5) {
            var fmag = c0 + c1 * m;
        } else if (m <= 5.5) {
            var fmag = c0 + c1 * m + c2 * (m - 4.5);
        } else if (m <= 6.5) {
            var fmag = c0 + c1 * m + c2 * (m - 4.5) + c3 * (m - 5.5);
        } else {
            var fmag = c0 + c1 * m + c2 * (m - 4.5) + c3 * (m - 5.5) + c4 * (m - 6.5);
        }
        return fmag;
    }

    ///////////////// Geometric Attenuation Term

    function F_dis(m, Rrup, c5, c6, c7) {
        var fdis = (c5 + c6 * m) * Math.log(Math.sqrt(Math.pow(Rrup, 2) + Math.pow(c7, 2)));
        return fdis;
    }

    ///////////////// Style of Faulting Term

    function F_flt(Frv, Fnm, c8, c9, m) {
        var ffltF = c8 * Frv + c9 * Fnm;
        if (m <= 4.5) {
            var ffltM = 0;
        } else if (m <= 5.5) {
            var ffltM = m - 4.5;
        } else {
            var ffltM = 1;
        }
        var fflt = ffltF * ffltM;
        return fflt;
    }

    ///////////////// Hanging Wall Term

    function F_hng(m, Rx, dip, Rrup, Rjb, width, depth, h1, h2, h3, h4, h5, h6, a2, c10) {
        var R1 = width * Math.cos(dip / 180 * Math.PI);
        var R2 = 62 * m - 350;
        if (Rx < 0) {
            var fhngRx = 0;
        } else if (Rx < R1) {
            var fhngRx = h1 + h2 * (Rx / R1) + h3 * Math.pow(Rx/R1, 2);
        } else {
            var fhngRx = Math.max(h4 + h5 * (Rx - R1) / (R2 - R1) + h6 * Math.pow((Rx - R1) / (R2 - R1), 2), 0);
        }
        if (Rrup === 0) {
            var fhngRrup = 1;
        } else {
            var fhngRrup = (Rrup - Rjb) / Rrup;
        }
        if (m <= 5.5) {
            var fhngM = 0;
        } else if (m <= 6.5) {
            var fhngM = (m - 5.5) * (1 + a2 * (m - 6.5));
        } else {
            var fhngM = 1 + a2 * (m - 6.5); 
        }
        if (depth <= 16.66) {
            var fhngZ = 1 - 0.06 * depth;
        } else {
            var fhngZ = 0;
        }
        var fhngdelta = (90 - dip) / 45;
        var fhng = c10 * fhngRx * fhngRrup * fhngM * fhngZ * fhngdelta; 
        return fhng;
    }

    ////////////////// Shallow Site Response Term

    function F_site(Vs30, k1, c11, k2, A1100, c, n, c12, c13, Sj) {
        if (Vs30 <= k1) {
            var fsiteG = c11 * Math.log(Vs30/k1) + k2 * (Math.log(A1100 + c * Math.pow(Vs30/k1, n)) - Math.log(A1100 + c));
        } else {
            var fsiteG = (c11 + k2 * n) * Math.log(Vs30 / k1);
        }
        if (Vs30 <= 200) {
            var fsiteJ = (c12 + k2 * n) * (Math.log(Vs30 / k1) - Math.log(200 / k1));
        } else {
            var fsiteJ = (c13 + k2 * n) * Math.log(Vs30 / k1);
        }
        var fsite = fsiteG + Sj * fsiteJ; 
        return fsite;
    }

    ////////////////// Basin Response Term

    function F_sed(z25, c14, c15, Sj, c16, k3) { 
        if (z25 <= 1) {
            var fsed = (c14 + c15 * Sj) * (z25 - 1);
        } else if (z25 <= 3) {
            var fsed = 0;
        } else {
            var fsed = c16 * k3 * Math.exp(-0.75) * (1 - Math.exp(-0.25 * (z25 - 3)));
        }
        return fsed;
    }

    ////////////////// Hypocentral Depth Term

    function F_hyp(Zhyp, c17, c18, m) {
        if (Zhyp <= 7) {
            var fhypH = 0;
        } else if ( Zhyp <= 20) {
            var fhypH = Zhyp - 7;
        } else {
            var fhypH = 13;
        }
        if (m <= 5.5) {
            var fhypM = c17;
        } else if (m <= 6.5) {
            var fhypM = c17 + (c18 - c17) * (m - 5.5);
        } else {
            var fhypM = c18;
        }
        var fhyp = fhypH * fhypM;
        return fhyp; 
    }

    ////////////////// Fault Dip Term  ????????????? dip is deg or radius

    function F_dip(dip, c19, m) {
        if (m <= 4.5) {
            var fdip = c19 * dip;
        } else if (m <= 5.5) {
            var fdip = c19 * (5.5 - m) * dip;
        } else {
            fdip = 0;
        }
        return fdip;
    }

    ///////////////// Anelastic Attenuation Term

    function F_atn(c20, deltac20, Rrup) {
        if (Rrup > 80) {
            var fatn = (c20 + deltac20) * (Rrup - 80);
        } else {
            var fatn = 0;
        }
        return fatn; 
    }
    // PGA at rock
    if (A1100 === -999) {
        var ip_f = 23 - 2
        var PSA_temp = F_mag(m, c0[ip_f], c1[ip_f], c2[ip_f], c3[ip_f], c4[ip_f]) + F_dis(m, Rrup, c5[ip_f], c6[ip_f], c7[ip_f]) + F_flt(Frv, Fnm, c8[ip_f], c9[ip_f], m) + F_hng(m, Rx, dip, Rrup, Rjb, width, depth, h1[ip_f], h2[ip_f], h3[ip_f], h4[ip_f], h5[ip_f], h6[ip_f], a2[ip_f], c10[ip_f]) + F_site(1100, k1[ip_f], c11[ip_f], k2[ip_f], A1100, c, n, c12[ip_f], c13[ip_f], Sj) + F_sed(z25a, c14[ip_f], c15[ip_f], Sj, c16[ip_f], k3[ip_f]) + F_hyp(Zhyp, c17[ip_f], c18[ip_f], m) + F_dip(dip, c19[ip_f], m) + F_atn(c20[ip_f], deltac20[ip_f], Rrup); 
        A1100 = Math.exp(PSA_temp);
    }
    // PGA
    var ip_t = 23 - 2
    var PGA = F_mag(m, c0[ip_t], c1[ip_t], c2[ip_t], c3[ip_t], c4[ip_t]) + F_dis(m, Rrup, c5[ip_t], c6[ip_t], c7[ip_t]) + F_flt(Frv, Fnm, c8[ip_t], c9[ip_t], m) + F_hng(m, Rx, dip, Rrup, Rjb, width, depth, h1[ip_t], h2[ip_t], h3[ip_t], h4[ip_t], h5[ip_t], h6[ip_t], a2[ip_t], c10[ip_t]) + F_site(Vs30, k1[ip_t], c11[ip_t], k2[ip_t], A1100, c, n, c12[ip_t], c13[ip_t], Sj) + F_sed(z25, c14[ip_t], c15[ip_t], Sj, c16[ip_t], k3[ip_t]) + F_hyp(Zhyp, c17[ip_t], c18[ip_t], m) + F_dip(dip, c19[ip_t], m) + F_atn(c20[ip_t], deltac20[ip_t], Rrup); 
    
    ///////////////// Final Predicated Ground Motion for target periods
    var PSA_out = new Array();
    var sigma_out = new Array();
    for(i=0; i<T.length; i++){        
        ip_t = idx_t[i];
        var PSA_temp = F_mag(m, c0[ip_t], c1[ip_t], c2[ip_t], c3[ip_t], c4[ip_t]) + F_dis(m, Rrup, c5[ip_t], c6[ip_t], c7[ip_t]) + F_flt(Frv, Fnm, c8[ip_t], c9[ip_t], m) + F_hng(m, Rx, dip, Rrup, Rjb, width, depth, h1[ip_t], h2[ip_t], h3[ip_t], h4[ip_t], h5[ip_t], h6[ip_t], a2[ip_t], c10[ip_t]) + F_site(Vs30, k1[ip_t], c11[ip_t], k2[ip_t], A1100, c, n, c12[ip_t], c13[ip_t], Sj) + F_sed(z25, c14[ip_t], c15[ip_t], Sj, c16[ip_t], k3[ip_t]) + F_hyp(Zhyp, c17[ip_t], c18[ip_t], m) + F_dip(dip, c19[ip_t], m) + F_atn(c20[ip_t], deltac20[ip_t], Rrup);        
        var PSA = Math.exp(PSA_temp);
        if (PSA < PGA && T[i] < 0.25 & T[i] != -1) {
            PSA = PGA;
        }
        PSA_out[i] = PSA;
        
        
        /////////////////////// standard deviation calculation /////////////////////////////////////////
        if(m<4.5){
            var tau_lny = t1[ip_t];
            var tau_lnPGA = t1[22];
            var phi_lny = phi1[ip_t];
            var phi_lnPGA = phi1[22];
        } else if(m<5.5){
            var tau_lny = t2[ip_t] + (t1[ip_t]-t2[ip_t]) * (5.5-m);
            var tau_lnPGA = t2[22] + (t1[22]-t2[22]) * (5.5-m);
            var phi_lny = phi2[ip_t] + (phi1[ip_t]-phi2[ip_t]) * (5.5-m);
            var phi_lnPGA = phi2[22] + (phi1[22]-phi2[22]) * (5.5-m);
        } else{
            var tau_lny = t2[ip_t];
            var tau_lnPGA = t2[22];
            var phi_lny = phi2[ip_t];
            var phi_lnPGA = phi2[22];
        }
        var tau_lnyB = tau_lny;
        var tau_lnPGAB = tau_lnPGA;
        var phi_lnyB = Math.sqrt(Math.pow(phi_lny,2) - Math.pow(flnAF[ip_t],2));
        var phi_lnPGAB = Math.sqrt(Math.pow(phi_lnPGA,2) - Math.pow(flnAF[ip_t],2));
        if(Vs30 < k1[ip_t]){
            var alpha = k2[ip_t]*A1100*(Math.pow((A1100 + c * Math.pow((Vs30/k1[ip_t]), n)) ,-1) - Math.pow((A1100 + c) ,-1));
        } else{
            var alpha = 0;
        }
        var tau = Math.sqrt(Math.pow(tau_lnyB,2) + Math.pow(alpha,2) * Math.pow(tau_lnPGAB,2) + 2 * alpha * rlnPGA_lnY[ip_t] * tau_lnyB * tau_lnPGAB);
        var phi = Math.sqrt(Math.pow(phi_lnyB,2) + Math.pow(flnAF[ip_t],2) + Math.pow(alpha,2) * Math.pow(phi_lnPGAB,2) + 2 * alpha * rlnPGA_lnY[ip_t] * phi_lnyB * phi_lnPGAB);
        var sigma = Math.sqrt(Math.pow(tau,2) + Math.pow(phi,2));
        sigma_out[i] = sigma;
        
        ////////////////////////// end SD calculation  ///////////////////////////////////////////////////////
        
        
    }
    return PSA_out;
}
