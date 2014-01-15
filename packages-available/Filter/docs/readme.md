# Filter

Provides a way to refine results in exactly the same way every time. This is useful for queries that you want to do regularly like getting just the live servers in mass with --filter=live (for example).

## Using it

* Make sure `Filter` is included via [repoParms](https://github.com/ksandom/achel/blob/master/docs/programming/creatingARepositoryWithProfiles.md#use-repoparmdefinepackages-to-create-a-profile). If BASE is included, it should be fine.
* Define a filter
* Refine a resultSet using that filter

## A worked example

Let's create some data to filter

    $ achel --set=Example,,'a fast cat' --set=Example,,'a fast dog' --set=Example,,'a fat cat' --set=Example,,'a fat dog' --set=Example,,'a stupid cat' --set=Example,,'a stupid dog' --set=Example,,"Aren't we all stupid?" --retrieveResults=Example

Define a filter on the command line

    $ achel 

