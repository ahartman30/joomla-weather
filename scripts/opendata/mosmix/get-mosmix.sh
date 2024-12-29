#!/bin/bash

# Exit if script is already running.
exec 9>${0%.*}.lck; flock -n 9 || exit 1

opendataUrl=https://opendata.dwd.de/weather/local_forecasts/mos/MOSMIX_S/all_stations/kml
opendataFilename=MOSMIX_S_LATEST_240
fileExtension=kmz
destFolder=/media/Daten/Davis/json/

opendataFile=$opendataFilename.$fileExtension

base=$(realpath $(dirname $0))
data=$base/data

[ "$1" = "--force" ] && rm -f $data/*

. $base/../../get-url.sh
get-url $opendataUrl/$opendataFile $data/$opendataFile || exit 0

rm -f $data/*.csv $data/*.kml $data/*.json
unzip -q $data/$opendataFile -d $data
kmlFile=`ls $data/*.kml | head -1`
$base/mosmix-kml-tool/mosmix-kml-tool.sh --kml $kmlFile --out $data --stations 10641,10020
rm -f $data/*.kml # free space


function mosmix() {
  station=$1
  stationName=$2
  csvFile=$data/mosmix_$station.csv
  if [ `cat $csvFile | wc -l` -gt 0 ]; then
    $base/mosmix-kml-tool/mosmix2json/mosmix2json.py $csvFile > $data/mosmix_kml_${stationName}.json
		cp -p $data/mosmix_kml_${stationName}.json $destFolder
  fi
}

mosmix "10641" "Heusenstamm"
mosmix "10020" "Sylt"
