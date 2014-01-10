# Get

Get stuff from external places. 

Eg get json via HTTP (getHTTP). As of this writing, getHTTP is the only available feature.

## Status

IMPORTANT I haven't actually used this, so I'm not sure if it works.

## Using it

* Make sure `Get` is included via [repoParms](https://github.com/ksandom/achel/blob/master/docs/programming/creatingARepositoryWithProfiles.md#use-repoparmdefinepackages-to-create-a-profile). If BASE is included, it should be fine.
* Perform the get (eg getHTTP).
* Use the results in the resultSet.

## A worked example

    # Gets the host definitions from a server serving up the definitions it has ~ hosts,example
    
    getHTTP hosts
    
TODO finish this
