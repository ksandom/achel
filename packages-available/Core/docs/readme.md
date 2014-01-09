# Core

Provides tests and tools for debugging the core and surrounding features.

## Using it

### describeMacro

Tells you what features are called in a specific macro. There a few important things to note

* The commands listed are what the compiler has resolved the original script to. They will always be the most complete, least ambiguous name.
* Variables get resolved at the time `describeMacro` is called, so they are outputted in what ever state they are in when `describeMacro` is run, not the state they would be if the macro had just been run. If you want that, then you need to set the verbosity to around 3 or 4.
* Indentation gets resolved as separate macros. You can look at those in the same way.

    $ achel --describeMacro=help
    templateOut help 
    searchHelp all 
    if ,!=,,help--6 
    else help--8 
    excludeItem tagString,hidden

Notice the if condition effectively says if nothing doesn't equal nothing, run `help--6`. But the original line says

    if ~!Global,help!~,!=,,

At the time I ran describeMacro `~!Global,help!~` didn't have a value. But if I do this

    $ achel --help=blah --describeMacro=help
    templateOut help 
    searchHelp all 
    if blah,!=,,help--6 
    else help--8 
    excludeItem tagString,hidden

We can see that the value has been resolved as expected.

### describeFeature

Tells you lots of *stuff* about a feature. Changing the above exampe from Macro to Feature we get

    $ achel --describeFeature=help
    
      0: 
        obj: I can't display this data type yet.
        flags: 
          0: help
          1: h
        name: help
        description: Display this help 
        tagString: macro,user,all,Macro
        provider: Macro
        isMacro: True
        source: /home/ksandom/.achel/profiles/achel/packages/achel-Help/help.macro
        referenced: 2

* You can ignore `obj`. It needs to be there, but it doesn't currently tell you anything interesting.
* `flags` are the ways you can invoke it. Eg --help or -h .
* `name` is what describeMacro would resolve it to when other macros call it.
* `description` is the description you see when --help tells you about a feature. This one is telling me I need to improve it!
* `tagString` is a list of tags that you can use for searching help efficiently.
* `provider` is the module that executes the code when that feature is called. For the forceeable future, macros will be handeled by Macro, although that may change in the future. However not all functionality provided by Macro is a macro. Therefore we have:
* `isMacro` which tells you if the feature is a macro. The other possibility is that it's a feature directly provided by the module.
* `source` tells you where the feature came from. This currently does not work for features provided by modules.
* `referenced` tells you how many times this feature is referenced in the current profile.
