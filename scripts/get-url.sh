
# 1: URL
# 2: Complete path to local file
# Return code 0 = downloaded
#             1 = not modified
# Usage: if get-url $url $file; then echo "downloaded"; else echo "not modified"; fi
#        get-url $url $file || exit 0
function get-url() {
  remoteFileUrl=$1 # complete file path
  localFile=$2 # complete path
  tmpFile=$localFile.tmp
  
  [ $# -eq 2 ] || exit 2
  
  curlOptions="--insecure --silent --show-error --location --max-time 300 --connect-timeout 60 --remove-on-error --remote-time"
  [ -f $localFile ] && curlOptions="$curlOptions --time-cond $localFile" # Send If-Modified-Since
  touch $tmpFile # prevent warnings
  curl $curlOptions --output $tmpFile $remoteFileUrl
  if [ $? -gt 0 ]; then
    rm -f $tmpFile
    return $?
  fi
  [ -s $tmpFile ] || rm -f $tmpFile # delete if zero filesize

  if [ -f $tmpFile ]; then
    mv $tmpFile $localFile
    return 0
  else
    return 1
  fi
}
