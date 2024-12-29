#!/bin/bash

# https://opendata.dwd.de/weather/charts/analysis/Z__C_EDZW_LATEST_nwv01,ico_hsy_NA_N_000000_850700_LATEST_WV1.png
# https://opendata.dwd.de/weather/charts/analysis/Z__C_EDZW_LATEST_nwv01,ico_hsy_NA_N_000000_500400_LATEST_WV1.png
# https://opendata.dwd.de/weather/charts/analysis/Z__C_EDZW_LATEST_nwv01,ico_hsy_NA_N_000000_300200_LATEST_WV1.png

# Exit if script is already running.
exec 9>${0%.*}.lck; flock -n 9 || exit 1

base=$(realpath $(dirname $0))
data=$base/data
. $base/../../get-url.sh

[ "$1" = "--force" ] && rm -f $data/*

# 1: [Druckfläche 1 hPa]
# 2: [Druckfläche 2 hPa]
function get-karte() {

	hPa1=$1
	hPa2=$2

	opendataUrl=https://opendata.dwd.de/weather/charts/analysis
	opendataFilename=Z__C_EDZW_LATEST_nwv01,ico_hsy_NA_N_000000_${hPa1}${hPa2}_LATEST_WV1
	resultFilename1=B_OD_ANA_NA_000000_000${hPa1}_DWD
	resultFilename2=B_OD_ANA_NA_000000_000${hPa2}_DWD
	fileExtension=png
	destFolder=/media/Daten/Medien/

	opendataFile=$opendataFilename.$fileExtension
	resultFile1=$resultFilename1.$fileExtension
	resultFile2=$resultFilename2.$fileExtension

	if get-url $opendataUrl/$opendataFile $data/$opendataFile; then
		convert $data/$opendataFile -crop 0x0+0+1103 $data/$resultFile1
		composite -gravity northwest -geometry +5+10 $base/stamp.png $data/$resultFile1 $data/$resultFile1

		convert $data/$opendataFile -crop 0x1103+0+0 $data/$resultFile2
		composite -gravity northwest -geometry +5+10 $base/stamp.png $data/$resultFile2 $data/$resultFile2

		cp -p $data/$resultFile1 $destFolder
		cp -p $data/$resultFile2 $destFolder
	fi
}

get-karte 850 700
get-karte 500 400
get-karte 300 200
