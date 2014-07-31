# Handel file replication configuration

function addProvider
{
	local providerName="$1"
	local providerPath="$2"
	
	if [ -e "$providerPath" ]; then
		collectionSetValue "FileReplication" "Providers,$providerName" "$providerPath"
		autoSetDefaultProvider
	else
		echo "addProvider: Could not find path \"$providerPath\"." >&2
	fi
}

function removeProvider
{
	local providerName="$1"
	
	collectionRemoveValue "FileReplication" "Providers,$providerName"
	
	if [ "`getDefaultProvider`" == "$providerName" ]; then
		autoSetDefaultProvider
	fi
}

function autoSetDefaultProvider
{
	if [ "`getDefaultProvider`" == "" ]; then
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
	local providerName="$1"
	collectionGetValue "FileReplication" "defaultProvider"
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
		echo "$folderName/$file    points to     $destination"
	done < <(resolveSymlinks "$configDir/$folderName")
}

function fileRepAddFile
{
	local fileToAdd="$1"
	local provider="$2"
	
	# Use the default provider if we don't have one.
	if [ "$provider" == "" ]; then
		provider=`getDefaultProvider`
	fi
	
	# TODO add check to make sure that the selected provider (via either method), actually exists
	
	local providerPath=`getProviderPath "$provider"`
	# Check that the path of the selected provider exists
	if [ ! -e "$providerPath" ]; then
		# TODO write error
		echo "fileRepAddFile: "
		return 1
	fi
	
	cd "$configDir"
	# TODO write this
	
	cd ~-
}