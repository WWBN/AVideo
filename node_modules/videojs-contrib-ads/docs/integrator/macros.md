# Macros

The contrib-ads library includes an optional ad macros feature, which allows ad plugins to incorporate runtime values into server URLs or configurations. Ad macros can be utilized to substitute specific placeholders in a URL string with dynamic values such as the player ID, page URL, or custom variables.

Here's an example of how ad macros work. An ad plugin might use a URL like this:

`'http://example.com/vmap.xml?id={player.id}'`

Within the ad plugin code, the videojs-contrib-ads macro feature is applied as follows:

`serverUrl = player.ads.adMacroReplacement(serverUrl, true, additionalMacros);`

This results in the final URL:

`'http://example.com/vmap.xml?id=12345'`

In this case, `12345` represents the player ID.

## Function: player.ads.adMacroReplacement
Replaces macros in a given string with their corresponding values.

### Parameters
- `string` (string): A string containing macros to be replaced.
- `uriEncode` (boolean, optional, default: false): A boolean value indicating whether the macros should be replaced with URI-encoded values.
- `customMacros` (object, optional): An object containing custom macros and their values. It can also be used to disable default macro replacement and provide custom names for default macros.
  - `customMacros.disableDefaultMacros` (boolean, optional, default: false): A boolean value indicating whether replacement of default macros should be skipped in favor of only custom macros.
  - `customMacros.macroNameOverrides` (object, optional): An object containing custom names for default macros. For example: {'{player.id}': '{{PLAYER_ID}}', ...}.

### Returns
- (string): The provided string with all macros replaced.

## Default Macros

### Static Macros

| Name                     | Value                                 |
|:-------------------------|:--------------------------------------|
| {player.id}              | The player ID                         |
| {player.width}           | The current player width              |
| {player.height}          | The current player height             |
| {player.widthInt}        | The current player width as int       |
| {player.heightInt}       | The current player height as int      |
| {player.duration}        | The duration of current video *       |
| {player.durationInt}     | The duration as int *                 |
| {player.live}            | `1` if live, `0` if VOD *             |
| {player.autoplay}        | `1` if set to autoplay, `0` if not    |
| {player.muted}           | `1` if muted, `0` if not              |
| {player.pageUrl}         | The page URL ** ***                   |
| {player.language}        | The current language of the player    |
| {timestamp}              | Current epoch time                    |
| {document.referrer}      | Value of document.referrer ***        |
| {window.location.href}   | Value of window.location.href         |
| {random}                 | A random number 0-1 trillion          |
| {mediainfo.id}           | Pulled from mediainfo object          |
| {mediainfo.name}         | Pulled from mediainfo object          |
| {mediainfo.description}  | Pulled from mediainfo object          |
| {mediainfo.tags}         | Pulled from mediainfo object          |
| {mediainfo.reference_id} | Pulled from mediainfo object          |
| {mediainfo.duration}     | Duration from mediainfo object        |
| {mediainfo.durationInt}  | Duration from mediainfo object as int |
| {mediainfo.ad_keys}      | Pulled from mediainfo object          |
| {playlistinfo.id}        | Pulled from playlistinfo object       |
| {playlistinfo.name}      | Pulled from playlistinfo object       |

\* Returns 0 if video is not loaded. Be careful timing your ad request with this macro.

\** Returns document referrer if in an iframe otherwise window location.

\*** Document referrer may not return the full URL depending on the effective [referrer policy][referrer-policy].

### Dynamic Macro: mediainfo.custom_fields.*

A macro such as {mediainfo.custom_fields.foobar} allows the user to access the value of any property in `mediainfo.custom_fields`.

### Dynamic Macro: pageVariable.*

A macro such as {pageVariable.foobar} allows the user access the value of any property on the `window` object. Only certain value types are allowed, per this table:

| Type      | What happens                          |
|:----------|:--------------------------------------|
| String    | Used without any change               |
| Number    | Converted to string automatically     |
| Boolean   | Converted to string automatically     |
| Null      | Returns the string `"null"`           |
| Undefined | Logs warning and returns empty string |
| Other     | Logs warning and returns empty string |

### TCF macros

If a CMP supporting the [GDPR Transparency and Consent Framework][tcf] is in use additional tcf macros are made available. The syntax is `{tcf.*}`, with `*` being a property in the [tcData][tcdata] object. Most commonly used will be:

| Name                     | Value                                       |
|:-------------------------|:--------------------------------------------|
| {tcf.gdprApplies}        | Whether GDPR applies to the current session |
| {tcf.tcString}           | The consent string                          |

Since `gdprApplies` is a boolean and many ad servers expect the value as an int, an additional `{tcf.gdprAppliesInt}` is provided which will return `1` or `0`.

If the player is in an iframe, a proxy will be added if any parent frame is detected to gain consent with the postmessage API. The CMP must be loaded first.

### Default values in macros

A default value can be provided within a macro in the format `{MACRO=DEFAULT}`, in which case this value will be used where not resolvable e.g. `http://example.com/ad/{pageVariable.adConf=1234}` becomes `http://example.com/ad/1234` if `window.adConf` is undefined. If a blank default is given, there will be a blank param, `http://example.com/ad?config={pageVariable.adConf=}&a=b` becomes `http://example.com/ad?config=&a=b` if `window.adConf` is unset.

## Custom Macros
Custom macros can be added by providing an object to the `customMacros` parameter, with keys representing the macro names and values representing the macro values. For example, if you want to add a custom macro `{myVar}` with a value of `5`, you can do so like this:

```js
const stringWithMacros = "http://ads.example.com?custom_var={myVar}";
const customMacros = {
  '{myVar}': 5
};
const replacedString = player.ads.adMacroReplacement(stringWithMacros, false, customMacros);
```

This would result in the `replacedString` containing `"http://ads.example.com?custom_var=5"`.

## Customizing Default Macro Names
You can also customize the names of any default macros, including dynamic macros, by providing a `macroNameOverrides` object within the `customMacros` parameter. For example, if you want to replace `{player.id}` with `{{PLAYER_ID}}`, you can do so like this:

```js
const stringWithMacros = "http://ads.example.com?player_id={{PLAYER_ID}}";
const customMacros = {
  macroNameOverrides: {
    '{player.id}': '{{PLAYER_ID}}'
  }
};
const replacedString = player.ads.adMacroReplacement(stringWithMacros, false, customMacros);
```

This would result in the `replacedString` containing the player ID in place of the `{{PLAYER_ID}}` macro instead of the `{player.id}` macro.

## Disabling Default Macro Replacement
If you want to disable the replacement of default macros and only use custom macros, you can set the `disableDefaultMacros` property of the `customMacros` parameter to `true`. For example:

```js
const stringWithMacros = "http://ads.example.com?player_id={player.id}&custom_var={myVar}";
const customMacros = {
  '{myVar}': 5,
  disableDefaultMacros: true
};
const replacedString = player.ads.adMacroReplacement(stringWithMacros, false, customMacros);
```

In this case, the `replacedString` would not replace the `{player.id}` macro but would replace the custom `{myVar}` macro.

[tcf]: https://github.com/InteractiveAdvertisingBureau/GDPR-Transparency-and-Consent-Framework/blob/master/TCFv2/IAB%20Tech%20Lab%20-%20CMP%20API%20v2.md
[tcdata]: https://github.com/InteractiveAdvertisingBureau/GDPR-Transparency-and-Consent-Framework/blob/master/TCFv2/IAB%20Tech%20Lab%20-%20CMP%20API%20v2.md#tcdata
[referrer-policy]: https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Referrer-Policy
