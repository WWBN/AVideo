import QUnit from 'qunit';
import window from 'global/window';
import document from 'global/document';
import sharedModuleHooks from './lib/shared-module-hooks.js';
import sinon from 'sinon';
import videojs from 'video.js';

QUnit.module('Ad Macros', sharedModuleHooks({}));

QUnit.test('player.id', function(assert) {
  this.player.options_['data-player'] = '12345';
  const result = this.player.ads.adMacroReplacement('{player.id}');

  assert.equal(result, '12345');
});

QUnit.test('player dimensions', function(assert) {
  this.player.options_['data-player'] = '12345';
  this.player.dimensions(200, 100);

  const resultHeight = this.player.ads.adMacroReplacement('{player.height}');
  const resultWidth = this.player.ads.adMacroReplacement('{player.width}');

  assert.equal(resultHeight, 100, 'player.height was replaced');
  assert.equal(resultWidth, 200, 'player.width was replaced');
});

QUnit.test('player dimensions as ints', function(assert) {
  this.player.options_['data-player'] = '12345';
  this.player.dimensions(200.3, 100.7);

  const resultHeight = this.player.ads.adMacroReplacement('{player.heightInt}');
  const resultWidth = this.player.ads.adMacroReplacement('{player.widthInt}');

  assert.equal(resultHeight, 101, 'player.height was replaced with int');
  assert.equal(resultWidth, 200, 'player.width was replaced with int');
});

QUnit.test('mediainfo', function(assert) {
  /* eslint-disable camelcase */
  this.player.mediainfo = {
    id: 1,
    name: 2,
    description: 3,
    tags: 4,
    reference_id: 5,
    duration: 6,
    ad_keys: 7
  };
  /* eslint-enable camelcase */
  const result = this.player.ads.adMacroReplacement('{mediainfo.id}' +
    '{mediainfo.name}' +
    '{mediainfo.description}' +
    '{mediainfo.tags}' +
    '{mediainfo.reference_id}' +
    '{mediainfo.duration}' +
    '{mediainfo.ad_keys}');

  assert.equal(result, '1234567');
});

QUnit.test('playlistinfo', function(assert) {
  this.player.playlistinfo = {
    id: 1,
    name: 2
  };
  const result = this.player.ads.adMacroReplacement('{playlistinfo.id}' +
    '{playlistinfo.name}');

  assert.equal(result, '12');
});

QUnit.test('player.duration', function(assert) {
  this.player.duration = function() {
    return 5;
  };
  const result = this.player.ads.adMacroReplacement('{player.duration}');

  assert.equal(result, 5);
});

QUnit.test('player.durationInt', function(assert) {
  this.player.duration = function() {
    return 5.2;
  };
  const result = this.player.ads.adMacroReplacement('{player.durationInt}');

  assert.equal(result, 5, 'float diration returns as int');
});

QUnit.test('player.live', function(assert) {
  this.player.duration = function() {
    return Infinity;
  };
  let result = this.player.ads.adMacroReplacement('{player.live}');

  assert.equal(result, 1, 'Infinity duration returns 1');

  this.player.duration = function() {
    return 100;
  };
  result = this.player.ads.adMacroReplacement('{player.live}');

  assert.equal(result, 0, 'finite duration returns 0');

  this.player.duration = function() {
    return NaN;
  };
  result = this.player.ads.adMacroReplacement('{player.live}');

  assert.equal(result, 0, 'NaN duration returns 0');
});

QUnit.test('player.autoplay', function(assert) {
  this.player.autoplay = function() {
    return true;
  };
  let result = this.player.ads.adMacroReplacement('{player.autoplay}');

  assert.equal(result, 1, 'true value returns 1');

  this.player.autoplay = function() {
    return false;
  };
  result = this.player.ads.adMacroReplacement('{player.autoplay}');

  assert.equal(result, 0, 'false value returns 0');

  this.player.autoplay = function() {
    return 'muted';
  };
  result = this.player.ads.adMacroReplacement('{player.autoplay}');

  assert.equal(result, 1, 'string value returns 1');
});

QUnit.test('player.muted', function(assert) {
  this.player.muted = function() {
    return true;
  };
  let result = this.player.ads.adMacroReplacement('{player.muted}');

  assert.equal(result, 1, 'true value returns 1');

  this.player.muted = function() {
    return false;
  };
  result = this.player.ads.adMacroReplacement('{player.muted}');

  assert.equal(result, 0, 'false value returns 0');
});

QUnit.test('player.language', function(assert) {
  this.player.language = function() {
    return 'en-us';
  };
  let result = this.player.ads.adMacroReplacement('{player.language}');

  assert.equal(result, 'en-us', 'returns correct lang');

  this.player.language = function() {};
  result = this.player.ads.adMacroReplacement('{player.language}');

  assert.equal(result, '', 'returns empty string if lang undefined');
});

QUnit.test('player.pageUrl', function(assert) {
  const result = this.player.ads.adMacroReplacement('{player.pageUrl}');

  assert.equal(result, document.referrer, 'tests run in iframe, so referrer should be used');
});

QUnit.test('timestamp', function(assert) {
  this.player.duration = function() {
    return 5;
  };
  const result = this.player.ads.adMacroReplacement('{timestamp}');

  assert.equal(result, new Date().getTime());
});

QUnit.test('document.referrer', function(assert) {
  const result = this.player.ads.adMacroReplacement('{document.referrer}');

  assert.equal(
    result,
    document.referrer,
    '"' + result + '" was the document.referrer'
  );
});

QUnit.test('window.location.href', function(assert) {
  const result = this.player.ads.adMacroReplacement('{window.location.href}');

  assert.equal(
    result,
    window.location.href,
    '"' + result + '" was the window.location.href'
  );
});

QUnit.test('random', function(assert) {
  const result = this.player.ads.adMacroReplacement('{random}');

  assert.ok(result.match(/^\d+$/), '"' + result + '" is a random number');
});

QUnit.test('mediainfo.custom_fields', function(assert) {
  /* eslint-disable camelcase */
  this.player.mediainfo = {
    custom_fields: {
      dog: 1,
      cat: 2,
      guinea_pig: 3
    },
    customFields: {
      dog: 1,
      cat: 2,
      guinea_pig: 3
    }
  };
  /* eslint-enable camelcase */
  const result = this.player.ads.adMacroReplacement('{mediainfo.custom_fields.dog}' +
    '{mediainfo.custom_fields.cat}' +
    '{mediainfo.custom_fields.guinea_pig}' +
    '{mediainfo.customFields.dog}' +
    '{mediainfo.customFields.cat}' +
    '{mediainfo.customFields.guinea_pig}');

  assert.equal(result, '123123');
});

QUnit.test('pageVariables', function(assert) {

  window.animal = {
    dog: 'Old Buddy',
    cat: {
      maineCoon: 'Huge the Cat',
      champion: {
        name: 'Champ'
      }
    }
  };
  window.bird = null;
  window.isAwesome = true;
  window.foo = function() {};
  window.bar = {};

  const result = this.player.ads.adMacroReplacement('Number: {pageVariable.scrollX}, ' +
    'Boolean: {pageVariable.isAwesome}, ' +
    'Null: {pageVariable.bird}, ' +
    'Undefined: {pageVariable.thisDoesNotExist}, ' +
    'Function: {pageVariable.foo}, ' +
    'Object: {pageVariable.bar}, ' +
    'Nested 2x: {pageVariable.animal.dog}, ' +
    'Nested 3x: {pageVariable.animal.cat.maineCoon}, ' +
    'Nested 4x: {pageVariable.animal.cat.champion.name}, ' +
    'Nested missing parent: {pageVariable.animal.ape.human}');

  assert.equal(
    result,
    'Number: 0, ' +
    'Boolean: true, ' +
    'Null: null, ' +
    'Undefined: , ' +
    'Function: , ' +
    'Object: , ' +
    'Nested 2x: Old Buddy, ' +
    'Nested 3x: Huge the Cat, ' +
    'Nested 4x: Champ, ' +
    'Nested missing parent: '
  );
});

QUnit.test('multiple, identical macros', function(assert) {
  const result = this.player.ads.adMacroReplacement('...&documentrefferer1={document.referrer}&documentrefferer2={document.referrer}&windowlocation1={window.location.href}&windowlocation2={window.location.href}');
  const expected = `...&documentrefferer1=${document.referrer}&documentrefferer2=${document.referrer}&windowlocation1=${window.location.href}&windowlocation2=${window.location.href}`;

  assert.equal(
    result,
    expected,
    `"${result}" includes 2 replaced document.referrer and 2 window.location.href strings`
  );
});

QUnit.test('uriEncode', function(assert) {
  /* eslint-disable camelcase */
  this.player.mediainfo = {
    custom_fields: {
      urlParam: '? &'
    }
  };
  /* eslint-enable camelcase */
  window.foo = '& ?';
  const result = this.player.ads.adMacroReplacement('{mediainfo.custom_fields.urlParam}{pageVariable.foo}', true);

  assert.equal(result, '%3F%20%26%26%20%3F');
});

QUnit.test('customMacros', function(assert) {
  const result = this.player.ads.adMacroReplacement('The sky is {skyColor}. {exclamation}!', false, {
    '{skyColor}': 'blue',
    '{exclamation}': 'Hooray'
  });

  assert.equal(result, 'The sky is blue. Hooray!');
});

QUnit.test('default values', function(assert) {
  /* eslint-disable camelcase */
  this.player.mediainfo = {
    customFields: {
      set: 1
    },
    reference_id: 'abc'
  };
  /* eslint-enable camelcase */
  window.testvar1 = 'a';

  assert.equal(
    this.player.ads.adMacroReplacement('{mediainfo.customFields.set=other}'), '1',
    'custom fields: set value is not replaced by default'
  );

  assert.equal(
    this.player.ads.adMacroReplacement('{mediainfo.customFields.unsset=2}'), '2',
    'custom fields: unset value is replaced by default'
  );

  assert.equal(
    this.player.ads.adMacroReplacement('{mediainfo.ad_keys=key=value}'), 'key=value',
    'equals in default value preserved'
  );

  assert.equal(
    this.player.ads.adMacroReplacement('{mediainfo.reference_id=Other}'), 'abc',
    'mediainfo: set value is not replaced by default'
  );

  assert.equal(
    this.player.ads.adMacroReplacement('{mediainfo.description=xyz}'), 'xyz',
    'mediainfo: unset value is replaced by default'
  );

  assert.equal(
    this.player.ads.adMacroReplacement('{pageVariable.testvar1=b}'), 'a',
    'pageVariable: set value is not replaced by default'
  );

  assert.equal(
    this.player.ads.adMacroReplacement('{pageVariable.testvar2=c}'), 'c',
    'pageVariable: unset value is replaced by default'
  );

  assert.equal(
    this.player.ads.adMacroReplacement('{pageVariable.testvar3=}'), '',
    'pageVariable: unset value is replaced by default empty string'
  );
});

QUnit.test('tcfMacros', function(assert) {
  let callback;

  const dummyData = {
    cmpId: 10,
    cmpVersion: 27,
    gdprApplies: true,
    tcfPolicyVersion: 2,
    eventStatus: 'cmpuishown',
    cmpStatus: 'loaded',
    listenerId: null,
    tcString: 'abcdefg',
    isServiceSpecific: true,
    useNonStandardStacks: false,
    purposeOneTreatment: false,
    publisherCC: 'DE'
  };

  const oldtcf = window.__tcfapi;

  // Stub of TCF API, enough to register an event listener. The callback is called immediately and on change to consent data.
  // https://github.com/InteractiveAdvertisingBureau/GDPR-Transparency-and-Consent-Framework/blob/master/TCFv2/IAB%20Tech%20Lab%20-%20CMP%20API%20v2.md
  window.__tcfapi = function(cmd, version, cb) {
    if (cmd === 'addEventListener') {
      callback = cb;
      cb(dummyData, true);
    }
  };

  this.player.ads.listenToTcf();

  assert.equal(
    this.player.ads.adMacroReplacement('{tcf.gdprApplies}&{tcf.gdprAppliesInt}&{tcf.tcString}'), 'true&1&abcdefg',
    'tcf macros resolved'
  );

  // Call callbak with changed data
  dummyData.tcString = 'zyxwvu';
  callback(dummyData, true);

  assert.equal(
    this.player.ads.adMacroReplacement('{tcf.tcString}'), 'zyxwvu',
    'tcf macros resolved with updated data'
  );

  window.__tcfapi = oldtcf;
});

QUnit.test('US Privacy macros', function(assert) {
  const done = assert.async();
  const olduspapi = window.__uspapi;

  window.__uspapi = function(cmd, version, cb) {
    if (cmd === 'getUSPData') {
      cb({uspString: '1YNN'}, true);
    }
  };

  this.player.ads.updateUsPrivacyString(() => {
    assert.equal(
      this.player.ads.adMacroReplacement('usp={usp.uspString}'),
      'usp=1YNN',
      'us privacy macro replaced correctly'
    );

    // Update the __uspapi function to return a new uspString
    window.__uspapi = function(cmd, version, cb) {
      if (cmd === 'getUSPData') {
        cb({uspString: '1YNY'}, true);
      }
    };

    // Call updateUsPrivacyString() again to update the uspString
    this.player.ads.updateUsPrivacyString(() => {
      assert.equal(
        this.player.ads.adMacroReplacement('usp={usp.uspString}'),
        'usp=1YNY',
        'us privacy macro replaced correctly after updating the uspString'
      );

      window.__uspapi = olduspapi;
      done();
    });
  });
});

QUnit.test('default macros should not be replaced when disableDefaultMacros is true', function(assert) {
  const string = '{player.id}';
  const customMacros = {
    disableDefaultMacros: true
  };

  const result = this.player.ads.adMacroReplacement(string, false, customMacros);

  assert.strictEqual(result, string, 'default macros should not be replaced');
});

QUnit.test('custom macros should still be replaced when disableDefaultMacros is true', function(assert) {
  const string = '{customMacro}';
  const customMacros = {
    disableDefaultMacros: true, // eslint-disable-line quote-props
    '{customMacro}': 'customValue'
  };

  const result = this.player.ads.adMacroReplacement(string, false, customMacros);

  assert.strictEqual(result, 'customValue', 'custom macros should be replaced');
});

QUnit.test('default macro name should be replaced with custom name when macroNameOverrides is provided', function(assert) {
  const string = '{{PLAYER_ID}}';
  const playerId = 'somePlayerId';
  const customMacros = {
    macroNameOverrides: {
      '{player.id}': '{{PLAYER_ID}}'
    }
  };

  this.player.options_ = {'data-player': playerId};
  this.player.id_ = playerId;

  const result = this.player.ads.adMacroReplacement(string, false, customMacros);

  assert.strictEqual(result, playerId, 'default macro name should be replaced with custom name');
});

QUnit.test('multiple macro name overrides should work correctly', function(assert) {
  const string = '{{PLAYER_ID}}-{{MEDIAINFO_ID}}';
  const playerId = 'somePlayerId';
  const mediaInfoId = 'someMediaInfoId';
  const customMacros = {
    macroNameOverrides: {
      '{player.id}': '{{PLAYER_ID}}',
      '{mediainfo.id}': '{{MEDIAINFO_ID}}'
    }
  };

  this.player.options_ = {'data-player': playerId};
  this.player.id_ = playerId;
  this.player.mediainfo = {id: mediaInfoId};

  const result = this.player.ads.adMacroReplacement(string, false, customMacros);

  assert.strictEqual(result, `${playerId}-${mediaInfoId}`, 'multiple macro name overrides should work correctly');
});

QUnit.test('pageVariable macro names can be overridden', function(assert) {
  window.pageVariableTest = 'globalValue';

  const string = '{{OVERRIDDEN_PAGE_VARIABLE}}';
  const customMacros = {
    macroNameOverrides: {
      '{pageVariable.pageVariableTest}': '{{OVERRIDDEN_PAGE_VARIABLE}}'
    }
  };

  const result = this.player.ads.adMacroReplacement(string, false, customMacros);

  assert.strictEqual(result, 'globalValue', 'pageVariable macros should be overridden correctly');
  delete window.pageVariableTest;
});

QUnit.test('mediainfo.custom_fields macros can be overridden', function(assert) {
  this.player.mediainfo = {
    custom_fields: { // eslint-disable-line
      customFieldTest: 'testValue'
    }
  };

  const string = '{{OVERRIDDEN_CUSTOM_FIELD}}';
  const customMacros = {
    macroNameOverrides: {
      '{mediainfo.custom_fields.customFieldTest}': '{{OVERRIDDEN_CUSTOM_FIELD}}'
    }
  };

  const result = this.player.ads.adMacroReplacement(string, false, customMacros);

  assert.strictEqual(result, 'testValue', 'mediainfo.custom_fields macros should be overridden correctly');
});

QUnit.test('TCF macro names can be overridden', function(assert) {
  const dummyData = {
    cmpId: 10,
    cmpVersion: 27,
    gdprApplies: true,
    tcfPolicyVersion: 2,
    eventStatus: 'cmpuishown',
    cmpStatus: 'loaded',
    listenerId: null,
    tcString: 'abcdefg',
    isServiceSpecific: true,
    useNonStandardStacks: false,
    purposeOneTreatment: false,
    publisherCC: 'DE'
  };

  const oldtcf = window.__tcfapi;

  // Stub of TCF API, enough to register an event listener. The callback is called immediately and on change to consent data.
  // https://github.com/InteractiveAdvertisingBureau/GDPR-Transparency-and-Consent-Framework/blob/master/TCFv2/IAB%20Tech%20Lab%20-%20CMP%20API%20v2.md
  window.__tcfapi = function(cmd, version, cb) {
    if (cmd === 'addEventListener') {
      cb(dummyData, true);
    }
  };

  this.player.ads.listenToTcf();

  const string = '{{GDPR_APPLIES}}&{{GDPR_APPLIES_INT}}&{{TCF_STRING}}';
  const customMacros = {
    macroNameOverrides: {
      '{tcf.gdprApplies}': '{{GDPR_APPLIES}}',
      '{tcf.tcString}': '{{TCF_STRING}}',
      '{tcf.gdprAppliesInt}': '{{GDPR_APPLIES_INT}}'
    }
  };
  const result = this.player.ads.adMacroReplacement(string, false, customMacros);

  assert.equal(result, 'true&1&abcdefg', 'tcf macro names correctly overridden');

  window.__tcfapi = oldtcf;
});

QUnit.test('disableDefaultMacros and macroNameOverrides customMacro properties should not be replaced in string', function(assert) {
  const string = 'disableDefaultMacros-macroNameOverrides';
  const customMacros = {
    disableDefaultMacros: false,
    macroNameOverrides: {
      'default-macro-name': 'new-macro-name'
    }
  };

  const result = this.player.ads.adMacroReplacement(string, false, customMacros);

  assert.strictEqual(result, string, 'special customMacros properties should not be replaced');
});

QUnit.test('regex macro should be replaced when passed in customMacros', function(assert) {
  const string = 'Test: {{url.foo}}';
  const customMacros = {
    'r:{{[\\s]*url.foo[\\s]*}}': 'someValue'
  };

  const result = this.player.ads.adMacroReplacement(string, false, customMacros);

  assert.strictEqual(result, 'Test: someValue', 'regex macro should be replaced correctly');
});

QUnit.test('regex macro should override default macro', function(assert) {
  const string = '{player.id} : {{url.foo}}';
  const playerId = 'somePlayerId';
  const customMacros = {
    macroNameOverrides: {
      '{player.id}': 'r:{{[\\s]*url.foo[\\s]*}}'
    }
  };

  this.player.options_ = {'data-player': playerId};
  this.player.id_ = playerId;

  const result = this.player.ads.adMacroReplacement(string, false, customMacros);

  assert.strictEqual(result, '{player.id} : somePlayerId', 'regex macro should override default macro correctly');
});

QUnit.test('regex macro should replace multiple instances of the pattern', function(assert) {
  const string = 'Test: {{url.foo}} and {{url.foo}} and {{ url.foo }}';
  const customMacros = {
    'r:{{[\\s]*url.foo[\\s]*}}': 'someValue'
  };

  const result = this.player.ads.adMacroReplacement(string, false, customMacros);

  assert.strictEqual(result, 'Test: someValue and someValue and someValue', 'regex macro should replace multiple instances of the pattern');
});

QUnit.test('replaceMacros() should log a warning when invalid regex is passed', function(assert) {
  const string = 'Test: {player.id} - {{url.foo}}';
  const playerId = 'somePlayerId';
  const customMacros = {
    'r:{[\\]}': 'someValue'
  };
  const vjsWarnSpy = sinon.spy(videojs.log, 'warn');

  this.player.options_ = {'data-player': playerId};
  this.player.id_ = playerId;

  const result = this.player.ads.adMacroReplacement(string, false, customMacros);

  assert.strictEqual(result, 'Test: somePlayerId - {{url.foo}}', 'regex macro should not be replaced, but others should be');
  assert.ok(vjsWarnSpy.calledOnce, 'videojs.log.warn should be called once');
  vjsWarnSpy.restore();
});
