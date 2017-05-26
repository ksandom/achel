# Supplimentary functions for bash tab completion.

function lookupFeaturesForApp
{
    commandName="$1"
    searchString="$2"
    
    echo `$commandName --help=$searchString --toString='~%name%~' -s | sed 's/^/--/g'`
    echo "$commandName --help=$searchString --toString='~%name%~' -s | sed 's/^/--/g'" > /tmp/a
}