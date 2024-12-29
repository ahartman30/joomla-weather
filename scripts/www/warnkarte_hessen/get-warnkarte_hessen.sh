#!/bin/bash

# https://www.dwd.de/DWD/warnungen/warnapp_gemeinden/json/warnungen_gemeinde_map_hes.png

# Exit if script is already running.
exec 9>${0%.*}.lck; flock -n 9 || exit 1

opendataUrl=https://www.dwd.de/DWD/warnungen/warnapp_gemeinden/json
opendataFilename=warnungen_gemeinde_map_hes
resultFilename=B_WWW_WARN_DL_Warnkarte_Hessen
fileExtension=png
destFolder=/media/Daten/Medien/

opendataFile=$opendataFilename.$fileExtension
resultFile=$resultFilename.$fileExtension

base=$(realpath $(dirname $0))
. $base/../../get-url.sh
data=$base/data
[ "$1" = "--force" ] && rm -f $data/*

get-url $opendataUrl/$opendataFile $data/$opendataFile || exit 0

# Add data and localization point
composite -gravity Center $base/overlay_heusenstamm.png $data/$opendataFile $data/$resultFile

cp -p $data/$resultFile $destFolder
