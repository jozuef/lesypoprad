function initMap() 
{
	"use strict";
  var map = new google.maps.Map(document.getElementById('map1'), {
    zoom: 12,
    center: {lat: 49.031956, lng: 20.285658},
    mapTypeId: 'roadmap'
  });
	var map2 = new google.maps.Map(document.getElementById('map2'), {
    zoom: 12,
    center: {lat: 49.08454, lng: 20.24368},
    mapTypeId: 'roadmap'
  });

  // Define the LatLng coordinates for the polygon's path.
  var forestCoords = [
    {lat:49.0270353, lng:20.2575445},
    {lat:49.0240243, lng:20.2596474},
    {lat:49.0218011, lng:20.2601624},
    {lat:49.0206754, lng:20.2578449},
    {lat:49.0223358, lng:20.2546262},
    {lat:49.0227861, lng:20.2523088},
    {lat:49.0217729, lng:20.2494336},
    {lat:49.0187334, lng:20.2472878},
    {lat:49.0146242, lng:20.2471161},
    {lat:49.0133294, lng:20.246129},
    {lat:49.0108524, lng:20.2460861},
    {lat:49.0086849, lng:20.2456999},
    {lat:49.0058135, lng:20.242374},
    {lat:49.0075588, lng:20.2503777},
    {lat:49.0068551, lng:20.2604628},
    {lat:49.0067425, lng:20.2653551},
    {lat:49.0059542, lng:20.2682734},
    {lat:49.0056164, lng:20.2718782},
    {lat:49.0058698, lng:20.2804613},
    {lat:49.0048281, lng:20.2867699},
    {lat:49.0036459, lng:20.289774},
    {lat:49.003477, lng:20.2949667},
    {lat:49.0043778, lng:20.2956533},
    {lat:49.0040962, lng:20.3013181},
    {lat:49.0076013, lng:20.3000095},
    {lat:49.010768, lng:20.2993012},
    {lat:49.0132168, lng:20.2971983},
    {lat:49.0134983, lng:20.2925634},
    {lat:49.0171573, lng:20.2934217},
    {lat:49.0248122, lng:20.2929497},
    {lat:49.0259942, lng:20.291276},
    {lat:49.0263881, lng:20.2858686},
    {lat:49.0261067, lng:20.2830792},
    {lat:49.0262755, lng:20.2784014},
    {lat:49.0280202, lng:20.2762556},
    {lat:49.0273028, lng:20.2715779},
    {lat:49.0285127, lng:20.2636385},
    {lat:49.0280836, lng:20.2592182},
    {lat:49.0270353, lng:20.2575445}
  ];
	var forestCoords2 = [
	  {lat:49.0818494, lng:20.2163887},
	  {lat:49.0787011, lng:20.2139854},
	  {lat:49.0771269, lng:20.2203369},
	  {lat:49.0772394, lng:20.2301216},
	  {lat:49.0798255, lng:20.2282333},
	  {lat:49.0826365, lng:20.2306366},
	  {lat:49.0851099, lng:20.2345848},
	  {lat:49.0903937, lng:20.2354431},
	  {lat:49.0909558, lng:20.2316666},
	  {lat:49.0921923, lng:20.2316666},
	  {lat:49.0924172, lng:20.229435},
	  {lat:49.0929792, lng:20.2264309},
	  {lat:49.0937099, lng:20.222826},
	  {lat:49.0866277, lng:20.2215385},
	  {lat:49.0826365, lng:20.2213669},
	  {lat:49.0824116, lng:20.2201653},
	  {lat:49.0816808, lng:20.2185345},
	  {lat:49.0818494, lng:20.2163887}
  ];
	var forestCoords3 = [
		{lat:49.095958, lng:20.2258301},
	   {lat:49.0969134, lng:20.2262592},
	   {lat:49.096239, lng:20.2301216},
	   {lat:49.1014654, lng:20.2331257},
	   {lat:49.101634, lng:20.2351856},
	   {lat:49.1024769, lng:20.2360439},
	   {lat:49.1033198, lng:20.2368164},
	   {lat:49.1051742, lng:20.2317524},
	   {lat:49.1064103, lng:20.2348423},
	   {lat:49.1087141, lng:20.2354431},
	   {lat:49.1078713, lng:20.2308941},
	   {lat:49.1080398, lng:20.2237701},
	   {lat:49.1093321, lng:20.2212811},
	   {lat:49.106916, lng:20.2200794},
	   {lat:49.1057923, lng:20.2204227},
	   {lat:49.1011844, lng:20.2209377},
	   {lat:49.1006225, lng:20.2201653},
	   {lat:49.0966324, lng:20.2217102},
	   {lat:49.095958, lng:20.2258301}
  ];
  // Construct the polygons.
  var urbanForest = new google.maps.Polygon(
  {
    paths: forestCoords,
    strokeColor: '#FF0000',
    strokeOpacity: 0.8,
    strokeWeight: 2,
    fillColor: '#FF0000',
    fillOpacity: 0.35
  });
  urbanForest.setMap(map);
  urbanForest.addListener('click', function() 
  {
    var url = "https://drive.google.com/open?id=1wnTfzH11ajigAgGjOlHKD9Rr8O8&usp=sharing";
    window.open(url, 'Mapa');
  });
	
  var urbanForest2 = new google.maps.Polygon({
    paths: forestCoords2,
    strokeColor: '#FF0000',
    strokeOpacity: 0.8,
    strokeWeight: 2,
    fillColor: '#FF0000',
    fillOpacity: 0.35
  });
  urbanForest2.setMap(map2);
  urbanForest2.addListener('click', function() 
  {
    var url = "https://drive.google.com/open?id=1d-4Hjf_Ru5uSCwF1X_98SOCcCpw&usp=sharing";
    window.open(url, 'Mapa');
  });
	var urbanForest3 = new google.maps.Polygon({
    paths: forestCoords3,
    strokeColor: '#FF0000',
    strokeOpacity: 0.8,
    strokeWeight: 2,
    fillColor: '#FF0000',
    fillOpacity: 0.35
  });
  urbanForest3.setMap(map2);
  urbanForest3.addListener('click', function() 
  {
    var url = "https://drive.google.com/open?id=1d-4Hjf_Ru5uSCwF1X_98SOCcCpw&usp=sharing";
    window.open(url, 'Mapa');
  });
  
}