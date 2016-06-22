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


