# Scripts for doing stuff with the filesystem
# This library is automatically included for you if you include includeLibs.sh

function resolveSymlinks
{
	# TODO Check this works as expected on other systems. It should produce output like this:
	#  aws-sdk-for-php	/home/ksandom/files/develop/externalRepos/aws-sdk-for-php/
	
	dirToScan="$1"
	ls -l --time-style=+NODATE "$dirToScan" | tail -n +2 | sed 's/^.*NODATE.//g;s/ -> /	/g' 
}

function getFile
{
	fullPath="$1"
	echo "$fullPath" | sed 's#.*/##g'
}

function testWriteable
{
	path="$1/canIWriteHere"
	if touch "$path"; then
		rm "$path"
		return 0
	else
		return 1
	fi
}

function removeFilesIfExisting
{
	for filename in "$@";do
		if [ -e "$filename" ]; then
			rm -fv "$filename"
		fi
	done
}

function removeDirectiesIfExisting
{
	for filename in "$@";do
		if [ -e "$filename" ]; then
			rm -Rfv "$filename"
		fi
	done
}

function linkSrc
{
	# Link once to a file.
	fileName="$1"
	asName="${2:-.}"
	
	# If asName is "." or "" the destination name is left to ln to figure out and it will therefore do it's best effort to replace the file whether it exists or not. This will lead to funny behavior if a directory already exists with that name. To prevent this, always specify the asName.
	
	
	if [ -e "$fileName" ]; then
		if [ "$asName" == '.' ]; then
			echo "linkSrc \"$fileName\" \"$asName\": asName is \".\". Therefore ln is being left to best effort placement. Strange things could happen." >&2
			ln -sf "$fileName" .
		else
			if [ -e "$asName" ]; then
				rm -Rf "$asName"
			fi
			
			ln -sf "$fileName" "$asName"
		fi
	else
		echo "linkSrc: \"$fileName\" not found. Please bug the Author to fix this." >&2
	fi
}