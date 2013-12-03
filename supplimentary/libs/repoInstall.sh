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
	repoAddress="$1"
	overRideRepoName="$2"
	
	checkoutDir="repoInstall-$$"
	addRepo "$repoAddress" "$checkoutDir"

	cd "$configDir/repos/$checkoutDir"

	# get tha name
	if [ "$overRideRepoName" == '' ]; then # detect name
		name=`repoGetParm "$checkoutDir" . name`
	else # override name
		echo "repoInstall: Overrode repoName. This may lead to pain. If you haven't already, read the help for repoInstall." >&2
		name="$overRideRepoName"
	fi

	if [ "$name" == "" ]; then
		# TODO add the option for the user to specify the parameters.
		echo "The repository at \"$repoAddress\" does not appear to have a name set. You'll need to do this installation manually."
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
	
	echo "$name"
}

function installRepo_setup
{
	name="$1"
	if [ ! -e "$configDir/repos/$name" ]; then
		echo "Repo \"$name\" is not currently installed." >&2
		return 1
	fi
	
	while read profileName; do
		if [ "$profileName" != '' ]; then
			# create profile
			createProfile "$profileName"
			
			# enable packages
			if [ "$name" != 'achel' ]; then
				disablePackage "$profileName" ".*" ".*"
			fi
			while read srcRepoName regex; do
				enabledPacakge "$srcRepoName" "$regex" "$profileName"
			done < <(repoGetParmPackages "$name" "$profileName")
			
			# create executable
			execName=`repoGetParm "$name"  "$profileName" execName`
			if [ ! "$execName" == '' ]; then
				createExec "$execName" "$name"
				$execName --verbosity=2 --finalInstallStage
			fi
		fi
	done < <(repoGetProfiles "$name")
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
			
			# TODO the input protection will likely be a curse here, so should be revised.
			removeExec "$execName"
			
			# remove profile
			removeProfile "$profileName"
		fi
	done < <(repoGetProfiles "$name")
	
	# Remove associations with ANY profile
	disablePackage "$repoName" ".*" ".*"
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


