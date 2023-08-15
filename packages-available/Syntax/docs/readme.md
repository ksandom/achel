# Syntax

## Function

```
# An example macro. ~ macro

function doSomething,
    parameters name,description

    set Example,~!Local,name!~,~!Local,description!~

# Example,thing1 will be set to "The first example".
doSomething thing1,The first example

# Example,thing2 will be set to "The second example".
doSomething thing2,The second example
```

`function` declarations must appear at the beginning of the macro.
