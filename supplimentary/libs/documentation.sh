# Handel management of documentation

# Requires
#   packages.sh
#   filesystem.sh

function documentationAddRepo
{
	repoName="$1"
	base="$configDir/docs/repos/$repoName"
	repoDir="$configDir/repos/$repoName"
	
	echo "documentationAddRepo: Adding repo \"$repoName\"."
	mkdir -p "$base/packages"
	cd "$base"
	
	# Link the overview and readme.md
	linkSrc "$repoDir/readme.md" "readme.md"
	linkSrc "$repoDir/docs" "overview"
	
	cd "$base/packages"
	listPackages "$repoName" | while read package; do
		linkSrc "$repoDir/packages-available/$package/docs" "$package"
	done
	
	# TODO test this. It's not yet called from anywhere.
}

function documentationRemoveRepo
{
	repoName="$1"
	base="$configDir/docs/$repoName"
	
	echo "documentationRemoveRepo: Removing repo \"$repoName\""
	
	rm -Rf "$base"
}

function documentationAddProfile
{
	profileName="$1"
	base="$configDir/docs/$profileName"
	profileDir="$configDir/profiles/$profileName"
	
	echo "documentationAddProfile: Adding profile \"$profileName\"."
	
	mkdir -p "$base"
	cd "$base"
	listPackagesForProfile "$profileName" | while read package; do
		linkSrc "$profileDir/packages/$package/docs" "$package"
	done
}

function documentatioRemoveProfile
{
	profileName="$1"
	base="$configDir/docs/$profileName"
	
	echo "documentatioRemoveProfile: Removing profile \"$profileName\"."
	
	rm -Rf "$base"
}

# TODO think about what to do when packages are added or removed to a profile.
