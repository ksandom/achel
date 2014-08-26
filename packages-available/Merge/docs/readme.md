# Merge

Merge multiple resultSets together.

## Using it

* Make sure `Merge` is included via [repoParms](https://github.com/ksandom/achel/blob/master/docs/programming/creatingARepositoryWithProfiles.md#use-repoparmdefinepackages-to-create-a-profile). If BASE is included, it should be fine.
* Perform options as needed.

## A worked example

Let's create some data sets

    setNested Example,animals,dog,name,dog
    setNested Example,animals,dog,legs,4
    setNested Example,animals,dog,noise,woof
    
    setNested Example,animals,cat,name,cat
    setNested Example,animals,cat,legs,4
    setNested Example,animals,cat,noise,meow
    
    setNested Example,animals,bird,name,bird
    setNested Example,animals,bird,legs,2
    setNested Example,animals,bird,noise,chirp
    
    setNested Example,vehicles,dog,name,dog
    setNested Example,vehicles,dog,wheels,4
    setNested Example,vehicles,dog,legs,until the fuel runs out
    setNested Example,vehicles,dog,noise,brrrrrr
    setNested Example,vehicles,dog,comment,runs like a
    
    setNested Example,vehicles,bike,name,fast
    setNested Example,vehicles,bike,wheels,2
    setNested Example,vehicles,bike,noise,whooosh
    
    setNested Example,vehicles,snowPlow,name,snowPlow
    setNested Example,vehicles,snowPlow,wheels,6
    setNested Example,vehicles,snowPlow,noise,wiiirrrrrEEEEEzzzz

Let's merge it and take only the first version when there's a clash.

    merge
    	dataSet
    		retieveResults Example,animals
    	
    	dataSet TakeFirst
    		retieveResults Example,vehicles

Let's merge it and take only the last version when there's a clash.

    merge
    	dataSet
    		retieveResults Example,animals
    	
    	dataSet TakeLast
    		retieveResults Example,vehicles

Let's merge it and combine clashes with a bias towards first version when there's a clash.

    merge
    	dataSet
    		retieveResults Example,animals
    	
    	dataSet CombineBiasFirst
    		retieveResults Example,vehicles

Let's merge it and combine clashes with a bias towards last version when there's a clash.

    merge
    	dataSet
    		retieveResults Example,animals
    	
    	dataSet CombineBiasLast
    		retieveResults Example,vehicles

