<?php
require_once "loxberry_web.php";
require_once "loxberry_system.php";
// This will read your language files to the array $L
$L = LBSystem::readlanguage("language.ini");

$template_title = "intercom22Lox";
$helplink = "http://www.loxwiki.eu:80/x/2wzL";
$helptemplate = "help.html";

$www_folder ="/plugins/intercom22lox/archive/";
$folder = LBPHTMLDIR.'/archive/';

require_once "menu.php";
// Activate the first element
$navbar[3]['active'] = True;
  
// Now output the header, it will include your navigation bar
LBWeb::lbheader($template_title, $helplink, $helptemplate);
 

function show_pagination($current_page, $last_page){
    global $L;
    echo '<div class="pagination">';
    if( $current_page > 1 ){
        echo ' <a href="?page='.($current_page-1).'"> &lt;&lt;'.$L['COMMON.PREV'].' </a> ';
    }
    if( $current_page < $last_page ){
        echo ' &nbsp; <a href="?page='.($current_page+1).'"> '.$L['COMMON.NEXT'].'&gt;&gt; </a> ';  
    }
    echo '</div>';
}

function getDateFromFilename($filename){
    // /plugins/intercom22lox/archive/2023.01.27-21:19:27-intercom.jpg
    $filename = str_replace("/plugins/intercom22lox/archive/","",$filename);
    $filename = str_replace("-intercom.jpg","",$filename);
    $filename_arr = explode("-",$filename);
    $date_arr= explode(".",$filename_arr[0]);
    $date = $date_arr[2].".".$date_arr[1].".".$date_arr[0];
    $date = $date." - ".$filename_arr[1]." Uhr";
    return $date;
}


?>
<script type="text/javascript" src="script.js"></script>
<h1><?=$L['COMMON.HELLO']?></h1>
<h2><?=$L['COMMON.BACKUP']?></h2>
<p><?=$L['COMMON.BACKUPTXT']?></p>

<style type="text/css">
    .gallery .container{
        width: 250px;
        float: left;
        margin: 5px;
    }
    .gallery{ display:block; }
    .pagination{
        width: 100%;
        text-align: center;
    }
    .pagination a{
        text-decoration: none;
    }




/* Container holding the image and the text */
.container {
    position: relative;
    text-align: center;
    color: white;
    font-family: arial;
    text-shadow: none;
    font-weight: normal;
    font-size: 12px;
    cursor: pointer;
}



/* Bottom right text */
.bottom-right {
    background-color: #000;
    color:#fff;
    position: absolute;
    bottom: 10px;
    right: 10px;
    padding: 5px;
}

.bottom-left {
    background-color: #000;
    color:#fff;
    position: absolute;
    bottom: 10px;
    left: 10px;
    padding: 5px;
}





  </style>


<?php 

$filetype = '*.jpg';    
$files = glob($folder.$filetype);    
$files = array_reverse($files);
$total = count($files);    
$per_page = 18;    
$last_page = (int)($total / $per_page);    
if(isset($_GET["page"])  && ($_GET["page"] <=$last_page) && ($_GET["page"] > 0) ){
    $page = $_GET["page"];
    $offset = ($per_page + 1)*($page - 1);      
}else{
    $page=1;
    $offset=0;      
}    
$max = $offset + $per_page;    
if($max>$total){
    $max = $total;
}

echo "<b> ".$L['COMMON.GALINFO1']."</b> $total - <b>".$L['COMMON.PAGE']."</b> $page/$last_page";        
show_pagination($page, $last_page);        
    
    ?><div class="gallery"> <?php

    for($i = $offset; $i< $max; $i++){
        $file = $files[$i];
        $path_parts = pathinfo($file);
        $filename = $path_parts['filename'];        
?>

    <div class="container">
        <a rel="group" href="<?php echo $www_folder.basename($file); ?>" target="_blank">
            <img src="<?php echo $www_folder.basename($file); ?>" style="width:100%;">
        </a>
      <div class="bottom-right"><?php echo getDateFromFilename($www_folder.basename($file)) ?></div>
      <div class="bottom-left delbtn">X</div>
    </div>

<?php
    }        

echo "</div>";
// Finally print the footer 
LBWeb::lbfooter();
?>