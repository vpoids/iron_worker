<?php
  //error_reporting(E_ALL);
  //ini_set('display_errors', true);

  // local
  $conn=mysql_connect ("localhost", "sfopeano", "") or die ('I cannot connect to the database because: ' . mysql_error());
  mysql_select_db ("afids");
  
  // Waiver receipt
  // Create a facsimile of an electronically signed waiver as a pdf
  // Then email it to some recipients
  //
  // Parameters passed
  // waiver_id = record id of the waiver
  // recipients = an array of recipients
  // $waiver_id = 15; // for testing
  $waiver_id = $payload->waiver_id;
  
  $cloud_files_path = "C:\\web\\afids_201\\browse\\trunk\\web\\uploads\\waiver_signatures\\";
  $cloud_files_url = "http://afids/uploads/waiver_signatures/";
  
  // Start by converting the signatures to images
  // It may make sense to check to see if the file already exists. I'm not sure which will take longer
  // checking to see if the image is there, or re-generating it. There is no problem with re-generating it

  // load the library that handles the conversion  
  require_once 'signature-to-image.php';

  // retrieve the waiver record from the database
  $sql = "select * from waiver where id = ".$waiver_id;
  $waiver_obj = mysql_query($sql); 
  if (mysql_error()) {
    echo "MySql error: ".mysql_error()." ".$sql;
    exit;
  }
  $waiver = mysql_fetch_assoc($waiver_obj);

  // These define the height and width of the two types of signatures
  $options_array = array("imageSize" => array(380, 125));
  $release_options_array = array("imageSize" => array(150, 125));
  
  // generate a signature file for each field that has data
  if ($waiver["passenger_signature"]) {
    $img = sigJsonToImage($waiver["passenger_signature"],$options_array);
    $filename = $cloud_files_path.$waiver_id."_pass.png";
    imagepng($img, $filename);
  }
  if ($waiver["companion_one"]) {
    $img = sigJsonToImage($waiver["companion_one"],$options_array);
    $filename = $cloud_files_path.$waiver_id."_com1.png";
    imagepng($img, $filename);
  }
  if ($waiver["companion_two"]) {
    $img = sigJsonToImage($waiver["companion_two"],$options_array);
    $filename = $cloud_files_path.$waiver_id."_com2.png";
    imagepng($img, $filename);
  }
  if ($waiver["companion_three"]) {
    $img = sigJsonToImage($waiver["companion_three"],$options_array);
    $filename = $cloud_files_path.$waiver_id."_com3.png";
    imagepng($img, $filename);
  }
  if ($waiver["photo_release_one"]) {
    $img = sigJsonToImage($waiver["photo_release_one"],$options_array);
    $filename = $cloud_files_path.$waiver_id."_rel1.png";
    imagepng($img, $filename);
  }
  if ($waiver["photo_release_two"]) {
    $img = sigJsonToImage($waiver["photo_release_two"],$options_array);
    $filename = $cloud_files_path.$waiver_id."_rel2.png";
    imagepng($img, $filename);
  }
  if ($waiver["guardian_signature"]) {
    $img = sigJsonToImage($waiver["guardian_signature"],$options_array);
    $filename = $cloud_files_path.$waiver_id."_guar.png";
    imagepng($img, $filename);
  }
  if ($waiver["mission_assistant"]) {
    $img = sigJsonToImage($waiver["mission_assistant"],$options_array);
    $filename = $cloud_files_path.$waiver_id."_mast.png";
    imagepng($img, $filename);
  }
  if ($waiver["addl_person_one"]) {
   $img = sigJsonToImage($waiver["addl_person_one"],$options_array);
   $filename = $cloud_files_path.$waiver_id."_adp1.png";
   imagepng($img, $filename);
  }
  if ($waiver["addl_person_two"]) {
    $img = sigJsonToImage($waiver["addl_person_two"],$options_array);
    $filename = $cloud_files_path.$waiver_id."_adp2.png";
    imagepng($img, $filename);
  }
  if ($waiver["pilot_signature"]) {
    $img = sigJsonToImage($waiver["pilot_signature"],$options_array);
    $filename = $cloud_files_path.$waiver_id."_pilt.png";
    imagepng($img, $filename);
  }

  // retrieve the waiver form from the database
  $waiver_template_id = $waiver["waiver_template_id"];
  $sql = "select * from waiver_template where id = ".$waiver_template_id;
  $template_obj = mysql_query($sql); 
  if (mysql_error()) {
    echo "MySql error: ".mysql_error()." ".$sql;
    exit;
  }
  $waiver_template = mysql_fetch_assoc($template_obj);
      
  // now read in the waiver text and handle the substitutions
  $template_html = $waiver_template["waiver_electronic_english"];

  // retrieve mission leg information from the database
  $mission_leg_id = $waiver["mission_leg_id"];

  $sql = "select m.external_id, ml.leg_number, concat(pass.first_name,' ',pass.last_name) as passName, concat(pp.first_name,' ',pp.last_name) as pilotName, ";
  $sql.= "concat(mapp.first_name,' ',mapp.last_name) as maName, concat(comp.first_name,' ',comp.last_name) as compName ";
  $sql.= "from mission m join mission_leg ml ON (m.id = ml.mission_id) ";
  $sql.= "join passenger pa ON (m.passenger_id = pa.id) ";
  $sql.= "join person pass ON (pa.person_id = pass.id) ";
  $sql.= "left join pilot pl ON (ml.pilot_id = pl.id) ";
  $sql.= "left join member mb ON (pl.member_id = mb.id) ";
  $sql.= "left join person pp ON (mb.person_id = pp.id) ";
  $sql.= "left join member mamb ON (ml.copilot_id = mamb.id) ";
  $sql.= "left join person mapp ON (mamb.person_id = mapp.id) ";
  $sql.= "left join mission_companion mc ON (m.id = mc.mission_id) ";
  $sql.= "join companion c ON (mc.companion_id = c.id) ";
  $sql.= "join person comp ON (c.person_id = comp.id) ";
  $sql.= "where ml.id = ".$waiver["mission_leg_id"];

  $mission_leg_obj = mysql_query($sql); 
  if (mysql_error()) {
    echo "MySql error: ".mysql_error()." ".$sql;
    exit;
  }
  $mission_leg = mysql_fetch_assoc($mission_leg_obj);

  // substitutions
  $mission_id = $mission_leg["external_id"]."-".$mission_leg["leg_number"];
  $template_html = str_replace("{{mission_number}}",$mission_id,$template_html);

  $template_html = str_replace("{{passenger_name}}",$mission_leg["passName"].", ",$template_html);
    
  $template_html = str_replace("{{pilot}}",$mission_leg["pilotName"]." ",$template_html);
  if ($mission_leg["maName"]) {
    $template_html = str_replace("{{mission_assistant}}"," and ".$mission_leg["maName"].", ",$template_html);
  } else {
    $template_html = str_replace("{{mission_assistant}}","",$template_html);
  }

  $header = "<h2>WAIVER AND RELEASE OF LIABILITY for Misssion ".$mission_leg["external_id"]."-".$mission_leg["leg_number"]."</h2> ";
  $header.= "<p>The following is a facsimile of a waiver of liability reviewed, signed electronically and posted on ".$waiver["date_received"].".</p> ";

  $counter = 1;
  $companion_count = mysql_num_rows($mission_leg_obj);

  $companion_names = "";
  $companion_table = "<p>Companions:<br/><table width=\"400\"><tr>";
  if ($companion_count > 1) $companion_names = " and ";
  while($mission_leg = mysql_fetch_assoc($mission_leg_obj)) {
    $companion_names.= $mission_leg["compName"];
    if ($counter < $companion_count) $companion_names.= ", and "; else $companion_names.= ", ";
    $companion_table.= "<td width=\"200\">";
    $companion_table.= "<img src=\"".$cloud_files_url.$waiver_id."_com".$counter.".png\" height=\"63\" width=\"190\"/>";
    $companion_table.= "<br/><strong>".$mission_leg["compName"]."</strong>";
    $companion_table.= "</td>";
		$counter++;
	}
  $companion_table.= "</tr></table></p>";
  $template_html = str_replace("{{companions}}",$companion_names,$template_html);
	if ($companion_count > 1) {
	  $template_html = str_replace("{{companion_signatures}}",$companion_table,$template_html);
  } else {
    $template_html = str_replace("{{companion_signatures}}","",$template_html);
  }

	// signatures
	if ($waiver["passenger_signature"]) {
	  $passenger_block = "<p>Passenger:<br/><img src=\"".$cloud_files_url.$waiver_id."_pass.png\" height=\"63\" width=\"190\"/>";
		$passenger_block.= "<br/><strong>".$mission_leg["passName"]."</strong></p>";
		$template_html = str_replace("{{passenger_signature}}",$passenger_block,$template_html);
  }
  if ($waiver["mission_assistant"]) {
	  $ma_block = "<p>Mission Assistant:<br/><img src=\"".$cloud_files_url.$waiver_id."_mast.png\" height=\"63\" width=\"190\"/>";
		$ma_block.= "<br/><strong>".$mission_leg["maName"]."</strong></p>";
		$template_html = str_replace("{{mission_assistant_block}}",$ma_block,$template_html);
  } else $template_html = str_replace("{{mission_assistant_block}}","",$template_html);
	if ($waiver["pilot_signature"]) {
	  $pilot_block = "<p>Pilot:<br/><img src=\"".$cloud_files_url.$waiver_id."_pilt.png\" height=\"63\" width=\"190\"/>";
		$pilot_block.= "<br/><strong>".$mission_leg["pilotName"]."</strong></p>";
		$template_html = str_replace("{{pilot_block}}",$pilot_block,$template_html);
  }	
	if ($waiver["guardian_signature"]) {
		$guardian = "<p>Guardian:<br/><img src=\"".$cloud_files_url.$waiver_id."_guar.png\" height=\"63\" width=\"190\"/>";
		$guardian.= "<br/><strong>".$waiver["guardian_name"]."</strong></p>";
		$template_html = str_replace("{{guardian_signature}}",$guardian,$template_html);
  }	else {
    $template_html = str_replace("{{guardian_signature}}","",$template_html);
    $template_html = substr($template_html,0,strpos($template_html,"{{guardian_block_start}}")).substr($template_html,strpos($template_html,"{{guardian_block_end}}")+strlen("{{guardian_block_end}}"));
  }

  if ($waiver["addl_person_one"] || $waiver["addl_person_two"]) {
    $ap_table = "<p>Additional signatures<br/><table width=\"400\"><tr>";
    if ($waiver["addl_person_one"]) {
      $ap_table.= "<td width=\"200\">";
      $ap_table.= "<p><img src=\"".$cloud_files_url.$waiver_id."_adp1.png\" height=\"63\" width=\"190\"/>";
      $ap_table.= "<br/><strong>".$waiver["addl_person_one_name"]."</strong></p>";
      $ap_table.= "</td>";
		}
    if ($waiver["addl_person_two"]) {
      $ap_table.= "<td width=\"200\">";
      $ap_table.= "<p><img src=\"".$cloud_files_url.$waiver_id."_adp2.png\" height=\"63\" width=\"190\"/>";
      $ap_table.= "<br/><strong>".$waiver["addl_person_two_name"]."</strong></p>";
      $ap_table.= "</td>";
		}
		$ap_table.= "</tr></table></p>";
		$template_html = str_replace("{{addl_person_one}}",$ap_table,$template_html);
  } else {
	  $template_html = str_replace("{{addl_person_one}}","",$template_html);
  }

  if ($waiver["photo_release_one"] || $waiver["photo_release_two"]) {
    $pr_table = "<p><table width=\"400\"><tr>";
    if ($waiver["photo_release_one"]) {
      $pr_table.= "<td width=\"200\">";
      $pr_table.= "<img src=\"".$cloud_files_url.$waiver_id."_rel1.png\" height=\"63\" width=\"75\"/>";
      $pr_table.= "</td>";
		}
    if ($waiver["photo_release_two"]) {
      $pr_table.= "<td width=\"200\">";
      $pr_table.= "<img src=\"".$cloud_files_url.$waiver_id."_rel2.png\" height=\"63\" width=\"75\"/>";
      $pr_table.= "</td>";
		}
		$pr_table.= "</tr></table></p>";
		$template_html = str_replace("{{photo_release}}",$pr_table,$template_html);
  } else {
	  $template_html = str_replace("{{photo_release}}","",$template_html);
  }
    
  $template_html.= "<p>Form version ".$waiver_template["version_number"].".0 Dated: ".$waiver_template["version_date"]."</p>";
  
  //echo $template_html;
  // load the libary for creating the pdf
  require_once("c:\\web\\iron_worker\\pdf_functions.php");
	$pdfString = createPDF($header.$template_html, 'Mission Itinerary', 'string', 'itinerary');
	$guid = uniqid();
	// save the pdf on the cloud files server, or maybe that's not necessary
	// maybe just save it in a temp path until the email is sent
	$filename = $cloud_files_path.$waiver_id."_".$guid.'.pdf';
	$fOut = fopen($filename,"w");
	fwrite($fOut, $pdfString);
	fclose($fOut);
	
	// now send the pdf by email
	// pull the body of the email from the database
	$sql = "select * from email_body where recipients_type = 'waiver_receipt'";
  $email_body_obj = mysql_query($sql); 
  if (mysql_error()) {
    echo "MySql error: ".mysql_error()." ".$sql;
    exit;
  }
  $email_body = mysql_fetch_assoc($email_body_obj);
  $sender_name = $email_body["sender_name"];
  $sender_email = $email_body["sender_email"];
  $html_body = $email_body["body"];
  $subject = $email_body["subject"];
  $recipients = $payload->recipients; /* can this be an array of multiple recipients? */
  
  // send the email using smtp
  $smtp_host = "";
  $smtp_port = "";
  $smtp_username = "";
  $smtp_password = "";