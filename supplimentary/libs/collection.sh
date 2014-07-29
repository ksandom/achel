# Handel collections for storing data in a persistent, safe way.

function collectionSetValue
{
	local collectionName="$1"
	local collectionValueName="$2"
	local collectionValueValue="$3"
	
	if [ "$collectionName" == "" ] || [ "$collectionValueName" == "" ] || [ "$collectionValueValue" == "" ]; then
		echo "collectionSetValue: Insufficient values. collectionName=\"$collectionName\" collectionValueName=\"$collectionValueName\" collectionValueValue=\"$collectionValueValue\""
		return 1
	fi
	
	achel --collectionLoad="$collectionName" --setNested="$collectionName,$collectionValueName,$collectionValueValue"
}

function collectionGetValue
{
	local collectionName="$1"
	local collectionValueName="$2"
	
	if [ "$collectionName" == "" ] || [ "$collectionValueName" == "" ]; then
		echo "collectionSetValue: Insufficient values. collectionName=\"$collectionName\" collectionValueName=\"$collectionValueName\""
		return 1
	fi
	
	achel --collectionLoad="$collectionName" --get="$collectionName,$collectionValueName" --singleString
}

function collectionGetArrayValue
{
	local collectionName="$1"
	local collectionValueName="$2"
	
	if [ "$collectionName" == "" ] || [ "$collectionValueName" == "" ]; then
		echo "collectionSetValue: Insufficient values. collectionName=\"$collectionName\" collectionValueName=\"$collectionValueName\""
		return 1
	fi
	
	achel --collectionLoad="$collectionName" --get="$collectionName,$collectionValueName" --nested
}

function collectionRemoveValue
{
	local collectionName="$1"
	local collectionValueName="$2"
	
	if [ "$collectionName" == "" ] || [ "$collectionValueName" == "" ]; then
		echo "collectionSetValue: Insufficient values. collectionName=\"$collectionName\" collectionValueName=\"$collectionValueName\""
		return 1
	fi
	
	achel --collectionLoad="$collectionName" --unset="$collectionName,$collectionValueName"
}

