# Index

For indexing stuff so that it can be quickly found later on.

## Important notes

* `--getIndexe` is not currently compatible with scoped variables such as `Me` and `Local`. Therefore it is recommended to store your indexes in a more permanent way such as Example or Blah, and unset them if you no longer need them. This does not mean you have to use a collection, but you can if you wish.

## How to use it

You have a resultSet.

Choose part of it and apply the index to that part

    manipulateItem hostName,^.*db.*,
    	addToIndex Example,index1

You may want add another group to the same index

    manipulateItem hostName,^.*app.*,
    	addToIndex Example,index1

Now you can do stuff with that index like get all the items from the resultSet that are referenced in that index.

    getIndexed Example,index1
