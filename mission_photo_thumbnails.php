<?php
  // Mission Photos
  // Thumbnail creation
  // We're creating two thumbnails for each image:
  // Display image: 250px wide
  // Thumbnail: 125px wide
  // Image types allowed: jpg, png, gif
  // Convert all images to .png
  //

  // Parameters passed
  // image_url = full url to the image file on the application server
  // mission_photo_id = record id of the mission photo

  /*
  thubbard - these will be the output locations

  $original_path = [Path to Cloudfiles container];
  $display_path = [Path to Cloudfiles container];
  $thumbnail_path = [Path to Cloudfiles container];
  */

  require 'vendor/autoload.php';

  use OpenCloud\Rackspace;

  echo "Starting mission_photo_thumbnails at ".date('r')."\n";
  echo "payload:";
  $payload = getPayload();
  print_r($payload);

  $opencloud_uid = getenv('OPENCLOUD_UID');
  $opencloud_api_key = getenv('OPENCLOUD_API_KEY');

  $client = new Rackspace(Rackspace::US_IDENTITY_ENDPOINT, array(
      'username' => $opencloud_uid,
      'apiKey'   => $opencloud_api_key
  ));

  // Obtain an Object Store service object from the client.
  $region = 'DFW';
  $objectStoreService = $client->objectStoreService(null, $region);

  // 3. Get container.
  $container = $objectStoreService->getContainer('public_files');
  
	
	// get elements from payload
  $url = $payload->image_url;
  $mission_photo_id = $payload->mission_photo_id;

  // Download image.
  $raw_image_content = file_get_contents($url);
  //$file = substr($url, strrpos($url, '/'), strlen($url));
  $img = imagecreatefromstring($raw_image_content);
    
  $original_width = imagesx( $img ); 
  $original_height = imagesy( $img );

  /*
  echo "original_width:";   
  print_r($original_width);
  print "\n";
  echo "original_height:";  
  print_r($original_height);
  print_r($file);
  */

	$display_width = 250;
	$thumb_width = 125;
					
  // Target size for the display image is 250 x 168. The ratio w/h is 1.48
	// If the ratio of the ratio of the image is greater, then scale to the width.
	// If smaller, scale to the height
  if ($original_width/$original_height > 1.48) {
	  // display properties
		$display_width = 250;
		$display_height = $original_height * (250/$original_width);

		// thumbnail properties
		$thumbnail_width = 125;
		$thumbnail_height = $display_height/2;
  } else {
	  // display properties
		$display_width = $original_width * (168/$original_height);
		$display_height = 168;

		// thumbnail properties
		$thumbnail_width = $display_width/2;
		$thumbnail_height = 84;

  }

  // display
  $localFileName  = "./{$mission_photo_id}.png";
  $remoteFileName = "{$mission_photo_id}.png";

  $tmp_img = imagecreatetruecolor( $display_width, $display_height );
  imagecopyresized( $tmp_img, $img, 0, 0, 0, 0, $display_width, $display_height, $original_width, $original_height );
  imagepng( $tmp_img, $localFileName );

  $fileData = fopen($localFileName, 'r');
  $container->uploadObject($remoteFileName, $fileData);     

  // thumbnail
  $localThumbFileName  = "./{$mission_photo_id}-thumb.png";
  $remoteThumbFileName = "{$mission_photo_id}-thumb.png";

  $tmp_img = imagecreatetruecolor( $thumbnail_width, $thumbnail_height );
  imagecopyresampled( $tmp_img, $img, 0, 0, 0, 0, $thumbnail_width, $thumbnail_height, $original_width, $original_height );
  imagepng( $tmp_img, $localThumbFileName, 0 ); // 0 means no compression

  $fileData = fopen($localThumbFileName, 'r');
  $container->uploadObject($remoteThumbFileName, $fileData);     

  // for testing that images were put in container
  $objects = $container->objectList();
  foreach ($objects as $object) {
      printf("Object name: %s\n", $object->getName());
  }







