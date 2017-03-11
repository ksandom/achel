# For building bug reports.

function creatBugReport
{
	profileName="$1"
	
	echo "Cache stats"
	achel --cacheStats
}
