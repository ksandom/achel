# Path

Figure out directory and file paths for different contexts.

## Using it

Path is provided as part of the base install of Achel.

## A worked example

    # Get the full path to an external executable
    
    # Look in myRepo for a package called MyPackage
    getPackagePath Local,myPackagePath,myRepo,MyPackage
    debug 1,The package is located at ~!Local,myPackagePath!~


