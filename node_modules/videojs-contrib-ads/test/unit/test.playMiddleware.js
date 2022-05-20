import pm from '../../src/playMiddleware.js';
import QUnit from 'qunit';
import sinon from 'sinon';
import videojs from 'video.js';

QUnit.module('Play Middleware', {}, function() {
  const baseMockedVjsNotSupported = {
    use: () => {},
    VERSION: '5.0.0',
    browser: {
    }
  };

  const baseMockedVjsIsSupported = {
    use: () => {},
    VERSION: '6.7.3',
    browser: {
      IS_IOS: false,
      IS_ANDROID: false
    },
    middleware: {
      TERMINATOR: {fake: 'terminator'}
    }
  };

  QUnit.module('Not supported unit tests', {
    beforeEach() {
      this.videojs = videojs.mergeOptions({}, baseMockedVjsNotSupported);
    },
    afterEach() {
      this.videojs = null;
    }
  }, function() {
    QUnit.test('isMiddlewareMediatorSupported is false if old video.js version', function(assert) {
      // Mock videojs.browser to mock an older videojs version
      pm.testHook(this.videojs);

      assert.equal(
        pm.isMiddlewareMediatorSupported(), false,
        'old video.js does not support middleware mediators'
      );
    });

    QUnit.test('isMiddlewareMediatorSupported is false if on mobile', function(assert) {
      // Mock videojs.browser to fake being on Android
      this.videojs.browser.IS_ANDROID = true;
      this.videojs.browser.IS_IOS = false;
      pm.testHook(this.videojs);

      assert.equal(
        pm.isMiddlewareMediatorSupported(), false,
        'is not supported on Android'
      );

      // Mock videojs.browser to fake being on iOS
      this.videojs.browser.IS_ANDROID = false;
      this.videojs.browser.IS_IOS = true;
      pm.testHook(this.videojs);

      assert.equal(
        pm.isMiddlewareMediatorSupported(), false,
        'is not supported on iOS'
      );
    });
  });

  QUnit.module('Supported unit tests', {
    beforeEach() {
      // Stub videojs to force playMiddleware to be used
      this.videojs = videojs.mergeOptions({}, baseMockedVjsIsSupported);
      pm.testHook(this.videojs);

      this.triggeredEvent = null;
      this.addedClass = null;

      // Stub the player
      this.player = {
        ads: {
          _shouldBlockPlay: false,
          _playBlocked: false,
          debug: () => {}
        },
        trigger: (event) => {
          this.triggeredEvent = event;
        },
        addClass: (className) => {
          this.addedClass = className;
        }
      };

      this.sandbox = sinon.sandbox.create();
    },
    afterEach() {
      // Reset variables
      this.videojs = null;
      this.sandbox.restore();
    }
  });

  QUnit.test('isMiddlewareMediatorSupported is true if middleware mediators exist on desktop', function(assert) {
    assert.equal(
      pm.isMiddlewareMediatorSupported(), true,
      'is supported if middleware mediators exist and not mobile'
    );
  });

  QUnit.test('playMiddleware returns with a setSource, callPlay and play method', function(assert) {
    const m = pm.playMiddleware(this.player);

    this.sandbox.stub(pm, 'isMiddlewareMediatorSupported').returns(true);
    assert.equal(typeof m, 'object', 'returns an object');
    assert.equal(typeof m.setSource, 'function', 'has setSource');
    assert.equal(typeof m.callPlay, 'function', 'has callPlay');
    assert.equal(typeof m.play, 'function', 'has play');
  });

  QUnit.test('playMiddleware callPlay will terminate if _shouldBlockPlay is true', function(assert) {
    const m = pm.playMiddleware(this.player);

    this.sandbox.stub(pm, 'isMiddlewareMediatorSupported').returns(true);
    this.player.ads._shouldBlockPlay = true;
    assert.equal(
      m.callPlay(), this.videojs.middleware.TERMINATOR,
      'callPlay returns terminator'
    );
    assert.strictEqual(
      this.player.ads._playBlocked, true,
      '_playBlocked is set'
    );
  });

  QUnit.test('playMiddleware callPlay will not terminate if _shouldBlockPlay is false', function(assert) {
    const m = pm.playMiddleware(this.player);

    this.sandbox.stub(pm, 'isMiddlewareMediatorSupported').returns(true);
    this.player.ads._shouldBlockPlay = false;

    assert.equal(
      m.callPlay(), undefined,
      'callPlay should not return an object'
    );
    assert.notEqual(
      m.callPlay(), this.videojs.middleware.TERMINATOR,
      'callPlay should not return the terminator'
    );
    assert.strictEqual(
      this.player.ads._playBlocked, false,
      '_playBlocked should not be set'
    );
  });

  QUnit.test("playMiddleware callPlay will not terminate if the player doesn't have this plugin", function(assert) {
    const nonAdsPlayer = {
      trigger: (event) => {
        this.triggeredEvent = event;
      },
      addClass: (className) => {
        this.addedClass = className;
      }
    };
    const m = pm.playMiddleware(nonAdsPlayer);

    this.sandbox.stub(pm, 'isMiddlewareMediatorSupported').returns(true);
    this.player.ads._shouldBlockPlay = true;

    assert.equal(
      m.callPlay(), undefined,
      'callPlay should not return an object'
    );
    assert.strictEqual(
      this.player.ads._playBlocked, false,
      '_playBlocked should not be set'
    );
  });

  QUnit.test('playMiddleware play will trigger play event if callPlay terminates', function(assert) {
    const m = pm.playMiddleware(this.player);

    this.sandbox.stub(pm, 'isMiddlewareMediatorSupported').returns(true);
    this.player.ads._shouldBlockPlay = true;
    // Mock that the callPlay method terminated
    this.player.ads._playBlocked = true;

    // Play terminates, there's no value returned
    m.play(true, null);
    assert.equal(this.triggeredEvent, 'play');
    assert.equal(this.addedClass, 'vjs-has-started');
    assert.equal(
      this.player.ads._playBlocked, false,
      '_playBlocked is reset'
    );
  });

  QUnit.test('playMiddleware play will not trigger play event if another middleware terminated', function(assert) {
    const m = pm.playMiddleware(this.player);

    this.sandbox.stub(pm, 'isMiddlewareMediatorSupported').returns(true);
    // Mock that another middleware terminated but the playMiddleware did not
    this.player.ads._shouldBlockPlay = true;
    this.player.ads._playBlocked = false;
    this.sandbox.stub(m, 'callPlay').returns(undefined);

    // Another middleware terminated so the first argument is true
    m.play(true, null);
    assert.equal(this.triggeredEvent, null, 'no events should be triggered');
    assert.equal(this.addedClass, null, 'no classes should be added');
    assert.equal(this.player.ads._playBlocked, false, '_playBlocked has not changed');
  });

  QUnit.test("playMiddleware play will not trigger play event if the player doesn't have this plugin", function(assert) {
    let evt = null;
    let cnm = null;
    const nonAdsPlayer = {
      trigger: (event) => {
        evt = event;
      },
      addClass: (className) => {
        cnm = className;
      }
    };
    const m = pm.playMiddleware(nonAdsPlayer);

    this.sandbox.stub(pm, 'isMiddlewareMediatorSupported').returns(true);

    m.play(true, null);
    assert.equal(evt, null, 'the play event should not have been triggered');
    assert.equal(cnm, null, 'the class should not have been added');
  });

  QUnit.test("playMiddleware won't trigger play event if callPlay doesn't terminate", function(assert) {
    const m = pm.playMiddleware(this.player);
    const originalPlayBlocked = this.player.ads._playBlocked;

    this.sandbox.stub(pm, 'isMiddlewareMediatorSupported').returns(true);
    m.play(false, {});
    assert.equal(this.triggeredEvent, null, 'no events should be triggered');
    assert.equal(this.addedClass, null, 'no classes should be added');
    assert.strictEqual(
      this.player.ads._playBlocked, originalPlayBlocked,
      '_playBlocked remains unchanged'
    );
  });

});

