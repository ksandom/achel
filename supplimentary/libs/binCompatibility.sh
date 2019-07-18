# When doing a user install, the path isn't always in an ideal state.
# This functionality is about getting it in a usable state and testing that is the case.

function getBinCompatibility
{
	uid=`id -u`
	setupBinCompatibilityTest
	retval1=0
	retval2=0
	
	# Bash
	if which bash >/dev/null; then
		if [ "$uid" == '0' ]; then # root
			if ! addBinBashProfiled; then
				# Fall back to the old fashioned /etc/profile
				addBinBashProfile
			fi
		else # non-root
			addBinBashRC
			# TODO .profile as well?
		fi
		
		runBinCompatibilityTest `which bash`
		retval1=$?
	fi
	
	# zsh
	if which zsh >/dev/null; then
		if [ "$uid" == '0' ]; then # root
			if ! false; then
				# Fall back to the old fashioned /etc/zprofile
				addZProfile
			fi
		else # non-root
			addUserZprofile
		fi
		runBinCompatibilityTest `which zsh`
		retval2=$?
	fi
	
	
	destroyBinCompatibilityTest
	let retval=$retval1+$retval2
	return $retval
}


function setupBinCompatibilityTest
{
	mkdir -p ~/bin
	echo "#!`which bash`
echo \"Successfully executed $0\"
exit 3"> ~/bin/binCompatibilityTest
	chmod 755 ~/bin/binCompatibilityTest
}

function destroyBinCompatibilityTest
{
	rm ~/bin/binCompatibilityTest
}

function runBinCompatibilityTest
{
	shell="$1"
	
	$shell -l -c binCompatibilityTest
	retval=$?
	echo "Compatibility test return code = $retval"
	return $?
}







function addBinBashProfiled
{
	echo -n "Attempting profiled ..."
	destinationFile="userBin"
	if cp "supplimentary/resources/bash/profileD-path.sh" "/etc/profile.d/$destinationFile" 2>/dev/null; then
		echo "Added"
		return exit 0
	else
		echo "NA"
		return 1
	fi
}


function addBinBashProfile
{
	echo -n "Attempting profile ..."
	tmpFile="/etc/profile.backupBeforeBin"
	cp /etc/profile "$tmpFile"
	if cp "$tmpFile" /etc/profile 2>/dev/null; then
		cat "supplimentary/resources/bash/profileD-path.sh" >> /etc/profile
			echo "Success!"
			return 0
	else
		echo "NA"
		return 1
	fi
}

function addBinBashRC
{
	echo -n "Adding user bashrc ..."
	configFile=~/.bashrc
	if [ -e "$configFile" ]; then
		tmpFile=~/".bashrc.backupBeforeBin"
		cp "$configFile" "$tmpFile"
	else
		echo "No existing "$configFile", so not backing it up."
		unset tmpFile
	fi
	
	cat "supplimentary/resources/bash/profileD-path.sh" >> "$configFile"
	echo "done."
	return 0
}

function addZProfile
{
	echo -n "Attempting zprofile ..."
	tmpFile="/etc/zprofile.backupBeforeBin"
	cp /etc/zprofile "$tmpFile"
	if cp "$tmpFile" /etc/zprofile 2>/dev/null; then
		cat "supplimentary/resources/bash/zprofileD-path.sh" >> /etc/zprofile
			echo "Success!"
			return 0
	else
		echo "NA"
		return 1
	fi
}

function addUserZprofile
{
	echo -n "Adding user zprofile ..."
	configFile=~/.zprofile
	if [ -e "$configFile" ]; then
		tmpFile=~/".zprofile.backupBeforeBin"
		cp "$configFile" "$tmpFile"
	else
		echo "No existing "$configFile", so not backing it up."
		unset tmpFile
	fi
	
	cat "supplimentary/resources/bash/profileD-path.sh" >> "$configFile"
	echo "done."
	return 0
}
