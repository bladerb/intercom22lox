<?php
require_once "loxberry_web.php";


// This will read your language files to the array $L
$L = LBSystem::readlanguage("language.ini");

$template_title = "intercom22Lox";
$helplink = "http://www.loxwiki.eu:80/x/2wzL";
$helptemplate = "help.html";

require_once "menu.php";
// Activate the first element
$navbar[1]['active'] = True;
  
// Now output the header, it will include your navigation bar
LBWeb::lbheader($template_title, $helplink, $helptemplate);
 


?>
    <script type="text/javascript" src="script.js"></script>
    <h1><?=$L['COMMON.HELLO']?></h1>
    <h2><?=$L['COMMON.LASTPIC']?></h2>
    <p><?=$L['COMMON.INDEXTXT']?></p>
    <img src="" class="lastpicture" style="width:600px;">
    <div class="msg"></div>




<?php 
// Finally print the footer 
LBWeb::lbfooter();
?>