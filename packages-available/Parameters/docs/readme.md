# Parameters

This package is currently for unit tests testing macro parameters. No user or developer content is here.

You can run the unit tests with

    $ achel --unitTests=parameters

The second reason for this change is that the early parameter scoping was very liberal, which made making concise unit testing very easy. Now that the scoping is more precise, real macros must be used to each the parameters.
