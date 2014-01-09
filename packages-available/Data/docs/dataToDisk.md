# Data-Disk interactoin

Data-disk interaction is an important consideration for the performance of an application and there are a few ways of achieving it, each with different pros and cons.

## Different methods

### Collection

Features

* `collectionLoad`

This is the recommened way of manipulating data when you want it to be persistent and to be able to load it easily.

Unless explicity over-ridden it will only load the data once, and save the data once per session, so it should perform well. Therefore it should perform the best of any of the methods currently avaiable.

It's stored in ACHELHOME/data .

[Read more](https://github.com/ksandom/achel/blob/master/packages-available/Collection/docs/readme.md)

### Data

Features

* `loadStoreFromData`
* `saveStoreToData`

This is what Collection uses to do its job. The advantage is that you have exact control over when data is loaded and saved. The disadvantage is that it is very easy to grow bugs when interacting with other scripts.

It's stored in ACHELHOME/data .

[Read more](https://github.com/ksandom/achel/blob/master/packages-available/Data/docs/readme.md)

### Data in an arbitrary location

Features

* `loadStoreFromFile`
* `saveStoreToFile`

The legitimate uses of this are rare and it is strongly discouraged. It's intended for importing and exporting data.

[Read more](https://github.com/ksandom/achel/blob/master/packages-available/Data/docs/readme.md)

### Config - *DEPRECATED*

Every entry in here is loaded every time every Achel application is loaded and therefore impacts the performance of every application. I'm in the process of migrating all my applications away from it. If you are using it, please do the same.

Any application should use `collectionLoad` instead, which will give the persistence when required, without impacting all the other applications. There are currently some valid system uses, which is what's keeping it around for now.

It's stored in ACHELHOME/config .

[Read more](https://github.com/ksandom/achel/blob/master/packages-available/Data/docs/readme.md)

## Performance

All of these methods are based on the [same code](https://github.com/ksandom/achel/blob/master/packages-available/Data/docs/readme.md). They all work by loading an entire copy of the data in memory, and by saving the entire data back to disk. In general you should only request the data (eg `collectionLoad`) in macros that need it. But this is especially true when working with larger data sets.