# Help

Provides help using mandatory internal documentation.

## Using it

* Make sure `Help` is included via [repoParms](https://github.com/ksandom/achel/blob/master/docs/programming/creatingARepositoryWithProfiles.md#use-repoparmdefinepackages-to-create-a-profile). If BASE is included, it should be fine.
* Providing help
 * Provide a desent description in the declaration comment on the first line of macros.
 * Provide desent tags in the declaration comment on the first line of macros.
* Searching help
 * On the command line, use --help=searchRegex where searchRegex is a regular expression to find what you want to know about.

## A worked example

### Providing help

lazy.macro could contain

    # This example does absolutely nothing. --lazy takes no parameters. ~ nothing,example
    #onDefine aliasFeature lazy,reallyLazy
    
    pass

* The description is "This example does absolutely nothing. --lazy takes no parameters." Note it says:
 * What it does.
 * How to use it.
* Tags `nothing` and `example` are defined.
* Alias the feature so that it can also be called with `--reallyLazy`.
* `pass` is the programmers' way to say "yeah, I haven't forgotten, I literally don't want to you do anything right now."

### Searching help

This example is on the command line

    $ achel --help=lazy
    Available features:
    --lazy, --reallyLazy ~ nothing,example (Macro)
      This example does absolutely nothing. --lazy takes no parameters.
