<?php
require_once "config.php";

if(isset($_REQUEST['f'])){

	if(isset($_REQUEST['t'])){
		// remove video and image
		$file = $_REQUEST['f'];
		$file = basename($file);
		$file = $folder_video_archive.$file;
		unlink($file);
		unlink(str_replace(".avi",".jpg", $file));
		echo json_encode( array("success"=>true));		

	}else{
		// remove image
		$file = $_REQUEST['f'];
		$file = basename($file);
		$file = $folder_img_archive.$file;
		unlink($file);
		echo json_encode( array("success"=>true));		
	}

}