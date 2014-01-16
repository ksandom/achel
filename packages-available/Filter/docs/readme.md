# Filter

Provides a way to refine results in exactly the same way every time. This is useful for queries that you want to do regularly like getting just the live servers in mass with --filter=live (for example).

## Using it

* Make sure `Filter` is included via [repoParms](https://github.com/ksandom/achel/blob/master/docs/programming/creatingARepositoryWithProfiles.md#use-repoparmdefinepackages-to-create-a-profile). If BASE is included, it should be fine.
* Define a filter
* Refine a resultSet using that filter

## A worked example

Here's some data

    $ achel --setArray=Example,things,"a fast cat,a fast dog,a fat cat,a fat dog,a stupid cat,a stupid dog" --retrieveResults=Example,things
    
      0: a fast cat
      1: a fast dog
      2: a fat cat
      3: a fat dog
      4: a stupid cat
      5: a stupid dog

Define a filter on the command line by adding the `--filter` to the end of it all

    $ achel --setArray=Example,things,"a fast cat,a fast dog,a fat cat,a fat dog,a stupid cat,a stupid dog" --retrieveResults=Example,things --filter=cat,"only show results containing a cat",,cat
    
      0: a fast cat
      2: a fat cat
      4: a stupid cat

Define more filters in createExampleFilters.macro that automatically runs at install time

    # Install example filters ~ filter,example
    #onDefine  registerForEvent Install,general,createExampleFilters
    
    # Note that on the first one, we've decided to say not to replace it in case the user has updated the filter.
    saveFilter cat,only show results containing a cat,,cat,noReplace
    saveFilter dog,only show results containing a dog,,dog
    saveFilter fast,only show results containing something fast,,fast
    saveFilter fat,only show results containing something fat,,fat
    saveFilter stupid,only show results containing something stupid,,stupid

After an install is invoked, we can use the filters across all Achel applications

    $ achel --setArray=Example,things,"a fast cat,a fast dog,a fat cat,a fat dog,a stupid cat,a stupid dog" --retrieveResults=Example,things --filter=cat
    
      0: a fast cat
      2: a fat cat
      4: a stupid cat

    $ mass --setArray=Example,things,"a fast cat,a fast dog,a fat cat,a fat dog,a stupid cat,a stupid dog" --retrieveResults=Example,things --filter=fat
    
      2: a fat cat
      3: a fat dog

Suddenly it's declared politically incorrect to search for stupid things

    $ achel --deleteFilter=stupid

Right, so here's the low down

* We created a filter on the fly with `--filter=cat,"only show results containing a cat",,cat` so that we could see that the filter was behaving as expected.
 * `cat` is the name of the filter. This name must be unique across all Achel applications since they share a lot of functionality.
 * `"only show results containing a cat"` is the description. This is particularly useful in `--listFilters`
 * The empty parameter between the ,, defines which field we want to refine on. When it's left blank like this, a result will match if any of it's fields match the regex.
 * `cat` is the regex to search for.
* We created some filters using a macro at install time. This is currently the best way to distribute filters you want users to use.
 * Note that we used `saveFilter` instead of `filter`. This is so that it doesn't interfere with the resultSet since we aren't going to be looking at it anyway.
* We searched with `--filter=cat` and `--filter=fat`
* We deleted a filter with `--deleteFilter=stupid`