# Outputs

Send data somewhere. This automatically happens when Achel terminates, but can also be invoked with `--outNow`. Most/all of this functionality should be available for influencing the resultSet, which is usually more useful.

This is currently made up of a few components that may get put into separate packages.

## Using it

* Make sure `Outputs` is included via [repoParms](https://github.com/ksandom/achel/blob/master/docs/programming/creatingARepositoryWithProfiles.md#use-repoparmdefinepackages-to-create-a-profile). If BASE is included, it should be fine.
* Do stuff that creates data to export.
* Export using one of the provided methods.

## json

Outputs the resultSet in json format.

See `achel --help=JsonOut` for full info. Or `achel --help=json` for a broader look at json features.

Of particular interest is `--jsonArray` which re-implements the behavior of a bug that was present in an earlier version of mass that some people relied on.

## null

Discards output.

See `achel --help=--null` for full info.

## string

Outputs the resultSet as a string. 

This is pretty much an array to string conversation with keys discarded and each entry being separated by `\n`. This idea currently doesn't work so well when there are nested arrays in the resultSet.

See `achel --help=String` for full info.
