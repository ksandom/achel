# Stuff that's useful for running the tests

function listTests
{
	ls -1 tests/* | sed 's/\.sh//g;s/^.*\//	/g'
}

function listTestsWithoutDebug
{
	ls -1 tests/* | sed 's/\.sh//g;s/^.*\///g' | grep -v ^debug$
}

function indent
{
	sed 's/^/    /g'
}

function reportLine
{
	printf "%-30s %-6s %-10s\n" "$1" "$2" "$3"
}
