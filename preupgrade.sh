#!/bin/bash

ARGV0=$0 # Zero argument is shell command
ARGV1=$1 # First argument is temp folder during install
ARGV2=$2 # Second argument is Plugin-Name for scipts etc.
ARGV3=$3 # Third argument is Plugin installation folder
ARGV4=$4 # Forth argument is Plugin version
ARGV5=$5 # Fifth argument is Base folder of LoxBerry

echo "<INFO> Creating temporary folders for upgrading"
mkdir -p /tmp/$ARGV1\_upgrade
mkdir -p /tmp/$ARGV1\_upgrade/config

echo "<INFO> Backing up existing config files"
cp -p -v -r $ARGV5/config/plugins/$ARGV3/ /tmp/$ARGV1\_upgrade/config

echo "<INFO> Backing up old file structure config to existing config files"
cp -p -v -r $ARGV5/data/plugins/$ARGV3/ /tmp/$ARGV1\_upgrade/config

echo "<INFO> Backing up old media files"
mkdir $ARGV5/webfrontend/legacy/intercom22lox_data/
mkdir $ARGV5/webfrontend/legacy/intercom22lox_data/img_archive/
mkdir $ARGV5/webfrontend/legacy/intercom22lox_data/video_archive/

cp -p -v -r $ARGV5/webfrontend/html/plugins/$ARGV3/archive/* $ARGV5/webfrontend/legacy/intercom22lox_data/img_archive/
cp -p -v -r $ARGV5/webfrontend/html/plugins/$ARGV3/videoarchive/* $ARGV5/webfrontend/legacy/intercom22lox_data/video_archive/



# Exit with Status 0
exit 0