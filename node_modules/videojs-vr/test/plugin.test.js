import document from 'global/document';

import QUnit from 'qunit';
import videojs from 'video.js';

import plugin from '../src/plugin';

const Player = videojs.getComponent('Player');

QUnit.test('the environment is sane', function(assert) {
  assert.strictEqual(typeof Array.isArray, 'function', 'es5 exists');
  assert.strictEqual(typeof sinon, 'object', 'sinon exists');
  assert.strictEqual(typeof videojs, 'function', 'videojs exists');
  assert.strictEqual(typeof plugin, 'function', 'plugin is a function');
});

QUnit.module('videojs-vr', {

  beforeEach(assert) {
    assert.timeout(80000);

    this.fixture = document.getElementById('qunit-fixture');
    this.video = document.createElement('video-js');

    this.video.muted = true;
    this.video.defaultPlaybackRate = 16;
    // this.fixture.style.position = 'inherit';

    this.video.setAttribute('controls', '');
    this.video.setAttribute('muted', '');
    this.video.width = 600;
    this.video.height = 300;
    this.video.defaultPlaybackRate = 16;

    this.fixture.appendChild(this.video);
    this.player = videojs(this.video);
  },

  afterEach() {
    this.player.dispose();
  }
});

QUnit.test('registers itself with video.js', function(assert) {
  assert.expect(1);

  assert.strictEqual(
    typeof Player.prototype.vr,
    'function',
    'videojs-vr plugin was registered'
  );

  this.player.vr();

});

QUnit.test('playback', function(assert) {
  const done = assert.async();

  this.player.src({src: '/samples/eagle-360.mp4', type: 'video/mp4'});
  this.player.mediainfo = {projection: '360'};

  // AUTO is the default and looks at mediainfo
  this.vr = this.player.vr({projection: 'AUTO', debug: true});

  this.player.play();

  const onTimeupdate = () => {
    if (this.player.currentTime() > 0) {
      this.player.off('timeupdate', onTimeupdate);
      assert.ok(true, 'played back video');
      done();
    }
  };

  this.player.on('timeupdate', onTimeupdate);
});
