# When doing a user install, the path isn't always in an ideal state.
# This functionality is about getting it in a usable state and testing that is the case.

function getBinCompatibility
{
	echo "Requested configuration will not work as it is. Going to try getting ~/bin to work."
	setupBinCompatibilityTest
	
	if testBinBashProfiled; then
		destroyBinCompatibilityTest
		return 0
	elif testBinBashProfile; then
		destroyBinCompatibilityTest
		return 0
	elif testBinBashRC; then
		destroyBinCompatibilityTest
		return 0
	fi
	
	echo "FAILURE: Hmmm, we don't yet have a solution for your problem. Please create an issue on http://github.com/ksandom/achel and be ready to answer questions about your setup."
	destroyBinCompatibilityTest
	return 1
}


function setupBinCompatibilityTest
{
	mkdir -p ~/bin
	echo "#!/bin/bash
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
	if which binCompatibilityTest; then
		binCompatibilityTest
		return $?
	else
		return 1
	fi
}







function testBinBashProfiled
{
	echo -n "Attempting profiled ..."
	destinationFile="userBin"
	if cp "supplimentary/resources/bash/profileD-path.sh" "/etc/profile.d/$destinationFile" 2>/dev/null; then
		source /etc/profile
		if runBinCompatibilityTest; then
			echo "Success!"
			restartInstall
			exit 0
		else
			echo "Failure"
			rm -f "/etc/profile.d/$destinationFile"
			return 1
		fi
	else
		echo "NA"
		return 1
	fi
}


function testBinBashProfile
{
	echo -n "Attempting profile ..."
	tmpFile="/tmp/$$-profile"
	cp /etc/profile "$tmpFile"
	if "$tmpFile" /etc/profile 2>/dev/null; then
		cat "supplimentary/resources/bash/profileD-path.sh" >> /etc/profile
		source /etc/profile
		if runBinCompatibilityTest; then
			echo "Success!"
			restartInstall
			exit 0
		else
			echo "Failure"
			cp "$tmpFile" /etc/profile
			rm -f "$tmpFile"
			return 1
		fi
	else
		echo "NA"
		return 1
	fi
}

function testBinBashRC
{
	echo -n "Attempting bashrc ..."
	configFile=~/.bashrc
	if [ -e "$configFile" ]; then
		tmpFile="/tmp/$$-bashrc"
		cp "$configFile" "$tmpFile"
	else
		echo "No existing "$configFile", so not backing it up."
		unset tmpFile
	fi
	
	cat "supplimentary/resources/bash/profileD-path.sh" >> "$configFile"
	source "$configFile"
	if runBinCompatibilityTest; then
		echo "Success!"
		restartInstall
		exit 0
	else
		echo "Failure"
		if [ "$tmpFile" != '' ]; then
			cp "$tmpFile" "$configFile"
			rm -f "$tmpFile"
		else
			rm "$configFile"
		fi
		return 1
	fi
}

function testZProfile
{
	# TODO write this
	source /etc/zprofile
}
