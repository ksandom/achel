**TODO: This article needs to be updated, but should still be better than nothing in the mean time.**

# Introduction

When you create a repo, you will likely want profiles to be automatically configured when the repo is installed. This document describes how to do it.

# TLDR

## Stuff to run

* Create/clone a git repository
* manageAchel repoInstall /path/to/exampleRepo
* manageAchel repoParmSet exampleRepo . name exampleRepo
* manageAchel repoParmSet exampleRepo profileName name profileName
* manageAchel repoParmSet exampleRepo profileName description "An example tool"
* manageAchel repoParmSet exampleRepo profileName execName example
* manageAchel repoParmDefinePackages exampleRepo profileName SELF
* manageAchel repoParmDefinePackages exampleRepo profileName BASE

What is what

* /path/to/exampleRepo - Where you created/cloned a git repository.
* exampleRepo - should be substituded with the shortName (no spaces) which the repository will be referred to as.
* profileName - should normally be the same as the shortName. If you think you need an exception, please read "Use repoParmSet to define a profile." if you think it needs to be different.
* "An example tool" - The description of the application what will appear when you run --help on the application.
* example - The name of the executable that will be created for the application.

# Detail

## Outline

* Create a repository.
* Use repoInstall to install the repository.
* Give the repository a name.
* Use repoParmSet to define a profile.
* Use repoParmDefinePackages to create a profile.
* Use repoList 

## Create a repository.

* Create/clone a normal git repository.
* Make the directory packages-available in the root of that repository.

## Use repoInstall to install the repository.

From the help

    Syntax:
      manageAchel repoInstall repoAddress [overRideRepoName]

So

    manageAchel repoInstall /path/to/your/repository

This could also be a github or similar address.

## Give the repository a name.

This is the short name, which should be the same as the repository name on github or similar service. When someone installs this repository at a later date, it will be used to choose the name that the repository is referred to as. In this example we set the name to `doneIt`.

    manageAchel repoParmSet doneIt . name doneIt

## Use repoParmSet to define a profile.

You need one profile per application. If you want to create multiple applications in one repository, you will need to create a profile for each one.

If you're unsure, profileName should probably be set the same as the repoName.
TODO tidy up how repoName is documented. At the moment it is reffered to as `exampleRepo` above.

To define a profile we need to set a

* name - The shortName that has been used above.
* description - A short description of what the app does/is.
* execName (optional) - The name of the executable to be created. If this is omitted, the executable simple won't be created.

    manageAchel repoParmSet doneIt doneIt name doneIt
    manageAchel repoParmSet doneIt doneIt description "A tool for tracking time based on a notation I've been using on paper for the last few years."
    manageAchel repoParmSet doneIt doneIt execName doneit

## Use repoParmDefinePackages to create a profile.

In most situations that I have thought of so far, you will only need BASE and SELF which are presets. You can add them like this

    manageAchel repoParmDefinePackages doneIt doneIt SELF
    manageAchel repoParmDefinePackages doneIt doneIt BASE

There may be cases where you want to enable functionality from another repo.

    manageAchel repoParmDefinePackages doneIt doneIt todoLibs todo '.*'

In this example, we are saying
* call this group of packages `todoLibs`. This is to make it easy to admin the requirement later.
* the source repo is `todo`.
* match all (`.*`) packages in the `todo` repo.

## Use repoList to confirm

So if all has gone well, you should end up with something like this.

    ksandom@zelappy:/usr/files/develop/achel/docs$ manageAchel repoList doneIt
    doneIt - /home/ksandom/files/develop/mass-experimental/
    
      name: doneIt
      doneIt: 
        name: doneIt
        description: A tool for tracking time based on a notation I've been using on paper for the last few years.
        execName: doneit
        packages: 
          SELF: 
            sourceRepo: doneIt
            packageRegex: .*
          BASE: 
            sourceRepo: achel
            packageRegex: .*

