# PID controller

* P - Proportional (Present)
* I - Integral (Past)
* D - Derivative (Future)

## Tunables

* Gains
  * `kP` - Get to the goal faster.
  * `kI` - Settle on the goal,
  * `kD` - Try not to overshoot.
* Input
  * `maxObserableError-P` - 
* History
  * `numberOfPoints` - How many points to keep. This is mostly used by I, and a little by D.
