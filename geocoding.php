<?php
//error_reporting(E_ALL);
//ini_set('display_errors', true);
  
  // Mission legs (ground legs)
  // Geocoding and map download process
  // Step 1: Download directions from the origin and destination
  //         -> save to the database
  // Step 2: Download a static map for the origin and the destination
  //         -> store these on Rackspace Cloudfiles
  // Step 3: Download a map of the route
  //
  // Parameters passed
  // origin_address = complete address of the origin, in the  string format expected by Google
  // destination_address
  // mission_leg_id = record id of the mission leg
  
  // Get directions
  $url = "https://maps.googleapis.com/maps/api/directions/json?origin=".urlencode($payload->origin_address)."&destination=".urlencode($payload->destination_address)."&key=".$google_api_key;
  
  //$origin_address = urlencode("10554 Ohio Ave,Los Angeles,CA 90024");
  //$destination_address = urlencode("3161 Donald Douglas Loop South,Santa Monica,CA,90405");  
  //$url = "https://maps.googleapis.com/maps/api/directions/json?origin=".$origin_address."&destination=".$destination_address."&key=AIzaSyAPIlFIrWMfj-tqlSCK4kX34YCe_lkHVho";
  
  // I assume that curl is the best approach here?
  $ch = curl_init($url);
  curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
 	curl_setopt($ch, CURLOPT_HEADER, 0);  // DO NOT RETURN HTTP HEADERS
 	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);  // RETURN THE CONTENTS OF THE CALL
  curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
  $directions_result = curl_exec($ch);
  $directions_obj = json_decode($directions_result);

  // update the database, save the json array or parse out the elements
  $distance_text = $directions_obj->routes[0]->legs[0]->distance->text;
  $distance_value = $directions_obj->routes[0]->legs[0]->distance->value;
  $duration_text = $directions_obj->routes[0]->legs[0]->duration->text;
  $duration_value = $directions_obj->routes[0]->legs[0]->duration->value;

  // static maps for the origin
  // store these image files to CloudFiles
  // get the lat & long for the origin from the directions
  $lat = $directions_obj->routes[0]->legs[0]->start_location->lat;
  $long = $directions_obj->routes[0]->legs[0]->start_location->lng;
  $lat_long = $lat.",".$long;
  $url = "https://maps.googleapis.com/maps/api/streetview?size=200x200&location=".$lat_long."&heading=0";
  $url = "https://maps.googleapis.com/maps/api/streetview?size=200x200&location=".$lat_long."&heading=90"; 
  $url = "https://maps.googleapis.com/maps/api/streetview?size=200x200&location=".$lat_long."&heading=180";  
  $url = "https://maps.googleapis.com/maps/api/streetview?size=200x200&location=".$lat_long."&heading=270"; 
  // grab these using file_get_contents() ?
  // save them to CloudFiles
  // the filename would be composed using the $payload->mission_leg_id
  // for example origin_streetview_90_xxx.png where xxx is the mission_leg_id
  
  $lat = $directions_obj->routes[0]->legs[0]->end_location->lat;
  $long = $directions_obj->routes[0]->legs[0]->end_location->lng;
  $lat_long = $lat.",".$long;
  $url = "https://maps.googleapis.com/maps/api/streetview?size=200x200&location=".$lat_long."&heading=0";
  $url = "https://maps.googleapis.com/maps/api/streetview?size=200x200&location=".$lat_long."&heading=90"; 
  $url = "https://maps.googleapis.com/maps/api/streetview?size=200x200&location=".$lat_long."&heading=180";  
  $url = "https://maps.googleapis.com/maps/api/streetview?size=200x200&location=".$lat_long."&heading=270";   
  // grab these using file_get_contents() ?
  // save them to CloudFiles
  // the filename would be composed using the $payload->mission_leg_id
  // for example destination_streetview_90_xxx.png where xxx is the mission_leg_id

  // Map of the route
  // store these image files to CloudFiles
  // get the svg path of the route from the directions
  $svg_polyline = $directions_result->routes[0]->overview_polyline;
  $url = "https://maps.googleapis.com/maps/api/staticmap?size=400x400&path=weight:3%7Ccolor:red%7Cenc:".$svg_polyline;
  // grab using file_get_contents() ?
  // save it to CloudFiles
  // the filename would be composed using the $payload->mission_leg_id
  // for example route_xxx.png where xxx is the mission_leg_id
  