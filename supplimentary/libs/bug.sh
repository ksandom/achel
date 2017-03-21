# For building bug reports.

function creatBugReport
{
	commandName="$1"
	
	echo;echo "Cache stats"
	$commandName --cacheStats
	
	echo;echo "Settings"
	$commandName --getCategory=Settings
	
	echo;echo "General"
	$commandName --getCategory=General
	
	possibleRepoPath=`$commandName --set='Local,possibleRepoPath,~!General,configDir!~/repos/~!General,programName!~' --get=Local,possibleRepoPath -s`
	
	echo;echo "Recent git items"; echo
	if [ -e "$possibleRepoPath" ]; then
		cd "$possibleRepoPath"
		git log -n 5 --pretty=oneline
	else
		echo "Was not able to reliably determine the repo path."
	fi
}
