#!/bin/bash
# Install the program
# Copyright (c) 2012-2018, Kevin Sandom under the GPL License. See LICENSE for full details.

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
libDir="supplimentary/libs"

cd `dirname $0`
. "$libDir"/installLibs.sh
. "$libDir"/binCompatibility.sh
. "$libDir"/documentation.sh
. "$libDir"/filesystem.sh
. "$libDir"/packages.sh
. "$libDir"/display.sh
. "$libDir"/repoInstall.sh
. "$libDir"/repoParms.sh
. "$libDir"/syntaxHighlighting.sh
. "$libDir"/tabCompletion.sh
. "$libDir"/cache.sh



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
