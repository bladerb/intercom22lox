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

			// Mehrere Ausloeser (Klingel, Briefkasten, Bewegungsmelder) unterscheiden:
			// ?trigger=NAME landet im Archiv-Dateinamen, in der JSON-Antwort und
			// als eigenes MQTT-Topic intercom22lox/trigger/NAME.
			$trigger = "";
			if(isset($_REQUEST['trigger'])){
				$trigger = preg_replace('/[^A-Za-z0-9_\-]/', '', substr($_REQUEST['trigger'], 0, 32));
			}
			$triggerpart = ($trigger !== "") ? "-".$trigger : "";

         	if(!isset($_REQUEST['hook'])){ // archive nur wenn über hook call aufgerufen
				$archiveimg = $folder_img_archive.date("Y.m.d-H:i:s").$triggerpart."-intercom.jpg";
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

	// Optionale Objekterkennung ueber einen selbst betriebenen Dienst
	// (CodeProject.AI oder DeepStack, Endpunkt /v1/vision/detection).
	// Damit laesst sich in Loxone unterscheiden, ob eine Person vor der Tuer
	// steht oder nur eine Katze vorbeigelaufen ist. Das Bild verlaesst das
	// Heimnetz nicht. Ist die Funktion aus, wird der Block uebersprungen.
	$ai = array();
	if(isset($arr['ai_enable']) && $arr['ai_enable']=="on" && !empty($arr['ai_url']) && function_exists('curl_init')){
		$minconf = (isset($arr['ai_minconf']) && is_numeric($arr['ai_minconf'])) ? ((float)$arr['ai_minconf'])/100 : 0.5;
		$ch = curl_init($arr['ai_url']);
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_TIMEOUT, 15);
		curl_setopt($ch, CURLOPT_POSTFIELDS, array(
			'image' => new CURLFile(__DIR__.'/lastpicture.jpg', 'image/jpeg', 'lastpicture.jpg'),
			'min_confidence' => $minconf,
		));
		$airesp = @curl_exec($ch);
		curl_close($ch);
		$aidata = @json_decode((string)$airesp, true);
		if(is_array($aidata) && isset($aidata['predictions']) && is_array($aidata['predictions'])){
			$labels = array();
			foreach($aidata['predictions'] as $p){
				if(isset($p['label'])){
					$labels[] = $p['label'].(isset($p['confidence']) ? " (".round($p['confidence']*100)."%)" : "");
				}
			}
			$ai = array("objects"=>$labels, "count"=>count($labels));
		}
	}

	$url = str_replace(basename($_SERVER['REQUEST_URI']), "", $_SERVER['REQUEST_URI']);
	$json = json_encode(array("success"=>true,"timestamp"=>date("d.m.Y-H:i:s"),"trigger"=>(isset($trigger)?$trigger:""),"ai"=>$ai,"image"=>'http://'.$_SERVER['HTTP_HOST'].$url.'lastpicture.jpg'));
	echo $json;
	$jsonarr = json_decode($json,true);

	// Bild an ein Anzeigegeraet schicken (App "Notifications for Android TV",
	// Standard-Port 7676). Ersetzt das Beispielskript unter tv/send.py, das
	// eine fest eingetragene Adresse und eine zusaetzliche Python-Bibliothek
	// voraussetzte - hier genuegen zwei Felder in den Einstellungen.
	if(isset($arr['tv_enable']) && $arr['tv_enable']=="on" && !empty($arr['tv_ip']) && function_exists('curl_init')){
		$tvport = (isset($arr['tv_port']) && is_numeric($arr['tv_port'])) ? (int)$arr['tv_port'] : 7676;
		$tvmsg = "Jemand hat geklingelt";
		if(isset($trigger) && $trigger !== ""){ $tvmsg = "Ausloeser: ".$trigger; }
		$ch = curl_init('http://'.$arr['tv_ip'].':'.$tvport.'/');
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_TIMEOUT, 5);
		curl_setopt($ch, CURLOPT_POSTFIELDS, array(
			'type' => '0',
			'title' => 'Loxone Intercom',
			'msg' => $tvmsg,
			'duration' => '10',
			'fontsize' => '0',
			'position' => '0',
			'bkgcolor' => '#009688',
			'transparency' => '0',
			'offset' => '0',
			'app' => 'intercom22lox',
			'force' => 'true',
			'interrupt' => '0',
			'filename' => new CURLFile(__DIR__.'/lastpicture.jpg', 'image/jpeg', 'lastpicture.jpg'),
		));
		@curl_exec($ch);
		curl_close($ch);
	}


	// hook nicht aufrufen wenn aus webfrontend aufgerufen
	if(isset($_REQUEST['hook'])){
		exit;
	}

	// TODO abfrage wenn credentials noch nicht gestezt ueberspringen
	if ( isset($arr['mqtt_enable']) ){
		if ( $arr['mqtt_enable']=="1" ){
			//MQTT parameter
			if (!isset($arr['mqtt_uselocal']) || $arr['mqtt_uselocal']=="1") {
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
			    if(isset($trigger) && $trigger !== ""){
			        $mqtt->publish("intercom22lox/trigger/".$trigger, $json, 0, 1);
			    }
			    if(!empty($ai)){
			        $mqtt->publish("intercom22lox/ai", json_encode($ai), 0, 1);
			    }
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
