# Cheet sheet for debugging

## Input codes

Send a ping to see if you have communication working. Note that is not intended as a keep alive, but would be a low impact way of achieving it if there was a need for it for some other, external networking layer.

    {"command":"ping"}
    {"message": "Returned from requested ping.", "shortMessage": "", "command": "pong", "level": "0"}

This will set pin 1 to 45.

    {"command":"setData","data":{"1":"45"}}


