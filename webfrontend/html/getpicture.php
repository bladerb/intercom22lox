<?php

require_once "../../../htmlauth/plugins/intercom22lox/config.php";
require_once "phpMQTT/phpMQTT.php";


$miniserver_config = LBSystem::get_miniservers();

	
function add_text_to_jpg($jpg_file, $text) {
    $img = imagecreatefromjpeg($jpg_file);
    $white = imagecolorallocate($img, 255, 255, 255);
    $black = imagecolorallocate($img, 0, 0, 0);
    imagefilledrectangle($img, 9, 29, strlen($text) * imagefontwidth(5) + 11, 45, $black);
    imagestring($img, 5, 10, 30, $text, $white);
    imagejpeg($img, $jpg_file);
    imagedestroy($img);
}

if(file_exists(LBPCONFIGDIR.'/data.json')){

	header('Content-type:application/json;charset=utf-8');
	$arr = json_decode(file_get_contents(LBPCONFIGDIR.'/data.json'),true);

	$camurl="http://". $miniserver_config[1]["Admin_RAW"] .":". $miniserver_config[1]["Pass_RAW"] ."@". $arr["intercomip"]. "/mjpg/video.mjpg";

	$boundary="\n--";
	$f = fopen($camurl,"r") ;
	$r="";
	if(!$f)
	{
	    echo "error";
	}else{
			while (substr_count($r,"Content-Length") != 2) $r.=fread($f,512);
			$start = strpos($r,"\xff");
			$end   = strpos($r,$boundary,$start)-1;
			$frame = substr("$r",$start,$end - $start);
			
			file_put_contents("lastpicture.jpg", $frame);
			
			// add timestamp
			if(isset($arr['timestamp_image'])){
				if($arr['timestamp_image']=="on"){
					$timestamp = date('d.m.Y H:i:s');
					add_text_to_jpg("lastpicture.jpg", $timestamp);
				}
			}

         	if(!isset($_REQUEST['hook'])){ // archive nur wenn über hook call aufgerufen
				$archiveimg = $folder_img_archive.date("Y.m.d-H:i:s")."-intercom.jpg";
				file_put_contents($archiveimg, $frame);

				// add timestamp
				if(isset($arr['timestamp_image'])){
					if($arr['timestamp_image']=="on"){
						$timestamp = date('d.m.Y H:i:s');
						add_text_to_jpg($archiveimg, $timestamp);
					}
				}
			}	

	   }

	fclose($f);

	$url = str_replace(basename($_SERVER['REQUEST_URI']), "", $_SERVER['REQUEST_URI']);
	$json = json_encode(array("success"=>true,"timestamp"=>date("d.m.Y-H:i:s"),"image"=>'http://'.$_SERVER['HTTP_HOST'].$url.'lastpicture.jpg'));
	echo $json;
	$jsonarr = json_decode($json,true);


	// hook nicht aufrufen wenn aus webfrontend aufgerufen
	if(isset($_REQUEST['hook'])){
		exit;
	}

	// TODO abfrage wenn credentials noch nicht gestezt ueberspringen
	if ( isset($arr['mqtt_enable']) ){
		if ( $arr['mqtt_enable']=="1" ){
			//MQTT parameter
			if ($arr['mqtt_uselocal']=="1") {
			    $creds = mqtt_connectiondetails();
			} else {
			    $creds['brokerhost'] = $arr['mqtt_server'];
			    $creds['brokerport'] = $arr['mqtt_port'];
			    $creds['brokeruser'] = $arr['mqtt_user'];
			    $creds['brokerpass'] = $arr['mqtt_password'];
			}	
			$client_id = uniqid(gethostname()."_client");
			$mqtt = new Bluerhinos\phpMQTT($creds['brokerhost'],  $creds['brokerport'], $client_id);
			if( $mqtt->connect(true, NULL, $creds['brokeruser'], $creds['brokerpass'] ) ) {
			    $mqtt->publish("intercom22lox", $json, 0, 1);
			    $mqtt->close();
			} else {
			    echo "MQTT connection error Please set custom Credentials or choose default MQTT Broker from Loxberry";
			}
		} 
	}// end mqtt post


	foreach (array(1,3) as $key => $value)
	if(isset($arr["webhook$value"])){
		if($arr["webhook$value"]!=""){
			$url = $arr["webhook$value"];
			$ch = curl_init($url);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $json);
			curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type:application/json'));
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			$result = curl_exec($ch);
			curl_close($ch);
		}
	} // end webhook 1

	foreach (array(2,4) as $key => $value)
	if(isset($arr["webhook$value"])){
		if($arr["webhook$value"]!=""){
			$url = $arr["webhook$value"];
			$url = str_replace("<imgurl>", urlencode($jsonarr['image']) , $url);
			file_get_contents($url);
		}
	} // end webhook2

} // end json data exists
