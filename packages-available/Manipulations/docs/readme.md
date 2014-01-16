# Manipulations

Provides features for manipulating the resultSet.

This is a hude package that grows as needed. Rather than try to explain everything here, please start by looking at the example below to see how it fits together, then do `achel --help=Manipulations` to see what features are available.

## Using it

* Make sure `Manipulations` is included via [repoParms](https://github.com/ksandom/achel/blob/master/docs/programming/creatingARepositoryWithProfiles.md#use-repoparmdefinepackages-to-create-a-profile). If BASE is included, it should be fine.
* Get a data set.
* Do some manipulations on it.

## A worked example

Here's some data

    $ achel --setArray=Example,things,"a fast cat,a fast dog,a fat cat,a fat dog,a stupid cat,a stupid dog" --retrieveResults=Example,things
    
      0: a fast cat
      1: a fast dog
      2: a fat cat
      3: a fat dog
      4: a stupid cat
      5: a stupid dog

Let's get the first three values

    $ achel --setArray=Example,things,"a fast cat,a fast dog,a fat cat,a fat dog,a stupid cat,a stupid dog" --retrieveResults=Example,things --first=3
    
      0: a fast cat
      1: a fast dog
      2: a fat cat

Now let's get only the second and third result

    $ achel --setArray=Example,things,"a fast cat,a fast dog,a fat cat,a fat dog,a stupid cat,a stupid dog" --retrieveResults=Example,things --first=3 --last=2
    
      1: a fast dog
      2: a fat cat

* We got some data
* We used `--first` and `--last` to select specific results.

## Another worked example

Here's some data

    $ achel --setArray=Example,things,"a fast cat,a fast dog,a fat cat,a fat dog,a stupid cat,a stupid dog" --retrieveResults=Example,things
    
      0: a fast cat
      1: a fast dog
      2: a fat cat
      3: a fat dog
      4: a stupid cat
      5: a stupid dog

Now let's get only the entries containing a cat

    $ achel --setArray=Example,things,"a fast cat,a fast dog,a fat cat,a fat dog,a stupid cat,a stupid dog" --retrieveResults=Example,things --refine=cat
    
      0: a fast cat
      2: a fat cat
      4: a stupid cat

Now let's get the entries that don't contain cat

    $ achel --setArray=Example,things,"a fast cat,a fast dog,a fat cat,a fat dog,a stupid cat,a stupid dog" --retrieveResults=Example,things --exclude=cat
    
      1: a fast dog
      3: a fat dog
      5: a stupid dog

* We got some data.
* We used `--refine` and `--exclude` to choose particular results.

## Summary

There's loooooots more functionality in this package. I really recommend taking a look at `achel --help=Manipulations`.
