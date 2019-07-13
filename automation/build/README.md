# Build tools

This is a first go at some build automation. This will get replaced, but it is a starting point.

*NOTE* that the lastHash, and lastWhen in .tag are used for tracking when the point in the tag should be incremented. They are not for saying what the current hash should be, because other wise we would have an infinite loop. Ie we update the hash to match git, but that changes git, giving us a new hash.

## Using/deploying it

TODO write this.

## Creating your own app

TODO write this.

## Building it

To build the latest code, run

```bash
achelctl buildBuild
```

Then when you're happy with it, you can run

```bash
achelctl buildPush
```
