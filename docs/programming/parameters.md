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

## In the now!

There are now three ways of doing this.

* The simple way `parameters name,thing`. This is the really really simple and easy to read way of doing it, but doesn't give any flexibility for validation.
* Flat JSON `parameters {"name":"noName","thing":"brick"}`. This is still pretty simple, but allows you to specify defaults.
* **Nested JSON** `parameters {"name":{"default":"noName","minLengthAllowed":"3","maxLength":"40"},"thing":{"default":"brick","maxLength":"40"}}`. This method allows you validate date and take action based on the result. It can also automatically fix a few things like if the string is 45 characters, but you specified maxLength to be 40, it will truncate off the remainder.

### The simple way

Use this for really basic, no frills parameter parsing. You should at least consider Flat JSON below.

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

### Flat JSON

Use this when you just want to set defaults for your parameters.

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

### Nested JSON

Use this when you want to set detaults or add validation. If you only use one method, this is the one to use. It is the one that is likely to stick around for a while.

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




