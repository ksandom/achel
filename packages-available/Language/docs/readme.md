# Language

Provides the language constructs that don't need to be in the core.

## Using it

* Make sure `Language` is included via [repoParms](https://github.com/ksandom/achel/blob/master/docs/programming/creatingARepositoryWithProfiles.md#use-repoparmdefinepackages-to-create-a-profile). If BASE is included, it should be fine.
* All the `Language` functionality should then be able to be used.

## More detail

### Condition

Choose different branches of code based on the result of a test.

Note that indentation (a TAB character) works really well with indentation. Just make sure the origin line ends with a comma.

#### A worked example

Here are the contents of isBork.macro

    # Be really happy if "bork" is specified. --isBork=input where input is a string ~ Condition,example
    
    if ~!Global,isBork!~,==,bork,
    	debug 1,BOOOOOORK bork! bork! bork! bork!
    elseIf ~!Global,isBork!~,>,0,
    	debug 1,~!Global,isBork!~ bork!.... bork! bork! bork!
    else
    	debug 1,No bork. So saaad.

Then we call it

    $ achel -v --isBork=blah
    [debug1]: verbosity: Incremented verbosity to "Information" (1)
    [debug1]: No bork. So saaad.
    
    $ achel -v --isBork=2
    [debug1]: verbosity: Incremented verbosity to "Information" (1)
    [debug1]: 2  bork!.... bork! bork! bork!
    
    $ achel -v --isBork=bork
    [debug1]: verbosity: Incremented verbosity to "Information" (1)
    [debug1]: 2  BOOOOOORK bork! bork! bork! bork!

#### More information

There's lots more you can do. All of it works the same way. You can find out about it like so

    $ achel --help=Condition

### Types

Recognise different logical data types that a string can represent.

#### A worked example

    $ achel --getType=Example,type,blah --get=Example,type
    
      0: string
    
    $ achel --getType=Example,type,0 --get=Example,type
    
      0: number

* In both examples we test a value ("blah" and 0 respectively) and stick the result into Example,type.
* Then we get that value into the resultSet.
