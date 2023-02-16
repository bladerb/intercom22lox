<?php

require_once "../../../htmlauth/plugins/intercom22lox/config.php";
require_once "phpMQTT/phpMQTT.php";


$miniserver_config = LBSystem::get_miniservers();

if(isset($_REQUEST['file'])){
	$file = $_REQUEST['file'];
}else{
	exit;
}
	
if(file_exists(LBPCONFIGDIR.'/data.json')){

	header('Content-type:application/json;charset=utf-8');
	$arr = json_decode(file_get_contents(LBPCONFIGDIR.'/data.json'),true);


	$www_folder = str_replace("/opt/loxberry/webfrontend","",$folder_video_archive);

	$url = $www_folder;

	$json = json_encode(array("success"=>true,"timestamp"=>date("d.m.Y-H:i:s"),"file"=>'http://'.$_SERVER['HTTP_HOST'].$url.$file));
	echo $json;
	$jsonarr = json_decode($json,true);


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
			    $mqtt->publish("intercom22loxvideo", $json, 0, 1);
			    $mqtt->close();
			} else {
			    echo "MQTT connection error Please set custom Credentials or choose default MQTT Broker from Loxberry";
			}
		} 
	}// end mqtt post


	if(isset($arr["videowebhook1"])){
		if($arr["videowebhook1"]!=""){
			$url = $arr["videowebhook1"];
			$ch = curl_init($url);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $json);
			curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type:application/json'));
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			$result = curl_exec($ch);
			curl_close($ch);
		}
	} // end webhook 1

	if(isset($arr["videowebhook2"])){
		if($arr["videowebhook2"]!=""){
			$url = $arr["videowebhook2"];
			$url = str_replace("<fileurl>", urlencode($jsonarr['file']) , $url);
			file_get_contents($url);
		}
	} // end webhook2



} // end json data exists
