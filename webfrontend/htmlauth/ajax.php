<?php
require_once "loxberry_web.php";

$folder = LBPHTMLDIR.'/archive/';
$foldervideo = LBPHTMLDIR.'/videoarchive/';

// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);


if(isset($_REQUEST['f'])){


	if(isset($_REQUEST['t'])){
		// remove video and image
		$file = $_REQUEST['f'];
		$file = basename($file);
		$file = $foldervideo.$file;
		unlink($file);
		unlink(str_replace(".avi",".jpg", $file));
		echo json_encode( array("success"=>true));		

	}else{
		// remove image
		$file = $_REQUEST['f'];
		$file = basename($file);
		$file = $folder.$file;
		unlink($file);
		echo json_encode( array("success"=>true));		
	}


}