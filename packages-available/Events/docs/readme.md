# Events

Provides event hooks which macros can register to to extend functionality in a clean way.

## Using it

* Make sure `Events` is included via [repoParms](https://github.com/ksandom/achel/blob/master/docs/programming/creatingARepositoryWithProfiles.md#use-repoparmdefinepackages-to-create-a-profile). If BASE is included, it should be fine.
* Register a macro for an event.
* Trigger the event.

## A worked example

Here is a script called woogie.macro

    # Do the boogie woogie ~ event,example
    #onDefine registerForEvent Example,boogie,woogie
    
    debug 1,woogie!

Then we trigger the event. Let's do it on the command line, but you could do it in a macro in exactly the same way

    $ achel -v --triggerEvent=Example,boogie
    [debug1]: verbosity: Incremented verbosity to "Information" (1)
    [debug1]: woogie!

* We define macro `woogie` that registers itself for the `Example,boogie` event.
* All `woogie` does is output some debugging `woggie!`
* We then trigger the event `Example,boogie`
* Note that the `-v` is just for correctness since `debug0` should not be used except in very rare situations.
