# Handel file replication configuration

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
	
	# Use the default provider if we don't have one.
	if [ "$provider" == "" ]; then
		provider=`getDefaultProvider`
	fi
	
	# Check that our origin file exists.
	if [ ! -e "$configDir/$fileToAdd" ]; then
		echo "fileRepAddFile: Origin \"$configDir/$fileToAdd\" does not exist. Aborting." >&2
		return 1
	fi
	
	# Check that the path of the selected provider exists
	local providerPath=`getProviderPath "$provider"`
	if [ ! -e "$providerPath" ]; then
		echo "fileRepAddFile: Provider path \"$providerPath\" does not exist for provider \"$provider\". Aborting." >&2
		return 1
	fi
	
	
	pathToFile=`dirname "$fileToAdd"`
	# Make sure the desintation folder exists.
	if [ "$pathToFile" != '.' ]; then
		mkdir -p "$providerPath/$pathToFile"
	fi

	# Put the file in the right place and symlink it back.
	cd "$configDir"
	if [ ! -e "$providerPath/$fileToAdd" ]; then
		echo "fileRepAddFile: \"$providerPath/$fileToAdd\" Already didn't exist. Copying."
		cp "$configDir/$fileToAdd" "$providerPath/$fileToAdd"
	fi
	
	
	rm -Rf "$configDir/$fileToAdd"
	ln -s "$providerPath/$fileToAdd" "$fileToAdd"
	
	cd ~-
}

function fileRepRemoveFile
{
	local fileToRemove="$1"
	
	# Resolve the symlink
	read fileName	destinationPath < <(resolveSymlinks "$configDir/$fileToRemove")
	
	# Do the work. NOTE that we are not deleting the destination file. This is because we can not be sure that deleteing it will not incorrectly affect other users. If you know it's not needed any more, it's easy to remove the file yourself.
	rm "$fileName"
	cp "$destinationPath" "$fileName"
	
}