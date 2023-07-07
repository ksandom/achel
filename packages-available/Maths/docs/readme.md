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

## psuedoMovingMean

In the unit tests, I'm starting with a value, and then every subsequent value is 0. The idea is to both test, and demonstrate how this pseudoMovingMean works. While with a normal moving-mean, there is a clear window that values are either in, or they are not; pseudoMovingMean decays old values by averaging them together. It is important to consider this when deciding whether this is the right solution for your needs.

```
defineTest pseudoMovingMean - data set 1,
    set Me,mean,
    pseudoMovingMean Me,mean,8,2
    expect 8,~!Me,mean!~

    pseudoMovingMean Me,mean,0,2
    expect 4,~!Me,mean!~

    pseudoMovingMean Me,mean,0,2
    expect 2,~!Me,mean!~

    pseudoMovingMean Me,mean,0,2
    expect 1,~!Me,mean!~

    pseudoMovingMean Me,mean,0,2
    expect .5,~!Me,mean!~

    pseudoMovingMean Me,mean,0,2
    expect .25,~!Me,mean!~

    pseudoMovingMean Me,mean,0,2
    expect .125,~!Me,mean!~
```

If this was a real moving mean; it should work like this:

```
defineTest movingMean - data set 1,
    set Me,mean,
    movingMean Me,mean,8,2
    expect 8,~!Me,mean!~

    movingMean Me,mean,0,2
    expect 4,~!Me,mean!~

    movingMean Me,mean,0,2
    expect 0,~!Me,mean!~

    movingMean Me,mean,0,2
    expect 0,~!Me,mean!~

    movingMean Me,mean,0,2
    expect 0,~!Me,mean!~
```
