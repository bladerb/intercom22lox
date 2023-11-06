<?php
require_once "config.php";
// This will read your language files to the array $L
$L = LBSystem::readlanguage("language.ini");

$template_title = "intercom22Lox";
$helplink = "https://github.com/bladerb/intercom22lox/";
$helptemplate = "help.html";

$www_folder = str_replace("/opt/loxberry/webfrontend","",$folder_video_archive);

$loxberryip = $_SERVER['HTTP_HOST'];

require_once "menu.php";
// Activate the first element
$navbar[4]['active'] = True;
  
// Now output the header, it will include your navigation bar
LBWeb::lbheader($template_title, $helplink, $helptemplate);



if(isset($_REQUEST['submit'])){
    $filetype = '*.jpg';    
    $files = glob($folder_video_archive.$filetype);    
    $files = array_reverse($files);
    foreach($files as $file){
        $file2 = str_replace(".jpg",".avi",$file);
        @unlink($file);
    }
    echo $L['COMMON.DELETEALLSUCCESS'];
}


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
    $filename = basename($filename);
    $filename = str_replace("-intercom.jpg","",$filename);
    $filename_arr = explode("-",$filename);

    $date_arr= explode("_",$filename_arr[0]);
    $date = $date_arr[2].".".$date_arr[1].".".$date_arr[0];
    $date = $date." - ".str_replace("_",":",$filename_arr[1])." Uhr (".$filename_arr[2].")";
    return $date;
}


?>
<script type="text/javascript" src="script.js"></script>
<div style="display:none;" id="DELALLCONFIRM"><?=$L['COMMON.DELALLCONFIRM']?></div>
<h1><?=$L['COMMON.HELLO']?></h1>
<h2><?=$L['COMMON.BACKUPVIDEO']?></h2>
<p><?=$L['COMMON.BACKUPVIDEOTXT']?></p>
<p><a href="http://<?= $loxberryip; ?>/plugins/intercom22lox/getvideo.php?s=10" target="_blank">http://<?= $loxberryip; ?>/plugins/intercom22lox/getvideo.php?s=10</a></p>
<p>Optional Param ?s=SECONDS</p>
<p><?=$L['COMMON.BACKUPVIDEOTXT2']?></p>

<p><?=$L['COMMON.BACKUPVIDEOTXT3']?></p>

<style type="text/css">
    .galleryvideo .container{
        width: 250px;
        float: left;
        margin: 5px;
    }
    .galleryvideo{ display:block; }
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

    <input type="button" name="button" id="delallvideo" value="<?=$L['COMMON.DELETEALL']?>">


<?php 

$filetype = '*.jpg';    
$files = glob($folder_video_archive.$filetype);    
$files = array_reverse($files);
$total = count($files);    
$per_page = 20;    
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

    echo "<b> ".$L['COMMON.GALINFO2']."</b> $total - <b>".$L['COMMON.PAGE']."</b> $page/$last_page";        
    show_pagination($page, $last_page);        
    
    ?><div class="galleryvideo"> <?php

    for($i = $offset; $i< $max; $i++){
        $file = $files[$i];
        $path_parts = pathinfo($file);
        $filename = $path_parts['filename'];        
        // echo '<img  src="'.$www_folder.basename($file).'" ><div>DATUM</div></a>';                

?>


<div class="container">
    <a rel="group" href="<?php echo $www_folder.basename(str_replace(".jpg",".avi",$file)); ?>" target="_blank">
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