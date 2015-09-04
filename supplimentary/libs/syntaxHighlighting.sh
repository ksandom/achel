# Manage syntax highlighting definitions

function highlightingInstall
{
	highlightingInstallKate
}


function highlightingInstallKate # Kate (KDE Advanced Text Editor)
{
	# TODO refactor to take all input in single parameters so multiple templates can all be supplied.
	
	templateOut="/tmp/$$-syntaxHighlighting"
	achel --combineFeatureSets --templateOut=KateSyntaxHighlighting > "$templateOut"
	uid=`id -u`
	
	for homeFolder in ~/.kde ~/.kde4 ~/.kde3 ~/.local /usr/share/kde4; do
		if [ "${homeFolder:0:5}" == '/home' ] || [ "$uid" == "0" ]; then
			for pathPrefix in /share/apps/katepart /share/katepart5 /share/apps/katepart5 /share/katepart5; do
				if [ -e "${homeFolder}${pathPrefix}" ]; then
					mkdir -p "${homeFolder}${pathPrefix}/syntax"
					cp -v "$templateOut" "${homeFolder}${pathPrefix}/syntax/achel.xml"
				fi
			done
		fi
	done
	
	rm "$templateOut"
}


