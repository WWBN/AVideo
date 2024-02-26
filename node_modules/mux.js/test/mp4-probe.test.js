'use strict';

var
  QUnit = require('qunit'),
  probe = require('../lib/mp4/probe'),
  mp4Helpers = require('./utils/mp4-helpers'),
  box = mp4Helpers.box,
  id3 = require('./utils/id3-generator'),

  // defined below
  moovWithoutMdhd,
  moovWithoutTkhd,
  moofWithTfdt,
  multiMoof,
  multiTraf,
  noTrunSamples,
  v1boxes;

QUnit.module('MP4 Probe');

QUnit.test('reads the timescale from an mdhd', function(assert) {
  // sampleMoov has a base timescale of 1000 with an override to 90kHz
  // in the mdhd
  assert.deepEqual(probe.timescale(new Uint8Array(mp4Helpers.sampleMoov)), {
    1: 90e3,
    2: 90e3
  }, 'found the timescale');
});

QUnit.test('reads tracks', function(assert) {
  var tracks = probe.tracks(new Uint8Array(mp4Helpers.sampleMoov));

  assert.equal(tracks.length, 2, 'two tracks');
  assert.equal(tracks[0].codec, 'avc1.4d400d', 'codec is correct');
  assert.equal(tracks[0].id, 1, 'id is correct');
  assert.equal(tracks[0].type, 'video', 'type is correct');
  assert.equal(tracks[0].timescale, 90e3, 'timescale is correct');

  assert.equal(tracks[1].codec, 'mp4a.40.2', 'codec is correct');
  assert.equal(tracks[1].id, 2, 'id is correct');
  assert.equal(tracks[1].type, 'audio', 'type is correct');
  assert.equal(tracks[1].timescale, 90e3, 'timescale is correct');
});

QUnit.test('returns null if the tkhd is missing', function(assert) {
  assert.equal(probe.timescale(new Uint8Array(moovWithoutTkhd)), null, 'indicated missing info');
});

QUnit.test('returns null if the mdhd is missing', function(assert) {
  assert.equal(probe.timescale(new Uint8Array(moovWithoutMdhd)), null, 'indicated missing info');
});

QUnit.test('startTime reads the base decode time from a tfdt', function(assert) {
  assert.equal(probe.startTime({
    4: 2
  }, new Uint8Array(moofWithTfdt)),
        0x01020304 / 2,
        'calculated base decode time');
});

QUnit.test('startTime returns the earliest base decode time', function(assert) {
  assert.equal(probe.startTime({
    4: 2,
    6: 1
  }, new Uint8Array(multiMoof)),
        0x01020304 / 2,
        'returned the earlier time');
});

QUnit.test('startTime parses 64-bit base decode times', function(assert) {
  assert.equal(probe.startTime({
    4: 3
  }, new Uint8Array(v1boxes)),
        0x0101020304 / 3,
        'parsed a long value');
});

QUnit.test('compositionStartTime calculates composition time using composition time' +
  'offset from first trun sample', function(assert) {
  assert.equal(probe.compositionStartTime({
    1: 6,
    4: 3
  }, new Uint8Array(moofWithTfdt)),
        (0x01020304 + 10) / 3,
        'calculated correct composition start time');
});

QUnit.test('compositionStartTime looks at only the first traf', function(assert) {
  assert.equal(probe.compositionStartTime({
    2: 6,
    4: 3
  }, new Uint8Array(multiTraf)),
        (0x01020304 + 10) / 3,
        'calculated composition start time from first traf');
});

QUnit.test('compositionStartTime uses default composition time offset of 0' +
  'if no trun samples present', function(assert) {
  assert.equal(probe.compositionStartTime({
    2: 6,
    4: 3
  }, new Uint8Array(noTrunSamples)),
        (0x01020304 + 0) / 3,
        'calculated correct composition start time using default offset');
});

QUnit.test('getTimescaleFromMediaHeader gets timescale for version 0 mdhd', function(assert) {
  var mdhd = new Uint8Array([
    0x00, // version 0
    0x00, 0x00, 0x00, // flags
    // version 0 has 32 bit creation_time, modification_time, and duration
    0x00, 0x00, 0x00, 0x02, // creation_time
    0x00, 0x00, 0x00, 0x03, // modification_time
    0x00, 0x00, 0x03, 0xe8, // timescale = 1000
    0x00, 0x00, 0x00, 0x00,
    0x00, 0x00, 0x02, 0x58, // 600 = 0x258 duration
    0x15, 0xc7 // 'eng' language
  ]);

  assert.equal(
    probe.getTimescaleFromMediaHeader(mdhd),
    1000,
    'got timescale from version 0 mdhd'
  );
});

QUnit.test('getTimescaleFromMediaHeader gets timescale for version 0 mdhd', function(assert) {
  var mdhd = new Uint8Array([
    0x01, // version 1
    0x00, 0x00, 0x00, // flags
    // version 1 has 64 bit creation_time, modification_time, and duration
    0x00, 0x00, 0x00, 0x00, 0x00, 0x00, 0x00, 0x02, // creation_time
    0x00, 0x00, 0x00, 0x00, 0x00, 0x00, 0x00, 0x03, // modification_time
    0x00, 0x00, 0x03, 0xe8, // timescale = 1000
    0x00, 0x00, 0x00, 0x00,
    0x00, 0x00, 0x00, 0x00, 0x00, 0x00, 0x02, 0x58, // 600 = 0x258 duration
    0x15, 0xc7 // 'eng' language
  ]);

  assert.equal(
    probe.getTimescaleFromMediaHeader(mdhd),
    1000,
    'got timescale from version 1 mdhd'
  );
});

QUnit.test('can get ID3 data from a v0 EMSG box', function(assert) { 
  var id3Data = new Uint8Array(id3.id3Tag(id3.id3Frame('PRIV', 
    id3.stringToCString('priv-owner@example.com'), 
    id3.stringToInts('foo.bar.id3.com')))
  );

  var v0EmsgId3Data = mp4Helpers.generateEmsgBoxData(0, id3Data);
  var emsgId3Box = new Uint8Array(box('emsg', [].slice.call(v0EmsgId3Data)));
  var emsgBoxes = probe.getEmsgID3(emsgId3Box, 10);
  assert.equal(emsgBoxes[0].cueTime, 20, 'got correct emsg cueTime value from v0 emsg');
  assert.equal(emsgBoxes[0].duration, 0, 'got correct emsg duration value from v0 emsg');
  assert.equal(emsgBoxes[0].frames[0].id, 'PRIV' , 'got correct ID3 id');
  assert.equal(emsgBoxes[0].frames[0].owner, 'priv-owner@example.com', 'got correct ID3 owner');
  assert.deepEqual(emsgBoxes[0].frames[0].data, new Uint8Array(id3.stringToInts('foo.bar.id3.com')), 'got correct ID3 data');
});

QUnit.test('can get ID3 data from a v1 EMSG box', function(assert) { 
  var id3Data = new Uint8Array(id3.id3Tag(id3.id3Frame('TXXX',
    0x03, // utf-8
    id3.stringToCString('foo bar'),
    id3.stringToCString('{ "key": "value" }')),
    [0x00, 0x00])
  );

  var v1EmsgId3Data = mp4Helpers.generateEmsgBoxData(1, id3Data);
  var emsgId3Box = new Uint8Array(box('emsg', [].slice.call(v1EmsgId3Data)));
  var emsgBoxes = probe.getEmsgID3(emsgId3Box);
  assert.equal(emsgBoxes[0].cueTime, 100, 'got correct emsg cueTime value from v1 emsg');
  assert.equal(emsgBoxes[0].duration, 0.01, 'got correct emsg duration value from v1 emsg');
  assert.equal(emsgBoxes[0].frames[0].id, 'TXXX' , 'got correct ID3 id');
  assert.equal(emsgBoxes[0].frames[0].description, 'foo bar', 'got correct ID3 description');
  assert.deepEqual(JSON.parse(emsgBoxes[0].frames[0].data), { key: 'value' }, 'got correct ID3 data');
});

QUnit.test('can get ID3 data from multiple EMSG boxes', function(assert) { 
  var v1id3Data = new Uint8Array(id3.id3Tag(id3.id3Frame('PRIV', 
    id3.stringToCString('priv-owner@example.com'), 
    id3.stringToInts('foo.bar.id3.com')))
  );

  var v0id3Data = new Uint8Array(id3.id3Tag(id3.id3Frame('TXXX',
    0x03, // utf-8
    id3.stringToCString('foo bar'),
    id3.stringToCString('{ "key": "value" }')),
    [0x00, 0x00])
  );

  var v1EmsgId3Data = mp4Helpers.generateEmsgBoxData(1, v1id3Data);
  var v1emsgId3Box = new Uint8Array(box('emsg', [].slice.call(v1EmsgId3Data)));

  var v0EmsgId3Data = mp4Helpers.generateEmsgBoxData(0, v0id3Data);
  var v0emsgId3Box = new Uint8Array(box('emsg', [].slice.call(v0EmsgId3Data)));

  var multiBoxData = new Uint8Array(v1emsgId3Box.length + v0emsgId3Box.length);
  multiBoxData.set(v1emsgId3Box);
  multiBoxData.set(v0emsgId3Box, v1emsgId3Box.length);

  var emsgBoxes = probe.getEmsgID3(multiBoxData);

  assert.equal(emsgBoxes[0].cueTime, 100, 'got correct emsg cueTime value from v1 emsg');
  assert.equal(emsgBoxes[0].duration, 0.01, 'got correct emsg duration value from v1 emsg');
  assert.equal(emsgBoxes[0].frames[0].id, 'PRIV' , 'got correct ID3 id');
  assert.equal(emsgBoxes[0].frames[0].owner, 'priv-owner@example.com', 'got correct ID3 owner');
  assert.deepEqual(emsgBoxes[0].frames[0].data, new Uint8Array(id3.stringToInts('foo.bar.id3.com')), 'got correct ID3 data');


  assert.equal(emsgBoxes[1].cueTime, 10, 'got correct emsg cueTime value from v0 emsg');
  assert.equal(emsgBoxes[1].duration, 0, 'got correct emsg duration value from v0 emsg');
  assert.equal(emsgBoxes[1].frames[0].id, 'TXXX' , 'got correct ID3 id');
  assert.equal(emsgBoxes[1].frames[0].description, 'foo bar', 'got correct ID3 description');
  assert.deepEqual(JSON.parse(emsgBoxes[1].frames[0].data),{ key: 'value' }, 'got correct ID3 data');
});

// ---------
// Test Data
// ---------

moovWithoutTkhd =
  box('moov',
      box('trak',
          box('mdia',
              box('mdhd',
                  0x00, // version 0
                  0x00, 0x00, 0x00, // flags
                  0x00, 0x00, 0x00, 0x02, // creation_time
                  0x00, 0x00, 0x00, 0x03, // modification_time
                  0x00, 0x00, 0x03, 0xe8, // timescale = 1000
                  0x00, 0x00, 0x00, 0x00,
                  0x00, 0x00, 0x02, 0x58, // 600 = 0x258 duration
                  0x15, 0xc7, // 'eng' language
                  0x00, 0x00),
              box('hdlr',
                  0x00, // version 1
                  0x00, 0x00, 0x00, // flags
                  0x00, 0x00, 0x00, 0x00, // pre_defined
                  mp4Helpers.typeBytes('vide'), // handler_type
                  0x00, 0x00, 0x00, 0x00, // reserved
                  0x00, 0x00, 0x00, 0x00, // reserved
                  0x00, 0x00, 0x00, 0x00, // reserved
                  mp4Helpers.typeBytes('one'), 0x00)))); // name

moovWithoutMdhd =
  box('moov',
      box('trak',
          box('tkhd',
              0x01, // version 1
              0x00, 0x00, 0x00, // flags
              0x00, 0x00, 0x00, 0x00,
              0x00, 0x00, 0x00, 0x02, // creation_time
              0x00, 0x00, 0x00, 0x00,
              0x00, 0x00, 0x00, 0x03, // modification_time
              0x00, 0x00, 0x00, 0x01, // track_ID
              0x00, 0x00, 0x00, 0x00, // reserved
              0x00, 0x00, 0x00, 0x00,
              0x00, 0x00, 0x02, 0x58, // 600 = 0x258 duration
              0x00, 0x00, 0x00, 0x00,
              0x00, 0x00, 0x00, 0x00, // reserved
              0x00, 0x00, // layer
              0x00, 0x00, // alternate_group
              0x00, 0x00, // non-audio track volume
              0x00, 0x00, // reserved
              mp4Helpers.unityMatrix,
              0x01, 0x2c, 0x00, 0x00, // 300 in 16.16 fixed-point
              0x00, 0x96, 0x00, 0x00), // 150 in 16.16 fixed-point
          box('mdia',
              box('hdlr',
                  0x01, // version 1
                  0x00, 0x00, 0x00, // flags
                  0x00, 0x00, 0x00, 0x00, // pre_defined
                  mp4Helpers.typeBytes('vide'), // handler_type
                  0x00, 0x00, 0x00, 0x00, // reserved
                  0x00, 0x00, 0x00, 0x00, // reserved
                  0x00, 0x00, 0x00, 0x00, // reserved
                  mp4Helpers.typeBytes('one'), 0x00)))); // name

moofWithTfdt =
  box('moof',
      box('mfhd',
          0x00, // version
          0x00, 0x00, 0x00, // flags
          0x00, 0x00, 0x00, 0x04), // sequence_number
      box('traf',
          box('tfhd',
              0x00, // version
              0x00, 0x00, 0x3b, // flags
              0x00, 0x00, 0x00, 0x04, // track_ID = 4
              0x00, 0x00, 0x00, 0x00,
              0x00, 0x00, 0x00, 0x01, // base_data_offset
              0x00, 0x00, 0x00, 0x02, // sample_description_index
              0x00, 0x00, 0x00, 0x03, // default_sample_duration,
              0x00, 0x00, 0x00, 0x04, // default_sample_size
              0x00, 0x00, 0x00, 0x05),
          box('tfdt',
              0x00, // version
              0x00, 0x00, 0x00, // flags
              0x01, 0x02, 0x03, 0x04), // baseMediaDecodeTime
          box('trun',
            0x00, // version
            0x00, 0x0f, 0x01, // flags: dataOffsetPresent, sampleDurationPresent,
                              // sampleSizePresent, sampleFlagsPresent,
                              // sampleCompositionTimeOffsetsPresent
            0x00, 0x00, 0x00, 0x02, // sample_count
            0x00, 0x00, 0x00, 0x00, // data_offset, no first_sample_flags
            // sample 1
            0x00, 0x00, 0x00, 0x0a, // sample_duration = 10
            0x00, 0x00, 0x00, 0x0a, // sample_size = 10
            0x00, 0x00, 0x00, 0x00, // sample_flags
            0x00, 0x00, 0x00, 0x0a, // signed sample_composition_time_offset = 10
            // sample 2
            0x00, 0x00, 0x00, 0x0a, // sample_duration = 10
            0x00, 0x00, 0x00, 0x0a, // sample_size = 10
            0x00, 0x00, 0x00, 0x00, // sample_flags
            0x00, 0x00, 0x00, 0x14))); // signed sample_composition_time_offset = 20

noTrunSamples =
  box('moof',
      box('mfhd',
          0x00, // version
          0x00, 0x00, 0x00, // flags
          0x00, 0x00, 0x00, 0x04), // sequence_number
      box('traf',
          box('tfhd',
              0x00, // version
              0x00, 0x00, 0x3b, // flags
              0x00, 0x00, 0x00, 0x04, // track_ID = 4
              0x00, 0x00, 0x00, 0x00,
              0x00, 0x00, 0x00, 0x01, // base_data_offset
              0x00, 0x00, 0x00, 0x02, // sample_description_index
              0x00, 0x00, 0x00, 0x03, // default_sample_duration,
              0x00, 0x00, 0x00, 0x04, // default_sample_size
              0x00, 0x00, 0x00, 0x05),
          box('tfdt',
              0x00, // version
              0x00, 0x00, 0x00, // flags
              0x01, 0x02, 0x03, 0x04), // baseMediaDecodeTime
          box('trun',
            0x00, // version
            0x00, 0x0f, 0x01, // flags: dataOffsetPresent, sampleDurationPresent,
                              // sampleSizePresent, sampleFlagsPresent,
                              // sampleCompositionTimeOffsetsPresent
            0x00, 0x00, 0x00, 0x00, // sample_count
            0x00, 0x00, 0x00, 0x00))); // data_offset, no first_sample_flags


multiTraf =
  box('moof',
      box('mfhd',
          0x00, // version
          0x00, 0x00, 0x00, // flags
          0x00, 0x00, 0x00, 0x04), // sequence_number
      box('traf',
          box('tfhd',
              0x00, // version
              0x00, 0x00, 0x3b, // flags
              0x00, 0x00, 0x00, 0x04, // track_ID = 4
              0x00, 0x00, 0x00, 0x00,
              0x00, 0x00, 0x00, 0x01, // base_data_offset
              0x00, 0x00, 0x00, 0x02, // sample_description_index
              0x00, 0x00, 0x00, 0x03, // default_sample_duration,
              0x00, 0x00, 0x00, 0x04, // default_sample_size
              0x00, 0x00, 0x00, 0x05),
          box('tfdt',
              0x00, // version
              0x00, 0x00, 0x00, // flags
              0x01, 0x02, 0x03, 0x04), // baseMediaDecodeTime
          box('trun',
            0x00, // version
            0x00, 0x0f, 0x01, // flags: dataOffsetPresent, sampleDurationPresent,
                              // sampleSizePresent, sampleFlagsPresent,
                              // sampleCompositionTimeOffsetsPresent
            0x00, 0x00, 0x00, 0x02, // sample_count
            0x00, 0x00, 0x00, 0x00, // data_offset, no first_sample_flags
            // sample 1
            0x00, 0x00, 0x00, 0x0a, // sample_duration = 10
            0x00, 0x00, 0x00, 0x0a, // sample_size = 10
            0x00, 0x00, 0x00, 0x00, // sample_flags
            0x00, 0x00, 0x00, 0x0a, // signed sample_composition_time_offset = 10
            // sample 2
            0x00, 0x00, 0x00, 0x0a, // sample_duration = 10
            0x00, 0x00, 0x00, 0x0a, // sample_size = 10
            0x00, 0x00, 0x00, 0x00, // sample_flags
            0x00, 0x00, 0x00, 0x14)), // signed sample_composition_time_offset = 20
        box('traf',
            box('tfhd',
                0x00, // version
                0x00, 0x00, 0x3b, // flags
                0x00, 0x00, 0x00, 0x02, // track_ID = 2
                0x00, 0x00, 0x00, 0x00,
                0x00, 0x00, 0x00, 0x01, // base_data_offset
                0x00, 0x00, 0x00, 0x02, // sample_description_index
                0x00, 0x00, 0x00, 0x03, // default_sample_duration,
                0x00, 0x00, 0x00, 0x04, // default_sample_size
                0x00, 0x00, 0x00, 0x05),
            box('tfdt',
                0x00, // version
                0x00, 0x00, 0x00, // flags
                0x01, 0x02, 0x01, 0x02), // baseMediaDecodeTime
            box('trun',
              0x00, // version
              0x00, 0x0f, 0x01, // flags: dataOffsetPresent, sampleDurationPresent,
                                // sampleSizePresent, sampleFlagsPresent,
                                // sampleCompositionTimeOffsetsPresent
              0x00, 0x00, 0x00, 0x02, // sample_count
              0x00, 0x00, 0x00, 0x00, // data_offset, no first_sample_flags
              // sample 1
              0x00, 0x00, 0x00, 0x0a, // sample_duration = 10
              0x00, 0x00, 0x00, 0x0a, // sample_size = 10
              0x00, 0x00, 0x00, 0x00, // sample_flags
              0x00, 0x00, 0x00, 0x0b, // signed sample_composition_time_offset = 11
              // sample 2
              0x00, 0x00, 0x00, 0x0a, // sample_duration = 10
              0x00, 0x00, 0x00, 0x0a, // sample_size = 10
              0x00, 0x00, 0x00, 0x00, // sample_flags
              0x00, 0x00, 0x00, 0x05))); // signed sample_composition_time_offset = 5

multiMoof = moofWithTfdt
  .concat(box('moof',
              box('mfhd',
                  0x00, // version
                  0x00, 0x00, 0x00, // flags
                  0x00, 0x00, 0x00, 0x04), // sequence_number
              box('traf',
                  box('tfhd',
                      0x00, // version
                      0x00, 0x00, 0x3b, // flags
                      0x00, 0x00, 0x00, 0x06, // track_ID = 6
                      0x00, 0x00, 0x00, 0x00,
                      0x00, 0x00, 0x00, 0x01, // base_data_offset
                      0x00, 0x00, 0x00, 0x02, // sample_description_index
                      0x00, 0x00, 0x00, 0x03, // default_sample_duration,
                      0x00, 0x00, 0x00, 0x04, // default_sample_size
                      0x00, 0x00, 0x00, 0x05),
                  box('tfdt',
                      0x00, // version
                      0x00, 0x00, 0x00, // flags
                      0x01, 0x02, 0x03, 0x04), // baseMediaDecodeTime
                  box('trun',
                    0x00, // version
                    0x00, 0x0f, 0x01, // flags: dataOffsetPresent, sampleDurationPresent,
                                      // sampleSizePresent, sampleFlagsPresent,
                                      // sampleCompositionTimeOffsetsPresent
                    0x00, 0x00, 0x00, 0x02, // sample_count
                    0x00, 0x00, 0x00, 0x00, // data_offset, no first_sample_flags
                    // sample 1
                    0x00, 0x00, 0x00, 0x0a, // sample_duration = 10
                    0x00, 0x00, 0x00, 0x0a, // sample_size = 10
                    0x00, 0x00, 0x00, 0x00, // sample_flags
                    0x00, 0x00, 0x00, 0x14, // signed sample_composition_time_offset = 20
                    // sample 2
                    0x00, 0x00, 0x00, 0x0a, // sample_duration = 10
                    0x00, 0x00, 0x00, 0x0a, // sample_size = 10
                    0x00, 0x00, 0x00, 0x00, // sample_flags
                    0x00, 0x00, 0x00, 0x0a)))); // signed sample_composition_time_offset = 10
v1boxes =
  box('moof',
      box('mfhd',
          0x01, // version
          0x00, 0x00, 0x00, // flags
          0x00, 0x00, 0x00, 0x04), // sequence_number
      box('traf',
          box('tfhd',
              0x01, // version
              0x00, 0x00, 0x3b, // flags
              0x00, 0x00, 0x00, 0x04, // track_ID = 4
              0x00, 0x00, 0x00, 0x00,
              0x00, 0x00, 0x00, 0x01, // base_data_offset
              0x00, 0x00, 0x00, 0x02, // sample_description_index
              0x00, 0x00, 0x00, 0x03, // default_sample_duration,
              0x00, 0x00, 0x00, 0x04, // default_sample_size
              0x00, 0x00, 0x00, 0x05),
          box('tfdt',
              0x01, // version
              0x00, 0x00, 0x00, // flags
              0x00, 0x00, 0x00, 0x01,
              0x01, 0x02, 0x03, 0x04))); // baseMediaDecodeTime
