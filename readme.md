# Achel
is a programming langue specilising in small data sets and _______ (the ___ will be released soon). I'm building it to scratch a very specific itch and I doubt the full picture it will be useful to many people. In fact, most of it isn't in the public repo yet. However the foundations, which are already released, are really useful for tools like mass which was written as a Sysadmin tool.

This is a tiny percentage of the final vision, so there's a lot more to come!

# Requirements

* PHP
* Bash

# Important updates

* As a general rule, when ever you update, you should re-run install.sh to apply any structural changes as the internals are regularly being refactored.
* The method for adding unknown terminal types has changed. Documentation for this will be in packages-available/Terminal/docs

# Install

## Easy way

    curl https://raw.github.com/ksandom/achel/master/supplimentary/misc/webInstall | bash

## Traditional way

* Clone the repository
* Use install.sh.

Eg

    git clone git@github.com:ksandom/achel.git
    cd achel
    ./install.sh

[More info](mass/tree/master/docs/install.md)


# Contributing

* If there's functionality you want that doesn't exist yet, take a look at the "creatingA" series in the [documentation](tree/master/docs). It would be lovely if you can contribute back.
* There are `TODO`'s floating around the documentation that need to be filled in. Filling these in would be very helpful.
* There are `# TODO`'s floating around in the code. There are going to be a few which I'll reserve for me. Typically I only do this if I've planned something else based on how that thing gets done.

The bottom line is, I wrote this tool because it's useful to me. If it's useful to you and you have something to contribute, it would be lovely for you to put it forward.

# History
This particular implementation of Achel has been separated out from my most recent version of mass, for which I needed the ability to quickly write macros to extend the functionality. It's the most recent in a long line of prototypes since 2001. It has two purposes

* The foundation of many of my comming projects.
* An intellectual playground.

The best is yet to come!
