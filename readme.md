*status:* Active.

# Achel
is a programming langue specilising in robotics and small data sets. I'm building it to scratch a very specific itch and I doubt the full picture it will be useful to many people. But if you are interested. Feel free to jump in.

# Requirements

* PHP
* Bash

# Important updates

* As a general rule, when ever you update, you should re-run install.sh to apply any structural changes as the internals are regularly being refactored.
* 2017-03-21: Caching is now active by default. For any questions you have, like how it works and how to manage it, see the [cache documentation](https://github.com/ksandom/achel/tree/master/packages-available/Cache/docs/readme.md).
* 2017-03-21: If you strike a bug, you can raise it [here](https://github.com/ksandom/achel/issues). Make sure you include the output from `achelctl bugReportCreate`.

# Install

If you are having troubles getting this installed, see "Docker" below, which is slightly less fully-featured, but makes it really easy to get kickstarted.

## Easy way

    curl https://raw.githubusercontent.com/ksandom/achel/master/supplimentary/misc/webInstall | bash

## Traditional way

* Clone the repository
* Use install.sh.

Eg

    git clone git@github.com:ksandom/achel.git
    cd achel
    ./install.sh

[More info](http://github.com/ksandom/achel/blob/master/docs/install.md)

## Docker

Get into the directory where you place bins (like bash scripts) and then run the following command.

    export IMAGE=kjsandom/achel; curl https://raw.githubusercontent.com/ksandom/achel/master/automation/dockerExternal/dumpBins | bash

This can be used for any Achel based docker container. It pulls the docker container, and then extracts the wrappers for each of the commands provided by that container.

## Docker for careful people (a good habbit)

    curl https://raw.githubusercontent.com/ksandom/achel/master/automation/dockerExternal/dumpBins > dumpBins
    cat dumpBins # sanity check
    export IMAGE=kjsandom/achel; cat dumpBins | bash

Exactly the same as the "Docker" section above, but gives you a chance to sanity check what this code you just downloaded actually does.

[More info](http://github.com/ksandom/achel/blob/master/docs/install.md)

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
