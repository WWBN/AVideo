import QUnit from 'qunit';
import sinon from 'sinon';
import playlistMaker from '../src/playlist-maker';
import * as autoadvance from '../src/auto-advance';
import playerProxyMaker from './player-proxy-maker';

const videoList = [{
  sources: [{
    src: 'http://media.w3.org/2010/05/sintel/trailer.mp4',
    type: 'video/mp4'
  }],
  poster: 'http://media.w3.org/2010/05/sintel/poster.png'
}, {
  sources: [{
    src: 'http://media.w3.org/2010/05/bunny/trailer.mp4',
    type: 'video/mp4'
  }],
  poster: 'http://media.w3.org/2010/05/bunny/poster.png'
}, {
  sources: [{
    src: 'http://vjs.zencdn.net/v/oceans.mp4',
    type: 'video/mp4'
  }],
  poster: 'http://www.videojs.com/img/poster.jpg'
}, {
  sources: [{
    src: 'http://media.w3.org/2010/05/bunny/movie.mp4',
    type: 'video/mp4'
  }],
  poster: 'http://media.w3.org/2010/05/bunny/poster.png'
}, {
  sources: [{
    src: 'http://media.w3.org/2010/05/video/movie_300.mp4',
    type: 'video/mp4'
  }],
  poster: 'http://media.w3.org/2010/05/video/poster.png'
}];

QUnit.module('playlist-maker', {

  beforeEach() {
    this.clock = sinon.useFakeTimers();
  },

  afterEach() {
    this.clock.restore();
  }
});

QUnit.test('playlistMaker takes a player and a list and returns a playlist', function(assert) {
  const playlist = playlistMaker(playerProxyMaker(), []);

  assert.equal(typeof playlist, 'function', 'playlist is a function');
  assert.equal(
    typeof playlist.autoadvance,
    'function',
    'we have a autoadvance function'
  );

  assert.equal(
    typeof playlist.currentItem,
    'function',
    'we have a currentItem function'
  );

  assert.equal(typeof playlist.first, 'function', 'we have a first function');
  assert.equal(typeof playlist.indexOf, 'function', 'we have a indexOf function');
  assert.equal(typeof playlist.next, 'function', 'we have a next function');
  assert.equal(typeof playlist.previous, 'function', 'we have a previous function');
});

QUnit.test('playlistMaker can either take nothing or an Array as its first argument', function(assert) {
  const playlist1 = playlistMaker(playerProxyMaker());
  const playlist2 = playlistMaker(playerProxyMaker(), 'foo');
  const playlist3 = playlistMaker(playerProxyMaker(), {foo: [1, 2, 3]});

  assert.deepEqual(playlist1(), [], 'if given no initial array, default to an empty array');

  assert.deepEqual(playlist2(), [], 'if given no initial array, default to an empty array');

  assert.deepEqual(playlist3(), [], 'if given no initial array, default to an empty array');
});

QUnit.test('playlist() is a getter and setter for the list', function(assert) {
  const playlist = playlistMaker(playerProxyMaker(), [1, 2, 3]);

  assert.deepEqual(playlist(), [1, 2, 3], 'equal to input list');

  assert.deepEqual(
    playlist([1, 2, 3, 4, 5]),
    [1, 2, 3, 4, 5],
    'equal to input list, arguments ignored'
  );

  assert.deepEqual(playlist(), [1, 2, 3, 4, 5], 'equal to input list');

  const list = playlist();

  list.unshift(10);

  assert.deepEqual(
    playlist(),
    [1, 2, 3, 4, 5],
    'changing the list did not affect the playlist'
  );

  assert.notDeepEqual(
    playlist(),
    [10, 1, 2, 3, 4, 5],
    'changing the list did not affect the playlist'
  );
});

QUnit.test('playlist() should only accept an Array as a new playlist', function(assert) {
  const playlist = playlistMaker(playerProxyMaker(), [1, 2, 3]);

  assert.deepEqual(
    playlist('foo'),
    [1, 2, 3],
    'when given "foo", it should be treated as a getter'
  );

  assert.deepEqual(
    playlist({foo: [1, 2, 3]}),
    [1, 2, 3],
    'when given {foo: [1,2,3]}, it should be treated as a getter'
  );
});

QUnit.test('playlist.currentItem() works as expected', function(assert) {
  const player = playerProxyMaker();
  const playlist = playlistMaker(player, videoList);
  let src;

  player.src = function(s) {
    if (s) {
      if (typeof s === 'string') {
        src = s;
      } else if (Array.isArray(s)) {
        return player.src(s[0]);
      } else {
        return player.src(s.src);
      }
    }
  };

  player.currentSrc = function() {
    return src;
  };

  src = videoList[0].sources[0].src;

  assert.equal(playlist.currentItem(), 0, 'begin at the first item, item 0');

  assert.equal(
    playlist.currentItem(2),
    2,
    'setting to item 2 gives us back the new item index'
  );

  assert.equal(playlist.currentItem(), 2, 'the current item is now 2');
  assert.equal(playlist.currentItem(5), 2, 'cannot change to an out-of-bounds item');
  assert.equal(playlist.currentItem(-1), 2, 'cannot change to an out-of-bounds item');
  assert.equal(playlist.currentItem(null), 2, 'cannot change to an invalid item');
  assert.equal(playlist.currentItem(NaN), 2, 'cannot change to an invalid item');
  assert.equal(playlist.currentItem(Infinity), 2, 'cannot change to an invalid item');
  assert.equal(playlist.currentItem(-Infinity), 2, 'cannot change to an invalid item');
});

QUnit.test('playlist.currentItem() shows a poster by default', function(assert) {
  const player = playerProxyMaker();
  const playlist = playlistMaker(player, videoList);

  playlist.currentItem(0);
  assert.notEqual(player.poster(), '', 'poster is shown for playlist index 0');
});

QUnit.test('playlist.currentItem() will hide the poster if suppressPoster param is true', function(assert) {
  const player = playerProxyMaker();
  const playlist = playlistMaker(player, videoList);

  playlist.currentItem(1, true);
  assert.equal(player.poster(), '', 'poster is suppressed');
});

QUnit.test('playlist.currentItem() returns -1 with an empty playlist', function(assert) {
  const playlist = playlistMaker(playerProxyMaker(), []);

  assert.equal(playlist.currentItem(), -1, 'we should get a -1 with an empty playlist');
});

QUnit.test('playlist.currentItem() does not change items if same index is given', function(assert) {
  const player = playerProxyMaker();
  let sources = 0;
  let src;

  player.src = function(s) {
    if (s) {
      if (typeof s === 'string') {
        src = s;
      } else if (Array.isArray(s)) {
        return player.src(s[0]);
      } else {
        return player.src(s.src);
      }
    }

    sources++;
  };

  player.currentSrc = function() {
    return src;
  };

  const playlist = playlistMaker(player, videoList);

  assert.equal(sources, 1, 'we switched to the first playlist item');
  sources = 0;

  assert.equal(playlist.currentItem(), 0, 'we start at index 0');

  playlist.currentItem(0);
  assert.equal(sources, 0, 'we did not try to set sources');

  playlist.currentItem(1);
  assert.equal(sources, 1, 'we did try to set sources');

  playlist.currentItem(1);
  assert.equal(sources, 1, 'we did not try to set sources');
});

QUnit.test('playlistMaker accepts a starting index', function(assert) {
  const player = playerProxyMaker();
  let src;

  player.src = function(s) {
    if (s) {
      if (typeof s === 'string') {
        src = s;
      } else if (Array.isArray(s)) {
        return player.src(s[0]);
      } else {
        return player.src(s.src);
      }
    }
  };

  player.currentSrc = function() {
    return src;
  };

  const playlist = playlistMaker(player, videoList, 1);

  assert.equal(playlist.currentItem(), 1, 'if given an initial index, load that video');
});

QUnit.test('playlistMaker accepts a starting index', function(assert) {
  const player = playerProxyMaker();
  let src;

  player.src = function(s) {
    if (s) {
      if (typeof s === 'string') {
        src = s;
      } else if (Array.isArray(s)) {
        return player.src(s[0]);
      } else {
        return player.src(s.src);
      }
    }
  };

  player.currentSrc = function() {
    return src;
  };

  const playlist = playlistMaker(player, videoList, -1);

  assert.equal(playlist.currentItem(), -1, 'if given -1 as initial index, load no video');
});

QUnit.test('playlist.contains() works as expected', function(assert) {
  const player = playerProxyMaker();
  const playlist = playlistMaker(player, videoList);

  player.playlist = playlist;

  assert.ok(
    playlist.contains('http://media.w3.org/2010/05/sintel/trailer.mp4'),
    'we can ask whether it contains a source string'
  );

  assert.ok(
    playlist.contains(['http://media.w3.org/2010/05/sintel/trailer.mp4']),
    'we can ask whether it contains a sources list of strings'
  );

  assert.ok(
    playlist.contains([{
      src: 'http://media.w3.org/2010/05/sintel/trailer.mp4',
      type: 'video/mp4'
    }]),
    'we can ask whether it contains a sources list of objects'
  );

  assert.ok(
    playlist.contains({
      sources: ['http://media.w3.org/2010/05/sintel/trailer.mp4']
    }),
    'we can ask whether it contains a playlist item'
  );

  assert.ok(
    playlist.contains({
      sources: [{
        src: 'http://media.w3.org/2010/05/sintel/trailer.mp4',
        type: 'video/mp4'
      }]
    }),
    'we can ask whether it contains a playlist item'
  );

  assert.ok(
    !playlist.contains('http://media.w3.org/2010/05/sintel/poster.png'),
    'we get false for a non-existent source string'
  );

  assert.ok(
    !playlist.contains(['http://media.w3.org/2010/05/sintel/poster.png']),
    'we get false for a non-existent source list of strings'
  );

  assert.ok(
    !playlist.contains([{
      src: 'http://media.w3.org/2010/05/sintel/poster.png',
      type: 'video/mp4'
    }]),
    'we get false for a non-existent source list of objects'
  );

  assert.ok(!playlist.contains({
    sources: ['http://media.w3.org/2010/05/sintel/poster.png']
  }), 'we can ask whether it contains a playlist item');

  assert.ok(
    !playlist.contains({
      sources: [{
        src: 'http://media.w3.org/2010/05/sintel/poster.png',
        type: 'video/mp4'
      }]
    }),
    'we get false for a non-existent playlist item'
  );
});

QUnit.test('playlist.indexOf() works as expected', function(assert) {
  const player = playerProxyMaker();
  const playlist = playlistMaker(player, videoList);

  const mixedSourcesPlaylist = playlistMaker(
    player,
    [{
      sources: [{
        src: 'http://media.w3.org/2010/05/sintel/trailer.mp4',
        type: 'video/mp4'
      }, {
        app_name: 'rtmp://example.com/sintel/trailer', // eslint-disable-line
        avg_bitrate: 4255000, // eslint-disable-line
        codec: 'H264',
        container: 'MP4'
      }],
      poster: 'http://media.w3.org/2010/05/sintel/poster.png'
    }]
  );

  player.playlist = playlist;

  assert.equal(
    playlist.indexOf('http://media.w3.org/2010/05/sintel/trailer.mp4'),
    0,
    'sintel trailer is first item'
  );

  assert.equal(
    playlist.indexOf('//media.w3.org/2010/05/sintel/trailer.mp4'),
    0,
    'sintel trailer is first item, protocol-relative url considered equal'
  );

  assert.equal(
    playlist.indexOf(['http://media.w3.org/2010/05/bunny/trailer.mp4']),
    1,
    'bunny trailer is second item'
  );

  assert.equal(
    playlist.indexOf([{
      src: 'http://vjs.zencdn.net/v/oceans.mp4',
      type: 'video/mp4'
    }]),
    2,
    'oceans is third item'
  );

  assert.equal(
    playlist.indexOf({
      sources: ['http://media.w3.org/2010/05/bunny/movie.mp4']
    }),
    3,
    'bunny movie is fourth item'
  );

  assert.equal(
    playlist.indexOf({
      sources: [{
        src: 'http://media.w3.org/2010/05/video/movie_300.mp4',
        type: 'video/mp4'
      }]
    }),
    4,
    'timer video is fifth item'
  );

  assert.equal(
    playlist.indexOf('http://media.w3.org/2010/05/sintel/poster.png'),
    -1,
    'poster.png does not exist'
  );

  assert.equal(
    playlist.indexOf(['http://media.w3.org/2010/05/sintel/poster.png']),
    -1,
    'poster.png does not exist'
  );

  assert.equal(
    playlist.indexOf([{
      src: 'http://media.w3.org/2010/05/sintel/poster.png',
      type: 'video/mp4'
    }]),
    -1,
    'poster.png does not exist'
  );

  assert.equal(
    playlist.indexOf({
      sources: ['http://media.w3.org/2010/05/sintel/poster.png']
    }),
    -1,
    'poster.png does not exist'
  );

  assert.equal(
    playlist.indexOf({
      sources: [{
        src: 'http://media.w3.org/2010/05/sintel/poster.png',
        type: 'video/mp4'
      }]
    }),
    -1,
    'poster.png does not exist'
  );

  assert.equal(
    mixedSourcesPlaylist.indexOf({
      sources: [{
        src: 'http://media.w3.org/2010/05/bunny/movie.mp4',
        type: 'video/mp4'
      }, {
        app_name: 'rtmp://example.com/bunny/movie', // eslint-disable-line
        avg_bitrate: 4255000, // eslint-disable-line
        codec: 'H264',
        container: 'MP4'
      }],
      poster: 'http://media.w3.org/2010/05/sintel/poster.png'
    }),
    -1,
    'bunny movie does not exist'
  );

  assert.equal(
    mixedSourcesPlaylist.indexOf({
      sources: [{
        src: 'http://media.w3.org/2010/05/sintel/trailer.mp4',
        type: 'video/mp4'
      }, {
        app_name: 'rtmp://example.com/sintel/trailer',// eslint-disable-line
        avg_bitrate: 4255000,// eslint-disable-line
        codec: 'H264',
        container: 'MP4'
      }],
      poster: 'http://media.w3.org/2010/05/sintel/poster.png'
    }),
    0,
    'sintel trailer does exist'
  );
});

QUnit.test('playlist.nextIndex() works as expected', function(assert) {
  const playlist = playlistMaker(playerProxyMaker(), []);

  assert.equal(playlist.nextIndex(), -1, 'the next index was -1 for an empty list');

  playlist([1, 2, 3]);
  playlist.currentItem = () => 0;
  assert.equal(playlist.nextIndex(), 1, 'the next index was 1');

  playlist.currentItem = () => 1;
  assert.equal(playlist.nextIndex(), 2, 'the next index was 2');

  playlist.currentItem = () => 2;
  assert.equal(playlist.nextIndex(), 2, 'the next index did not change because the playlist does not repeat');

  playlist.repeat(true);
  assert.equal(playlist.nextIndex(), 0, 'the next index was now 0 because the playlist repeats');
});

QUnit.test('playlist.previousIndex() works as expected', function(assert) {
  const playlist = playlistMaker(playerProxyMaker(), []);

  assert.equal(playlist.previousIndex(), -1, 'the previous index was -1 for an empty list');

  playlist([1, 2, 3]);
  playlist.currentItem = () => 2;
  assert.equal(playlist.previousIndex(), 1, 'the previous index was 1');

  playlist.currentItem = () => 1;
  assert.equal(playlist.previousIndex(), 0, 'the previous index was 0');

  playlist.currentItem = () => 0;
  assert.equal(playlist.previousIndex(), 0, 'the previous index did not change because the playlist does not repeat');

  playlist.repeat(true);
  assert.equal(playlist.previousIndex(), 2, 'the previous index was now 2 because the playlist repeats');
});

QUnit.test('playlist.lastIndex() works as expected', function(assert) {
  const playlist = playlistMaker(playerProxyMaker(), []);

  assert.equal(playlist.lastIndex(), -1, 'the last index was -1 for an empty list');

  playlist([1, 2, 3]);
  assert.equal(playlist.lastIndex(), 2, 'the last index was 2');
});

QUnit.test('playlist.next() works as expected', function(assert) {
  const player = playerProxyMaker();
  const playlist = playlistMaker(player, videoList);
  let src;

  player.currentSrc = function() {
    return src;
  };

  src = videoList[0].sources[0].src;
  assert.equal(playlist.currentItem(), 0, 'we start on item 0');

  assert.deepEqual(
    playlist.next(),
    videoList[1],
    'we get back the value of currentItem 2'
  );

  src = videoList[1].sources[0].src;
  assert.equal(playlist.currentItem(), 1, 'we are now on item 1');

  assert.deepEqual(
    playlist.next(),
    videoList[2],
    'we get back the value of currentItem 3'
  );

  src = videoList[2].sources[0].src;
  assert.equal(playlist.currentItem(), 2, 'we are now on item 2');
  src = videoList[4].sources[0].src;
  assert.equal(playlist.currentItem(4), 4, 'we are now on item 4');

  assert.equal(
    typeof playlist.next(),
    'undefined',
    'we get nothing back if we try to go out of bounds'
  );
});

QUnit.test('playlist.previous() works as expected', function(assert) {
  const player = playerProxyMaker();
  const playlist = playlistMaker(player, videoList);
  let src;

  player.currentSrc = function() {
    return src;
  };

  src = videoList[0].sources[0].src;
  assert.equal(playlist.currentItem(), 0, 'we start on item 0');

  assert.equal(
    typeof playlist.previous(),
    'undefined',
    'we get nothing back if we try to go out of bounds'
  );

  src = videoList[2].sources[0].src;
  assert.equal(playlist.currentItem(), 2, 'we are on item 2');

  assert.deepEqual(
    playlist.previous(),
    videoList[1],
    'we get back value of currentItem 1'
  );

  src = videoList[1].sources[0].src;
  assert.equal(playlist.currentItem(), 1, 'we are on item 1');

  assert.deepEqual(
    playlist.previous(),
    videoList[0],
    'we get back value of currentItem 0'
  );

  src = videoList[0].sources[0].src;
  assert.equal(playlist.currentItem(), 0, 'we are on item 0');

  assert.equal(
    typeof playlist.previous(),
    'undefined',
    'we get nothing back if we try to go out of bounds'
  );
});

QUnit.test('loading a non-playlist video will cancel autoadvance and set index of -1', function(assert) {
  const oldReset = autoadvance.reset;
  const player = playerProxyMaker();

  const playlist = playlistMaker(player, [{
    sources: [{
      src: 'http://media.w3.org/2010/05/sintel/trailer.mp4',
      type: 'video/mp4'
    }],
    poster: 'http://media.w3.org/2010/05/sintel/poster.png'
  }, {
    sources: [{
      src: 'http://media.w3.org/2010/05/bunny/trailer.mp4',
      type: 'video/mp4'
    }],
    poster: 'http://media.w3.org/2010/05/bunny/poster.png'
  }]);

  player.currentSrc = function() {
    return 'http://vjs.zencdn.net/v/oceans.mp4';
  };

  autoadvance.setReset_(function() {
    assert.ok(true, 'autoadvance.reset was called');
  });

  player.trigger('loadstart');

  assert.equal(playlist.currentItem(), -1, 'new currentItem is -1');

  player.currentSrc = function() {
    return 'http://media.w3.org/2010/05/sintel/trailer.mp4';
  };

  autoadvance.setReset_(function() {
    assert.ok(false, 'autoadvance.reset should not be called');
  });

  player.trigger('loadstart');

  autoadvance.setReset_(oldReset);
});

QUnit.test('when loading a new playlist, trigger "duringplaylistchange" on the player', function(assert) {
  const done = assert.async();
  const player = playerProxyMaker();
  const playlist = playlistMaker(player, [1, 2, 3], 1);

  player.on('duringplaylistchange', (e) => {
    assert.strictEqual(e.type, 'duringplaylistchange', 'the event object had the correct "type" property');
    assert.strictEqual(e.previousIndex, 1, 'the event object had the correct "previousIndex" property');
    assert.deepEqual(e.previousPlaylist, [1, 2, 3], 'the event object had the correct "previousPlaylist" property');
    assert.strictEqual(e.nextIndex, 0, 'the event object had the correct "nextIndex" property');
    assert.deepEqual(e.nextPlaylist, [4, 5, 6], 'the event object had the correct "nextPlaylist" property');

    assert.throws(() => {
      playlist([1, 2, 3]);
    }, Error, 'cannot set a new playlist during a change');

    const spy = sinon.spy();

    player.on('playlistsorted', spy);
    playlist.sort();
    playlist.reverse();
    playlist.shuffle();
    assert.strictEqual(spy.callCount, 0, 'the "playlistsorted" event never fired');

    playlist.currentItem(2);
    assert.strictEqual(playlist.currentItem(), 1, 'the playlist current item could not be changed');

    playlist.next();
    assert.strictEqual(playlist.currentItem(), 1, 'the playlist current item could not be changed');

    playlist.previous();
    assert.strictEqual(playlist.currentItem(), 1, 'the playlist current item could not be changed');

    playlist.first();
    assert.strictEqual(playlist.currentItem(), 1, 'the playlist current item could not be changed');

    playlist.last();
    assert.strictEqual(playlist.currentItem(), 1, 'the playlist current item could not be changed');

    done();
  });

  playlist([4, 5, 6]);
});

QUnit.test('when loading a new playlist, trigger "playlistchange" on the player', function(assert) {
  const spy = sinon.spy();
  const player = playerProxyMaker();

  player.on('playlistchange', spy);
  const playlist = playlistMaker(player, [1, 2, 3]);

  playlist([4, 5, 6]);
  this.clock.tick(1);

  assert.strictEqual(spy.callCount, 1);
  assert.strictEqual(spy.firstCall.args[0].type, 'playlistchange');
  assert.strictEqual(spy.firstCall.args[0].action, 'change');
});

QUnit.test('"duringplaylistchange" and "playlistchange" on first call without an initial list', function(assert) {
  const changeSpy = sinon.spy();
  const duringSpy = sinon.spy();
  const player = playerProxyMaker();

  player.on('playlistchange', changeSpy);
  player.on('duringplaylistchange', duringSpy);

  const playlist = playlistMaker(player);

  this.clock.tick(1);

  assert.strictEqual(changeSpy.callCount, 0, 'on initial call, the "playlistchange" event did not fire');
  assert.strictEqual(duringSpy.callCount, 0, 'on initial call, the "duringplaylistchange" event did not fire');

  playlist([1]);
  this.clock.tick(1);

  assert.strictEqual(changeSpy.callCount, 1, 'on second call, the "playlistchange" event did fire');
  assert.strictEqual(duringSpy.callCount, 1, 'on second call, the "duringplaylistchange" event did fire');

  playlist([2]);
  this.clock.tick(1);

  assert.strictEqual(changeSpy.callCount, 2, 'on third call, the "playlistchange" event did fire');
  assert.strictEqual(duringSpy.callCount, 2, 'on third call, the "duringplaylistchange" event did fire');
});

QUnit.test('"duringplaylistchange" and "playlistchange" on first call with an initial list', function(assert) {
  const changeSpy = sinon.spy();
  const duringSpy = sinon.spy();
  const player = playerProxyMaker();

  player.on('playlistchange', changeSpy);
  player.on('duringplaylistchange', duringSpy);

  const playlist = playlistMaker(player, [1]);

  this.clock.tick(1);

  assert.strictEqual(changeSpy.callCount, 0, 'on initial call, the "playlistchange" event did not fire');
  assert.strictEqual(duringSpy.callCount, 1, 'on initial call, the "duringplaylistchange" event did fire');

  playlist([2]);
  this.clock.tick(1);

  assert.strictEqual(changeSpy.callCount, 1, 'on second call, the "playlistchange" event did fire');
  assert.strictEqual(duringSpy.callCount, 2, 'on second call, the "duringplaylistchange" event did fire');

  playlist([3]);
  this.clock.tick(1);

  assert.strictEqual(changeSpy.callCount, 2, 'on third call, the "playlistchange" event did fire');
  assert.strictEqual(duringSpy.callCount, 3, 'on third call, the "duringplaylistchange" event did fire');
});

QUnit.test('playlist.sort() works as expected', function(assert) {
  const player = playerProxyMaker();
  const spy = sinon.spy();

  player.on('playlistsorted', spy);
  const playlist = playlistMaker(player, []);

  playlist.sort();
  assert.deepEqual(playlist(), [], 'playlist did not change because it is empty');
  assert.strictEqual(spy.callCount, 0, 'the "playlistsorted" event did not trigger');

  playlist([4, 2, 1, 3]);

  playlist.sort();
  assert.deepEqual(playlist(), [1, 2, 3, 4], 'playlist is sorted per default sort behavior');
  assert.strictEqual(spy.callCount, 1, 'the "playlistsorted" event triggered');

  playlist.sort((a, b) => b - a);
  assert.deepEqual(playlist(), [4, 3, 2, 1], 'playlist is sorted per default sort behavior');
  assert.strictEqual(spy.callCount, 2, 'the "playlistsorted" event triggered');
});

QUnit.test('playlist.reverse() works as expected', function(assert) {
  const player = playerProxyMaker();
  const spy = sinon.spy();

  player.on('playlistsorted', spy);
  const playlist = playlistMaker(player, []);

  playlist.reverse();
  assert.deepEqual(playlist(), [], 'playlist did not change because it is empty');
  assert.strictEqual(spy.callCount, 0, 'the "playlistsorted" event did not trigger');

  playlist([1, 2, 3, 4]);

  playlist.reverse();
  assert.deepEqual(playlist(), [4, 3, 2, 1], 'playlist is reversed');
  assert.strictEqual(spy.callCount, 1, 'the "playlistsorted" event triggered');
});

QUnit.test('playlist.shuffle() works as expected', function(assert) {
  const player = playerProxyMaker();
  const spy = sinon.spy();

  player.on('playlistsorted', spy);
  const playlist = playlistMaker(player, []);

  playlist.shuffle();
  assert.deepEqual(playlist(), [], 'playlist did not change because it is empty');
  assert.strictEqual(spy.callCount, 0, 'the "playlistsorted" event did not trigger');

  playlist([1, 2, 3, 4]);

  playlist.shuffle();

  const list = playlist();

  assert.strictEqual(list.length, 4, 'playlist is the correct length');
  assert.notStrictEqual(list.indexOf(1), -1, '1 is in the list');
  assert.notStrictEqual(list.indexOf(2), -1, '2 is in the list');
  assert.notStrictEqual(list.indexOf(3), -1, '3 is in the list');
  assert.notStrictEqual(list.indexOf(4), -1, '4 is in the list');
  assert.strictEqual(spy.callCount, 1, 'the "playlistsorted" event triggered');
});

QUnit.test('playlist.shuffle({rest: true}) works as expected', function(assert) {
  const player = playerProxyMaker();
  const spy = sinon.spy();

  player.on('playlistsorted', spy);
  const playlist = playlistMaker(player, [1, 2, 3, 4]);

  playlist.currentIndex_ = 3;
  playlist.shuffle({rest: true});
  let list = playlist();

  assert.deepEqual(list, [1, 2, 3, 4], 'playlist is unchanged because the last item is selected');
  assert.strictEqual(spy.callCount, 0, 'the "playlistsorted" event was not triggered');

  playlist.currentIndex_ = 2;
  playlist.shuffle({rest: true});
  list = playlist();

  assert.deepEqual(list, [1, 2, 3, 4], 'playlist is unchanged because the second-to-last item is selected');
  assert.strictEqual(spy.callCount, 0, 'the "playlistsorted" event was not triggered');

  playlist.currentIndex_ = 1;
  playlist.shuffle({rest: true});
  list = playlist();

  assert.strictEqual(list.length, 4, 'playlist is the correct length');
  assert.strictEqual(list.indexOf(1), 0, '1 is the first item in the list');
  assert.strictEqual(list.indexOf(2), 1, '2 is the second item in the list');
  assert.notStrictEqual(list.indexOf(3), -1, '3 is in the list');
  assert.notStrictEqual(list.indexOf(4), -1, '4 is in the list');
  assert.strictEqual(spy.callCount, 1, 'the "playlistsorted" event triggered');

  playlist.currentIndex_ = 0;
  playlist.shuffle({rest: true});
  list = playlist();

  assert.strictEqual(list.length, 4, 'playlist is the correct length');
  assert.strictEqual(list.indexOf(1), 0, '1 is the first item in the list');
  assert.notStrictEqual(list.indexOf(2), -1, '2 is in the list');
  assert.notStrictEqual(list.indexOf(3), -1, '3 is in the list');
  assert.notStrictEqual(list.indexOf(4), -1, '4 is in the list');
  assert.strictEqual(spy.callCount, 2, 'the "playlistsorted" event triggered');

  playlist.currentIndex_ = -1;
  playlist.shuffle({rest: true});
  list = playlist();

  assert.strictEqual(list.length, 4, 'playlist is the correct length');
  assert.notStrictEqual(list.indexOf(1), -1, '1 is in the list');
  assert.notStrictEqual(list.indexOf(2), -1, '2 is in the list');
  assert.notStrictEqual(list.indexOf(3), -1, '3 is in the list');
  assert.notStrictEqual(list.indexOf(4), -1, '4 is in the list');
  assert.strictEqual(spy.callCount, 3, 'the "playlistsorted" event triggered');
});

QUnit.test('playlist.add will append an item by default', function(assert) {
  const player = playerProxyMaker();
  const playlist = playlistMaker(player, [1, 2, 3]);
  const spy = sinon.spy();

  this.clock.tick(1);
  player.on(['playlistchange', 'playlistadd'], spy);
  playlist.add(4);
  assert.deepEqual(playlist(), [1, 2, 3, 4]);
  assert.strictEqual(spy.callCount, 2);
  assert.strictEqual(spy.firstCall.args[0].type, 'playlistchange');
  assert.strictEqual(spy.firstCall.args[0].action, 'add');
  assert.strictEqual(spy.secondCall.args[0].type, 'playlistadd');
  assert.strictEqual(spy.secondCall.args[0].index, 3);
  assert.strictEqual(spy.secondCall.args[0].count, 1);
});

QUnit.test('playlist.add can insert an item at a specific index', function(assert) {
  const player = playerProxyMaker();
  const playlist = playlistMaker(player, [1, 2, 3]);
  const spy = sinon.spy();

  this.clock.tick(1);
  player.on(['playlistchange', 'playlistadd'], spy);
  playlist.add(4, 1);
  assert.deepEqual(playlist(), [1, 4, 2, 3]);
  assert.strictEqual(spy.callCount, 2);
  assert.strictEqual(spy.firstCall.args[0].type, 'playlistchange');
  assert.strictEqual(spy.firstCall.args[0].action, 'add');
  assert.strictEqual(spy.secondCall.args[0].type, 'playlistadd');
  assert.strictEqual(spy.secondCall.args[0].index, 1);
  assert.strictEqual(spy.secondCall.args[0].count, 1);
});

QUnit.test('playlist.add appends when specified index is out of bounds', function(assert) {
  const player = playerProxyMaker();
  const playlist = playlistMaker(player, [1, 2, 3]);
  const spy = sinon.spy();

  this.clock.tick(1);
  player.on(['playlistchange', 'playlistadd'], spy);
  playlist.add(4, 10);
  assert.deepEqual(playlist(), [1, 2, 3, 4]);
  assert.strictEqual(spy.callCount, 2);
  assert.strictEqual(spy.firstCall.args[0].type, 'playlistchange');
  assert.strictEqual(spy.firstCall.args[0].action, 'add');
  assert.strictEqual(spy.secondCall.args[0].type, 'playlistadd');
  assert.strictEqual(spy.secondCall.args[0].index, 3);
  assert.strictEqual(spy.secondCall.args[0].count, 1);
});

QUnit.test('playlist.add can append multiple items', function(assert) {
  const player = playerProxyMaker();
  const playlist = playlistMaker(player, [1, 2, 3]);
  const spy = sinon.spy();

  this.clock.tick(1);
  player.on(['playlistchange', 'playlistadd'], spy);
  playlist.add([4, 5, 6]);
  assert.deepEqual(playlist(), [1, 2, 3, 4, 5, 6]);
  assert.strictEqual(spy.callCount, 2);
  assert.strictEqual(spy.firstCall.args[0].type, 'playlistchange');
  assert.strictEqual(spy.firstCall.args[0].action, 'add');
  assert.strictEqual(spy.secondCall.args[0].type, 'playlistadd');
  assert.strictEqual(spy.secondCall.args[0].index, 3);
  assert.strictEqual(spy.secondCall.args[0].count, 3);
});

QUnit.test('playlist.add can insert multiple items at a specific index', function(assert) {
  const player = playerProxyMaker();
  const playlist = playlistMaker(player, [1, 2, 3]);
  const spy = sinon.spy();

  this.clock.tick(1);
  player.on(['playlistchange', 'playlistadd'], spy);
  playlist.add([4, 5, 6, 7], 1);
  assert.deepEqual(playlist(), [1, 4, 5, 6, 7, 2, 3]);
  assert.strictEqual(spy.callCount, 2);
  assert.strictEqual(spy.firstCall.args[0].type, 'playlistchange');
  assert.strictEqual(spy.firstCall.args[0].action, 'add');
  assert.strictEqual(spy.secondCall.args[0].type, 'playlistadd');
  assert.strictEqual(spy.secondCall.args[0].index, 1);
  assert.strictEqual(spy.secondCall.args[0].count, 4);
});

QUnit.test('playlist.add throws an error duringplaylistchange', function(assert) {
  const done = assert.async();
  const player = playerProxyMaker();
  const playlist = playlistMaker(player, [1, 2, 3]);

  player.on('duringplaylistchange', (e) => {
    assert.throws(() => playlist.add(4));
    done();
  });

  playlist([4, 5, 6]);
});

QUnit.test('playlist.remove can remove an item at an index', function(assert) {
  const player = playerProxyMaker();
  const playlist = playlistMaker(player, [1, 2, 3]);
  const spy = sinon.spy();

  this.clock.tick(1);
  player.on(['playlistchange', 'playlistremove'], spy);
  playlist.remove(1);
  assert.deepEqual(playlist(), [1, 3]);
  assert.strictEqual(spy.callCount, 2);
  assert.strictEqual(spy.firstCall.args[0].type, 'playlistchange');
  assert.strictEqual(spy.firstCall.args[0].action, 'remove');
  assert.strictEqual(spy.secondCall.args[0].type, 'playlistremove');
  assert.strictEqual(spy.secondCall.args[0].index, 1);
  assert.strictEqual(spy.secondCall.args[0].count, 1);
});

QUnit.test('playlist.remove does nothing when index is out of range', function(assert) {
  const player = playerProxyMaker();
  const playlist = playlistMaker(player, [1, 2, 3]);
  const spy = sinon.spy();

  this.clock.tick(1);
  player.on(['playlistchange', 'playlistremove'], spy);
  playlist.remove(4);
  assert.deepEqual(playlist(), [1, 2, 3]);
  assert.strictEqual(spy.callCount, 0);
});

QUnit.test('playlist.remove can remove multiple items at an index', function(assert) {
  const player = playerProxyMaker();
  const playlist = playlistMaker(player, [1, 2, 3]);
  const spy = sinon.spy();

  this.clock.tick(1);
  player.on(['playlistchange', 'playlistremove'], spy);
  playlist.remove(1, 2);
  assert.deepEqual(playlist(), [1]);
  assert.strictEqual(spy.callCount, 2);
  assert.strictEqual(spy.firstCall.args[0].type, 'playlistchange');
  assert.strictEqual(spy.firstCall.args[0].action, 'remove');
  assert.strictEqual(spy.secondCall.args[0].type, 'playlistremove');
  assert.strictEqual(spy.secondCall.args[0].index, 1);
  assert.strictEqual(spy.secondCall.args[0].count, 2);
});

QUnit.test('playlist.remove throws an error duringplaylistchange', function(assert) {
  const done = assert.async();
  const player = playerProxyMaker();
  const playlist = playlistMaker(player, [1, 2, 3]);

  player.on('duringplaylistchange', (e) => {
    assert.throws(() => playlist.remove(0));
    done();
  });

  playlist([4, 5, 6]);
});
