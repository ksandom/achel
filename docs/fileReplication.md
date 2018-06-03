# File replication

provides a way to distrubute config to your users in quick way without them needing to manually update.

A really good example of this would be your company's hosts definitions along with all the meta data. Ie update it once, and everyone has the latest definitions pretty much straight away.

For the majority of situations, the simple happy day scenario should work just fine.

## Simple happy day scenario.

This example sets up mass to replicate to other users. It assumes you already have host definitions and that you are using Dropbox. If you'd like to use some other replication provider, please see using other replication providers below.

### On the first computer

Find the file with the definitions you want to replicate.

    $ achelctl fileRepFileList

Tell it which files we want to replicate

    $ achelctl fileRepFileAdd data/1LayerHosts/manual.json
    fileRepAddFile: "/home/ksandom/Dropbox/Achel/data/1LayerHosts/manual.json" Already didn't exist. Copying.

Now go to dropbox and share the `Achel` folder within the Dropbox folder with whoever you want to be able to recieve the updates.

### On subsequent computers

Tell achel that we want to replicate this file.

    $ achelctl fileRepFileAdd data/1LayerHosts/manual.json

*This assumes that the file has been shared with this person.*

## Using other replication providers.

Since the majority of people I've been working with over the last while have been using Dropbox as the first choice, I have made that the default. But it's really easy to change that. Here's how you do it.

The important parts are

 * Add the provider
 * Set it as default if that's what you want.

### First, let's see what we have

    $ achelctl fileRepShowConfig
    
      Providers:
        dropbox: /home/ksandom/Dropbox/Achel
      defaultProvider: dropbox

This tells us that we have one provider, called `dropbox`, which is sitting in `/home/ksandom/Dropbox/Achel` and that it is the default provider. Notice that `/home/ksandom/Dropbox/Achel` actually represents a folder (`Achel`) inside the `Dropbox` home. This will be described further below, but is basically for allowing you a graceful way to share different data with different groups of people by representing them as different providers, even if they are using the same replication account. Ie you might share `~/Dropbox/Achel` with your friends, but `~/Dropbox/Achel-work` with your workmates.

### Adding a different provider

    $ achelctl fileRepProviderAdd googleDrive ~/gDrive

This creates a provider that looks like this (using `achelctl fileRepShowConfig`)

        googleDrive: /home/ksandom/gDrive/Achel

### Choosing a different sub folder

Simply specify the subfolder you want as the next parameter

    $ achelctl fileRepProviderAdd googleDrive ~/gDrive Achel-work

This creates a provider that looks like this (using `achelctl fileRepShowConfig`)

        googleDrive: /home/ksandom/gDrive/Achel-work

Here I've amended the config with this method, but it would work equally well doing it from scratch.

### Setting the default provider

When specifying a file that you want to replicate, you can specify which provider you want to use. If you don't, the default one will be used.

Find which provider you want to make default with `achelctl fileRepShowConfig`.

    $ achelctl fileRepDefaultProvider googleDrive

### Removing a provider

    $ achelctl fileRepProviderRemove googleDrive
    autoSetDefaultProvider: "dropbox" has been assigned as the default provider. Use fileRepDefaultProvider to change it.
