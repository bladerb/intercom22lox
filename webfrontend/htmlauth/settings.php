<?php
require_once "loxberry_web.php";
require_once "loxberry_system.php";

// This will read your language files to the array $L
$L = LBSystem::readlanguage("language.ini");

$template_title = "intercom22Lox";
$helplink = "http://www.loxwiki.eu:80/x/2wzL";
$helptemplate = "help.html";

// The Navigation Bar
$navbar[1]['Name'] = $L['COMMON.NAVSTART'];
$navbar[1]['URL'] = 'index.php';

$navbar[2]['Name'] = $L['COMMON.BACKUP'];
$navbar[2]['URL'] = 'archive.php';


$navbar[3]['Name'] = $L['COMMON.NAVSETTINGS'];
$navbar[3]['URL'] = 'settings.php';

// Activate the first element
$navbar[3]['active'] = True;
  
// Now output the header, it will include your navigation bar
LBWeb::lbheader($template_title, $helplink, $helptemplate);
 
$jsonconfigfile = LBPDATADIR.'/data.json';

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

?>
<h1><?=$L['COMMON.HELLO']?></h1>
<h2><?=$L['COMMON.NAVSETTINGS']?></h2>

<p><?= str_replace("LOXBERRYIP",$loxberryip,$L['COMMON.MANUAL1']); ?></p>



<p><?= str_replace("LOXBERRYIP",$loxberryip,$L['COMMON.MANUAL2']); ?></p>

<form name="form1" method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>" >

	<h3>Intercom</h3>
	<?=$L['COMMON.LABEL_INTERCOMIP']?><br>
	<input type="text" name="intercomip" value="<?php echo $arr['intercomip']; ?>"><br>

	<h3>Webhooks</h3>

	<b>Webhook1</b> (JSON POST String equals result from <a href="http://<?= $loxberryip; ?>/plugins/intercom22lox/getpicture.php" target="_blank">http://<?= $loxberryip; ?>/plugins/intercom22lox/getpicture.php</a> ):<br>
	<input type="text" name="webhook1" value="<?php echo $arr['webhook1']; ?>"><br>
	<b>Webhook2</b> (GET Params use &lt;imgurl&gt; in url params for imageurl ):<br>
	<input type="text" name="webhook2" value="<?php echo $arr['webhook2']; ?>"><br>


	<br>
	<hr><br><br>

	<h3>MQTT Webhooks</h3>

	<p><?= $L['COMMON.MANUAL3']; ?></p>

	<div class="ui-checkbox"><label class="control-label ui-btn ui-corner-all ui-btn-inherit ui-btn-icon-left ui-checkbox-off" for="mqtt_enable">enable MQTT (Topic: intercom22lox)</label><input type="checkbox" name="mqtt_enable" value="1" id="mqtt_enable" <?php if ( $arr['mqtt_enable']=="1" ){ echo ' checked="checked" '; }; ?>></div>

	<div class="ui-checkbox"><label class="control-label ui-btn ui-corner-all ui-btn-inherit ui-btn-icon-left ui-checkbox-off" for="mqtt_uselocal">Use Loxberry MQTT Gateway credentials</label><input type="checkbox" name="mqtt_uselocal" value="1" id="mqtt_uselocal" <?php if ( $arr['mqtt_uselocal']=="1" ){ echo ' checked="checked" '; }; ?>></div>

	<br>
	<b>or use custom MQTT Credentials</b><br><br>

	<b>MQTT Broker Server:</b><br>
	<input type="text" name="mqtt_server" value="<?php echo $arr['mqtt_server']; ?>"><br>	
	<b>MQTT Broker Port:</b><br>
	<input type="text" name="mqtt_port" value="<?php echo $arr['mqtt_port']; ?>"><br>	
	<b>MQTT Broker Username:</b><br>
	<input type="text" name="mqtt_user" value="<?php echo $arr['mqtt_user']; ?>"><br>	
	<b>MQTT Broker Password:</b><br>
	<input type="password" name="mqtt_password" value="<?php echo $arr['mqtt_password']; ?>"><br>	




	
	<input type="submit" name="submit" value="<?=$L['COMMON.SUBMIT']?>">
</form>
 
<?php 
// Finally print the footer 
LBWeb::lbfooter();
?>