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

  $original_path = [Path to Cloudfiles container];
  $display_path = [Path to Cloudfiles container];
  $thumbnail_path = [Path to Cloudfiles container];
	
	// Download image.
  $raw_image_content = file_get_contents($payload->image_url);
  $url = $payload->image_url;
  $file = substr($url, strrpos($url, '/'), strlen($url));
  $img = imagecreatefromstring($raw_image_content);
     
  $original_width = imagesx( $img );
  $original_height = imagesy( $img );

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
  $tmp_img = imagecreatetruecolor( $display_width, $display_height );
  imagecopyresized( $tmp_img, $img, 0, 0, 0, 0, $display_width, $display_height, $original_width, $original_height );
  imagepng( $tmp_img, "{$display_path}{$filename_only}" );

  // thumbnail
  $tmp_img = imagecreatetruecolor( $thumbnail_width, $thumbnail_height );
  imagecopyresampled( $tmp_img, $img, 0, 0, 0, 0, $thumbnail_width, $thumbnail_height, $original_width, $original_height );
  imagepng( $tmp_img, "{$thumbnail_path}{$filename_only}", 0 ); // 0 means no compression
     
  // return an array with the file values to be stored
  // return array("filesize" => $filesize, "height" => $original_height, "width" => $original_width, "format" => $format);
  // This is done via a callback?