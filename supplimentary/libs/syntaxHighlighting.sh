# Manage syntax highlighting definitions

function highlightingInstall
{
	highlightingInstallKate
}


# Kate (KDE Advanced Text Editor)
function highlightingInstallKate
{
	achel --combineFeatureSets --templateOut=KateSyntaxHighlighting > ~/.kde/share/apps/katepart/syntax/achel.xml
}
