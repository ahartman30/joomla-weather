#!/bin/bash

# https://maps.dwd.de/geoserver/dwd/wms?service=WMS&version=1.1.0&request=GetMap&layers=dwd%3ASatellite_meteosat_1km_euat_rgb_day_hrv_and_night_ir108_3h&bbox=-15.074981,34.7373759815055,35.2350956772705,63.567219&width=1100&height=760&srs=EPSG%3A4326&styles=&format=image%2Fjpeg

# Exit if script is already running.
exec 9>${0%.*}.lck; flock -n 9 || exit 1

opendataUrl='https://maps.dwd.de/geoserver/dwd/wms?service=WMS&version=1.1.0&request=GetMap&layers=dwd%3ASatellite_meteosat_1km_euat_rgb_day_hrv_and_night_ir108_3h&bbox=-15.074981,34.7373759815055,35.2350956772705,63.567219&width=1100&height=760&srs=EPSG%3A4326&styles=&format=image%2Fjpeg'
resultFilename=B_GW_SAT_EU_Satbild_Europa_Central_RGB
fileExtension=jpg
destFolder=/media/Daten/Medien/

resultFile=$resultFilename.$fileExtension
dateCurrentHour=$(date +%Y%m%d%H00)

base=$(realpath $(dirname $0))
. $base/../../get-url.sh
data=$base/data

get-url $opendataUrl $data/${resultFilename}_current.$fileExtension || exit 0

# Geoserver doesn't support If-modified-since, always new image downloaded.
# Compare current image with last original image and remove images if they are the same on force.
md5CurrentImage="$(md5sum --binary $data/${resultFilename}_current.$fileExtension | cut -d " " -f1)"
md5LastImage="$(md5sum --binary $data/$resultFile | cut -d " " -f1)"
if [ $md5CurrentImage = $md5LastImage ]; then
  if [ "$1" = "--force" ]; then
    rm -f $data/*_latest_*
    rm -f $data/*_first_*
    latest=$(ls -r $data/${resultFilename}_2* | head -1)
    rm -f $latest
  else
    exit 0
  fi
fi
cp -p $data/${resultFilename}_current.$fileExtension $data/$resultFile

### Process image
resultFileProcessed=${resultFilename}_$dateCurrentHour.$fileExtension
cp -p $data/$resultFile $data/$resultFileProcessed

# Overlay logo and borders
composite -gravity Center $base/overlay_SAT_EUR_Logo_Grenzen.png $data/$resultFileProcessed $data/$resultFileProcessed

# Insert date and time
captionDate="$(date -u +%a' '%d.%m.%Y' '%H':00 UTC')"
convert -background '#00000080' -pointsize 18 -fill white -gravity south -size 520x24 caption:"$captionDate  copyright: EUMETSAT/DWD" $data/$resultFileProcessed +swap -gravity south -composite $data/$resultFileProcessed

# Insert WstHst
composite -gravity northwest -geometry +5+10 $base/stamp.png $data/$resultFileProcessed $data/$resultFileProcessed

# Resize
convert $data/$resultFileProcessed -thumbnail 40% -unsharp 0x.5 $data/${resultFilename}_klein.$fileExtension

### Create video
videoCountImages=10
delayMilliseconds=40
videoFilename=F_GW_SAT_EUR_Satfilm_Europa_Central_RGB

# Keep videoCountImages images only
ls -r $data/${resultFilename}_2* | tail -n +$((videoCountImages+1)) | xargs rm -f

rm -f $data/*_latest_*
rm -f $data/*_first_*

latest=$(ls -r $data/${resultFilename}_2* | head -1)
cp $latest ${latest%.*}_latest_1.$fileExtension
cp $latest ${latest%.*}_latest_2.$fileExtension

first=`ls $data/${resultFilename}_2* | head -1`
cp $first ${first%.*}_first_1.$fileExtension
cp $first ${first%.*}_first_2.$fileExtension

convert -delay $delayMilliseconds -loop 0 $data/${resultFilename}_2* $data/$videoFilename.gif
convert $data/$videoFilename.gif -thumbnail 60% -unsharp 0x.5 $data/${videoFilename}_klein.gif

cp -p $data/$resultFileProcessed $destFolder/$resultFile
cp -p $data/${resultFilename}_klein.$fileExtension $destFolder/
cp -p $data/*.gif $destFolder

