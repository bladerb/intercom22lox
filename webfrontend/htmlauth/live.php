<?php
require_once "config.php";


// This will read your language files to the array $L
$L = LBSystem::readlanguage("language.ini");


$loxberryip = $_SERVER['HTTP_HOST'];

$template_title = "intercom22Lox";
$helplink = "https://github.com/bladerb/intercom22lox/";
$helptemplate = "help.html";

require_once "menu.php";
// Activate the first element
$navbar[2]['active'] = True;
  
// Now output the header, it will include your navigation bar
LBWeb::lbheader($template_title, $helplink, $helptemplate);
 


?>
    <script type="text/javascript" src="script.js"></script>
    <h1><?=$L['COMMON.HELLO']?></h1>
    <p><?=$L['COMMON.LIVETXT']?></p>

<p></p>

<p><a href="http://<?= $loxberryip; ?>/plugins/intercom22lox/mjpgproxy.php" target="_blank">http://<?= $loxberryip; ?>/plugins/intercom22lox/mjpgproxy.php</a></p>


<iframe src="http://<?= $loxberryip; ?>/plugins/intercom22lox/mjpgproxy.php" style="width: 1280px; height: 720px;">



<?php 
// Finally print the footer 
LBWeb::lbfooter();
?>