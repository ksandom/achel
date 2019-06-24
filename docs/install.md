# Install

First off, thanks for installing Achel. I hope you like it.

For most uses, the easy way will be sufficient. This document will be of particur interest to

* Systems administrators who want to make the installation available to all users.
* Developers that want to
 * write their own code for in-house or open source projects.
 * want to write make modifications to Achel or Achel applications. More info about how to share this coming soon. (pull request)

## Installing it

### Easy way

    curl https://raw.github.com/ksandom/achel/master/supplimentary/misc/webInstall | bash

### Traditional way

* Clone the repository
* Use install.sh.

Eg

    git clone git@github.com:ksandom/achel.git
    cd achel
    ./install.sh

[More info](/ksandom/achel/blob/docs/install.md).

## Docker

Get into the directory where you place bins (like bash scripts) and then run the following command.

    export CONTAINER=kjsandom/achel; curl https://raw.githubusercontent.com/ksandom/achel/master/automation/dockerExternal/dumpBins | bash

This can be used for any Achel based docker container. It pulls the docker container, and then extracts the wrappers for each of the commands provided by that container.

If you would like multiple installations for testing, you can do so like this

    export CONTAINER=kjsandom/achel; NAMESPACE=thing1; curl https://raw.githubusercontent.com/ksandom/achel/master/automation/dockerExternal/dumpBins | bash
    export CONTAINER=kjsandom/achel; NAMESPACE=thing2; curl https://raw.githubusercontent.com/ksandom/achel/master/automation/dockerExternal/dumpBins | bash
    export CONTAINER=kjsandom/achel; NAMESPACE=thing3; curl https://raw.githubusercontent.com/ksandom/achel/master/automation/dockerExternal/dumpBins | bash

## Docker for careful people (a good habbit)

    curl https://raw.githubusercontent.com/ksandom/achel/master/automation/dockerExternal/dumpBins > dumpBins
    cat dumpBins # sanity check
    export CONTAINER=kjsandom/achel; cat dumpBins | bash

Exactly the same as the "Docker" section above, but gives you a chance to sanity check what this code you just downloaded actually does.

## Other install variations

There's a fair bit that you can customise with the install. The best way for you to find out how to do this is to follow the traditional way, and when you get to the `./install.sh` step, run `./install.sh --help` instead and you will recieve full documentation.

Note: Running the install as root *will allow you to install Achel system wide so that more than just the current user can use it*. This is likely what you want in many situations.

## Installing Achel applications

### Easy way 1

    export extraSrc="git@github.com:ksandom/doneIt.git"; curl https://raw.github.com/ksandom/achel/master/supplimentary/misc/webInstall | bash

In this example we are installing [doneIt](https://github.com/ksandom/doneIt). Simply set `extraSrc` to the *clone URL* that github provides. In this case `git@github.com:ksandom/doneIt.git`. It could also be in the form `https://github.com/ksandom/doneIt.git` or any other form that git recognises.

The advantage of this way is that if Achel isn't installed, it will get installed and everything will be set up cleanly.

### Easy way 2

* Use `achelctl repoInstall` to install the app directly from the git url.

eg

    $ achelctl repoInstall https://github.com/ksandom/doneIt.git

The advantage of this is that it's easier for you to derive if you already have Achel already installed.

### Traditional way (for developers)

* Get into the directory where you clone your repositories.
* Clone the repository.
* Use `achelctl repoInstall` to install directly from the cloned repository.

eg

    $ cd ~/repos
    $ git clone https://github.com/ksandom/doneIt.git
    $ achelctl repoInstall ~/repos/doneIt

The advantage of this is that you can develop within your own directory structure.

*Never develop within the Achel home directory.* This is because it's far too easy to make mistakes when doing thorough testing of the installation of your application. Eg is the default data set being created correctly when being installled for the first time.

## Programming in Achel

Please start [here](https://github.com/ksandom/achel/tree/master/docs/programming).
