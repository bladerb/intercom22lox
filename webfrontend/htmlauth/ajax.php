<?php
require_once "loxberry_web.php";

$folder = LBPHTMLDIR.'/archive/';

// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);


if(isset($_REQUEST['f'])){
	$file = $_REQUEST['f'];
	$file = basename($file);
	$file = $folder.$file;
	unlink($file);
	echo json_encode( array("success"=>true));
}