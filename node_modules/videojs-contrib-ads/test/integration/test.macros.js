import QUnit from 'qunit';
import window from 'global/window';
import document from 'global/document';
import sharedModuleHooks from './lib/shared-module-hooks.js';

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
    'Nested 4x: {pageVariable.animal.cat.champion.name}');

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
    'Nested 4x: Champ'
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
