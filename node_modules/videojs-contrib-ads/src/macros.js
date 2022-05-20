/*
This feature provides an optional method for ad plugins to insert run-time values
into an ad server URL or configuration.
*/

import window from 'global/window';
import document from 'global/document';

import videojs from 'video.js';

import { tcData } from './tcf.js';

// Return URI encoded version of value if uriEncode is true
const uriEncodeIfNeeded = function(value, uriEncode) {
  if (uriEncode) {
    return encodeURIComponent(value);
  }
  return value;
};

// Add custom field macros to macros object
// based on given name for custom fields property of mediainfo object.
const customFields = function(mediainfo, macros, customFieldsName) {
  if (mediainfo && mediainfo[customFieldsName]) {
    const fields = mediainfo[customFieldsName];
    const fieldNames = Object.keys(fields);

    for (let i = 0; i < fieldNames.length; i++) {
      const tag = '{mediainfo.' + customFieldsName + '.' + fieldNames[i] + '}';

      macros[tag] = fields[fieldNames[i]];
    }
  }
};

// Public method that ad plugins use for ad macros.
// "string" is any string with macros to be replaced
// "uriEncode" if true will uri encode macro values when replaced
// "customMacros" is a object with custom macros and values to map them to
//  - For example: {'{five}': 5}
// Return value is is "string" with macros replaced
//  - For example: adMacroReplacement('{player.id}') returns a string of the player id
export default function adMacroReplacement(string, uriEncode, customMacros) {

  const defaults = {};

  // Get macros with defaults e.g. {x=y}, store values and replace with standard macros
  string = string.replace(/{([^}=]+)=([^}]+)}/g, function(match, name, defaultVal) {
    defaults[`{${name}}`] = defaultVal;

    return `{${name}}`;
  });

  if (uriEncode === undefined) {
    uriEncode = false;
  }

  let macros = {};

  if (customMacros !== undefined) {
    macros = customMacros;
  }

  // Static macros
  macros['{player.id}'] = this.options_['data-player'] || this.id_;
  macros['{player.height}'] = this.currentHeight();
  macros['{player.width}'] = this.currentWidth();
  macros['{mediainfo.id}'] = this.mediainfo ? this.mediainfo.id : '';
  macros['{mediainfo.name}'] = this.mediainfo ? this.mediainfo.name : '';
  macros['{mediainfo.duration}'] = this.mediainfo ? this.mediainfo.duration : '';
  macros['{player.duration}'] = this.duration();
  macros['{player.pageUrl}'] = videojs.dom.isInFrame() ? document.referrer : window.location.href;
  macros['{playlistinfo.id}'] = this.playlistinfo ? this.playlistinfo.id : '';
  macros['{playlistinfo.name}'] = this.playlistinfo ? this.playlistinfo.name : '';
  macros['{timestamp}'] = new Date().getTime();
  macros['{document.referrer}'] = document.referrer;
  macros['{window.location.href}'] = window.location.href;
  macros['{random}'] = Math.floor(Math.random() * 1000000000000);

  ['description', 'tags', 'reference_id', 'ad_keys'].forEach((prop) => {
    if (this.mediainfo && this.mediainfo[prop]) {
      macros[`{mediainfo.${prop}}`] = this.mediainfo[prop];
    } else if (defaults[`{mediainfo.${prop}}`]) {
      macros[`{mediainfo.${prop}}`] = defaults[`{mediainfo.${prop}}`];
    } else {
      macros[`{mediainfo.${prop}}`] = '';
    }
  });

  // Custom fields in mediainfo
  customFields(this.mediainfo, macros, 'custom_fields');
  customFields(this.mediainfo, macros, 'customFields');

  // tcf macros
  Object.keys(tcData).forEach(key => {
    macros[`{tcf.${key}}`] = tcData[key];
  });

  // Ad servers commonly want this bool as an int
  macros['{tcf.gdprAppliesInt}'] = tcData.gdprApplies ? 1 : 0;

  // Go through all the replacement macros and apply them to the string.
  // This will replace all occurrences of the replacement macros.
  for (const i in macros) {
    string = string.split(i).join(uriEncodeIfNeeded(macros[i], uriEncode));
  }

  // Page variables
  string = string.replace(/{pageVariable\.([^}]+)}/g, function(match, name) {
    let value;
    let context = window;
    const names = name.split('.');

    // Iterate down multiple levels of selector without using eval
    // This makes things like pageVariable.foo.bar work
    for (let i = 0; i < names.length; i++) {
      if (i === names.length - 1) {
        value = context[names[i]];
      } else {
        context = context[names[i]];
      }
    }

    const type = typeof value;

    // Only allow certain types of values. Anything else is probably a mistake.
    if (value === null) {
      return 'null';
    } else if (value === undefined) {
      if (defaults[`{pageVariable.${name}}`]) {
        return defaults[`{pageVariable.${name}}`];
      }
      videojs.log.warn(`Page variable "${name}" not found`);
      return '';
    } else if (type !== 'string' && type !== 'number' && type !== 'boolean') {
      videojs.log.warn(`Page variable "${name}" is not a supported type`);
      return '';
    }

    return uriEncodeIfNeeded(String(value), uriEncode);
  });

  // Replace defaults
  for (const defaultVal in defaults) {
    string = string.replace(defaultVal, defaults[defaultVal]);
  }

  return string;
}
