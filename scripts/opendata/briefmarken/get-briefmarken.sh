#!/bin/bash

# Vorhersage 16er Blatt farbig/SW      
# 000 h - 036 h: https://opendata.dwd.de/weather/charts/forecasts/icon/global/na/Z__C_EDZW_LATEST_nwv01,ico_hptfg_na_N_000036_999999_LATEST_WV1.png
# 036 h - 072 h: https://opendata.dwd.de/weather/charts/forecasts/icon/global/na/Z__C_EDZW_LATEST_nwv01,ico_hptfg_na_N_036072_999999_LATEST_470.png
# 084 h - 120 h: https://opendata.dwd.de/weather/charts/forecasts/icon/global/na/Z__C_EDZW_LATEST_nwv01,ico_hptfg_na_N_084120_999999_LATEST_470.png
# 132 h - 168 h: https://opendata.dwd.de/weather/charts/forecasts/icon/global/na/Z__C_EDZW_LATEST_nwv01,ico_hptfg_na_N_084120_999999_LATEST_470.png

# Exit if script is already running.
exec 9>${0%.*}.lck; flock -n 9 || exit 1

base=$(realpath $(dirname $0))
data=$base/data
. $base/../../get-url.sh

[ "$1" = "--force" ] && rm -f $data/*

resultFilename=B_OD__VHS_NA_16er_Blatt_
fileExtension=png
destFolder=/media/Daten/Medien/
updated=false


# 1: time start
# 2: product
function get-karte() {
  timeStart=$1
  opendataFilename=$2
  opendataUrl=https://opendata.dwd.de/weather/charts/forecasts/icon/global/na
  opendataFile=$opendataFilename.$fileExtension

  if get-url $opendataUrl/$opendataFile $data/$opendataFile; then
    updated=true
    
    timeSuffix=$(printf "%.3d\n" $timeStart)
    convert $data/$opendataFile -crop 0x817+0+0    $data/$resultFilename$timeSuffix.$fileExtension
    cp -p $data/$resultFilename$timeSuffix.$fileExtension $destFolder
    
    timeStart=$(($timeStart + 12))
    timeSuffix=$(printf "%.3d\n" $timeStart)
    convert $data/$opendataFile -crop 0x817+0+817  $data/$resultFilename$timeSuffix.$fileExtension
    cp -p $data/$resultFilename$timeSuffix.$fileExtension $destFolder
    
    timeStart=$(($timeStart + 12))
    timeSuffix=$(printf "%.3d\n" $timeStart)
    convert $data/$opendataFile -crop 0x817+0+1634 $data/$resultFilename$timeSuffix.$fileExtension
    cp -p $data/$resultFilename$timeSuffix.$fileExtension $destFolder
    
    timeStart=$(($timeStart + 12))
    timeSuffix=$(printf "%.3d\n" $timeStart)
    convert $data/$opendataFile -crop 0x817+0+2451 $data/$resultFilename$timeSuffix.$fileExtension
    cp -p $data/$resultFilename$timeSuffix.$fileExtension $destFolder
  fi
}


get-karte 0 "Z__C_EDZW_LATEST_nwv01,ico_hptfg_na_N_000036_999999_LATEST_WV1"
get-karte 36 "Z__C_EDZW_LATEST_nwv01,ico_hptfg_na_N_036072_999999_LATEST_470"
get-karte 84 "Z__C_EDZW_LATEST_nwv01,ico_hptfg_na_N_084120_999999_LATEST_470"
get-karte 132 "Z__C_EDZW_LATEST_nwv01,ico_hptfg_na_N_132168_999999_LATEST_470"


[ $updated = false ] && exit 0


# Video erstellen
delayMilliseconds=60
videoFilename=F_OD__VHS_NA_16er-Blatt

rm -f $data/*_latest_*
rm -f $data/*_first_*

latest=$(ls -r $data/${resultFilename}* | head -1)
cp $latest ${latest%.*}_latest_1.$fileExtension
cp $latest ${latest%.*}_latest_2.$fileExtension

first=`ls $data/${resultFilename}* | head -1`
cp $first ${first%.*}_first_1.$fileExtension
cp $first ${first%.*}_first_2.$fileExtension

convert -delay $delayMilliseconds -loop 0 $data/${resultFilename}* $data/$videoFilename.gif
convert $data/$videoFilename.gif -thumbnail 60% -unsharp 0x.5 $data/${videoFilename}_klein.gif
cp -p $data/*.gif $destFolder
