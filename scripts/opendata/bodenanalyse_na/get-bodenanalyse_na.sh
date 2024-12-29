#!/bin/bash

# https://opendata.dwd.de/weather/charts/analysis/Z__C_EDZW_LATEST_tka01,ana_bwkman_dwdna_O_000000_000000_LATEST_WV12.png

# Exit if script is already running.
exec 9>${0%.*}.lck; flock -n 9 || exit 1

opendataUrl=https://opendata.dwd.de/weather/charts/analysis
opendataFilename=Z__C_EDZW_LATEST_tka01,ana_bwkman_dwdna_O_000000_000000_LATEST_WV12
resultFilename=B_OD_ANA_NA_000000_000000_DWD_C
fileExtension=png
destFolder=/media/Daten/Medien/

opendataFile=$opendataFilename.$fileExtension
resultFile=$resultFilename.$fileExtension

base=$(realpath $(dirname $0))
data=$base/data
[ "$1" = "--force" ] && rm -f $data/*

. $base/../../get-url.sh
get-url $opendataUrl/$opendataFile $data/$opendataFile || exit 0

composite -gravity Center $base/overlay_heusenstamm.png $data/$opendataFile $data/$resultFile
composite -gravity northwest -geometry +5+10 $base/stamp.png $data/$resultFile $data/$resultFile
convert $data/$resultFile -thumbnail 10% -unsharp 0x.5 $data/${resultFilename}_klein.$fileExtension

cp -p $data/$resultFile $destFolder
cp -p $data/${resultFilename}_klein.$fileExtension $destFolder

