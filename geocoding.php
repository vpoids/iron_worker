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
  
  //--- OpenCloud setup ---
  require 'vendor/autoload.php';
  use OpenCloud\Rackspace;

  // opencloud connect
  $opencloud_uid = getenv('OPENCLOUD_UID');
  $opencloud_api_key = getenv('OPENCLOUD_API_KEY');

  $client = new Rackspace(Rackspace::US_IDENTITY_ENDPOINT, array(
      'username' => $opencloud_uid,
      'apiKey'   => $opencloud_api_key
  ));
  // Obtain an Object Store service object from the client.
  $region = 'DFW';
  $objectStoreService = $client->objectStoreService(null, $region);
  // Get container.
  $container = $objectStoreService->getContainer('public_files');

  // get env vars for google and mysql (set in .worker file on upload of code package)
  $google_api_key = getenv('GOOGLE_API_KEY');
  $dbhost = getenv('DBHOST');
  $dbuser = getenv('DBUSER');
  $dbpass = getenv('DBPASS');

  // get elements from payload
  $payload = getPayload();
  $origin_address = $payload->origin_address;
  $destination_address = $payload->destination_address;
  $mission_leg_id = $payload->mission_leg_id;

  // Get directions
  $url = "https://maps.googleapis.com/maps/api/directions/json?origin=".urlencode($origin_address)."&destination=".urlencode($destination_address)."&key=".$google_api_key;
  
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

  // origin images
  $lat = $directions_json->routes[0]->legs[0]->start_location->lat;
  $long = $directions_json->routes[0]->legs[0]->start_location->lng;
  $lat_long = $lat.",".$long;

  $url = "https://maps.googleapis.com/maps/api/streetview?size=200x200&location=".$lat_long."&heading=0";
  $container->uploadObject("origin_streetview_0_".$mission_leg_id.".png", file_get_contents($url));
  

  $url = "https://maps.googleapis.com/maps/api/streetview?size=200x200&location=".$lat_long."&heading=90"; 
  $container->uploadObject("origin_streetview_90_".$mission_leg_id.".png", file_get_contents($url));
  

  $url = "https://maps.googleapis.com/maps/api/streetview?size=200x200&location=".$lat_long."&heading=180"; 
  $container->uploadObject("origin_streetview_180_".$mission_leg_id.".png", file_get_contents($url));
  

  $url = "https://maps.googleapis.com/maps/api/streetview?size=200x200&location=".$lat_long."&heading=270"; 
  $container->uploadObject("origin_streetview_270_".$mission_leg_id.".png", file_get_contents($url));
  

  // destination images
  $lat = $directions_json->routes[0]->legs[0]->end_location->lat;
  $long = $directions_json->routes[0]->legs[0]->end_location->lng;
  $lat_long = $lat.",".$long;
  $url = "https://maps.googleapis.com/maps/api/streetview?size=200x200&location=".$lat_long."&heading=0";
  $container->uploadObject("destination_streetview_0_".$mission_leg_id.".png", file_get_contents($url));
  

  $url = "https://maps.googleapis.com/maps/api/streetview?size=200x200&location=".$lat_long."&heading=90"; 
  $container->uploadObject("destination_streetview_90_".$mission_leg_id.".png", file_get_contents($url));
  

  $url = "https://maps.googleapis.com/maps/api/streetview?size=200x200&location=".$lat_long."&heading=180";  
  $container->uploadObject("destination_streetview_180_".$mission_leg_id.".png", file_get_contents($url));
  

  $url = "https://maps.googleapis.com/maps/api/streetview?size=200x200&location=".$lat_long."&heading=270";   
  $container->uploadObject("destination_streetview_270_".$mission_leg_id.".png", file_get_contents($url));


  // Map of the route
  // get the svg path of the route from the directions
  $svg_polyline = $directions_json->routes[0]->overview_polyline->points;
  $url = "https://maps.googleapis.com/maps/api/staticmap?size=400x400&path=weight:3%7Ccolor:red%7Cenc:".$svg_polyline;
  $container->uploadObject("route_".$mission_leg_id.".png", file_get_contents($url));

  // for testing that images were put in container
  /*
  $objects = $container->objectList();
  foreach ($objects as $object) {
      printf("Object name: %s\n", $object->getName());
  }  
  */