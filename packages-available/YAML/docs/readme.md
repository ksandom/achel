# CSV

Imports/Exports YAML.

## Using it

* Make sure `YAML` is included via [repoParms](https://github.com/ksandom/achel/blob/master/docs/programming/creatingARepositoryWithProfiles.md#use-repoparmdefinepackages-to-create-a-profile). If BASE is included, it should be fine.
* Perform options as needed.

### On ubuntu

    sudo apt-get install php5-dev libyaml-dev
    sudo pecl install yaml

*Snip*

    Build process completed successfully
    Installing '/usr/lib/php5/20121212/yaml.so'
    install ok: channel://pecl.php.net/yaml-1.1.1
    configuration option "php_ini" is not set to php.ini location
    You should add "extension=yaml.so" to php.ini

Create `/etc/php5/mods-available/yaml.ini`

    extension=yaml.so

Create a symlink to it from inside `/etc/php5/cli/conf.d`

    root@zelappy:/etc/php5/cli/conf.d# ln -s ../../mods-available/yaml.ini 20-yaml.ini

## A worked example



    $ achel --setNested=A,b,c,d,1 --setNested=A,b,c,e,2 --setNested=A,b,c,f,2 --setNested=A,b,g,h,3 --retrieveResults=A,b --toYAML --singleString
    
    ---
    c:
      d: "1"
      e: "2"
      f: "2"
    g:
      h: "3"
    ...


