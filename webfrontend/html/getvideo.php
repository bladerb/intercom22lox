<?php
require_once "../../../htmlauth/plugins/intercom22lox/config.php";

ini_set('max_execution_time', 120);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$seconds = 20;
if(isset($_REQUEST['s']) && is_numeric($_REQUEST['s'])){
	$seconds = $_REQUEST['s'];
}


if(file_exists(LBPCONFIGDIR.'/data.json')){
	$arr = json_decode(file_get_contents(LBPCONFIGDIR.'/data.json'),true);
}

$videofile = $folder_video_archive.date("Y_m_d-H_i_s")."-SECONDSs-intercom.avi";

$videofile = str_replace("SECONDS",$seconds,$videofile);
$video_tn_file = str_replace(".avi",".jpg",$videofile);

// $time= replaceSpecialFfmpegChars();

// box=1:boxcolor=white

$timestampstring ="";
if(isset($arr['timestamp_video'])){
	if($arr['timestamp_video']=="on"){
		$timestampstring = ' -vf "drawtext=text=\'%{localtime\:%d\.%m\.%Y %H\\\\\\\\\\\:%M\\\\\\\\\\\:%S}\':x=10:y=10:fontsize=24:fontcolor=white:box=1:boxcolor=black" ';
	}
}



$command = 	'(ffmpeg -f mjpeg -t '.$seconds.' -r 20 -i "http://192.168.86.3/plugins/intercom22lox/mjpgproxy.php" '.$timestampstring.
			' -r 5 '.$videofile." ; ffmpeg -i $videofile -ss 00:00:02 -vframes 1 -q:v 2 $video_tn_file )";

// for debug
// echo $command;exit;

// run command in background
shell_exec(sprintf('%s > /dev/null 2>&1 &', $command));

header('Content-type:application/json;charset=utf-8');

$url = str_replace(basename($_SERVER['REQUEST_URI']), "", $_SERVER['REQUEST_URI']);
$videofile = str_replace("./","",$videofile);
$videofile = 'http://'.$_SERVER['HTTP_HOST'].$url.$videofile;
$json = json_encode(array("success"=>true,"timestamp"=>date("d.m.Y-H:i:s"),"videofile"=>$videofile,"length"=>$seconds));
echo $json;

?>