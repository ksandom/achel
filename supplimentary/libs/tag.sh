# Management of version tags

tagFile=.tag

function getNow
{
  date +%Y-%m-%d
}

function getHash
{
  git rev-parse HEAD
}

function setState
{
  echo "# Tag settings
  lastWhen='$1'
  lastHash='$2'
  point=$3" > $tagFile
}

function generateTag
{
  # If we don't have a saved state, let's fix that.
  if [ ! -e $tagFile ]; then
    setState `getNow` `getHash` 0
  fi

  # Get current and previous state.
  currentHash=`getHash`
  currentWhen=`getNow`
  . $tagFile

  # Figure out if we need to change anything.
  if [ "$currentHash" != "$lastHash" ]; then
    # Stuff has changed, we need a new tag.
    if [ "$currentWhen" != "$lastWhen" ]; then
      # It's a new day. Reset the point.
      point=0
    else
      # It's the same day. Increment the point.
      let point=$point+1
    fi
    
    setState "$currentWhen" "$currentHash" $point
    # If omit the point if .0.
    if [ "$point" == "0" ]; then
      echo "$currentWhen"
    else
      echo "$currentWhen"."$point"
    fi
  else
    # No change. Return the current tag.
    # If omit the point if .0.
    if [ "$point" == "0" ]; then
      echo "$lastWhen"
    else
      echo "$lastWhen"."$point"
    fi
  fi
}
