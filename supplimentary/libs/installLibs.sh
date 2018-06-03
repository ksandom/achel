# Useful install libraries.

settingNames="configDir storageDir installType binExec"

function userInstall
{
	# echo "Non root install chosen"
	configDir=~/.$programName
	storageDir=$configDir
	binExec=~/bin
}

function rootInstall
{
	# echo "Root install chosen"
	configDir="/etc/$programName"
	storageDir=$configDir
	binExec=/usr/bin
	installType='cp'
	
	if [ -e /root/.mass ]; then
		echo "Legacy root install exists. This will interfere with new installs."
		mv -v /root/.mass /root/.mass.obsolete
	fi
}

function linkedInstall
{
	# echo "Linked install chosen"
	installTypeComments="This is a linked install so you need to keep the repository that you installed from in place. 
	If you don't want to do this, you may want to consider installing as root, which will make it available to all users."
	
	configDir=~/.$programName
	storageDir=$configDir
	binExec=~/bin
	installType='ln'
	
	if [ "`echo $PATH|grep $binExec`" == '' ]; then
		newBinExec="/usr/local/bin"
		testExec="$newBinExec/canIWriteHere"
		
		if testWriteable "$testExec"; then
			binExec="$newBinExec"
		else
			if getBinCompatibility; then
				echo "We should be working, let's continue!"
				# TODO check binExec. It won't necessarily be what was chosen. Currently assuming it to be ~/bin for a user install.
			else
				echo
				echo "You have chosen a linked user install, but \"$binExec\" is not in \$PATH. And \"$newBinExec\" is not writeable. Installation can not continue."
				
				cat docs/errors/install/notWriteable.md
				exit 1
			fi
		fi
	fi
}

function chooseInstallSettings
{
	# Choose defaults based on whether we are root or not.
	if [ `id -u` -gt 0 ];then
		linkedInstall
	else
		rootInstall
	fi
}





function removeObsoleteStuff
{
	if ! mkdir -p $configDir/obsolete; then
		echo "Mass install: Fatal. Could not create the $configDir/obsolete."
		echo "Check that you can write to $configDir."
		exit 1
	fi
	
	for thing in $things;do
		obsolete $configDir/$thing-enabled
	done
	
	if [ `ls $configDir/obsolete 2> /dev/null | wc -l` -lt 1 ]; then
		rmdir $configDir/obsolete
	else
		echo "removeObsoleteStuff: Obsolete stuff has been put in $configDir/obsolete. It is likely that this directory can simply be deleted. But if you have done any custom work, you will want to check that it isn't here first." | tee $configDir/obsolete/readme.md
	fi
	
	obsolete "$configDir/config/Verbosity.config.json"
}

function uninstallAchelOrMass
{
	programName="$1"
	capitalProgramName="$2"
	fileAction=${3:-removeFilesIfExisting}
	directoryAction=${4:-removeDirectiesIfExisting}
	
	# Remove program executable
	filename="$programName"
	$fileAction ~/bin/$filename /usr/bin/$filename /usr/local/bin/$filename

	# Remove manageProgram executable
	filename="manage$capitalProgramName"
	$fileAction ~/bin/$filename /usr/bin/$filename /usr/local/bin/$filename

	# Remove program home
	$directoryAction ~/.mass /etc/mass

}

function checkPrereqs
{
	for prereq in php;do
		if ! command -v $prereq 1>/dev/null; then
			echo "Could not find $prereq in \$PATH" >&2
			exit 1
		fi
	done
}

function derivePaths
{
	export startDir=`pwd`
	export repoDir="$configDir/repos/$programName"
}

function showConfig
{
	echo "Install config"
	
	for configItem in configDir storageDir installType binExec;do
		oldConfigItem=old$configItem
		if [ "${!oldConfigItem}" == '' ]; then
			echo "	$configItem: 	${!configItem} **NEW**"
		elif [ "${!configItem}" != "${!oldConfigItem}" ]; then
			echo "	$configItem: 	${!configItem} **CHANGED FROM** ${!oldConfigItem}"
		else
			echo "	$configItem: 	${!configItem}"
		fi
	done
	
	echo "	installNotes:"
	echo "$installTypeComments" | cleanWrap 16
}

function copyTemplatedFile
{
	src="$1"
	dst="$2"
	
	# echo "copyTemplatedFile: $src -> $dst in `pwd`"
	rm -f "$dst"
	cat $src | sed '
		s#~%configDir%~#'$configDir'#g;
		s#~%docsDir%~#'$configDir'/docs#g;
		s#~%storageDir%~#'$storageDir'#g;
		s#~%installType%~#'$installType'#g;
		s#~%binExec%~#'$binExec'#g;
		s#~%programName%~#'$programName'#g;
		s#~%languageName%~#achel#g;
		s#~%.*%~##g' > "$dst"
}

function restartInstall
{
	if [ "$restartInstall" != '1' ]; then
		echo "------- Restart of install requested -------"
		export restartInstall=1
		# Make sure we have the information we need to continue installation.
		if [ "$startDir" == '' ]; then
			startDir=`pwd`
		fi
		
		derivePaths
		
		# Test sanity
		if ! [ -e "$startDir"/install.sh ]; then
			echo "Could not find \"$startDir/install.sh\". startDir may not be set correctly." >&2
			exit 1
		fi
		
		# Make absolutely sure we have a functional environment
		[ -e /etc/profile ] && source /etc/profile
		[ -e /etc/zprofile ] && source /etc/zprofile
		
		doInstall
		exit 0
	else
		echo "Restart of install requested, however the install has already been restarted once. It is not safe to continue. Aborting." >&2
		exit 1
	fi
}

function doInstall
{
	echo "start location=`pwd`"
	# Last sanity checks before begining.
	mkdir -p "$storageDir" "$configDir"
	if ! testWriteable "$storageDir" || ! testWriteable "$configDir" ; then
		cat docs/errors/install/notWriteable.md
		exit 1
	fi
	
	
	# Clean up any old structure that must be gone before the new structure can happen
	if [ -h "$configDir"/docs ]; then
		rm "$configDir"/docs
	fi
	rm -f examples
	
	
	# Migrate any old data changing between a unified directory structure to a split structure.
	if [ "$configDir" != "$storageDir" ]; then
		for dirName in "$configDir"{data,config} ~/.mass/{data,config}; do
			if [ -e "$dirName" ]; then
				lastName=`echo $dirName | sed 's#.*/##g'`
				if [ -e "$storageDir/$lastName" ]; then
					echo "$dirName exists, but $storageDir/$lastName also exists, so no migration will be done."
				else
					echo "$dirName exists, migrating to $storageDir/$lastName."
					mv "$dirName" "$storageDir"
				fi
			fi
		done
	fi
	
	
	# Do initial directory structure and test write access
	if mkdir -p "$configDir/"{externalLibraries,repos} "$binExec" "$storageDir/"{data/1LayerHosts,config,credentials}
	then
		echo a> $configDir/canWrite
	elif [ "`cat $configDir/canWrite`" != 'a' ]; then
		echo "Could not write to $configDir."
		exit 1
	else
		echo "Mass install: Fatal. Could not create the crucial directories."
		echo "Check that you can write to $configDir."
		exit 1
	fi
	
	rm $configDir/canWrite
	
	
	# Pre install stuff
	checkPrereqs
	removeObsoleteStuff
	
	showConfig
	
	
	# Put in the main content
	if [ "$installType" == 'cp' ]; then
		# echo -e "Copying available stuff"
		cp -R "$startDir" "$configDir/repos"
	else
		cd "$configDir/repos"
		ln -sf "$startDir" .
	fi
	
	
	# Compiled documentation folder
	mkdir -p "$configDir"/docs
	
	
	
	# Linking like there's no tomorrow.
	cd "$configDir"
	if [ -d interfaces ]; then
		rm -Rf interfaces
	fi
	ln -sf "$repoDir/src/core.php" "$repoDir"/interfaces .
	
	# Remove legacy supplimentary symlink and create a directory instead.
	if [ -h "$configDir"/supplimentary ]; then
		rm "$configDir"/supplimentary; mkdir -p "$configDir"/supplimentary/libs
	fi
	if [ -h "$configDir"/supplimentary/libs ]; then
		rm "$configDir"/supplimentary/libs; mkdir -p "$configDir"/supplimentary/libs
	fi
	
	# This seems a little silly to repeat this a third time, and so probably needs to be re-thought. 
	# The reason for the two previoius ones is so that the directory gets re-created on the same line to prevent the script dying horribly. This appeared to fix the problem at the time, but has not been scientifically tested.
	mkdir -p "$configDir"/supplimentary/libs
	
	supplimentaryInstall achel
	
	
	# Make it executable
	cd "$binExec"
	rm -f "$programName" "manageMass" "achelctl"
	copyTemplatedFile "$startDir/src/exec" "$programName"
	copyTemplatedFile "$startDir/src/manage" achelctl
	chmod 755 "$programName" "achelctl"
	
	# If someone has the old manageAchel command installed, replace it with a symlink to achelctl.
	if [ -f manageAchel ]; then
		ln -sfv achelctl manageAchel
	fi
	
	
	# Set up profiles
	# removeProfile achel
	createProfile achel --noExec
	enableEverythingForProfile achel achel
	cleanProfile achel
	
	# Perform logical setup
	installRepo_setup "achel"
	
	# Install syntax highlighting
	highlightingInstall
	
	# TODO mass: This needs to be migrated to the new repoParms system.
	# createProfile massPrivateWebAPI --noExec
	# enableEverythingForProfile massPrivateWebAPI mass 
	# disableItemInProfile massPrivateWebAPI packages mass-SSH
	# cleanProfile massPrivateWebAPI
	
	# cloneProfile massPrivateWebAPI massPublicWebAPI
	# disableItemInProfile massPublicWebAPI packages mass-AWS
	# cleanProfile massPublicWebAPI
	
	
	# Cleanup
	rm -f "$configDir/macros-enabled/example"*
	rm -f "$configDir/modules-enabled/example"
	rm -f "$configDir/templates-enabled/example"
}

function detectOldSettingsIfWeDontHaveThem
{
	shouldDetect=false
	
	for setting in $settingNames;do
		if [ "${!$setting}" != '' ]; then
			shouldDetect=true
		fi
	done
	
	if [ "$shouldDetect" == 'true' ]; then
		detectOldSettings
	fi
}

function detectOldSettings
{
	if which achel > /dev/null; then
		echo -n "Detecting settings from previous install... "
		
		request=""
		for setting in $settingNames; do
			request="$request~!General,$setting!~	"
		done
		values=`achel --get=Tmp,nonExistent --toString="$request" -s`
		
		if [ $? -gt 0 ]; then
			cat "docs/errors/install/detectSettingsFailed.md"
			waitSeconds 5
		else
			let settingPosition=0
			for setting in $settingNames; do
				let settingPosition=$settingPosition+1
				settingValue=`echo "$values" | cut -d\	  -f $settingPosition`
				
				# Protect against invalid input. Ie if a previou achel install is broken.
				if [ "`echo \"$settingValue\" | wc -l`" -gt 1 ]; then
					settingValue=''
					echo "Warning: Broken install detected. Setting \"$setting\" will be ignored." >&2
				fi
				
				# If we still have a value, make it available for use.
				if [ "$settingValue" != '' ]; then
					export $setting=$settingValue
					export old$setting=$settingValue
				fi
			done
			
			echo "Done."
		fi
	else
		echo "detectOldSettings: No previous install found. Using defaults."
	fi
}

function checkParameters
{
	derivePaths
	
	allowed='^--\(configDir\|storageDir\|binExec\|installType\)'
	for parm in $1;do
		parmAction=`echo $parm | cut -d= -f1`
		parmValue=`echo $parm | cut -d= -f2`
		case $parmAction in
			'--help')
				helpFile="docs/installTimeParameters.md"
				if [ -e $helpFile ]; then
					cat "$helpFile"
				else
					echo "Could not find $helpFile. Currently looking from `pwd`."
				fi
				exit 0
			;;
			'--showConfig')
				showConfig
				exit 0
			;;
			'--dontDetect')
				echo "checkParameters: User requested not to detect previous settings."
			;;
			'--defaults')
				echo "checkParameters: User requested to migrate from the previous settings to the defaults."
			;;
			*)
				if [ "`echo $parmAction| grep "$allowed"`" != "" ]; then
					if [ "$parmValue" != "$parmAction" ]; then
						varName=`echo $parmAction| cut -b 3-`
						echo "Will set $varName to $parmValue."
						export "$varName=$parmValue"
					else
						echo "A value must be specified for $parmAction in the form $parmAction=value."
						exit 0
					fi
				else
					echo "Unknown parameter $parm."
					exit 1
				fi
			;;
		esac
	done
}

function obsolete
{
	for thingPath in "$@";do
		if [ -e "$thingPath" ]; then
			if [ ! -d "$configDir" ]; then
				echo "obsolete: \$configDir (\"$configDir\") does not appear to be set correctly. It is not sane to obsolete \"$thingPath\" without this being correct."
				return 1
			fi
			
			destination="$configDir/obsolete"
			thingName=`echo "$thingPath" | sed 's#.*/##g'`
			fullDestination="$destination/$thingName"
			mkdir -p "$destination"
			if [ -d "$fullDestination" ]; then
				echo "obsolete: \"$fullDestination\" already exists. Removing."
				rm -Rf "$fullDestination"
			fi
			mv "$thingPath" "$destination"
			
			echo "obsolete: \"$thingPath\" has been marked as obsolete and has been moved to \"$destination\"."
		fi
	done
}
