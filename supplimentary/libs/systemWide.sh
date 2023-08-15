# A library for performing system-wide changes.

function makeSystemWide
{
    local src="$1"
    local dstDir="$(dirname "$2")"
    local dstFile="$(basename "$2")"

    cd "$dstDir"
    sudo ln -vs "$src" "$dstFile"
}

function unMakeSystemWide
{
    local dstDir="$(dirname "$1")"
    local dstFile="$(basename "$1")"

    cd "$dstDir"
    sudo rm "$dstFile"
}
