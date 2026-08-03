<?php

require_once "loxberry_io.php";
require_once "loxberry_web.php";
require_once "loxberry_system.php";

$legacyfolder = "/opt/loxberry/webfrontend/legacy/intercom22lox_data/";

// Konfigurierbarer Speicherort (z.B. externer USB-Speicher):
// Ist in den Einstellungen ein Pfad hinterlegt und beschreibbar, wird
// /webfrontend/legacy/intercom22lox_data als Symlink dorthin gefuehrt -
// alle Archiv-URLs funktionieren dadurch unveraendert weiter.
$icfg = array();
if (defined('LBPCONFIGDIR') && file_exists(LBPCONFIGDIR.'/data.json')) {
	$icfg = json_decode(file_get_contents(LBPCONFIGDIR.'/data.json'), true);
	if (!is_array($icfg)) { $icfg = array(); }
}
$storage = isset($icfg['storage_path']) ? rtrim(trim($icfg['storage_path']), '/') : '';
if ($storage !== '' && is_dir($storage) && is_writable($storage)) {
	$target = $storage . '/intercom22lox_data';
	if (!file_exists($target)) { @mkdir($target, 0777, true); }
	$linkbase = rtrim($legacyfolder, '/');
	if (is_link($linkbase)) {
		if (readlink($linkbase) !== $target) { @unlink($linkbase); @symlink($target, $linkbase); }
	} elseif (is_dir($linkbase)) {
		// vorhandene Daten einmalig auf den neuen Speicher uebernehmen
		@shell_exec('cp -rn ' . escapeshellarg($linkbase) . '/. ' . escapeshellarg($target) . '/ 2>/dev/null');
		@shell_exec('rm -rf ' . escapeshellarg($linkbase));
		@symlink($target, $linkbase);
	} else {
		@symlink($target, $linkbase);
	}
}

if (!file_exists($legacyfolder)) {
	mkdir("/opt/loxberry/webfrontend/legacy/intercom22lox_data",0777);
}

$folder_img_archive = $legacyfolder."img_archive/";
$folder_video_archive = $legacyfolder."video_archive/";

if (!file_exists($folder_img_archive)) {
	mkdir($folder_img_archive,0777);
} 

if (!file_exists($folder_video_archive)) {
	mkdir($folder_video_archive,0777);
}

$folder_timelapse = $legacyfolder."timelapse/";
if (!file_exists($folder_timelapse)) {
	@mkdir($folder_timelapse,0777);
}

