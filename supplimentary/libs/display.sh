# Display stuff nicely

function tabsToSpacedDashes
{
	if [ "$noFormat" == '' ]; then
		sed 's/	/ - /g'
	else
		cat -
	fi
}

function testInput
{
	# TODO This function can be dramatically improved by using bash's builtin functionality.

	userInput="$1"
	possibleOptions="$2"

	if [ "$userInput" == '' ]; then
		return 1
	else
		if [ "`echo \"$possibleOptions\" | grep $userInput`" ]; then
			return 0
		else
		return 1
		fi
	fi
}

function applyDefault
{
	# TODO This function can be dramatically improved by using bash's builtin functionality.
	inputToTest="$1"
	defaultValue="$2"
	if [ "$inputToTest" == '' ]; then
		echo "$defaultValue"
	else
		echo "$inputToTest"
	fi
}

function confirm
{
	# TODO This function can be dramatically improved by using bash's builtin functionality.

	message="$1"
	default=${2:-'n'}
	match=${3:-'y'}
	input="$4"
	options='y n'

	while ! testInput "$input" "$options"; do
		echo -n "$message ($options)[$default]: "
		read input
		input=`applyDefault "$input" "$default"`
	done

	if [ "$input" == "$match" ]; then
		return 0
	else
		return 1
	fi
}

function waitSeconds
{
	seconds="$1"
	message=${2:-'Will continue in %s seconds...'}

	# Sanity check
	if ! [ $seconds -gt 0 ]; then
		echo "waitSeconds: Invalid number of seconds." &2>/dev/null
		return 1
	fi

	# Count down
	let secondsPosition=$seconds
	while [ $secondsPosition -gt 0 ];do
		echo -ne "\r$message " | sed "s/%s/$secondsPosition/g"
		let secondsPosition=$secondsPosition-1
		sleep 1
	done

	# Clean up
	finalMessage=`echo "$message" | sed "s/%s/0/g;s/./ /g"`
	echo -e "\r$finalMessage\rWaited $seconds seconds."
}

function displayMessage
{
	messageFile="$1"

	fullFilePath="$configDir/docs/repos/$messageFile"

	# Default to a slightly helpful error message if the description file can't be found.
	if [ -e "$fullFilePath" ]; then
		echo -e "\n"
		cat "$fullFilePath" | fold -s
	else
		echo "getAnswer: Could not find \"$fullFilePath\" so I can't give you a description. The name of the value being set is \"$name\". Good luck."
	fi
}

function getAnswer
{
	displayName="$1"
	name="wizard_$displayName"
	descriptionFile="$2"
	default="$3"

	displayMessage "$descriptionFile"

	# Choose the default value
	if [ "${!name}" == '' ]; then
		defaultToUse="$default"
	else
		defaultToUse="${!name}"
	fi

	# Ask for input
	if [ "$defaultToUse" == '' ]; then
		promptText="$displayName: "
	else
		promptText="$displayName [$defaultToUse]: "
	fi
	tmpName="tmp_$name"
	read -p "$promptText" "$tmpName"

	# Export
	if [ "${!tmpName}" != '' ]; then
		export $name="${!tmpName}"
		# echo "Set \"$name\" to ${!name}"
	else
		export $name="$defaultToUse"
	fi
}

function cleanWrap
{
	# Takes an integer specifying how many characters to indent by
	# and the cleanly wraps the content piped to it while indenting
	# by the requested amount.

	indent="$1"
	let derivedWidth=$COLUMNS-$indent

	# TODO This is a hack, there must be a better way.
	lotsOfPadding='                                          '
	lotsOfPadding="$lotsOfPadding$lotsOfPadding$lotsOfPadding"

	padding=${lotsOfPadding::indent}

	if [ "$derivedWidth" -gt 0 ]; then
		while read line;do
			echo "$padding$line"
		done < <(fold -s --width=$derivedWidth)
	else
		while read line;do
			echo "$padding$line"
		done < <(fold -s)
	fi
}

function getDimensions
{
	# TODO There has got to be a better way than this.

	if which resize > /dev/null; then
		resize 2>/dev/null | grep COL | grep -v export | sed 's/.*=//g;s/;//g'
	else
		echo 80
	fi
}

export COLUMNS=`getDimensions`

function indent
{
	local prefix="$1"

	while read -r line; do
		echo "$prefix$line"
	done
}
