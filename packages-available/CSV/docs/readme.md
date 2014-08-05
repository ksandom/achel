# CSV

Loads CSV files. Typically you'd use a template of --toString to generate a template. Although if there's a need to do it automatically in the future, I'll write it.

## Using it

* Make sure `CSV` is included via [repoParms](https://github.com/ksandom/achel/blob/master/docs/programming/creatingARepositoryWithProfiles.md#use-repoparmdefinepackages-to-create-a-profile). If BASE is included, it should be fine.
* Perform options as needed.

## A worked example

Here is a file

    $ cat example.csv 
    Field1,Field2,Field3
    a1,a2,a3
    b1,b2,b3

We can load it

    $ achel --loadCSV=example.csv
    
      0: 
        Field1: a1
        Field2: a2
        Field3: a3
      1: 
        Field1: b1
        Field2: b2
        Field3: b3

And then we can do stuff with it

    $ achel --loadCSV=example.csv --requireItem=Field2,b2
    
      1: 
        Field1: b1
        Field2: b2
        Field3: b3
