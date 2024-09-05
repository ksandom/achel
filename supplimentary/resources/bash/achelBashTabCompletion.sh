# Load the bash tab completion for all Achel apps.

function getDir
{
    uid="$(id -u)"

    if [ "$uid" != '0' ]; then
        echo ~/.achel/bash_completion.d
    else
        echo /etc/achel/bash_completion.d
    fi
}

srcPath="$(getDir)"
while read -r fileName; do
    . "$srcPath/$fileName"
done < <(ls -1 "$srcPath")
