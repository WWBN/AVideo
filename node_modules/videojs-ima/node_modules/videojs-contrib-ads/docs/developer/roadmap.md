# Roadmap

## Version 7

* Pause content video if there is a programmatic call to play (prefixed as adplay) while an ad is playing in an ad container (rather than content video element). Prefixing doesn't prevent the videojs behavior, so this would prevent the content from playing behind the ad. Right now, ad plugins I am aware of are doing this on their own, so this would require a migration to move the behavior into this project.
* `contentended` will change from its current deprecated purpose to being a normal prefixed event during content restoration.
* The default value for `liveCuePoints` will change to `false` because we believe this is a more common and intuitive default.
