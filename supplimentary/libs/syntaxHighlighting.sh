# Manage syntax highlighting definitions

function highlightingInstall
{
	legacy=0
	quiet=0
	install=1

	if [ "$1" == 'legacy' ]; then
		legacy=1
	fi

	uid="$(id -u)"

	if which kate > /dev/null; then
		highlightingInstallKate
	fi
}

function highlightingUninstall
{
	legacy=0
	quiet=1
	install=0

	uid="$(id -u)"

	if which kate > /dev/null; then
		highlightingInstallKate

		legacy=1
		highlightingInstallKate
	fi
}

function highlightingInstallKate # Kate (KDE Advanced Text Editor)
{
	# TODO refactor to make more readable

	homeDir=~

	if [ "$legacy" == 0 ]; then
		kdeConfigDirectories=''
		if [ "$uid" == '0' ]; then
			kdeConfigDirectories="/usr/share"
		else
			kdeConfigDirectories="${homeDir}/.local/share"
		fi

		theRest="org.kde.syntax-highlighting/syntax"
	else
		kdeConfigDirectories=''
		if [ "$uid" == '0' ]; then
			kdeConfigDirectories="/usr/share/kde4"
		else
			kdeConfigDirectories="${homeDir}/.kde ${homeDir}/.kde4 ${homeDir}/.local"
		fi

		theRest="/share/apps/katepart/syntax /share/katepart5/syntax /share/apps/katepart5/syntax /share/katepart5/syntax"
	fi

	version=`kate --version | cut -d\  -f2`

	case $version in
		"18.04.3")
			echo "Syntax highlighting: Skipping known broken version ($version)."
		;;
		*)
			installTemplates "KateSyntaxHighlighting:achel.xml" "$kdeConfigDirectories" "$theRest"
		;;
	esac
}

function installTemplates
{
	# Each input is a space separated list
	templates="$1" # achelTemplate:outputFile
	# eg KateSyntaxHighlighting:syntax/achel.xml
	# The purpose is to install multiple syntax highlighting definitions per program. Eg one for a macro, another for a template.

	primarySearchPath="$2" # The configuration directory
	# eg /usr/share/kde4, ~/.kde (note that ~ needs to be resolved before getting here.)
	# The purpose of this is to

	secondarySearchPath="$3" # The rest of the path excluding what's in the templates variable.
	# eg /share/apps/katepart, /share/katepart5


	for template in $templates; do
		echo "Syntax template: $template"
		# TODO find a better way of splitting the string in bash. It's not working for me right now
		templateName=`echo "$template" | cut -d: -f 1`
		outputFile=`echo "$template" | cut -d: -f 2`
		lastDir=`dirname "$outputFile"`

		templateOut="/tmp/$$-syntaxHighlighting"
		$binExec/achel --combineFeatureSets --templateOut="$templateName" > "$templateOut"
		uid=`id -u`

		indent=""
		if [ "$quiet" == '0' ]; then
			indent="      "
		fi

		for homeFolder in $primarySearchPath; do
			[ "$quiet" == '0' ] && echo "  primarySearch: $homeFolder"
			for pathPrefix in $secondarySearchPath; do
				[ "$quiet" == '0' ] && echo "    secondarySearch: $pathPrefix"
				[ "$quiet" == '0' ] && echo "      lastDir: $lastDir"
				[ "$quiet" == '0' ] && echo "      file: $outputFile"
				# Some of the directories are not created if they are not being used. Even if they are the right ones to use. Therefore I am currently creating all  possible combinations. This is messey!

				if [ ! -f "$templateOut" ]; then
					[ "$quiet" == '0' ] && echo "      Could not find \""$templateOut"\"." >&2
				fi

				destinationDir="${homeFolder}/${pathPrefix}/$lastDir"
				if [ "$lastDir" == '.' ]; then
					destinationDir="${homeFolder}/${pathPrefix}"
				fi
				mkdir -p "$destinationDir"

				if [ ! -e "$destinationDir" ]; then
					[ "$quiet" == '0' ] && echo "      Could not find \""$destinationDir"\"." >&2
				fi

				fullPath="$destinationDir/${outputFile}"
				[ "$quiet" == '0' ] && echo "      assembled: $fullPath"
				if [ "$install" == '1' ]; then
					cp -v "$templateOut" "$fullPath" | indent "$indent"
				else
					if [ -e "$fullPath" ]; then
						rm -v $fullPath
						echo "Cleanup: $fullPath" | indent "$indent"
						gentlyRemoveParent "$fullPath" | indent "$indent  "
					fi
				fi
			done
		done

		rm "$templateOut"
	done
}

function gentlyRemoveParent
{
	parent="$(dirname "$1")"

	if [ "$parent" != '/' ]; then
		if rmdir -v "$parent" 2>&1; then
			gentlyRemoveParent "$parent"
		fi
	fi
}


