/* eslint-disable no-console */
import document from 'global/document';
import window from 'global/window';
import QUnit from 'qunit';
import sinon from 'sinon';
import videojs from 'video.js';

import 'videojs-playlist';
import '../src/plugin';

const playlist = [{
  name: 'Movie 1',
  description: 'Movie 1 description',
  duration: 100,
  data: {
    id: '1',
    foo: 'bar'
  },
  sources: [{
    src: '//example.com/movie1.mp4',
    type: 'video/mp4'
  }]
}, {
  sources: [{
    src: '//example.com/movie2.mp4',
    type: 'video/mp4'
  }],
  thumbnail: '//example.com/movie2.jpg'
}];

const resolveUrl = url => {
  const a = document.createElement('a');

  a.href = url;
  return a.href;
};

const Html5 = videojs.getTech('Html5');

QUnit.test('the environment is sane', function(assert) {
  assert.ok(true, 'everything is swell');
});

function setup() {
  this.oldVideojsBrowser = videojs.browser;
  videojs.browser = videojs.obj.merge({}, videojs.browser);

  this.fixture = document.querySelector('#qunit-fixture');

  // force HTML support so the tests run in a reasonable
  // environment under phantomjs
  this.realIsHtmlSupported = Html5.isSupported;
  Html5.isSupported = function() {
    return true;
  };

  // create a video element
  const video = document.createElement('video');

  this.fixture.appendChild(video);

  // create a video.js player
  this.player = videojs(video);

  // Create two playlist container elements.
  this.fixture.appendChild(videojs.dom.createEl('div', {className: 'vjs-playlist'}));
  this.fixture.appendChild(videojs.dom.createEl('div', {className: 'vjs-playlist'}));
}

function teardown() {
  videojs.browser = this.oldVideojsBrowser;
  Html5.isSupported = this.realIsHtmlSupported;
  this.player.dispose();
  this.player = null;
  videojs.dom.emptyEl(this.fixture);
}

QUnit.module('videojs-playlist-ui', {beforeEach: setup, afterEach: teardown});

QUnit.test('registers itself', function(assert) {
  assert.ok(this.player.playlistUi, 'registered the plugin');
});

QUnit.test('errors if used without the playlist plugin', function(assert) {
  sinon.spy(this.player.log, 'error');

  this.player.playlist = null;
  this.player.playlistUi();

  assert.ok(this.player.log.error.calledOnce, 'player.log.error was called');
});

QUnit.test('is empty if the playlist plugin isn\'t initialized', function(assert) {
  this.player.playlistUi();

  const items = this.fixture.querySelectorAll('.vjs-playlist-item');

  assert.ok(this.fixture.querySelector('.vjs-playlist'), 'created the menu');
  assert.strictEqual(items.length, 0, 'displayed no items');
});

QUnit.test('can be initialized with an element', function(assert) {
  const elem = videojs.dom.createEl('div');

  this.player.playlist(playlist);
  this.player.playlistUi({el: elem});

  assert.strictEqual(
    elem.querySelectorAll('li.vjs-playlist-item').length,
    playlist.length,
    'created an element for each playlist item'
  );
});

QUnit.test('can look for an element with the class "vjs-playlist" that is not already in use', function(assert) {
  const firstEl = this.fixture.querySelectorAll('.vjs-playlist')[0];
  const secondEl = this.fixture.querySelectorAll('.vjs-playlist')[1];

  // Give the firstEl a child, so the plugin thinks it is in use and moves on
  // to the next one.
  firstEl.appendChild(videojs.dom.createEl('div'));

  this.player.playlist(playlist);
  this.player.playlistUi();

  assert.strictEqual(this.player.playlistMenu.el(), secondEl, 'used the first matching/empty element');
  assert.strictEqual(
    secondEl.querySelectorAll('li.vjs-playlist-item').length,
    playlist.length,
    'found an element for each playlist item'
  );
});

QUnit.test('can look for an element with a custom class that is not already in use', function(assert) {
  const firstEl = videojs.dom.createEl('div', {className: 'super-playlist'});
  const secondEl = videojs.dom.createEl('div', {className: 'super-playlist'});

  // Give the firstEl a child, so the plugin thinks it is in use and moves on
  // to the next one.
  firstEl.appendChild(videojs.dom.createEl('div'));

  this.fixture.appendChild(firstEl);
  this.fixture.appendChild(secondEl);

  this.player.playlist(playlist);
  this.player.playlistUi({
    className: 'super-playlist'
  });

  assert.strictEqual(this.player.playlistMenu.el(), secondEl, 'used the first matching/empty element');
  assert.strictEqual(
    this.fixture.querySelectorAll('li.vjs-playlist-item').length,
    playlist.length,
    'created an element for each playlist item'
  );
});

QUnit.test('specializes the class name if touch input is absent', function(assert) {
  videojs.browser.TOUCH_ENABLED = false;

  this.player.playlist(playlist);
  this.player.playlistUi();

  assert.ok(this.player.playlistMenu.hasClass('vjs-mouse'), 'marked the playlist menu');
});

QUnit.test('can be re-initialized without doubling the contents of the list', function(assert) {
  const el = this.fixture.querySelectorAll('.vjs-playlist')[0];

  this.player.playlist(playlist);
  this.player.playlistUi();
  this.player.playlistUi();
  this.player.playlistUi();

  assert.strictEqual(this.player.playlistMenu.el(), el, 'used the first matching/empty element');
  assert.strictEqual(
    el.querySelectorAll('li.vjs-playlist-item').length,
    playlist.length,
    'found an element for each playlist item'
  );
});

QUnit.module('videojs-playlist-ui: Components', {beforeEach: setup, afterEach: teardown});

// --------------------
// Creation and Updates
// --------------------

QUnit.test('includes the video name if provided', function(assert) {
  this.player.playlist(playlist);
  this.player.playlistUi();

  const items = this.fixture.querySelectorAll('.vjs-playlist-item');

  assert.strictEqual(
    items[0].querySelector('.vjs-playlist-name').textContent,
    playlist[0].name,
    'wrote the name'
  );
  assert.strictEqual(
    items[1].querySelector('.vjs-playlist-name').textContent,
    'Untitled Video',
    'wrote a placeholder for the name'
  );
});

QUnit.test('includes the video description if user specifies it', function(assert) {
  this.player.playlist(playlist);
  this.player.playlistUi({showDescription: true});

  const items = this.fixture.querySelectorAll('.vjs-playlist-item');

  assert.strictEqual(
    items[0].querySelector('.vjs-playlist-description').textContent,
    playlist[0].description,
    'description is displayed'
  );
});

QUnit.test('hides video description by default', function(assert) {
  this.player.playlist(playlist);
  this.player.playlistUi();

  const items = this.fixture.querySelectorAll('.vjs-playlist-item');

  assert.strictEqual(
    items[0].querySelector('.vjs-playlist-description'),
    null,
    'description is not displayed'
  );
});

QUnit.test('includes custom data attribute if provided', function(assert) {
  this.player.playlist(playlist);
  this.player.playlistUi();

  const items = this.fixture.querySelectorAll('.vjs-playlist-item');

  assert.strictEqual(
    items[0].dataset.id,
    playlist[0].data.id,
    'set a single data attribute'
  );
  assert.strictEqual(
    items[0].dataset.id,
    '1',
    'set a single data attribute (actual value)'
  );
  assert.strictEqual(
    items[0].dataset.foo,
    playlist[0].data.foo,
    'set an addtional data attribute'
  );
  assert.strictEqual(
    items[0].dataset.foo,
    'bar',
    'set an addtional data attribute'
  );
});

QUnit.test('outputs a <picture> for simple thumbnails', function(assert) {
  this.player.playlist(playlist);
  this.player.playlistUi();

  const pictures = this.fixture.querySelectorAll('.vjs-playlist-item picture');

  assert.strictEqual(pictures.length, 1, 'output one picture');
  const imgs = pictures[0].querySelectorAll('img');

  assert.strictEqual(imgs.length, 1, 'output one img');
  assert.strictEqual(imgs[0].src, window.location.protocol + playlist[1].thumbnail, 'set the src attribute');
});

QUnit.test('outputs a <picture> for responsive thumbnails', function(assert) {
  const playlistOverride = [{
    sources: [{
      src: '//example.com/movie.mp4',
      type: 'video/mp4'
    }],
    thumbnail: [{
      srcset: '/test/example/oceans.jpg',
      type: 'image/jpeg',
      media: '(min-width: 400px;)'
    }, {
      src: '/test/example/oceans-low.jpg'
    }]
  }];

  this.player.playlist(playlistOverride);
  this.player.playlistUi();

  const sources = this.fixture.querySelectorAll('.vjs-playlist-item picture source');
  const imgs = this.fixture.querySelectorAll('.vjs-playlist-item picture img');

  assert.strictEqual(sources.length, 1, 'output one source');
  assert.strictEqual(
    sources[0].srcset,
    playlistOverride[0].thumbnail[0].srcset,
    'wrote the srcset attribute'
  );
  assert.strictEqual(
    sources[0].type,
    playlistOverride[0].thumbnail[0].type,
    'wrote the type attribute'
  );
  assert.strictEqual(
    sources[0].media,
    playlistOverride[0].thumbnail[0].media,
    'wrote the type attribute'
  );
  assert.strictEqual(imgs.length, 1, 'output one img');
  assert.strictEqual(
    imgs[0].src,
    resolveUrl(playlistOverride[0].thumbnail[1].src),
    'output the img src attribute'
  );
});

QUnit.test('outputs a placeholder for items without thumbnails', function(assert) {
  this.player.playlist(playlist);
  this.player.playlistUi();

  const thumbnails = this.fixture.querySelectorAll('.vjs-playlist-item .vjs-playlist-thumbnail');

  assert.strictEqual(thumbnails.length, playlist.length, 'output two thumbnails');
  assert.strictEqual(thumbnails[0].nodeName.toLowerCase(), 'div', 'the second is a placeholder');
});

QUnit.test('includes the duration if one is provided', function(assert) {
  this.player.playlist(playlist);
  this.player.playlistUi();

  const durations = this.fixture.querySelectorAll('.vjs-playlist-item .vjs-playlist-duration');

  assert.strictEqual(durations.length, 1, 'skipped the item without a duration');
  assert.strictEqual(
    durations[0].textContent,
    '1:40',
    'wrote the duration'
  );
  assert.strictEqual(
    durations[0].getAttribute('datetime'),
    'PT0H0M' + playlist[0].duration + 'S',
    'wrote a machine-readable datetime'
  );
});

QUnit.test('marks the selected playlist item on startup', function(assert) {
  this.player.playlist(playlist);
  this.player.currentSrc = () => playlist[0].sources[0].src;
  this.player.playlistUi();

  const selectedItems = this.fixture.querySelectorAll('.vjs-playlist-item.vjs-selected');

  assert.strictEqual(selectedItems.length, 1, 'marked one playlist item');
  assert.strictEqual(
    selectedItems[0].querySelector('.vjs-playlist-name').textContent,
    playlist[0].name,
    'marked the first playlist item'
  );
});

QUnit.test('updates the selected playlist item on loadstart', function(assert) {
  this.player.playlist(playlist);
  this.player.playlistUi();

  this.player.playlist.currentItem(1);
  this.player.currentSrc = () => playlist[1].sources[0].src;
  this.player.trigger('loadstart');

  const selectedItems = this.fixture.querySelectorAll('.vjs-playlist-item.vjs-selected');

  assert.strictEqual(
    this.fixture.querySelectorAll('.vjs-playlist-item').length,
    playlist.length,
    'displayed the correct number of items'
  );
  assert.strictEqual(selectedItems.length, 1, 'marked one playlist item');
  assert.strictEqual(
    selectedItems[0].querySelector('img').src,
    resolveUrl(playlist[1].thumbnail),
    'marked the second playlist item'
  );
});

QUnit.test('selects no item if the playlist is not in use', function(assert) {
  this.player.playlist(playlist);
  this.player.playlist.currentItem = () => -1;
  this.player.playlistUi();

  this.player.trigger('loadstart');

  assert.strictEqual(
    this.fixture.querySelectorAll('.vjs-playlist-item.vjs-selected').length,
    0,
    'no items selected'
  );
});

QUnit.test('updates on "playlistchange", different lengths', function(assert) {
  this.player.playlist([]);
  this.player.playlistUi();

  let items = this.fixture.querySelectorAll('.vjs-playlist-item');

  assert.strictEqual(items.length, 0, 'no items initially');

  this.player.playlist(playlist);
  this.player.trigger('playlistchange');
  items = this.fixture.querySelectorAll('.vjs-playlist-item');
  assert.strictEqual(items.length, playlist.length, 'updated with the new items');
});

QUnit.test('updates on "playlistchange", equal lengths', function(assert) {
  this.player.playlist([{sources: []}, {sources: []}]);
  this.player.playlistUi();

  let items = this.fixture.querySelectorAll('.vjs-playlist-item');

  assert.strictEqual(items.length, 2, 'two items initially');

  this.player.playlist(playlist);
  this.player.trigger('playlistchange');
  items = this.fixture.querySelectorAll('.vjs-playlist-item');
  assert.strictEqual(items.length, playlist.length, 'updated with the new items');
  assert.strictEqual(this.player.playlistMenu.items[0].item, playlist[0], 'we have updated items');
  assert.strictEqual(this.player.playlistMenu.items[1].item, playlist[1], 'we have updated items');
});

QUnit.test('updates on "playlistchange", update selection', function(assert) {
  this.player.playlist(playlist);
  this.player.currentSrc = function() {
    return playlist[0].sources[0].src;
  };
  this.player.playlistUi();

  let items = this.fixture.querySelectorAll('.vjs-playlist-item');

  assert.strictEqual(items.length, 2, 'two items initially');

  assert.ok((/vjs-selected/).test(items[0].getAttribute('class')), 'first item is selected by default');
  this.player.playlist.currentItem(1);
  this.player.currentSrc = function() {
    return playlist[1].sources[0].src;
  };

  this.player.trigger('playlistchange');
  items = this.fixture.querySelectorAll('.vjs-playlist-item');
  assert.strictEqual(items.length, playlist.length, 'updated with the new items');
  assert.ok((/vjs-selected/).test(items[1].getAttribute('class')), 'second item is selected after update');
  assert.ok(!(/vjs-selected/).test(items[0].getAttribute('class')), 'first item is not selected after update');
});

QUnit.test('updates on "playlistsorted", different lengths', function(assert) {
  this.player.playlist([]);
  this.player.playlistUi();

  let items = this.fixture.querySelectorAll('.vjs-playlist-item');

  assert.strictEqual(items.length, 0, 'no items initially');

  this.player.playlist(playlist);
  this.player.trigger('playlistsorted');
  items = this.fixture.querySelectorAll('.vjs-playlist-item');
  assert.strictEqual(items.length, playlist.length, 'updated with the new items');
});

QUnit.test('updates on "playlistsorted", equal lengths', function(assert) {
  this.player.playlist([{sources: []}, {sources: []}]);
  this.player.playlistUi();

  let items = this.fixture.querySelectorAll('.vjs-playlist-item');

  assert.strictEqual(items.length, 2, 'two items initially');

  this.player.playlist(playlist);
  this.player.trigger('playlistsorted');
  items = this.fixture.querySelectorAll('.vjs-playlist-item');
  assert.strictEqual(items.length, playlist.length, 'updated with the new items');
  assert.strictEqual(this.player.playlistMenu.items[0].item, playlist[0], 'we have updated items');
  assert.strictEqual(this.player.playlistMenu.items[1].item, playlist[1], 'we have updated items');
});

QUnit.test('updates on "playlistsorted", update selection', function(assert) {
  this.player.playlist(playlist);
  this.player.currentSrc = function() {
    return playlist[0].sources[0].src;
  };
  this.player.playlistUi();

  let items = this.fixture.querySelectorAll('.vjs-playlist-item');

  assert.strictEqual(items.length, 2, 'two items initially');

  assert.ok((/vjs-selected/).test(items[0].getAttribute('class')), 'first item is selected by default');
  this.player.playlist.currentItem(1);
  this.player.currentSrc = function() {
    return playlist[1].sources[0].src;
  };

  this.player.trigger('playlistsorted');
  items = this.fixture.querySelectorAll('.vjs-playlist-item');
  assert.strictEqual(items.length, playlist.length, 'updated with the new items');
  assert.ok((/vjs-selected/).test(items[1].getAttribute('class')), 'second item is selected after update');
  assert.ok(!(/vjs-selected/).test(items[0].getAttribute('class')), 'first item is not selected after update');
});

QUnit.test('tracks when an ad is playing', function(assert) {
  this.player.playlist([]);
  this.player.playlistUi();

  this.player.duration = () => 5;

  const playlistMenu = this.player.playlistMenu;

  assert.ok(
    !playlistMenu.hasClass('vjs-ad-playing'),
    'does not have class vjs-ad-playing'
  );
  this.player.trigger('adstart');
  assert.ok(
    playlistMenu.hasClass('vjs-ad-playing'),
    'has class vjs-ad-playing'
  );

  this.player.trigger('adend');
  assert.ok(
    !playlistMenu.hasClass('vjs-ad-playing'),
    'does not have class vjs-ad-playing'
  );
});

// -----------
// Interaction
// -----------

QUnit.test('changes the selection when tapped', function(assert) {
  let playCalled = false;

  this.player.playlist(playlist);
  this.player.playlistUi({playOnSelect: true});
  this.player.play = function() {
    playCalled = true;
  };

  let sources;

  this.player.src = (src) => {
    if (src) {
      sources = src;
    }
    return sources[0];
  };
  this.player.currentSrc = () => sources[0].src;
  this.player.playlistMenu.items[1].trigger('tap');
  // trigger a loadstart synchronously to simplify the test
  this.player.trigger('loadstart');

  assert.ok(
    this.player.playlistMenu.items[1].hasClass('vjs-selected'),
    'selected the new item'
  );
  assert.ok(
    !this.player.playlistMenu.items[0].hasClass('vjs-selected'),
    'deselected the old item'
  );
  assert.strictEqual(playCalled, true, 'play gets called if option is set');
});

QUnit.test('play should not get called by default upon selection of menu items ', function(assert) {
  let playCalled = false;

  this.player.playlist(playlist);
  this.player.playlistUi();
  this.player.play = function() {
    playCalled = true;
  };

  let sources;

  this.player.src = (src) => {
    if (src) {
      sources = src;
    }
    return sources[0];
  };
  this.player.currentSrc = () => sources[0].src;
  this.player.playlistMenu.items[1].trigger('tap');
  // trigger a loadstart synchronously to simplify the test
  this.player.trigger('loadstart');
  assert.strictEqual(playCalled, false, 'play should not get called by default');
});

QUnit.test('disposing the playlist menu nulls out the player\'s reference to it', function(assert) {
  assert.strictEqual(this.fixture.querySelectorAll('.vjs-playlist').length, 2, 'there are two playlist containers at the start');

  this.player.playlist(playlist);
  this.player.playlistUi();
  this.player.playlistMenu.dispose();

  assert.strictEqual(this.fixture.querySelectorAll('.vjs-playlist').length, 1, 'only the unused playlist container is left');
  assert.strictEqual(this.player.playlistMenu, null, 'the playlistMenu property is null');
});

QUnit.test('disposing the playlist menu removes playlist menu items', function(assert) {
  assert.strictEqual(this.fixture.querySelectorAll('.vjs-playlist').length, 2, 'there are two playlist containers at the start');

  this.player.playlist(playlist);
  this.player.playlistUi();

  // Cache some references so we can refer to them after disposal.
  const items = [].concat(this.player.playlistMenu.items);

  this.player.playlistMenu.dispose();

  assert.strictEqual(this.fixture.querySelectorAll('.vjs-playlist').length, 1, 'only the unused playlist container is left');
  assert.strictEqual(this.player.playlistMenu, null, 'the playlistMenu property is null');

  items.forEach(i => {
    assert.strictEqual(i.el_, null, `the item "${i.id_}" has been disposed`);
  });
});

QUnit.test('disposing the player also disposes the playlist menu', function(assert) {
  assert.strictEqual(this.fixture.querySelectorAll('.vjs-playlist').length, 2, 'there are two playlist containers at the start');

  this.player.playlist(playlist);
  this.player.playlistUi();
  this.player.dispose();

  assert.strictEqual(this.fixture.querySelectorAll('.vjs-playlist').length, 1, 'only the unused playlist container is left');
  assert.strictEqual(this.player.playlistMenu, null, 'the playlistMenu property is null');
});

QUnit.module('videojs-playlist-ui: add/remove', {beforeEach: setup, afterEach: teardown});

QUnit.test('adding zero items at the start of the playlist', function(assert) {
  this.player.playlist(playlist);
  this.player.playlistUi();

  let items = this.fixture.querySelectorAll('.vjs-playlist-item');

  assert.strictEqual(items.length, 2, 'two items initially');

  this.player.playlist.add([], 0);
  items = this.fixture.querySelectorAll('.vjs-playlist-item');
  assert.strictEqual(items.length, playlist.length, 'correct number of items');
});

QUnit.test('adding one item at the start of the playlist', function(assert) {
  this.player.playlist(playlist);
  this.player.playlistUi();

  let items = this.fixture.querySelectorAll('.vjs-playlist-item');

  assert.strictEqual(items.length, 2, 'two items initially');

  this.player.playlist.add({name: 'Test 1'}, 0);
  items = this.fixture.querySelectorAll('.vjs-playlist-item');
  assert.strictEqual(items.length, 3, 'correct number of items');
  assert.strictEqual(items[0].querySelector('.vjs-playlist-name').textContent, 'Test 1', 'has the correct name in the playlist DOM');
});

QUnit.test('adding two items at the start of the playlist', function(assert) {
  this.player.playlist(playlist);
  this.player.playlistUi();

  let items = this.fixture.querySelectorAll('.vjs-playlist-item');

  assert.strictEqual(items.length, 2, 'two items initially');

  this.player.playlist.add([{name: 'Test 1'}, {name: 'Test 2'}], 0);
  items = this.fixture.querySelectorAll('.vjs-playlist-item');
  assert.strictEqual(items.length, 4, 'correct number of items');
  assert.strictEqual(items[0].querySelector('.vjs-playlist-name').textContent, 'Test 1', 'has the correct name in the playlist DOM');
  assert.strictEqual(items[1].querySelector('.vjs-playlist-name').textContent, 'Test 2', 'has the correct name in the playlist DOM');
});

QUnit.test('adding one item in the middle of the playlist', function(assert) {
  this.player.playlist(playlist);
  this.player.playlistUi();

  let items = this.fixture.querySelectorAll('.vjs-playlist-item');

  assert.strictEqual(items.length, 2, 'two items initially');

  this.player.playlist.add({name: 'Test 1'}, 1);
  items = this.fixture.querySelectorAll('.vjs-playlist-item');
  assert.strictEqual(items.length, 3, 'correct number of items');
  assert.strictEqual(items[1].querySelector('.vjs-playlist-name').textContent, 'Test 1', 'has the correct name in the playlist DOM');
});

QUnit.test('adding two items in the middle of the playlist', function(assert) {
  this.player.playlist(playlist);
  this.player.playlistUi();

  let items = this.fixture.querySelectorAll('.vjs-playlist-item');

  assert.strictEqual(items.length, 2, 'two items initially');

  this.player.playlist.add([{name: 'Test 1'}, {name: 'Test 2'}], 1);
  items = this.fixture.querySelectorAll('.vjs-playlist-item');
  assert.strictEqual(items.length, 4, 'correct number of items');
  assert.strictEqual(items[1].querySelector('.vjs-playlist-name').textContent, 'Test 1', 'has the correct name in the playlist DOM');
  assert.strictEqual(items[2].querySelector('.vjs-playlist-name').textContent, 'Test 2', 'has the correct name in the playlist DOM');
});

QUnit.test('adding one item at the end of the playlist', function(assert) {
  this.player.playlist(playlist);
  this.player.playlistUi();

  let items = this.fixture.querySelectorAll('.vjs-playlist-item');

  assert.strictEqual(items.length, 2, 'two items initially');

  this.player.playlist.add({name: 'Test 1'}, playlist.length);
  items = this.fixture.querySelectorAll('.vjs-playlist-item');
  assert.strictEqual(items.length, 3, 'correct number of items');
  assert.strictEqual(items[2].querySelector('.vjs-playlist-name').textContent, 'Test 1', 'has the correct name in the playlist DOM');
});

QUnit.test('adding two items at the end of the playlist', function(assert) {
  this.player.playlist(playlist);
  this.player.playlistUi();

  let items = this.fixture.querySelectorAll('.vjs-playlist-item');

  assert.strictEqual(items.length, 2, 'two items initially');

  this.player.playlist.add([{name: 'Test 1'}, {name: 'Test 2'}], playlist.length);
  items = this.fixture.querySelectorAll('.vjs-playlist-item');
  assert.strictEqual(items.length, 4, 'correct number of items');
  assert.strictEqual(items[2].querySelector('.vjs-playlist-name').textContent, 'Test 1', 'has the correct name in the playlist DOM');
  assert.strictEqual(items[3].querySelector('.vjs-playlist-name').textContent, 'Test 2', 'has the correct name in the playlist DOM');
});

QUnit.test('removing zero items at the start of the playlist', function(assert) {
  this.player.playlist(playlist);
  this.player.playlistUi();

  let items = this.fixture.querySelectorAll('.vjs-playlist-item');

  assert.strictEqual(items.length, 2, 'two items initially');

  this.player.playlist.remove(0, 0);
  items = this.fixture.querySelectorAll('.vjs-playlist-item');
  assert.strictEqual(items.length, playlist.length, 'correct number of items');
});

QUnit.test('removing one item at the start of the playlist', function(assert) {
  this.player.playlist(playlist);
  this.player.playlistUi();

  let items = this.fixture.querySelectorAll('.vjs-playlist-item');

  assert.strictEqual(items.length, 2, 'two items initially');

  this.player.playlist.add({name: 'Test 1'}, 0);
  items = this.fixture.querySelectorAll('.vjs-playlist-item');

  assert.strictEqual(items.length, 3, 'correct number of items');

  this.player.playlist.remove(0, 1);
  items = this.fixture.querySelectorAll('.vjs-playlist-item');

  assert.strictEqual(items.length, 2, 'correct number of items');
  assert.notStrictEqual(items[0].querySelector('.vjs-playlist-name').textContent, 'Test 1', 'the added item was properly removed from the DOM');
});

QUnit.test('removing two items at the start of the playlist', function(assert) {
  this.player.playlist(playlist);
  this.player.playlistUi();

  let items = this.fixture.querySelectorAll('.vjs-playlist-item');

  assert.strictEqual(items.length, 2, 'two items initially');

  this.player.playlist.add([{name: 'Test 1'}, {name: 'Test 2'}], 0);
  items = this.fixture.querySelectorAll('.vjs-playlist-item');

  assert.strictEqual(items.length, 4, 'correct number of items');

  this.player.playlist.remove(0, 2);
  items = this.fixture.querySelectorAll('.vjs-playlist-item');

  assert.notStrictEqual(items[0].querySelector('.vjs-playlist-name').textContent, 'Test 1', 'the added item was properly removed from the DOM');
  assert.notStrictEqual(items[1].querySelector('.vjs-playlist-name').textContent, 'Test 2', 'the added item was properly removed from the DOM');
});

QUnit.test('removing one item in the middle of the playlist', function(assert) {
  this.player.playlist(playlist);
  this.player.playlistUi();

  let items = this.fixture.querySelectorAll('.vjs-playlist-item');

  assert.strictEqual(items.length, 2, 'two items initially');

  this.player.playlist.add({name: 'Test 1'}, 1);
  items = this.fixture.querySelectorAll('.vjs-playlist-item');

  assert.strictEqual(items.length, 3, 'correct number of items');

  this.player.playlist.remove(1, 1);
  items = this.fixture.querySelectorAll('.vjs-playlist-item');

  assert.strictEqual(items.length, 2, 'correct number of items');
  assert.notStrictEqual(items[1].querySelector('.vjs-playlist-name').textContent, 'Test 1', 'the added item was properly removed from the DOM');
});

QUnit.test('removing two items in the middle of the playlist', function(assert) {
  this.player.playlist(playlist);
  this.player.playlistUi();

  let items = this.fixture.querySelectorAll('.vjs-playlist-item');

  assert.strictEqual(items.length, 2, 'two items initially');

  this.player.playlist.add([{name: 'Test 1'}, {name: 'Test 2'}], 1);
  items = this.fixture.querySelectorAll('.vjs-playlist-item');

  assert.strictEqual(items.length, 4, 'correct number of items');

  this.player.playlist.remove(1, 2);
  items = this.fixture.querySelectorAll('.vjs-playlist-item');

  assert.notStrictEqual(items[1].querySelector('.vjs-playlist-name').textContent, 'Test 1', 'the added item was properly removed from the DOM');
  assert.strictEqual(items[2], undefined, 'the added item was properly removed from the DOM');
});

QUnit.test('removing one item at the end of the playlist', function(assert) {
  this.player.playlist(playlist);
  this.player.playlistUi();

  let items = this.fixture.querySelectorAll('.vjs-playlist-item');

  assert.strictEqual(items.length, 2, 'two items initially');

  this.player.playlist.add({name: 'Test 1'}, 2);
  items = this.fixture.querySelectorAll('.vjs-playlist-item');

  assert.strictEqual(items.length, 3, 'correct number of items');

  this.player.playlist.remove(2, 1);
  items = this.fixture.querySelectorAll('.vjs-playlist-item');

  assert.strictEqual(items.length, 2, 'correct number of items');
  assert.notStrictEqual(items[1].querySelector('.vjs-playlist-name').textContent, 'Test 1', 'the added item was properly removed from the DOM');
});

QUnit.test('removing two items at the end of the playlist', function(assert) {
  this.player.playlist(playlist);
  this.player.playlistUi();

  let items = this.fixture.querySelectorAll('.vjs-playlist-item');

  assert.strictEqual(items.length, 2, 'two items initially');

  this.player.playlist.add([{name: 'Test 1'}, {name: 'Test 2'}], 2);
  items = this.fixture.querySelectorAll('.vjs-playlist-item');

  assert.strictEqual(items.length, 4, 'correct number of items');

  this.player.playlist.remove(2, 2);
  items = this.fixture.querySelectorAll('.vjs-playlist-item');

  assert.strictEqual(items.length, 2, 'correct number of items');
  assert.notStrictEqual(items[1].querySelector('.vjs-playlist-name').textContent, 'Test 1', 'the added item was properly removed from the DOM');
  assert.notStrictEqual(items[1].querySelector('.vjs-playlist-name').textContent, 'Test 2', 'the added item was properly removed from the DOM');
});
