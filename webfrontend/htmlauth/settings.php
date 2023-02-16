<?php
require_once "config.php";

// This will read your language files to the array $L
$L = LBSystem::readlanguage("language.ini");

$template_title = "intercom22Lox";
$helplink = "https://github.com/bladerb/intercom22lox/";
$helptemplate = "help.html";

require_once "menu.php";
// Activate the first element
$navbar[5]['active'] = True;
  
// Now output the header, it will include your navigation bar
LBWeb::lbheader($template_title, $helplink, $helptemplate);
 
$jsonconfigfile = LBPCONFIGDIR.'/data.json';

$loxberryip = $_SERVER['HTTP_HOST'];

if(isset($_REQUEST['submit'])){
	$json = json_encode($_REQUEST);
	file_put_contents($jsonconfigfile, $json);
}

if(file_exists($jsonconfigfile)){
	$arr = json_decode(file_get_contents($jsonconfigfile),true);
}else{
	$arr=[];
	$arr['webhook1']="";
	$arr['webhook2']="";
}


if($arr['timestamp_image']=="on") $arr['timestamp_image']=" checked ";
if($arr['timestamp_video']=="on") $arr['timestamp_video']=" checked ";


?>
<h1><?=$L['COMMON.HELLO']?></h1>
<h2><?=$L['COMMON.NAVSETTINGS']?></h2>

<p><?= str_replace("LOXBERRYIP",$loxberryip,$L['COMMON.MANUAL1']); ?></p>


<form name="form1" method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>" >

	<div class="wide">Intercom</div>
	<br>

	<div data-role="fieldcontain">
		<label for="intercomip"><?=$L['COMMON.LABEL_INTERCOMIP']?></label>
		<input type="text" name="intercomip" value="<?php echo $arr['intercomip']; ?>"><br>
		<p class="hint">z.B. 192.168.86.5:80</p>
	</div>

	
	<div class="wide">Zeitstempel</div>


	<div data-role="fieldcontain">
		<fieldset data-role="controlgroup">
			<legend>Soll ein Zeitstempel ausgegeben werden</legend>
			<input type="checkbox" name="timestamp_image" id="Main.use_http" <?php echo $arr['timestamp_image']; ?>>
			<label for="Main.use_http">Zeitstempel auf den Bildern</label>
			<input type="checkbox" name="timestamp_video" id="Main.use_udp" <?php echo $arr['timestamp_video']; ?>>
			<label for="Main.use_udp">Zeitstempel auf den Videos</label>
			<p class="hint">Hier kannst du einstellen ob ein Zeitsstempel inerhalb der Videos / Bilder angezeigt werden soll.</p>
		</fieldset>
	<div>

	<div class="wide">Image Webhooks</div>

	<p><?= str_replace("LOXBERRYIP",$loxberryip,$L['COMMON.MANUAL2']); ?></p>

	<div data-role="fieldcontain">
		<label for="webhook1">Webhook 1 (POST)</label>
		<input type="text" name="webhook1" value="<?php echo $arr['webhook1']; ?>">
		<p class="hint">POST JSON String to given URL equals result from <a href="http://<?= $loxberryip; ?>/plugins/intercom22lox/getpicture.php" target="_blank">http://<?= $loxberryip; ?>/plugins/intercom22lox/getpicture.php</a></p>
	</div>


	<div data-role="fieldcontain">
		<label for="webhook2">Webhook 2 (GET)</label>
		<input type="text" name="webhook2" value="<?php echo $arr['webhook2']; ?>">
		<p class="hint">GET Params use &lt;imgurl&gt; in url params for imageurl. Example: http://192.168.86.2:8087/myservice/?value=&lt;imgurl&gt;</p>
	</div>

	<div data-role="fieldcontain">
		<label for="webhook3">Webhook 3 (POST)</label>
		<input type="text" name="webhook3" value="<?php echo $arr['webhook3']; ?>">
		<p class="hint">POST JSON String to given URL equals result from <a href="http://<?= $loxberryip; ?>/plugins/intercom22lox/getpicture.php" target="_blank">http://<?= $loxberryip; ?>/plugins/intercom22lox/getpicture.php</a></p>
	</div>

	<div data-role="fieldcontain">
		<label for="webhook4">Webhook 4 (GET)</label>
		<input type="text" name="webhook4" value="<?php echo $arr['webhook4']; ?>">
		<p class="hint">GET Params use &lt;imgurl&gt; in url params for imageurl. Example: http://192.168.86.2:8087/myservice/?value=&lt;imgurl&gt;</p>
	</div>


	<div class="wide">Video Webhooks</div>

	<p><?= str_replace("LOXBERRYIP",$loxberryip,$L['COMMON.MANUAL4']); ?></p>

	<div data-role="fieldcontain">
		<label for="videowebhook1">Video Webhook 1 (POST)</label>
		<input type="text" name="videowebhook1" value="<?php echo $arr['videowebhook1']; ?>">
		<p class="hint">POST JSON String to given URL equals result from <a href="http://<?= $loxberryip; ?>/plugins/intercom22lox/getvideo.php?s=10" target="_blank">http://<?= $loxberryip; ?>/plugins/intercom22lox/getvideo.php?s=10</a></p>
	</div>


	<div data-role="fieldcontain">
		<label for="videowebhook2">Video Webhook 2 (GET)</label>
		<input type="text" name="videowebhook2" value="<?php echo $arr['videowebhook2']; ?>">
		<p class="hint">GET Params use &lt;imgurl&gt; in url params for video fileurl. Example: http://192.168.86.2:8087/myservice/?value=&lt;fileurl&gt;</p>
	</div>



	<div class="wide">Webhooks MQTT</div>

	<p><?= $L['COMMON.MANUAL3']; ?></p>


	<div data-role="fieldcontain">
		<fieldset data-role="controlgroup">
			<legend>Use local MQTT or use custom MQTT</legend>
			<input type="checkbox" name="mqtt_enable" value="1" id="mqtt_enable" <?php if ( $arr['mqtt_enable']=="1" ){ echo ' checked="checked" '; }; ?>>
			<label for="mqtt_enable">enable MQTT (Topic: intercom22lox)</label>
			<input type="checkbox" name="mqtt_uselocal" value="1" id="mqtt_uselocal" <?php if ( $arr['mqtt_uselocal']=="1" ){ echo ' checked="checked" '; }; ?>>
			<label for="mqtt_uselocal">Use Loxberry MQTT Gateway credentials</label>
			<p class="hint">Hier kannst du einstellen ob ein Zeitsstempel inerhalb der Videos / Bilder angezeigt werden soll.</p>
		</fieldset>
	<div>

	<div data-role="fieldcontain">
		<label for="mqtt_server">MQTT Broker Server</label>
		<input type="text" name="mqtt_server" value="<?php echo $arr['mqtt_server']; ?>">
		<p class="hint">z.B. 192.168.86.7</p>
	</div>

	<div data-role="fieldcontain">
		<label for="mqtt_server">MQTT Broker Port</label>
		<input type="text" name="mqtt_port" value="<?php echo $arr['mqtt_port']; ?>">
		<p class="hint">z.B. 192.168.86.7</p>
	</div>

	<div data-role="fieldcontain">
		<label for="mqtt_server">MQTT Broker Username</label>
		<input type="text" name="mqtt_user" value="<?php echo $arr['mqtt_user']; ?>">
		<p class="hint">z.B. 192.168.86.7</p>
	</div>

	<div data-role="fieldcontain">
		<label for="mqtt_server">MQTT Broker Password</label>
		<input type="password" name="mqtt_password" value="<?php echo $arr['mqtt_password']; ?>">
		<p class="hint">z.B. 192.168.86.7</p>
	</div>

	<input type="submit" name="submit" value="<?=$L['COMMON.SUBMIT']?>">
</form>
 
<?php 
// Finally print the footer 
LBWeb::lbfooter();
?>