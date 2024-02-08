# Architecture Overview

videojs-contrib-ads is separated into files by module.

## Modules

### plugin.js

The entry point of the application. Registers the plugin with video.js and initializes other feature modules.

### ads.js

Implements the [public API](../integrator/api.md).

### adBreak.js

Common code that is invoked when ad breaks start and end. Used by Preroll.js, Midroll.js, and Postroll.js.

### cancelContentPlay.js

Feature that prevents content playback while prerolls are handled. cancelContentPlay is used when video.js middleware is *not* available.

### playMiddleware.js

Feature that prevents content playback while prerolls are handled. playMiddleware is used when video.js middleware is available.

### contentupdate

Implements the `contentchanged` event.

### plugin.scss

Styles for the ad player.

### redispatch.js

Feature that makes the presense of ads transparent to event listeners.

### snapshot.js

Feature that captures the player state before ads and restores it after ads.

### states.js

Used to import modules from the `states` folder. This works around an issue with bundler where importing the files directly may not load them in the correct order.

### states

The states folder contains the various states that videojs-contrib-ads can be in.

### states/abstract

States in the `abstract` subfolder are subclassed by the main states in the `states` folder itself. They implement common functionality used by similar states.

## What's Next

Learn more about [states](states.md).
