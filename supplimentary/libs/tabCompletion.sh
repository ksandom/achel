# Manage tab completion configuration.

function tabCompletionInstall
{
    local appName="$1"
    uid="$(id -u)"
    home=~
    tcInstallPath="$(getDestinationDir)"

    local installFile="$(setupFoundations  "$appName")"

    echo "Installing bash tab competion to $installFile."
    achel --generateTCConf="$appName" > "$installFile"

    setupBashRC
    setupTCAchelctl
}

function setupFoundations
{
    local appName="$1"

    mkdir -p "$tcInstallPath"

    echo "$tcInstallPath/$appName"
}

function getDestinationDir
{
    if [ "$uid" == 0 ]; then
        #tcInstallPath="/usr/share/bash-completion/completions"
        echo "/etc/bash_completion.d"
    else
        echo "$home/.achel/bash_completion.d"
    fi
}

function setupBashRC
{
    if [ "$uid" == 0 ]; then
        echo "No need to set up achel/bash_completion.d in root."
        return 0
    fi

    destinationFile="$home/.profile"
    line="$home/.achel/supplimentary/resources/bash/achelBashTabCompletion.sh"

    if grep -q "$line" "$destinationFile"; then
        echo "achel/bash_completion.d is already set up for `whoami`."
        return 0
    fi

    echo "Setting up achel/bash_completion.d for `whoami`."
    echo "" >> "$destinationFile"
    echo "# Load bash tab completion for Achel apps." >> "$destinationFile"
    echo ". $line" >> "$destinationFile"
}

function setupTCAchelctl
{
    cp -v ~/.achel/supplimentary/resources/bash/achelctl "$tcInstallPath"
}
