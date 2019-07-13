# Balance

Balance was my first attempt at taking multiple inputs and outputting multiple outputs to establish and maintain state.

## Challenges

As with many prototypes, I made the wrong things easy, and the wrong things hard. Therefore this will soon be replaced with BalanceM2N.

## Expos

Expos are for making the controls more, or less sensitive in the middle. The final deflection at the ends will still be the same; what changes is where the bias of the control happens compared to a 1:1 mapping. I've implemented this out of curiosity. While makes a lot of sense for human input, it's unlikely to be that useful for algorythms.

### Input vs Output

TODO write this.

Eg

### Expo 1 (1:1)

```
      Input            Output
|           *   |           *
|         *     |         *
|       *       |       *
|     *         |     *
|   *           |   *
| *             | *
|______________ |_______________
```

### Expo 0.5

```
      Input            Output
|           *   |           *
|         *     |          *
|       *       |         *
|     *         |        *
|   *           |      *
| *             | *
|______________ |_______________
```

### Expo 0.125

```
      Input            Output
|           *   |           *
|         *     |           *
|       *       |          *
|     *         |         *
|   *           |       *
| *             | * *
|______________ |_______________
```

### Expo 2

```
      Input            Output
|           *   |         * *
|         *     |     *
|       *       |   *
|     *         |  *
|   *           | *
| *             | *
|______________ |_______________
```

### Expo 1.5

```
      Input            Output
|           *   |           *
|         *     |      *
|       *       |    *
|     *         |   *
|   *           |  *
| *             | *
|______________ |_______________
```

### Expo 0
There might be a legitimate use case for this, but understand it's an expensive way to hardcode a value.

```
      Input            Output
|           *   | * * * * * *
|         *     |
|       *       |
|     *         |
|   *           |
| *             |
|______________ |_______________
```

