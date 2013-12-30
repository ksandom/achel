#!/bin/bash
# Install the program
# Copyright (c) 2012, Kevin Sandom under the BSD License. See LICENSE for full details.

# If installed using the root user, the program will be available to all users. Otherwise it will be installed locally to the current user.
# Alternatively, if you install it as a non-root user, you can do a linked install like this:
# ./install.sh linked
#
# This is useful for development where you want to test changes without reinstalling.

# install.sh will get replaced eventually. For now it does what I need.

programName='achel'
fileThings='macros modules templates'
directoryThings='packages'
things="$fileThings $directoryThings"
installTypeComments=''

cd `dirname $0`
. supplimentary/libs/installLibs.sh
. supplimentary/libs/documentation.sh
. supplimentary/libs/filesystem.sh
. supplimentary/libs/packages.sh
. supplimentary/libs/display.sh


# Chose our default settings
chooseInstallSettings

# Detect old settings in the right situations.
case  "$1" in
	'--help')
		true
	;;
	'--dontDetect')
		true
	;;
	'--defaults')
		detectOldSettings defaults
	;;
	*)
		detectOldSettings
	;;
esac

# Check parameters for any settings that needs to be set.
checkParameters "$*" $0

# Make it happen
doInstall
