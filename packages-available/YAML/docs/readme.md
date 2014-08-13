# CSV

Imports/Exports YAML.

## Using it

* Make sure `YAML` is included via [repoParms](https://github.com/ksandom/achel/blob/master/docs/programming/creatingARepositoryWithProfiles.md#use-repoparmdefinepackages-to-create-a-profile). If BASE is included, it should be fine.
* Perform options as needed.

## A worked example

    mass --setNested=A,b,c,d,1 --setNested=A,b,c,e,2 --setNested=A,b,c,f,2 --setNested=A,b,g,h,3 --retrieveResults=A,b --toYAML

