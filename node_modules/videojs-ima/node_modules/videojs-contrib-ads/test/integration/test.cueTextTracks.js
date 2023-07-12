import QUnit from 'qunit';
import sinon from 'sinon';
import sharedModuleHooks from './lib/shared-module-hooks.js';

QUnit.module('Cue Metadata Text Tracks', sharedModuleHooks({

  beforeEach() {
    this.tt = {
      player: this.player,
      kind: 'metadata',
      mode: 'hidden',
      id: '1',
      startTime: 1,
      endTime: 2,
      addEventListener(event, cb) {
        if (event === 'cuechange') {
          cb.apply(this, [this]);
        }
      },
      activeCues: []
    };
  },
  afterEach() {
    this.player.ads.cueTextTracks.getSupportedAdCue = function(player, cue) {
      return cue;
    };
    this.player.ads.cueTextTracks.getCueId = function(cue) {
      return cue.id;
    };
    this.player.ads.cueTextTracks.setMetadataTrackMode = function(track) {
      track.mode = 'hidden';
    };
  }
}));

QUnit.test('runs processMetadataTrack callback as tracks are added', function(assert) {
  const tt = this.tt;
  const processMetadataTrackSpy = sinon.spy();
  const cueTextTracks = this.player.ads.cueTextTracks;

  // Start by adding a text track before processing
  this.player.addRemoteTextTrack(tt);

  cueTextTracks.processMetadataTracks(this.player, processMetadataTrackSpy);
  assert.strictEqual(processMetadataTrackSpy.callCount, 1);

  // add a new text track after initial processing
  this.player.textTracks().trigger({
    track: this.tt,
    type: 'addtrack'
  });
  assert.strictEqual(processMetadataTrackSpy.callCount, 2);
});

QUnit.test('does not call processMetadataTrack callback until tracks available', function(assert) {
  const processMetadataTrackSpy = sinon.spy();
  const cueTextTracks = this.player.ads.cueTextTracks;

  cueTextTracks.processMetadataTracks(this.player, processMetadataTrackSpy);
  assert.strictEqual(processMetadataTrackSpy.callCount, 0);

  const addTrackEvent = {
    track: this.tt,
    type: 'addtrack'
  };

  this.player.textTracks().trigger(addTrackEvent);
  assert.strictEqual(processMetadataTrackSpy.callCount, 1);
});

QUnit.test('setMetadataTrackMode should work when overriden', function(assert) {
  const tt = this.tt;
  const cueTextTracks = this.player.ads.cueTextTracks;

  cueTextTracks.setMetadataTrackMode(tt);
  assert.strictEqual(tt.mode, 'hidden');

  cueTextTracks.setMetadataTrackMode = function(track) {
    track.mode = 'disabled';
  };
  cueTextTracks.setMetadataTrackMode(tt);
  assert.strictEqual(tt.mode, 'disabled');
});

QUnit.test('getSupportedAdCue should work when overriden', function(assert) {
  const cue = {
    startTime: 0,
    endTime: 1
  };

  const cueTextTracks = this.player.ads.cueTextTracks;
  let supportedCue = cueTextTracks.getSupportedAdCue(this.player, cue);

  assert.strictEqual(supportedCue, cue);

  cueTextTracks.getSupportedAdCue = function(player, subcue) {
    return -1;
  };
  supportedCue = cueTextTracks.getSupportedAdCue(this.player, cue);
  assert.strictEqual(supportedCue, -1);
});

QUnit.test('getCueId should work when overriden', function(assert) {
  const originalTextTracks = this.player.textTracks;
  const cue = {
    startTime: 0,
    endTime: 1,
    id: 1,
    inner: {
      id: 2
    }
  };
  const tt = this.tt;

  tt.activeCues = [cue];

  this.player.textTracks = function() {
    return {
      length: 1,
      0: tt
    };
  };

  const cueTextTracks = this.player.ads.cueTextTracks;
  let cueId = cueTextTracks.getCueId(cue);

  assert.strictEqual(cueId, 1);

  cueTextTracks.getCueId = function(subcue) {
    return subcue.inner.id;
  };
  cueId = cueTextTracks.getCueId(cue);
  assert.strictEqual(cueId, 2);

  // Clean Up
  this.player.textTracks = originalTextTracks;
});

QUnit.test('processAdTrack runs processCue callback', function(assert) {
  const processCueSpy = sinon.spy();
  const cueTextTracks = this.player.ads.cueTextTracks;
  const cues = [{
    startTime: 0,
    endTime: 1,
    id: 1,
    callCount: 0
  }];

  cueTextTracks.processAdTrack(this.player, cues, processCueSpy);
  assert.strictEqual(processCueSpy.callCount, 1);

  const processCue = function(player, cueData, cueId, startTime) {
    cueData.callCount += 1;
  };

  cueTextTracks.processAdTrack(this.player, cues, processCue);
  assert.strictEqual(cues[0].callCount, 1);
});

QUnit.test('processAdTrack runs cancelAds callback', function(assert) {
  const cancelAdsSpy = sinon.spy();
  const cueTextTracks = this.player.ads.cueTextTracks;
  const cues = [{
    startTime: 0,
    endTime: 1,
    id: 1,
    callCount: 0
  }];
  const processCue = function(player, cueData, cueId, startTime) {
    return;
  };
  const cancelAds = function(player, cueData, cueId, startTime) {
    cueData.callCount += 1;
  };

  cueTextTracks.processAdTrack(this.player, cues, processCue, cancelAdsSpy);
  assert.strictEqual(cancelAdsSpy.callCount, 1);

  cueTextTracks.processAdTrack(this.player, cues, processCue, cancelAds);
  assert.strictEqual(cues[0].callCount, 1);
});
