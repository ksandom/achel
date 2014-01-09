# Codes

Provides ANSI escape codes for interacting with the terminal.

## How setup works

Oninstall all possible combination of colours are generated and then undesireable combinations are removed. Note that this is subjective. So to prevent every colour combination from being removed by variouis people, the current recommendation is to remove combinations that are technically impossible to see, or technically difficult to see. eg:

* black on black
* dark green on dark cyan
* blinking

At some point more combinations will be made available again once the restricted set can be accessed separately. This is because the computer needs to be able to randomly choose readable combinations for some applications.

## Using it

Simply embed the color code you want like this (normally you'd do it in a template)

    debug 0,~!Color,blue!~blah~!Color,default!~ text

Note that I've reverted the font back to default at the end to bring it back to a known state.

You can find what colours are available to you with `--listColors`.
