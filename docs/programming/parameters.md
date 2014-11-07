# Sending parameters to macros.

This documents new functionality. Once it has solidified, it will be integrated into creatingAMacro.md

# Before and after!

## In the past

Parameters would get to a macro like so.

example.macro

    # This is an example. --example=name where name is your name. ~ example
    
    debug 1,Hello ~!Global,example-0!~.

So calling it would look like this

    $ achel --example=Kevin
    [debug1]: Hello Kevin.

Nice and simple when your macro is small and simple. However it quickly became confusing in more complicated macros.

## In the now!

Desired parameters are named as follows.

example.macro

    # This is an example. --example=name,thing where name is your name and thing is something you see. ~ example
    parameters name,thing
    
    debug 1,Hello ~!Me,name!~. What does the ~!Me,thing!~ look like?

So calling it would look like this

    $ achel --example=Kevin,chain
    [debug1]: Hello Kevin. What does the chair look like?

There are two advantages to doing this

* Easier to read, especially as the code grows.
* The variables are tidied away when they are no longer needed. So you don't need to worry about this yourself.
