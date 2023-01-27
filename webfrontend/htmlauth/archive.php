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

// The Navigation Bar
$navbar[1]['Name'] = $L['COMMON.NAVSTART'];
$navbar[1]['URL'] = 'index.php';

$navbar[2]['Name'] = $L['COMMON.BACKUP'];
$navbar[2]['URL'] = 'archive.php';

$navbar[3]['Name'] = $L['COMMON.NAVSETTINGS'];
$navbar[3]['URL'] = 'settings.php';

// Activate the first element
$navbar[2]['active'] = True;
  
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


?>
<script type="text/javascript" src="script.js"></script>
<h1><?=$L['COMMON.HELLO']?></h1>
<h2><?=$L['COMMON.BACKUP']?></h2>
<p><?=$L['COMMON.BACKUPTXT']?></p>

<style type="text/css">
    .gallery img{
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
</style>


  <style>
    .image-container {
/*      position: relative;*/
    }
    .image-overlay {
        float:left;
/*      position: absolute;
      bottom: 0;
      width: 100%;
      background-color: rgba(255, 0, 0, 0.5);
      color: white;
      padding: 10px;
      text-align: center;*/
    }
  </style>


<?php 

$filetype = '*.*';    
$files = glob($folder.$filetype);    
$files = array_reverse($files);
$total = count($files);    
$per_page = 20;    
$last_page = (int)($total / $per_page);    
if(isset($_GET["page"])  && ($_GET["page"] <=$last_page) && ($_GET["page"] > 0) ){
    $page = $_GET["page"];
    $offset = ($per_page + 1)*($page - 1);      
}else{
    echo "Page out of range showing results for page one";
    $page=1;
    $offset=0;      
}    
$max = $offset + $per_page;    
if($max>$total){
    $max = $total;
}

    echo "<b>".$L['COMMON.GALINFO1']."</b> $total - <b>".$L['COMMON.PAGE']."</b> $page/$last_page";        
    show_pagination($page, $last_page);        
    
    ?><div class="gallery"> <?php

    for($i = $offset; $i< $max; $i++){
        $file = $files[$i];
        $path_parts = pathinfo($file);
        $filename = $path_parts['filename'];        
        // echo '<img  src="'.$www_folder.basename($file).'" ><div>DATUM</div></a>';                

?>
  <div class="image-container">
    <a rel="group" href="<?php echo $www_folder.basename($file); ?>" target="_blank">
        <img src="<?php echo $www_folder.basename($file); ?>">
        <!-- <div class="image-overlay">Hallo Welt</div> -->
    </a>
    
  </div>
<?php


    }        
    echo "</div>";


// Finally print the footer 
LBWeb::lbfooter();
?>