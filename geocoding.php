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

  // set all below as env variable in .worker file
  $google_api_key = getenv('GOOGLE_API_KEY');
  $dbhost = getenv('DBHOST');
  $dbuser = getenv('DBUSER');
  $dbpass = getenv('DBPASS');

  // get elements from payload
  $payload = getPayload();
  //$origin_address = $payload->origin_address;
  //$destination_address = $payload->destination_address;
  $mission_leg_id = $payload->mission_leg_id;

  // test
  $origin_address = urlencode("10554 Ohio Ave,Los Angeles,CA 90024");
  $destination_address = urlencode("3161 Donald Douglas Loop South,Santa Monica,CA,90405");  
  $url = "https://maps.googleapis.com/maps/api/directions/json?origin=".$origin_address."&destination=".$destination_address."&key=".$google_api_key;
  // end test

  // thubbard
  //$url = "https://maps.googleapis.com/maps/api/directions/json?origin=".urlencode($payload->origin_address)."&destination=".urlencode($payload->destination_address)."&key=".$google_api_key;
  
  // I assume that curl is the best approach here?
  $ch = curl_init($url);
  curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
 	curl_setopt($ch, CURLOPT_HEADER, 0);  // DO NOT RETURN HTTP HEADERS
 	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);  // RETURN THE CONTENTS OF THE CALL
  curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
  $directions_result = curl_exec($ch);
  $directions_json = json_decode($directions_result);

  // update the database, save the json array or parse out the elements
  $distance_text = $directions_json->routes[0]->legs[0]->distance->text;
  $distance_value = $directions_json->routes[0]->legs[0]->distance->value;
  $duration_text = $directions_json->routes[0]->legs[0]->duration->text;
  $duration_value = $directions_json->routes[0]->legs[0]->duration->value;
  
  $conn = mysql_connect($dbhost, $dbuser, $dbpass);
  if(! $conn )
  {
    die('Could not connect: ' . mysql_error());
  }
  $sql = "INSERT INTO mission_leg_geo ".
         "(mission_leg_id, distance_text, distance_value, duration_text,duration_value, directions_json) ".
         "VALUES($mission_leg_id,'$distance_text',$distance_value, '$duration_text', $duration_value, '$directions_result' )";

  mysql_select_db('dev_afids');

  $retval = mysql_query( $sql, $conn );

  if(! $retval )
  {
    die('Could not save distance and duration data: ' . mysql_error());
  }
  echo "Successfully saved distance and duration data.\n";

  mysql_close($conn);

  // static maps for the origin
  // store these image files to CloudFiles
  // get the lat & long for the origin from the directions

/* travis
  $lat = $directions_json->routes[0]->legs[0]->start_location->lat;
  $long = $directions_json->routes[0]->legs[0]->start_location->lng;
  $lat_long = $lat.",".$long;
  $url = "https://maps.googleapis.com/maps/api/streetview?size=200x200&location=".$lat_long."&heading=0";
  $url = "https://maps.googleapis.com/maps/api/streetview?size=200x200&location=".$lat_long."&heading=90"; 
  $url = "https://maps.googleapis.com/maps/api/streetview?size=200x200&location=".$lat_long."&heading=180";  
  $url = "https://maps.googleapis.com/maps/api/streetview?size=200x200&location=".$lat_long."&heading=270"; 
*/
  // grab these using file_get_contents() ?
  // save them to CloudFiles
  // the filename would be composed using the $payload->mission_leg_id
  // for example origin_streetview_90_xxx.png where xxx is the mission_leg_id


/* travis  
  $lat = $directions_json->routes[0]->legs[0]->end_location->lat;
  $long = $directions_json->routes[0]->legs[0]->end_location->lng;
  $lat_long = $lat.",".$long;
  $url = "https://maps.googleapis.com/maps/api/streetview?size=200x200&location=".$lat_long."&heading=0";
  $url = "https://maps.googleapis.com/maps/api/streetview?size=200x200&location=".$lat_long."&heading=90"; 
  $url = "https://maps.googleapis.com/maps/api/streetview?size=200x200&location=".$lat_long."&heading=180";  
  $url = "https://maps.googleapis.com/maps/api/streetview?size=200x200&location=".$lat_long."&heading=270";   
*/

  // grab these using file_get_contents() ?
  // save them to CloudFiles
  // the filename would be composed using the $payload->mission_leg_id
  // for example destination_streetview_90_xxx.png where xxx is the mission_leg_id

  // Map of the route
  // store these image files to CloudFiles
  // get the svg path of the route from the directions

/* travis
  $svg_polyline = $directions_result->routes[0]->overview_polyline;
  $url = "https://maps.googleapis.com/maps/api/staticmap?size=400x400&path=weight:3%7Ccolor:red%7Cenc:".$svg_polyline;
*/


  // grab using file_get_contents() ?
  // save it to CloudFiles
  // the filename would be composed using the $payload->mission_leg_id
  // for example route_xxx.png where xxx is the mission_leg_id
  