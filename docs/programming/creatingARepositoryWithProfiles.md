# Introduction

When you create a repo, you will likely want profiles to be automatically configured when the repo is installed. This document describes how to do it.

In most situations, your easiest way of defining the parameters is to use `achelctl repoCreateUsingWizard` which will run you through creating the repository and setting the parameters accordingly.

# Detail

## Every day scenario

Use `achelctl repoCreateUsingWizard` to create/setup a repository. It will guide you and take care of the chicken & egg scenario for you :)

Most of this document relies on you having used the wizard to get set up since you need a repository to be installed to use repoParms, but you need repoParms to be defined to install the repository.

It will explain everything you need to do and walk you through any problems.

## Pulling in packages from another repository

The most common case where you'll want to do this is including the Achel libraries. The wizard will have done this for you. But if you want some functionality from another repository, this is how you go about it.

This example works with the kevtest2345 repo.

Let's see what repositories we could pull from?

    $ achelctl repoList --short
    achel
    colouredWeb
    doneIt
    mass
    tui
    kevtest2345

Let's pull from doneIt

    $ achelctl repoParmDefinePackages kevtest2345 profiles123 pacakgeSetName123 doneIt '.*'

And it looks like this

    $ achelctl repoList kev
    kevtest2345
    
      name: kevtest2345
      profile123: 
        name: profile123
        description: A made up application for the documentation.
        execName: application123
      profiles123: 
        packages: 
          SELF: 
            sourceRepo: kevtest2345
            packageRegex: .*
          BASE: 
            sourceRepo: achel
            packageRegex: .*
          pacakgeSetName123: 
            sourceRepo: doneIt
            packageRegex: .*

Let's apply these changes

    $ achelctl repoReinstall kevtest2345

* Here we've specified we wanted all packages (`.*`) from `doneIt`. We could restrict this down by replacing the `.*` with a more restrictive regex.
 * If you want to specify several packages, rather than making a nasty regex with lots of logical ORs, use multiple entries instead. The only requirement is that the packageSetName (`pacakgeSetName123`) is unique.

## Pushing to another profile

Here we are going to push to the `doneIt` profile from `kevtest2345`. You would do this when you want to extend the functionality of another application.

First we need to set a name and description. In the future these will not be needed any more for this purpose, however currerently they are still needed for sanity checks to pass.

    $ achelctl repoParmSet kevtest2345 doneIt name doneIt
    $ achelctl repoParmSet kevtest2345 doneIt description 'Send functionality to doneIt'

Now let's say that we want the packages to be sent over.

    $ achelctl repoParmDefinePackages kevtest2345 doneIt SELF
    /home/ksandom/.achel/supplimentary/repoParmDefinePackages: Assumed sourceRepo="kevtest2345" and packageRegex=".*"

And it looks like this

    $ achelctl repoList kev
    kevtest2345
    
      name: kevtest2345
      profile123: 
        name: profile123
        description: A made up application for the documentation.
        execName: application123
      profiles123: 
        packages: 
          SELF: 
            sourceRepo: kevtest2345
            packageRegex: .*
          BASE: 
            sourceRepo: achel
            packageRegex: .*
      doneIt: 
        packages: 
          SELF: 
            sourceRepo: kevtest2345
            packageRegex: .*
        name: doneIt
        description: Send functionality to doneIt

Let's apply these changes

    $ achelctl repoReinstall kevtest2345

* Here we've specified we want all to send all packages (`.*`) from `kevtest2345` to the `doneIt` profile. We could restrict this down by replacing the `.*` with a more restrictive regex.
 * If you want to specify several packages, rather than making a nasty regex with lots of logical ORs, use multiple entries instead. The only requirement is that the packageSetName (`pacakgeSetName123`) is unique.

# Stuff explained a little more

## Profiles

You need one profile per application. If you want to create multiple applications in one repository, you will need to create a profile for each one.

If you're unsure, profileName should probably be set the same as the repoName.

