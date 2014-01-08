# Credentials

For managing credentials for things like APIs.

This is very basic and a first step in the direction of keeping credentials secure.

Credentials data is stored in ACHELHOME/credentials .

## Using it

* The `Credentials` and `Data` packages need to included via [repoParms](https://github.com/ksandom/achel/blob/master/docs/programming/creatingARepositoryWithProfiles.md#use-repoparmdefinepackages-to-create-a-profile). If BASE is included, it should be fine.
* Add some credentials in to it using `saveCredentials`.
* Later an application can retrieve credentials for automatic usage.

## A worked example

First lets save saves some credentials

    # Save a password for later use. --pwsSavePassword=password where password is the password you want to save. ~ password,shower,example
    
    saveCredentials PasswordShower,dev,hardCodedTwatUser,~!Global,pwsSavePassword!~

Then the user runs the application/macro which looks like this

    # Show the credentials for this application. --showPassWord takes no parameters, but a password can be set using --pwsSavePassword ~ password,shower,example
    
    # NOTE the destroyPassWordCallBack. We need to create that as well.
    loadCredentials PasswordShower,dev,showPassWordCallBack

This is what showPassWordCallBack.macro looks like

    # The call back for showPassWord. --showPassWordCallBack=username,password ~ password,shower,hidden,example
    
    debug 1,showPassWordCallBack: username=~!Global,showPassWordCallBack-0!~ password=~!Global,showPassWordCallBack-0!~

NOTE that this example demonstraghts how anyone could write a macro to harvest the credentials for any application, which is not ideal. This will be fixed and documented here very soon.

## Another worked example

First lets save saves some credentials

    # Save a password for later use. --pwdSavePassword=password where password is the password you want to save. ~ password,destroy,example
    
    saveCredentials PasswordDestrOOOYer,dev,hardCodedTwatUser,~!Global,pwdSavePassword!~

Then the user runs the application/macro which looks like this

    # Destroys passwords because we can. --destroyPassWord takes no parameters, but a password can be set (for destroying) using --pwdSavePassword ~ password,destroy,example
    
    # NOTE the destroyPassWordCallBack. We need to create that as well.
    loadCredentials PasswordDestrOOOYer,dev,destroyPassWordCallBack,dev

This is what destroyPassWordCallBack.macro looks like

    # Does the actual work of destroying the password. --destroyPassWordCallBack=account,username,password ~ password,destroy,hidden,example
    
    debug 1,destroyPassWordCallBack: Destroying password for user ~!Global,destroyPassWordCallBack-1!~ on account ~!Global,destroyPassWordCallBack-0!~.
    
    # Test if we have a password.
    if ~!Global,destroyPassWordCallBack-2!~,==,,
    	# No password.
    	debug 1,destroyPassWordCallBack: Wait a second.... There is no password! Wut ya pullin bruv?
    else
    	# We have one, let's proceed. First let's kill the password itself
    	unset Global,destroyPassWordCallBack-2
    	
    	# Now let's kill the un-split parameter set. Note that this will not kill the other parameters even though they will no longer be visible in the un-split set
    	unset Global,destroyPassWordCallBack
    	
    	if ~!Global,destroyPassWordCallBack-2!~,==,,
    		debug 1,destroyPassWordCallBack: I know nah-THING!
    	else
    		debug 1,destroyPassWordCallBack: Something has failed. Time to debug.
