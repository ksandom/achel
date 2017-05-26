# The achelManagement side of bash completion.

function installBashCompletion
{
	bcProgramName="$1"
	
	echo "Setting up bash completion for $bcProgramName"
	
	if [ `id -u` -gt 0 ];then # User install
		addBashCompletionToUserProfile
		addBashCompletionFiles ~/.bash_completion.d "$bcProgramName"
	else # Root install
		addBashCompletionFiles "/etc/bash_completion.d" "$bcProgramName"
	fi
}

function addBashCompletionToUserProfile
{
	fileToUse=~/.bashrc
	
	bashRCComment="# Local tab completion config."
	
	if ! grep -q "$bashRCComment" "$fileToUse"; then
		echo "$bashRCComment" >> "$fileToUse"
		echo 'for fileName in ~/.bash_completion.d/*; do . $fileName;done' >> "$fileToUse"
	fi
}

function addBashCompletionFiles
{
	directoryLocation="$1"
	bcProgramName="$2"
	
	mkdir -p "$directoryLocation"
	cd "$directoryLocation"
	
	copyTemplatedFile "$startDir/src/bashCompletion.sh" "$bcProgramName"
	
	cp "$startDir/supplimentary/libs/achelBashCompletionLib.sh" aa-achelShared
}
