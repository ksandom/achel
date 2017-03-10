# For managing the various achel caches for any given Achel application.

function clearCache
{
	profileName="$1"
	
	if isValidProfile "$profileName"; then
		cd "`getProfilePath \"$profileName\"`/cache" && rm -f *
	else
		echo "Could not find profile \"$profileName\"" >&2
		exit 1
	fi
}

function clearAllCache
{
	rm `getProfileHomePath`/*/cache/*
}

function turnCacheOn
{
	achel --unset=Settings,disableCache --saveStoreToConfig=Settings
}

function turnCacheOff
{
	clearAllCache
	achel --set=Settings,disableCache,true --saveStoreToConfig=Settings
}
