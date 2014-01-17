# Maths

Provides some basic maths operations.

More info at `achel --help=Maths`

## Using it

* Make sure `Maths` is included via [repoParms](https://github.com/ksandom/achel/blob/master/docs/programming/creatingARepositoryWithProfiles.md#use-repoparmdefinepackages-to-create-a-profile). If BASE is included, it should be fine.
* Perform options as needed.

## A worked example - basicMaths

`blackAdder.macro` would look like this

    # Adds up two numbers and outputs them in black text. --blackAdder=firstNumber,secondNumber ~ maths,example
    
    basicMaths Result,sum,~!Global,blackAdder-0!~,+,~!Global,blackAdder-1!~
    debug 1,~!Color,blackHLWhite!~~!Result,sum!~~!Color,default!~

And then we run it like this

    $ achel -v --blackAdder=1,1
    [debug1]: verbosity: Incremented verbosity to "Information" (1)
    [debug1]: 2

* In the macro we
 * add the two parameters and stick the result in `Result,sum`.
 * set the color, show the result and then reset the colour (always do this).
* On the command line we
 * increment the verbosity to 1.
 * ask the blackAdder macro to add 1 and 1.

## A worked example - base

`sayHi.macro` would look like this

    # Say hi to the user. --sayHi takes no parameters. ~ base,example
    
    intToHex Example,result,30516,5
    get Example,result

* Here we convert 30516 into hex, while asserting that the length of the hex result is 5 characters.
* Then we get the result so it will be displayed on exit.

I've leave it to you to work out what the result is ;)
In the mean time, if you'd like to find out without having to write a macro, the way you'd do it on the command line is like this

    $ achel --intToHex=Example,result,30516,5 --get=Example,result
