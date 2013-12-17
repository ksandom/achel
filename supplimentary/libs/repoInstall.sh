# To use this library, you need to include

# . $libDir/repoInstall.sh
# . $libDir/getRepo.sh
# . $libDir/repoParms.sh
# . $libDir/packages.sh
# . $libDir/installLibs.sh

function installRepo
{
	repoAddress="$1"
	overRideRepoName="$2"
	
	name="`installRepo_get \"$repoAddress\" \"$overRideRepoName\"`"
	installRepo_setup "$name"
}

function installRepo_get
{
	repoAddress="`echo \"$1\" | sed 's/=.*//g'`"
	repoVersion="`echo \"$1\" | sed 's/.*=//g'`"
	overRideRepoName="$2"
	
	
	# Get it
	checkoutDir="repoInstall-$$"
	addRepo "$repoAddress" "$checkoutDir"
	
	cd "$configDir/repos/$checkoutDir"
	
	
	# get tha name
	if [ "$overRideRepoName" == '' ]; then # detect name
		name=`repoGetParm "$checkoutDir" . name`
	else # override name
		echo "installRepo_get: Overrode repoName. This may lead to pain. If you haven't already, read the help for repoInstall." >&2
		name="$overRideRepoName"
	fi

	if [ "$name" == "" ]; then
		# TODO add the option for the user to specify the parameters.
		echo "installRepo_get: The repository at \"$repoAddress\" does not appear to have a name set. You'll need to do this installation manually."
		removeRepo "$checkoutDir"
		exit 1
	fi
	
	
	# detect conflict
	if repoExists "$name"; then # clean up and warn
		echo "$scriptName: A repo of name \"$name\" is already installed. Re-installing." >&2
		removeRepo "$checkoutDir"
	else
		renameRepo "$checkoutDir" "$name"
	fi
	
	
	# Choose a specific version (if requested)
	cd "$configDir/repos/$name"
	if [ "$repoVersion" != '' ]; then
		echo "installRepo_get: Setting $name to $repoVersion"
		git check out "$repoVersion"
	fi
	
	
	echo "$name"
}

function installRepo_clean
{
	irs_repoName="$1"
	if [ ! -e "$configDir/repos/$irs_repoName" ]; then
		echo "Repo \"$irs_repoName\" is not currently installed." >&2
		return 1
	fi
	
	while read profileRefName; do
		if [ "$profileRefName" != '' ]; then
			# Get the logical name of the profile
			profileName=`repoGetParm "$irs_repoName" "$profileRefName" "name"`
			
			# create profile
			createProfile "$profileName"
			
			# disable packages
			if [ "$irs_repoName" != 'achel' ] ; then
				disablePackage "$profileName" ".*" ".*"
			fi
		else
			echo "installRepo_setup: profileRefName=\"$profileRefName\""
		fi
	done < <(repoGetProfiles "$irs_repoName")
}

function installRepo_setup
{
	irs_repoName="$1"
	if [ ! -e "$configDir/repos/$irs_repoName" ]; then
		echo "Repo \"$irs_repoName\" is not currently installed." >&2
		return 1
	fi
	
	while read profileRefName; do
		if [ "$profileRefName" != '' ]; then
			# Get the logical name of the profile
			profileName=`repoGetParm "$irs_repoName" "$profileRefName" "name"`
			execName=`repoGetParm "$irs_repoName"  "$profileName" execName`
			
			# create profile
			createProfile "$profileName"
			
			# enable packages
			while read srcRepoName regex; do
				echo "installRepo_setup($irs_repoName/$profileRefName): Doing enabledPacakge "$srcRepoName" "$regex" "$profileName""
				enabledPacakge "$srcRepoName" "$regex" "$profileName"
			done < <(repoGetParmPackages "$irs_repoName" "$profileRefName")
			
			# create executable
			if [ ! "$execName" == '' ]; then
				createExec "$execName" "$irs_repoName"
				$execName --verbosity=2 --finalInstallStage
			fi
		else
			echo "installRepo_setup: profileRefName=\"$profileRefName\""
		fi
	done < <(repoGetProfiles "$irs_repoName")
}

function userUninstallRepo
{
	repo="$1"
	overRideRepoName="$2"
	
	repoName=`findRepo "$repo"`
	returnValue=$?
	userUninstallRepo_confirm "$repoName" $returnValue "$overRideRepoName"
	
	if [ "$?" -eq 0 ]; then
		uninstallRepo_removeBindings "$repoName"
		removeRepo "$repoName"
	fi
}

function userUninstallRepo_confirm
{
	repoResults="$1"
	repoValue=$2
	overRideRepoName="$3"
	
	
	if [ "$repoResults" == '' ]; then
		echo "No results found for the search \"$repo\". Try repoList to get some clues." >&2
		exit 1
	fi

	echo "$repoResults" | formatRepoResults

	if [ $repoValue -eq 0 ]; then
		repoName=$repoResults
		if [ "$overRideRepoName" == '--force' ]; then
			echo "--force was specified, so will not ask for confirmation." >&2
		else
			if ! confirm "Do you want to uninstall the \"$repoName\" repository?"; then
				echo "User abort." >&2
				exit 1
			fi
		fi
	else
		echo "Too many results. Please refine your search to get one result." >&2
		exit 1
	fi
}

function uninstallRepo_removeBindings
{
	repoName="$1"
	
	while read profileName; do
		if [ "$profileName" != '' ]; then
			# remove executable
			execName=`repoGetParm "$repoName" "$profileName" execName`
			echo "uninstallRepo_removeBindings: Will remove profile '$profileName' for repo '$repoName' with executable '$execName'"
			
			# TODO the input protection will likely be a curse here, so should be revised.
			removeExec "$execName"
			
			# remove profile
			removeProfile "$profileName"
		fi
	done < <(repoGetProfiles "$repoName")
	
	# Remove associations with ANY profile
	disablePackage "$repoName" ".*" ".*"
}

function cleanRepos
{
	while read repo;do
		echo "cleanRepos Doing \"$repo\""
		installRepo_clean "$repo"
	done
}

function reInstallRepos
{
	while read repo;do
		echo "reInstallRepos: Doing \"$repo\""
		installRepo_setup "$repo"
	done
}

function listRepos
{
	refine="$1"
	
	if [ "$refine" == '' ]; then
		ls -1 "$configDir"/repos
	else
		ls -1 "$configDir"/repos | grep "$refine"
	fi
}


