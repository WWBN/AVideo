# Changelog

## [3.1.0] - 2021-11-04

### Added

- Support promises and `abstract-level` ([`3074af8`](https://github.com/Level/concat-iterator/commit/3074af8)) (Vincent Weevers)

## [3.0.0] - 2021-04-08

_If you are upgrading: please see [`UPGRADING.md`](UPGRADING.md)._

### Changed

- **Breaking:** modernize syntax and bump standard ([`dd37269`](https://github.com/Level/concat-iterator/commit/dd37269)) ([Level/community#98](https://github.com/Level/community/issues/98)) (Vincent Weevers).

## [2.0.1] - 2019-04-01

### Changed

- Upgrade `standard` devDependency from `^11.0.1` to `^12.0.1` ([`6b957cf`](https://github.com/Level/concat-iterator/commit/6b957cf)) ([**@vweevers**](https://github.com/vweevers))
- Apply common project tweaks ([#12](https://github.com/Level/concat-iterator/issues/12), [#13](https://github.com/Level/concat-iterator/issues/13), [`0c1d4e0`](https://github.com/Level/concat-iterator/commit/0c1d4e0)) ([**@vweevers**](https://github.com/vweevers))

### Added

- Add `nyc` and `coveralls` ([#11](https://github.com/Level/concat-iterator/issues/11), [`a3e312a`](https://github.com/Level/concat-iterator/commit/a3e312a)) ([**@ralphtheninja**](https://github.com/ralphtheninja), [**@vweevers**](https://github.com/vweevers))

### Removed

- Remove node 9 from travis ([`0347b82`](https://github.com/Level/concat-iterator/commit/0347b82)) ([**@ralphtheninja**](https://github.com/ralphtheninja))

## [2.0.0] - 2018-06-27

_If you are upgrading: please see [`UPGRADING.md`](UPGRADING.md)._

### Changed

- Detect end by checking key and value ([**@vweevers**](https://github.com/vweevers))
- Use `level` in example instead of `leveldown` ([**@ralphtheninja**](https://github.com/ralphtheninja))
- End iterator if next errors ([**@ralphtheninja**](https://github.com/ralphtheninja))

### Removed

- Remove custom `nextTick` parameter ([**@ralphtheninja**](https://github.com/ralphtheninja))

### Added

- Add `homepage` and `repository` to `package.json` ([**@ralphtheninja**](https://github.com/ralphtheninja))
- Add `UPGRADING.md` ([**@ralphtheninja**](https://github.com/ralphtheninja))

## [1.0.0] - 2018-06-24

:seedling: Initial release.

[3.1.0]: https://github.com/Level/concat-iterator/releases/tag/v3.1.0

[3.0.0]: https://github.com/Level/concat-iterator/releases/tag/v3.0.0

[2.0.1]: https://github.com/Level/concat-iterator/releases/tag/v2.0.1

[2.0.0]: https://github.com/Level/concat-iterator/releases/tag/v2.0.0

[1.0.0]: https://github.com/Level/concat-iterator/releases/tag/v1.0.0
