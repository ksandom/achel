# File

Do file system operations. Right now, that just means list files.

## Using it

* Make sure `File` is included via [repoParms](https://github.com/ksandom/achel/blob/master/docs/programming/creatingARepositoryWithProfiles.md#use-repoparmdefinepackages-to-create-a-profile). If BASE is included, it should be fine.
* Perform options as needed.

## A worked example

    $ mkdir -p /tmp/a/{x,y,z}
    $ achel --listFiles=/tmp/a
    
      x: /tmp/a/x
      y: /tmp/a/y
      z: /tmp/a/z

Here we mame the directory structure and then list it out. The formatting of the listing is

 * the key is the filename.
 * the value is the full path to the file including the filename.
