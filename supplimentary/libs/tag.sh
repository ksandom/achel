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

function getTag
{
  . $tagFile
  if [ "$point" == "0" ]; then
    echo "$lastWhen"
  else
    echo "$lastWhen"."$point"
  fi
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
  if [ "$currentHash" != "$lastHash" ] || [ "$requestNew" == 'true' ] ; then
    # Stuff has changed, we need a new tag.
    if [ "$currentWhen" != "$lastWhen" ]; then
      # It's a new day. Reset the point.
      point=0
    elif [ "$requestNew" == 'true' ]; then
      # We've requested a new tag.
      let point=$point+1
    else
      # It's the same day. Increment the point.
      let point=$point+1
    fi
    
    setState "$currentWhen" "$currentHash" $point
    # If omit the point if .0.
    echo "$currentWhen"."$point"
  else
    # No change. Return the current tag.
    echo "$lastWhen"."$point"
  fi
}
