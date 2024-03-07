# streamroller Changelog

## [3.1.5](https://github.com/log4js-node/streamroller/milestone/30)

- [fix: tilde expansion for windows](https://github.com/log4js-node/streamroller/pull/165) - thanks [@lamweili](https://github.com/lamweili)
- [chore(deps-dev): updated dependencies](https://github.com/log4js-node/streamroller/pull/166) - thanks [@lamweili](https://github.com/lamweili)
  - chore(deps-dev): bump @commitlint/cli from 17.3.0 to 17.4.3
  - chore(deps-dev): bump @commitlint/config-conventional from 17.3.0 to 17.4.3
  - chore(deps-dev): bump @types/node from 8.11.18 to 8.13.0
  - chore(deps-dev): bump eslint from 8.30.0 to 8.34.0
  - chore(deps-dev): bump husky from 8.0.2 to 8.0.3
  - chore(deps-dev): updated package-lock.json

## [3.1.4](https://github.com/log4js-node/streamroller/milestone/29)

- [fix: addressed unhandled promise rejection when a file gets deleted in midst of rolling](https://github.com/log4js-node/streamroller/pull/160) - thanks [@lamweili](https://github.com/lamweili)
- [docs: updated repository url](https://github.com/log4js-node/streamroller/pull/158) - thanks [@lamweili](https://github.com/lamweili)
- [ci: replaced deprecated github set-output](https://github.com/log4js-node/streamroller/pull/159) - thanks [@lamweili](https://github.com/lamweili)
- [ci: added quotes](https://github.com/log4js-node/streamroller/pull/157) - thanks [@lamweili](https://github.com/lamweili)
- [chore(deps-dev): updated dependencies](https://github.com/log4js-node/streamroller/pull/161) - thanks [@lamweili](https://github.com/lamweili)
  - chore(deps-dev): bump @types/node from 18.11.9 to 18.11.18
  - chore(deps-dev): bump eslint from 8.28.0 to 8.30.0
  - chore(deps-dev): bump mocha from 10.1.0 to 10.2.0
  - chore(deps-dev): updated package-lock.json
- [chore(deps-dev): updated dependencies](https://github.com/log4js-node/streamroller/pull/156) - thanks [@lamweili](https://github.com/lamweili)
  - chore(deps-dev): bump @commitlint/cli from 17.1.2 to 17.3.0
  - chore(deps-dev): bump @commitlint/config-conventional from 17.1.0 to 17.3.0
  - chore(deps-dev): bump @types/node from 18.7.23 to 18.11.9
  - chore(deps-dev): bump eslint from 8.24.0 to 8.28.0
  - chore(deps-dev): bump husky from 8.0.1 to 8.0.2
  - chore(deps-dev): bump mocha from 10.0.0 to 10.1.0
  - chore(deps-dev): updated package-lock.json

## [3.1.3](https://github.com/log4js-node/streamroller/milestone/28)

- [ci: manually downgrade dev dependencies for older versions](https://github.com/log4js-node/streamroller/pull/153) - thanks [@lamweili](https://github.com/lamweili)
- [ci: removed scheduled job from codeql and separated npm audit](https://github.com/log4js-node/streamroller/pull/152) - thanks [@lamweili](https://github.com/lamweili)
- [ci: updated codeql from v1 to v2](https://github.com/log4js-node/streamroller/pull/151) - thanks [@lamweili](https://github.com/lamweili)
- [chore(deps): updated dependencies](https://github.com/log4js-node/streamroller/pull/154) - thanks [@lamweili](https://github.com/lamweili)
  - chore(deps-dev): bump @commitlint/cli from 17.0.3 to 17.1.2
  - chore(deps-dev): bump @commitlint/config-conventional from 17.0.3 to 17.1.0
  - chore(deps-dev): bump @types/node from 18.0.6 to 18.7.23
  - chore(deps-dev): bump eslint from 6.8.0 to 8.24.0
  - chore(deps-dev): bump mocha from 7.2.0 to 10.0.0
  - chore(deps): bump date-format from 4.0.13 to 4.0.14
  - chore(deps): updated package-lock.json

## [3.1.2](https://github.com/log4js-node/streamroller/milestone/27)

- [refactor: support older Node.js versions](https://github.com/log4js-node/streamroller/pull/147) - thanks [@lamweili](https://github.com/lamweili)
- [docs: renamed peteriman to lamweili](https://github.com/log4js-node/streamroller/pull/144) - thanks [@lamweili](https://github.com/lamweili)
- [ci: added tests for Node.js 8.x, 10.x, 18.x](https://github.com/log4js-node/streamroller/pull/148) - thanks [@lamweili](https://github.com/lamweili)
- [chore(deps): bump date-format from 4.0.11 to 4.0.13](https://github.com/log4js-node/streamroller/pull/150) - thanks [@lamweili](https://github.com/lamweili)
- [chore(deps-dev): updated dependencies](https://github.com/log4js-node/streamroller/pull/146) - thanks [@lamweili](https://github.com/lamweili)
  - chore(deps-dev): bump @commitlint/cli from 17.0.1 to 17.0.3
  - chore(deps-dev): bump @commitlint/config-conventional from 17.0.2 to 17.0.3
  - chore(deps-dev): bump @types/node from 17.0.38 to 18.0.6
  - chore(deps-dev): bump eslint from 8.16.0 to 8.20.0
  - chore(deps-dev): updated package-lock.json
- [chore(deps-dev): updated dependencies](https://github.com/log4js-node/streamroller/pull/143) - thanks [@lamweili](https://github.com/lamweili)
  - chore(deps-dev): bump @commitlint/cli from 17.0.0 to 17.0.1
  - chore(deps-dev): bump @commitlint/config-conventional 17.0.0 to 17.0.2
  - chore(deps-dev): bump @types/node from 17.0.35 to 17.0.38
  - chore(deps): bump date-format 4.0.10 to 4.0.11
  - chore(deps): updated package-lock.json

## [3.1.1](https://github.com/log4js-node/streamroller/milestone/26)

- [fix: fs.appendFileSync should use flag instead of flags](https://github.com/log4js-node/streamroller/pull/141) - thanks [@lamweili](https://github.com/lamweili)

## [3.1.0](https://github.com/log4js-node/streamroller/milestone/25)

- [feat: tilde expansion for filename](https://github.com/log4js-node/streamroller/pull/135) - thanks [@lamweili](https://github.com/lamweili)
- [fix: better file validation](https://github.com/log4js-node/streamroller/pull/134) - thanks [@lamweili](https://github.com/lamweili)
- [chore(deps-dev): updated dependencies](https://github.com/log4js-node/streamroller/pull/140) - thanks [@lamweili](https://github.com/lamweili)
  - chore(deps-dev): bump @commitlint/cli from 16.3.0 to 17.0.0
  - chore(deps-dev): bump @commitlint/config-conventional from 16.2.4 to 17.0.0
  - chore(deps-dev): bump @types/node from 17.0.33 to 17.0.35
  - chore(deps-dev): bump eslint from 8.15.0 to 8.16.0
  - chore(deps): updated package-lock.json

## [3.0.9](https://github.com/log4js-node/streamroller/milestone/24)

- [fix: maxSize=0 means no rolling](https://github.com/log4js-node/streamroller/pull/131) - thanks [@lamweili](https://github.com/lamweili)
- [chore(deps): updated dependencies](https://github.com/log4js-node/streamroller/pull/132) - thanks [@lamweili](https://github.com/lamweili)
  - chore(deps-dev): bump @commitlint/cli from 16.2.3 to 16.3.0
  - chore(deps-dev): bump @commitlint/config-conventional from 16.2.1 to 16.2.4
  - chore(deps-dev): bump @types/node from 17.0.26 to 17.0.33
  - chore(deps-dev): bump eslint from 8.14.0 to 8.15.0
  - chore(deps-dev): bump husky from 7.0.4 to 8.0.1
  - chore(deps-dev): bump mocha from 9.2.2 to 10.0.0
  - chore(deps): bump date-format from 4.0.9 to 4.0.10
  - chore(deps): updated package-lock.json

## [3.0.8](https://github.com/log4js-node/streamroller/milestone/23)

- [fix: concurrency issues when forked processes trying to roll same file](https://github.com/log4js-node/streamroller/pull/124) - thanks [@lamweili](https://github.com/lamweili)
  - [refactor: use writeStream.destroy() instead](https://github.com/log4js-node/streamroller/pull/125)
  - [refactor: use isCreated variable instead of e.code='EEXIST'](https://github.com/log4js-node/streamroller/pull/126)
- [chore(lint): added .eslintrc and fixed linting issues](https://github.com/log4js-node/streamroller/pull/123) - thanks [@lamweili](https://github.com/lamweili)
- [chore(deps): updated dependencies](https://github.com/log4js-node/streamroller/pull/127) - thanks [@lamweili](https://github.com/lamweili)
  - chore(deps-dev): bump @types/node from 17.0.24 to 17.0.26
  - chore(deps-dev): bump eslint from 8.13.0 to 8.14.0
  - chore(deps): bump date-format from 4.0.7 to 4.0.9
  - chore(deps): updated package-lock.json
- [chore(deps): updated dependencies](https://github.com/log4js-node/streamroller/pull/119) - thanks [@lamweili](https://github.com/lamweili)
  - chore(deps): bump fs-extra from 10.0.1 to 10.1.0
  - chore(deps): updated package-lock.json
  - revert: "[chore(dep): temporary fix for fs-extra issue (to be reverted when fs-extra patches it)](https://github.com/log4js-node/streamroller/pull/116)"

## [3.0.7](https://github.com/log4js-node/streamroller/milestone/22)

- [chore(deps): temporary fix for fs-extra issue (to be reverted when fs-extra patches it)](https://github.com/log4js-node/streamroller/pull/116) - thanks [@lamweili](https://github.com/lamweili)
- [chore(deps): updated dependencies](https://github.com/log4js-node/streamroller/pull/117) - thanks [@lamweili](https://github.com/lamweili)
  - chore(deps): bump date-format from 4.0.6 to 4.0.7
  - chore(deps): updated package-lock.json
- [chore(deps-dev): updated dependencies](https://github.com/log4js-node/streamroller/pull/113) - thanks [@lamweili](https://github.com/lamweili)
  - chore(deps-dev): bump @types/node from 17.0.23 to 17.0.24
  - chore(deps-dev): updated package-lock.json
- [chore(deps-dev): updated dependencies](https://github.com/log4js-node/streamroller/pull/112) - thanks [@lamweili](https://github.com/lamweili)
  - chore(deps-dev): bump @types/node from 17.0.22 to 17.0.23
  - chore(deps-dev): bump eslint from 8.11.0 to 8.13.0
  - chore(deps-dev): updated package-lock.json

## [3.0.6](https://github.com/log4js-node/streamroller/milestone/21)

- [chore(deps): updated dependencies](https://github.com/log4js-node/streamroller/pull/110) - thanks [@lamweili](https://github.com/lamweili)
  - chore(deps): bump debug from 4.3.3 to 4.3.4
  - chore(deps): bump date-format from 4.0.5 to 4.0.6
  - chore(deps-dev): bump @types/node from 17.0.21 to 17.0.22
  - chore(deps-dev): bump @commitlint/cli from 16.2.1 to 16.2.3
  - chore(deps): updated package-lock.json

## [3.0.5](https://github.com/log4js-node/streamroller/milestone/20)

- [fix: added filename validation](https://github.com/log4js-node/streamroller/pull/101) - thanks [@lamweili](https://github.com/lamweili)
- [docs: updated README.md with badges](https://github.com/log4js-node/streamroller/pull/105) - thanks [@lamweili](https://github.com/lamweili)
- [docs: updated README.md for DateRollingFileStream](https://github.com/log4js-node/streamroller/pull/106) - thanks [@lamweili](https://github.com/lamweili)
- [docs: added docs for istanbul ignore](https://github.com/log4js-node/streamroller/pull/107) - thanks [@lamweili](https://github.com/lamweili)
- [chore(deps): updated dependencies](https://github.com/log4js-node/streamroller/pull/109) - thanks [@lamweili](https://github.com/lamweili)
  - chore(deps-dev): eslint from 8.10.0 to 8.11.0
  - chore(deps-dev): mocha from 9.2.1 to 9.2.2
  - chore(deps): date-format from 4.0.4 to 4.0.5
  - chore(deps): updated package-lock.json

## [3.0.4](https://github.com/log4js-node/streamroller/milestone/19)

- [test: remove test file/folder remnants](https://github.com/log4js-node/streamroller/pull/99) - thanks [@lamweili](https://github.com/lamweili)

## [3.0.3](https://github.com/log4js-node/streamroller/milestone/18)

- [fix: backward compatibility for RollingFileWriteStream to recursively create directory](https://github.com/log4js-node/streamroller/pull/96) - thanks [@lamweili](https://github.com/lamweili)
- [test: 100% test coverage](https://github.com/log4js-node/streamroller/pull/94) - thanks [@lamweili](https://github.com/lamweili)
- [chore(deps): updated dependencies](https://github.com/log4js-node/streamroller/pull/97) - thanks [@lamweili](https://github.com/lamweili)
  - chore(deps-dev): bump @commitlint/cli from 16.1.0 to 16.2.1
  - chore(deps-dev): bump @commitlint/config-conventional from 16.0.0 to 16.2.1
  - chore(deps-dev): bump @types/node from 17.0.16 to 17.0.21
  - chore(deps-dev): bump eslint from 8.8.0 to 8.10.0
  - chore(deps-dev): bump mocha from 9.2.0 to 9.2.1
  - chore(deps): bump date-format from 4.0.3 to 4.0.4
  - chore(deps): bump fs-extra from 10.0.0 to 10.0.1
- [chore(deps): updated dependencies](https://github.com/log4js-node/streamroller/pull/95) - thanks [@lamweili](https://github.com/lamweili)
  - chore(deps-dev): bump @commitlint/cli from 16.0.2 to 16.1.0
  - chore(deps-dev): bump @types/node from 17.0.9 to 17.0.16
  - chore(deps-dev): bump eslint from 8.7.0 to 8.8.0
  - chore(deps-dev): bump proxyquire from 2.1.1 to 2.1.3
  - chore(deps): bump debug from 4.1.1 to 4.3.3
- [chore(deps): updated dependencies](https://github.com/log4js-node/streamroller/pull/92) - thanks [@lamweili](https://github.com/lamweili)
  - updated package-lock.json 
- [chore(deps): updated dependencies](https://github.com/log4js-node/streamroller/pull/91) - thanks [@lamweili](https://github.com/lamweili)
  - chore(deps-dev): bump mocha from 9.1.4 to 9.2.0

## [3.0.2](https://github.com/log4js-node/streamroller/milestone/17)

- [fix: changed default file modes from 0o644 to 0o600 for better security](https://github.com/log4js-node/streamroller/pull/87) - thanks [@lamweili](https://github.com/lamweili)
- [refactor: housekeeping for comments and .gitignore](https://github.com/log4js-node/streamroller/pull/89) - thanks [@lamweili](https://github.com/lamweili)
- [chore(deps): updated dependencies](https://github.com/log4js-node/streamroller/pull/88) - thanks [@lamweili](https://github.com/lamweili)
  - chore(deps-dev): bump caniuse-lite from 1.0.30001299 to 1.0.30001300
  - chore(deps-dev): bump electron-to-chromium from 1.4.45 to 1.4.47
  - chore(deps-dev): bump @types/node from 17.0.8 to 17.0.9
  - chore(deps-dev): bump eslint from 8.6.0 to 8.7.0
  - chore(deps-dev): bump mocha from 9.1.3 to 9.1.4
  - chore(deps): bump date-format from 4.0.2 to 4.0.3

## [3.0.1](https://github.com/log4js-node/streamroller/milestone/16)

- [build: not to publish misc files to NPM](https://github.com/log4js-node/streamroller/pull/82) - thanks [@lamweili](https://github.com/lamweili)
- chore(deps): updated dependencies - thanks [@lamweili](https://github.com/lamweili)
  - [chore(deps): bump date-format from 4.0.1 to 4.0.2](https://github.com/log4js-node/streamroller/pull/86)
  - [chore(deps-dev): bump electron-to-chromium from 1.4.44 to 1.4.45](https://github.com/log4js-node/streamroller/pull/81) 

## [3.0.0](https://github.com/log4js-node/streamroller/milestone/15)

- [feat: allow for 0 backups (only hot file)](https://github.com/log4js-node/streamroller/pull/74) - thanks [@lamweili](https://github.com/lamweili)
- [feat: exposed fileNameSep to be configurable](https://github.com/log4js-node/streamroller/pull/67) - thanks [@laidaxian](https://github.com/laidaxian)
  - [fix: for fileNameSep affecting globally](https://github.com/log4js-node/streamroller/pull/79) - thanks [@lamweili](https://github.com/lamweili)
- [fix: for mode to apply to compressed file](https://github.com/log4js-node/streamroller/pull/65) - thanks [@rnd-debug](https://github.com/rnd-debug)
- [fix: for extra separator in filenames](https://github.com/log4js-node/streamroller/pull/75) - thanks [@lamweili](https://github.com/lamweili)
- [refactor: moved to options.numBackups instead of options.daysToKeep](https://github.com/log4js-node/streamroller/pull/78) - thanks [@lamweili](https://github.com/lamweili)
- [test: improved test case performance for fakeNow](https://github.com/log4js-node/streamroller/pull/76) - thanks [@lamweili](https://github.com/lamweili)
- chore(deps-dev): updated dependencies - thanks <a href="https://github.com/lamweili">@lamweili</a></summary>
  - [chore(deps-dev): updated package.json](https://github.com/log4js-node/streamroller/pull/70)
    - chore(deps-dev): bump @commitlint/cli from 8.1.0 to 16.0.2
    - chore(deps-dev): bump @commitlint/config-conventional from 8.1.0 to 16.0.0
    - chore(deps-dev): bump @type/nodes 17.0.8
    - chore(deps-dev): bump eslint from 6.0.1 to 8.6.0
    - chore(deps-dev): bump mocha from 6.1.4 to 9.1.3
    - chore(deps-dev): bump nyc from 14.1.1 to 15.1.0
  - [chore(deps-dev): updated package-lock.json](https://github.com/log4js-node/streamroller/pull/71) 
    - chore(deps-dev): bump @babel/compat-data from 7.16.4 to 7.16.8 
    - chore(deps-dev): bump @babel/generator from 7.16.7 to 7.16.8
    - chore(deps-dev): bump @babel/parser from 7.16.7 to 7.16.8
    - chore(deps-dev): bump @babel/travers from 7.16.7 to 7.16.8
    - chore(deps-dev): bump @babel/types from 7.16.7 to 7.16.8
  - [chore(deps-dev): updated package-lock.json](https://github.com/log4js-node/streamroller/pull/77)
    - chore(deps-dev): bump caniuse-lite from 1.0.30001298 to 1.0.30001299
    - chore(deps-dev): bump electron-to-chromium from 1.4.39 to 1.4.44
  - [chore(deps-dev): updated package.json](https://github.com/log4js-node/streamroller/pull/80)
    - chore(deps): bump date-format from 3.0.0 to 4.0.1
    - chore(deps-dev): bump husky from 3.0.0 to 7.0.4
    - chore(deps): bump fs-extra from 8.1.0 to 10.0.0

## [2.2.4](https://github.com/log4js-node/streamroller/milestone/14)

- [Fix for incorrect filename matching](https://github.com/log4js-node/streamroller/pull/61) - thanks [@rnd-debug](https://github.com/rnd-debug)

## [2.2.3](https://github.com/log4js-node/streamroller/milestone/13)

- [Fix for unhandled promise rejection during cleanup](https://github.com/log4js-node/streamroller/pull/56)

## [2.2.2](https://github.com/log4js-node/streamroller/milestone/12)

- [Fix for overwriting current file when using date rotation](https://github.com/log4js-node/streamroller/pull/54)

## 2.2.1

- Fix for num to keep not working when date pattern is all digits (forgot to do a PR for this one)

## [2.2.0](https://github.com/log4js-node/streamroller/milestone/11)

- [Fallback to copy and truncate when file is busy](https://github.com/log4js-node/streamroller/pull/53)

## [2.1.0](https://github.com/log4js-node/streamroller/milestone/10)

- [Improve Windows support (closing streams)](https://github.com/log4js-node/streamroller/pull/52)

## [2.0.0](https://github.com/log4js-node/streamroller/milestone/9)

- [Remove support for node v6](https://github.com/log4js-node/streamroller/pull/44)
- [Replace lodash with native alternatives](https://github.com/log4js-node/streamroller/pull/45) - thanks [@devoto13](https://github.com/devoto13)
- [Simplify filename formatting and parsing](https://github.com/log4js-node/streamroller/pull/46)
- [Removed async lib from main code](https://github.com/log4js-node/streamroller/pull/47)
- [Fix timezone issues in tests](https://github.com/log4js-node/streamroller/pull/48) - thanks [@devoto13](https://github.com/devoto13)
- [Fix for flag values that need existing file size](https://github.com/log4js-node/streamroller/pull/49)
- [Refactor for better readability](https://github.com/log4js-node/streamroller/pull/50)
- [Removed async lib from test code](https://github.com/log4js-node/streamroller/pull/51)

## [1.0.6](https://github.com/log4js-node/streamroller/milestone/8)

- [Fix for overwriting old backup files](https://github.com/log4js-node/streamroller/pull/43)
- Updated lodash to 4.17.14

## [1.0.5](https://github.com/log4js-node/streamroller/milestone/7)

- [Updated dependencies](https://github.com/log4js-node/streamroller/pull/38)
- [Fix for initial file date when appending to existing file](https://github.com/log4js-node/streamroller/pull/40)

## [1.0.4](https://github.com/log4js-node/streamroller/milestone/6)

- [Fix for initial size when appending to existing file](https://github.com/log4js-node/streamroller/pull/35)

## [1.0.3](https://github.com/log4js-node/streamroller/milestone/5)

- [Fix for crash when pattern is all digits](https://github.com/log4js-node/streamroller/pull/33)

## 1.0.2

- is exactly the same as 1.0.1, due to me being an idiot and not pulling before I pushed

## Previous versions

Previous release details are available by browsing the [milestones](https://github.com/log4js-node/streamroller/milestones) in github.
