# Handel file replication configuration

defaultProvider=''

function assertFileRepSetup
{
	if [ "`getDefaultProvider`" == '' ]; then
		echo "assertSetup: FileReplication does not appear to be set up. Attempting to configure it automatically."
		autoSetupReplicators
	fi
	
	if [ "`getDefaultProvider`" == '' ]; then
		echo "assertSetup: Bummer. It looks like that didn't work. It's not sane to continue."
		return 1
	else
		return 0
	fi
}

function addProvider
{
	local providerName="$1"
	local providerPath="$2"
	local providerFolder="$3"
	
	if [ "$providerFolder" == '' ]; then
		providerFolder='Achel'
	fi
	
	if [ -e "$providerPath" ]; then
		mkdir -p "$providerPath/$providerFolder"
		collectionSetValue "FileReplication" "Providers,$providerName" "$providerPath/$providerFolder"
		autoSetDefaultProvider
	else
		echo "addProvider: Could not find path \"$providerPath\"." >&2
	fi
}

function removeProvider
{
	local providerName="$1"
	
	# Destroy the cache, so it gets looked up again.
	defaultProvider=''
	
	collectionRemoveValue "FileReplication" "Providers,$providerName"
	
	testedProvider=`getDefaultProvider`
	if [ "$testedProvider" == "$providerName" ]; then
		autoSetDefaultProvider --ignoreCurrent
	fi
}

function autoSetDefaultProvider
{
	if [ "`getDefaultProvider`" == "" ] || [ "$1" == '--ignoreCurrent' ] ; then
		firstProvider=`getProviders | head -n 1`
		if [ "$firstProvider" != "" ]; then
			setDefaultProvider "$firstProvider"
			echo "autoSetDefaultProvider: \"$firstProvider\" has been assigned as the default provider. Use fileRepDefaultProvider to change it."
		else
			echo "autoSetDefaultProvider: No providers found to set." >&2
		fi
	fi
}

function getProviders
{
	achel --collectionLoad=FileReplication --retrieveResults=FileReplication,Providers --getKeys --singleString
}

function setDefaultProvider
{
	local providerName="$1"
	
	if [ "`getProviders | grep \"^$providerName$\"`" != "" ]; then
		collectionSetValue "FileReplication" "defaultProvider" "$providerName"
		defaultProvider="$providerName"
	else
		echo "setDefaultProvider: \"$providerName\" not found in the current providers." >&2
	fi
}

function getProviderPath
{
	local providerName="$1"
	
	collectionGetValue "FileReplication" "Providers,$providerName"
}

function getDefaultProvider
{
	if [ "$defaultProvider" == '' ]; then
		defaultProvider=`collectionGetValue "FileReplication" "defaultProvider"`
	fi
	echo "$defaultProvider"
}


function autoSetupReplicators
{
	addProvider "dropbox" ~/Dropbox 2>/dev/null
	
	# TODO Add more providers
}

function fileRepListFiles
{
	local fileRegex="$1"
	if [ "$fileRegex" != "" ]; then
		fileRepListFilesInAllFolders | grep "$fileRegex"
	else
		fileRepListFilesInAllFolders
	fi
}

function fileRepListFilesInAllFolders
{
	fileRepListFilesIn data
}

function fileRepListFilesIn
{
	local folderName="$1"
	while read file	destination;do
		if [ "$destination" == "" ]; then
			echo "$folderName/$file      **local**"
		else
			echo "$folderName/$file    points to     $destination"
		fi
	done < <(resolveSymlinks "$configDir/$folderName")
}

function fileRepAddFile
{
	local fileToAdd="$1"
	local provider="$2"
	
	if ! assertFileRepSetup; then return 1; fi
	
	# Use the default provider if we don't have one.
	if [ "$provider" == "" ]; then
		provider=`getDefaultProvider`
	fi
	
	local providerPath=`getProviderPath "$provider"`
	
	# TODO I think the problem is somewhere in this test.
	if [ ! -e "$configDir/$fileToAdd" ] && [ ! -e "$providerPath" ]; then
		# Check that our origin file exists.
		echo "fileRepAddFile: Origin \"$configDir/$fileToAdd\" does not exist. And provider path \"$providerPath\" does not exist for provider \"$provider\". Aborting." >&2
		return 1
	fi
	
	if [ ! -e "$configDir/$fileToAdd" ] && [ ! -e "$providerPath/$fileToAdd" ]; then
		# Check that our origin file exists.
		echo "fileRepAddFile: Origin \"$configDir/$fileToAdd\" does not exist. And destination \"$providerPath/$fileToAdd\" does not exist for provider \"$provider\". Aborting." >&2
		return 1
	fi
	
	pathToFile=`dirname "$fileToAdd"`
	# Make sure the desintation folder exists.
	if [ "$pathToFile" != '.' ]; then
		mkdir -p "$providerPath/$pathToFile"
	fi
	
	mkdir -p "$configDir/$pathToFile"

	# Put the file in the right place and symlink it back.
	cd "$configDir"
	if [ ! -e "$providerPath/$fileToAdd" ]; then
		echo "fileRepAddFile: \"$providerPath/$fileToAdd\" Already didn't exist. Copying."
		cp -v "$configDir/$fileToAdd" "$providerPath/$fileToAdd"
	fi
	
	
	rm -Rf "$configDir/$fileToAdd"
	ln -s "$providerPath/$fileToAdd" "$fileToAdd"
	
	cd ~-
}

function fileRepRemoveFile
{
	local fileToRemove="$1"
	if [ "$fileToRemove" != '' ]; then
		if [ -e "$fileToRemove" ]; then
			if ! assertFileRepSetup; then return 1; fi
			# Resolve the symlink
			read fileName	destinationPath < <(resolveSymlinks "$configDir/$fileToRemove")
			# Do the work. NOTE that we are not deleting the destination file. This is because we can not be sure that deleteing it will not incorrectly affect other users. If you know it's not needed any more, it's easy to remove the file yourself.
			rm "$fileName"
			cp "$destinationPath" "$fileName"
		fi
	fi
}