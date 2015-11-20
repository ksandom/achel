# massSet

TODO the contends of this document should be moved to the appropriate document so that it can easily be found.

## What is it

The intended purpose is set deep memory nesting while keeping code concise and easy to read. 

I suspect it will end up getting used for other purposes I haven't yet thought of.

## How to use it

    massSet ["Example,a,really,long,address"],
    	set ~!Me,stuff!~,1,cat
    	set ~!Me,stuff!~,2,dog
    	set ~!Me,stuff!~,3,goat

This will set `Example,a,really,long,address,1` to `cat`, `Example,a,really,long,address,2` to `dog`, and `Example,a,really,long,address,3` to `goat`.
