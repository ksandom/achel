# TimeThing

Provides time manipulation features. Mostly for refining result sets, but also for getting the current time and derivitive times.

TODO As of this writing, there are still bugs to be resolved.

## Using it

* Make sure `TimeThing` is included via [repoParms](https://github.com/ksandom/achel/blob/master/docs/programming/creatingARepositoryWithProfiles.md#use-repoparmdefinepackages-to-create-a-profile). If BASE is included, it should be fine.
* Do something with it. eg:
 * Create a timestamp using `--now`.
 * Filter results with something like `--newer` or `--older`.
 * Manipulate a timestamp with something like `--timeDiff` or `--fuzzyTime`.

## A worked example

Here is `getTSDemoData.macro` which creates some data

    # Create some data to demonstrate TimeThing ~ timething,example
    
    # Let's make our time stamps.
    now Example,now
    tsToday Example,todayMidnight
    strToTime Example,yesterday,-1 day,~!Example,todayMidnight!~
    strToTime Example,lastWeek,-1 week,~!Example,todayMidnight!~
    strToTime Example,nextWeek,+1 week,~!Example,todayMidnight!~
    getCategory Example
    
    # Let's manipulate it slightly to make the format more useful for searching
    loop
    	set Result,name,~!Result,key!~
    	set Result,ts,~!Result,line!~
    	fuzzyTime Result,fuzzyTime,~!Result,line!~
    	unset Result,key
    	unset Result,line
    
    # This line is really important. If the data is not in order, incorrect results will come back and it simply won't make any sense.
    sortOnItemKey ts

This creates a data set like this

    $ achel --getTSDemoData
    
      now: 
        name: now
        ts: 1390323826
      toDayMidnight: 
        name: toDayMidnight
        ts: 1390262400
      yesterday: 
        name: yesterday
        ts: 1390176000
      lastWeek: 
        name: lastWeek
        ts: 1389657600
      nextWeek: 
        name: nextWeek
        ts: 1390867200

Now let's do stuff with it

    ksandom@massTesting:/media/sf_files/develop/achel$ achel --getTSDemoData  --tsDay=0,,ts
    
      _1390417693: 
        name: now
        ts: 1390417693
        fuzzyTime: 2014-01-22--19:08:13

Note that todayMidnight doesn't show up. This is because the search is from midnight to midnight, exclusive.

Now let's search for this week
    $ achel --getTSDemoData  --tsWeek=0,,ts
    
      _1390262400: 
        name: yesterday
        ts: 1390262400
        fuzzyTime: 2014-01-21--0:00:00
      _1390348800: 
        name: todayMidnight
        ts: 1390348800
        fuzzyTime: 2014-01-22--0:00:00
      _1390418220: 
        name: now
        ts: 1390418220
        fuzzyTime: 2014-01-22--19:17:00

Or next week

    $ achel --getTSDemoData  --tsWeek=-1,,ts
    
      _1390953600: 
        name: nextWeek
        ts: 1390953600
        fuzzyTime: 2014-01-29--0:00:00
    
