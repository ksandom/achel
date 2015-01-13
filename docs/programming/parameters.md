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

## In the now! - The three amigos

There are now three ways of doing this.

* The simple way `parameters name,thing`. This is the really really simple and easy to read way of doing it, but doesn't give any flexibility for validation.
* Flat JSON `parameters {"name":"noName","thing":"brick"}`. This is still pretty simple, and allows you to specify default values.
* **Nested JSON** `parameters {"name":{"default":"noName","minLengthAllowed":"3","maxLength":"40"},"thing":{"default":"brick","maxLength":"40"}}`. This method allows you validate data and take action based on the result. It can also automatically fix a few things like if the string is 45 characters, but you specified maxLength to be 40, it will truncate off the remainder.

### The simple way

Use this for really basic, no frills parameter parsing. You should at least consider Flat JSON below.

example.macro

    This is an example. --example=name,thing,quantity where name is your name, thing is something you see and quanitity is how many you see. ~ example
    parameters name,thing,quantity
    
    debug 1,Hello ~!Local,name!~. What does the ~!Local,thing!~ look like? You have selected to have ~!Local,quantity!~ of them.

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
    
    debug 1,Hello ~!Local,name!~. What does the ~!Local,thing!~ look like? You have selected to have ~!Local,quantity!~ of them.

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
    	debug 1,Hello ~!Local,name!~. What does the ~!Local,thing!~ look like? You have selected to have ~!Local,quantity!~ of them.
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

Generally you'll have a `parameters` line that looks like this

    parameters {"name":{"default":"blah"}}

This line sets the default to blah. But you don't actually have to specify any more parameters

    parameters {"name":{}}

Here `name` is assumed to be a string, has no default, no validation or manipulations. Everything that is passed to it will go straight through.

But you may want to specify lots of things

    parameters {"name":{"default":"blah","minLength":"3",""maxLength:"40"}}

In this case, the name will be truncated to 40 characters if it gets longer than 40 characters. And will be appended with spaces if it is shorter than 3.

### Data types and their parameters

All data types take (or at least accept)

* default - What value to set in the absense of a value.

#### String

* minLength - If the length of the string is less than this value, it will be padded with spaces at the end until it reaches this length.
* maxLength - If the length of the string is more than this value, it will be truncated to this length.
* minLengthAllowed - If the length of the string is less than this value, the parameter will fail and a message will be returned accordingly. `maxLength` and `minLength` can still be applied.
* maxLengthAllowed - If the length of the string is more than this value, the parameter will fail and a message will be returned accordingly. `maxLength` and `minLength` can still be applied.

#### Number

* min - If the number is less than this value, it will be set to it.
* max - If the number is more than this value, it will be set to it.
* minAllowed - If the number is less than this value, the parameter will fail and a message will be returned accordingly. `min` and `max` can still be used accordingly.
* maxAllowed - If the number is more than this value, the parameter will fail and a message will be returned accordingly. `min` and `max` can still be used accordingly.

#### Boolean

*no extra parameters for now*

### Getting the knowledge!

#### Parameter values

So we have all this information about our parameters. How do we get it?

If you define `name` via one of these methods

    parameters name

    parameters {"name":"blah"}

    parameters {"name":{"default":"blah"}}

You can access it in the form `~!Local,parameterName!~`. Eg

    debug 1,You name is ~!Local,name!~.


Notice how the `Local` category is used. This is so that it will only be visible within the current scope. At the moment this equates to the current macro. See [scope.md](scope.md) for more information.

#### Whether the parameter tests passed

Let's say we have a test that fails that looks like this

    parameters {"name":{"minLengthAllowed":"3"}}

And no characters were passed to it. How do we test for it?

You can use the `~!Isolated,pass!~` variable like so

    if ~!Isolated,pass!~,==,true,
    	debug 1,Yay!
    else
    	debug 1,Boo!

**NOTE** It's really important to understand that an `Isolated` variable will not get inherited in either direction as your program goes up and down the stack. Therefore **the following will not work**, but the previous example will.

    if 1,==,1,
    	if ~!Isolated,pass!~,==,true,
    		debug 1,Yay!
    	else
    		debug 1,Boo!

The reason for this is that we don't want the result to be interferred with as we move around the stack.
