# Trig

Provides features for trigonometry.

There are many real world uses for this. Mine originate from wanting to calculate a heading between two points.

## Using it

* Make sure `Maths` and `Trig` are included via [repoParms](https://github.com/ksandom/achel/blob/master/docs/programming/creatingARepositoryWithProfiles.md#use-repoparmdefinepackages-to-create-a-profile). If BASE is included, it should be fine.
* Perform options as needed.

## A worked example

This is a simple calculation based on SohCahToa. More info [here](http://www.mathsisfun.com/algebra/trig-finding-angle-right-triangle.html).

    # Calculate the oposite angle of a right angle triangle. ~ example,trig
    
    # Input
    set Tmp,oppositeLength,2
    set Tmp,adjacentLength,3
    
    # Calculations
    basicMaths Tmp,atanInput,~!Tmp,oppositeLength!~,/,~!Tmp,adjacentLength!~
    atan Tmp,radianAngle,~!Tmp,atanInput!~
    radiansToDegrees Tmp,degreeAngle,~!Tmp,radianAngle!~
    
    # Our results are in ~!Tmp,radianAngle!~ and ~!Tmp,degreeAngle!~
    
    # Clean up
    unset Tmp,oppositeLength
    unset Tmp,adjacentLength
    unset Tmp,atanInput

In the example we

* Set some input.
* Do the calculations.
 * The input for atan needs to be the oppositeLength divided by the adjacentLength.
 * Running that value through atan gives us the angle in radians.
 * Running the angle through radiansToDegrees gives us the angle in degrees.
