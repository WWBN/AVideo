/*
This feature provides an optional method for ad plugins to insert run-time values
into an ad server URL or configuration.
*/

import window from 'global/window';
import document from 'global/document';

import videojs from 'video.js';

import {tcData} from './tcf.js';
import {getCurrentUspString} from './usPrivacy.js';
import AdsError from './consts/errors.js';

const uriEncodeIfNeeded = function(value, uriEncode) {
  return uriEncode ? encodeURIComponent(value) : value;
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

const getMediaInfoMacros = function(mediainfo, defaults) {
  const macros = {};

  ['description', 'tags', 'reference_id', 'ad_keys'].forEach((prop) => {
    if (mediainfo && mediainfo[prop]) {
      macros[`{mediainfo.${prop}}`] = mediainfo[prop];
    } else if (defaults[`{mediainfo.${prop}}`]) {
      macros[`{mediainfo.${prop}}`] = defaults[`{mediainfo.${prop}}`];
    } else {
      macros[`{mediainfo.${prop}}`] = '';
    }
  });

  ['custom_fields', 'customFields'].forEach((customFieldProp) => {
    customFields(mediainfo, macros, customFieldProp);
  });

  return macros;
};

const getDefaultValues = function(string) {
  const defaults = {};
  const modifiedString = string.replace(/{([^}=]+)=([^}]*)}/g, (match, name, defaultVal) => {
    defaults[`{${name}}`] = defaultVal;
    return `{${name}}`;
  });

  return {defaults, modifiedString};
};

const getStaticMacros = function(player) {
  return {
    '{player.id}': player.options_['data-player'] || player.id_,
    '{player.height}': player.currentHeight(),
    '{player.width}': player.currentWidth(),
    '{player.heightInt}': Math.round(player.currentHeight()),
    '{player.widthInt}': Math.round(player.currentWidth()),
    '{player.autoplay}': player.autoplay() ? 1 : 0,
    '{player.muted}': player.muted() ? 1 : 0,
    '{player.language}': player.language() || '',
    '{mediainfo.id}': player.mediainfo ? player.mediainfo.id : '',
    '{mediainfo.name}': player.mediainfo ? player.mediainfo.name : '',
    '{mediainfo.duration}': player.mediainfo ? player.mediainfo.duration : '',
    '{player.duration}': player.duration(),
    '{player.durationInt}': Math.round(player.duration()),
    '{player.live}': player.duration() === Infinity ? 1 : 0,
    '{player.pageUrl}': videojs.dom.isInFrame() ? document.referrer : window.location.href,
    '{playlistinfo.id}': player.playlistinfo ? player.playlistinfo.id : '',
    '{playlistinfo.name}': player.playlistinfo ? player.playlistinfo.name : '',
    '{timestamp}': new Date().getTime(),
    '{document.referrer}': document.referrer,
    '{window.location.href}': window.location.href,
    '{random}': Math.floor(Math.random() * 1000000000000)
  };
};

const getTcfMacros = function(tcDataObj) {
  const tcfMacros = {};

  Object.keys(tcDataObj).forEach((key) => {
    tcfMacros[`{tcf.${key}}`] = tcDataObj[key];
  });

  tcfMacros['{tcf.gdprAppliesInt}'] = tcDataObj.gdprApplies ? 1 : 0;

  return tcfMacros;
};

const getUspMacros = () => {
  return {'{usp.uspString}': getCurrentUspString()};
};

// This extracts and evaluates variables from the `window` object for macro replacement. While replaceMacros() handles generic macro name
// overriding for other macro types, this function also needs to reference the overrides in order to map custom macro names in the string
// to their corresponding default pageVariable names, so they can be evaluated on the `window` and stored for later replacement in replaceMacros().
const getPageVariableMacros = function(string, defaults, macroNameOverrides) {
  const pageVarRegex = new RegExp('{pageVariable\\.([^}]+)}', 'g');
  const pageVariablesMacros = {};

  // Aggregate any default pageVariable macros found in the string with any pageVariable macros that have custom names specified in
  // macroNameOverrides.
  const pageVariables = (string.match(pageVarRegex) || []).concat(Object.keys(macroNameOverrides)
    .filter(macroName => pageVarRegex.test(macroName) && string.includes(macroNameOverrides[macroName])));

  if (!pageVariables) {
    return;
  }

  pageVariables.forEach((pageVar) => {
    const key = pageVar;
    const name = pageVar.slice(14, -1);
    const names = name.split('.');
    let context = window;
    let value;

    // Iterate down multiple levels of selector without using eval
    // This makes things like pageVariable.foo.bar work
    for (let i = 0; i < names.length; i++) {
      if (i === names.length - 1) {
        value = context[names[i]];
      } else {
        context = context[names[i]];
        if (typeof context === 'undefined') {
          break;
        }
      }
    }

    const type = typeof value;

    // Only allow certain types of values. Anything else is probably a mistake.
    if (value === null) {
      pageVariablesMacros[key] = 'null';
    } else if (value === undefined) {
      if (defaults[key]) {
        pageVariablesMacros[key] = defaults[key];
      } else {
        videojs.log.warn(`Page variable "${name}" not found`);
        pageVariablesMacros[key] = '';
      }
    } else if (type !== 'string' && type !== 'number' && type !== 'boolean') {
      videojs.log.warn(`Page variable "${name}" is not a supported type`);
      pageVariablesMacros[key] = '';
    } else {
      pageVariablesMacros[key] = value;
    }
  });

  return pageVariablesMacros;
};

const replaceMacros = function(string, macros, uriEncode, overrides = {}, player) {
  for (const macroName in macros) {
    // The resolvedMacroName is the macro as it is expected to appear in the actual string, or regex if it has been provided.
    const resolvedMacroName = overrides.hasOwnProperty(macroName) ? overrides[macroName] : macroName;

    if (resolvedMacroName.startsWith('r:')) {
      try {
        const regex = new RegExp(resolvedMacroName.slice(2), 'g');

        string = string.replace(regex, uriEncodeIfNeeded(macros[macroName], uriEncode));
      } catch (error) {
        player.ads.error({
          errorType: AdsError.AdsMacroReplacementFailed,
          macro: macroName,
          error
        });

        videojs.log.warn(`Unable to replace macro with regex "${resolvedMacroName}". The provided regex may be invalid.`);
      }
    } else {
      string = string.split(resolvedMacroName).join(uriEncodeIfNeeded(macros[macroName], uriEncode));
    }
  }

  return string;
};

/**
 *
 * @param {string} string
 *                 Any string with macros to be replaced
 * @param {boolean} uriEncode
 *                  A Boolean value indicating whether the macros should be replaced with URI-encoded values
 * @param {object} customMacros
 *                 An object with custom macros and values to map them to. For example: {'{five}': 5}
 * @param {boolean} customMacros.disableDefaultMacros
 *                  A boolean indicating whether replacement of default macros should be forgone in favor of only customMacros
 * @param {object} customMacros.macroNameOverrides
 *                 An object that specifies custom names for default macros, following the following format:
 *                 // {'{default-macro-name}': '{new-macro-name}'}
 *                 {'{player.id}': '{{PLAYER_ID}}', ...}
 * @returns {string}
 *          The provided string with all macros replaced. For example: adMacroReplacement('{player.id}') returns a string of the player id
 */
export default function adMacroReplacement(string, uriEncode = false, customMacros = {}) {
  const disableDefaultMacros = customMacros.disableDefaultMacros || false;
  const macroNameOverrides = customMacros.macroNameOverrides || {};

  // Remove special properties from customMacros
  delete customMacros.disableDefaultMacros;
  delete customMacros.macroNameOverrides;

  const macros = customMacros;

  if (disableDefaultMacros) {
    return replaceMacros(string, macros, uriEncode, macroNameOverrides);
  }

  // Get macros with defaults e.g. {x=y}, store the values in `defaults` and replace with standard macros in the string
  const {defaults, modifiedString} = getDefaultValues(string);

  string = modifiedString;

  // Get all macro values
  Object.assign(
    macros,
    getStaticMacros(this),
    getMediaInfoMacros(this.mediainfo, defaults),
    getTcfMacros(tcData),
    getUspMacros(),
    getPageVariableMacros(string, defaults, macroNameOverrides)
  );

  // Perform macro replacement
  string = replaceMacros(string, macros, uriEncode, macroNameOverrides, this);

  // Replace any remaining default values that have not already been replaced. This includes mediainfo custom fields.
  for (const macro in defaults) {
    string = string.replace(macro, defaults[macro]);
  }

  return string;
}
