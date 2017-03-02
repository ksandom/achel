# For managing the various achel caches for any given Achel application.

function clearCache
{
	profileName="$1"
	
	if isValidProfile "$profileName"; then
		cd "`getProfilePath \"$profileName\"`/cache" && rm *
	else
		echo "Could not find profile \"$profileName\"" >&2
		exit 1
	fi
}
