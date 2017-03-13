# Cache

is the first step in bringing scalability to less powerful devices.
As this evolves, this document will be the single source of truth for how to use cache.

## How to use cache

### Enaling it

It's enabled by default at install/re-install time.

```
cd ~/.achel/repos/achel
./install.sh
manageAchel repoReinstall
```

Note: Developers may not want it enabled at all, see "Turning cache off" below.

### Clearing it

Context: Specific to app/profile.

    manageAchel cacheClear doneit

Will clear cache for the `doneit` app.

For more information type `manageAchel cacheClear`.

### Turning cache off

Context: System wide.

    manageAchel cacheOff

### Turning cache on

Context: System wide.

    manageAchel cacheOn

## Why would you want it on or off?

The normal use-case is now with cache enabled. Therefore there are two reasons for turning on cache

* You are a developer rapidly adding/removing features to/from an app.
* You have a cache-specific bug that you have reported as described in the [Reporting bugs](https://github.com/ksandom/achel/blob/master/docs/reportingBugs.md) section.

## How it works

* Before much logic is loaded, all available cache will be loaded from the `cache` directory located in the profile directory for the current app.
* If there is enough cache available
 * Execution begins.
* If there is not enough cache available
 * All macros will be found and loaded.
 * Execution begins.
 * Cache is persisted to disk unless it has been disabled as described in "Turning cache off".
* If there are additions to the FileListCache, it will be persisted to disk now.

## What is cached

* CacheUnitTest.cache.json - This is the result of a unit test. It will be cleaned up in a future version. You can safely ignore it.
* Events.cache.json - Registrations for events (eg using --registerForEvent). This is one less reason Features need to be processed at startup.
* Features.cache.json - A catalog of all features and where to load them from when needed.
* FeatureAliases.cache.json - This is almost obsolete due to Features.cache.json. It will be removed in a future version.
* FileListCache.cache.json - Every time a file list is requested, and achel doesn't already know about it, it's added here. This is a massive performance boost, but comes at the cost of not detecting new or removed Features/Macros. If you are developing new features, `manageAchel cacheClear` will be your friend.
* MacroListCache.cache.json - This is almost obsolete due to Features.cache.json. It will be removed in a future version.
* Tags.cache.json - A list of tags and what features they match against.
