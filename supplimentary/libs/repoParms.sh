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
	
	while ! wizard_createRepo_passTests > /dev/null || [ "$wizard_confirm" != 'yes' ] ; do
		wizard_createRepo_getAnswers
		wizard_createRepo_displayAnswers
		
		export subsequent='true'
		
		if wizard_createRepo_passTests; then
			getAnswer "confirm" "achel/overview/messages/repo/create/repoCreate-confirm.md" "no"
		fi
	done
	
	wizard_createRepo_takeAction
}

function wizard_createRepo_passTests
{
	wizardPrefix='wizard_'
	
	if [ "$subsequent" == '' ]; then
		echo "wizard: First time round. Let's get some answers :)"
		return 1
	fi
	
	result=0
	
	# Some basic input testing
	# I originally played with joining these tests together with && or ||. The problem I struck was that once one of the commands returned false, no others would be executed, which means that the user would not get a complete list of failures.
	if ! inputValidation_groupNotRegex 'name description repoURL' '^$' "The field is required. No input was entered." "$wizardPrefix"; then
		result=1
	fi
	
	if ! inputValidation_groupNotRegex 'name execName repoURL' ' ' "No spaces." "$wizardPrefix" ; then
		result=1
	fi
	
	if ! inputValidation_groupNotRegex 'name execName description' ',' "No commas (\",\")." "$wizardPrefix" ; then
		result=1
	fi
	
	if ! inputValidation_expectLines 'name execName description repoURL' 1 'No new lines. You might have a backslash at the end of the line.' "$wizardPrefix" ; then
		result=1
	fi
	
	
	# devFolder
	if [ ! -d $wizard_devFolder ] && [ ! -h $wizard_devFolder ]; then
		displayMessage "achel/overview/messages/repo/create/repoCreate-devFolderNotFound.md"
		result=1
		export wizard_devFolder=`pwd`
	fi
	
	
	
	if [ "$result" == '1' ] && [ "$subsequent" != '' ]; then
		echo -e "\nOne or more of the tests did not pass. The questions will cycle again with your previous answers as defaults. You can press CTRL+C at any time."
	fi
	
	return $result
}

function wizard_createRepo_displayAnswers
{
	
	echo -e "\n\n\nWizard summary:"
	set | grep '^wizard_' | grep -v '()' | sed 's/^wizard_//g;s/=/ = /'
	echo
}

function wizard_createRepo_getAnswers
{
	getAnswer "name" "achel/overview/messages/repo/create/repoCreate-name.md" ""
	getAnswer "description" "achel/overview/messages/repo/create/repoCreate-description.md" ""
	getAnswer "execName" "achel/overview/messages/repo/create/repoCreate-execName.md" "$wizard_name"
	getAnswer "repoURL" "achel/overview/messages/repo/create/repoCreate-repoURL.md" "git@github.com:`whoami`/$wizard_name.git"
	getAnswer "devFolder" "achel/overview/messages/repo/create/repoCreate-devFolder.md" "`pwd`"
}

function wizard_createRepo_takeAction
{
	echo -e "\n\nAction time!"
	
	# Get into development directory and clone it
	
	# Last minute sanity checks
		# readme
		# repoParms
	
	# Create readme
	# repoParms
	# commit back to the repo
	
	# display information about next steps
		# push the repo back
		# repoParms
		# getting further help
}