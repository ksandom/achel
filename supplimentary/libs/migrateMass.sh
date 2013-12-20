# Migrate a legacy mass installation to achel

migrateMassPrefix='migrateMass'
migrateThings='config data credentials externalLibraries repos'

function showMigratePossibilities
{
	from="$1"
	if [ ! -d "$from" ]; then
		echo "$migrateMassPrefix: No migration necessary."
		return 0
	fi
	
	actionToUse="recommended"
	recommendedActions=`callStuff "$migrateMassPrefix" 'getRecommended' "$migrateThings" "$from"`
	echo "$recommendedActions" | chooseAction "$actionToUse" | migrateMass_takeAction "$from"
}

function chooseAction
{
	actionToUse="$1"
	
	case "$actionToUse" in
		'min')
			actionColumn=2
		;;
		'recommended')
			actionColumn=3
		;;
		'max')
			actionColumn=4
		;;
	esac
	
	while read in;do
		echo "$in" | cut -d\  -f 1,$actionColumn
	done
}

function migrateMass_takeAction
{
	from="$1"
	while read thing action;do
		${migrateMassPrefix}_${thing}_takeAction "$from" "$action"
	done
}




function callStuff
{
	prefix="$1"
	action="$2"
	listOfThings="$3"
	
	passParm1="$4"
	passParm2="$5"
	passParm3="$6"
	passParm4="$7"
	passParm5="$8"
	passParm6="$9"
	
	for thing in $listOfThings;do
		cmd="${prefix}_${thing}_${action}"
		value=`$cmd "$passParm1" "$passParm2" "$passParm3" "$passParm4" "$passParm5" "$passParm6"`
		echo "$thing $value"
	done
}

function migrateMass_config_getRecommended
{
	from="$1"
	testDefaultFileTransfer "$from" "$configDir" 'config'
}

function migrateMass_config_takeAction
{
	from="$1"
	thing='config'
	action="$2"
	takeActionDefault "$from" "$configDir" "$thing" "$action"
}

function migrateMass_data_getRecommended
{
	from="$1"
	testDefaultFileTransfer "$from" "$configDir" 'data'
}

function migrateMass_data_takeAction
{
	from="$1"
	thing='data'
	action="$2"
	takeActionDefault "$from" "$configDir" "$thing" "$action"
}

function migrateMass_credentials_getRecommended
{
	from="$1"
	testDefaultFileTransfer "$from" "$configDir" 'credentials'
}

function migrateMass_credentials_takeAction
{
	from="$1"
	thing='credentials'
	action="$2"
	takeActionDefault "$from" "$configDir" "$thing" "$action"
}

function migrateMass_externalLibraries_getRecommended
{
	from="$1"
	testDefaultFileTransfer "$from" "$configDir" 'externalLibraries'
}

function migrateMass_externalLibraries_takeAction
{
	from="$1"
	thing='externalLibraries'
	action="$2"
	takeActionDefault "$from" "$configDir" "$thing" "$action"
}

function migrateMass_repos_getRecommended
{
	from="$1"
	testDefaultFolderTransfer "$from" "$configDir" 'repos'
}

function migrateMass_repos_takeAction
{
	from="$1"
	thing='repos'
	action="$2"
	takeActionDefault "$from" "$configDir" "$thing" "$action"
}





function testDefaultFileTransfer
{
	from="$1"
	to="$2"
	folderToTest="$3"
	
	# test src folder
	if [ ! -d "$from/$folderToTest" ]; then
		# If the folder doesn't exist, we have nothing to import
		mrmResult 'none'
		return 0
	fi
	
	# test src files
	if [ "`ls \"$from/$folderToTest\" | wc -l`" -eq 0 ]; then
		# The folder exists, but there isn't anything inside it. We have nothing to import.
		mrmResult 'none'
		return 0
	fi
	
	# test dst files
	mkdir -p "$to/$folderToTest"
	if [ `ls "$to/$folderToTest" | wc -l` -eq 0 ]; then
		mrmResult 'create'
		return 0
	else
		mrmResult "none" "noClobber" "replace"
		return 0
	fi
}

function testDefaultFolderTransfer
{
	from="$1"
	to="$2"
	folderToTest="$3"
	
	# test src folder
	if [ ! -d "$from/$folderToTest" ]; then
		# If the folder doesn't exist, we have nothing to import
		mrmResult 'none'
		return 0
	fi
	
	# test src files
	if [ "`ls \"$from/$folderToTest\" | wc -l`" -eq 0 ]; then
		# The folder exists, but there isn't anything inside it. We have nothing to import.
		mrmResult 'none'
		return 0
	fi
	
	# test dst files
	mkdir -p "$to/$folderToTest"
	if [ `ls "$to/$folderToTest" | wc -l` -eq 0 ]; then
		mrmResult 'create'
		return 0
	else
		mrmResult "none" "noClobber" "replace"
		return 0
	fi
}

function mrmResult
{
	# Syntax:
	#   mrmResult [min[ recommended[ max]]]
	#     min 		defaults to "none".
	#     recommended	defaults to min.
	#     max		defaults to recommended.
	
	min="${1:-none}"
	recommended="${2:-$min}"
	max="${3:-$recommended}"
	echo "$min $recommended $max"
}

function takeActionDefault
{
	from="$1"
	to="$2"
	folder="$3"
	action="$4"
	
	case "$action" in
		'none')
			echo "$migrateMassPrefix: No action taken for \"$folder\"."
		;;
		'create'|'replace')
			echo "$migrateMassPrefix: Will $action the contents of \"$folder\"."
			cp -Rfv "$from/$folder" "$to/"
		;;
		'noClobber')
			echo "$migrateMassPrefix: Will copy the contents of \"$folder\" without disturbing existing files."
			cp -Rv --no-clobber "$from/$folder" "$to/"
		;;
		*)
			echo "$migrateMassPrefix: Unknown action \"$action\" for \"$folder\"."
		;;
	esac
}

