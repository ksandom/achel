# The supplimentary folder

is a place to put bash scripts that manage your application. They will then appear as options to the manageAchel application.

There are lots of libraries to improve code re-use.

## Using the supplimentary folder

The supplimentary folder goes in the root of a given repository. If you would like to reuse code that is not provided by another repository, you can do so by putting it in the libs folder inside the supplimentary folder. So the structure looks like this

    ./supplimentary
    ./supplimentary/aScript
    ./supplimentary/libs
    ./supplimentary/libs/aLibrary.sh

Inside the aScript file there will be a line that pulls in the library and looks like this

    . $libDir/aLibrary.sh

More information about this can be found in "The anatomy of a supplimentary script" section.

## The anatomy of a supplimentary script

### The normal bash declaration

    #!/bin/bash

### The comment section

at the begining of the script contains the description, syntax and example usage. It looks like this

    # Description
    #   Installs a repository from start to finish. **Most users will want this.**
    #
    # Syntax:
    #   $0 repoAddress[=version] [overRideRepoName]
    #     repoAddress is where to get the repo from.
    #     version can be a tag, branch or specific commit in the git history.
    #     overRideRepoName allows you to resolve a conflict if two different repositories specify the same name. 
    #       Note that you will be in for many headaches if you need to do this.
    #       I recommend that you uninstall one of the two repos.
    #
    # Examples:
    #   Install a github repo.
    #     $0 http://github.com/ksandom/something
    #
    #   Another way to install the same repo.
    #     $0 git@github.com:ksandom/something
    #
    #   How to install a repo that is stored on your local machine.
    #     $0 /usr/local/something

The formatting is important, because it's used for generating help. So let's look at it a bit closer

    # Description
    #   Installs a repository from start to finish. **Most users will want this.**

While achel is a tab indented project, the indentation for the comments of the bash scripts is done using spaces.

* Everything is left aligned with one space.
* From then on everything is indented in increments of 2 spaces.
* Titles have 0 indentation.
* Content begis with one indentation.

So lookig at the description again,

    # Description
    #   Installs a repository from start to finish. **Most users will want this.**

The title is left aligned with one space. And then the content begins on the next line with one indentation beyond the left align, which is therefore 3 spaces.

### Required input

First we say how many parameters we require

    requiredParms="$1"

If you put $0, no parameters would be required. $1 for 1 parameters. $2 for 2 parameters etc. If insufficient parameters are provided, help will be displayed based on the comments at the begining of the script.

### Including libraries

The first one is

    . `dirname $0`/libs/includeLibs.sh

This sets up the environment for you. Pretty much all scripts will have this line.

Now pull in the libraries you want.

    . $libDir/repoInstall.sh
    . $libDir/getRepo.sh
    . $libDir/repoParms.sh
    . $libDir/packages.sh
    . $libDir/installLibs.sh
    . $libDir/documentation.sh

Now lets assign some meaningful names to our variables.

    repoAddress="$1"
    overRideRepoName="$2"

And finally we do some stuff. In this case we are using the repoInstall library to install a repository and triggering the final stage of that installation.

    installRepo "$repoAddress" "$overRideRepoName"
    achel --verbosity=2 --finalInstallStage

So all together it looks like this

    #!/bin/bash
    # Description
    #   Installs a repository from start to finish. **Most users will want this.**
    #
    # Syntax:
    #   $0 repoAddress[=version] [overRideRepoName]
    #     repoAddress is where to get the repo from.
    #     version can be a tag, branch or specific commit in the git history.
    #     overRideRepoName allows you to resolve a conflict if two different repositories specify the same name. 
    #       Note that you will be in for many headaches if you need to do this.
    #       I recommend that you uninstall one of the two repos.
    #
    # Examples:
    #   Install a github repo.
    #     $0 http://github.com/ksandom/something
    #
    #   Another way to install the same repo.
    #     $0 git@github.com:ksandom/something
    #
    #   How to install a repo that is stored on your local machine.
    #     $0 /usr/local/something
    
    requiredParms="$1"
    . `dirname $0`/libs/includeLibs.sh
    . $libDir/repoInstall.sh
    . $libDir/getRepo.sh
    . $libDir/repoParms.sh
    . $libDir/packages.sh
    . $libDir/installLibs.sh
    . $libDir/documentation.sh
    
    repoAddress="$1"
    overRideRepoName="$2"
    
    installRepo "$repoAddress" "$overRideRepoName"
    achel --verbosity=2 --finalInstallStage

## Internal documentation

Only comments with the `#` hard left-aligned will be displayed in generated help output. So internal comments can be indented in any form and they will not be displayed.

## The anatomy of a library

Put a comment at the beginning of the library describing what it does

    # This library indexes imaginary books.

Put in the code that does the work

    function indexBook
    {
    	local bookName="$1"
    	local genre="$2"
    	
    	echo "Whoops I dropped the book called \"$bookName\". It was supposed to go in the \"$genre\" section."
    }



