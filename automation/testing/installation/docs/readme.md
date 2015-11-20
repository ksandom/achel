# Installation

For performing tests on the Installation.

## Using it

Run `./run` without parameters to see the possible tests to run

    $ ./run 
    Could not find tests/.sh.
    Syntax: run testName
    
    Possible tests are
            basicRootInstall
            basicUserInstall
            zshRootInstall
            zshUserInstall

Run a test by putting it's name just after the `./run` like this

    $ ./run basicRootInstall
    Error response from daemon: no such id: experiment
    Error: failed to remove containers: [experiment]
    Sending build context to Docker daemon 15.87 kB
    Step 0 : FROM debian:jessie
     ---> 4a5e6db8c069
    Step 1 : RUN apt-get update && apt-get install -y php5-cli git zsh
     ---> Using cache
     ---> 2a14460665b8
    Step 2 : ENV builton 2015-09-07
     ---> Using cache
     ---> d89ed35aa617
    Step 3 : ENV preCloneSerial 000
     ---> Using cache
     ---> 75427a963eb3
    Step 4 : ENV preInstallSerial 000
     ---> Using cache
     ---> a2c9d3de7348
    Step 5 : ADD runInstall /usr/bin/runInstall
     ---> Using cache
     ---> a12cbd48f613
    Step 6 : ADD tests /var/tests
     ---> Using cache
     ---> 34f93c826369
    Step 7 : RUN chmod 755 /var/tests/* /usr/bin/runInstall
     ---> Using cache
     ---> 40ef77b4c343
    Successfully built 40ef77b4c343
    detectOldSettings: No previous install found. Using defaults.
    Install config
            configDir:      /etc/achel **NEW**
            storageDir:     /etc/achel **NEW**
            installType:    cp **NEW**
            binExec:        /usr/bin **NEW**
            installNotes:
                    
    installRepo_setup: Going to setup "achel"
    documentationAddRepo: Adding repo "achel".
    installRepo_setup(achel/achel): Doing enabledPacakge achel .* achel
    [debug2] setFeatureType-2: verbosity: Set verbosity to "" (2)
    [debug2] improveColorsNoBlink-6: improveColorsNoBlink: Removing nasty colour combinations
    [debug2] installCoreStuff-5: Creating Verbosity levels
    [debug1] installCredentials-5: Creating credentials defaults
    [debug1] installSemantics-5: Creating semantics data
    [debug1] registerFeatures-5: Registering features for achel
    [debug2] detectTerminal-5: Creating information for detecting the best terminal
    [debug1] detectTerminal-5: Detecting the best terminal to use
    [debug2] setTerminalDefaults-5: Set terminal defaults
    [debug2] collectionSave-3: Collection: Saving SyntaxHighlightingFeatures.
    documentationAddProfile: Adding profile "achel".
    [##############################] / r=  100% 20/s 0 seconds remaining  
    
    [pass] colorText - simple - Got expected result (!="brightHLPurple"). 
    [pass] colorText - simple repeat - Got expected result (!="brightHLPurple"). 
    [pass] colorText - not the same - Got expected result (!="underscoreYellowHLGreen"). 
    [pass] colorText - empty cache - Got expected result (""). 
    [pass] colorText - filled cache - Got expected result (!="redHLYellow"). 
    [pass] collectionLoad - No clobber! - Got expected result ("blah"). 
    [pass] collectionDelete - Is it still there? - Got expected result (""). 
    [pass] collectionDelete - Is the data unset? - Got expected result (""). 
    [pass] collectionUnload - Is the data gone from memory? - Got expected result (""). 
    [pass] collectionExists - Non-existent - Got expected result ("false"). 
    [pass] collectionExists - Exists - Got expected result ("true"). 
    [pass] collectionSave - Is the data there? - Got expected result ("blah"). 
    [pass] getFileList - get some the right number of entries - Got expected result ("3"). 
    [pass] getFileList - get the right entries - No incorrect keys
    [pass] getFileTree - get some the right number of entries - Got expected result ("3"). 
    [pass] getFileTree - get the right entries - No incorrect keys
    [pass] getFileTree with attributes - get some the right number of entries - Got expected result ("3"). 
    [pass] getFileTree with attributes - get the right entries - Found some correct entries
    [pass] copyCategory - Got expected result ("1"). 
    [pass] setIfNothing Tmp - happy day - Got expected result ("asdf"). 
    [pass] setIfNothing Tmp - already set - Got expected result ("already set"). 
    [pass] setIfNothing Tmp - already set but empty - Got expected result ("should get this."). 
    [pass] setIfNothing Tmp - already set by setIfNothing - Got expected result ("already set"). 
    [pass] setIfNothing Local - happy day - Got expected result ("asdf"). 
    [pass] setIfNothing Local - already set - Got expected result ("already set"). 
    [pass] setIfNothing Local - already set but empty - Got expected result ("should get this."). 
    [pass] setIfNothing Local - already set by setIfNothing - Got expected result ("already set"). 
    [pass] setIfNothing Me - happy day - Got expected result ("asdf"). 
    [pass] setIfNothing Me - already set - Got expected result ("already set"). 
    [pass] setIfNothing Me - already set but empty - Got expected result ("should get this."). 
    [pass] setIfNothing Me - already set by setIfNothing - Got expected result ("already set"). 
    [pass] setIfNothing Isolated - happy day - Got expected result ("asdf"). 
    [pass] setIfNothing Isolated - already set - Got expected result ("already set"). 
    [pass] setIfNothing Isolated - already set but empty - Got expected result ("should get this."). 
    [pass] setIfNothing Isolated - already set by setIfNothing - Got expected result ("already set"). 
    [pass] setIfNotSet Tmp - happy day - Got expected result ("asdf"). 
    [pass] setIfNotSet Tmp - already set - Got expected result ("already set"). 
    [pass] setIfNotSet Tmp - already set but empty - Got expected result (""). 
    [pass] setIfNotSet Tmp - already set by setIfNotSet - Got expected result ("already set"). 
    [pass] copy - Got expected result ("4"). 
    [pass] parameter handeling with nesting - simple hit - got to expected location
    [pass] parameter handeling with nesting - simple miss - got to expected location
    [pass] parameter handeling with nesting - json [ hit - got to expected location
    [pass] parameter handeling with nesting - json [ miss - got to expected location
    [pass] Expected nesting matches - Got expected value.
    [pass] Inherit earlier nesting - Inherited expected value.
    [pass] Pollution from earlier higher nesting - clear - Did not inherit polluted value.
    [pass] Pollution from earlier higher nesting - intermediate results - Did not inherit polluted value.
    [pass] retrieveResults traditional - Got expected result ("3"). 
    [pass] retrieveResults nested - Got expected result ("4"). 
    [pass] stashResults traditional - Got expected result ("2"). 
    [pass] stashResults nested - Got expected result ("3"). 
    [pass] setNested - 3 simple entries - Got expected result ("3"). 
    [pass] setNested - 3 simple auto-indexed entries - Got expected result ("3"). 
    [pass] setNested - 3 auto-indexed entries - Got expected result ("3"). 
    [pass] setNested - 3 auto-indexed entries - expected addresses - The expected addresses worked.
    [pass] setNested - reproduced addressing bug - 0 - Got expected result ("1"). 
    [pass] setNested - reproduced addressing bug - 1 - Got expected result ("2"). 
    [pass] setNested - reproduced addressing bug - 2 - Got expected result ("3"). 
    [pass] setNested - simplified addressing test - no blank - Got expected result ("A"). 
    [pass] setNested - simplified addressing test - no appendage - Got expected result ("A"). 
    [pass] setNested - simplified addressing test - appendage - Got expected result ("A"). 
    [pass] getPreviousStackData - default - Got expected result ("stackUnitTests--3"). 
    [pass] getPreviousStackData - -2 - Got expected result ("stackUnitTests--7"). 
    [pass] getPreviousStackData - 3 - Got expected result ("stackUnitTests"). 
    [pass] stackTrace - Do we get results? - Got expected result (!="10"). 
    [pass] dataExists - false 1 - Got expected result ("false"). 
    [pass] saveStoreToData 1 - Got expected result ("true"). 
    [pass] loadStoreFromData - Got expected result ("a"). 
    [pass] deleteData - Got expected result ("false"). 
    [pass] saveStoreToData 2 - Got expected result ("true"). 
    [pass] dataDelete - Got expected result ("false"). 
    [pass] configExists - false 1 - Got expected result ("false"). 
    [pass] saveStoreToConfig - Got expected result ("true"). 
    [pass] loadStoreFromConfig - Got expected result ("a"). 
    [pass] deleteConfig - Got expected result ("false"). 
    [pass] Trigger event - Got expected result ("it was triggered"). 
    [pass] Trigger deleted event - Got expected result (""). 
    [pass] Trigger still deleted event - Got expected result (""). 
    [pass] exExec - no command - Got expected result (""). 
    [pass] exExec - simple to self - Got expected result ("1"). 
    [pass] exExec - simple echo - Got expected result ("Dream a dream"). 
    [pass] forceFailureStatus - Got expected result ("1"). 
    [pass] setFailureStatus - Got expected result ("1"). 
    [pass] forceWarningStatus - Got expected result ("2"). 
    [pass] setWarningStatus - from success - Got expected result ("2"). 
    [pass] setWarningStatus - from failure - Got expected result ("1"). 
    [pass] forceSuccessStatus - Got expected result ("0"). 
    [pass] getHomeUsingDirectEnv - Got a home value.
    [warn] getHomeUsingUserName - Didn't get a home value. This is not fatal; but not ideal either. Can you see a better way to do it? Start at getHome.macro.
    [pass] getHome - Got a home value.
    [pass] generateChars 5 # - Got expected result ("#####"). 
    [pass] generateChars 2 b - Got expected result ("bb"). 
    [pass] generateChars 6 - - Got expected result ("------"). 
    [pass] crcVar - got expected value.
    [pass] positiveCRCVar - Got expected result ("907060870"). 
    [pass] crcResultVar - got expected value.
    [pass] positiveCRCResultVar - Got expected result ("907060870"). 
    [pass] Index - add all items - Got expected result ("6"). 
    [pass] Index - add some items - Got expected result ("3"). 
    [pass] Index - get the items - Got expected result ("3"). 
    [pass] Index - get the right items - Got expected result ("do1"). 
    [pass] loop - defaults - single iteration - Got expected result ("1"). 
    [pass] loop - defaults - full line - Got expected result ("1234"). 
    [pass] loop - defaults - full subvalue - Got expected result ("1234"). 
    [pass] loop - Custom - full line - Got expected result ("1234"). 
    [pass] loop - Custom - full subvalue - Got expected result ("1234"). 
    [pass] ifResult - no result - no result
    [pass] ifResult - result - result
    [pass] if - if - if
    [pass] if - elseIf - elseif
    [pass] if - else - else
    [pass] nested - if else - if else
    [pass] nested - elseIf else - elseIf else
    [pass] nested - else else - else else
    [pass] ifNested true - true
    [pass] ifNested false - false.
    [pass] Load - executes - Got expected result ("1"). 
    [pass] Load - executes only once - Got expected result ("1"). 
    [pass] Unload - can allow an extra execution - Got expected result ("2"). 
    [pass] macro - loop does iterations - Got expected result ("5"). 
    [pass] macro - loop gets correct data - Got expected result ("1"). 
    [pass] macro - loop gets correct next data - Got expected result ("b"). 
    [pass] macro - loop gets correct previous data - Got expected result (""). 
    [pass] getProgressKey - Got expected result ("progress-macroUnitTests-6"). 
    [pass] macro - loop removes next and previous keys - Got expected result (""). 
    [pass] escape - Got expected result ("a"b"). 
    [pass] escapeForJson - Got expected result ("a"b'c"). 
    [pass] replaceInString - Got expected result ("a good string"). 
    [pass] replaceRegexInString - Got expected result ("a2-censored-c4"). 
    [pass] keyPositionToKey - 2 - Got expected result ("c"). 
    [pass] keyPositionToKey - 2 repeat - Got expected result ("c"). 
    [pass] keyPositionToKey - 3 repeat - Got expected result ("d"). 
    [pass] Manipulator - Fatal error in --toString - No fatal error.
    [pass] copyResultVar - Got expected result ("1"). 
    [pass] moveResultVar - destination - Got expected result ("2"). 
    [pass] moveResultVar - source - Got expected result (""). 
    [pass] Round 1 - 0 - got expected value.
    [pass] Round 1 - 1 - got expected value.
    [pass] Round 5 - 1 - got expected value.
    [pass] Round 5 - -1 - got expected value.
    [pass] Round 123 - -1 - got expected value.
    [pass] Round 153 - -2 - got expected value.
    [pass] Round 0.1 - 1 - got expected value.
    [pass] Round 0.1 - 0 - got expected value.
    [pass] Round 0.5 - 0 - got expected value.
    [pass] Round 0.005 - 0 - got expected value.
    [pass] Round 0.005 - 1 - got expected value.
    [pass] Round 0.005 - 2 - got expected value.
    [pass] Round 0.005 - 3 - got expected value.
    [pass] 2 squared - Got expected result ("4"). 
    [pass] square root of 4 - Got expected result ("2"). 
    [pass] absolute 2 - Got expected result ("2"). 
    [pass] absolute -2 - Got expected result ("2"). 
    [pass] Iterator 1-10 inclusive - Step 1 - Got expected result ("10"). 
    [pass] Iterator 0-10 inclusive - Step 0.1 - Got expected result ("101"). 
    [pass] Iterator 1-10 inclusive - Step 2 - Got expected result ("5"). 
    [pass] Iterator 10-1 inclusive - Step -1 - Got expected result ("10"). 
    [pass] Iterator 10-0 inclusive - Step -0.1 - Got expected result ("101"). 
    [pass] Iterator 1-10 inclusive - Step 0 (invalid incrementor) - Got expected result ("1"). 
    [pass] Iterator 1-0 inclusive - Step 1 (incrementor is in the wrong direction) - Got expected result ("1"). 
    [pass] Iterator 1- (missing stop) inclusive - Step 1 - Got expected result ("1"). 
    [pass] Iterator -10 (missing start) inclusive - Step 1 - Got expected result ("1"). 
    [pass] Merge - TakeFirst - Got expected result ("woof"). 
    [pass] Merge - TakeLast - Got expected result ("brrrrrr"). 
    [pass] Merge - Default (TakeLast) - Got expected result ("brrrrrr"). 
    [pass] Merge - TakeFirst - surrounding data - Got expected result (""). 
    [pass] Merge - TakeLast - surrounding data - Got expected result ("until the fuel runs out"). 
    [pass] Merge - CombineBiasFirst - right bias - Got expected result ("woof"). 
    [pass] Merge - CombineBiasLast - right bias - Got expected result ("brrrrrr"). 
    [pass] Merge - CombineBiasFirst - surrounding data - Got expected result ("4"). 
    [pass] Merge - CombineBiasLast - surrounding data - Got expected result ("runs like a"). 
    [pass] Merge - TakeFirst - surrounding data repeat (pollution test) - Got expected result (""). 
    [pass] Merge - TakeLast - surrounding data repeat (pollution test) - Got expected result ("until the fuel runs out"). 
    [pass] getPHPServer - gets results - Got 20>5 results. This is probably working.
    [pass] getEnv/getPHPServer - gets results using the alias - Got 20>5 results. This is probably working.
    [pass] 1 parameter (plain) - Got expected result ("ABC123"). 
    [pass] 2 parameters (plain) - Got expected result ("ABC123--DEF456"). 
    [pass] 3 parameters (plain) - Got expected result ("ABC123--DEF456--HIJ789"). 
    [pass] 2 parameters (json) - no default - filled in - Got expected result ("abc123--def456"). 
    [pass] 2 parameters (json) - no default - missing value - Got expected result ("--def456"). 
    [pass] 2 parameters (json) - with defaults - filled in - Got expected result ("abc123--def456"). 
    [pass] 2 parameters (json) - with defaults - missing value - Got expected result ("value1--def456"). 
    [pass] 1 parameter (nested json) - string 6!>3 - Got expected result ("def"). 
    [pass] 1 parameter (nested json) - string fail 6!>3 allowed - Got expected result ("false"). 
    [pass] 1 parameter (nested json) - string pass 3!>3 allowed - Got expected result ("true"). 
    [pass] 1 parameter (nested json) - string fail 3!<6 - Got expected result ("def   "). 
    [pass] 1 parameter (nested json) - string pass 3!<6 - Got expected result ("defasd"). 
    [pass] 1 parameter (nested json) - string fail 3!<6 allowed - Got expected result ("false"). 
    [pass] 1 parameter (nested json) - string pass 3!<6 allowed - Got expected result ("true"). 
    [pass] 1 parameter (nested json) - string (default) - Got expected result ("qwertyuiop"). 
    [pass] 1 parameter (nested json) - number pass 3<4<6 - Got expected result ("4"). 
    [pass] 1 parameter (nested json) - number fail 3<7!<6=6 - Got expected result ("6"). 
    [pass] 1 parameter (nested json) - number fail 3!<2<6=3 - Got expected result ("3"). 
    [pass] 1 parameter (nested json) - number fail 0<-1<6=0 - Got expected result ("0"). 
    [pass] 1 parameter (nested json) - number fail -3<2<0=0 - Got expected result ("0"). 
    [pass] 1 parameter (nested json) - number fail 0<1<6= - Got expected result ("1"). 
    [pass] 1 parameter (nested json) - number pass 3<4<6 allowed - Got expected result ("true"). 
    [pass] 1 parameter (nested json) - number fail 3<7!<6=6 allowed - Got expected result ("false"). 
    [pass] 1 parameter (nested json) - number fail 3!<2<6=3 allowed - Got expected result ("false"). 
    [pass] 1 parameter (nested json) - number (default) - Got expected result ("5"). 
    [pass] 1 parameter (nested json) - boolean true true - Got expected result ("true"). 
    [pass] 1 parameter (nested json) - boolean true 1 - Got expected result ("true"). 
    [pass] 1 parameter (nested json) - boolean true asdf - Got expected result ("true"). 
    [pass] 1 parameter (nested json) - boolean false false - Got expected result ("false"). 
    [pass] 1 parameter (nested json) - boolean false 0 - Got expected result ("false"). 
    [pass] 1 parameter (nested json) - boolean false '' (default) - Got expected result ("true"). 
    [pass] 1 parameter (nested json) - empty definition - Got expected result ("abc"). 
    [pass] 1 parameter (nested json) - empty definition - no value - Got expected result (""). 
    [pass] before loadRepoParms - Got expected result (""). 
    [pass] loadRepoParms - Got expected result ("achel"). 
    [pass] setRepoParm - Got expected result ("blah"). 
    [pass] listRepoParms - Got expected result ("achel"). 
    [pass] getRepoParm - Got expected result ("achel"). 
    [pass] Isolated - Got expected result ("aa1"). 
    [pass] Isolated - inherit forwards - Got expected result (""). 
    [pass] Isolated - inherit backwards - Got expected result (""). 
    [pass] Isolated - repeat - Got expected result ("aa1"). 
    [pass] Me - Got expected result ("aa1"). 
    [pass] Me - inherit forwards - Got expected result ("aa1"). 
    [pass] Me - inherit backwards - Got expected result (""). 
    [pass] Me - repeat - Got expected result ("aa1"). 
    [pass] Me - return - Got value from nested setting.
    [pass] Normal - Got expected result ("aa1"). 
    [pass] Normal - inherit forwards - Got expected result ("aa1"). 
    [pass] Normal - inherit backwards - Got expected result ("aa1"). 
    [pass] Normal - repeat - Got expected result ("aa1"). 
    [pass] makeMeAvailable - not available - Got expected result (""). 
    [pass] makeMeAvailable - available - Got expected result ("blah"). 
    [pass] makeLocalAvailable - not available - Got expected result (""). 
    [pass] makeLocalAvailable - available - Got expected result ("blah"). 
    [pass] makeLocalAvailable - interference - integrity check 1 - Got expected result ("3.14"). 
    [pass] makeLocalAvailable - interference - integrity check 2 - Got expected result ("3.14"). 
    [pass] makeLocalAvailable - interference - Got expected result ("3.14"). 
    [pass] is Local clean? - Got expected result (""). 
    [pass] is Local clean with makeLocalAvailable? - Got expected result (""). 
    [pass] is a nested Local clean with makeLocalAvailable? - Got expected result (""). 
    [pass] scopeName - set - We have a scopeName.
    [pass] scopeName - same scope - Got expected result ("defineTest-7"). 
    [pass] scopeName - different scope - Got expected result. Got defineTest-7 != 
    [pass] Local set - Got expected result ("aa1"). 
    [pass] Local set - inherit forwards - Got expected result ("aa1"). 
    [pass] Local set - inherit backwards - Got expected result ("aa1"). 
    [pass] Local set - different scope read - Got expected result. Got aa1 != 
    [pass] Local set - different scope write - Got expected result (""). 
    [pass] Local set - repeat - Got expected result ("aa1"). 
    [pass] Local setNested - different scope read without variable nesting - Got expected result. Got aa1 != 
    [pass] Local setNested - different scope read with variable nesting - Got expected result. Got aa1 != 
    [pass] Local setNested without nesting - Got expected result ("aa1"). 
    [pass] Local setNested without nesting - inherit forwards - Got expected result ("aa1"). 
    [pass] Local setNested without nesting - inherit backwards - Got expected result ("aa1"). 
    [pass] Local setNested without nesting - different scope read - Got expected result. Got aa1 != 
    [pass] Local setNested without nesting - different scope write - Got expected result (""). 
    [pass] Local setNested without nesting - repeat - Got expected result ("aa1"). 
    [pass] Local setNested with nesting - Got expected result ("aa1"). 
    [pass] Local setNested with nesting - inherit forwards - Got expected result ("aa1"). 
    [pass] Local setNested with nesting - inherit backwards - Got expected result ("aa1"). 
    [pass] Local setNested with nesting - different scope read - Got expected result. Got aa1 != 
    [pass] Local setNested with nesting - different scope write - Got expected result (""). 
    [pass] Local setNested with nesting - repeat - Got expected result ("aa1"). 
    [pass] Local setNested overwrite bug - Got expected result ("aa1-aa2"). 
    [pass] Me setNested overwrite bug - Got expected result ("aa1-aa2"). 
    [pass] Normal setNested overwrite bug - Got expected result ("aa1-aa2"). 
    [pass] Normal unset - integrity check - Got expected result ("aa1"). 
    [pass] Normal unset - Got expected result (""). 
    [pass] Isolated unset - Got expected result (""). 
    [pass] Me unset - Got expected result (""). 
    [pass] Local unset - integrity check - Got expected result ("aa1"). 
    [pass] Local unset - Got expected result (""). 
    [pass] dataType does not exist yet - Got expected result ("0"). 
    [pass] createDataType - Got expected result ("1"). 
    [pass] createDataType - amend more template - Got expected result ("testutMore"). 
    [pass] deleteDataType - Got expected result ("0"). 
    [pass] listDataType - Got expected result ("1"). 
    [pass] featureType does not exist yet - Got expected result ("0"). 
    [pass] createFeatureType - Got expected result ("1"). 
    [pass] deleteFeatureType - Got expected result ("0"). 
    [pass] listFeatureType - Got expected result ("1"). 
    [pass] setFeatureAttribute - Got expected result ("123"). 
    [pass] setFeatureType - Got expected result ("test123ut"). 
    [pass] listFeatures - Do we get expected data - Got expected result (!="registerTags"). name
    [warn] listFeatureSets - Got one featureSet. It could be you have nothing installed yet
    [pass] registerFeatures - Got expected result ("476"). 
    [pass] tagResult - default tagSet - Got expected result ("test1"). 
    [pass] tagResult - defined tagSet - Got expected result ("test2"). 
    [pass] tagResults - default tagSet - Got expected result ("test3"). 
    [pass] tagResults - defined tagSet - Got expected result ("test4"). 
    [pass] TimeThing - tsDay/today - yay
    [pass] TimeThing - tsDay/yesterday - yay
    [pass] TimeThing - tsDay/tomorrow - yay
    [pass] TimeThing - tsDay/yesterday and today - yay
    [pass] TimeThing - convertWhenToTimeStamp defaults - Got expected result ("2014-01-30--0:00:00"). 
    [pass] TimeThing - convertWhenToTimeStamp anotherField (in) - Got expected result ("2014-01-30--0:00:01"). 
    [pass] TimeThing - convertWhenToTimeStamp andAnotherField (out) - Got expected result ("2014-01-30--0:00:02"). 
    [pass] TimeThing - convertWhenToTimeStamp both in and out - Got expected result ("2014-01-30--0:00:03"). 
    [pass] Sine 1 - got expected value.
    [pass] Sine 0 - got expected value.
    [pass] Sine -1 - got expected value.
    [pass] Arc Sine 1 - got expected value.
    [pass] Arc Sine 0 - got expected value.
    [pass] Arc Sine -1 - got expected value.
    [pass] CoSine 1 - got expected value.
    [pass] CoSine 0 - got expected value.
    [pass] CoSine -1 - got expected value.
    [pass] Arc CoSine 1 - got expected value.
    [pass] Arc CoSine 0 - got expected value.
    [pass] Arc CoSine -1 - got expected value.
    [pass] Tangent 1 - got expected value.
    [pass] Tangent 0 - got expected value.
    [pass] Tangent -1 - got expected value.
    [pass] Arc Tangent 1 - got expected value.
    [pass] Arc Tangent 0 - got expected value.
    [pass] Arc Tangent -1 - got expected value.
    [pass] Degrees to Radians 90 - got expected value.
    [pass] Degrees to Radians 0 - got expected value.
    [pass] Degrees to Radians -90 - got expected value.
    [pass] Radians to Degrees 1.570796 - got expected value.
    [pass] Radians to Degrees 0 - got expected value.
    [pass] Radians to Degrees -1.570796 - got expected value.
    [pass] Pi - got expected value.
    [pass] PiBy 1 - Got expected result ("3.14"). 
    [pass] PiBy 1.5 - Got expected result ("4.71"). 
    [pass] PiBy 2 - Got expected result ("6.28"). 
    [pass] PiBy - Local - Got expected result ("6.28"). 
    [pass] PiBy - Pure Local scoping - Got a value.
    [pass] Right angle triangle - opposite angle - got expected value.
    [pass] getHypotenuse 2 2 - Got expected result ("2.83"). 
    [pass] getHypotenuse 3.4 6.8 - Got expected result ("7.6"). 
    [pass] angle and distance to co-ordinates 0 - Got expected result ("0 5"). 
    [pass] angle and distance to co-ordinates 180 - Got expected result ("0 -5"). 
    [pass] angle and distance to co-ordinates 45 - Got expected result ("3.54 3.54"). 
    [pass] angle and distance to co-ordinates 33.69 - Got expected result ("2 3"). 
    [pass] angle and distance to co-ordinates 123.69 - Got expected result ("3 -2"). 
    [pass] angle and distance to co-ordinates 213.69 - Got expected result ("-2 -3"). 
    [pass] angle and distance to co-ordinates 303.69 - Got expected result ("-3 2"). 
    [pass] get opposite from angle and distance - Got expected result ("2"). 
    [pass] get adjacent from angle and distance - Got expected result ("3"). 
    [pass] coordToAngle 0 0 - Got expected result ("undefined"). 
    [pass] coordToAngle 2 3 - Got expected result ("33.69"). 
    [pass] coordToAngle 3 -2 - Got expected result ("123.69"). 
    [pass] coordToAngle -2 -3 - Got expected result ("213.69"). 
    [pass] coordToAngle -3 2 - Got expected result ("303.69"). 
    [pass] coordToAngle 0 3 - Got expected result ("0"). 
    [pass] coordToAngle 3 0 - Got expected result ("90"). 
    [pass] coordToAngle 0 -3 - Got expected result ("180"). 
    [pass] coordToAngle -3 0 - Got expected result ("270"). 
    [pass] 2CoordsToAngle 0 0 0 0 - Got expected result ("undefined"). 
    [pass] 2CoordsToAngle 0 0 2 3 - Got expected result ("33.69"). 
    [pass] 2CoordsToAngle 0 0 3 -2 - Got expected result ("123.69"). 
    [pass] 2CoordsToAngle 0 0 -2 -3 - Got expected result ("213.69"). 
    [pass] 2CoordsToAngle 0 0 -3 2 - Got expected result ("303.69"). 
    [pass] 2CoordsToAngle 0 0 0 3 - Got expected result ("0"). 
    [pass] 2CoordsToAngle 0 0 3 0 - Got expected result ("90"). 
    [pass] 2CoordsToAngle 0 0 0 -3 - Got expected result ("180"). 
    [pass] 2CoordsToAngle 0 0 -3 0 - Got expected result ("270"). 
    [pass] 2CoordsToAngle 10 10 10 10 - Got expected result ("undefined"). 
    [pass] 2CoordsToAngle 10 10 12 13 - Got expected result ("33.69"). 
    [pass] 2CoordsToAngle 10 10 13 8 - Got expected result ("123.69"). 
    [pass] 2CoordsToAngle 10 10 8 7 - Got expected result ("213.69"). 
    [pass] 2CoordsToAngle 10 10 7 12 - Got expected result ("303.69"). 
    [pass] 2CoordsToAngle 10 10 10 13 - Got expected result ("0"). 
    [pass] 2CoordsToAngle 10 10 13 10 - Got expected result ("90"). 
    [pass] 2CoordsToAngle 10 10 10 7 - Got expected result ("180"). 
    [pass] 2CoordsToAngle 10 10 7 10 - Got expected result ("270"). 
    [pass] 2CoordsToAngle 10 -10 10 -10 - Got expected result ("undefined"). 
    [pass] 2CoordsToAngle 10 -10 12 -7 - Got expected result ("33.69"). 
    [pass] 2CoordsToAngle 10 -10 13 -12 - Got expected result ("123.69"). 
    [pass] 2CoordsToAngle 10 -10 8 -13 - Got expected result ("213.69"). 
    [pass] 2CoordsToAngle 10 -10 7 -8 - Got expected result ("303.69"). 
    [pass] 2CoordsToAngle 10 -10 10 -7 - Got expected result ("0"). 
    [pass] 2CoordsToAngle 10 -10 13 -10 - Got expected result ("90"). 
    [pass] 2CoordsToAngle 10 -10 10 -13 - Got expected result ("180"). 
    [pass] 2CoordsToAngle 10 -10 7 -10 - Got expected result ("270"). 
    [pass] 2CoordsToAngle -10 -10 -10 -10 - Got expected result ("undefined"). 
    [pass] 2CoordsToAngle -10 -10 -8 -7 - Got expected result ("33.69"). 
    [pass] 2CoordsToAngle -10 -10 -7 -12 - Got expected result ("123.69"). 
    [pass] 2CoordsToAngle -10 -10 -12 -13 - Got expected result ("213.69"). 
    [pass] 2CoordsToAngle -10 -10 -13 -8 - Got expected result ("303.69"). 
    [pass] 2CoordsToAngle -10 -10 -10 -7 - Got expected result ("0"). 
    [pass] 2CoordsToAngle -10 -10 -7 -10 - Got expected result ("90"). 
    [pass] 2CoordsToAngle -10 -10 -10 -13 - Got expected result ("180"). 
    [pass] 2CoordsToAngle -10 -10 -13 -10 - Got expected result ("270"). 
    [pass] 2CoordsToAngle -10 10 -10 10 - Got expected result ("undefined"). 
    [pass] 2CoordsToAngle -10 10 -8 13 - Got expected result ("33.69"). 
    [pass] 2CoordsToAngle -10 10 -7 8 - Got expected result ("123.69"). 
    [pass] 2CoordsToAngle -10 10 -12 7 - Got expected result ("213.69"). 
    [pass] 2CoordsToAngle -10 10 -13 12 - Got expected result ("303.69"). 
    [pass] 2CoordsToAngle -10 10 -10 13 - Got expected result ("0"). 
    [pass] 2CoordsToAngle -10 10 -7 10 - Got expected result ("90"). 
    [pass] 2CoordsToAngle -10 10 -10 7 - Got expected result ("180"). 
    [pass] 2CoordsToAngle -10 10 -13 10 - Got expected result ("270"). 
    [pass] 2CoordsTo1Coord x + - Got expected result ("2"). 
    [pass] 2CoordsTo1Coord y + - Got expected result ("3"). 
    [pass] 2CoordsTo1Coord x - - Got expected result ("2"). 
    [pass] 2CoordsTo1Coord y - - Got expected result ("2"). 
    [pass] 2CoordsToDistanceData (derived test) - 0 0 2 2 (*1) - Got expected result ("2.83"). 
    [pass] 2CoordsToDistanceData (derived test) - 0 0 3.4 6.8 (*1) - Got expected result ("7.6"). 
    [pass] 2CoordsToDistanceData (derived test) - 2 2 4 4 (*1) - Got expected result ("2.83"). 
    [pass] 2CoordsToDistanceData (derived test) - 2 2 5.4 8.8 (*1) - Got expected result ("7.6"). 
    [pass] 2CoordsToDistanceData (derived test) - -10 -10 -8 -8 (*1) - Got expected result ("2.83"). 
    [pass] 2CoordsToDistanceData (derived test) - -10 -10 -6.6 -3.2 (*1) - Got expected result ("7.6"). 
    [pass] 2CoordsToDistanceData (derived test) - 0 0 2000000 2000000 (*1000000) - Got expected result ("2.83"). 
    [pass] 2CoordsToDistanceData (derived test) - 0 0 3400000 6800000 (*1000000) - Got expected result ("7.6"). 
    [pass] 2CoordsToDistanceData (derived test) - 2000000 2000000 4000000 4000000 (*1000000) - Got expected result ("2.83"). 
    [pass] 2CoordsToDistanceData (derived test) - 2000000 2000000 5400000 8800000 (*1000000) - Got expected result ("7.6"). 
    [pass] 2CoordsToDistanceData (derived test) - -10000000 -10000000 -8000000 -8000000 (*1000000) - Got expected result ("2.83"). 
    [pass] 2CoordsToDistanceData (derived test) - -10000000 -10000000 -6600000 -3200000 (*1000000) - Got expected result ("7.6"). 
    [pass] 2CoordsToDistanceData (derived test) - 0 0 2000000000 2000000000 (*1000000000) - Got expected result ("2.83"). 
    [pass] 2CoordsToDistanceData (derived test) - 0 0 3400000000 6800000000 (*1000000000) - Got expected result ("7.6"). 
    [pass] 2CoordsToDistanceData (derived test) - 2000000000 2000000000 4000000000 4000000000 (*1000000000) - Got expected result ("2.83"). 
    [pass] 2CoordsToDistanceData (derived test) - 2000000000 2000000000 5400000000 8800000000 (*1000000000) - Got expected result ("7.6"). 
    [pass] 2CoordsToDistanceData (derived test) - -10000000000 -10000000000 -8000000000 -8000000000 (*1000000000) - Got expected result ("2.83"). 
    [pass] 2CoordsToDistanceData (derived test) - -10000000000 -10000000000 -6600000000 -3200000000 (*1000000000) - Got expected result ("7.6"). 
    [pass] 2CoordsToDistanceData (derived test) - 0 0 2000000000000 2000000000000 (*1000000000000) - Got expected result ("2.83"). 
    [pass] 2CoordsToDistanceData (derived test) - 0 0 3400000000000 6800000000000 (*1000000000000) - Got expected result ("7.6"). 
    [pass] 2CoordsToDistanceData (derived test) - 2000000000000 2000000000000 4000000000000 4000000000000 (*1000000000000) - Got expected result ("2.83"). 
    [pass] 2CoordsToDistanceData (derived test) - 2000000000000 2000000000000 5400000000000 8800000000000 (*1000000000000) - Got expected result ("7.6"). 
    [pass] 2CoordsToDistanceData (derived test) - -10000000000000 -10000000000000 -8000000000000 -8000000000000 (*1000000000000) - Got expected result ("2.83"). 
    [pass] 2CoordsToDistanceData (derived test) - -10000000000000 -10000000000000 -6600000000000 -3200000000000 (*1000000000000) - Got expected result ("7.6"). 
    [pass] 2CoordsToDistanceData (derived test) - 0 0 2000000000000000 2000000000000000 (*1000000000000000) - Got expected result ("2.83"). 
    [pass] 2CoordsToDistanceData (derived test) - 0 0 3.4E+15 6.8E+15 (*1000000000000000) - Got expected result ("7.6"). 
    [pass] 2CoordsToDistanceData (derived test) - 2000000000000000 2000000000000000 4000000000000000 4000000000000000 (*1000000000000000) - Got expected result ("2.83"). 
    [pass] 2CoordsToDistanceData (derived test) - 2000000000000000 2000000000000000 5.4E+15 8.8E+15 (*1000000000000000) - Got expected result ("7.6"). 
    [pass] 2CoordsToDistanceData (derived test) - -10000000000000000 -10000000000000000 -8000000000000000 -8000000000000000 (*1000000000000000) - Got expected result ("2.83"). 
    [pass] 2CoordsToDistanceData (derived test) - -10000000000000000 -10000000000000000 -6.6E+15 -3.2E+15 (*1000000000000000) - Got expected result ("7.6"). 
    [pass] 2CoordsToDistanceData (derived test) - 0 0 2000000000000000000 2000000000000000000 (*1000000000000000000) - Got expected result ("2.83"). 
    [pass] 2CoordsToDistanceData (derived test) - 0 0 3.4E+18 6.8E+18 (*1000000000000000000) - Got expected result ("7.6"). 
    [pass] 2CoordsToDistanceData (derived test) - 2000000000000000000 2000000000000000000 4000000000000000000 4000000000000000000 (*1000000000000000000) - Got expected result ("2.83"). 
    [pass] 2CoordsToDistanceData (derived test) - 2000000000000000000 2000000000000000000 5.4E+18 8.8E+18 (*1000000000000000000) - Got expected result ("7.6"). 
    [pass] 2CoordsToDistanceData (derived test) - -1.0E+19 -1.0E+19 -8000000000000000000 -8000000000000000000 (*1000000000000000000) - Got expected result ("2.83"). 
    [pass] 2CoordsToDistanceData (derived test) - -1.0E+19 -1.0E+19 -6.6E+18 -3.2E+18 (*1000000000000000000) - Got expected result ("7.6"). 
    [pass] 2CoordsToDistanceData (derived test) - 0 0 2.0E+21 2.0E+21 (*1000000000000000000000) - Got expected result ("2.83"). 
    [pass] 2CoordsToDistanceData (derived test) - 0 0 3.4E+21 6.8E+21 (*1000000000000000000000) - Got expected result ("7.6"). 
    [pass] 2CoordsToDistanceData (derived test) - 2.0E+21 2.0E+21 4.0E+21 4.0E+21 (*1000000000000000000000) - Got expected result ("2.83"). 
    [pass] 2CoordsToDistanceData (derived test) - 2.0E+21 2.0E+21 5.4E+21 8.8E+21 (*1000000000000000000000) - Got expected result ("7.6"). 
    [pass] 2CoordsToDistanceData (derived test) - -1.0E+22 -1.0E+22 -8.0E+21 -8.0E+21 (*1000000000000000000000) - Got expected result ("2.83"). 
    [pass] 2CoordsToDistanceData (derived test) - -1.0E+22 -1.0E+22 -6.6E+21 -3.2E+21 (*1000000000000000000000) - Got expected result ("7.6"). 
    [pass] 2CoordsToDistanceData (derived test) - 0 0 2.0E+24 2.0E+24 (*1000000000000000000000000) - Got expected result ("2.83"). 
    [pass] 2CoordsToDistanceData (derived test) - 0 0 3.4E+24 6.8E+24 (*1000000000000000000000000) - Got expected result ("7.6"). 
    [pass] 2CoordsToDistanceData (derived test) - 2.0E+24 2.0E+24 4.0E+24 4.0E+24 (*1000000000000000000000000) - Got expected result ("2.83"). 
    [pass] 2CoordsToDistanceData (derived test) - 2.0E+24 2.0E+24 5.4E+24 8.8E+24 (*1000000000000000000000000) - Got expected result ("7.6"). 
    [pass] 2CoordsToDistanceData (derived test) - -1.0E+25 -1.0E+25 -8.0E+24 -8.0E+24 (*1000000000000000000000000) - Got expected result ("2.83"). 
    [pass] 2CoordsToDistanceData (derived test) - -1.0E+25 -1.0E+25 -6.6E+24 -3.2E+24 (*1000000000000000000000000) - Got expected result ("7.6"). 
    [pass] 2CoordsToDistanceData (derived test) - 0 0 2.0E+27 2.0E+27 (*1000000000000000000000000000) - Got expected result ("2.83"). 
    [pass] 2CoordsToDistanceData (derived test) - 0 0 3.4E+27 6.8E+27 (*1000000000000000000000000000) - Got expected result ("7.6"). 
    [pass] 2CoordsToDistanceData (derived test) - 2.0E+27 2.0E+27 4.0E+27 4.0E+27 (*1000000000000000000000000000) - Got expected result ("2.83"). 
    [pass] 2CoordsToDistanceData (derived test) - 2.0E+27 2.0E+27 5.4E+27 8.8E+27 (*1000000000000000000000000000) - Got expected result ("7.6"). 
    [pass] 2CoordsToDistanceData (derived test) - -1.0E+28 -1.0E+28 -8.0E+27 -8.0E+27 (*1000000000000000000000000000) - Got expected result ("2.83"). 
    [pass] 2CoordsToDistanceData (derived test) - -1.0E+28 -1.0E+28 -6.6E+27 -3.2E+27 (*1000000000000000000000000000) - Got expected result ("7.6"). 
    [pass] 2CoordsToDistanceData (derived test) - 0 0 2.0E+30 2.0E+30 (*1000000000000000000000000000000) - Got expected result ("2.83"). 
    [pass] 2CoordsToDistanceData (derived test) - 0 0 3.4E+30 6.8E+30 (*1000000000000000000000000000000) - Got expected result ("7.6"). 
    [pass] 2CoordsToDistanceData (derived test) - 2.0E+30 2.0E+30 4.0E+30 4.0E+30 (*1000000000000000000000000000000) - Got expected result ("2.83"). 
    [pass] 2CoordsToDistanceData (derived test) - 2.0E+30 2.0E+30 5.4E+30 8.8E+30 (*1000000000000000000000000000000) - Got expected result ("7.6"). 
    [pass] 2CoordsToDistanceData (derived test) - -1.0E+31 -1.0E+31 -8.0E+30 -8.0E+30 (*1000000000000000000000000000000) - Got expected result ("2.83"). 
    [pass] 2CoordsToDistanceData (derived test) - -1.0E+31 -1.0E+31 -6.6E+30 -3.2E+30 (*1000000000000000000000000000000) - Got expected result ("7.6"). 
    [pass] 2CoordsToDistanceData (derived test) - 0 0 2.0E+33 2.0E+33 (*1000000000000000000000000000000000) - Got expected result ("2.83"). 
    [pass] 2CoordsToDistanceData (derived test) - 0 0 3.4E+33 6.8E+33 (*1000000000000000000000000000000000) - Got expected result ("7.6"). 
    [pass] 2CoordsToDistanceData (derived test) - 2.0E+33 2.0E+33 4.0E+33 4.0E+33 (*1000000000000000000000000000000000) - Got expected result ("2.83"). 
    [pass] 2CoordsToDistanceData (derived test) - 2.0E+33 2.0E+33 5.4E+33 8.8E+33 (*1000000000000000000000000000000000) - Got expected result ("7.6"). 
    [pass] 2CoordsToDistanceData (derived test) - -1.0E+34 -1.0E+34 -8.0E+33 -8.0E+33 (*1000000000000000000000000000000000) - Got expected result ("2.83"). 
    [pass] 2CoordsToDistanceData (derived test) - -1.0E+34 -1.0E+34 -6.6E+33 -3.2E+33 (*1000000000000000000000000000000000) - Got expected result ("7.6"). 
    [pass] 2CoordsToDistanceData (derived test) - 0 0 2.0E+36 2.0E+36 (*1000000000000000000000000000000000000) - Got expected result ("2.83"). 
    [pass] 2CoordsToDistanceData (derived test) - 0 0 3.4E+36 6.8E+36 (*1000000000000000000000000000000000000) - Got expected result ("7.6"). 
    [pass] 2CoordsToDistanceData (derived test) - 2.0E+36 2.0E+36 4.0E+36 4.0E+36 (*1000000000000000000000000000000000000) - Got expected result ("2.83"). 
    [pass] 2CoordsToDistanceData (derived test) - 2.0E+36 2.0E+36 5.4E+36 8.8E+36 (*1000000000000000000000000000000000000) - Got expected result ("7.6"). 
    [pass] 2CoordsToDistanceData (derived test) - -1.0E+37 -1.0E+37 -8.0E+36 -8.0E+36 (*1000000000000000000000000000000000000) - Got expected result ("2.83"). 
    [pass] 2CoordsToDistanceData (derived test) - -1.0E+37 -1.0E+37 -6.6E+36 -3.2E+36 (*1000000000000000000000000000000000000) - Got expected result ("7.6"). 
    [pass] 2CoordsToDistanceData (derived test) - 0 0 2.0E+39 2.0E+39 (*1000000000000000000000000000000000000000) - Got expected result ("2.83"). 
    [pass] 2CoordsToDistanceData (derived test) - 0 0 3.4E+39 6.8E+39 (*1000000000000000000000000000000000000000) - Got expected result ("7.6"). 
    [pass] 2CoordsToDistanceData (derived test) - 2.0E+39 2.0E+39 4.0E+39 4.0E+39 (*1000000000000000000000000000000000000000) - Got expected result ("2.83"). 
    [pass] 2CoordsToDistanceData (derived test) - 2.0E+39 2.0E+39 5.4E+39 8.8E+39 (*1000000000000000000000000000000000000000) - Got expected result ("7.6"). 
    [pass] 2CoordsToDistanceData (derived test) - -1.0E+40 -1.0E+40 -8.0E+39 -8.0E+39 (*1000000000000000000000000000000000000000) - Got expected result ("2.83"). 
    [pass] 2CoordsToDistanceData (derived test) - -1.0E+40 -1.0E+40 -6.6E+39 -3.2E+39 (*1000000000000000000000000000000000000000) - Got expected result ("7.6"). 
    [pass] 2CoordsToDistanceData (derived test) - 0 0 2.0E+42 2.0E+42 (*1000000000000000000000000000000000000000000) - Got expected result ("2.83"). 
    [pass] 2CoordsToDistanceData (derived test) - 0 0 3.4E+42 6.8E+42 (*1000000000000000000000000000000000000000000) - Got expected result ("7.6"). 
    [pass] 2CoordsToDistanceData (derived test) - 2.0E+42 2.0E+42 4.0E+42 4.0E+42 (*1000000000000000000000000000000000000000000) - Got expected result ("2.83"). 
    [pass] 2CoordsToDistanceData (derived test) - 2.0E+42 2.0E+42 5.4E+42 8.8E+42 (*1000000000000000000000000000000000000000000) - Got expected result ("7.6"). 
    [pass] 2CoordsToDistanceData (derived test) - -1.0E+43 -1.0E+43 -8.0E+42 -8.0E+42 (*1000000000000000000000000000000000000000000) - Got expected result ("2.83"). 
    [pass] 2CoordsToDistanceData (derived test) - -1.0E+43 -1.0E+43 -6.6E+42 -3.2E+42 (*1000000000000000000000000000000000000000000) - Got expected result ("7.6"). 
    [pass] 2CoordsToDistanceData (derived test) - 0 0 2.0E+45 2.0E+45 (*1000000000000000000000000000000000000000000000) - Got expected result ("2.83"). 
    [pass] 2CoordsToDistanceData (derived test) - 0 0 3.4E+45 6.8E+45 (*1000000000000000000000000000000000000000000000) - Got expected result ("7.6"). 
    [pass] 2CoordsToDistanceData (derived test) - 2.0E+45 2.0E+45 4.0E+45 4.0E+45 (*1000000000000000000000000000000000000000000000) - Got expected result ("2.83"). 
    [pass] 2CoordsToDistanceData (derived test) - 2.0E+45 2.0E+45 5.4E+45 8.8E+45 (*1000000000000000000000000000000000000000000000) - Got expected result ("7.6"). 
    [pass] 2CoordsToDistanceData (derived test) - -1.0E+46 -1.0E+46 -8.0E+45 -8.0E+45 (*1000000000000000000000000000000000000000000000) - Got expected result ("2.83"). 
    [pass] 2CoordsToDistanceData (derived test) - -1.0E+46 -1.0E+46 -6.6E+45 -3.2E+45 (*1000000000000000000000000000000000000000000000) - Got expected result ("7.6"). 
    [pass] 2CoordsToDistanceData (derived test) - 0 0 2.0E+48 2.0E+48 (*1000000000000000000000000000000000000000000000000) - Got expected result ("2.83"). 
    [pass] 2CoordsToDistanceData (derived test) - 0 0 3.4E+48 6.8E+48 (*1000000000000000000000000000000000000000000000000) - Got expected result ("7.6"). 
    [pass] 2CoordsToDistanceData (derived test) - 2.0E+48 2.0E+48 4.0E+48 4.0E+48 (*1000000000000000000000000000000000000000000000000) - Got expected result ("2.83"). 
    [pass] 2CoordsToDistanceData (derived test) - 2.0E+48 2.0E+48 5.4E+48 8.8E+48 (*1000000000000000000000000000000000000000000000000) - Got expected result ("7.6"). 
    [pass] 2CoordsToDistanceData (derived test) - -1.0E+49 -1.0E+49 -8.0E+48 -8.0E+48 (*1000000000000000000000000000000000000000000000000) - Got expected result ("2.83"). 
    [pass] 2CoordsToDistanceData (derived test) - -1.0E+49 -1.0E+49 -6.6E+48 -3.2E+48 (*1000000000000000000000000000000000000000000000000) - Got expected result ("7.6"). 
    [pass] 2CoordsToDistanceData (derived test) - 0 0 2.0E+51 2.0E+51 (*1000000000000000000000000000000000000000000000000000) - Got expected result ("2.83"). 
    [pass] 2CoordsToDistanceData (derived test) - 0 0 3.4E+51 6.8E+51 (*1000000000000000000000000000000000000000000000000000) - Got expected result ("7.6"). 
    [pass] 2CoordsToDistanceData (derived test) - 2.0E+51 2.0E+51 4.0E+51 4.0E+51 (*1000000000000000000000000000000000000000000000000000) - Got expected result ("2.83"). 
    [pass] 2CoordsToDistanceData (derived test) - 2.0E+51 2.0E+51 5.4E+51 8.8E+51 (*1000000000000000000000000000000000000000000000000000) - Got expected result ("7.6"). 
    [pass] 2CoordsToDistanceData (derived test) - -1.0E+52 -1.0E+52 -8.0E+51 -8.0E+51 (*1000000000000000000000000000000000000000000000000000) - Got expected result ("2.83"). 
    [pass] 2CoordsToDistanceData (derived test) - -1.0E+52 -1.0E+52 -6.6E+51 -3.2E+51 (*1000000000000000000000000000000000000000000000000000) - Got expected result ("7.6"). 
    [pass] 2CoordsToDistanceData (derived test) - 0 0 2.0E-6 2.0E-6 (*0.000001) - Got expected result ("2.83"). 
    [pass] 2CoordsToDistanceData (derived test) - 0 0 3.4E-6 6.8E-6 (*0.000001) - Got expected result ("7.6"). 
    [pass] 2CoordsToDistanceData (derived test) - 2.0E-6 2.0E-6 4.0E-6 4.0E-6 (*0.000001) - Got expected result ("2.83"). 
    [pass] 2CoordsToDistanceData (derived test) - 2.0E-6 2.0E-6 5.4E-6 8.8E-6 (*0.000001) - Got expected result ("7.6"). 
    [pass] 2CoordsToDistanceData (derived test) - -1.0E-5 -1.0E-5 -8.0E-6 -8.0E-6 (*0.000001) - Got expected result ("2.83"). 
    [pass] 2CoordsToDistanceData (derived test) - -1.0E-5 -1.0E-5 -6.6E-6 -3.2E-6 (*0.000001) - Got expected result ("7.6"). 
    [pass] 2CoordsToDistanceData (derived test) - 0 0 2.0E-9 2.0E-9 (*0.000000001) - Got expected result ("2.83"). 
    [pass] 2CoordsToDistanceData (derived test) - 0 0 3.4E-9 6.8E-9 (*0.000000001) - Got expected result ("7.6"). 
    [pass] 2CoordsToDistanceData (derived test) - 2.0E-9 2.0E-9 4.0E-9 4.0E-9 (*0.000000001) - Got expected result ("2.83"). 
    [pass] 2CoordsToDistanceData (derived test) - 2.0E-9 2.0E-9 5.4E-9 8.8E-9 (*0.000000001) - Got expected result ("7.6"). 
    [pass] 2CoordsToDistanceData (derived test) - -1.0E-8 -1.0E-8 -8.0E-9 -8.0E-9 (*0.000000001) - Got expected result ("2.83"). 
    [pass] 2CoordsToDistanceData (derived test) - -1.0E-8 -1.0E-8 -6.6E-9 -3.2E-9 (*0.000000001) - Got expected result ("7.6"). 
    [pass] 2CoordsToDistanceData (derived test) - 0 0 2.0E-12 2.0E-12 (*0.000000000001) - Got expected result ("2.83"). 
    [pass] 2CoordsToDistanceData (derived test) - 0 0 3.4E-12 6.8E-12 (*0.000000000001) - Got expected result ("7.6"). 
    [pass] 2CoordsToDistanceData (derived test) - 2.0E-12 2.0E-12 4.0E-12 4.0E-12 (*0.000000000001) - Got expected result ("2.83"). 
    [pass] 2CoordsToDistanceData (derived test) - 2.0E-12 2.0E-12 5.4E-12 8.8E-12 (*0.000000000001) - Got expected result ("7.6"). 
    [pass] 2CoordsToDistanceData (derived test) - -1.0E-11 -1.0E-11 -8.0E-12 -8.0E-12 (*0.000000000001) - Got expected result ("2.83"). 
    [pass] 2CoordsToDistanceData (derived test) - -1.0E-11 -1.0E-11 -6.6E-12 -3.2E-12 (*0.000000000001) - Got expected result ("7.6"). 
    [pass] 2CoordsToDistanceData (derived test) - 0 0 2.0E-15 2.0E-15 (*0.000000000000001) - Got expected result ("2.83"). 
    [pass] 2CoordsToDistanceData (derived test) - 0 0 3.4E-15 6.8E-15 (*0.000000000000001) - Got expected result ("7.6"). 
    [pass] 2CoordsToDistanceData (derived test) - 2.0E-15 2.0E-15 4.0E-15 4.0E-15 (*0.000000000000001) - Got expected result ("2.83"). 
    [pass] 2CoordsToDistanceData (derived test) - 2.0E-15 2.0E-15 5.4E-15 8.8E-15 (*0.000000000000001) - Got expected result ("7.6"). 
    [pass] 2CoordsToDistanceData (derived test) - -1.0E-14 -1.0E-14 -8.0E-15 -8.0E-15 (*0.000000000000001) - Got expected result ("2.83"). 
    [pass] 2CoordsToDistanceData (derived test) - -1.0E-14 -1.0E-14 -6.6E-15 -3.2E-15 (*0.000000000000001) - Got expected result ("7.6"). 
    [pass] 2CoordsToDistanceData (derived test) - 0 0 2.0E-18 2.0E-18 (*0.000000000000000001) - Got expected result ("2.83"). 
    [pass] 2CoordsToDistanceData (derived test) - 0 0 3.4E-18 6.8E-18 (*0.000000000000000001) - Got expected result ("7.6"). 
    [pass] 2CoordsToDistanceData (derived test) - 2.0E-18 2.0E-18 4.0E-18 4.0E-18 (*0.000000000000000001) - Got expected result ("2.83"). 
    [pass] 2CoordsToDistanceData (derived test) - 2.0E-18 2.0E-18 5.4E-18 8.8E-18 (*0.000000000000000001) - Got expected result ("7.6"). 
    [pass] 2CoordsToDistanceData (derived test) - -1.0E-17 -1.0E-17 -8.0E-18 -8.0E-18 (*0.000000000000000001) - Got expected result ("2.83"). 
    [pass] 2CoordsToDistanceData (derived test) - -1.0E-17 -1.0E-17 -6.6E-18 -3.2E-18 (*0.000000000000000001) - Got expected result ("7.6"). 
    [pass] 2CoordsToDistanceData (derived test) - 0 0 2.0E-21 2.0E-21 (*0.000000000000000000001) - Got expected result ("2.83"). 
    [pass] 2CoordsToDistanceData (derived test) - 0 0 3.4E-21 6.8E-21 (*0.000000000000000000001) - Got expected result ("7.6"). 
    [pass] 2CoordsToDistanceData (derived test) - 2.0E-21 2.0E-21 4.0E-21 4.0E-21 (*0.000000000000000000001) - Got expected result ("2.83"). 
    [pass] 2CoordsToDistanceData (derived test) - 2.0E-21 2.0E-21 5.4E-21 8.8E-21 (*0.000000000000000000001) - Got expected result ("7.6"). 
    [pass] 2CoordsToDistanceData (derived test) - -1.0E-20 -1.0E-20 -8.0E-21 -8.0E-21 (*0.000000000000000000001) - Got expected result ("2.83"). 
    [pass] 2CoordsToDistanceData (derived test) - -1.0E-20 -1.0E-20 -6.6E-21 -3.2E-21 (*0.000000000000000000001) - Got expected result ("7.6"). 
    [pass] 2CoordsToDistanceData (derived test) - 0 0 2.0E-24 2.0E-24 (*0.000000000000000000000001) - Got expected result ("2.83"). 
    [pass] 2CoordsToDistanceData (derived test) - 0 0 3.4E-24 6.8E-24 (*0.000000000000000000000001) - Got expected result ("7.6"). 
    [pass] 2CoordsToDistanceData (derived test) - 2.0E-24 2.0E-24 4.0E-24 4.0E-24 (*0.000000000000000000000001) - Got expected result ("2.83"). 
    [pass] 2CoordsToDistanceData (derived test) - 2.0E-24 2.0E-24 5.4E-24 8.8E-24 (*0.000000000000000000000001) - Got expected result ("7.6"). 
    [pass] 2CoordsToDistanceData (derived test) - -1.0E-23 -1.0E-23 -8.0E-24 -8.0E-24 (*0.000000000000000000000001) - Got expected result ("2.83"). 
    [pass] 2CoordsToDistanceData (derived test) - -1.0E-23 -1.0E-23 -6.6E-24 -3.2E-24 (*0.000000000000000000000001) - Got expected result ("7.6"). 
    [pass] 2CoordsToDistanceData (derived test) - 0 0 2.0E-27 2.0E-27 (*0.000000000000000000000000001) - Got expected result ("2.83"). 
    [pass] 2CoordsToDistanceData (derived test) - 0 0 3.4E-27 6.8E-27 (*0.000000000000000000000000001) - Got expected result ("7.6"). 
    [pass] 2CoordsToDistanceData (derived test) - 2.0E-27 2.0E-27 4.0E-27 4.0E-27 (*0.000000000000000000000000001) - Got expected result ("2.83"). 
    [pass] 2CoordsToDistanceData (derived test) - 2.0E-27 2.0E-27 5.4E-27 8.8E-27 (*0.000000000000000000000000001) - Got expected result ("7.6"). 
    [pass] 2CoordsToDistanceData (derived test) - -1.0E-26 -1.0E-26 -8.0E-27 -8.0E-27 (*0.000000000000000000000000001) - Got expected result ("2.83"). 
    [pass] 2CoordsToDistanceData (derived test) - -1.0E-26 -1.0E-26 -6.6E-27 -3.2E-27 (*0.000000000000000000000000001) - Got expected result ("7.6"). 
    [pass] 2CoordsToDistanceData (derived test) - 0 0 2.0E-30 2.0E-30 (*0.000000000000000000000000000001) - Got expected result ("2.83"). 
    [pass] 2CoordsToDistanceData (derived test) - 0 0 3.4E-30 6.8E-30 (*0.000000000000000000000000000001) - Got expected result ("7.6"). 
    [pass] 2CoordsToDistanceData (derived test) - 2.0E-30 2.0E-30 4.0E-30 4.0E-30 (*0.000000000000000000000000000001) - Got expected result ("2.83"). 
    [pass] 2CoordsToDistanceData (derived test) - 2.0E-30 2.0E-30 5.4E-30 8.8E-30 (*0.000000000000000000000000000001) - Got expected result ("7.6"). 
    [pass] 2CoordsToDistanceData (derived test) - -1.0E-29 -1.0E-29 -8.0E-30 -8.0E-30 (*0.000000000000000000000000000001) - Got expected result ("2.83"). 
    [pass] 2CoordsToDistanceData (derived test) - -1.0E-29 -1.0E-29 -6.6E-30 -3.2E-30 (*0.000000000000000000000000000001) - Got expected result ("7.6"). 
    [pass] 2CoordsToDistanceData (derived test) - 0 0 2.0E-33 2.0E-33 (*0.000000000000000000000000000000001) - Got expected result ("2.83"). 
    [pass] 2CoordsToDistanceData (derived test) - 0 0 3.4E-33 6.8E-33 (*0.000000000000000000000000000000001) - Got expected result ("7.6"). 
    [pass] 2CoordsToDistanceData (derived test) - 2.0E-33 2.0E-33 4.0E-33 4.0E-33 (*0.000000000000000000000000000000001) - Got expected result ("2.83"). 
    [pass] 2CoordsToDistanceData (derived test) - 2.0E-33 2.0E-33 5.4E-33 8.8E-33 (*0.000000000000000000000000000000001) - Got expected result ("7.6"). 
    [pass] 2CoordsToDistanceData (derived test) - -1.0E-32 -1.0E-32 -8.0E-33 -8.0E-33 (*0.000000000000000000000000000000001) - Got expected result ("2.83"). 
    [pass] 2CoordsToDistanceData (derived test) - -1.0E-32 -1.0E-32 -6.6E-33 -3.2E-33 (*0.000000000000000000000000000000001) - Got expected result ("7.6"). 
    [pass] 2CoordsToDistanceData (derived test) - 0 0 2.0E-36 2.0E-36 (*0.000000000000000000000000000000000001) - Got expected result ("2.83"). 
    [pass] 2CoordsToDistanceData (derived test) - 0 0 3.4E-36 6.8E-36 (*0.000000000000000000000000000000000001) - Got expected result ("7.6"). 
    [pass] 2CoordsToDistanceData (derived test) - 2.0E-36 2.0E-36 4.0E-36 4.0E-36 (*0.000000000000000000000000000000000001) - Got expected result ("2.83"). 
    [pass] 2CoordsToDistanceData (derived test) - 2.0E-36 2.0E-36 5.4E-36 8.8E-36 (*0.000000000000000000000000000000000001) - Got expected result ("7.6"). 
    [pass] 2CoordsToDistanceData (derived test) - -1.0E-35 -1.0E-35 -8.0E-36 -8.0E-36 (*0.000000000000000000000000000000000001) - Got expected result ("2.83"). 
    [pass] 2CoordsToDistanceData (derived test) - -1.0E-35 -1.0E-35 -6.6E-36 -3.2E-36 (*0.000000000000000000000000000000000001) - Got expected result ("7.6"). 
    [pass] 2CoordsToDistanceData (derived test) - 0 0 2.0E-39 2.0E-39 (*0.000000000000000000000000000000000000001) - Got expected result ("2.83"). 
    [pass] 2CoordsToDistanceData (derived test) - 0 0 3.4E-39 6.8E-39 (*0.000000000000000000000000000000000000001) - Got expected result ("7.6"). 
    [pass] 2CoordsToDistanceData (derived test) - 2.0E-39 2.0E-39 4.0E-39 4.0E-39 (*0.000000000000000000000000000000000000001) - Got expected result ("2.83"). 
    [pass] 2CoordsToDistanceData (derived test) - 2.0E-39 2.0E-39 5.4E-39 8.8E-39 (*0.000000000000000000000000000000000000001) - Got expected result ("7.6"). 
    [pass] 2CoordsToDistanceData (derived test) - -1.0E-38 -1.0E-38 -8.0E-39 -8.0E-39 (*0.000000000000000000000000000000000000001) - Got expected result ("2.83"). 
    [pass] 2CoordsToDistanceData (derived test) - -1.0E-38 -1.0E-38 -6.6E-39 -3.2E-39 (*0.000000000000000000000000000000000000001) - Got expected result ("7.6"). 
    [pass] 2CoordsToDistanceData (derived test) - 0 0 2.0E-42 2.0E-42 (*0.000000000000000000000000000000000000000001) - Got expected result ("2.83"). 
    [pass] 2CoordsToDistanceData (derived test) - 0 0 3.4E-42 6.8E-42 (*0.000000000000000000000000000000000000000001) - Got expected result ("7.6"). 
    [pass] 2CoordsToDistanceData (derived test) - 2.0E-42 2.0E-42 4.0E-42 4.0E-42 (*0.000000000000000000000000000000000000000001) - Got expected result ("2.83"). 
    [pass] 2CoordsToDistanceData (derived test) - 2.0E-42 2.0E-42 5.4E-42 8.8E-42 (*0.000000000000000000000000000000000000000001) - Got expected result ("7.6"). 
    [pass] 2CoordsToDistanceData (derived test) - -1.0E-41 -1.0E-41 -8.0E-42 -8.0E-42 (*0.000000000000000000000000000000000000000001) - Got expected result ("2.83"). 
    [pass] 2CoordsToDistanceData (derived test) - -1.0E-41 -1.0E-41 -6.6E-42 -3.2E-42 (*0.000000000000000000000000000000000000000001) - Got expected result ("7.6"). 
    [pass] 2CoordsToDistanceData (derived test) - 0 0 2.0E-45 2.0E-45 (*0.000000000000000000000000000000000000000000001) - Got expected result ("2.83"). 
    [pass] 2CoordsToDistanceData (derived test) - 0 0 3.4E-45 6.8E-45 (*0.000000000000000000000000000000000000000000001) - Got expected result ("7.6"). 
    [pass] 2CoordsToDistanceData (derived test) - 2.0E-45 2.0E-45 4.0E-45 4.0E-45 (*0.000000000000000000000000000000000000000000001) - Got expected result ("2.83"). 
    [pass] 2CoordsToDistanceData (derived test) - 2.0E-45 2.0E-45 5.4E-45 8.8E-45 (*0.000000000000000000000000000000000000000000001) - Got expected result ("7.6"). 
    [pass] 2CoordsToDistanceData (derived test) - -1.0E-44 -1.0E-44 -8.0E-45 -8.0E-45 (*0.000000000000000000000000000000000000000000001) - Got expected result ("2.83"). 
    [pass] 2CoordsToDistanceData (derived test) - -1.0E-44 -1.0E-44 -6.6E-45 -3.2E-45 (*0.000000000000000000000000000000000000000000001) - Got expected result ("7.6"). 
    [pass] 2CoordsToDistanceData (derived test) - 0 0 2.0E-48 2.0E-48 (*0.000000000000000000000000000000000000000000000001) - Got expected result ("2.83"). 
    [pass] 2CoordsToDistanceData (derived test) - 0 0 3.4E-48 6.8E-48 (*0.000000000000000000000000000000000000000000000001) - Got expected result ("7.6"). 
    [pass] 2CoordsToDistanceData (derived test) - 2.0E-48 2.0E-48 4.0E-48 4.0E-48 (*0.000000000000000000000000000000000000000000000001) - Got expected result ("2.83"). 
    [pass] 2CoordsToDistanceData (derived test) - 2.0E-48 2.0E-48 5.4E-48 8.8E-48 (*0.000000000000000000000000000000000000000000000001) - Got expected result ("7.6"). 
    [pass] 2CoordsToDistanceData (derived test) - -1.0E-47 -1.0E-47 -8.0E-48 -8.0E-48 (*0.000000000000000000000000000000000000000000000001) - Got expected result ("2.83"). 
    [pass] 2CoordsToDistanceData (derived test) - -1.0E-47 -1.0E-47 -6.6E-48 -3.2E-48 (*0.000000000000000000000000000000000000000000000001) - Got expected result ("7.6"). 
    [pass] 2CoordsToDistanceData (derived test) - 0 0 2.0E-51 2.0E-51 (*0.000000000000000000000000000000000000000000000000001) - Got expected result ("2.83"). 
    [pass] 2CoordsToDistanceData (derived test) - 0 0 3.4E-51 6.8E-51 (*0.000000000000000000000000000000000000000000000000001) - Got expected result ("7.6"). 
    [pass] 2CoordsToDistanceData (derived test) - 2.0E-51 2.0E-51 4.0E-51 4.0E-51 (*0.000000000000000000000000000000000000000000000000001) - Got expected result ("2.83"). 
    [pass] 2CoordsToDistanceData (derived test) - 2.0E-51 2.0E-51 5.4E-51 8.8E-51 (*0.000000000000000000000000000000000000000000000000001) - Got expected result ("7.6"). 
    [pass] 2CoordsToDistanceData (derived test) - -1.0E-50 -1.0E-50 -8.0E-51 -8.0E-51 (*0.000000000000000000000000000000000000000000000000001) - Got expected result ("2.83"). 
    [pass] 2CoordsToDistanceData (derived test) - -1.0E-50 -1.0E-50 -6.6E-51 -3.2E-51 (*0.000000000000000000000000000000000000000000000000001) - Got expected result ("7.6"). 
    [pass] rightTriangleGetAdjacentFromAngleAndOpposite - 45 - Got expected result ("2"). 
    [pass] rightTriangleGetAdjacentFromAngleAndOpposite - 20 - Got expected result ("5.49"). 
    [pass] Unit example test1 - Yay
    [pass] Unit example test2 - Wheee
    [pass] Unit example duplication - Last entry wins.
    [pass] Stub test with " in it - This test is used for reference.
    [pass] Did the stub test get referenced correctly? - Got expected result ("pass"). 
    
    Pass=600, Warn=2, Fail=0

