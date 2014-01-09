# UserInteractoin

Provides reuseable functionality for interacting with a user in a consistent way.

As of this writing, it consists solely of `--complain`.

## Using it

* Make sure `UserInteractoin` is included via [repoParms](https://github.com/ksandom/achel/blob/master/docs/programming/creatingARepositoryWithProfiles.md#use-repoparmdefinepackages-to-create-a-profile). If BASE is included, it should be fine.
* Invoke the required functionality where needed.

## A worked example

    # Say the output you give. --say='message text' (no quotes when within a macro) ~ ui,example
    
    if ~!Global,say!~,!=,,
    	debug 0,say: ~!Global,say!~
    else
    	complain y u no input?
