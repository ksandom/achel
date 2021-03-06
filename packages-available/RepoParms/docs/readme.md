# RepoParms

Tracks parameters for repositories such as name, description and what packages to use in each profile it provides.

This is primarily meant to be used by `achelctl` although it could be developed to help applications intellegently determine what functionality is available to them. 

*If you are looking for day-to-day repoParms usage, see [docs/programming/creatingARepositoryWithProfiles.md](https://github.com/ksandom/achel/blob/master/docs/programming/creatingARepositoryWithProfiles.md)*

## Using it

* Make sure `Language` is included via [repoParms](https://github.com/ksandom/achel/blob/master/docs/programming/creatingARepositoryWithProfiles.md#use-repoparmdefinepackages-to-create-a-profile). If BASE is included, it should be fine.
* Get some parameters.

## A worked example

Let's find out what repositories we have. The easiest way right now is with `achelctl` like so

    $ achelctl repoList --short
    achel
    colouredWeb
    doneIt
    mass
    tui

Let's take a look at what `achel` looks like

    $ achel --listRepoParms=achel
    
      name: achel
      achel: 
        name: achel
        description: A programming language for infinite flows and small data sets.
        execName: achel
        packages: 
          BASE: 
            sourceRepo: achel
            packageRegex: .*

This example ends prematurely since the rest of what I want to show you is not in a useful state. You can query this data like any other resource, but please use `achelctl` to manipulate any settings.

## More info

`achel --help=RepoParm`
