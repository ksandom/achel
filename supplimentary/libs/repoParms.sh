# Manipulate application repository parameters.

function repoSetParm
{
	repoName="$1"
	profileName="$2"
	if [ "$profileName" == '.' ]; then
		parameterName="$3"
	else
		parameterName="$profileName,$3"
	fi
	value="$4"
	
	parmFile="$configDir/repos/$repoName/parameters.json"
	touch "$parmFile"
	
	achel --collectionLoadArbitrary=RepoParms,"$parmFile" --setNested="RepoParms,$parameterName,$value"
}

function repoGetParm
{
	repoName="$1"
	profileName="$2"
	if [ "$profileName" == '.' ]; then
		parameterName="$3"
	else
		parameterName="$profileName,$3"
	fi
	
	parmFile="$configDir/repos/$repoName/parameters.json"
	
	if [ -f "$parmFile" ]; then
		achel --collectionLoadArbitrary=RepoParms,"$parmFile",noSave --getNested="RepoParms,$parameterName" -s
	fi
}

function repoRemoveParm
{
	repoName="$1"
	profileName="$2"
	if [ "$profileName" == '.' ]; then
		parameterName="$3"
	else
		parameterName="$profileName,$3"
	fi
	
	parmFile="$configDir/repos/$repoName/parameters.json"
	
	if [ -f "$parmFile" ]; then
		achel --collectionLoadArbitrary=RepoParms,"$parmFile" --unset="RepoParms,$parameterName"
	fi
}

function repoGetParms
{
	# TODO write a data version of this.... I don't remember what I meant this. If not remembered, remove after 2013-12-28.
	repoName="$1"
	
	parmFile="$configDir/repos/$repoName/parameters.json"
	
	if [ -f "$parmFile" ]; then
		achel --collectionLoadArbitrary=RepoParms,"$parmFile",noSave --getCategory="RepoParms"
	fi
}

function repoGetProfiles
{
	repoName="$1"
	
	parmFile="$configDir/repos/$repoName/parameters.json"
	
	if [ -f "$parmFile" ]; then
		achel --collectionLoadArbitrary=RepoParms,"$parmFile",noSave --getCategory="RepoParms" --getKeys -s --exclude='(name|description)'
	fi
}

function repoGetParmPackages
{
	repoName="$1"
	profileName="$2" # TODO Make sure that all code refering to this function now handle this variable correctly.
	
	parmFile="$configDir/repos/$repoName/parameters.json"
	
	if [ -f "$parmFile" ]; then
		achel --collectionLoadArbitrary=RepoParms,"$parmFile",noSave --retrieveResults="RepoParms,$profileName" --takeSubResult=packages --flatten --toString="~%sourceRepo%~ ~%packageRegex%~" -s
	fi
}

function showReposWithParms
{
	ls -1 "$configDir"/repos | while read repoName;do
		if [ -f ""$configDir"/repos/$repoName/parameters.json" ]; then
			echo "$repoName"
		fi
	done
}




function wizard_createRepo
{
	displayMessage "achel/overview/messages/repo/create/repoCreate-welcome.md"
	
	while ! wizard_createRepo_passTests; do
		wizard_createRepo_getAnswers
		wizard_createRepo_displayAnswers
		
		getAnswer "confirm" "achel/overview/messages/repo/create/repoCreate-confirm.md" "no"
		export subsequent=true
	done
	
	wizard_createRepo_takeAction
}

function wizard_createRepo_passTests
{
	result=0
	
	# TODO add tests
	
	if [ "$wizard_confirm" != 'yes' ]; then
		result=1
		message="User did not approve the answers."
	fi
	
	if [ "$result" == '1' ] && [ "$subsequent" != '' ]; then
		echo "One or more of the tests did not pass. The questions will cycle again with your previous answers as defaults. You can press CTRL+C at any time."
	fi
	
	return $result
}

function wizard_createRepo_displayAnswers
{
	true
}

function wizard_createRepo_getAnswers
{
	true
}

function wizard_createRepo_takeAction
{
	echo "Got to action."
	true
}