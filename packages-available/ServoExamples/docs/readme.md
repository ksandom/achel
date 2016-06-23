# ServoExamples

This is a collection of examples to kickstart you into having things move.


## Examples

### getLocalStats.sh

At the moment the servo faucet defaults to a range 0<=x<=100. Therefore we need to adapt values to be in that range before sending them over. It does gracefully handle out of bounds values, but it's unlikely to be the behavior you want. Ie the servo is likly to spend almost all of its time at one end of the range.

Running the origin command looks like this

    $ ./getLocalStats.sh {"cpu":"8", "cores":"4", "memory":"18"}
    {"cpu":"8", "cores":"4", "memory":"18"}
    {"cpu":"8", "cores":"4", "memory":"18"}
    {"cpu":"8", "cores":"4", "memory":"18"}
    {"cpu":"7", "cores":"4", "memory":"18"}
    {"cpu":"7", "cores":"4", "memory":"18"}
    {"cpu":"7", "cores":"4", "memory":"18"}
    {"cpu":"7", "cores":"4", "memory":"18"}
    {"cpu":"7", "cores":"4", "memory":"18"}
    {"cpu":"6", "cores":"4", "memory":"18"}

You can see it run like this

    $ achel --tui --playWithServos
    [debug1] tui-3: verbosity: Incremented verbosity to "Information" (1)
    [debug1] showNesting-7: Current nesting level is 7

Then type this

    servoExample

You can get it into your program like this

    createNullFaucet null
    createLocalStatsFaucet stats
    createDebugFaucet debug

    # The null faucet gives the debug faucet a prerequisite, and thus the debug faucet gets called.
    createPipe debug,null
    
    # This sends everything to the debug faucet. It will be displayed if verbosity has been incremented to at least 1.
    createPipe stats,debug,~*,1
    
    
    # This sends % CPU to the first servo.
    createPipe stats,servo,cpu,0
    
    # This sends % memory to the second servo.
    createPipe stats,servo,memory,1

