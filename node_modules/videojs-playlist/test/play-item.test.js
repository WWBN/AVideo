import QUnit from 'qunit';
import sinon from 'sinon';
import playItem from '../src/play-item';
import {clearTracks} from '../src/play-item';
import playerProxyMaker from './player-proxy-maker';

QUnit.module('play-item');

QUnit.test('clearTracks will try and remove all tracks', function(assert) {
  const player = playerProxyMaker();
  const remoteTracks = [1, 2, 3];
  const removedTracks = [];

  player.remoteTextTracks = function() {
    return remoteTracks;
  };

  player.removeRemoteTextTrack = function(tt) {
    removedTracks.push(tt);
  };

  clearTracks(player);

  assert.deepEqual(
    removedTracks.sort(),
    remoteTracks.sort(),
    'the removed tracks are equivalent to our remote tracks'
  );
});

QUnit.test('will not try to play if paused', function(assert) {
  const player = playerProxyMaker();
  let tryPlay = false;

  player.paused = function() {
    return true;
  };

  player.play = function() {
    tryPlay = true;
  };

  playItem(player, {
    sources: [1, 2, 3],
    textTracks: [4, 5, 6],
    poster: 'http://example.com/poster.png'
  });

  assert.ok(!tryPlay, 'we did not reply on paused');
});

QUnit.test('will try to play if not paused', function(assert) {
  const player = playerProxyMaker();
  let tryPlay = false;

  player.paused = function() {
    return false;
  };

  player.play = function() {
    tryPlay = true;
  };

  playItem(player, {
    sources: [1, 2, 3],
    textTracks: [4, 5, 6],
    poster: 'http://example.com/poster.png'
  });

  assert.ok(tryPlay, 'we replayed on not-paused');
});

QUnit.test('will not try to play if paused and not ended', function(assert) {
  const player = playerProxyMaker();
  let tryPlay = false;

  player.paused = function() {
    return true;
  };

  player.ended = function() {
    return false;
  };

  player.play = function() {
    tryPlay = true;
  };

  playItem(player, {
    sources: [1, 2, 3],
    textTracks: [4, 5, 6],
    poster: 'http://example.com/poster.png'
  });

  assert.ok(!tryPlay, 'we did not replaye on paused and not ended');
});

QUnit.test('will try to play if paused and ended', function(assert) {
  const player = playerProxyMaker();
  let tryPlay = false;

  player.paused = function() {
    return true;
  };

  player.ended = function() {
    return true;
  };

  player.play = function() {
    tryPlay = true;
  };

  playItem(player, {
    sources: [1, 2, 3],
    poster: 'http://example.com/poster.png'
  });

  assert.ok(tryPlay, 'we replayed on not-paused');
});

QUnit.test('fires "beforeplaylistitem" and "playlistitem"', function(assert) {
  const player = playerProxyMaker();
  const beforeSpy = sinon.spy();
  const spy = sinon.spy();

  player.on('beforeplaylistitem', beforeSpy);
  player.on('playlistitem', spy);

  playItem(player, {
    sources: [1, 2, 3],
    poster: 'http://example.com/poster.png'
  });

  assert.strictEqual(beforeSpy.callCount, 1);
  assert.strictEqual(spy.callCount, 1);
});
