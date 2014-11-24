# Recievinng parameters in macros.

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

This will stick around for a short time.

It was nice and simple when your macro is small and simple. However it quickly became confusing in more complicated macros. 

## In the now!

There are now three ways of doing this.

* The simple way `parameters name,thing`. This is the really really simple and easy to read way of doing it, but doesn't give any flexibility for validation.
* Flat JSON `parameters {"name":"noName","thing":"brick"}`. This is still pretty simple, but allows you to specify default values.
* **Nested JSON** `parameters {"name":{"default":"noName","minLengthAllowed":"3","maxLength":"40"},"thing":{"default":"brick","maxLength":"40"}}`. This method allows you validate data and take action based on the result. It can also automatically fix a few things like if the string is 45 characters, but you specified maxLength to be 40, it will truncate off the remainder.

### The simple way

Use this for really basic, no frills parameter parsing. You should at least consider Flat JSON below.

example.macro

    This is an example. --example=name,thing,quantity where name is your name, thing is something you see and quanitity is how many you see. ~ example
    parameters name,thing,quantity
    
    debug 1,Hello ~!Me,name!~. What does the ~!Me,thing!~ look like? You have selected to have ~!Me,quantity!~ of them.

So calling it would look like this

    $ achel -v --example=Kevin,chair,6
    [debug1]: Hello Kevin. What does the chair look like? You have selected to have 6 of them.

There are two advantages to doing this

* Easier to read, especially as the code grows.
* The variables are tidied away when they are no longer needed. So you don't need to worry about this yourself.

### Flat JSON

Use this when you just want to set defaults for your parameters.

example.macro

    # This is an example. --example=name,thing,quantity where name is your name, thing is something you see and quanitity is how many you see. ~ example
    parameters {"name":"noName","thing":"brick","quantity":"0"}
    
    debug 1,Hello ~!Me,name!~. What does the ~!Me,thing!~ look like? You have selected to have ~!Me,quantity!~ of them.

So calling it would look like this

    $ achel -v --example=Kevin,chair
    [debug1]: Hello Kevin. What does the chair look like? You have selected to have 0 of them.
    
    $ achel -v --example=,chain,5
    [debug1]: Hello noName. What does the chair look like? You have selected to have 5 of them.

This has the advantages of the simple way, plus now you can specify defaults for if a parameter is missing.

### Nested JSON

Use this when you want to set detaults, add adaptions or add validation. If you only use one method, this is the one to use. It is the one that is likely to stick around for a while.

example.macro

    # This is an example. --example=name,thing,quantity where name is your name, thing is something you see and quanitity is how many you see. ~ example
    parameters {"name":{"required":1},"thing":"brick","quantity":{"type":"number","min":"0","max":"10","default":"1"}}
    
    if ~!Isolated,passed!~,==,true,
    	debug 1,Hello ~!Me,name!~. What does the ~!Me,thing!~ look like? You have selected to have ~!Me,quantity!~ of them.
    else
    	debug 1,Missing parameters :(

So calling it would look like this

    $ achel -v --example=Kevin,chair
    debug 1,Hello Kevin. What does the chain look like? You have selected to have 1 of them.
    
    $ achel -v --example=Kevin,chair,4
    debug 1,Hello Kevin. What does the chain look like? You have selected to have 4 of them.
    
    $ achel -v --example=,chair,4
    debug 1,Missing parameters :(

Now you can specify defaults for if a parameter is missing.

## Taking advantage of the Nested JSON method

This is where the future and power lies. So let's understand it a little better.

### Basics

Generally you'll have a parameters line that looks like this

    parameters {"name":{"default":"blah"}}

This line sets the default to blah. But you don't actually have to specify any more parameters

    parameters {"name":{}}

TODO Test this!!!

But you may want to specify lots of things

TODO write this

### Data types

### Other parameters and what they do


