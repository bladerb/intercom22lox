<?php
require_once "loxberry_io.php";
require_once "loxberry_system.php";
require_once "phpMQTT/phpMQTT.php";

$miniserver_config = LBSystem::get_miniservers();


if(file_exists(LBPDATADIR.'/data.json')){

	header('Content-type:application/json;charset=utf-8');
	$arr = json_decode(file_get_contents(LBPDATADIR.'/data.json'),true);

	$camurl="http://". $miniserver_config[1]["Admin_RAW"] .":". $miniserver_config[1]["Pass_RAW"] ."@". $arr["intercomip"]. "/mjpg/video.mjpg";

	$boundary="\n--";
	$f = fopen($camurl,"r") ;
	$r="";
	   if(!$f)
	   {
	        echo "error";
	   }
	    else
	  {
	         while (substr_count($r,"Content-Length") != 2) $r.=fread($f,512);
	         $start = strpos($r,"\xff");
	         $end   = strpos($r,$boundary,$start)-1;
	         $frame = substr("$r",$start,$end - $start);

         	if(!isset($_REQUEST['hook'])){ // archive nur wenn über hook call aufgerufen
				$archiveimg = "archive/".date("Y.m.d-H:i:s")."-intercom.jpg";
				file_put_contents($archiveimg, $frame);
			}	
	         file_put_contents("lastpicture.jpg", $frame);
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

	if(isset($arr['webhook1'])){
		if($arr['webhook1']!=""){
			$url = $arr['webhook1'];
			$ch = curl_init($url);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $json);
			curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type:application/json'));
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			$result = curl_exec($ch);
			curl_close($ch);
		}
	} // end webhook 1

	if(isset($arr['webhook2'])){
		if($arr['webhook2']!=""){

			$url = $arr['webhook2'];
			$url = str_replace("<imgurl>", urlencode($jsonarr['image']) , $url);

			file_get_contents($url);


		}
	} // end webhook2

} // end json data exists



// https://loxwiki.atlassian.net/wiki/spaces/LOXBERRY/pages/1239253670/mqtt+connectiondetails
// daten automatisch vom mqtt plugin nehmen mqtt kram hiervon nehmen
// https://github.com/romanlum/LoxBerry-Plugin-Zigbee2Mqtt/blob/master/bin/update-config.php
// http://nas:8087/set/mqtt.0.devices.mirrorhtml?value=<imgurl>&prettyPrint

// write mqttt data
// MQTT requires a unique client id
