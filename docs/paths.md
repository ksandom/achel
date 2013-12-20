# Intro to paths
Achel can be installed system wide, for a particular user, or linked to the checked out repository. The structure changes a little based on which way you choose, but most of it stays the same.

For this document, _ACHEL_ refers to where achel is installed, and the checked out repository will be refered to as _REPO_.

# The paths

~/bin/achel (/usr/local/bin/achel on the mac) is a symlink to REPO/achel.

 * _ACHEL_/proilfes - You will find the -enabled folders in here.
 * _ACHEL_/profiles/commandLine - The profile for the command line interface.
 * _ACHEL_/profiles/commandLine/* - The variout -enabled directories. Note that -enabled is now omitted and therefore assumed.
 * _ACHEL_/docs links to REPO/docs
 * _ACHEL_/repos - The achel repo and anythird party repos go here. This is where packages etc are enabled from.
 * _ACHEL_/repos/achel - The achel repo.
 * _ACHEL_/examples links to REPO/examples
 * _ACHEL_/supplimentary - (coming soon) Various stuff that is not part of achel, but belongs with achel. Currently I'm using this for scripts that retrieve external dependancies.
 * _ACHEL_/externalLibraries - Where external dependancies go that are not part of achel, but really useful for it.
 * _ACHEL_/core.php links to REPO/core.php
 * _ACHEL_/config - is real. Everything in here is either unique to you or derived on install.
 * _ACHEL_/data - is real. Everything in here is either unique to you or derived on install.
 * _ACHEL_/index.php - Used for the web API
 * _ACHEL_/interfaces - Libraries for specific interfaces. These go here if they should not be shared with other interfaces.
 * _ACHEL_/obsolete - This appears if you upgrade from a version of achel that uses an obsolete layout. It's sole purpose is to keep hold of non-standard configuration you may have installed that would have otherwise been destroyed in the upgrade process. If there is nothing in here that you need, it is safe to delete.

NOTE that the commandLine profile is no longer used and shouldn't exist after doing an install.


# System wide

 * _ACHEL_ is /etc/achel
 * _ACHEL_/*-available folders are the various things that can be enabled or disabled.

Right now, everything goes into /etc/achel. 

TODO In the furture I intend to separate this out so that only configuration goes in /etc/achel. Anything else that appears there will simply be symlinks to the actual content. As far as the program is concerned, the content is relative to /etc/achel.

NOTE You may want to set permissions of various folders/files so that specific users can administor achel without root access.

TODO Make the permissions part of the default install via a group called achel which users can become a member of.

# User

 * _ACHEL_ is ~/.achel
 * _ACHEL_/*-available folders are the various things that can be enabled or disabled.

This is currently borked and is low on my priorities to fix. The idea is to install achel into ~/.achel and then symlink the achel file into ~/bin

The paths are all the same as the Linked install described below.

# Linked

 * _ACHEL_ is ~/.achel
 * _ACHEL_/*-available folders are the various things that can be enabled or disabled and are symlinks to REPO/*-available

TODO fix paths for the mac so that individual users can install achel on separate accounts.

