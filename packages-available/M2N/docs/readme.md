# M2N

This is for managing the ability for connecting many users to many things.

The idea is that you `--selectUsers` and `--selectThings` and then `--join` them together. For M2N to know about what can be `--join`ed together, they must be registered with `--registerForM2n`.

TODO write more detail.

## Users and things

Achel provides a very powerful way of connecting arbitrary things together. This can get messey/confusing very quickly. Users and things are a way of classify what you want to connect together to bring some sanity to managing it in the real world.

For the use case that will be relevant to most people, it will look like this

* Users are you. It could be the original terminal window, or subsequent terminal windows.
* Things are what you want to connect users to. These could be ssh sessions, or an application like an autopilot.

You can connect a single user to multiple things (think cluster SSH), and multiple users can be connected to a single thing (sharing control).
