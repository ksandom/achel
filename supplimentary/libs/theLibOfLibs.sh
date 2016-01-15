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
	refine="$1"
	
	if [ "$refine" == '' ];  then
		getListOfSupplimeentaryScripts | sed 's/	/ - /g;s/^/   /g'
	else
		getListOfSupplimeentaryScripts | grep "$refine" | sed 's/	/ - /g;s/^/   /g'
	fi
}

function processParms
{
	for parm in "$@"; do
		if [ "${PATH:0:2}" == '--' ]; then
			parmName=`echo "$parm" | cut -d\- -f3- | sed 's/=.*$//g'`
			parmValue=`echo "$parm" | cut -d\- -f3- | sed 's/^.*=//g'`
			
			export parm_$parmName=$parmValue
		fi
	done
}
