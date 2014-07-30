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

function getDefaultProvider
{
	local providerName="$1"
	collectionGetValue "FileReplication" "defaultProvider"
}


function autoSetupReplicators
{
	addProvider "dropbox" ~/Dropbox 2>/dev/null
	addProvider "googleDrive" ~/gDrive 2>/dev/null
	addProvider "dropbox-test" ~/Dropbox 2>/dev/null
}
