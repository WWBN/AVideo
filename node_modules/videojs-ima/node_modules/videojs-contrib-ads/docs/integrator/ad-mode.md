# What is ad mode?

Defining "ad mode" has turned out to be an interesting challenge for this project. A naive definition might be "is an ad playing or not?" It turns out there are a lot of edge cases that this naive definition does not cover. The following precise definition of ad mode drives the implementation of contrib-ads and ad plugins should be careful to follow it as well to assure maximum correctness.

Ad mode is split into three parts as identified by the methods `isWaitingForAdBreak()`, `inAdBreak()`, and `isContentResuming()`. See the [API reference](api.md) for more information.

## Definition

> The player is in ad mode if the ad plugin is currently preventing content playback.

### Examples of ad mode:

* Waiting to find out if an ad is going to play while content would normally be playing
* Waiting for an ad to start playing while content would normally be playing
* A linear ad is playing
* An ad has completed and content is about to resume, but content has not resumed yet

### Examples of not ad mode:

* Content playback has not been requested
* Content playback is paused
* An asynchronous ad request is ongoing while content is playing
* A non-linear ad (such as an overlay) is active
