# Macros

An optional feature that contrib-ads supports is ad macros. Ad macros are often used by ad plugins to support addition of run-time values into a server URL or configuration.

For example, an ad plugin that supports this feature might accept an ad server URL like this:

`'http://example.com/vmap.xml?id={player.id}'`

In the ad plugin code, it would use the videojs-contrib-ads macro feature to process that URL like this:

`serverUrl = player.ads.adMacroReplacement(serverUrl, true, additionalMacros);`

This would result in a server URL like this:

`'http://example.com/vmap.xml?id=12345'`

where 12345 is the player ID.

adMacroReplacement takes 3 arguments:

1. The string that has macros to be replaced.
2. `true` if the macro values should be URI encoded when they are inserted, else `false` (default `false`)
3. An optional object that defines additional macros, such as `{'{five}': 5}` (default `{}`)

## Static Macros

| Name                     | Value                             |
|:-------------------------|:----------------------------------|
| {player.id}              | The player ID                     |
| {player.width}           | The current player width          |
| {player.height}          | The current player height         |
| {player.duration}        | The duration of current video *   |
| {player.pageUrl}         | The page URL ** ***               |
| {timestamp}              | Current epoch time                |
| {document.referrer}      | Value of document.referrer ***    |
| {window.location.href}   | Value of window.location.href     |
| {random}                 | A random number 0-1 trillion      |
| {mediainfo.id}           | Pulled from mediainfo object      |
| {mediainfo.name}         | Pulled from mediainfo object      |
| {mediainfo.description}  | Pulled from mediainfo object      |
| {mediainfo.tags}         | Pulled from mediainfo object      |
| {mediainfo.reference_id} | Pulled from mediainfo object      |
| {mediainfo.duration}     | Pulled from mediainfo object      |
| {mediainfo.ad_keys}      | Pulled from mediainfo object      |
| {playlistinfo.id}        | Pulled from playlistinfo object   |
| {playlistinfo.name}      | Pulled from playlistinfo object   |

\* Returns 0 if video is not loaded. Be careful timing your ad request with this macro.

\** Returns document referrer if in an iframe otherwise window location.

\*** Document referrer may not return the full URL depending on the effective [referrer policy][referrer-policy].

## Dynamic Macro: mediainfo.custom_fields.*

A macro such as {mediainfo.custom_fields.foobar} allows the user to access the value of any property in `mediainfo.custom_fields`.

## Dynamic Macro: pageVariable.*

A macro such as {pageVariable.foobar} allows the user access the value of any property on the `window` object. Only certain value types are allowed, per this table:

| Type      | What happens                          |
|:----------|:--------------------------------------|
| String    | Used without any change               |
| Number    | Converted to string automatically     |
| Boolean   | Converted to string automatically     |
| Null      | Returns the string `"null"`           |
| Undefined | Logs warning and returns empty string |
| Other     | Logs warning and returns empty string |

## TCF macros

If a CMP supporting the [GDPR Transparency and Consent Framework][tcf] is in use additional tcf macros are made available. The syntax is `{tcf.*}`, with `*` being a property in the [tcData][tcdata] object. Most commonly used will be:

| Name                     | Value                                       |
|:-------------------------|:--------------------------------------------|
| {tcf.gdprApplies}        | Whether GDPR applies to the current session |
| {tcf.tcString}           | The consent string                          |

Since `gdprApplies` is a boolean and many ad servers expect the value as an int, an additional `{tcf.gdprAppliesInt}` is provided which will return `1` or `0`.

If the player is in an iframe, a proxy will be added if any parent frame is detected to gain consent with the postmessage API. The CMP must be loaded first.

## Default values in macros

A default value can be provided within a macro, in which case this value will be used where not resolvable e.g. `http://example.com/ad/{pageVariable.adConf=1234}` becomes `http://example.com/ad/1234` if `window.adConf` is undefined.

[tcf]: https://github.com/InteractiveAdvertisingBureau/GDPR-Transparency-and-Consent-Framework/blob/master/TCFv2/IAB%20Tech%20Lab%20-%20CMP%20API%20v2.md
[tcdata]: https://github.com/InteractiveAdvertisingBureau/GDPR-Transparency-and-Consent-Framework/blob/master/TCFv2/IAB%20Tech%20Lab%20-%20CMP%20API%20v2.md#tcdata
[referrer-policy]: https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Referrer-Policy
