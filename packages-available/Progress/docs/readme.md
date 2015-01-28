# Progress

Display progress of a long running task to a user. Use this when you have to iterate over data that will take a long time to process. This could be anything from a large number of fast values to a small number of slow values.

## Using it

This is included in the Achel repository. Make sure that is included.
It relies on the Generate and Maths packages.

## How it fits together

* `displayProgress` is called inside of the long running loop. This takes care of
 * keeping track of progress.
 * displaying the progress to the user.
* `finishProgress` is called immediately after the loop. This takes care of 
 * returning the output to normal.

## A worked example

Without showing the progress

    getSomeData
    loop
    	doSomeHeavyLifting

Showing the progress

    getSomeData
    loop
    	displayProgress ~!Local,progress!~
    	doSomeHeavyLifting
    finishProgress

The two extra lines are `displayProgress` and `finishProgress`. 

Note that for the `displayProgress` line, we actually have to pass `~!Local,progress!~` to it so that it knows where it's up to. This requirement is likely to disappear in the future.
