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
	
	if [ "`echo $PATH|grep $binExec`" == '' ]; then # A hack for the mac
		newBinExec="/usr/local/bin"
		testExec="$newBinExec/canIWriteHere"
		
		if testWriteable "$testExec"; then
			binExec="$newBinExec"
		else
			echo "You have chosen a linked user install, but \"$binExec\" is not in \$PATH. And \"$newBinExec\" is not writeable. Installation can not continue."
			
			cat docs/errors/install/notWriteable.md
			exit 1
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
		if ! which $prereq 1>/dev/null; then
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
	
	echo "	installNotes: 	$installTypeComments"
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

function doInstall
{
	
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
	if mkdir -p "$configDir/"{externalLibraries,repos} "$binExec" "$storageDir/"{data/hosts,config,credentials}
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
	
	
	# Compile documentation folder
	mkdir -p "$repoDir"/docs
	
	
	
	# Linking like there's no tomorrow.
	cd "$configDir"
	ln -sf "$repoDir/src/core.php" "$repoDir"/interfaces "$repoDir"/supplimentary .
	
	
	# Setting up remaining directory structure
	cd "$storageDir"
	mkdir -p config data/1LayerHosts
	
	
	# Make it executable
	cd "$binExec"
	rm -f "$programName" "manageMass" "manageAchel"
	copyTemplatedFile "$startDir/src/exec" "$programName"
	copyTemplatedFile "$startDir/src/manage" manageAchel
	chmod 755 "$programName" "manageAchel"
	
	
	# Set up profiles
	# removeProfile achel
	createProfile achel --noExec
	enableEverythingForProfile achel achel
	cleanProfile achel
	
	
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
	
	if [ ! -f "$configDir/config/Credentials.config.json" ];then
		echo -e "First time setup"
		achel --set=Credentials,defaultKey,id_rsa --saveStoreToConfig=Credentials
	fi
	
	
	# Run the final stage
	echo -e "Calling the final stage"
	achel --verbosity=2 --finalInstallStage
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