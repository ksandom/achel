# Scope

Achel now supports variable scoping.

Right now there are 3 different scoping models. Everything else is effectively Global and not auto-cleaned.

## Local

Local scope makes a variable only available within the macro you are in. Macros earlier and later in the stack can interacti with a variable of the same name, but they will be different variables. This is automatically cleaned up when you exit the scope.

You can specify Local scope by setting the category to Local. Eg

    set Local,test1,yay!
    debug 1,And I said "~!Local,test1!~"
    
    # Outputs: And I said "yay!"

If you want to make a variable available to the calling scope, you can use `makeLocalAvailable` any time after the last time the variable is written to. Typically this would be at the end of the macro. Eg

    set Local,test1,yay!
    debug 1,And I said "~!Local,test1!~"
    makeLocalAvailable test1

## Isolated (DEPRECATED)

**It is not recommended to use Isolated**

Isolated was an interesting idea in that the code behind it is very basic, but using it is very un-intuitive and so it will probably be removed very soon.

For Isolated variables, a change in nesting level is a change of scope. Eg

    set Isolated,test1,blah
    if 1,==,1,
    	set Isolated,test1,wheee
    
    debug 1,~!Isolated,test1!~ == blah
    
    # Outputs: blah == blah

## Me

*WARNING: As you'll see below; Me has a definite, well defined scope. However its nature is easy to lead to bugs if you are using it in the wrong situation. Eg If you have two Macros unintentionally both using the same variable name, they will step on each other's toes. You will almost always be better off with using **Local** instead.*

Me's scope starts at the current stack nesting level when it is first written, and remains in scope for nesting level after that regardless of which macro it is accessed from. But not before the nesting level for which it was first written. 

    if 1,==,1,
    	set Me,test1,wibble
    	if 1,==,1,
    		set Me,test1,wobble
    		debug 1,value=~!Me,test1!~
    		# Outputs: value=wobble
    		
    	debug 1,value=~!Me,test1!~
    	# Outputs: value=wobble
    	
    debug 1,value=~!Me,test1!~
    # Outputs: value=

There are two ways you can make the variable accessible to the previous scope. The first is to write to the variable from the scope you want to read it from. Eg:

    set Me,test1,
    if 1,==,1,
    	set Me,test1,wibble
    	if 1,==,1,
    		set Me,test1,wobble
    		debug 1,value=~!Me,test1!~
    		# Outputs: value=wobble
    		
    	debug 1,value=~!Me,test1!~
    	# Outputs: value=wobble
    	
    debug 1,value=~!Me,test1!~
    # Outputs: value=wobble

This could lead to some difficult bugs. (It did!)

The better way is to use `makeMeAvailable`

    if 1,==,1,
    	set Me,test1,wibble
    	if 1,==,1,
    		set Me,test1,wobble
    		debug 1,value=~!Me,test1!~
    		# Outputs: value=wobble
    		
    	debug 1,value=~!Me,test1!~
    	# Outputs: value=wobble
    	
    	makeMeAvailable test1
    	
    debug 1,value=~!Me,test1!~
    # Outputs: value=wobble


