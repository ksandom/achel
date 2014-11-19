# Sending parameters to macros.

This documents new functionality. Once it has solidified, it will be integrated into creatingAMacro.md

# Before and after!

## In the past

Parameters would get to a macro like so.

example.macro

    # This is an example. --example=name where name is your name. ~ example
    
    debug 1,Hello ~!Global,example-0!~.

So calling it would look like this

    $ achel -v --example=Kevin
    [debug1]: Hello Kevin.

Nice and simple when your macro is small and simple. However it quickly became confusing in more complicated macros.

## In the now! - simple way

Desired parameters are named as follows.

example.macro

    # This is an example. --example=name,thing where name is your name and thing is something you see. ~ example
    parameters name,thing
    
    debug 1,Hello ~!Me,name!~. What does the ~!Me,thing!~ look like?

So calling it would look like this

    $ achel -v --example=Kevin,chain
    [debug1]: Hello Kevin. What does the chair look like?

There are two advantages to doing this

* Easier to read, especially as the code grows.
* The variables are tidied away when they are no longer needed. So you don't need to worry about this yourself.

## In the now! - better way

Desired parameters are named as follows.

example.macro

    # This is an example. --example=name,thing where name is your name and thing is something you see. ~ example
    parameters {"name":"noName","thing":"brick"}
    
    debug 1,Hello ~!Me,name!~. What does the ~!Me,thing!~ look like?

So calling it would look like this

    $ achel -v --example=Kevin,chain
    [debug1]: Hello Kevin. What does the chair look like?
    
    $ achel -v --example=,chain
    [debug1]: Hello noName. What does the chair look like?

Now you can specify defaults for if a parameter is missing.

## In the now! - when you want to be precise

Desired parameters are named as follows.

example.macro

    # This is an example. --example=name,thing where name is your name and thing is something you see. ~ example
    parameters {"name":{"required":1},"thing":"brick","quantity":{"type":"number","min":"0","max":"10","default":"1"}}
    
    if ~!Isolated,passed!~,==,true,
    	debug 1,Hello ~!Me,name!~. What does the ~!Me,thing!~ look like? You have selected to have ~!Me,quantity!~ of them.
    else
    	debug 1,Missing parameters :(

So calling it would look like this

    $ achel -v --example=Kevin,chain
    debug 1,Hello Kevin. What does the chain look like? You have selected to have 1 of them.
    
    $ achel -v --example=Kevin,chain,4
    debug 1,Hello Kevin. What does the chain look like? You have selected to have 4 of them.
    
    $ achel -v --example=,chain,4
    debug 1,Missing parameters :(

Now you can specify defaults for if a parameter is missing.




