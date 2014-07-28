# Functions for working with dropbox

function setDropboxHome
{
	if [ "$1" == "" ]; then
		local dbHome=~/Dropbox
	else
		local dbHome="$1"
	fi
	
	setDropboxValue "home" "$dbHome"
}

function setDropboxValue
{
	local valueName="$1"
	local valueValue="$2"
	
	achel --collectionLoad=Dropbox --setNested=Dropbox,"$valueName","$valueValue"
}

function showDropboxConfig
{
	achel --collectionLoad=Dropbox --getCategory=Dropbox --nested
}