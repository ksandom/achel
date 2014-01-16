# Load

Provides a safe way to load something only once in an Achel session.

This should not be confused with the php $_SESSION variable, which depending on your usage, is likely to last much longer.

## Using it

* Make sure `Load` is included via [repoParms](https://github.com/ksandom/achel/blob/master/docs/programming/creatingARepositoryWithProfiles.md#use-repoparmdefinepackages-to-create-a-profile). If BASE is included, it should be fine.
* Create a macro to do the work that should only be done once.
* Run `load macroName` as many times as you want.

## A worked example

`doTellMe.macro` would contain

    # Tell you something ~ load,example
    
    debug 1,I'm not going to tell you twice!

`tellMe.macro` would contain

    # Tell you something once ~ load,example
    
    load doTellMe

Then, we run it

    $ achel -v --tellMe --tellMe --tellMe
    [debug1]: verbosity: Incremented verbosity to "Information" (1)
    [debug1]: I'm not going to tell you twice!

We could actually simplify this example slightly

    $ achel -v --load=doTellMe --load=doTellMe --load=doTellMe
    [debug1]: verbosity: Incremented verbosity to "Information" (1)
    [debug1]: I'm not going to tell you twice!

This means we can do away with `--tellMe.macro`.

* We create something to run once.
* We then invoke it by prepending `load `.
* Note that no parameters have been passed along to the macro to be loaded. This is because `load` doesn't currently support it. There is a hack that works, however there will be an official way to do it soon.
