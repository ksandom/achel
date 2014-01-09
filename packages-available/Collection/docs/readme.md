# Collection

Collection provides a sane way of providing persistent data to an application, taking care of making sure the data is only loaded and saved once per session.

To use it, simply add `collectionLoad CollectionName` to the beginning of any script that needs to use or manipulate the data where `CollectionName` is the name of the collection you would like to use. *The save will happen automatically when Achel terminates*.

Examples of usage
* settings
* collections of things eg
 * types of task (eg doneIt)
 * filters used by --filter

## Using it

* Make sure `Collection` and `Data` are included via [repoParms](https://github.com/ksandom/achel/blob/master/docs/programming/creatingARepositoryWithProfiles.md#use-repoparmdefinepackages-to-create-a-profile). If BASE is included, it should be fine.
* Add `collectionLoad CollectionName` to the beginning of each script that needs to work with the data. That way the data will only get loaded when you absolutely need it. It should be below the header comment.
* Work with the data.
* Enjoy the persistence.

## A worked example

debork.macro would contain

    # Removes any borks found in the ReallyImportantDataSet. --debork takes no arguments. ~ bork,admin,example
    collectionLoad ReallyImportantDataSet
    
    retrieveResults ReallyImportantDataSet
    excludeEach bork
    stashResults ReallyImportantDataSet
    clear

In this example we

* Make sure the `ReallyImportantDataSet` is loaded.
* Retrieve the Store into the ResultSet.
* Remove all entries that contain bork.
* Stash the ResultSet back into the Store.
* Clear the ResultSet.

## Another worked example

borkSet.macro would contain

    # Saves a setting for the Bork application. --borkSet=settingName,settingValue ~ bork,user,example
    collectionLoad Bork
    
    set Bork,settings,~!Global,borkSet-0!~,~!Global,borkSet-1!~

In this example we

* Make sure the `Bork` Collection is loaded.
* Make the requested changes to the settings.
* `Bork` will be saved to disk when Achel terminates.

## Performance

Collection is currently the best performing method of data-disk interaction available in Achel at the moment.

For more information read the [Data documentation](https://github.com/ksandom/achel/blob/master/packages-available/Data/docs/dataToDisk.md)