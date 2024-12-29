#!/bin/bash

# https://www.dwd.de/DWD/wetter/radar/radfilm_brd_akt.gif

# Exit if script is already running.
exec 9>${0%.*}.lck; flock -n 9 || exit 1

opendataUrl=https://www.dwd.de/DWD/wetter/radar
opendataFilename=radfilm_brd_akt
resultFilename=F_WWW_RAD_DL_WEB_Radarfilm_Deutschland_klein
fileExtension=gif
destFolder=/media/Daten/Medien/

opendataFile=$opendataFilename.$fileExtension
resultFile=$resultFilename.$fileExtension

base=$(realpath $(dirname $0))
. $base/../../get-url.sh
data=$base/data
[ "$1" = "--force" ] && rm -f $data/*

get-url $opendataUrl/$opendataFile $data/$opendataFile || exit 0

convert $data/$opendataFile -thumbnail 60% -unsharp 0x.5 $data/$resultFile
cp -p $data/$resultFile $destFolder
