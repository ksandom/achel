# Faucet unit tests

The new way to test faucets in a reliable way.

# What is trying to be solved

Fix the shortcommings of the old way:

* slow.
* heavy coding the tests.
* unreliable.
* inconsistent.

New way

* parallel.
* consistent.
* hard stuff abstracted away.

# Dependencies

* AchelFaucets
* Unit

# Components

* deliverUntilDone
  * Take a category name. Exit when that category no longer contains any items.
  * Timeout after a configurable time. This is not intended to be readily accessible to users, but could be tweaked if needed.
* unitFaucet
  * types
    * expect
    * expectNot
  * actions
    * On create
      * Record details in a specific category.
    * On result
      * Store results for the tests.
      * Delete details from the specific category.
  * assumptions
    * inherit test details.
* defineFaucetTest
  * abstract hard stuff
    * create meta faucet.
    * anchor each unitFaucet?
  * record information about the tests.
* defineFaucetTestSet
  * abstract hard stuff
    * contain everything in a metafaucet for easy cleanup?
    * trigger deliverUntilDone.
    * return results of each test.

# Example code

## Example tests

```
defineFaucetTestSet
  defineFaucetTest The first test,
    createTestFaucet result1,Should be happy,expect,yay
    deliver result1,,yay

  defineFaucetTest Second test,
    createTestFaucet result1,Should be happy,expect,yay
    createTestFaucet result2,Should be sad,expect,boo
    deliver result1,,yay
    deliver result2,,boo
```
