<?php
/**
 * intercom22lox - Timelapse: jeden Tag ein Foto zu einer festen Uhrzeit.
 *
 * Wird minuetlich per Cron aufgerufen und beendet sich sofort, wenn die
 * Funktion deaktiviert ist oder die konfigurierte Uhrzeit (HH:MM) nicht der
 * aktuellen Minute entspricht. Die Fotos landen im Ordner "timelapse" des
 * Archiv-Speicherorts (Dateiname = Datum) und lassen sich spaeter z.B. mit
 * ffmpeg zu einem Zeitraffer-Video zusammensetzen:
 *   ffmpeg -pattern_type glob -i 'timelapse/*.jpg' -r 10 zeitraffer.mp4
 */

require_once "../../../htmlauth/plugins/intercom22lox/config.php";

$miniserver_config = LBSystem::get_miniservers();

if (!file_exists(LBPCONFIGDIR.'/data.json')) { exit; }
$arr = json_decode(file_get_contents(LBPCONFIGDIR.'/data.json'), true);
if (!is_array($arr)) { exit; }

if (!isset($arr['timelapse_enable']) || $arr['timelapse_enable'] != "on") { exit; }

$tltime = isset($arr['timelapse_time']) ? trim($arr['timelapse_time']) : '';
if (!preg_match('/^([01]?\d|2[0-3]):([0-5]\d)$/', $tltime, $m)) { exit; }
$soll = sprintf('%02d:%02d', $m[1], $m[2]);
if (date('H:i') !== $soll) { exit; }

// Heute schon aufgenommen? (Schutz bei mehrfachen Cron-Laeufen in derselben Minute)
$outfile = $folder_timelapse . date("Y.m.d") . "-timelapse.jpg";
if (file_exists($outfile)) { exit; }

// Einzelbild aus dem MJPEG-Stream der Intercom holen (gleiche Logik wie getpicture.php)
$camurl = "http://" . $miniserver_config[1]["Admin_RAW"] . ":" . $miniserver_config[1]["Pass_RAW"] . "@" . $arr["intercomip"] . "/mjpg/video.mjpg";
$boundary = "\n--";
$f = @fopen($camurl, "r");
if (!$f) { exit; }
$r = "";
$guard = 0;
while (substr_count($r, "Content-Length") != 2 && $guard++ < 4000) { $r .= fread($f, 512); }
fclose($f);
$start = strpos($r, "\xff");
if ($start === false) { exit; }
$end = strpos($r, $boundary, $start) - 1;
$frame = substr($r, $start, $end - $start);
if ($frame === '' ) { exit; }

file_put_contents($outfile, $frame);

// Optionaler Zeitstempel (wie bei den Archivbildern)
if (isset($arr['timestamp_image']) && $arr['timestamp_image'] == "on" && function_exists('imagecreatefromjpeg')) {
    $img = @imagecreatefromjpeg($outfile);
    if ($img) {
        $white = imagecolorallocate($img, 255, 255, 255);
        $black = imagecolorallocate($img, 0, 0, 0);
        $text = date('d.m.Y H:i:s');
        imagefilledrectangle($img, 9, 29, strlen($text) * imagefontwidth(5) + 11, 45, $black);
        imagestring($img, 5, 10, 30, $text, $white);
        imagejpeg($img, $outfile);
        imagedestroy($img);
    }
}
echo "OK: " . basename($outfile) . "\n";

// Optional: Zeitraffer-Video automatisch neu rendern (im Hintergrund).
// Aus allen Timelapse-Fotos entsteht taeglich ein aktualisiertes MP4.
if (isset($arr['timelapse_video']) && $arr['timelapse_video'] == "on") {
    $video = $folder_timelapse . "zeitraffer.mp4";
    $cmd = 'ffmpeg -y -pattern_type glob -framerate 10 -i ' . escapeshellarg($folder_timelapse . '*-timelapse.jpg')
         . ' -vf "scale=trunc(iw/2)*2:trunc(ih/2)*2" -c:v libx264 -pix_fmt yuv420p ' . escapeshellarg($video);
    shell_exec(sprintf('%s > /dev/null 2>&1 &', $cmd));
    echo "Zeitraffer-Video wird aktualisiert: " . basename($video) . "\n";
}
