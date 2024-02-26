import QUnit from 'qunit';
import sinon from 'sinon';
import '../src/plugin';

import {createFixturePlayer, destroyFixturePlayer} from './util';

const samplePlaylist = [{
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
}];

QUnit.module('current-item', {
  beforeEach() {
    this.clock = sinon.useFakeTimers();
    createFixturePlayer(this);
  },
  afterEach() {
    destroyFixturePlayer(this);
    this.clock.restore();
  }
}, function() {

  QUnit.module('without a playlist', function() {

    QUnit.test('player without a source', function(assert) {
      assert.strictEqual(this.player.playlist.currentItem(), -1, 'currentItem() before tech ready');

      // Tick forward to ready the playback tech.
      this.clock.tick(1);

      assert.strictEqual(this.player.playlist.currentItem(), -1, 'currentItem() after tech ready');
    });

    QUnit.test('player with a source', function(assert) {
      assert.strictEqual(this.player.playlist.currentItem(), -1, 'currentItem() before tech ready');

      // Tick forward to ready the playback tech.
      this.clock.tick(1);

      this.player.src({
        src: 'http://vjs.zencdn.net/v/oceans.mp4',
        type: 'video/mp4'
      });

      assert.strictEqual(this.player.playlist.currentItem(), -1, 'currentItem() after tech ready');
    });
  });

  QUnit.module('with a playlist', function() {

    QUnit.test('set new source by calling currentItem()', function(assert) {
      this.player.playlist(samplePlaylist);

      assert.strictEqual(this.player.playlist.currentItem(), 0, 'currentItem() before tech ready');

      // Tick forward to ready the playback tech.
      this.clock.tick(1);

      assert.strictEqual(this.player.playlist.currentItem(), 0, 'currentItem() after tech ready');

      this.player.playlist.currentItem(1);

      assert.strictEqual(this.player.playlist.currentItem(), 1, 'currentItem() changes the current item');
    });

    QUnit.test('set a new source via src()', function(assert) {
      this.player.playlist(samplePlaylist);

      assert.strictEqual(this.player.playlist.currentItem(), 0, 'currentItem() before tech ready');

      // Tick forward to ready the playback tech.
      this.clock.tick(1);

      assert.strictEqual(this.player.playlist.currentItem(), 0, 'currentItem() after tech ready');

      this.player.src({
        src: 'http://vjs.zencdn.net/v/oceans.mp4',
        type: 'video/mp4'
      });

      assert.strictEqual(this.player.playlist.currentItem(), 2, 'src() changes the current item');
    });

    QUnit.test('set a new source via src() - source is NOT in the playlist', function(assert) {

      // Populate the player with a playlist without oceans.mp4
      this.player.playlist(samplePlaylist.slice(0, 2));

      assert.strictEqual(this.player.playlist.currentItem(), 0, 'currentItem() before tech ready');

      // Tick forward to ready the playback tech.
      this.clock.tick(1);

      assert.strictEqual(this.player.playlist.currentItem(), 0, 'currentItem() after tech ready');

      this.player.src({
        src: 'http://vjs.zencdn.net/v/oceans.mp4',
        type: 'video/mp4'
      });

      assert.strictEqual(this.player.playlist.currentItem(), -1, 'src() changes the current item');
    });
  });

  QUnit.module('duplicate sources playlist', function() {

    QUnit.test('set new sources by calling currentItem()', function(assert) {

      // Populate the player with a playlist with another sintel on the end.
      this.player.playlist(samplePlaylist.concat([{
        sources: [{
          src: 'http://media.w3.org/2010/05/sintel/trailer.mp4',
          type: 'video/mp4'
        }],
        poster: 'http://media.w3.org/2010/05/sintel/poster.png'
      }]));

      assert.strictEqual(this.player.playlist.currentItem(), 0, 'currentItem() before tech ready');

      // Tick forward to ready the playback tech.
      this.clock.tick(1);

      assert.strictEqual(this.player.playlist.currentItem(), 0, 'currentItem() after tech ready');

      // Set the playlist to the last item.
      this.player.playlist.currentItem(3);

      assert.strictEqual(this.player.playlist.currentItem(), 3, 'currentItem() matches the duplicated item that was actually selected');

      // Set the playlist back to the first item (also sintel).
      this.player.playlist.currentItem(0);

      assert.strictEqual(this.player.playlist.currentItem(), 0, 'currentItem() matches the duplicated item that was actually selected');

      // Set the playlist to the second item (NOT sintel).
      this.player.playlist.currentItem(1);

      assert.strictEqual(this.player.playlist.currentItem(), 1, 'currentItem() is correct');
    });

    QUnit.test('set new source by calling src()', function(assert) {

      // Populate the player with a playlist with another sintel on the end.
      this.player.playlist(samplePlaylist.concat([{
        sources: [{
          src: 'http://media.w3.org/2010/05/sintel/trailer.mp4',
          type: 'video/mp4'
        }],
        poster: 'http://media.w3.org/2010/05/sintel/poster.png'
      }]));

      assert.strictEqual(this.player.playlist.currentItem(), 0, 'currentItem() before tech ready');

      // Tick forward to ready the playback tech.
      this.clock.tick(1);

      assert.strictEqual(this.player.playlist.currentItem(), 0, 'currentItem() after tech ready');

      // Set the playlist to the second item (NOT sintel).
      this.player.playlist.currentItem(1);

      assert.strictEqual(this.player.playlist.currentItem(), 1, 'currentItem() acted as a setter');

      this.player.src({
        src: 'http://media.w3.org/2010/05/sintel/trailer.mp4',
        type: 'video/mp4'
      });

      assert.strictEqual(this.player.playlist.currentItem(), 0, 'currentItem() defaults to the first playlist item that matches the current source');
    });
  });
});
