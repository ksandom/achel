# Get

Get stuff from external places. 

Eg get data via HTTP (getHTTP). As of this writing, getHTTP is the only available feature.

## Status

IMPORTANT I haven't actually used this, so I'm not sure if it works.
TODO test it.

## Using it

* Make sure `Get` is included via [repoParms](https://github.com/ksandom/achel/blob/master/docs/programming/creatingARepositoryWithProfiles.md#use-repoparmdefinepackages-to-create-a-profile). If BASE is included, it should be fine.
* Perform the get (eg getHTTP).
* Use the results in the resultSet.

## A worked example

    # Gets the status of every host that has been found using --list . --getStatus takes no parameters. ~ hosts,example
    
    getHTTP status,/status,IP,5

To run this on all servers with donkey in their name, this would be called like so

    mass --list=doneky --getStatus

which would give us a result like this

      0: 
        hostName: donkey
        internalIP: 127.0.0.1
        internalFQDN: donkey.example.com
        externalIP: 
        externalFQDN: 
        location: 
        collection: manual
        pseudoID: 2548956920
        color: reversePurpleHLWhite
        filename: manual.json
        categoryName: default
        key: 0
        IP: 127.0.0.1
        FQDN: donkey.example.com
        status: up
