# Manage syntax highlighting definitions

function highlightingInstall
{
	if which kate > /dev/null; then
		highlightingInstallKate
	fi
}


function highlightingInstallKate # Kate (KDE Advanced Text Editor)
{
	# TODO refactor to make more readable
	
	homeDir=~
	kdeConfigDirectories="${homeDir}/.kde ${homeDir}/.kde4 ${homeDir}/.local /usr/share/kde4"
	theRest="/share/apps/katepart /share/katepart5 /share/apps/katepart5 /share/katepart5"
	
	version=`kate --version | cut -d\  -f2`
	
	case $version in
		"18.04.3")
			echo "Syntax highlighting: Skipping known broken version ($version)."
		;;
		*)
			installTemplates "KateSyntaxHighlighting:syntax/achel.xml" "$kdeConfigDirectories" "$theRest"
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
		# TODO find a better way of splitting the string in bash. It's not working for me right now
		templateName=`echo "$template" | cut -d: -f 1`
		outputFile=`echo "$template" | cut -d: -f 2`
		lastDir=`dirname "$outputFile"`
		
		templateOut="/tmp/$$-syntaxHighlighting"
		$binExec/achel --combineFeatureSets --templateOut="$templateName" > "$templateOut"
		uid=`id -u`
		
		for homeFolder in $primarySearchPath; do
			if [ "${homeFolder:0:5}" == '/home' ] || [ "$uid" == "0" ]; then
				for pathPrefix in $secondarySearchPath; do
					# Some of the directories are not created if they are not being used. Even if they are the right ones to use. Therefore I am currently creating all  possible combinations. This is messey!
					# TODO Find a better way to detect what should be set.
					if true; then #[ -e "${homeFolder}${pathPrefix}" ]; then
						mkdir -p "${homeFolder}${pathPrefix}/$lastDir"
						cp -v "$templateOut" "${homeFolder}${pathPrefix}/${outputFile}"
					fi
				done
			fi
		done
		
		rm "$templateOut"
	done
}


