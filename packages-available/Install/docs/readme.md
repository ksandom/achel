# Install

Provides functionality to support the install.

The average user will rarely need to know about anything in here. It could however be useful to developers wanting to know how to hook the setup of their applications into the install.

For information about how to install Achel and other applications, please start at the [Achel readme](https://github.com/ksandom/achel/blob/master/readme.md)

## Using it

* Make sure `Install` is included via [repoParms](https://github.com/ksandom/achel/blob/master/docs/programming/creatingARepositoryWithProfiles.md#use-repoparmdefinepackages-to-create-a-profile). If BASE is included, it should be fine.
* Near the end of the install process `--finalInstallStage` will be called.
* This is turn triggers three events
 * Install,early
 * Install,general
 * Install,late

## The install events

*`Install,general` should be used for pretty much everything.*
`Install,early` and `Install,late` are only to be used in the rare situation where there are dependancies on one package being installed to be able to install another.

## A worked example

    # Sets aValue to 1 during install ~ install,example
    #onDefine registerForEvent Install,general,setAValueToOne
    
    load collectionLoad Example
    set Example,aValue,1
