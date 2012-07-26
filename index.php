#!/usr/bin/php
<?php
# Copyright (c) 2012, Kevin Sandom under the BSD License. See LICENSE for full details.
 
define('programName', 'mass');
$description="A commandline tool/API for doing awesome stuff on many nodes of a cluster.";

# Find home
$home=trim(`echo \$HOME`); # TODO There is a better way than this!

# Find bzse
if (file_exists("$home/.".programName."/core.php"))
{
	$configDir="$home/.".programName;
}
elseif (file_exists("/etc/".programName."/core.php"))
{
	$configDir="/etc/".programName;
}
else
{
	die (programName.": Could not find installation. Please run install.sh.\n");
}

include "$configDir/core.php";


# initiate core
$core=core::assert();
$core->set('General', 'configDir', $configDir);
$core->set('General', 'hostsDir', "$configDir/data/1LayerHosts");
$core->set('General', 'programName', programName);
$core->set('General', 'description', $description);
include ($configDir.'/interfaces/basicWeb.php');
$core->setRef('BasicWeb', 'arguments', $_REQUEST);
loadModules($core, "$configDir/modules-enabled");

$core->go();

?>
