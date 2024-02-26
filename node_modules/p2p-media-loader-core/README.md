# P2P Media Loader Core

[![npm version](https://badge.fury.io/js/p2p-media-loader-core.svg)](https://npmjs.com/package/p2p-media-loader-core)

Core functionality for P2P sharing of segmented media streams (i.e. HLS, MPEG-DASH) using WebRTC.

Useful links:
- [P2P development, support & consulting](https://novage.com.ua/)
- [Demo](http://novage.com.ua/p2p-media-loader/demo.html)
- [FAQ](https://github.com/Novage/p2p-media-loader/blob/master/FAQ.md)
- [Overview](http://novage.com.ua/p2p-media-loader/overview.html)
- [Technical overview](http://novage.com.ua/p2p-media-loader/technical-overview.html)
- JS CDN
  - [Core](https://cdn.jsdelivr.net/npm/p2p-media-loader-core@latest/build/)
  - [Hls.js integration](https://cdn.jsdelivr.net/npm/p2p-media-loader-hlsjs@latest/build/)
  - [Shaka integration](https://cdn.jsdelivr.net/npm/p2p-media-loader-shaka@latest/build/)

# API

The library uses `window.p2pml.core` as a root namespace in Web browser for:
- `HybridLoader` - HTTP and P2P loader
- `Events` - Events emitted by `HybridLoader`
- `Segment` - Media stream segment
- `version` - API version

---

## `HybridLoader`

HTTP and P2P loader.

### `HybridLoader.isSupported()`

Returns `true` if WebRTC data channels API is supported by the browser. Read more [here](http://iswebrtcreadyyet.com/legacy.html).

### `loader = new HybridLoader([settings])`

Creates a new `HybridLoader` instance.

If `settings` is specified, then the default settings (shown below) will be overridden.

| Name | Type | Default Value | Description |
| --- | ---- | ------ | ------ |
| `cachedSegmentExpiration` | Integer | 300000 | Segment lifetime in cache. The segment is deleted from the cache if the last access time is greater than this value (in milliseconds). Cached segments are shared over P2P network. Affects only default segments storage.
| `cachedSegmentsCount` | Integer | 30 | Max number of segments that can be stored in the cache. Cached segments are shared over P2P network. Affects only default segments storage.
| `requiredSegmentsPriority` | Integer | 1 | The maximum priority of the segments to be downloaded (if not available) as quickly as possible (i.e. via HTTP method). First segment that should be downloaded has priority 0.
| `useP2P` | Boolean | true | Enable/Disable peers interaction
| `consumeOnly` | Boolean | false | The peer will not upload segments data to the P2P network but still download from others.
| `simultaneousHttpDownloads` | Integer | 2 | Max number of simultaneous downloads from HTTP source
| `httpDownloadProbability` | Float | 0.1 | Probability of downloading remaining not downloaded segment in the segments queue via HTTP
| `httpDownloadProbabilityInterval` | Integer | 1000 | Interval of the httpDownloadProbability check (in milliseconds)
| `httpDownloadProbabilitySkipIfNoPeers` | Boolean | false | Don't download segments over HTTP randomly when there is no peers
| `httpFailedSegmentTimeout` | Integer | 10000 | Timeout before trying to load a segment again via HTTP after failed attempt (in milliseconds)
| `httpDownloadMaxPriority` | Integer | 20 | Segments with higher priority will not be downloaded over HTTP
| `httpDownloadInitialTimeout` | Integer | 0 | Try to download initial segments over P2P if the value is > 0. But HTTP download will be forcibly enabled if there is no peers on tracker or single sequential segment P2P download is timed out (see `httpDownloadInitialTimeoutPerSegment`).
| `httpDownloadInitialTimeoutPerSegment` | Integer | 4000 | If initial HTTP download timeout is enabled (see `httpDownloadInitialTimeout`) this parameter sets additional timeout for a single sequential segment download over P2P. It will cancel initial HTTP download timeout mode if a segment download is timed out.
| `httpUseRanges` | Boolean | false | Use HTTP ranges requests where it is possible. Allows to continue (and not start over) aborted P2P downloads over HTTP.
| `simultaneousP2PDownloads` | Integer | 3 | Max number of simultaneous downloads from peers
| `p2pDownloadMaxPriority` | Integer | 20 | Segments with higher priority will not be downloaded over P2P
| `p2pSegmentDownloadTimeout` | Integer | 60000 | Time allowed for a segment to start downloading. This value only limits time needed for segment to start, not the time required for full download.
| `webRtcMaxMessageSize` | Integer | 64 * 1024 - 1 | Max WebRTC message size. 64KiB - 1B should work with most of recent browsers. Set it to 16KiB for older browsers support.
| `trackerAnnounce` | String[] | wss://tracker.novage.com.ua wss://tracker.openwebtorrent.com | WebTorrent trackers to use for announcement
| `peerRequestsPerAnnounce` | Integer | 10 | Number of requested peers in each announce for each tracker. Maximum is 10.
| `rtcConfig` | [RTCConfiguration](https://developer.mozilla.org/en-US/docs/Web/API/RTCPeerConnection/RTCPeerConnection#RTCConfiguration_dictionary) | Object | An [RTCConfiguration](https://developer.mozilla.org/en-US/docs/Web/API/RTCPeerConnection/RTCPeerConnection#RTCConfiguration_dictionary) dictionary providing options to configure WebRTC connections.
| `segmentValidator` | Function | undefined | Segment validation callback - validates the data after it has been downloaded.<br><br>Arguments:<br>`segment` (Segment) - The segment object.<br>`method` (String) - Can be "http" or "p2p" only.<br>`peerId` (String) - The ID of the peer that the segment was downloaded from in case it is P2P download; and *undefined* for HTTP donwload.<br><br>Returns:<br>A promise - if resolved the segment considered to be valid, if rejected the error object will be passed to `SegmentError` event.
| `xhrSetup` | Function | undefined | XMLHttpRequest setup callback. Handle it when you need additional setup for requests made by the library. If handled, expected a function with two arguments: xhr (XMLHttpRequest), url (String).
| `segmentUrlBuilder` | Function | undefined | Allow to modify the segment URL before HTTP request. If handled, expected a function of one argument of type `Segment` that returns a `string` - generated segment URL.
| `segmentsStorage` | Object | undefined | A storage for the downloaded segments. By default the segments are stored in JavaScript memory. Can be used to implement offline plabyack. See [SegmentsStorage](#segmentsstorage-interface) interface for details.

### SegmentsStorage interface
```typescript
interface SegmentsStorage {
    storeSegment(segment: Segment): Promise<void>;
    getSegmentsMap(masterSwarmId: string): Promise<Map<string, {segment: Segment}>>;
    getSegment(id: string, masterSwarmId: string): Promise<Segment | undefined>;
    clean(lockedSementsfilter?: (id: string) => boolean): Promise<boolean>;
    destroy(): Promise<void>;
}
```

### `loader.load(segments, streamSwarmId)`

Creates new queue of segments to download. Aborts all http and peer connections for segments that are not in the new load and emits `Events.SegmentAbort` event for each aborted event.

Function args:
- `segments` - array of `Segment` class instances with populated `url` and `priority` field;
- `streamSwarmId` - current swarm;

### `loader.on(Events.SegmentLoaded, function (segment, peerId) {})`

Emitted when segment have been downloaded.

Listener args:
- `segment` - instance of `Segment` class with populated `url` and `data` fields;
- `peerId` - Id of the peer the segment was downloaded from; `undefined` for HTTP method;

### `loader.on(Events.SegmentError, function (segment, error, peerId) {})`

Emitted when an error occurred while loading the segment.

Listener args:
- `segment` - url of the segment;
- `error` - error details;
- `peerId` - Id of the peer the error occured with; `undefined` for HTTP method;

### `loader.on(Events.SegmentAbort, function (segment) {})`

Emitted for each segment that does not hit into a new segments queue when the `load` method is called.

Listener args:
- `segment` - aborted segment;

### `loader.on(Events.PeerConnect, function (peer) {})`

Emitted when a peer is connected.

Listener args:
- `peer` - peer object with populated `id` and `remoteAddress` fields;

### `loader.on(Events.PeerClose, function (peerId) {})`

Emitted when a peer is disconnected.

Listener args:
- `peerId` - Id of the disconnected peer;

### `loader.on(Events.PieceBytesDownloaded, function (method, bytes, peerId) {})`

Emitted when a segment piece downloaded.

Listener args:
- `method` - downloading method, possible values: `http`, `p2p`;
- `bytes` - amount of bytes downloaded;
- `peerId` - Id of the peer these bytes downloaded from; `undefined` for HTTP method;

### `loader.on(Events.PieceBytesUploaded, function (method, bytes) {})`

Emitted when a segment piece uploaded.

Listener args:
- `method` - uploading method, possible values: `p2p`;
- `bytes` - amount of bytes uploaded;
- `peerId` - Id of the peer these bytes uploaded to; `undefined` for HTTP method;

### `loader.getSettings()`

Returns loader instance settings.

### `loader.getDetails()`

Returns loader instance details.

### `loader.getSegment(id)`

Returns a segment from loader cache or undefined if the segment is not available.

Function args:
- `id` - Id of the segment;

### `loader.destroy()`

Destroys loader: abort all connections (http, tcp, peer), clears cached segments.

---

## `Events`

Events that are emitted by `HybridLoader`.

- [SegmentLoaded](#loaderoneventssegmentloaded-function-segment-peerid-)
- [SegmentError](#loaderoneventssegmenterror-function-segment-error-peerid-)
- [SegmentAbort](#loaderoneventssegmentabort-function-segment-)
- [PeerConnect](#loaderoneventspeerconnect-function-peer-)
- [PeerClose](#loaderoneventspeerclose-function-peerid-)
- [PieceBytesDownloaded](#loaderoneventspiecebytesdownloaded-function-method-bytes-peerid-)
- [PieceBytesUploaded](#loaderoneventspiecebytesuploaded-function-method-bytes-peerid-)

---

## `Segment`

Media stream segment.

Instance contains:
- `id`
    + a `String`
    + unique identifier of the segment across peers
    + can be equal to URL if it is the same for all peers
- `url`
    + a `String`
    + URL of the segment
`masterSwarmId`
    + a `String`
    + segment's master swarm ID
`masterManifestUri`
    + a `String`
    + segment's master manifest URI
`streamId`
    + a `String` or `undefined`
    + segment's stream ID
`sequence`
    + a `String`
    + segment's sequence ID
- `range` or `undefined`
    + a `String`
    + must be valid HTTP Range header value or `undefined`
- `priority`
    + a non-negative integer `Number`
    + the lower value - the higher priority
    + default is `0`
- `data`
    + an `ArrayBuffer` or `undefined`
    + available only when segment is fully loaded; subscribe to `SegmentLoaded`
      event for this very moment
- `downloadSpeed` or `undefined`
    + a non-negative integer `Number`
    + download speed in bytes per millisecond or 0
- `requestUrl`
    + a `String` or `undefined`
    + Request URL of the segment
- `responseUrl`
    + a `String` or `undefined`
    + Response URL of the segment

---

## Usage Example

```javascript
var loader = new p2pml.core.HybridLoader();

loader.on(p2pml.core.Events.SegmentLoaded, function (segment) {
    console.log("Loading finished, bytes:", segment.data.byteLength);
});

loader.on(p2pml.core.Events.SegmentError, function (segment, error) {
    console.log("Loading failed", segment, error);
});

loader.load([
    new p2pml.core.Segment("segment-1", "//url/to/segment/1", undefined, 0),
    new p2pml.core.Segment("segment-2", "//url/to/segment/2", undefined, 1),
    new p2pml.core.Segment("segment-3", "//url/to/segment/3", undefined, 2)
], "swarm-1");
```
