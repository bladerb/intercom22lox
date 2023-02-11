<?php

require_once "loxberry_io.php";
require_once "loxberry_web.php";
require_once "loxberry_system.php";

$legacyfolder = "/opt/loxberry/webfrontend/legacy/intercom22lox_data/";

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

