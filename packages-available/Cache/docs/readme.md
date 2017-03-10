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

Developers may not want it enabled at all, see "Turning cache off" below.

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

## How it works

* Before much logic is loaded, all available cache will be loaded from the `cache` directory located in the profile directory for the current app.
* If there is enough cache available
 * Execution begins.
* If there is not enough cache available
 * All macros will be found and loaded.
 * Execution begins.
 * Cache is persisted to disk unless it has been disabled as described in "Turning cache off".
* If there are additions to the FileListCache, it will be persisted to disk now.

