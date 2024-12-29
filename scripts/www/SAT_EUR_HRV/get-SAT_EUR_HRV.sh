#!/bin/bash

# https://www.dwd.de/DWD/wetter/sat/bilder/meteosat/satbild_hrvir_akt.png

# Exit if script is already running.
exec 9>${0%.*}.lck; flock -n 9 || exit 1

opendataUrl=https://www.dwd.de/DWD/wetter/sat/bilder/meteosat
opendataFilename=satbild_hrvir_akt
resultFilename=B_WWW_SAT_EUR_Satbild_Europa_HRV
fileExtension=png
destFolder=/media/Daten/Medien/

opendataFile=$opendataFilename.$fileExtension
resultFile=$resultFilename.$fileExtension

base=$(realpath $(dirname $0))
. $base/../../get-url.sh
data=$base/data

[ "$1" = "--force" ] && rm -f $data/$resultFile

get-url $opendataUrl/$opendataFile $data/$resultFile

if [ $? -eq 0 ]; then
  # New image downloaded
  if [ "$1" = "--force" ]; then
    # Delete latest image, if same as current.
    rm -f $data/*_latest_*
    rm -f $data/*_first_*
    latest=$(ls -r $data/${resultFilename}_* | head -1)
    md5CurrentImage="$(md5sum --binary $data/$resultFile | cut -d " " -f1)"
    md5LastImage="$(md5sum --binary $latest | cut -d " " -f1)"
    [ $md5CurrentImage = $md5LastImage ] && rm -f $latest
  fi
  dateCurrentHour=$(date +%Y-%m-%d_%H00)
  cp -p $data/$resultFile $data/${resultFilename}_$dateCurrentHour.$fileExtension
else
  # Image Not-modified-since current
  exit 0
fi

videoCountImages=12
delayMilliseconds=40
videoFilename=F_WWW_SAT_EUR_Satfilm_Europa_HRV

rm -f $data/*_latest_*
rm -f $data/*_first_*

# Keep videoCountImages images only
ls -r $data/${resultFilename}_* | tail -n +$((videoCountImages+1)) | xargs rm -f

latest=$(ls -r $data/${resultFilename}_* | head -1)
cp $latest ${latest%.*}_latest_1.$fileExtension
cp $latest ${latest%.*}_latest_2.$fileExtension

first=`ls $data/${resultFilename}_* | head -1`
cp $first ${first%.*}_first_1.$fileExtension
cp $first ${first%.*}_first_2.$fileExtension

convert -delay $delayMilliseconds -loop 0 $data/${resultFilename}_* $data/$videoFilename.gif
convert $data/$videoFilename.gif -thumbnail 40% -unsharp 0x.5 $data/${videoFilename}_klein.gif

cp -p $data/$resultFile $destFolder
cp -p $data/*.gif $destFolder
