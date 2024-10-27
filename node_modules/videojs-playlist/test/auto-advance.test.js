import window from 'global/window';
import QUnit from 'qunit';
import sinon from 'sinon';
import * as autoadvance from '../src/auto-advance.js';
import playerProxyMaker from './player-proxy-maker.js';

QUnit.module('auto-advance');

QUnit.test('set up ended listener if one does not exist yet', function(assert) {
  const player = playerProxyMaker();
  const ones = [];

  player.one = function(type) {
    if (Array.isArray(type)) {
      ones.push(...type);
    } else {
      ones.push(type);
    }
  };

  autoadvance.setup(player, 0);

  assert.equal(ones.length, 3, 'there should have been three event added');
  assert.deepEqual(ones, ['ended', 'abort', 'error'], 'the events we want to one is "ended", "abort" and "error"');
});

QUnit.test('off previous listener if exists before adding a new one', function(assert) {
  const player = playerProxyMaker();
  const ones = [];
  const offs = [];

  player.one = function(type) {
    if (Array.isArray(type)) {
      ones.push(...type);
    } else {
      ones.push(type);
    }
  };

  player.off = function(type) {
    if (Array.isArray(type)) {
      offs.push(...type);
    } else {
      offs.push(type);
    }
  };

  autoadvance.setup(player, 0);
  assert.equal(ones.length, 3, 'there should have been only three one events added');
  assert.deepEqual(ones, ['ended', 'abort', 'error'], 'the events we want to one is "ended", "abort" and "error"');
  assert.equal(offs.length, 0, 'we should not have off-ed anything yet');

  autoadvance.setup(player, 10);

  assert.equal(ones.length, 6, 'there should have been six one event added');
  assert.equal(ones[0], 'ended', 'first event to one is "ended"');
  assert.equal(ones[1], 'abort', 'second event to one is "abort"');
  assert.equal(ones[2], 'error', 'third event to one is "error"');

  assert.equal(ones[3], 'ended', 'fourth event to one is "ended"');
  assert.equal(ones[4], 'abort', 'fifth event to one is "abort"');
  assert.equal(ones[5], 'error', 'sixth event to one is "error"');

  assert.equal(offs.length, 3, 'there should have been three off event added');
  assert.deepEqual(offs.sort(), ['ended', 'abort', 'error'].sort(), 'the events we want to off is "ended", "abort" and "error"');
});

QUnit.test('do nothing if timeout is weird', function(assert) {
  const player = playerProxyMaker();

  const ones = [];
  const offs = [];

  player.one = function(type) {
    ones.push(type);
  };

  player.off = function(type) {
    offs.push(type);
  };

  autoadvance.setup(player, -1);
  autoadvance.setup(player, -100);
  autoadvance.setup(player, null);
  autoadvance.setup(player, {});
  autoadvance.setup(player, []);

  assert.equal(offs.length, 0, 'we did nothing');
  assert.equal(ones.length, 0, 'we did nothing');
});

QUnit.test('reset if timeout is weird after we advance', function(assert) {
  const player = playerProxyMaker();

  const ones = [];
  const offs = [];

  player.one = function(type) {
    if (Array.isArray(type)) {
      ones.push(...type);
    } else {
      ones.push(type);
    }
  };

  player.off = function(type) {
    if (Array.isArray(type)) {
      offs.push(...type);
    } else {
      offs.push(type);
    }
  };

  autoadvance.setup(player, 0);
  autoadvance.setup(player, -1);
  autoadvance.setup(player, 0);
  autoadvance.setup(player, -100);
  autoadvance.setup(player, 0);
  autoadvance.setup(player, null);
  autoadvance.setup(player, 0);
  autoadvance.setup(player, {});
  autoadvance.setup(player, 0);
  autoadvance.setup(player, []);
  autoadvance.setup(player, 0);
  autoadvance.setup(player, NaN);
  autoadvance.setup(player, 0);
  autoadvance.setup(player, Infinity);
  autoadvance.setup(player, 0);
  autoadvance.setup(player, -Infinity);

  assert.equal(offs.length, 24, 'we reset the advance 8 times, removing 3 events each time');
  assert.equal(ones.length, 24, 'we autoadvanced 8 times, adding 3 events each time');
});

QUnit.test('reset if we have already started advancing', function(assert) {
  const player = playerProxyMaker();
  const oldClearTimeout = window.clearTimeout;
  let clears = 0;

  window.clearTimeout = function() {
    clears++;
  };

  // pretend we started autoadvancing
  player.playlist.autoadvance_.timeout = 1;
  autoadvance.setup(player, 0);

  assert.equal(clears, 1, 'we reset the auto advance');

  window.clearTimeout = oldClearTimeout;
});

QUnit.test('timeout is given in seconds', function(assert) {
  const player = playerProxyMaker();
  const oldSetTimeout = window.setTimeout;

  player.addEventListener = Function.prototype;

  window.setTimeout = function(fn, timeout) {
    assert.equal(timeout, 10 * 1000, 'timeout was given in seconds');
  };

  autoadvance.setup(player, 10);
  player.trigger('ended');

  window.setTimeout = oldSetTimeout;
});

QUnit.test('cancel a pending auto-advance if play is requested', function(assert) {
  const clock = sinon.useFakeTimers();
  const player = playerProxyMaker();

  sinon.spy(player.playlist, 'next');

  autoadvance.setup(player, 10);
  player.trigger('ended');
  clock.tick(10000);

  assert.equal(player.playlist.next.callCount, 1, 'next was called');

  autoadvance.setup(player, 10);
  player.trigger('ended');
  clock.tick(5000);
  player.trigger('play');
  clock.tick(5000);

  assert.equal(player.playlist.next.callCount, 1, 'next was not called because a "play" occurred');

  player.trigger('ended');
  clock.tick(10000);

  assert.equal(player.playlist.next.callCount, 2, 'next was called again because the content ended again and the appropriate wait time elapsed');
});
