# ExitStatus

ExitStatus helps you give feedback to scripts that call your code.

## Overview

### Normal flow
* `--setFailureStatus` - Something went wrong, let's alert the calling script. This will be your most common usage. It will happen regardless of any other value because it is the most critical value.
* `--setWarningStatus` - You need to warn the script of something in a non-critical way. Will only happen if the current status is success.

### Force for if you need to reset the status
* `--forceFailureStatus` - Force to failure status.
* `--forceWarningStatus` - Force to warning status.
* `--forceSuccessStatus` - Force to success status.

In each case, the applicable action will be taken straightaway, but it will only be accessible to the calling script when Achel terminates.

## Force vs Set

set eg `--setWarningStatus` is the normal use case. Eg if you have a failure, and then a warning, the exit code should remain reflecting the failure because that is the more severe problem. While if it was success, it should be set to warning. You would force if you definitely wanted to warn, even though a failure had previously occured.

Force was a logical abstraction. I can't currently think of any reason for you to use them day to day, but they are there if you need them. If a sensible use for them does not appear soon, I will remove them from general use.

