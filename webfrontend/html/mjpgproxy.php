<?php
require_once "../../../htmlauth/plugins/intercom22lox/config.php";

$miniserver_config = LBSystem::get_miniservers();
$arr = json_decode(file_get_contents(LBPCONFIGDIR.'/data.json'),true);

// config:
$mjpeg_url="http://". $miniserver_config[1]["Admin_RAW"] .":". $miniserver_config[1]["Pass_RAW"] ."@". $arr["intercomip"]. "/mjpg/video.mjpg";

// preparing http options (10s connect timeout so the page does not hang forever):
$opts = array(
	'http'=>array(
		'method'=>"GET",
		'timeout'=>10,
		'header'=>"Accept-language: en\r\n" .
		"Cookie: foo=bar\r\n"
	  )
);
$context = stream_context_create($opts);

// set no time limit and disable compression/buffering:
set_time_limit(0);
@apache_setenv('no-gzip', 1);
@ini_set('zlib.output_compression', 0);
@ini_set('output_buffering', 'off');
while (ob_get_level() > 0) { @ob_end_flush(); }
ignore_user_abort(false);

/* Sends an http request with the options shown above */
$fp = @fopen($mjpeg_url, 'r', false, $context);
if ($fp) {
	// Fix v1.4.0: echten Content-Type der Kamera inkl. Boundary weiterreichen.
	// Die alte Version sendete fest boundary=athene, die Kamera nutzt aber eine
	// eigene Boundary - Browser warten dann endlos und zeigen kein Bild.
	$contenttype = 'multipart/x-mixed-replace';
	if (isset($http_response_header) && is_array($http_response_header)) {
		foreach ($http_response_header as $h) {
			if (stripos($h, 'Content-Type:') === 0) {
				$contenttype = trim(substr($h, 13));
				break;
			}
		}
	}
	header("Cache-Control: no-cache");
	header("Cache-Control: private");
	header("Pragma: no-cache");
	header("Content-type: ".$contenttype);

	// pass data (manual loop with flush instead of fpassthru, so frames
	// reach the browser immediately and a client disconnect ends the script)
	stream_set_timeout($fp, 15);
	while (!feof($fp) && !connection_aborted()) {
		$chunk = fread($fp, 8192);
		if ($chunk === false || $chunk === '') {
			$meta = stream_get_meta_data($fp);
			if (!empty($meta['timed_out'])) break;
			continue;
		}
		echo $chunk;
		flush();
	}
	fclose($fp);
} else {
	// error: webcam probably offline
	// send alternative picture:
	$d = file_get_contents("offline.jpg");

	Header("Content-Type: image/jpeg");
	Header("Content-Length: ".strlen($d));
	header("Cache-Control: no-cache");
	header("Cache-Control: private");
	header("Pragma: no-cache");

	echo $d;
}
