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
	
	achel -q --collectionLoad="$collectionName" --setNested="$collectionName,$collectionValueName,$collectionValueValue"
}

function collectionGetValue
{
	local collectionName="$1"
	local collectionValueName="$2"
	
	if [ "$collectionName" == "" ] || [ "$collectionValueName" == "" ]; then
		echo "collectionSetValue: Insufficient values. collectionName=\"$collectionName\" collectionValueName=\"$collectionValueName\""
		return 1
	fi
	
	achel -q --collectionLoad="$collectionName" --getNested="$collectionName,$collectionValueName" --flatten --singleString
}

function collectionGetArrayValue
{
	local collectionName="$1"
	local collectionValueName="$2"
	
	if [ "$collectionName" == "" ] || [ "$collectionValueName" == "" ]; then
		echo "collectionSetValue: Insufficient values. collectionName=\"$collectionName\" collectionValueName=\"$collectionValueName\""
		return 1
	fi
	
	achel -q --collectionLoad="$collectionName" --getNested="$collectionName,$collectionValueName" --nested
}

function collectionGet
{
	local collectionName="$1"
	
	if [ "$collectionName" == "" ]; then
		echo "collectionSetValue: Insufficient values. collectionName=\"$collectionName\""
		return 1
	fi
	
	achel -q --collectionLoad="$collectionName" --getCategory="$collectionName" --nested
}

function collectionRemoveValue
{
	local collectionName="$1"
	local collectionValueName="$2"
	
	if [ "$collectionName" == "" ] || [ "$collectionValueName" == "" ]; then
		echo "collectionSetValue: Insufficient values. collectionName=\"$collectionName\" collectionValueName=\"$collectionValueName\""
		return 1
	fi
	
	achel -q --collectionLoad="$collectionName" --unset="$collectionName,$collectionValueName"
}

