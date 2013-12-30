# Stuff for managing the libraries and supplimentary scripts.

function getListOfSupplimeentaryScripts
{
	cd "$supplimentaryDir"
	while read file;do
		if [ -f "$file" ]; then
			description=`grep -A 1 "Description" "$file" | tail -n 1 | sed 's/# *//g'`
			echo "$name $file	$description"
		fi
	done < <(ls -1)
}

function displayListOfSupplimeentaryScripts
{
	getListOfSupplimeentaryScripts | sed 's/	/ - /g;s/^/   /g'
}

function processParms
{
	for parm in "$@"; do
		parmName=`echo "$parm" | cut -d\- 3- | sed 's/=.*$//g'`
		parmValue=`echo "$parm" | cut -d\- 3- | sed 's/^.*=//g'`
		export parm_$parmName=$parmValue
	done
}
