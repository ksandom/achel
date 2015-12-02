# Json

More fomalised Json support for Achel. Eventually most Json support will be moved into this package.

## Using it

* Make sure `Json` is included via [repoParms](https://github.com/ksandom/achel/blob/master/docs/programming/creatingARepositoryWithProfiles.md#use-repoparmdefinepackages-to-create-a-profile). If BASE is included, it should be fine.
* See the examples below

## A worked example - toJsons

    # Put some data in the result set
    setNested Local,testResultSet,a,b,c
    retrieveResults Local,testResultSet
    
    # Convert it
    toJsons
    
    # The resultSet now looks like this
    #   a: {"b":"c"}



