# Exec

Execute stuff. If the results are json, the result will be returned directly in the resultSet. If it's a string, it will be a single entry in the resultSet.

## A worked example using a macro

First, let's here's a command that returns some json

    achel --set=UnitTmp,a,1 --set=UnitTmp,b,2 --getCategory=UnitTmp --json

And it looks like this

    $ achel --set=UnitTmp,a,1 --set=UnitTmp,b,2 --getCategory=UnitTmp --json
    {"a":"1","b":"2"}

So let's create a macro

    # Get data from an external source ~ example
    exExec achel --set=UnitTmp,a,1 --set=UnitTmp,b,2 --getCategory=UnitTmp --json

And running it looks like this

      a: 1
      b: 2

## An example from the command line

I actually tested the above example from the command line. It looked like this

    $ achel --exExec="achel --set=UnitTmp,a,1 --set=UnitTmp,b,2 --getCategory=UnitTmp --json"
    
      a: 1
      b: 2

