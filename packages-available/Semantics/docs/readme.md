# Semantics

Keeps a fairly close track of the data type to determine what can be run against the data available.

One of the advantages of using semantics with your functionality is that you can use the `--more` and `--less` to choose more and less verbose templates to display the data. `--whatNext` is also interesting as it shows you what functionality can be run against the current data.

At this point there is no enforcing, though this could be done in the future if all functionality adheres to it.

TODO This is a first go at this documentation and there is a lot of room for improvement. Potentially it could benefit from breaking the package documentation mold a little.

## Using it

### User

For you, all you need to know about is `--more`, `--less` and `--whatNext`. See the examples below to see how these are used.

### Programming/Admin

If your package creates any data types that are not created in Semantics by something else, then you need to use `createDataType` and `createFeatureType` at install time. Please see the examples below.

Here's an overview

* Make sure `Semantics` is included via [repoParms](https://github.com/ksandom/achel/blob/master/docs/programming/creatingARepositoryWithProfiles.md#use-repoparmdefinepackages-to-create-a-profile). If BASE is included, it should be fine.
* Definitions are created persistently at install time.
* When a feature is executed, if a definition matches then that definition will now represent the current data type.
 * Note that the code to achieve this located in the core to keep the performance reasonable. For this reason the Semantics package is not totally independant. However the core should not rely on the Semantics package in any way to be able to execute code. The Semantics package configures Semantics and proivides some user functionality.
 * Note that if Semantics is to enfore data types in the future, this is where it would be done.
* User functionality executed to alter the behavior.
 * `--more` and `--less` change which template is chosen from the definition.
 * `--whatNext` replaces the resultSet with help for the features which could have been called with the data that existed in that resultSet until that point.

## Worked examples - user

These examples demonstrate `--more` and `--less` on the command line.

First let's get some data

    $ achel --listDataTypes
    dataType / Defines how a dataType will be displayed.
      templateOut / dataTypeLess / dataType / dataTypeMore
    featureType / Defines how a featureType will be displayed.
      templateOut / featureTypeLess / featureType / featureTypeMore
    feature / Features
      templateOut / feature / feature / feature
    task / Something you can do.
      templateOut / task / task / task
    did / A task you have done or worked towards.
      templateOut / did / did / did
    oAuthAPI / Defines APIs for OAuth to talk to.
      templateOut / oAuthAPI / oAuthAPI / oAuthAPI
    oAuthEndPoint / Defines endPoints for APIs for OAuth to talk to.
      templateOut / oAuthEndPoint / oAuthEndPoint / oAuthEndPoint
    ELB / Elastic load balancer.
      templateOut / elbLess / elb / elbMore
    route53 / Route53 DNS entry.
      templateOut / route53 / route53 / route53
    host / All types of hosts.
      templateOut / hostLess / host / hostMore

We want something more concise

    $ achel --listDataTypes --less
    dataType / Defines how a dataType will be displayed.
    featureType / Defines how a featureType will be displayed.
    feature / Features
    task / Something you can do.
    did / A task you have done or worked towards.
    oAuthAPI / Defines APIs for OAuth to talk to.
    oAuthEndPoint / Defines endPoints for APIs for OAuth to talk to.
    ELB / Elastic load balancer.
    route53 / Route53 DNS entry.
    host / All types of hosts.

Or maybe more verbose, but let's just take the first 3

    $ achel --listDataTypes --more --first=3
    dataType / templateOut
      less:    dataTypeLess 
      default: dataType 
      more:    dataTypeMore 
      Defines how a dataType will be displayed.
    featureType / templateOut
      less:    featureTypeLess 
      default: featureType 
      more:    featureTypeMore 
      Defines how a featureType will be displayed.
    feature / templateOut
      less:    feature 
      default: feature 
      more:    feature 
      Features

## Worked examples - programming/admin

Normally I try not to put live code in examples, but this illustrates it so simply and beautifully.

This is what `installDataTypesForSemantics.macro` contains

    # Install the data types to be used with semantics. Ya have to try your own medicine roooight?. ~ semantics,internal,install
    #onDefine registerForEvent Install,dataTypes,installDataTypesForSemantics
    
    createDataType dataType,dataType,dataTypeMore,dataTypeLess,,Defines how a dataType will be displayed.
    createDataType featureType,featureType,featureTypeMore,featureTypeLess,,Defines how a featureType will be displayed.

This is what `installFeatureTypesForSemantics.macro` contains

    # Install the feature types to be used with semantics. ~ semantics,internal,install
    #onDefine registerForEvent Install,featureTypes,installFeatureTypesForSemantics
    
    createFeatureType generateDataTypes,,dataType,Generates a list of dataTypes.
    createFeatureType generateFeatureTypes,,featureType,Generates a list of featureTypes.

* Here we have two macros, one for data and one for features, which register for the events `Install,dataTypes` and `Install,featureTypes` respectively.
* The data types are created. The first one goes like this
 * The typeName is `dataType`. This is a unique identifier that will be referred to by at least one featureType.
 * The default template is `dataType`. This will be used if neither `--more` nor `--less` have been specified.
 * The template to use when `--more` is specified is `dataTypeMore`.
 * The template to use when `--less` is specified is `dataTypeLess`.
 * The action to be performed when achel terminates defaults to `templateOut`, which is what we want, and therefore hasn't been specified.
 * The description is `Defines how a dataType will be displayed.`
* The feature types are created. The first one goes like this
 * The typeName is `generateDataTypes`. This is a unique identifier.
 * The inDataType is empty and therefore this featureType doesn't require input data.
 * The outDataType is `dataType`. This means that a feature of this type will produce data of type `dataType`.
 * The description is `Generates a list of dataTypes.`

Here's the macro that lists the features types for semantics

    # List feature types. --listFeatureTypes=regex ~ semantics,featureTypes
    #onDefine aliasFeature listFeatureTypes,listFT
    #onLoaded setFeatureType listFeatureTypes,generateFeatureTypes
    
    retrieveResults Semantics,featureTypes
    requireEach ~!Global,listFeatureTypes!~

* The `#onDefine aliasFeature` line simply makes the feature available as `--listFT` as well as `--listFeatureTypes`.
* The `#onLoaded setFeatureType` **is the important line** that tells achel what type of feature this is.
* The rest is just performing the actions.

## Solving Problems

### [debug0]: callFeature: Could not find a module to match ','

I have caused this by failing to create the feature type. In my case it was a typo in registering for the `Install,featureTypes` event.

This lead to `callFeature()` assuming that there is no semantics data for the feature, and therefore falling back to basic, explicit behavior.

The same thing could happen if the corresponding data type is not created.

## More info

`$ achel --help=Semantics`


