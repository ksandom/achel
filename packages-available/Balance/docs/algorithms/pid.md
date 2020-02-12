# PID controller

* P - Proportional (Present)
* W - Wandering (Past)
* I - Integral (Past)
* D - Derivative (Future)

If you're familiar with a PID controller, you'll see there is an elephant in the room. The Wandering component is my own deviation from how a PID controller is supposed to work that I find works better for my needs in place of the Integral component. If it doesn't suit your needs, just set its gain to 0.

## Tunables

* Gains
  * `kP` - Get to the goal faster.
  * `kW` - Settle on the goal by constantly adjusting the offset.
  * `kI` - Settle on the goal, based on how far off it has been in the past.
  * `kD` - Try not to overshoot.
* Input
  * `maxObserableError-P` - \[Not implemented.\] Anything beyond this value is simply considered the maximum value.
* History
  * `numberOfPoints` - \[Not implemented.\] How many points to keep. This is mostly used by I, and a little by D.
  * `wanderingTime` - How long it takes to wander to the limit. A short time, like 5 seconds will be quite quick to adapt to inaccuracies. A longer time like 60 seconds will be much much more gentle, and have finer grained control.
