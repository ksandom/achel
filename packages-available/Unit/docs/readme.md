# Unit

Provides a unit testing framework.

## Using it

* Make sure `Unit` is included via [repoParms](https://github.com/ksandom/achel/blob/master/docs/programming/creatingARepositoryWithProfiles.md#use-repoparmdefinepackages-to-create-a-profile). If BASE is included, it should be fine.
* Create tests inside a macro with `unitTest` in the tags on the first line.
* Run the tests like so `achel --unitTests`

## A worked example - normal feature

Let's define some tests.

    # A unit test example ~ unitTest,example
    
    defineTest Unit example test1,
    	if 1,==,1,
    		passTest Yay
    	else
    		failTest If this happens something has gone very wrong with the Condition module.
    
    defineTest Unit example test2,
    	if 1,==,1,
    		passTest Wheee
    	else
    		failTest Hmmmm. Why you no like me. PS: Check the Condition module.

    defineTest Unit example duplication,
    	failTest First entry looses.
    	warnTest Anything that is not the last entry looses.
    	passTest Last entry wins.

* Notice `unitTest` in the tags. This is what tells the framework that this macro needs to be run.
* There can be multiple tests in a singe macro. They should be logically related.
* Each test is indented inside the `defineTest` feature which takes a description followed by a `,`.
* You can use `passTest`, `warnTest` or `failTest`. Each with a description of why it failed.
 * You can have many results in one test. Eg you can fail in many ways. This helps you to have a useful comment as to why it failed. *Do this.*
 * The last one in the test wins.

## A worked example - faucet

This used to be hard. Now it's easy

    defineTest IPC - prereq - procFaucet (replacement),
    	testFaucets
    		createProcFaucet proc,echo Hi!
    		createPipe proc,.
    	
    	expect Hi!,~!Test,default,0!~

* Define a test as you would for a normal feature.
* Nest the thing you want to test inside of `testFaucets`.
* Create a pipe from your stuff to `.` and any data recieved will be dumped to ~!Test,channel,messageNumber!~.
* You can then test the results with the expect statement just like you would for normal features.