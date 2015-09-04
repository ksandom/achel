# Manage syntax highlighting definitions

function highlightingInstall
{
	highlightingInstallKate
}


function highlightingInstallKate # Kate (KDE Advanced Text Editor)
{
	# TODO refactor to make more readable
	
	homeDir=~
	kdeConfigDirectories="${homeDir}/.kde ${homeDir}/.kde4 ${homeDir}/.local /usr/share/kde4"
	theRest="/share/apps/katepart /share/katepart5 /share/apps/katepart5 /share/katepart5"
	
	installTemplates "KateSyntaxHighlighting:syntax/achel.xml" "$kdeConfigDirectories" "$theRest"
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
		achel --combineFeatureSets --templateOut="$templateName" > "$templateOut"
		uid=`id -u`
		
		for homeFolder in $primarySearchPath; do
			if [ "${homeFolder:0:5}" == '/home' ] || [ "$uid" == "0" ]; then
				for pathPrefix in $secondarySearchPath; do
					if [ -e "${homeFolder}${pathPrefix}" ]; then
						mkdir -p "${homeFolder}${pathPrefix}/$lastDir"
						cp -v "$templateOut" "${homeFolder}${pathPrefix}/${outputFile}"
					fi
				done
			fi
		done
		
		rm "$templateOut"
	done
}


