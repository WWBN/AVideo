(function e(t,n,r){function s(o,u){if(!n[o]){if(!t[o]){var a=typeof require=="function"&&require;if(!u&&a)return a(o,!0);if(i)return i(o,!0);var f=new Error("Cannot find module '"+o+"'");throw f.code="MODULE_NOT_FOUND",f}var l=n[o]={exports:{}};t[o][0].call(l.exports,function(e){var n=t[o][1][e];return s(n?n:e)},l,l.exports,e,t,n,r)}return n[o].exports}var i=typeof require=="function"&&require;for(var o=0;o<r.length;o++)s(r[o]);return s})({1:[function(require,module,exports){
/*! npm.im/intervalometer */
'use strict';

Object.defineProperty(exports, '__esModule', { value: true });

function intervalometer(cb, request, cancel, requestParameter) {
	var requestId;
	var previousLoopTime;
	function loop(now) {
		// must be requested before cb() because that might call .stop()
		requestId = request(loop, requestParameter);

		// called with "ms since last call". 0 on start()
		cb(now - (previousLoopTime || now));

		previousLoopTime = now;
	}
	return {
		start: function start() {
			if (!requestId) { // prevent double starts
				loop(0);
			}
		},
		stop: function stop() {
			cancel(requestId);
			requestId = null;
			previousLoopTime = 0;
		}
	};
}

function frameIntervalometer(cb) {
	return intervalometer(cb, requestAnimationFrame, cancelAnimationFrame);
}

function timerIntervalometer(cb, delay) {
	return intervalometer(cb, setTimeout, clearTimeout, delay);
}

exports.intervalometer = intervalometer;
exports.frameIntervalometer = frameIntervalometer;
exports.timerIntervalometer = timerIntervalometer;
},{}],2:[function(require,module,exports){
/*! npm.im/iphone-inline-video */
'use strict';

function _interopDefault (ex) { return (ex && (typeof ex === 'object') && 'default' in ex) ? ex['default'] : ex; }

var Symbol = _interopDefault(require('poor-mans-symbol'));
var intervalometer = require('intervalometer');

function preventEvent(element, eventName, toggleProperty, preventWithProperty) {
	function handler(e) {
		if (Boolean(element[toggleProperty]) === Boolean(preventWithProperty)) {
			e.stopImmediatePropagation();
			// console.log(eventName, 'prevented on', element);
		}
		delete element[toggleProperty];
	}
	element.addEventListener(eventName, handler, false);

	// Return handler to allow to disable the prevention. Usage:
	// const preventionHandler = preventEvent(el, 'click');
	// el.removeEventHandler('click', preventionHandler);
	return handler;
}

function proxyProperty(object, propertyName, sourceObject, copyFirst) {
	function get() {
		return sourceObject[propertyName];
	}
	function set(value) {
		sourceObject[propertyName] = value;
	}

	if (copyFirst) {
		set(object[propertyName]);
	}

	Object.defineProperty(object, propertyName, {get: get, set: set});
}

function proxyEvent(object, eventName, sourceObject) {
	sourceObject.addEventListener(eventName, function () { return object.dispatchEvent(new Event(eventName)); });
}

function dispatchEventAsync(element, type) {
	Promise.resolve().then(function () {
		element.dispatchEvent(new Event(type));
	});
}

// iOS 10 adds support for native inline playback + silent autoplay
var isWhitelisted = 'object-fit' in document.head.style && /iPhone|iPod/i.test(navigator.userAgent) && !matchMedia('(-webkit-video-playable-inline)').matches;

var ಠ = Symbol();
var ಠevent = Symbol();
var ಠplay = Symbol('nativeplay');
var ಠpause = Symbol('nativepause');

/**
 * UTILS
 */

function getAudioFromVideo(video) {
	var audio = new Audio();
	proxyEvent(video, 'play', audio);
	proxyEvent(video, 'playing', audio);
	proxyEvent(video, 'pause', audio);
	audio.crossOrigin = video.crossOrigin;

	// 'data:' causes audio.networkState > 0
	// which then allows to keep <audio> in a resumable playing state
	// i.e. once you set a real src it will keep playing if it was if .play() was called
	audio.src = video.src || video.currentSrc || 'data:';

	// if (audio.src === 'data:') {
	//   TODO: wait for video to be selected
	// }
	return audio;
}

var lastRequests = [];
var requestIndex = 0;
var lastTimeupdateEvent;

function setTime(video, time, rememberOnly) {
	// allow one timeupdate event every 200+ ms
	if ((lastTimeupdateEvent || 0) + 200 < Date.now()) {
		video[ಠevent] = true;
		lastTimeupdateEvent = Date.now();
	}
	if (!rememberOnly) {
		video.currentTime = time;
	}
	lastRequests[++requestIndex % 3] = time * 100 | 0 / 100;
}

function isPlayerEnded(player) {
	return player.driver.currentTime >= player.video.duration;
}

function update(timeDiff) {
	var player = this;
	// console.log('update', player.video.readyState, player.video.networkState, player.driver.readyState, player.driver.networkState, player.driver.paused);
	if (player.video.readyState >= player.video.HAVE_FUTURE_DATA) {
		if (!player.hasAudio) {
			player.driver.currentTime = player.video.currentTime + ((timeDiff * player.video.playbackRate) / 1000);
			if (player.video.loop && isPlayerEnded(player)) {
				player.driver.currentTime = 0;
			}
		}
		setTime(player.video, player.driver.currentTime);
	} else if (player.video.networkState === player.video.NETWORK_IDLE && !player.video.buffered.length) {
		// this should happen when the source is available but:
		// - it's potentially playing (.paused === false)
		// - it's not ready to play
		// - it's not loading
		// If it hasAudio, that will be loaded in the 'emptied' handler below
		player.video.load();
		// console.log('Will load');
	}

	// console.assert(player.video.currentTime === player.driver.currentTime, 'Video not updating!');

	if (player.video.ended) {
		delete player.video[ಠevent]; // allow timeupdate event
		player.video.pause(true);
	}
}

/**
 * METHODS
 */

function play() {
	// console.log('play');
	var video = this;
	var player = video[ಠ];

	// if it's fullscreen, use the native player
	if (video.webkitDisplayingFullscreen) {
		video[ಠplay]();
		return;
	}

	if (player.driver.src !== 'data:' && player.driver.src !== video.src) {
		// console.log('src changed on play', video.src);
		setTime(video, 0, true);
		player.driver.src = video.src;
	}

	if (!video.paused) {
		return;
	}
	player.paused = false;

	if (!video.buffered.length) {
		// .load() causes the emptied event
		// the alternative is .play()+.pause() but that triggers play/pause events, even worse
		// possibly the alternative is preventing this event only once
		video.load();
	}

	player.driver.play();
	player.updater.start();

	if (!player.hasAudio) {
		dispatchEventAsync(video, 'play');
		if (player.video.readyState >= player.video.HAVE_ENOUGH_DATA) {
			// console.log('onplay');
			dispatchEventAsync(video, 'playing');
		}
	}
}
function pause(forceEvents) {
	// console.log('pause');
	var video = this;
	var player = video[ಠ];

	player.driver.pause();
	player.updater.stop();

	// if it's fullscreen, the developer the native player.pause()
	// This is at the end of pause() because it also
	// needs to make sure that the simulation is paused
	if (video.webkitDisplayingFullscreen) {
		video[ಠpause]();
	}

	if (player.paused && !forceEvents) {
		return;
	}

	player.paused = true;
	if (!player.hasAudio) {
		dispatchEventAsync(video, 'pause');
	}
	if (video.ended) {
		video[ಠevent] = true;
		dispatchEventAsync(video, 'ended');
	}
}

/**
 * SETUP
 */

function addPlayer(video, hasAudio) {
	var player = video[ಠ] = {};
	player.paused = true; // track whether 'pause' events have been fired
	player.hasAudio = hasAudio;
	player.video = video;
	player.updater = intervalometer.frameIntervalometer(update.bind(player));

	if (hasAudio) {
		player.driver = getAudioFromVideo(video);
	} else {
		video.addEventListener('canplay', function () {
			if (!video.paused) {
				// console.log('oncanplay');
				dispatchEventAsync(video, 'playing');
			}
		});
		player.driver = {
			src: video.src || video.currentSrc || 'data:',
			muted: true,
			paused: true,
			pause: function () {
				player.driver.paused = true;
			},
			play: function () {
				player.driver.paused = false;
				// media automatically goes to 0 if .play() is called when it's done
				if (isPlayerEnded(player)) {
					setTime(video, 0);
				}
			},
			get ended() {
				return isPlayerEnded(player);
			}
		};
	}

	// .load() causes the emptied event
	video.addEventListener('emptied', function () {
		// console.log('driver src is', player.driver.src);
		var wasEmpty = !player.driver.src || player.driver.src === 'data:';
		if (player.driver.src && player.driver.src !== video.src) {
			// console.log('src changed to', video.src);
			setTime(video, 0, true);
			player.driver.src = video.src;
			// playing videos will only keep playing if no src was present when .play()’ed
			if (wasEmpty) {
				player.driver.play();
			} else {
				player.updater.stop();
			}
		}
	}, false);

	// stop programmatic player when OS takes over
	video.addEventListener('webkitbeginfullscreen', function () {
		if (!video.paused) {
			// make sure that the <audio> and the syncer/updater are stopped
			video.pause();

			// play video natively
			video[ಠplay]();
		} else if (hasAudio && !player.driver.buffered.length) {
			// if the first play is native,
			// the <audio> needs to be buffered manually
			// so when the fullscreen ends, it can be set to the same current time
			player.driver.load();
		}
	});
	if (hasAudio) {
		video.addEventListener('webkitendfullscreen', function () {
			// sync audio to new video position
			player.driver.currentTime = video.currentTime;
			// console.assert(player.driver.currentTime === video.currentTime, 'Audio not synced');
		});

		// allow seeking
		video.addEventListener('seeking', function () {
			if (lastRequests.indexOf(video.currentTime * 100 | 0 / 100) < 0) {
				// console.log('User-requested seeking');
				player.driver.currentTime = video.currentTime;
			}
		});
	}
}

function overloadAPI(video) {
	var player = video[ಠ];
	video[ಠplay] = video.play;
	video[ಠpause] = video.pause;
	video.play = play;
	video.pause = pause;
	proxyProperty(video, 'paused', player.driver);
	proxyProperty(video, 'muted', player.driver, true);
	proxyProperty(video, 'playbackRate', player.driver, true);
	proxyProperty(video, 'ended', player.driver);
	proxyProperty(video, 'loop', player.driver, true);
	preventEvent(video, 'seeking');
	preventEvent(video, 'seeked');
	preventEvent(video, 'timeupdate', ಠevent, false);
	preventEvent(video, 'ended', ಠevent, false); // prevent occasional native ended events
}

function enableInlineVideo(video, hasAudio, onlyWhitelisted) {
	if ( hasAudio === void 0 ) hasAudio = true;
	if ( onlyWhitelisted === void 0 ) onlyWhitelisted = true;

	if ((onlyWhitelisted && !isWhitelisted) || video[ಠ]) {
		return;
	}
	addPlayer(video, hasAudio);
	overloadAPI(video);
	video.classList.add('IIV');
	if (!hasAudio && video.autoplay) {
		video.play();
	}
	if (!/iPhone|iPod|iPad/.test(navigator.platform)) {
		console.warn('iphone-inline-video is not guaranteed to work in emulated environments');
	}
}

enableInlineVideo.isWhitelisted = isWhitelisted;

module.exports = enableInlineVideo;
},{"intervalometer":1,"poor-mans-symbol":3}],3:[function(require,module,exports){
'use strict';

var index = typeof Symbol === 'undefined' ? function (description) {
	return '@' + (description || '@') + Math.random();
} : Symbol;

module.exports = index;
},{}],4:[function(require,module,exports){
/**
 *
 * (c) Wensheng Yan <yanwsh@gmail.com>
 * Date: 10/30/16
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
'use strict';

Object.defineProperty(exports, "__esModule", {
    value: true
});

var _Detector = require('../lib/Detector');

var _Detector2 = _interopRequireDefault(_Detector);

var _MobileBuffering = require('../lib/MobileBuffering');

var _MobileBuffering2 = _interopRequireDefault(_MobileBuffering);

var _Util = require('../lib/Util');

var _Util2 = _interopRequireDefault(_Util);

function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { default: obj }; }

var HAVE_CURRENT_DATA = 2;

var BaseCanvas = function BaseCanvas(baseComponent, THREE) {
    var settings = arguments.length > 2 && arguments[2] !== undefined ? arguments[2] : {};

    return {
        constructor: function init(player, options) {
            this.settings = options;
            //basic settings
            this.width = player.el().offsetWidth, this.height = player.el().offsetHeight;
            this.lon = options.initLon, this.lat = options.initLat, this.phi = 0, this.theta = 0;
            this.videoType = options.videoType;
            this.clickToToggle = options.clickToToggle;
            this.mouseDown = false;
            this.isUserInteracting = false;

            //define render
            this.renderer = new THREE.WebGLRenderer();
            this.renderer.setPixelRatio(window.devicePixelRatio);
            this.renderer.setSize(this.width, this.height);
            this.renderer.autoClear = false;
            this.renderer.setClearColor(0x000000, 1);

            //define texture, on ie 11, we need additional helper canvas to solve rendering issue.
            var video = settings.getTech(player);
            this.supportVideoTexture = _Detector2.default.supportVideoTexture();
            this.liveStreamOnSafari = _Detector2.default.isLiveStreamOnSafari(video);
            if (this.liveStreamOnSafari) this.supportVideoTexture = false;
            if (!this.supportVideoTexture) {
                this.helperCanvas = player.addChild("HelperCanvas", {
                    video: video,
                    width: options.helperCanvas.width ? options.helperCanvas.width : this.width,
                    height: options.helperCanvas.height ? options.helperCanvas.height : this.height
                });
                var context = this.helperCanvas.el();
                this.texture = new THREE.Texture(context);
            } else {
                this.texture = new THREE.Texture(video);
            }

            video.style.visibility = "hidden";

            this.texture.generateMipmaps = false;
            this.texture.minFilter = THREE.LinearFilter;
            this.texture.maxFilter = THREE.LinearFilter;
            this.texture.format = THREE.RGBFormat;

            this.el_ = this.renderer.domElement;
            this.el_.classList.add('vjs-video-canvas');

            options.el = this.el_;
            baseComponent.call(this, player, options);

            this.attachControlEvents();
            this.player().on("play", function () {
                this.time = new Date().getTime();
                this.startAnimation();
            }.bind(this));
        },

        attachControlEvents: function attachControlEvents() {
            this.on('mousemove', this.handleMouseMove.bind(this));
            this.on('touchmove', this.handleTouchMove.bind(this));
            this.on('mousedown', this.handleMouseDown.bind(this));
            this.on('touchstart', this.handleTouchStart.bind(this));
            this.on('mouseup', this.handleMouseUp.bind(this));
            this.on('touchend', this.handleTouchEnd.bind(this));
            if (this.settings.scrollable) {
                this.on('mousewheel', this.handleMouseWheel.bind(this));
                this.on('MozMousePixelScroll', this.handleMouseWheel.bind(this));
            }
            this.on('mouseenter', this.handleMouseEnter.bind(this));
            this.on('mouseleave', this.handleMouseLease.bind(this));
            this.on('dispose', this.handleDispose.bind(this));
        },

        handleDispose: function handleDispose(event) {
            this.off('mousemove', this.handleMouseMove.bind(this));
            this.off('touchmove', this.handleTouchMove.bind(this));
            this.off('mousedown', this.handleMouseDown.bind(this));
            this.off('touchstart', this.handleTouchStart.bind(this));
            this.off('mouseup', this.handleMouseUp.bind(this));
            this.off('touchend', this.handleTouchEnd.bind(this));
            if (this.settings.scrollable) {
                this.off('mousewheel', this.handleMouseWheel.bind(this));
                this.off('MozMousePixelScroll', this.handleMouseWheel.bind(this));
            }
            this.off('mouseenter', this.handleMouseEnter.bind(this));
            this.off('mouseleave', this.handleMouseLease.bind(this));
            this.off('dispose', this.handleDispose.bind(this));
            this.stopAnimation();
        },

        startAnimation: function startAnimation() {
            this.render_animation = true;
            this.animate();
        },

        stopAnimation: function stopAnimation() {
            this.render_animation = false;
            if (this.requestAnimationId) {
                cancelAnimationFrame(this.requestAnimationId);
            }
        },

        handleResize: function handleResize() {
            this.width = this.player().el().offsetWidth, this.height = this.player().el().offsetHeight;
            this.renderer.setSize(this.width, this.height);
        },

        handleMouseUp: function handleMouseUp(event) {
            this.mouseDown = false;
            if (this.clickToToggle) {
                var clientX = event.clientX || event.changedTouches && event.changedTouches[0].clientX;
                var clientY = event.clientY || event.changedTouches && event.changedTouches[0].clientY;
                if (typeof clientX === "undefined" || clientY === "undefined") return;
                var diffX = Math.abs(clientX - this.onPointerDownPointerX);
                var diffY = Math.abs(clientY - this.onPointerDownPointerY);
                if (diffX < 0.1 && diffY < 0.1) this.player().paused() ? this.player().play() : this.player().pause();
            }
        },

        handleMouseDown: function handleMouseDown(event) {
            event.preventDefault();
            var clientX = event.clientX || event.touches && event.touches[0].clientX;
            var clientY = event.clientY || event.touches && event.touches[0].clientY;
            if (typeof clientX === "undefined" || clientY === "undefined") return;
            this.mouseDown = true;
            this.onPointerDownPointerX = clientX;
            this.onPointerDownPointerY = clientY;
            this.onPointerDownLon = this.lon;
            this.onPointerDownLat = this.lat;
        },

        handleTouchStart: function handleTouchStart(event) {
            if (event.touches.length > 1) {
                this.isUserPinch = true;
                this.multiTouchDistance = _Util2.default.getTouchesDistance(event.touches);
            }
            this.handleMouseDown(event);
        },

        handleTouchEnd: function handleTouchEnd(event) {
            this.isUserPinch = false;
            this.handleMouseUp(event);
        },

        handleMouseMove: function handleMouseMove(event) {
            var clientX = event.clientX || event.touches && event.touches[0].clientX;
            var clientY = event.clientY || event.touches && event.touches[0].clientY;
            if (typeof clientX === "undefined" || clientY === "undefined") return;
            if (this.settings.clickAndDrag) {
                if (this.mouseDown) {
                    this.lon = (this.onPointerDownPointerX - clientX) * 0.2 + this.onPointerDownLon;
                    this.lat = (clientY - this.onPointerDownPointerY) * 0.2 + this.onPointerDownLat;
                }
            } else {
                var x = clientX - this.el_.offsetLeft;
                var y = clientY - this.el_.offsetTop;
                this.lon = x / this.width * 430 - 225;
                this.lat = y / this.height * -180 + 90;
            }
        },

        handleTouchMove: function handleTouchMove(event) {
            //handle single touch event,
            if (!this.isUserPinch || event.touches.length <= 1) {
                this.handleMouseMove(event);
            }
        },

        handleMobileOrientation: function handleMobileOrientation(event) {
            if (typeof event.rotationRate === "undefined") return;
            var x = event.rotationRate.alpha;
            var y = event.rotationRate.beta;
            var portrait = typeof event.portrait !== "undefined" ? event.portrait : window.matchMedia("(orientation: portrait)").matches;
            var landscape = typeof event.landscape !== "undefined" ? event.landscape : window.matchMedia("(orientation: landscape)").matches;
            var orientation = event.orientation || window.orientation;

            if (portrait) {
                this.lon = this.lon - y * this.settings.mobileVibrationValue;
                this.lat = this.lat + x * this.settings.mobileVibrationValue;
            } else if (landscape) {
                var orientationDegree = -90;
                if (typeof orientation != "undefined") {
                    orientationDegree = orientation;
                }

                this.lon = orientationDegree == -90 ? this.lon + x * this.settings.mobileVibrationValue : this.lon - x * this.settings.mobileVibrationValue;
                this.lat = orientationDegree == -90 ? this.lat + y * this.settings.mobileVibrationValue : this.lat - y * this.settings.mobileVibrationValue;
            }
        },

        handleMouseWheel: function handleMouseWheel(event) {
            event.stopPropagation();
            event.preventDefault();
        },

        handleMouseEnter: function handleMouseEnter(event) {
            this.isUserInteracting = true;
        },

        handleMouseLease: function handleMouseLease(event) {
            this.isUserInteracting = false;
            if (this.mouseDown) {
                this.mouseDown = false;
            }
        },

        animate: function animate() {
            if (!this.render_animation) return;
            this.requestAnimationId = requestAnimationFrame(this.animate.bind(this));
            if (!this.player().paused()) {
                if (typeof this.texture !== "undefined" && (!this.isPlayOnMobile && this.player().readyState() >= HAVE_CURRENT_DATA || this.isPlayOnMobile && this.player().hasClass("vjs-playing"))) {
                    var ct = new Date().getTime();
                    if (ct - this.time >= 30) {
                        this.texture.needsUpdate = true;
                        this.time = ct;
                    }
                    if (this.isPlayOnMobile) {
                        var currentTime = this.player().currentTime();
                        if (_MobileBuffering2.default.isBuffering(currentTime)) {
                            if (!this.player().hasClass("vjs-panorama-mobile-inline-video-buffering")) {
                                this.player().addClass("vjs-panorama-mobile-inline-video-buffering");
                            }
                        } else {
                            if (this.player().hasClass("vjs-panorama-mobile-inline-video-buffering")) {
                                this.player().removeClass("vjs-panorama-mobile-inline-video-buffering");
                            }
                        }
                    }
                }
            }
            this.render();
        },

        render: function render() {
            if (!this.isUserInteracting) {
                var symbolLat = this.lat > this.settings.initLat ? -1 : 1;
                var symbolLon = this.lon > this.settings.initLon ? -1 : 1;
                if (this.settings.backToVerticalCenter) {
                    this.lat = this.lat > this.settings.initLat - Math.abs(this.settings.returnStepLat) && this.lat < this.settings.initLat + Math.abs(this.settings.returnStepLat) ? this.settings.initLat : this.lat + this.settings.returnStepLat * symbolLat;
                }
                if (this.settings.backToHorizonCenter) {
                    this.lon = this.lon > this.settings.initLon - Math.abs(this.settings.returnStepLon) && this.lon < this.settings.initLon + Math.abs(this.settings.returnStepLon) ? this.settings.initLon : this.lon + this.settings.returnStepLon * symbolLon;
                }
            }
            this.lat = Math.max(this.settings.minLat, Math.min(this.settings.maxLat, this.lat));
            this.lon = Math.max(this.settings.minLon, Math.min(this.settings.maxLon, this.lon));
            this.phi = THREE.Math.degToRad(90 - this.lat);
            this.theta = THREE.Math.degToRad(this.lon);

            if (!this.supportVideoTexture) {
                this.helperCanvas.update();
            }
            this.renderer.clear();
        },

        playOnMobile: function playOnMobile() {
            this.isPlayOnMobile = true;
            if (this.settings.autoMobileOrientation) window.addEventListener('devicemotion', this.handleMobileOrientation.bind(this));
        },

        el: function el() {
            return this.el_;
        }
    };
};

exports.default = BaseCanvas;

},{"../lib/Detector":6,"../lib/MobileBuffering":8,"../lib/Util":11}],5:[function(require,module,exports){
'use strict';

Object.defineProperty(exports, "__esModule", {
    value: true
});

var _BaseCanvas = require('./BaseCanvas');

var _BaseCanvas2 = _interopRequireDefault(_BaseCanvas);

var _Util = require('./Util');

var _Util2 = _interopRequireDefault(_Util);

function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { default: obj }; }

/**
 * Created by yanwsh on 4/3/16.
 */

var Canvas = function Canvas(baseComponent, THREE) {
    var settings = arguments.length > 2 && arguments[2] !== undefined ? arguments[2] : {};

    var parent = (0, _BaseCanvas2.default)(baseComponent, THREE, settings);

    return _Util2.default.extend(parent, {
        constructor: function init(player, options) {
            parent.constructor.call(this, player, options);

            this.VRMode = false;
            //define scene
            this.scene = new THREE.Scene();
            //define camera
            this.camera = new THREE.PerspectiveCamera(options.initFov, this.width / this.height, 1, 2000);
            this.camera.target = new THREE.Vector3(0, 0, 0);

            //define geometry
            var geometry = this.videoType === "equirectangular" ? new THREE.SphereGeometry(500, 60, 40) : new THREE.SphereBufferGeometry(500, 60, 40).toNonIndexed();
            if (this.videoType === "fisheye") {
                var normals = geometry.attributes.normal.array;
                var uvs = geometry.attributes.uv.array;
                for (var i = 0, l = normals.length / 3; i < l; i++) {
                    var x = normals[i * 3 + 0];
                    var y = normals[i * 3 + 1];
                    var z = normals[i * 3 + 2];

                    var r = Math.asin(Math.sqrt(x * x + z * z) / Math.sqrt(x * x + y * y + z * z)) / Math.PI;
                    if (y < 0) r = 1 - r;
                    var theta = x == 0 && z == 0 ? 0 : Math.acos(x / Math.sqrt(x * x + z * z));
                    if (z < 0) theta = theta * -1;
                    uvs[i * 2 + 0] = -0.8 * r * Math.cos(theta) + 0.5;
                    uvs[i * 2 + 1] = 0.8 * r * Math.sin(theta) + 0.5;
                }
                geometry.rotateX(options.rotateX);
                geometry.rotateY(options.rotateY);
                geometry.rotateZ(options.rotateZ);
            } else if (this.videoType === "dual_fisheye") {
                var _normals = geometry.attributes.normal.array;
                var _uvs = geometry.attributes.uv.array;
                var _l = _normals.length / 3;
                for (var _i = 0; _i < _l / 2; _i++) {
                    var _x2 = _normals[_i * 3 + 0];
                    var _y = _normals[_i * 3 + 1];
                    var _z = _normals[_i * 3 + 2];

                    var _r = _x2 == 0 && _z == 0 ? 1 : Math.acos(_y) / Math.sqrt(_x2 * _x2 + _z * _z) * (2 / Math.PI);
                    _uvs[_i * 2 + 0] = _x2 * options.dualFish.circle1.rx * _r * options.dualFish.circle1.coverX + options.dualFish.circle1.x;
                    _uvs[_i * 2 + 1] = _z * options.dualFish.circle1.ry * _r * options.dualFish.circle1.coverY + options.dualFish.circle1.y;
                }
                for (var _i2 = _l / 2; _i2 < _l; _i2++) {
                    var _x3 = _normals[_i2 * 3 + 0];
                    var _y2 = _normals[_i2 * 3 + 1];
                    var _z2 = _normals[_i2 * 3 + 2];

                    var _r2 = _x3 == 0 && _z2 == 0 ? 1 : Math.acos(-_y2) / Math.sqrt(_x3 * _x3 + _z2 * _z2) * (2 / Math.PI);
                    _uvs[_i2 * 2 + 0] = -_x3 * options.dualFish.circle2.rx * _r2 * options.dualFish.circle2.coverX + options.dualFish.circle2.x;
                    _uvs[_i2 * 2 + 1] = _z2 * options.dualFish.circle2.ry * _r2 * options.dualFish.circle2.coverY + options.dualFish.circle2.y;
                }
                geometry.rotateX(options.rotateX);
                geometry.rotateY(options.rotateY);
                geometry.rotateZ(options.rotateZ);
            }
            geometry.scale(-1, 1, 1);
            //define mesh
            this.mesh = new THREE.Mesh(geometry, new THREE.MeshBasicMaterial({ map: this.texture }));
            //this.mesh.scale.x = -1;
            this.scene.add(this.mesh);
        },

        enableVR: function enableVR() {
            this.VRMode = true;
            if (typeof vrHMD !== 'undefined') {
                var eyeParamsL = vrHMD.getEyeParameters('left');
                var eyeParamsR = vrHMD.getEyeParameters('right');

                this.eyeFOVL = eyeParamsL.recommendedFieldOfView;
                this.eyeFOVR = eyeParamsR.recommendedFieldOfView;
            }

            this.cameraL = new THREE.PerspectiveCamera(this.camera.fov, this.width / 2 / this.height, 1, 2000);
            this.cameraR = new THREE.PerspectiveCamera(this.camera.fov, this.width / 2 / this.height, 1, 2000);
        },

        disableVR: function disableVR() {
            this.VRMode = false;
            this.renderer.setViewport(0, 0, this.width, this.height);
            this.renderer.setScissor(0, 0, this.width, this.height);
        },

        handleResize: function handleResize() {
            parent.handleResize.call(this);
            this.camera.aspect = this.width / this.height;
            this.camera.updateProjectionMatrix();
            if (this.VRMode) {
                this.cameraL.aspect = this.camera.aspect / 2;
                this.cameraR.aspect = this.camera.aspect / 2;
                this.cameraL.updateProjectionMatrix();
                this.cameraR.updateProjectionMatrix();
            }
        },

        handleMouseWheel: function handleMouseWheel(event) {
            parent.handleMouseWheel(event);
            // WebKit
            if (event.wheelDeltaY) {
                this.camera.fov -= event.wheelDeltaY * 0.05;
                // Opera / Explorer 9
            } else if (event.wheelDelta) {
                this.camera.fov -= event.wheelDelta * 0.05;
                // Firefox
            } else if (event.detail) {
                this.camera.fov += event.detail * 1.0;
            }
            this.camera.fov = Math.min(this.settings.maxFov, this.camera.fov);
            this.camera.fov = Math.max(this.settings.minFov, this.camera.fov);
            this.camera.updateProjectionMatrix();
            if (this.VRMode) {
                this.cameraL.fov = this.camera.fov;
                this.cameraR.fov = this.camera.fov;
                this.cameraL.updateProjectionMatrix();
                this.cameraR.updateProjectionMatrix();
            }
        },

        handleTouchMove: function handleTouchMove(event) {
            parent.handleTouchMove.call(this, event);
            if (this.isUserPinch) {
                var currentDistance = _Util2.default.getTouchesDistance(event.touches);
                event.wheelDeltaY = (currentDistance - this.multiTouchDistance) * 2;
                this.handleMouseWheel.call(this, event);
                this.multiTouchDistance = currentDistance;
            }
        },

        render: function render() {
            parent.render.call(this);
            this.camera.target.x = 500 * Math.sin(this.phi) * Math.cos(this.theta);
            this.camera.target.y = 500 * Math.cos(this.phi);
            this.camera.target.z = 500 * Math.sin(this.phi) * Math.sin(this.theta);
            this.camera.lookAt(this.camera.target);

            if (!this.VRMode) {
                this.renderer.render(this.scene, this.camera);
            } else {
                var viewPortWidth = this.width / 2,
                    viewPortHeight = this.height;
                if (typeof vrHMD !== 'undefined') {
                    this.cameraL.projectionMatrix = _Util2.default.fovToProjection(this.eyeFOVL, true, this.camera.near, this.camera.far);
                    this.cameraR.projectionMatrix = _Util2.default.fovToProjection(this.eyeFOVR, true, this.camera.near, this.camera.far);
                } else {
                    var lonL = this.lon + this.settings.VRGapDegree;
                    var lonR = this.lon - this.settings.VRGapDegree;

                    var thetaL = THREE.Math.degToRad(lonL);
                    var thetaR = THREE.Math.degToRad(lonR);

                    var targetL = _Util2.default.deepCopy(this.camera.target);
                    targetL.x = 500 * Math.sin(this.phi) * Math.cos(thetaL);
                    targetL.z = 500 * Math.sin(this.phi) * Math.sin(thetaL);
                    this.cameraL.lookAt(targetL);

                    var targetR = _Util2.default.deepCopy(this.camera.target);
                    targetR.x = 500 * Math.sin(this.phi) * Math.cos(thetaR);
                    targetR.z = 500 * Math.sin(this.phi) * Math.sin(thetaR);
                    this.cameraR.lookAt(targetR);
                }
                // render left eye
                this.renderer.setViewport(0, 0, viewPortWidth, viewPortHeight);
                this.renderer.setScissor(0, 0, viewPortWidth, viewPortHeight);
                this.renderer.render(this.scene, this.cameraL);

                // render right eye
                this.renderer.setViewport(viewPortWidth, 0, viewPortWidth, viewPortHeight);
                this.renderer.setScissor(viewPortWidth, 0, viewPortWidth, viewPortHeight);
                this.renderer.render(this.scene, this.cameraR);
            }
        }
    });
};

exports.default = Canvas;

},{"./BaseCanvas":4,"./Util":11}],6:[function(require,module,exports){
'use strict';

Object.defineProperty(exports, "__esModule", {
    value: true
});
/**
 * @author alteredq / http://alteredqualia.com/
 * @author mr.doob / http://mrdoob.com/
 */

//in case it's running on node.js
var win = {};

if (typeof window !== "undefined") {
    win = window;
}

var Detector = {

    canvas: !!win.CanvasRenderingContext2D,
    webgl: function () {

        try {

            var canvas = document.createElement('canvas');return !!(win.WebGLRenderingContext && (canvas.getContext('webgl') || canvas.getContext('experimental-webgl')));
        } catch (e) {

            return false;
        }
    }(),
    workers: !!win.Worker,
    fileapi: win.File && win.FileReader && win.FileList && win.Blob,

    Check_Version: function Check_Version() {
        var rv = -1; // Return value assumes failure.

        if (navigator.appName == 'Microsoft Internet Explorer') {

            var ua = navigator.userAgent,
                re = new RegExp("MSIE ([0-9]{1,}[\\.0-9]{0,})");

            if (re.exec(ua) !== null) {
                rv = parseFloat(RegExp.$1);
            }
        } else if (navigator.appName == "Netscape") {
            /// in IE 11 the navigator.appVersion says 'trident'
            /// in Edge the navigator.appVersion does not say trident
            if (navigator.appVersion.indexOf('Trident') !== -1) rv = 11;else {
                var ua = navigator.userAgent;
                var re = new RegExp("Edge\/([0-9]{1,}[\\.0-9]{0,})");
                if (re.exec(ua) !== null) {
                    rv = parseFloat(RegExp.$1);
                }
            }
        }

        return rv;
    },

    supportVideoTexture: function supportVideoTexture() {
        //ie 11 and edge 12 doesn't support video texture.
        var version = this.Check_Version();
        return version === -1 || version >= 13;
    },

    isLiveStreamOnSafari: function isLiveStreamOnSafari(videoElement) {
        //live stream on safari doesn't support video texture
        var videoSources = [].slice.call(videoElement.querySelectorAll("source"));
        var result = false;
        if (videoElement.src && videoElement.src.indexOf('.m3u8') > -1) {
            videoSources.push({
                src: videoElement.src,
                type: "application/x-mpegURL"
            });
        }
        for (var i = 0; i < videoSources.length; i++) {
            var currentVideoSource = videoSources[i];
            if ((currentVideoSource.type === "application/x-mpegURL" || currentVideoSource.type === "application/vnd.apple.mpegurl") && /(Safari|AppleWebKit)/.test(navigator.userAgent) && /Apple Computer/.test(navigator.vendor)) {
                result = true;
                break;
            }
        }
        return result;
    },

    getWebGLErrorMessage: function getWebGLErrorMessage() {

        var element = document.createElement('div');
        element.id = 'webgl-error-message';

        if (!this.webgl) {

            element.innerHTML = win.WebGLRenderingContext ? ['Your graphics card does not seem to support <a href="http://khronos.org/webgl/wiki/Getting_a_WebGL_Implementation" style="color:#000">WebGL</a>.<br />', 'Find out how to get it <a href="http://get.webgl.org/" style="color:#000">here</a>.'].join('\n') : ['Your browser does not seem to support <a href="http://khronos.org/webgl/wiki/Getting_a_WebGL_Implementation" style="color:#000">WebGL</a>.<br/>', 'Find out how to get it <a href="http://get.webgl.org/" style="color:#000">here</a>.'].join('\n');
        }

        return element;
    },

    addGetWebGLMessage: function addGetWebGLMessage(parameters) {

        var parent, id, element;

        parameters = parameters || {};

        parent = parameters.parent !== undefined ? parameters.parent : document.body;
        id = parameters.id !== undefined ? parameters.id : 'oldie';

        element = Detector.getWebGLErrorMessage();
        element.id = id;

        parent.appendChild(element);
    }

};

exports.default = Detector;

},{}],7:[function(require,module,exports){
"use strict";

Object.defineProperty(exports, "__esModule", {
    value: true
});
/**
 * Created by wensheng.yan on 5/23/16.
 */
var HelperCanvas = function HelperCanvas(baseComponent) {
    var element = document.createElement('canvas');
    element.className = "vjs-video-helper-canvas";
    return {
        constructor: function init(player, options) {
            this.videoElement = options.video;
            this.width = options.width;
            this.height = options.height;

            element.width = this.width;
            element.height = this.height;
            element.style.display = "none";
            options.el = element;

            this.context = element.getContext('2d');
            this.context.drawImage(this.videoElement, 0, 0, this.width, this.height);
            baseComponent.call(this, player, options);
        },

        getContext: function getContext() {
            return this.context;
        },

        update: function update() {
            this.context.drawImage(this.videoElement, 0, 0, this.width, this.height);
        },

        el: function el() {
            return element;
        }
    };
};

exports.default = HelperCanvas;

},{}],8:[function(require,module,exports){
"use strict";

Object.defineProperty(exports, "__esModule", {
    value: true
});
/**
 * Created by yanwsh on 6/6/16.
 */
var MobileBuffering = {
    prev_currentTime: 0,
    counter: 0,

    isBuffering: function isBuffering(currentTime) {
        if (currentTime == this.prev_currentTime) this.counter++;else this.counter = 0;
        this.prev_currentTime = currentTime;
        if (this.counter > 10) {
            //not let counter overflow
            this.counter = 10;
            return true;
        }
        return false;
    }
};

exports.default = MobileBuffering;

},{}],9:[function(require,module,exports){
"use strict";

Object.defineProperty(exports, "__esModule", {
    value: true
});

var _typeof = typeof Symbol === "function" && typeof Symbol.iterator === "symbol" ? function (obj) { return typeof obj; } : function (obj) { return obj && typeof Symbol === "function" && obj.constructor === Symbol && obj !== Symbol.prototype ? "symbol" : typeof obj; };

/**
 * Created by yanwsh on 4/4/16.
 */

var Notice = function Notice(baseComponent) {
    var element = document.createElement('div');
    element.className = "vjs-video-notice-label";

    return {
        constructor: function init(player, options) {
            if (_typeof(options.NoticeMessage) == "object") {
                element = options.NoticeMessage;
                options.el = options.NoticeMessage;
            } else if (typeof options.NoticeMessage == "string") {
                element.innerHTML = options.NoticeMessage;
                options.el = element;
            }

            baseComponent.call(this, player, options);
        },

        el: function el() {
            return element;
        }
    };
};

exports.default = Notice;

},{}],10:[function(require,module,exports){
/**
 *
 * (c) Wensheng Yan <yanwsh@gmail.com>
 * Date: 10/21/16
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
'use strict';

Object.defineProperty(exports, "__esModule", {
    value: true
});

var _BaseCanvas = require('./BaseCanvas');

var _BaseCanvas2 = _interopRequireDefault(_BaseCanvas);

var _Util = require('./Util');

var _Util2 = _interopRequireDefault(_Util);

function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { default: obj }; }

var ThreeDCanvas = function ThreeDCanvas(baseComponent, THREE) {
    var settings = arguments.length > 2 && arguments[2] !== undefined ? arguments[2] : {};

    var parent = (0, _BaseCanvas2.default)(baseComponent, THREE, settings);
    return _Util2.default.extend(parent, {
        constructor: function init(player, options) {
            parent.constructor.call(this, player, options);
            //only show left part by default
            this.VRMode = false;
            //define scene
            this.scene = new THREE.Scene();

            var aspectRatio = this.width / this.height;
            //define camera
            this.cameraL = new THREE.PerspectiveCamera(options.initFov, aspectRatio, 1, 2000);
            this.cameraL.target = new THREE.Vector3(0, 0, 0);

            this.cameraR = new THREE.PerspectiveCamera(options.initFov, aspectRatio / 2, 1, 2000);
            this.cameraR.position.set(1000, 0, 0);
            this.cameraR.target = new THREE.Vector3(1000, 0, 0);

            var geometryL = new THREE.SphereBufferGeometry(500, 60, 40).toNonIndexed();
            var geometryR = new THREE.SphereBufferGeometry(500, 60, 40).toNonIndexed();

            var uvsL = geometryL.attributes.uv.array;
            var normalsL = geometryL.attributes.normal.array;
            for (var i = 0; i < normalsL.length / 3; i++) {
                uvsL[i * 2 + 1] = uvsL[i * 2 + 1] / 2;
            }

            var uvsR = geometryR.attributes.uv.array;
            var normalsR = geometryR.attributes.normal.array;
            for (var i = 0; i < normalsR.length / 3; i++) {
                uvsR[i * 2 + 1] = uvsR[i * 2 + 1] / 2 + 0.5;
            }

            geometryL.scale(-1, 1, 1);
            geometryR.scale(-1, 1, 1);

            this.meshL = new THREE.Mesh(geometryL, new THREE.MeshBasicMaterial({ map: this.texture }));

            this.meshR = new THREE.Mesh(geometryR, new THREE.MeshBasicMaterial({ map: this.texture }));
            this.meshR.position.set(1000, 0, 0);

            this.scene.add(this.meshL);

            if (options.callback) options.callback();
        },

        handleResize: function handleResize() {
            parent.handleResize.call(this);
            var aspectRatio = this.width / this.height;
            if (!this.VRMode) {
                this.cameraL.aspect = aspectRatio;
                this.cameraL.updateProjectionMatrix();
            } else {
                aspectRatio /= 2;
                this.cameraL.aspect = aspectRatio;
                this.cameraR.aspect = aspectRatio;
                this.cameraL.updateProjectionMatrix();
                this.cameraR.updateProjectionMatrix();
            }
        },

        handleMouseWheel: function handleMouseWheel(event) {
            parent.handleMouseWheel(event);
            // WebKit
            if (event.wheelDeltaY) {
                this.cameraL.fov -= event.wheelDeltaY * 0.05;
                // Opera / Explorer 9
            } else if (event.wheelDelta) {
                this.cameraL.fov -= event.wheelDelta * 0.05;
                // Firefox
            } else if (event.detail) {
                this.cameraL.fov += event.detail * 1.0;
            }
            this.cameraL.fov = Math.min(this.settings.maxFov, this.cameraL.fov);
            this.cameraL.fov = Math.max(this.settings.minFov, this.cameraL.fov);
            this.cameraL.updateProjectionMatrix();
            if (this.VRMode) {
                this.cameraR.fov = this.cameraL.fov;
                this.cameraR.updateProjectionMatrix();
            }
        },

        enableVR: function enableVR() {
            this.VRMode = true;
            this.scene.add(this.meshR);
            this.handleResize();
        },

        disableVR: function disableVR() {
            this.VRMode = false;
            this.scene.remove(this.meshR);
            this.handleResize();
        },

        render: function render() {
            parent.render.call(this);
            this.cameraL.target.x = 500 * Math.sin(this.phi) * Math.cos(this.theta);
            this.cameraL.target.y = 500 * Math.cos(this.phi);
            this.cameraL.target.z = 500 * Math.sin(this.phi) * Math.sin(this.theta);
            this.cameraL.lookAt(this.cameraL.target);

            if (this.VRMode) {
                var viewPortWidth = this.width / 2,
                    viewPortHeight = this.height;
                this.cameraR.target.x = 1000 + 500 * Math.sin(this.phi) * Math.cos(this.theta);
                this.cameraR.target.y = 500 * Math.cos(this.phi);
                this.cameraR.target.z = 500 * Math.sin(this.phi) * Math.sin(this.theta);
                this.cameraR.lookAt(this.cameraR.target);

                // render left eye
                this.renderer.setViewport(0, 0, viewPortWidth, viewPortHeight);
                this.renderer.setScissor(0, 0, viewPortWidth, viewPortHeight);
                this.renderer.render(this.scene, this.cameraL);

                // render right eye
                this.renderer.setViewport(viewPortWidth, 0, viewPortWidth, viewPortHeight);
                this.renderer.setScissor(viewPortWidth, 0, viewPortWidth, viewPortHeight);
                this.renderer.render(this.scene, this.cameraR);
            } else {
                this.renderer.render(this.scene, this.cameraL);
            }
        }
    });
};

exports.default = ThreeDCanvas;

},{"./BaseCanvas":4,"./Util":11}],11:[function(require,module,exports){
'use strict';

Object.defineProperty(exports, "__esModule", {
    value: true
});
/**
 * Created by wensheng.yan on 4/4/16.
 */
function whichTransitionEvent() {
    var t;
    var el = document.createElement('fakeelement');
    var transitions = {
        'transition': 'transitionend',
        'OTransition': 'oTransitionEnd',
        'MozTransition': 'transitionend',
        'WebkitTransition': 'webkitTransitionEnd'
    };

    for (t in transitions) {
        if (el.style[t] !== undefined) {
            return transitions[t];
        }
    }
}

function mobileAndTabletcheck() {
    var check = false;
    (function (a) {
        if (/(android|bb\d+|meego).+mobile|avantgo|bada\/|blackberry|blazer|compal|elaine|fennec|hiptop|iemobile|ip(hone|od)|iris|kindle|lge |maemo|midp|mmp|mobile.+firefox|netfront|opera m(ob|in)i|palm( os)?|phone|p(ixi|re)\/|plucker|pocket|psp|series(4|6)0|symbian|treo|up\.(browser|link)|vodafone|wap|windows ce|xda|xiino|android|ipad|playbook|silk/i.test(a) || /1207|6310|6590|3gso|4thp|50[1-6]i|770s|802s|a wa|abac|ac(er|oo|s\-)|ai(ko|rn)|al(av|ca|co)|amoi|an(ex|ny|yw)|aptu|ar(ch|go)|as(te|us)|attw|au(di|\-m|r |s )|avan|be(ck|ll|nq)|bi(lb|rd)|bl(ac|az)|br(e|v)w|bumb|bw\-(n|u)|c55\/|capi|ccwa|cdm\-|cell|chtm|cldc|cmd\-|co(mp|nd)|craw|da(it|ll|ng)|dbte|dc\-s|devi|dica|dmob|do(c|p)o|ds(12|\-d)|el(49|ai)|em(l2|ul)|er(ic|k0)|esl8|ez([4-7]0|os|wa|ze)|fetc|fly(\-|_)|g1 u|g560|gene|gf\-5|g\-mo|go(\.w|od)|gr(ad|un)|haie|hcit|hd\-(m|p|t)|hei\-|hi(pt|ta)|hp( i|ip)|hs\-c|ht(c(\-| |_|a|g|p|s|t)|tp)|hu(aw|tc)|i\-(20|go|ma)|i230|iac( |\-|\/)|ibro|idea|ig01|ikom|im1k|inno|ipaq|iris|ja(t|v)a|jbro|jemu|jigs|kddi|keji|kgt( |\/)|klon|kpt |kwc\-|kyo(c|k)|le(no|xi)|lg( g|\/(k|l|u)|50|54|\-[a-w])|libw|lynx|m1\-w|m3ga|m50\/|ma(te|ui|xo)|mc(01|21|ca)|m\-cr|me(rc|ri)|mi(o8|oa|ts)|mmef|mo(01|02|bi|de|do|t(\-| |o|v)|zz)|mt(50|p1|v )|mwbp|mywa|n10[0-2]|n20[2-3]|n30(0|2)|n50(0|2|5)|n7(0(0|1)|10)|ne((c|m)\-|on|tf|wf|wg|wt)|nok(6|i)|nzph|o2im|op(ti|wv)|oran|owg1|p800|pan(a|d|t)|pdxg|pg(13|\-([1-8]|c))|phil|pire|pl(ay|uc)|pn\-2|po(ck|rt|se)|prox|psio|pt\-g|qa\-a|qc(07|12|21|32|60|\-[2-7]|i\-)|qtek|r380|r600|raks|rim9|ro(ve|zo)|s55\/|sa(ge|ma|mm|ms|ny|va)|sc(01|h\-|oo|p\-)|sdk\/|se(c(\-|0|1)|47|mc|nd|ri)|sgh\-|shar|sie(\-|m)|sk\-0|sl(45|id)|sm(al|ar|b3|it|t5)|so(ft|ny)|sp(01|h\-|v\-|v )|sy(01|mb)|t2(18|50)|t6(00|10|18)|ta(gt|lk)|tcl\-|tdg\-|tel(i|m)|tim\-|t\-mo|to(pl|sh)|ts(70|m\-|m3|m5)|tx\-9|up(\.b|g1|si)|utst|v400|v750|veri|vi(rg|te)|vk(40|5[0-3]|\-v)|vm40|voda|vulc|vx(52|53|60|61|70|80|81|83|85|98)|w3c(\-| )|webc|whit|wi(g |nc|nw)|wmlb|wonu|x700|yas\-|your|zeto|zte\-/i.test(a.substr(0, 4))) check = true;
    })(navigator.userAgent || navigator.vendor || window.opera);
    return check;
}

function isIos() {
    return (/iPhone|iPad|iPod/i.test(navigator.userAgent)
    );
}

function isRealIphone() {
    return (/iPhone|iPod/i.test(navigator.platform)
    );
}

//adopt code from: https://github.com/MozVR/vr-web-examples/blob/master/threejs-vr-boilerplate/js/VREffect.js
function fovToNDCScaleOffset(fov) {
    var pxscale = 2.0 / (fov.leftTan + fov.rightTan);
    var pxoffset = (fov.leftTan - fov.rightTan) * pxscale * 0.5;
    var pyscale = 2.0 / (fov.upTan + fov.downTan);
    var pyoffset = (fov.upTan - fov.downTan) * pyscale * 0.5;
    return { scale: [pxscale, pyscale], offset: [pxoffset, pyoffset] };
}

function fovPortToProjection(fov, rightHanded, zNear, zFar) {

    rightHanded = rightHanded === undefined ? true : rightHanded;
    zNear = zNear === undefined ? 0.01 : zNear;
    zFar = zFar === undefined ? 10000.0 : zFar;

    var handednessScale = rightHanded ? -1.0 : 1.0;

    // start with an identity matrix
    var mobj = new THREE.Matrix4();
    var m = mobj.elements;

    // and with scale/offset info for normalized device coords
    var scaleAndOffset = fovToNDCScaleOffset(fov);

    // X result, map clip edges to [-w,+w]
    m[0 * 4 + 0] = scaleAndOffset.scale[0];
    m[0 * 4 + 1] = 0.0;
    m[0 * 4 + 2] = scaleAndOffset.offset[0] * handednessScale;
    m[0 * 4 + 3] = 0.0;

    // Y result, map clip edges to [-w,+w]
    // Y offset is negated because this proj matrix transforms from world coords with Y=up,
    // but the NDC scaling has Y=down (thanks D3D?)
    m[1 * 4 + 0] = 0.0;
    m[1 * 4 + 1] = scaleAndOffset.scale[1];
    m[1 * 4 + 2] = -scaleAndOffset.offset[1] * handednessScale;
    m[1 * 4 + 3] = 0.0;

    // Z result (up to the app)
    m[2 * 4 + 0] = 0.0;
    m[2 * 4 + 1] = 0.0;
    m[2 * 4 + 2] = zFar / (zNear - zFar) * -handednessScale;
    m[2 * 4 + 3] = zFar * zNear / (zNear - zFar);

    // W result (= Z in)
    m[3 * 4 + 0] = 0.0;
    m[3 * 4 + 1] = 0.0;
    m[3 * 4 + 2] = handednessScale;
    m[3 * 4 + 3] = 0.0;

    mobj.transpose();

    return mobj;
}

function fovToProjection(fov, rightHanded, zNear, zFar) {
    var DEG2RAD = Math.PI / 180.0;

    var fovPort = {
        upTan: Math.tan(fov.upDegrees * DEG2RAD),
        downTan: Math.tan(fov.downDegrees * DEG2RAD),
        leftTan: Math.tan(fov.leftDegrees * DEG2RAD),
        rightTan: Math.tan(fov.rightDegrees * DEG2RAD)
    };

    return fovPortToProjection(fovPort, rightHanded, zNear, zFar);
}

function extend(superClass) {
    var subClassMethods = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : {};

    for (var method in superClass) {
        if (superClass.hasOwnProperty(method) && !subClassMethods.hasOwnProperty(method)) {
            subClassMethods[method] = superClass[method];
        }
    }
    return subClassMethods;
}

function deepCopy(obj) {
    var to = {};

    for (var name in obj) {
        to[name] = obj[name];
    }

    return to;
}

function getTouchesDistance(touches) {
    return Math.sqrt((touches[0].clientX - touches[1].clientX) * (touches[0].clientX - touches[1].clientX) + (touches[0].clientY - touches[1].clientY) * (touches[0].clientY - touches[1].clientY));
}

exports.default = {
    whichTransitionEvent: whichTransitionEvent,
    mobileAndTabletcheck: mobileAndTabletcheck,
    isIos: isIos,
    isRealIphone: isRealIphone,
    fovToProjection: fovToProjection,
    extend: extend,
    deepCopy: deepCopy,
    getTouchesDistance: getTouchesDistance
};

},{}],12:[function(require,module,exports){
"use strict";

Object.defineProperty(exports, "__esModule", {
    value: true
});
/**
 * Created by yanwsh on 8/13/16.
 */

var VRButton = function VRButton(ButtonComponent) {
    return {
        constructor: function init(player, options) {
            ButtonComponent.call(this, player, options);
        },

        buildCSSClass: function buildCSSClass() {
            return "vjs-VR-control " + ButtonComponent.prototype.buildCSSClass.call(this);
        },

        handleClick: function handleClick() {
            var canvas = this.player().getChild("Canvas");
            !canvas.VRMode ? canvas.enableVR() : canvas.disableVR();
            canvas.VRMode ? this.addClass("enable") : this.removeClass("enable");
            canvas.VRMode ? this.player().trigger('VRModeOn') : this.player().trigger('VRModeOff');
        },

        controlText_: "VR"
    };
};

exports.default = VRButton;

},{}],13:[function(require,module,exports){
/**
 * Created by yanwsh on 4/3/16.
 */
'use strict';

Object.defineProperty(exports, "__esModule", {
    value: true
});

var _Util = require('./lib/Util');

var _Util2 = _interopRequireDefault(_Util);

var _Detector = require('./lib/Detector');

var _Detector2 = _interopRequireDefault(_Detector);

function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { default: obj }; }

var runOnMobile = typeof window !== "undefined" ? _Util2.default.mobileAndTabletcheck() : false;

// Default options for the plugin.
var defaults = {
    clickAndDrag: runOnMobile,
    showNotice: true,
    NoticeMessage: "Please use your mouse drag and drop the video.",
    autoHideNotice: 3000,
    //limit the video size when user scroll.
    scrollable: true,
    initFov: 75,
    maxFov: 105,
    minFov: 51,
    //initial position for the video
    initLat: 0,
    initLon: -180,
    //A float value back to center when mouse out the canvas. The higher, the faster.
    returnStepLat: 0.5,
    returnStepLon: 2,
    backToVerticalCenter: !runOnMobile,
    backToHorizonCenter: !runOnMobile,
    clickToToggle: false,

    //limit viewable zoom
    minLat: -85,
    maxLat: 85,

    minLon: -Infinity,
    maxLon: Infinity,

    videoType: "equirectangular",

    rotateX: 0,
    rotateY: 0,
    rotateZ: 0,

    autoMobileOrientation: false,
    mobileVibrationValue: runOnMobile && _Util2.default.isIos() ? 0.022 : 1,

    VREnable: true,
    VRGapDegree: 2.5,

    closePanorama: false,

    helperCanvas: {},

    dualFish: {
        width: 1920,
        height: 1080,
        circle1: {
            x: 0.240625,
            y: 0.553704,
            rx: 0.23333,
            ry: 0.43148,
            coverX: 0.913,
            coverY: 0.9
        },
        circle2: {
            x: 0.757292,
            y: 0.553704,
            rx: 0.232292,
            ry: 0.4296296,
            coverX: 0.913,
            coverY: 0.9308
        }
    }
};

function playerResize(player) {
    var canvas = player.getChild('Canvas');
    return function () {
        player.el().style.width = window.innerWidth + "px";
        player.el().style.height = window.innerHeight + "px";
        canvas.handleResize();
    };
}

function fullscreenOnIOS(player, clickFn) {
    var resizeFn = playerResize(player);
    player.controlBar.fullscreenToggle.off("tap", clickFn);
    player.controlBar.fullscreenToggle.on("tap", function fullscreen() {
        var canvas = player.getChild('Canvas');
        if (!player.isFullscreen()) {
            //set to fullscreen
            player.isFullscreen(true);
            player.enterFullWindow();
            resizeFn();
            window.addEventListener("devicemotion", resizeFn);
        } else {
            player.isFullscreen(false);
            player.exitFullWindow();
            player.el().style.width = "";
            player.el().style.height = "";
            canvas.handleResize();
            window.removeEventListener("devicemotion", resizeFn);
        }
    });
}

/**
 * Function to invoke when the player is ready.
 *
 * This is a great place for your plugin to initialize itself. When this
 * function is called, the player will have its DOM and child components
 * in place.
 *
 * @function onPlayerReady
 * @param    {Player} player
 * @param    {Object} [options={}]
 */
var onPlayerReady = function onPlayerReady(player, options, settings) {
    player.addClass('vjs-panorama');
    if (!_Detector2.default.webgl) {
        PopupNotification(player, {
            NoticeMessage: _Detector2.default.getWebGLErrorMessage(),
            autoHideNotice: options.autoHideNotice
        });
        if (options.callback) {
            options.callback();
        }
        return;
    }
    player.addChild('Canvas', _Util2.default.deepCopy(options));
    var canvas = player.getChild('Canvas');
    if (runOnMobile) {
        var videoElement = settings.getTech(player);
        if (_Util2.default.isRealIphone()) {
            var makeVideoPlayableInline = require('iphone-inline-video');
            //ios 10 support play video inline
            videoElement.setAttribute("playsinline", "");
            makeVideoPlayableInline(videoElement, true);
        }
        if (_Util2.default.isIos()) {
            fullscreenOnIOS(player, settings.getFullscreenToggleClickFn(player));
        }
        player.addClass("vjs-panorama-mobile-inline-video");
        player.removeClass("vjs-using-native-controls");
        canvas.playOnMobile();
    }
    if (options.showNotice) {
        player.on("playing", function () {
            PopupNotification(player, _Util2.default.deepCopy(options));
        });
    }
    if (options.VREnable) {
        player.controlBar.addChild('VRButton', {}, player.controlBar.children().length - 1);
    }
    canvas.hide();
    player.on("play", function () {
        canvas.show();
    });
    player.on("fullscreenchange", function () {
        canvas.handleResize();
    });
    if (options.callback) options.callback();
};

var PopupNotification = function PopupNotification(player) {
    var options = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : {
        NoticeMessage: ""
    };

    var notice = player.addChild('Notice', options);

    if (options.autoHideNotice > 0) {
        setTimeout(function () {
            notice.addClass("vjs-video-notice-fadeOut");
            var transitionEvent = _Util2.default.whichTransitionEvent();
            var hide = function hide() {
                notice.hide();
                notice.removeClass("vjs-video-notice-fadeOut");
                notice.off(transitionEvent, hide);
            };
            notice.on(transitionEvent, hide);
        }, options.autoHideNotice);
    }
};

var plugin = function plugin() {
    var settings = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : {};

    /**
     * A video.js plugin.
     *
     * In the plugin function, the value of `this` is a video.js `Player`
     * instance. You cannot rely on the player being in a "ready" state here,
     * depending on how the plugin is invoked. This may or may not be important
     * to you; if not, remove the wait for "ready"!
     *
     * @function panorama
     * @param    {Object} [options={}]
     *           An object of options left to the plugin author to define.
     */
    var videoTypes = ["equirectangular", "fisheye", "3dVideo", "dual_fisheye"];
    var panorama = function panorama(options) {
        var _this = this;

        if (settings.mergeOption) options = settings.mergeOption(defaults, options);
        if (typeof settings._init === "undefined" || typeof settings._init !== "function") {
            console.error("plugin must implement init function().");
            return;
        }
        if (videoTypes.indexOf(options.videoType) == -1) options.videoType = defaults.videoType;
        settings._init(options);
        /* implement callback function when videojs is ready */
        this.ready(function () {
            onPlayerReady(_this, options, settings);
        });
    };

    // Include the version number.
    panorama.VERSION = '0.1.7';

    return panorama;
};

exports.default = plugin;

},{"./lib/Detector":6,"./lib/Util":11,"iphone-inline-video":2}],14:[function(require,module,exports){
'use strict';

var _Canvas = require('./lib/Canvas');

var _Canvas2 = _interopRequireDefault(_Canvas);

var _ThreeCanvas = require('./lib/ThreeCanvas');

var _ThreeCanvas2 = _interopRequireDefault(_ThreeCanvas);

var _Notice = require('./lib/Notice');

var _Notice2 = _interopRequireDefault(_Notice);

var _HelperCanvas = require('./lib/HelperCanvas');

var _HelperCanvas2 = _interopRequireDefault(_HelperCanvas);

var _VRButton = require('./lib/VRButton');

var _VRButton2 = _interopRequireDefault(_VRButton);

var _plugin = require('./plugin');

var _plugin2 = _interopRequireDefault(_plugin);

function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { default: obj }; }

function getTech(player) {
    return player.tech ? player.tech.el() : player.h.el();
}

function getFullscreenToggleClickFn(player) {
    return player.controlBar.fullscreenToggle.onClick || player.controlBar.fullscreenToggle.u;
}

var component = videojs.Component;
var compatiableInitialFunction = function compatiableInitialFunction(player, options) {
    this.constructor(player, options);
};

var notice = (0, _Notice2.default)(component);
notice.init = compatiableInitialFunction;
videojs.Notice = component.extend(notice);

var helperCanvas = (0, _HelperCanvas2.default)(component);
helperCanvas.init = compatiableInitialFunction;
videojs.HelperCanvas = component.extend(helperCanvas);

var button = videojs.Button;
var vrBtn = (0, _VRButton2.default)(button);
vrBtn.init = compatiableInitialFunction;
vrBtn.onClick = vrBtn.u = vrBtn.handleClick;
vrBtn.buttonText = vrBtn.ta = vrBtn.controlText_;
vrBtn.T = function () {
    return 'vjs-VR-control ' + button.prototype.T.call(this);
};
videojs.VRButton = button.extend(vrBtn);

// Register the plugin with video.js.
videojs.plugin('panorama', (0, _plugin2.default)({
    _init: function _init(options) {
        var canvas = options.videoType !== "3dVideo" ? (0, _Canvas2.default)(component, window.THREE, {
            getTech: getTech
        }) : (0, _ThreeCanvas2.default)(component, window.THREE, {
            getTech: getTech
        });
        canvas.init = compatiableInitialFunction;
        videojs.Canvas = component.extend(canvas);
    },
    mergeOption: function mergeOption(defaults, options) {
        return videojs.util.mergeOptions(defaults, options);
    },
    getTech: getTech,
    getFullscreenToggleClickFn: getFullscreenToggleClickFn
}));

},{"./lib/Canvas":5,"./lib/HelperCanvas":7,"./lib/Notice":9,"./lib/ThreeCanvas":10,"./lib/VRButton":12,"./plugin":13}]},{},[14])
//# sourceMappingURL=data:application/json;charset=utf-8;base64,eyJ2ZXJzaW9uIjozLCJzb3VyY2VzIjpbIm5vZGVfbW9kdWxlcy9icm93c2VyLXBhY2svX3ByZWx1ZGUuanMiLCJub2RlX21vZHVsZXMvaW50ZXJ2YWxvbWV0ZXIvZGlzdC9pbnRlcnZhbG9tZXRlci5jb21tb24tanMuanMiLCJub2RlX21vZHVsZXMvaXBob25lLWlubGluZS12aWRlby9kaXN0L2lwaG9uZS1pbmxpbmUtdmlkZW8uY29tbW9uLWpzLmpzIiwibm9kZV9tb2R1bGVzL3Bvb3ItbWFucy1zeW1ib2wvZGlzdC9wb29yLW1hbnMtc3ltYm9sLmNvbW1vbi1qcy5qcyIsInNyYy9zY3JpcHRzL2xpYi9CYXNlQ2FudmFzLmpzIiwic3JjL3NjcmlwdHMvbGliL0NhbnZhcy5qcyIsInNyYy9zY3JpcHRzL2xpYi9EZXRlY3Rvci5qcyIsInNyYy9zY3JpcHRzL2xpYi9IZWxwZXJDYW52YXMuanMiLCJzcmMvc2NyaXB0cy9saWIvTW9iaWxlQnVmZmVyaW5nLmpzIiwic3JjL3NjcmlwdHMvbGliL05vdGljZS5qcyIsInNyYy9zY3JpcHRzL2xpYi9UaHJlZUNhbnZhcy5qcyIsInNyYy9zY3JpcHRzL2xpYi9VdGlsLmpzIiwic3JjL3NjcmlwdHMvbGliL1ZSQnV0dG9uLmpzIiwic3JjL3NjcmlwdHMvcGx1Z2luLmpzIiwic3JjL3NjcmlwdHMvcGx1Z2luX3Y0LmpzIl0sIm5hbWVzIjpbXSwibWFwcGluZ3MiOiJBQUFBO0FDQUE7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBOztBQ3pDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTs7QUN2VUE7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7O0FDTkE7Ozs7Ozs7O0FBUUE7Ozs7OztBQUVBOzs7O0FBQ0E7Ozs7QUFDQTs7Ozs7O0FBRUEsSUFBTSxvQkFBb0IsQ0FBMUI7O0FBRUEsSUFBSSxhQUFhLFNBQWIsVUFBYSxDQUFVLGFBQVYsRUFBeUIsS0FBekIsRUFBK0M7QUFBQSxRQUFmLFFBQWUsdUVBQUosRUFBSTs7QUFDNUQsV0FBTztBQUNILHFCQUFhLFNBQVMsSUFBVCxD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Q3RDLGVBQU8sUUFBUSxJQUFSLENBQWEsWUFBYixDQUEwQixRQUExQixFQUFvQyxPQUFwQyxDQUFQO0FBQ0gsS0FkK0I7QUFlaEMsYUFBUyxPQWZ1QjtBQWdCaEMsZ0NBQTRCO0FBaEJJLENBQVQsQ0FBM0IiLCJmaWxlIjoiZ2VuZXJhdGVkLmpzIiwic291cmNlUm9vdCI6IiIsInNvdXJjZXNDb250ZW50IjpbIihmdW5jdGlvbiBlKHQsbixyKXtmdW5jdGlvbiBzKG8sdSl7aWYoIW5bb10pe2lmKCF0W29dKXt2YXIgYT10eXBlb2YgcmVxdWlyZT09XCJmdW5jdGlvblwiJiZyZXF1aXJlO2lmKCF1JiZhKXJldHVybiBhKG8sITApO2lmKGkpcmV0dXJuIGkobywhMCk7dmFyIGY9bmV3IEVycm9yKFwiQ2Fubm90IGZpbmQgbW9kdWxlICdcIitvK1wiJ1wiKTt0aHJvdyBmLmNvZGU9XCJNT0RVTEVfTk9UX0ZPVU5EXCIsZn12YXIgbD1uW29dPXtleHBvcnRzOnt9fTt0W29dWzBdLmNhbGwobC5leHBvcnRzLGZ1bmN0aW9uKGUpe3ZhciBuPXRbb11bMV1bZV07cmV0dXJuIHMobj9uOmUpfSxsLGwuZXhwb3J0cyxlLHQsbixyKX1yZXR1cm4gbltvXS5leHBvcnRzfXZhciBpPXR5cGVvZiByZXF1aXJlPT1cImZ1bmN0aW9uXCImJnJlcXVpcmU7Zm9yKHZhciBvPTA7bzxyLmxlbmd0aDtvKyspcyhyW29dKTtyZXR1cm4gc30pIiwiLyohIG5wbS5pbS9pbnRlcnZhbG9tZXRlciAqL1xuJ3VzZSBzdHJpY3QnO1xuXG5PYmplY3QuZGVmaW5lUHJvcGVydHkoZXhwb3J0cywgJ19fZXNNb2R1bGUnLCB7IHZhbHVlOiB0cnVlIH0pO1xuXG5mdW5jdGlvbiBpbnRlcnZhbG9tZXRlcihjYiwgcmVxdWVzdCwgY2FuY2VsLCByZXF1ZXN0UGFyYW1ldGVyKSB7XG5cdHZhciByZXF1ZXN0SWQ7XG5cdHZhciBwcmV2aW91c0xvb3BUaW1lO1xuXHRmdW5jdGlvbiBsb29wKG5vdykge1xuXHRcdC8vIG11c3QgYmUgcmVxdWVzdGVkIGJlZm9yZSBjYigpIGJlY2F1c2UgdGhhdCBtaWdodCBjYWxsIC5zdG9wKClcblx0XHRyZXF1ZXN0SWQgPSByZXF1ZXN0KGxvb3AsIHJlcXVlc3RQYXJhbWV0ZXIpO1xuXG5cdFx0Ly8gY2FsbGVkIHdpdGggXCJtcyBzaW5jZSBsYXN0IGNhbGxcIi4gMCBvbiBzdGFydCgpXG5cdFx0Y2Iobm93IC0gKHByZXZpb3VzTG9vcFRpbWUgfHwgbm93KSk7XG5cblx0XHRwcmV2aW91c0xvb3BUaW1lID0gbm93O1xuXHR9XG5cdHJldHVybiB7XG5cdFx0c3RhcnQ6IGZ1bmN0aW9uIHN0YXJ0KCkge1xuXHRcdFx0aWYgKCFyZXF1ZXN0SWQpIHsgLy8gcHJldmVudCBkb3VibGUgc3RhcnRzXG5cdFx0XHRcdGxvb3AoMCk7XG5cdFx0XHR9XG5cdFx0fSxcblx0XHRzdG9wOiBmdW5jdGlvbiBzdG9wKCkge1xuXHRcdFx0Y2FuY2VsKHJlcXVlc3RJZCk7XG5cdFx0XHRyZXF1ZXN0SWQgPSBudWxsO1xuXHRcdFx0cHJldmlvdXNMb29wVGltZSA9IDA7XG5cdFx0fVxuXHR9O1xufVxuXG5mdW5jdGlvbiBmcmFtZUludGVydmFsb21ldGVyKGNiKSB7XG5cdHJldHVybiBpbnRlcnZhbG9tZXRlcihjYiwgcmVxdWVzdEFuaW1hdGlvbkZyYW1lLCBjYW5jZWxBbmltYXRpb25GcmFtZSk7XG59XG5cbmZ1bmN0aW9uIHRpbWVySW50ZXJ2YWxvbWV0ZXIoY2IsIGRlbGF5KSB7XG5cdHJldHVybiBpbnRlcnZhbG9tZXRlcihjYiwgc2V0VGltZW91dCwgY2xlYXJUaW1lb3V0LCBkZWxheSk7XG59XG5cbmV4cG9ydHMuaW50ZXJ2YWxvbWV0ZXIgPSBpbnRlcnZhbG9tZXRlcjtcbmV4cG9ydHMuZnJhbWVJbnRlcnZhbG9tZXRlciA9IGZyYW1lSW50ZXJ2YWxvbWV0ZXI7XG5leHBvcnRzLnRpbWVySW50ZXJ2YWxvbWV0ZXIgPSB0aW1lckludGVydmFsb21ldGVyOyIsIi8qISBucG0uaW0vaXBob25lLWlubGluZS12aWRlbyAqL1xuJ3VzZSBzdHJpY3QnO1xuXG5mdW5jdGlvbiBfaW50ZXJvcERlZmF1bHQgKGV4KSB7IHJldHVybiAoZXggJiYgKHR5cGVvZiBleCA9PT0gJ29iamVjdCcpICYmICdkZWZhdWx0JyBpbiBleCkgPyBleFsnZGVmYXVsdCddIDogZXg7IH1cblxudmFyIFN5bWJvbCA9IF9pbnRlcm9wRGVmYXVsdChyZXF1aXJlKCdwb29yLW1hbnMtc3ltYm9sJykpO1xudmFyIGludGVydmFsb21ldGVyID0gcmVxdWlyZSgnaW50ZXJ2YWxvbWV0ZXInKTtcblxuZnVuY3Rpb24gcHJldmVudEV2ZW50KGVsZW1lbnQsIGV2ZW50TmFtZSwgdG9nZ2xlUHJvcGVydHksIHByZXZlbnRXaXRoUHJvcGVydHkpIHtcblx0ZnVuY3Rpb24gaGFuZGxlcihlKSB7XG5cdFx0aWYgKEJvb2xlYW4oZWxlbWVudFt0b2dnbGVQcm9wZXJ0eV0pID09PSBCb29sZWFuKHByZXZlbnRXaXRoUHJvcGVydHkpKSB7XG5cdFx0XHRlLnN0b3BJbW1lZGlhdGVQcm9wYWdhdGlvbigpO1xuXHRcdFx0Ly8gY29uc29sZS5sb2coZXZlbnROYW1lLCAncHJldmVudGVkIG9uJywgZWxlbWVudCk7XG5cdFx0fVxuXHRcdGRlbGV0ZSBlbGVtZW50W3RvZ2dsZVByb3BlcnR5XTtcblx0fVxuXHRlbGVtZW50LmFkZEV2ZW50TGlzdGVuZXIoZXZlbnROYW1lLCBoYW5kbGVyLCBmYWxzZSk7XG5cblx0Ly8gUmV0dXJuIGhhbmRsZXIgdG8gYWxsb3cgdG8gZGlzYWJsZSB0aGUgcHJldmVudGlvbi4gVXNhZ2U6XG5cdC8vIGNvbnN0IHByZXZlbnRpb25IYW5kbGVyID0gcHJldmVudEV2ZW50KGVsLCAnY2xpY2snKTtcblx0Ly8gZWwucmVtb3ZlRXZlbnRIYW5kbGVyKCdjbGljaycsIHByZXZlbnRpb25IYW5kbGVyKTtcblx0cmV0dXJuIGhhbmRsZXI7XG59XG5cbmZ1bmN0aW9uIHByb3h5UHJvcGVydHkob2JqZWN0LCBwcm9wZXJ0eU5hbWUsIHNvdXJjZU9iamVjdCwgY29weUZpcnN0KSB7XG5cdGZ1bmN0aW9uIGdldCgpIHtcblx0XHRyZXR1cm4gc291cmNlT2JqZWN0W3Byb3BlcnR5TmFtZV07XG5cdH1cblx0ZnVuY3Rpb24gc2V0KHZhbHVlKSB7XG5cdFx0c291cmNlT2JqZWN0W3Byb3BlcnR5TmFtZV0gPSB2YWx1ZTtcblx0fVxuXG5cdGlmIChjb3B5Rmlyc3QpIHtcblx0XHRzZXQob2JqZWN0W3Byb3BlcnR5TmFtZV0pO1xuXHR9XG5cblx0T2JqZWN0LmRlZmluZVByb3BlcnR5KG9iamVjdCwgcHJvcGVydHlOYW1lLCB7Z2V0OiBnZXQsIHNldDogc2V0fSk7XG59XG5cbmZ1bmN0aW9uIHByb3h5RXZlbnQob2JqZWN0LCBldmVudE5hbWUsIHNvdXJjZU9iamVjdCkge1xuXHRzb3VyY2VPYmplY3QuYWRkRXZlbnRMaXN0ZW5lcihldmVudE5hbWUsIGZ1bmN0aW9uICgpIHsgcmV0dXJuIG9iamVjdC5kaXNwYXRjaEV2ZW50KG5ldyBFdmVudChldmVudE5hbWUpKTsgfSk7XG59XG5cbmZ1bmN0aW9uIGRpc3BhdGNoRXZlbnRBc3luYyhlbGVtZW50LCB0eXBlKSB7XG5cdFByb21pc2UucmVzb2x2ZSgpLnRoZW4oZnVuY3Rpb24gKCkge1xuXHRcdGVsZW1lbnQuZGlzcGF0Y2hFdmVudChuZXcgRXZlbnQodHlwZSkpO1xuXHR9KTtcbn1cblxuLy8gaU9TIDEwIGFkZHMgc3VwcG9ydCBmb3IgbmF0aXZlIGlubGluZSBwbGF5YmFjayArIHNpbGVudCBhdXRvcGxheVxudmFyIGlzV2hpdGVsaXN0ZWQgPSAnb2JqZWN0LWZpdCcgaW4gZG9jdW1lbnQuaGVhZC5zdHlsZSAmJiAvaVBob25lfGlQb2QvaS50ZXN0KG5hdmlnYXRvci51c2VyQWdlbnQpICYmICFtYXRjaE1lZGlhKCcoLXdlYmtpdC12aWRlby1wbGF5YWJsZS1pbmxpbmUpJykubWF0Y2hlcztcblxudmFyIOCyoCA9IFN5bWJvbCgpO1xudmFyIOCyoGV2ZW50ID0gU3ltYm9sKCk7XG52YXIg4LKgcGxheSA9IFN5bWJvbCgnbmF0aXZlcGxheScpO1xudmFyIOCyoHBhdXNlID0gU3ltYm9sKCduYXRpdmVwYXVzZScpO1xuXG4vKipcbiAqIFVUSUxTXG4gKi9cblxuZnVuY3Rpb24gZ2V0QXVkaW9Gcm9tVmlkZW8odmlkZW8pIHtcblx0dmFyIGF1ZGlvID0gbmV3IEF1ZGlvKCk7XG5cdHByb3h5RXZlbnQodmlkZW8sICdwbGF5JywgYXVkaW8pO1xuXHRwcm94eUV2ZW50KHZpZGVvLCAncGxheWluZycsIGF1ZGlvKTtcblx0cHJveHlFdmVudCh2aWRlbywgJ3BhdXNlJywgYXVkaW8pO1xuXHRhdWRpby5jcm9zc09yaWdpbiA9IHZpZGVvLmNyb3NzT3JpZ2luO1xuXG5cdC8vICdkYXRhOicgY2F1c2VzIGF1ZGlvLm5ldHdvcmtTdGF0ZSA+IDBcblx0Ly8gd2hpY2ggdGhlbiBhbGxvd3MgdG8ga2VlcCA8YXVkaW8+IGluIGEgcmVzdW1hYmxlIHBsYXlpbmcgc3RhdGVcblx0Ly8gaS5lLiBvbmNlIHlvdSBzZXQgYSByZWFsIHNyYyBpdCB3aWxsIGtlZXAgcGxheWluZyBpZiBpdCB3YXMgaWYgLnBsYXkoKSB3YXMgY2FsbGVkXG5cdGF1ZGlvLnNyYyA9IHZpZGVvLnNyYyB8fCB2aWRlby5jdXJyZW50U3JjIHx8ICdkYXRhOic7XG5cblx0Ly8gaWYgKGF1ZGlvLnNyYyA9PT0gJ2RhdGE6Jykge1xuXHQvLyAgIFRPRE86IHdhaXQgZm9yIHZpZGVvIHRvIGJlIHNlbGVjdGVkXG5cdC8vIH1cblx0cmV0dXJuIGF1ZGlvO1xufVxuXG52YXIgbGFzdFJlcXVlc3RzID0gW107XG52YXIgcmVxdWVzdEluZGV4ID0gMDtcbnZhciBsYXN0VGltZXVwZGF0ZUV2ZW50O1xuXG5mdW5jdGlvbiBzZXRUaW1lKHZpZGVvLCB0aW1lLCByZW1lbWJlck9ubHkpIHtcblx0Ly8gYWxsb3cgb25lIHRpbWV1cGRhdGUgZXZlbnQgZXZlcnkgMjAwKyBtc1xuXHRpZiAoKGxhc3RUaW1ldXBkYXRlRXZlbnQgfHwgMCkgKyAyMDAgPCBEYXRlLm5vdygpKSB7XG5cdFx0dmlkZW9b4LKgZXZlbnRdID0gdHJ1ZTtcblx0XHRsYXN0VGltZXVwZGF0ZUV2ZW50ID0gRGF0ZS5ub3coKTtcblx0fVxuXHRpZiAoIXJlbWVtYmVyT25seSkge1xuXHRcdHZpZGVvLmN1cnJlbnRUaW1lID0gdGltZTtcblx0fVxuXHRsYXN0UmVxdWVzdHNbKytyZXF1ZXN0SW5kZXggJSAzXSA9IHRpbWUgKiAxMDAgfCAwIC8gMTAwO1xufVxuXG5mdW5jdGlvbiBpc1BsYXllckVuZGVkKHBsYXllcikge1xuXHRyZXR1cm4gcGxheWVyLmRyaXZlci5jdXJyZW50VGltZSA+PSBwbGF5ZXIudmlkZW8uZHVyYXRpb247XG59XG5cbmZ1bmN0aW9uIHVwZGF0ZSh0aW1lRGlmZikge1xuXHR2YXIgcGxheWVyID0gdGhpcztcblx0Ly8gY29uc29sZS5sb2coJ3VwZGF0ZScsIHBsYXllci52aWRlby5yZWFkeVN0YXRlLCBwbGF5ZXIudmlkZW8ubmV0d29ya1N0YXRlLCBwbGF5ZXIuZHJpdmVyLnJlYWR5U3RhdGUsIHBsYXllci5kcml2ZXIubmV0d29ya1N0YXRlLCBwbGF5ZXIuZHJpdmVyLnBhdXNlZCk7XG5cdGlmIChwbGF5ZXIudmlkZW8ucmVhZHlTdGF0ZSA+PSBwbGF5ZXIudmlkZW8uSEFWRV9GVVRVUkVfREFUQSkge1xuXHRcdGlmICghcGxheWVyLmhhc0F1ZGlvKSB7XG5cdFx0XHRwbGF5ZXIuZHJpdmVyLmN1cnJlbnRUaW1lID0gcGxheWVyLnZpZGVvLmN1cnJlbnRUaW1lICsgKCh0aW1lRGlmZiAqIHBsYXllci52aWRlby5wbGF5YmFja1JhdGUpIC8gMTAwMCk7XG5cdFx0XHRpZiAocGxheWVyLnZpZGVvLmxvb3AgJiYgaXNQbGF5ZXJFbmRlZChwbGF5ZXIpKSB7XG5cdFx0XHRcdHBsYXllci5kcml2ZXIuY3VycmVudFRpbWUgPSAwO1xuXHRcdFx0fVxuXHRcdH1cblx0XHRzZXRUaW1lKHBsYXllci52aWRlbywgcGxheWVyLmRyaXZlci5jdXJyZW50VGltZSk7XG5cdH0gZWxzZSBpZiAocGxheWVyLnZpZGVvLm5ldHdvcmtTdGF0ZSA9PT0gcGxheWVyLnZpZGVvLk5FVFdPUktfSURMRSAmJiAhcGxheWVyLnZpZGVvLmJ1ZmZlcmVkLmxlbmd0aCkge1xuXHRcdC8vIHRoaXMgc2hvdWxkIGhhcHBlbiB3aGVuIHRoZSBzb3VyY2UgaXMgYXZhaWxhYmxlIGJ1dDpcblx0XHQvLyAtIGl0J3MgcG90ZW50aWFsbHkgcGxheWluZyAoLnBhdXNlZCA9PT0gZmFsc2UpXG5cdFx0Ly8gLSBpdCdzIG5vdCByZWFkeSB0byBwbGF5XG5cdFx0Ly8gLSBpdCdzIG5vdCBsb2FkaW5nXG5cdFx0Ly8gSWYgaXQgaGFzQXVkaW8sIHRoYXQgd2lsbCBiZSBsb2FkZWQgaW4gdGhlICdlbXB0aWVkJyBoYW5kbGVyIGJlbG93XG5cdFx0cGxheWVyLnZpZGVvLmxvYWQoKTtcblx0XHQvLyBjb25zb2xlLmxvZygnV2lsbCBsb2FkJyk7XG5cdH1cblxuXHQvLyBjb25zb2xlLmFzc2VydChwbGF5ZXIudmlkZW8uY3VycmVudFRpbWUgPT09IHBsYXllci5kcml2ZXIuY3VycmVudFRpbWUsICdWaWRlbyBub3QgdXBkYXRpbmchJyk7XG5cblx0aWYgKHBsYXllci52aWRlby5lbmRlZCkge1xuXHRcdGRlbGV0ZSBwbGF5ZXIudmlkZW9b4LKgZXZlbnRdOyAvLyBhbGxvdyB0aW1ldXBkYXRlIGV2ZW50XG5cdFx0cGxheWVyLnZpZGVvLnBhdXNlKHRydWUpO1xuXHR9XG59XG5cbi8qKlxuICogTUVUSE9EU1xuICovXG5cbmZ1bmN0aW9uIHBsYXkoKSB7XG5cdC8vIGNvbnNvbGUubG9nKCdwbGF5Jyk7XG5cdHZhciB2aWRlbyA9IHRoaXM7XG5cdHZhciBwbGF5ZXIgPSB2aWRlb1vgsqBdO1xuXG5cdC8vIGlmIGl0J3MgZnVsbHNjcmVlbiwgdXNlIHRoZSBuYXRpdmUgcGxheWVyXG5cdGlmICh2aWRlby53ZWJraXREaXNwbGF5aW5nRnVsbHNjcmVlbikge1xuXHRcdHZpZGVvW+CyoHBsYXldKCk7XG5cdFx0cmV0dXJuO1xuXHR9XG5cblx0aWYgKHBsYXllci5kcml2ZXIuc3JjICE9PSAnZGF0YTonICYmIHBsYXllci5kcml2ZXIuc3JjICE9PSB2aWRlby5zcmMpIHtcblx0XHQvLyBjb25zb2xlLmxvZygnc3JjIGNoYW5nZWQgb24gcGxheScsIHZpZGVvLnNyYyk7XG5cdFx0c2V0VGltZSh2aWRlbywgMCwgdHJ1ZSk7XG5cdFx0cGxheWVyLmRyaXZlci5zcmMgPSB2aWRlby5zcmM7XG5cdH1cblxuXHRpZiAoIXZpZGVvLnBhdXNlZCkge1xuXHRcdHJldHVybjtcblx0fVxuXHRwbGF5ZXIucGF1c2VkID0gZmFsc2U7XG5cblx0aWYgKCF2aWRlby5idWZmZXJlZC5sZW5ndGgpIHtcblx0XHQvLyAubG9hZCgpIGNhdXNlcyB0aGUgZW1wdGllZCBldmVudFxuXHRcdC8vIHRoZSBhbHRlcm5hdGl2ZSBpcyAucGxheSgpKy5wYXVzZSgpIGJ1dCB0aGF0IHRyaWdnZXJzIHBsYXkvcGF1c2UgZXZlbnRzLCBldmVuIHdvcnNlXG5cdFx0Ly8gcG9zc2libHkgdGhlIGFsdGVybmF0aXZlIGlzIHByZXZlbnRpbmcgdGhpcyBldmVudCBvbmx5IG9uY2Vcblx0XHR2aWRlby5sb2FkKCk7XG5cdH1cblxuXHRwbGF5ZXIuZHJpdmVyLnBsYXkoKTtcblx0cGxheWVyLnVwZGF0ZXIuc3RhcnQoKTtcblxuXHRpZiAoIXBsYXllci5oYXNBdWRpbykge1xuXHRcdGRpc3BhdGNoRXZlbnRBc3luYyh2aWRlbywgJ3BsYXknKTtcblx0XHRpZiAocGxheWVyLnZpZGVvLnJlYWR5U3RhdGUgPj0gcGxheWVyLnZpZGVvLkhBVkVfRU5PVUdIX0RBVEEpIHtcblx0XHRcdC8vIGNvbnNvbGUubG9nKCdvbnBsYXknKTtcblx0XHRcdGRpc3BhdGNoRXZlbnRBc3luYyh2aWRlbywgJ3BsYXlpbmcnKTtcblx0XHR9XG5cdH1cbn1cbmZ1bmN0aW9uIHBhdXNlKGZvcmNlRXZlbnRzKSB7XG5cdC8vIGNvbnNvbGUubG9nKCdwYXVzZScpO1xuXHR2YXIgdmlkZW8gPSB0aGlzO1xuXHR2YXIgcGxheWVyID0gdmlkZW9b4LKgXTtcblxuXHRwbGF5ZXIuZHJpdmVyLnBhdXNlKCk7XG5cdHBsYXllci51cGRhdGVyLnN0b3AoKTtcblxuXHQvLyBpZiBpdCdzIGZ1bGxzY3JlZW4sIHRoZSBkZXZlbG9wZXIgdGhlIG5hdGl2ZSBwbGF5ZXIucGF1c2UoKVxuXHQvLyBUaGlzIGlzIGF0IHRoZSBlbmQgb2YgcGF1c2UoKSBiZWNhdXNlIGl0IGFsc29cblx0Ly8gbmVlZHMgdG8gbWFrZSBzdXJlIHRoYXQgdGhlIHNpbXVsYXRpb24gaXMgcGF1c2VkXG5cdGlmICh2aWRlby53ZWJraXREaXNwbGF5aW5nRnVsbHNjcmVlbikge1xuXHRcdHZpZGVvW+CyoHBhdXNlXSgpO1xuXHR9XG5cblx0aWYgKHBsYXllci5wYXVzZWQgJiYgIWZvcmNlRXZlbnRzKSB7XG5cdFx0cmV0dXJuO1xuXHR9XG5cblx0cGxheWVyLnBhdXNlZCA9IHRydWU7XG5cdGlmICghcGxheWVyLmhhc0F1ZGlvKSB7XG5cdFx0ZGlzcGF0Y2hFdmVudEFzeW5jKHZpZGVvLCAncGF1c2UnKTtcblx0fVxuXHRpZiAodmlkZW8uZW5kZWQpIHtcblx0XHR2aWRlb1vgsqBldmVudF0gPSB0cnVlO1xuXHRcdGRpc3BhdGNoRXZlbnRBc3luYyh2aWRlbywgJ2VuZGVkJyk7XG5cdH1cbn1cblxuLyoqXG4gKiBTRVRVUFxuICovXG5cbmZ1bmN0aW9uIGFkZFBsYXllcih2aWRlbywgaGFzQXVkaW8pIHtcblx0dmFyIHBsYXllciA9IHZpZGVvW+CyoF0gPSB7fTtcblx0cGxheWVyLnBhdXNlZCA9IHRydWU7IC8vIHRyYWNrIHdoZXRoZXIgJ3BhdXNlJyBldmVudHMgaGF2ZSBiZWVuIGZpcmVkXG5cdHBsYXllci5oYXNBdWRpbyA9IGhhc0F1ZGlvO1xuXHRwbGF5ZXIudmlkZW8gPSB2aWRlbztcblx0cGxheWVyLnVwZGF0ZXIgPSBpbnRlcnZhbG9tZXRlci5mcmFtZUludGVydmFsb21ldGVyKHVwZGF0ZS5iaW5kKHBsYXllcikpO1xuXG5cdGlmIChoYXNBdWRpbykge1xuXHRcdHBsYXllci5kcml2ZXIgPSBnZXRBdWRpb0Zyb21WaWRlbyh2aWRlbyk7XG5cdH0gZWxzZSB7XG5cdFx0dmlkZW8uYWRkRXZlbnRMaXN0ZW5lcignY2FucGxheScsIGZ1bmN0aW9uICgpIHtcblx0XHRcdGlmICghdmlkZW8ucGF1c2VkKSB7XG5cdFx0XHRcdC8vIGNvbnNvbGUubG9nKCdvbmNhbnBsYXknKTtcblx0XHRcdFx0ZGlzcGF0Y2hFdmVudEFzeW5jKHZpZGVvLCAncGxheWluZycpO1xuXHRcdFx0fVxuXHRcdH0pO1xuXHRcdHBsYXllci5kcml2ZXIgPSB7XG5cdFx0XHRzcmM6IHZpZGVvLnNyYyB8fCB2aWRlby5jdXJyZW50U3JjIHx8ICdkYXRhOicsXG5cdFx0XHRtdXRlZDogdHJ1ZSxcblx0XHRcdHBhdXNlZDogdHJ1ZSxcblx0XHRcdHBhdXNlOiBmdW5jdGlvbiAoKSB7XG5cdFx0XHRcdHBsYXllci5kcml2ZXIucGF1c2VkID0gdHJ1ZTtcblx0XHRcdH0sXG5cdFx0XHRwbGF5OiBmdW5jdGlvbiAoKSB7XG5cdFx0XHRcdHBsYXllci5kcml2ZXIucGF1c2VkID0gZmFsc2U7XG5cdFx0XHRcdC8vIG1lZGlhIGF1dG9tYXRpY2FsbHkgZ29lcyB0byAwIGlmIC5wbGF5KCkgaXMgY2FsbGVkIHdoZW4gaXQncyBkb25lXG5cdFx0XHRcdGlmIChpc1BsYXllckVuZGVkKHBsYXllcikpIHtcblx0XHRcdFx0XHRzZXRUaW1lKHZpZGVvLCAwKTtcblx0XHRcdFx0fVxuXHRcdFx0fSxcblx0XHRcdGdldCBlbmRlZCgpIHtcblx0XHRcdFx0cmV0dXJuIGlzUGxheWVyRW5kZWQocGxheWVyKTtcblx0XHRcdH1cblx0XHR9O1xuXHR9XG5cblx0Ly8gLmxvYWQoKSBjYXVzZXMgdGhlIGVtcHRpZWQgZXZlbnRcblx0dmlkZW8uYWRkRXZlbnRMaXN0ZW5lcignZW1wdGllZCcsIGZ1bmN0aW9uICgpIHtcblx0XHQvLyBjb25zb2xlLmxvZygnZHJpdmVyIHNyYyBpcycsIHBsYXllci5kcml2ZXIuc3JjKTtcblx0XHR2YXIgd2FzRW1wdHkgPSAhcGxheWVyLmRyaXZlci5zcmMgfHwgcGxheWVyLmRyaXZlci5zcmMgPT09ICdkYXRhOic7XG5cdFx0aWYgKHBsYXllci5kcml2ZXIuc3JjICYmIHBsYXllci5kcml2ZXIuc3JjICE9PSB2aWRlby5zcmMpIHtcblx0XHRcdC8vIGNvbnNvbGUubG9nKCdzcmMgY2hhbmdlZCB0bycsIHZpZGVvLnNyYyk7XG5cdFx0XHRzZXRUaW1lKHZpZGVvLCAwLCB0cnVlKTtcblx0XHRcdHBsYXllci5kcml2ZXIuc3JjID0gdmlkZW8uc3JjO1xuXHRcdFx0Ly8gcGxheWluZyB2aWRlb3Mgd2lsbCBvbmx5IGtlZXAgcGxheWluZyBpZiBubyBzcmMgd2FzIHByZXNlbnQgd2hlbiAucGxheSgp4oCZZWRcblx0XHRcdGlmICh3YXNFbXB0eSkge1xuXHRcdFx0XHRwbGF5ZXIuZHJpdmVyLnBsYXkoKTtcblx0XHRcdH0gZWxzZSB7XG5cdFx0XHRcdHBsYXllci51cGRhdGVyLnN0b3AoKTtcblx0XHRcdH1cblx0XHR9XG5cdH0sIGZhbHNlKTtcblxuXHQvLyBzdG9wIHByb2dyYW1tYXRpYyBwbGF5ZXIgd2hlbiBPUyB0YWtlcyBvdmVyXG5cdHZpZGVvLmFkZEV2ZW50TGlzdGVuZXIoJ3dlYmtpdGJlZ2luZnVsbHNjcmVlbicsIGZ1bmN0aW9uICgpIHtcblx0XHRpZiAoIXZpZGVvLnBhdXNlZCkge1xuXHRcdFx0Ly8gbWFrZSBzdXJlIHRoYXQgdGhlIDxhdWRpbz4gYW5kIHRoZSBzeW5jZXIvdXBkYXRlciBhcmUgc3RvcHBlZFxuXHRcdFx0dmlkZW8ucGF1c2UoKTtcblxuXHRcdFx0Ly8gcGxheSB2aWRlbyBuYXRpdmVseVxuXHRcdFx0dmlkZW9b4LKgcGxheV0oKTtcblx0XHR9IGVsc2UgaWYgKGhhc0F1ZGlvICYmICFwbGF5ZXIuZHJpdmVyLmJ1ZmZlcmVkLmxlbmd0aCkge1xuXHRcdFx0Ly8gaWYgdGhlIGZpcnN0IHBsYXkgaXMgbmF0aXZlLFxuXHRcdFx0Ly8gdGhlIDxhdWRpbz4gbmVlZHMgdG8gYmUgYnVmZmVyZWQgbWFudWFsbHlcblx0XHRcdC8vIHNvIHdoZW4gdGhlIGZ1bGxzY3JlZW4gZW5kcywgaXQgY2FuIGJlIHNldCB0byB0aGUgc2FtZSBjdXJyZW50IHRpbWVcblx0XHRcdHBsYXllci5kcml2ZXIubG9hZCgpO1xuXHRcdH1cblx0fSk7XG5cdGlmIChoYXNBdWRpbykge1xuXHRcdHZpZGVvLmFkZEV2ZW50TGlzdGVuZXIoJ3dlYmtpdGVuZGZ1bGxzY3JlZW4nLCBmdW5jdGlvbiAoKSB7XG5cdFx0XHQvLyBzeW5jIGF1ZGlvIHRvIG5ldyB2aWRlbyBwb3NpdGlvblxuXHRcdFx0cGxheWVyLmRyaXZlci5jdXJyZW50VGltZSA9IHZpZGVvLmN1cnJlbnRUaW1lO1xuXHRcdFx0Ly8gY29uc29sZS5hc3NlcnQocGxheWVyLmRyaXZlci5jdXJyZW50VGltZSA9PT0gdmlkZW8uY3VycmVudFRpbWUsICdBdWRpbyBub3Qgc3luY2VkJyk7XG5cdFx0fSk7XG5cblx0XHQvLyBhbGxvdyBzZWVraW5nXG5cdFx0dmlkZW8uYWRkRXZlbnRMaXN0ZW5lcignc2Vla2luZycsIGZ1bmN0aW9uICgpIHtcblx0XHRcdGlmIChsYXN0UmVxdWVzdHMuaW5kZXhPZih2aWRlby5jdXJyZW50VGltZSAqIDEwMCB8IDAgLyAxMDApIDwgMCkge1xuXHRcdFx0XHQvLyBjb25zb2xlLmxvZygnVXNlci1yZXF1ZXN0ZWQgc2Vla2luZycpO1xuXHRcdFx0XHRwbGF5ZXIuZHJpdmVyLmN1cnJlbnRUaW1lID0gdmlkZW8uY3VycmVudFRpbWU7XG5cdFx0XHR9XG5cdFx0fSk7XG5cdH1cbn1cblxuZnVuY3Rpb24gb3ZlcmxvYWRBUEkodmlkZW8pIHtcblx0dmFyIHBsYXllciA9IHZpZGVvW+CyoF07XG5cdHZpZGVvW+CyoHBsYXldID0gdmlkZW8ucGxheTtcblx0dmlkZW9b4LKgcGF1c2VdID0gdmlkZW8ucGF1c2U7XG5cdHZpZGVvLnBsYXkgPSBwbGF5O1xuXHR2aWRlby5wYXVzZSA9IHBhdXNlO1xuXHRwcm94eVByb3BlcnR5KHZpZGVvLCAncGF1c2VkJywgcGxheWVyLmRyaXZlcik7XG5cdHByb3h5UHJvcGVydHkodmlkZW8sICdtdXRlZCcsIHBsYXllci5kcml2ZXIsIHRydWUpO1xuXHRwcm94eVByb3BlcnR5KHZpZGVvLCAncGxheWJhY2tSYXRlJywgcGxheWVyLmRyaXZlciwgdHJ1ZSk7XG5cdHByb3h5UHJvcGVydHkodmlkZW8sICdlbmRlZCcsIHBsYXllci5kcml2ZXIpO1xuXHRwcm94eVByb3BlcnR5KHZpZGVvLCAnbG9vcCcsIHBsYXllci5kcml2ZXIsIHRydWUpO1xuXHRwcmV2ZW50RXZlbnQodmlkZW8sICdzZWVraW5nJyk7XG5cdHByZXZlbnRFdmVudCh2aWRlbywgJ3NlZWtlZCcpO1xuXHRwcmV2ZW50RXZlbnQodmlkZW8sICd0aW1ldXBkYXRlJywg4LKgZXZlbnQsIGZhbHNlKTtcblx0cHJldmVudEV2ZW50KHZpZGVvLCAnZW5kZWQnLCDgsqBldmVudCwgZmFsc2UpOyAvLyBwcmV2ZW50IG9jY2FzaW9uYWwgbmF0aXZlIGVuZGVkIGV2ZW50c1xufVxuXG5mdW5jdGlvbiBlbmFibGVJbmxpbmVWaWRlbyh2aWRlbywgaGFzQXVkaW8sIG9ubHlXaGl0ZWxpc3RlZCkge1xuXHRpZiAoIGhhc0F1ZGlvID09PSB2b2lkIDAgKSBoYXNBdWRpbyA9IHRydWU7XG5cdGlmICggb25seVdoaXRlbGlzdGVkID09PSB2b2lkIDAgKSBvbmx5V2hpdGVsaXN0ZWQgPSB0cnVlO1xuXG5cdGlmICgob25seVdoaXRlbGlzdGVkICYmICFpc1doaXRlbGlzdGVkKSB8fCB2aWRlb1vgsqBdKSB7XG5cdFx0cmV0dXJuO1xuXHR9XG5cdGFkZFBsYXllcih2aWRlbywgaGFzQXVkaW8pO1xuXHRvdmVybG9hZEFQSSh2aWRlbyk7XG5cdHZpZGVvLmNsYXNzTGlzdC5hZGQoJ0lJVicpO1xuXHRpZiAoIWhhc0F1ZGlvICYmIHZpZGVvLmF1dG9wbGF5KSB7XG5cdFx0dmlkZW8ucGxheSgpO1xuXHR9XG5cdGlmICghL2lQaG9uZXxpUG9kfGlQYWQvLnRlc3QobmF2aWdhdG9yLnBsYXRmb3JtKSkge1xuXHRcdGNvbnNvbGUud2FybignaXBob25lLWlubGluZS12aWRlbyBpcyBub3QgZ3VhcmFudGVlZCB0byB3b3JrIGluIGVtdWxhdGVkIGVudmlyb25tZW50cycpO1xuXHR9XG59XG5cbmVuYWJsZUlubGluZVZpZGVvLmlzV2hpdGVsaXN0ZWQgPSBpc1doaXRlbGlzdGVkO1xuXG5tb2R1bGUuZXhwb3J0cyA9IGVuYWJsZUlubGluZVZpZGVvOyIsIid1c2Ugc3RyaWN0JztcblxudmFyIGluZGV4ID0gdHlwZW9mIFN5bWJvbCA9PT0gJ3VuZGVmaW5lZCcgPyBmdW5jdGlvbiAoZGVzY3JpcHRpb24pIHtcblx0cmV0dXJuICdAJyArIChkZXNjcmlwdGlvbiB8fCAnQCcpICsgTWF0aC5yYW5kb20oKTtcbn0gOiBTeW1ib2w7XG5cbm1vZHVsZS5leHBvcnRzID0gaW5kZXg7IiwiLyoqXG4gKlxuICogKGMpIFdlbnNoZW5nIFlhbiA8eWFud3NoQGdtYWlsLmNvbT5cbiAqIERhdGU6IDEwLzMwLzE2XG4gKlxuICogRm9yIHRoZSBmdWxsIGNvcHlyaWdodCBhbmQgbGljZW5zZSBpbmZvcm1hdGlvbiwgcGxlYXNlIHZpZXcgdGhlIExJQ0VOU0VcbiAqIGZpbGUgdGhhdCB3YXMgZGlzdHJpYnV0ZWQgd2l0aCB0aGlzIHNvdXJjZSBjb2RlLlxuICovXG4ndXNlIHN0cmljdCc7XG5cbmltcG9ydCBEZXRlY3RvciBmcm9tICcuLi9saWIvRGV0ZWN0b3InO1xuaW1wb3J0IE1vYmlsZUJ1ZmZlcmluZyBmcm9tICcuLi9saWIvTW9iaWxlQnVmZmVyaW5nJztcbmltcG9ydCBVdGlsIGZyb20gJy4uL2xpYi9VdGlsJztcblxuY29uc3QgSEFWRV9DVVJSRU5UX0RBVEEgPSAyO1xuXG52YXIgQmFzZUNhbnZhcyA9IGZ1bmN0aW9uIChiYXNlQ29tcG9uZW50LCBUSFJFRSwgc2V0dGluZ3MgPSB7fSkge1xuICAgIHJldHVybiB7XG4gICAgICAgIGNvbnN0cnVjdG9yOiBmdW5jdGlvbiBpbml0KHBsYXllciwgb3B0aW9ucyl7XG4gICAgICAgICAgICB0aGlzLnNldHRpbmdzID0gb3B0aW9ucztcbiAgICAgICAgICAgIC8vYmFzaWMgc2V0dGluZ3NcbiAgICAgICAgICAgIHRoaXMud2lkdGggPSBwbGF5ZXIuZWwoKS5vZmZzZXRXaWR0aCwgdGhpcy5oZWlnaHQgPSBwbGF5ZXIuZWwoKS5vZmZzZXRIZWlnaHQ7XG4gICAgICAgICAgICB0aGlzLmxvbiA9IG9wdGlvbnMuaW5pdExvbiwgdGhpcy5sYXQgPSBvcHRpb25zLmluaXRMYXQsIHRoaXMucGhpID0gMCwgdGhpcy50aGV0YSA9IDA7XG4gICAgICAgICAgICB0aGlzLnZpZGVvVHlwZSA9IG9wdGlvbnMudmlkZW9UeXBlO1xuICAgICAgICAgICAgdGhpcy5jbGlja1RvVG9nZ2xlID0gb3B0aW9ucy5jbGlja1RvVG9nZ2xlO1xuICAgICAgICAgICAgdGhpcy5tb3VzZURvd24gPSBmYWxzZTtcbiAgICAgICAgICAgIHRoaXMuaXNVc2VySW50ZXJhY3RpbmcgPSBmYWxzZTtcblxuICAgICAgICAgICAgLy9kZWZpbmUgcmVuZGVyXG4gICAgICAgICAgICB0aGlzLnJlbmRlcmVyID0gbmV3IFRIUkVFLldlYkdMUmVuZGVyZXIoKTtcbiAgICAgICAgICAgIHRoaXMucmVuZGVyZXIuc2V0UGl4ZWxSYXRpbyh3aW5kb3cuZGV2aWNlUGl4ZWxSYXRpbyk7XG4gICAgICAgICAgICB0aGlzLnJlbmRlcmVyLnNldFNpemUodGhpcy53aWR0aCwgdGhpcy5oZWlnaHQpO1xuICAgICAgICAgICAgdGhpcy5yZW5kZXJlci5hdXRvQ2xlYXIgPSBmYWxzZTtcbiAgICAgICAgICAgIHRoaXMucmVuZGVyZXIuc2V0Q2xlYXJDb2xvcigweDAwMDAwMCwgMSk7XG5cbiAgICAgICAgICAgIC8vZGVmaW5lIHRleHR1cmUsIG9uIGllIDExLCB3ZSBuZWVkIGFkZGl0aW9uYWwgaGVscGVyIGNhbnZhcyB0byBzb2x2ZSByZW5kZXJpbmcgaXNzdWUuXG4gICAgICAgICAgICB2YXIgdmlkZW8gPSBzZXR0aW5ncy5nZXRUZWNoKHBsYXllcik7XG4gICAgICAgICAgICB0aGlzLnN1cHBvcnRWaWRlb1RleHR1cmUgPSBEZXRlY3Rvci5zdXBwb3J0VmlkZW9UZXh0dXJlKCk7XG4gICAgICAgICAgICB0aGlzLmxpdmVTdHJlYW1PblNhZmFyaSA9IERldGVjdG9yLmlzTGl2ZVN0cmVhbU9uU2FmYXJpKHZpZGVvKTtcbiAgICAgICAgICAgIGlmKHRoaXMubGl2ZVN0cmVhbU9uU2FmYXJpKSB0aGlzLnN1cHBvcnRWaWRlb1RleHR1cmUgPSBmYWxzZTtcbiAgICAgICAgICAgIGlmKCF0aGlzLnN1cHBvcnRWaWRlb1RleHR1cmUpe1xuICAgICAgICAgICAgICAgIHRoaXMuaGVscGVyQ2FudmFzID0gcGxheWVyLmFkZENoaWxkKFwiSGVscGVyQ2FudmFzXCIsIHtcbiAgICAgICAgICAgICAgICAgICAgdmlkZW86IHZpZGVvLFxuICAgICAgICAgICAgICAgICAgICB3aWR0aDogKG9wdGlvbnMuaGVscGVyQ2FudmFzLndpZHRoKT8gb3B0aW9ucy5oZWxwZXJDYW52YXMud2lkdGg6IHRoaXMud2lkdGgsXG4gICAgICAgICAgICAgICAgICAgIGhlaWdodDogKG9wdGlvbnMuaGVscGVyQ2FudmFzLmhlaWdodCk/IG9wdGlvbnMuaGVscGVyQ2FudmFzLmhlaWdodDogdGhpcy5oZWlnaHRcbiAgICAgICAgICAgICAgICB9KTtcbiAgICAgICAgICAgICAgICB2YXIgY29udGV4dCA9IHRoaXMuaGVscGVyQ2FudmFzLmVsKCk7XG4gICAgICAgICAgICAgICAgdGhpcy50ZXh0dXJlID0gbmV3IFRIUkVFLlRleHR1cmUoY29udGV4dCk7XG4gICAgICAgICAgICB9ZWxzZXtcbiAgICAgICAgICAgICAgICB0aGlzLnRleHR1cmUgPSBuZXcgVEhSRUUuVGV4dHVyZSh2aWRlbyk7XG4gICAgICAgICAgICB9XG5cbiAgICAgICAgICAgIHZpZGVvLnN0eWxlLnZpc2liaWxpdHkgPSBcImhpZGRlblwiO1xuXG4gICAgICAgICAgICB0aGlzLnRleHR1cmUuZ2VuZXJhdGVNaXBtYXBzID0gZmFsc2U7XG4gICAgICAgICAgICB0aGlzLnRleHR1cmUubWluRmlsdGVyID0gVEhSRUUuTGluZWFyRmlsdGVyO1xuICAgICAgICAgICAgdGhpcy50ZXh0dXJlLm1heEZpbHRlciA9IFRIUkVFLkxpbmVhckZpbHRlcjtcbiAgICAgICAgICAgIHRoaXMudGV4dHVyZS5mb3JtYXQgPSBUSFJFRS5SR0JGb3JtYXQ7XG5cbiAgICAgICAgICAgIHRoaXMuZWxfID0gdGhpcy5yZW5kZXJlci5kb21FbGVtZW50O1xuICAgICAgICAgICAgdGhpcy5lbF8uY2xhc3NMaXN0LmFkZCgndmpzLXZpZGVvLWNhbnZhcycpO1xuXG4gICAgICAgICAgICBvcHRpb25zLmVsID0gdGhpcy5lbF87XG4gICAgICAgICAgICBiYXNlQ29tcG9uZW50LmNhbGwodGhpcywgcGxheWVyLCBvcHRpb25zKTtcblxuICAgICAgICAgICAgdGhpcy5hdHRhY2hDb250cm9sRXZlbnRzKCk7XG4gICAgICAgICAgICB0aGlzLnBsYXllcigpLm9uKFwicGxheVwiLCBmdW5jdGlvbiAoKSB7XG4gICAgICAgICAgICAgICAgdGhpcy50aW1lID0gbmV3IERhdGUoKS5nZXRUaW1lKCk7XG4gICAgICAgICAgICAgICAgdGhpcy5zdGFydEFuaW1hdGlvbigpO1xuICAgICAgICAgICAgfS5iaW5kKHRoaXMpKTtcbiAgICAgICAgfSxcblxuICAgICAgICBhdHRhY2hDb250cm9sRXZlbnRzOiBmdW5jdGlvbigpe1xuICAgICAgICAgICAgdGhpcy5vbignbW91c2Vtb3ZlJywgdGhpcy5oYW5kbGVNb3VzZU1vdmUuYmluZCh0aGlzKSk7XG4gICAgICAgICAgICB0aGlzLm9uKCd0b3VjaG1vdmUnLCB0aGlzLmhhbmRsZVRvdWNoTW92ZS5iaW5kKHRoaXMpKTtcbiAgICAgICAgICAgIHRoaXMub24oJ21vdXNlZG93bicsIHRoaXMuaGFuZGxlTW91c2VEb3duLmJpbmQodGhpcykpO1xuICAgICAgICAgICAgdGhpcy5vbigndG91Y2hzdGFydCcsdGhpcy5oYW5kbGVUb3VjaFN0YXJ0LmJpbmQodGhpcykpO1xuICAgICAgICAgICAgdGhpcy5vbignbW91c2V1cCcsIHRoaXMuaGFuZGxlTW91c2VVcC5iaW5kKHRoaXMpKTtcbiAgICAgICAgICAgIHRoaXMub24oJ3RvdWNoZW5kJywgdGhpcy5oYW5kbGVUb3VjaEVuZC5iaW5kKHRoaXMpKTtcbiAgICAgICAgICAgIGlmKHRoaXMuc2V0dGluZ3Muc2Nyb2xsYWJsZSl7XG4gICAgICAgICAgICAgICAgdGhpcy5vbignbW91c2V3aGVlbCcsIHRoaXMuaGFuZGxlTW91c2VXaGVlbC5iaW5kKHRoaXMpKTtcbiAgICAgICAgICAgICAgICB0aGlzLm9uKCdNb3pNb3VzZVBpeGVsU2Nyb2xsJywgdGhpcy5oYW5kbGVNb3VzZVdoZWVsLmJpbmQodGhpcykpO1xuICAgICAgICAgICAgfVxuICAgICAgICAgICAgdGhpcy5vbignbW91c2VlbnRlcicsIHRoaXMuaGFuZGxlTW91c2VFbnRlci5iaW5kKHRoaXMpKTtcbiAgICAgICAgICAgIHRoaXMub24oJ21vdXNlbGVhdmUnLCB0aGlzLmhhbmRsZU1vdXNlTGVhc2UuYmluZCh0aGlzKSk7XG4gICAgICAgICAgICB0aGlzLm9uKCdkaXNwb3NlJywgdGhpcy5oYW5kbGVEaXNwb3NlLmJpbmQodGhpcykpO1xuICAgICAgICB9LFxuXG4gICAgICAgIGhhbmRsZURpc3Bvc2U6IGZ1bmN0aW9uIChldmVudCl7XG4gICAgICAgICAgICB0aGlzLm9mZignbW91c2Vtb3ZlJywgdGhpcy5oYW5kbGVNb3VzZU1vdmUuYmluZCh0aGlzKSk7XG4gICAgICAgICAgICB0aGlzLm9mZigndG91Y2htb3ZlJywgdGhpcy5oYW5kbGVUb3VjaE1vdmUuYmluZCh0aGlzKSk7XG4gICAgICAgICAgICB0aGlzLm9mZignbW91c2Vkb3duJywgdGhpcy5oYW5kbGVNb3VzZURvd24uYmluZCh0aGlzKSk7XG4gICAgICAgICAgICB0aGlzLm9mZigndG91Y2hzdGFydCcsdGhpcy5oYW5kbGVUb3VjaFN0YXJ0LmJpbmQodGhpcykpO1xuICAgICAgICAgICAgdGhpcy5vZmYoJ21vdXNldXAnLCB0aGlzLmhhbmRsZU1vdXNlVXAuYmluZCh0aGlzKSk7XG4gICAgICAgICAgICB0aGlzLm9mZigndG91Y2hlbmQnLCB0aGlzLmhhbmRsZVRvdWNoRW5kLmJpbmQodGhpcykpO1xuICAgICAgICAgICAgaWYodGhpcy5zZXR0aW5ncy5zY3JvbGxhYmxlKXtcbiAgICAgICAgICAgICAgICB0aGlzLm9mZignbW91c2V3aGVlbCcsIHRoaXMuaGFuZGxlTW91c2VXaGVlbC5iaW5kKHRoaXMpKTtcbiAgICAgICAgICAgICAgICB0aGlzLm9mZignTW96TW91c2VQaXhlbFNjcm9sbCcsIHRoaXMuaGFuZGxlTW91c2VXaGVlbC5iaW5kKHRoaXMpKTtcbiAgICAgICAgICAgIH1cbiAgICAgICAgICAgIHRoaXMub2ZmKCdtb3VzZWVudGVyJywgdGhpcy5oYW5kbGVNb3VzZUVudGVyLmJpbmQodGhpcykpO1xuICAgICAgICAgICAgdGhpcy5vZmYoJ21vdXNlbGVhdmUnLCB0aGlzLmhhbmRsZU1vdXNlTGVhc2UuYmluZCh0aGlzKSk7XG4gICAgICAgICAgICB0aGlzLm9mZignZGlzcG9zZScsIHRoaXMuaGFuZGxlRGlzcG9zZS5iaW5kKHRoaXMpKTtcbiAgICAgICAgICAgIHRoaXMuc3RvcEFuaW1hdGlvbigpO1xuICAgICAgICB9LFxuXG4gICAgICAgIHN0YXJ0QW5pbWF0aW9uOiBmdW5jdGlvbigpe1xuICAgICAgICAgICAgdGhpcy5yZW5kZXJfYW5pbWF0aW9uID0gdHJ1ZTtcbiAgICAgICAgICAgIHRoaXMuYW5pbWF0ZSgpO1xuICAgICAgICB9LFxuXG4gICAgICAgIHN0b3BBbmltYXRpb246IGZ1bmN0aW9uKCl7XG4gICAgICAgICAgICB0aGlzLnJlbmRlcl9hbmltYXRpb24gPSBmYWxzZTtcbiAgICAgICAgICAgIGlmKHRoaXMucmVxdWVzdEFuaW1hdGlvbklkKXtcbiAgICAgICAgICAgICAgICBjYW5jZWxBbmltYXRpb25GcmFtZSh0aGlzLnJlcXVlc3RBbmltYXRpb25JZCk7XG4gICAgICAgICAgICB9XG4gICAgICAgIH0sXG5cbiAgICAgICAgaGFuZGxlUmVzaXplOiBmdW5jdGlvbiAoKSB7XG4gICAgICAgICAgICB0aGlzLndpZHRoID0gdGhpcy5wbGF5ZXIoKS5lbCgpLm9mZnNldFdpZHRoLCB0aGlzLmhlaWdodCA9IHRoaXMucGxheWVyKCkuZWwoKS5vZmZzZXRIZWlnaHQ7XG4gICAgICAgICAgICB0aGlzLnJlbmRlcmVyLnNldFNpemUoIHRoaXMud2lkdGgsIHRoaXMuaGVpZ2h0ICk7XG4gICAgICAgIH0sXG5cbiAgICAgICAgaGFuZGxlTW91c2VVcDogZnVuY3Rpb24oZXZlbnQpe1xuICAgICAgICAgICAgdGhpcy5tb3VzZURvd24gPSBmYWxzZTtcbiAgICAgICAgICAgIGlmKHRoaXMuY2xpY2tUb1RvZ2dsZSl7XG4gICAgICAgICAgICAgICAgdmFyIGNsaWVudFggPSBldmVudC5jbGllbnRYIHx8IGV2ZW50LmNoYW5nZWRUb3VjaGVzICYmIGV2ZW50LmNoYW5nZWRUb3VjaGVzWzBdLmNsaWVudFg7XG4gICAgICAgICAgICAgICAgdmFyIGNsaWVudFkgPSBldmVudC5jbGllbnRZIHx8IGV2ZW50LmNoYW5nZWRUb3VjaGVzICYmIGV2ZW50LmNoYW5nZWRUb3VjaGVzWzBdLmNsaWVudFk7XG4gICAgICAgICAgICAgICAgaWYodHlwZW9mIGNsaWVudFggPT09IFwidW5kZWZpbmVkXCIgfHwgY2xpZW50WSA9PT0gXCJ1bmRlZmluZWRcIikgcmV0dXJuO1xuICAgICAgICAgICAgICAgIHZhciBkaWZmWCA9IE1hdGguYWJzKGNsaWVudFggLSB0aGlzLm9uUG9pbnRlckRvd25Qb2ludGVyWCk7XG4gICAgICAgICAgICAgICAgdmFyIGRpZmZZID0gTWF0aC5hYnMoY2xpZW50WSAtIHRoaXMub25Qb2ludGVyRG93blBvaW50ZXJZKTtcbiAgICAgICAgICAgICAgICBpZihkaWZmWCA8IDAuMSAmJiBkaWZmWSA8IDAuMSlcbiAgICAgICAgICAgICAgICAgICAgdGhpcy5wbGF5ZXIoKS5wYXVzZWQoKSA/IHRoaXMucGxheWVyKCkucGxheSgpIDogdGhpcy5wbGF5ZXIoKS5wYXVzZSgpO1xuICAgICAgICAgICAgfVxuICAgICAgICB9LFxuXG4gICAgICAgIGhhbmRsZU1vdXNlRG93bjogZnVuY3Rpb24oZXZlbnQpe1xuICAgICAgICAgICAgZXZlbnQucHJldmVudERlZmF1bHQoKTtcbiAgICAgICAgICAgIHZhciBjbGllbnRYID0gZXZlbnQuY2xpZW50WCB8fCBldmVudC50b3VjaGVzICYmIGV2ZW50LnRvdWNoZXNbMF0uY2xpZW50WDtcbiAgICAgICAgICAgIHZhciBjbGllbnRZID0gZXZlbnQuY2xpZW50WSB8fCBldmVudC50b3VjaGVzICYmIGV2ZW50LnRvdWNoZXNbMF0uY2xpZW50WTtcbiAgICAgICAgICAgIGlmKHR5cGVvZiBjbGllbnRYID09PSBcInVuZGVmaW5lZFwiIHx8IGNsaWVudFkgPT09IFwidW5kZWZpbmVkXCIpIHJldHVybjtcbiAgICAgICAgICAgIHRoaXMubW91c2VEb3duID0gdHJ1ZTtcbiAgICAgICAgICAgIHRoaXMub25Qb2ludGVyRG93blBvaW50ZXJYID0gY2xpZW50WDtcbiAgICAgICAgICAgIHRoaXMub25Qb2ludGVyRG93blBvaW50ZXJZID0gY2xpZW50WTtcbiAgICAgICAgICAgIHRoaXMub25Qb2ludGVyRG93bkxvbiA9IHRoaXMubG9uO1xuICAgICAgICAgICAgdGhpcy5vblBvaW50ZXJEb3duTGF0ID0gdGhpcy5sYXQ7XG4gICAgICAgIH0sXG5cbiAgICAgICAgaGFuZGxlVG91Y2hTdGFydDogZnVuY3Rpb24oZXZlbnQpe1xuICAgICAgICAgICAgaWYoZXZlbnQudG91Y2hlcy5sZW5ndGggPiAxKXtcbiAgICAgICAgICAgICAgICB0aGlzLmlzVXNlclBpbmNoID0gdHJ1ZTtcbiAgICAgICAgICAgICAgICB0aGlzLm11bHRpVG91Y2hEaXN0YW5jZSA9IFV0aWwuZ2V0VG91Y2hlc0Rpc3RhbmNlKGV2ZW50LnRvdWNoZXMpO1xuICAgICAgICAgICAgfVxuICAgICAgICAgICAgdGhpcy5oYW5kbGVNb3VzZURvd24oZXZlbnQpO1xuICAgICAgICB9LFxuXG4gICAgICAgIGhhbmRsZVRvdWNoRW5kOiBmdW5jdGlvbihldmVudCl7XG4gICAgICAgICAgICB0aGlzLmlzVXNlclBpbmNoID0gZmFsc2U7XG4gICAgICAgICAgICB0aGlzLmhhbmRsZU1vdXNlVXAoZXZlbnQpO1xuICAgICAgICB9LFxuXG4gICAgICAgIGhhbmRsZU1vdXNlTW92ZTogZnVuY3Rpb24oZXZlbnQpe1xuICAgICAgICAgICAgdmFyIGNsaWVudFggPSBldmVudC5jbGllbnRYIHx8IGV2ZW50LnRvdWNoZXMgJiYgZXZlbnQudG91Y2hlc1swXS5jbGllbnRYO1xuICAgICAgICAgICAgdmFyIGNsaWVudFkgPSBldmVudC5jbGllbnRZIHx8IGV2ZW50LnRvdWNoZXMgJiYgZXZlbnQudG91Y2hlc1swXS5jbGllbnRZO1xuICAgICAgICAgICAgaWYodHlwZW9mIGNsaWVudFggPT09IFwidW5kZWZpbmVkXCIgfHwgY2xpZW50WSA9PT0gXCJ1bmRlZmluZWRcIikgcmV0dXJuO1xuICAgICAgICAgICAgaWYodGhpcy5zZXR0aW5ncy5jbGlja0FuZERyYWcpe1xuICAgICAgICAgICAgICAgIGlmKHRoaXMubW91c2VEb3duKXtcbiAgICAgICAgICAgICAgICAgICAgdGhpcy5sb24gPSAoIHRoaXMub25Qb2ludGVyRG93blBvaW50ZXJYIC0gY2xpZW50WCApICogMC4yICsgdGhpcy5vblBvaW50ZXJEb3duTG9uO1xuICAgICAgICAgICAgICAgICAgICB0aGlzLmxhdCA9ICggY2xpZW50WSAtIHRoaXMub25Qb2ludGVyRG93blBvaW50ZXJZICkgKiAwLjIgKyB0aGlzLm9uUG9pbnRlckRvd25MYXQ7XG4gICAgICAgICAgICAgICAgfVxuICAgICAgICAgICAgfWVsc2V7XG4gICAgICAgICAgICAgICAgdmFyIHggPSBjbGllbnRYIC0gdGhpcy5lbF8ub2Zmc2V0TGVmdDtcbiAgICAgICAgICAgICAgICB2YXIgeSA9IGNsaWVudFkgLSB0aGlzLmVsXy5vZmZzZXRUb3A7XG4gICAgICAgICAgICAgICAgdGhpcy5sb24gPSAoeCAvIHRoaXMud2lkdGgpICogNDMwIC0gMjI1O1xuICAgICAgICAgICAgICAgIHRoaXMubGF0ID0gKHkgLyB0aGlzLmhlaWdodCkgKiAtMTgwICsgOTA7XG4gICAgICAgICAgICB9XG4gICAgICAgIH0sXG5cbiAgICAgICAgaGFuZGxlVG91Y2hNb3ZlOiBmdW5jdGlvbihldmVudCl7XG4gICAgICAgICAgICAvL2hhbmRsZSBzaW5nbGUgdG91Y2ggZXZlbnQsXG4gICAgICAgICAgICBpZighdGhpcy5pc1VzZXJQaW5jaCB8fCBldmVudC50b3VjaGVzLmxlbmd0aCA8PSAxKXtcbiAgICAgICAgICAgICAgICB0aGlzLmhhbmRsZU1vdXNlTW92ZShldmVudCk7XG4gICAgICAgICAgICB9XG4gICAgICAgIH0sXG5cbiAgICAgICAgaGFuZGxlTW9iaWxlT3JpZW50YXRpb246IGZ1bmN0aW9uIChldmVudCkge1xuICAgICAgICAgICAgaWYodHlwZW9mIGV2ZW50LnJvdGF0aW9uUmF0ZSA9PT0gXCJ1bmRlZmluZWRcIikgcmV0dXJuO1xuICAgICAgICAgICAgdmFyIHggPSBldmVudC5yb3RhdGlvblJhdGUuYWxwaGE7XG4gICAgICAgICAgICB2YXIgeSA9IGV2ZW50LnJvdGF0aW9uUmF0ZS5iZXRhO1xuICAgICAgICAgICAgdmFyIHBvcnRyYWl0ID0gKHR5cGVvZiBldmVudC5wb3J0cmFpdCAhPT0gXCJ1bmRlZmluZWRcIik/IGV2ZW50LnBvcnRyYWl0IDogd2luZG93Lm1hdGNoTWVkaWEoXCIob3JpZW50YXRpb246IHBvcnRyYWl0KVwiKS5tYXRjaGVzO1xuICAgICAgICAgICAgdmFyIGxhbmRzY2FwZSA9ICh0eXBlb2YgZXZlbnQubGFuZHNjYXBlICE9PSBcInVuZGVmaW5lZFwiKT8gZXZlbnQubGFuZHNjYXBlIDogd2luZG93Lm1hdGNoTWVkaWEoXCIob3JpZW50YXRpb246IGxhbmRzY2FwZSlcIikubWF0Y2hlcztcbiAgICAgICAgICAgIHZhciBvcmllbnRhdGlvbiA9IGV2ZW50Lm9yaWVudGF0aW9uIHx8IHdpbmRvdy5vcmllbnRhdGlvbjtcblxuICAgICAgICAgICAgaWYgKHBvcnRyYWl0KSB7XG4gICAgICAgICAgICAgICAgdGhpcy5sb24gPSB0aGlzLmxvbiAtIHkgKiB0aGlzLnNldHRpbmdzLm1vYmlsZVZpYnJhdGlvblZhbHVlO1xuICAgICAgICAgICAgICAgIHRoaXMubGF0ID0gdGhpcy5sYXQgKyB4ICogdGhpcy5zZXR0aW5ncy5tb2JpbGVWaWJyYXRpb25WYWx1ZTtcbiAgICAgICAgICAgIH1lbHNlIGlmKGxhbmRzY2FwZSl7XG4gICAgICAgICAgICAgICAgdmFyIG9yaWVudGF0aW9uRGVncmVlID0gLTkwO1xuICAgICAgICAgICAgICAgIGlmKHR5cGVvZiBvcmllbnRhdGlvbiAhPSBcInVuZGVmaW5lZFwiKXtcbiAgICAgICAgICAgICAgICAgICAgb3JpZW50YXRpb25EZWdyZWUgPSBvcmllbnRhdGlvbjtcbiAgICAgICAgICAgICAgICB9XG5cbiAgICAgICAgICAgICAgICB0aGlzLmxvbiA9IChvcmllbnRhdGlvbkRlZ3JlZSA9PSAtOTApPyB0aGlzLmxvbiArIHggKiB0aGlzLnNldHRpbmdzLm1vYmlsZVZpYnJhdGlvblZhbHVlIDogdGhpcy5sb24gLSB4ICogdGhpcy5zZXR0aW5ncy5tb2JpbGVWaWJyYXRpb25WYWx1ZTtcbiAgICAgICAgICAgICAgICB0aGlzLmxhdCA9IChvcmllbnRhdGlvbkRlZ3JlZSA9PSAtOTApPyB0aGlzLmxhdCArIHkgKiB0aGlzLnNldHRpbmdzLm1vYmlsZVZpYnJhdGlvblZhbHVlIDogdGhpcy5sYXQgLSB5ICogdGhpcy5zZXR0aW5ncy5tb2JpbGVWaWJyYXRpb25WYWx1ZTtcbiAgICAgICAgICAgIH1cbiAgICAgICAgfSxcblxuICAgICAgICBoYW5kbGVNb3VzZVdoZWVsOiBmdW5jdGlvbihldmVudCl7XG4gICAgICAgICAgICBldmVudC5zdG9wUHJvcGFnYXRpb24oKTtcbiAgICAgICAgICAgIGV2ZW50LnByZXZlbnREZWZhdWx0KCk7XG4gICAgICAgIH0sXG5cbiAgICAgICAgaGFuZGxlTW91c2VFbnRlcjogZnVuY3Rpb24gKGV2ZW50KSB7XG4gICAgICAgICAgICB0aGlzLmlzVXNlckludGVyYWN0aW5nID0gdHJ1ZTtcbiAgICAgICAgfSxcblxuICAgICAgICBoYW5kbGVNb3VzZUxlYXNlOiBmdW5jdGlvbiAoZXZlbnQpIHtcbiAgICAgICAgICAgIHRoaXMuaXNVc2VySW50ZXJhY3RpbmcgPSBmYWxzZTtcbiAgICAgICAgICAgIGlmKHRoaXMubW91c2VEb3duKSB7XG4gICAgICAgICAgICAgICAgdGhpcy5tb3VzZURvd24gPSBmYWxzZTtcbiAgICAgICAgICAgIH1cbiAgICAgICAgfSxcblxuICAgICAgICBhbmltYXRlOiBmdW5jdGlvbigpe1xuICAgICAgICAgICAgaWYoIXRoaXMucmVuZGVyX2FuaW1hdGlvbikgcmV0dXJuO1xuICAgICAgICAgICAgdGhpcy5yZXF1ZXN0QW5pbWF0aW9uSWQgPSByZXF1ZXN0QW5pbWF0aW9uRnJhbWUoIHRoaXMuYW5pbWF0ZS5iaW5kKHRoaXMpICk7XG4gICAgICAgICAgICBpZighdGhpcy5wbGF5ZXIoKS5wYXVzZWQoKSl7XG4gICAgICAgICAgICAgICAgaWYodHlwZW9mKHRoaXMudGV4dHVyZSkgIT09IFwidW5kZWZpbmVkXCIgJiYgKCF0aGlzLmlzUGxheU9uTW9iaWxlICYmIHRoaXMucGxheWVyKCkucmVhZHlTdGF0ZSgpID49IEhBVkVfQ1VSUkVOVF9EQVRBIHx8IHRoaXMuaXNQbGF5T25Nb2JpbGUgJiYgdGhpcy5wbGF5ZXIoKS5oYXNDbGFzcyhcInZqcy1wbGF5aW5nXCIpKSkge1xuICAgICAgICAgICAgICAgICAgICB2YXIgY3QgPSBuZXcgRGF0ZSgpLmdldFRpbWUoKTtcbiAgICAgICAgICAgICAgICAgICAgaWYgKGN0IC0gdGhpcy50aW1lID49IDMwKSB7XG4gICAgICAgICAgICAgICAgICAgICAgICB0aGlzLnRleHR1cmUubmVlZHNVcGRhdGUgPSB0cnVlO1xuICAgICAgICAgICAgICAgICAgICAgICAgdGhpcy50aW1lID0gY3Q7XG4gICAgICAgICAgICAgICAgICAgIH1cbiAgICAgICAgICAgICAgICAgICAgaWYodGhpcy5pc1BsYXlPbk1vYmlsZSl7XG4gICAgICAgICAgICAgICAgICAgICAgICB2YXIgY3VycmVudFRpbWUgPSB0aGlzLnBsYXllcigpLmN1cnJlbnRUaW1lKCk7XG4gICAgICAgICAgICAgICAgICAgICAgICBpZihNb2JpbGVCdWZmZXJpbmcuaXNCdWZmZXJpbmcoY3VycmVudFRpbWUpKXtcbiAgICAgICAgICAgICAgICAgICAgICAgICAgICBpZighdGhpcy5wbGF5ZXIoKS5oYXNDbGFzcyhcInZqcy1wYW5vcmFtYS1tb2JpbGUtaW5saW5lLXZpZGVvLWJ1ZmZlcmluZ1wiKSl7XG4gICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIHRoaXMucGxheWVyKCkuYWRkQ2xhc3MoXCJ2anMtcGFub3JhbWEtbW9iaWxlLWlubGluZS12aWRlby1idWZmZXJpbmdcIik7XG4gICAgICAgICAgICAgICAgICAgICAgICAgICAgfVxuICAgICAgICAgICAgICAgICAgICAgICAgfWVsc2V7XG4gICAgICAgICAgICAgICAgICAgICAgICAgICAgaWYodGhpcy5wbGF5ZXIoKS5oYXNDbGFzcyhcInZqcy1wYW5vcmFtYS1tb2JpbGUtaW5saW5lLXZpZGVvLWJ1ZmZlcmluZ1wiKSl7XG4gICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIHRoaXMucGxheWVyKCkucmVtb3ZlQ2xhc3MoXCJ2anMtcGFub3JhbWEtbW9iaWxlLWlubGluZS12aWRlby1idWZmZXJpbmdcIik7XG4gICAgICAgICAgICAgICAgICAgICAgICAgICAgfVxuICAgICAgICAgICAgICAgICAgICAgICAgfVxuICAgICAgICAgICAgICAgICAgICB9XG4gICAgICAgICAgICAgICAgfVxuICAgICAgICAgICAgfVxuICAgICAgICAgICAgdGhpcy5yZW5kZXIoKTtcbiAgICAgICAgfSxcblxuICAgICAgICByZW5kZXI6IGZ1bmN0aW9uKCl7XG4gICAgICAgICAgICBpZighdGhpcy5pc1VzZXJJbnRlcmFjdGluZyl7XG4gICAgICAgICAgICAgICAgdmFyIHN5bWJvbExhdCA9ICh0aGlzLmxhdCA+IHRoaXMuc2V0dGluZ3MuaW5pdExhdCk/ICAtMSA6IDE7XG4gICAgICAgICAgICAgICAgdmFyIHN5bWJvbExvbiA9ICh0aGlzLmxvbiA+IHRoaXMuc2V0dGluZ3MuaW5pdExvbik/ICAtMSA6IDE7XG4gICAgICAgICAgICAgICAgaWYodGhpcy5zZXR0aW5ncy5iYWNrVG9WZXJ0aWNhbENlbnRlcil7XG4gICAgICAgICAgICAgICAgICAgIHRoaXMubGF0ID0gKFxuICAgICAgICAgICAgICAgICAgICAgICAgdGhpcy5sYXQgPiAodGhpcy5zZXR0aW5ncy5pbml0TGF0IC0gTWF0aC5hYnModGhpcy5zZXR0aW5ncy5yZXR1cm5TdGVwTGF0KSkgJiZcbiAgICAgICAgICAgICAgICAgICAgICAgIHRoaXMubGF0IDwgKHRoaXMuc2V0dGluZ3MuaW5pdExhdCArIE1hdGguYWJzKHRoaXMuc2V0dGluZ3MucmV0dXJuU3RlcExhdCkpXG4gICAgICAgICAgICAgICAgICAgICk/IHRoaXMuc2V0dGluZ3MuaW5pdExhdCA6IHRoaXMubGF0ICsgdGhpcy5zZXR0aW5ncy5yZXR1cm5TdGVwTGF0ICogc3ltYm9sTGF0O1xuICAgICAgICAgICAgICAgIH1cbiAgICAgICAgICAgICAgICBpZih0aGlzLnNldHRpbmdzLmJhY2tUb0hvcml6b25DZW50ZXIpe1xuICAgICAgICAgICAgICAgICAgICB0aGlzLmxvbiA9IChcbiAgICAgICAgICAgICAgICAgICAgICAgIHRoaXMubG9uID4gKHRoaXMuc2V0dGluZ3MuaW5pdExvbiAtIE1hdGguYWJzKHRoaXMuc2V0dGluZ3MucmV0dXJuU3RlcExvbikpICYmXG4gICAgICAgICAgICAgICAgICAgICAgICB0aGlzLmxvbiA8ICh0aGlzLnNldHRpbmdzLmluaXRMb24gKyBNYXRoLmFicyh0aGlzLnNldHRpbmdzLnJldHVyblN0ZXBMb24pKVxuICAgICAgICAgICAgICAgICAgICApPyB0aGlzLnNldHRpbmdzLmluaXRMb24gOiB0aGlzLmxvbiArIHRoaXMuc2V0dGluZ3MucmV0dXJuU3RlcExvbiAqIHN5bWJvbExvbjtcbiAgICAgICAgICAgICAgICB9XG4gICAgICAgICAgICB9XG4gICAgICAgICAgICB0aGlzLmxhdCA9IE1hdGgubWF4KCB0aGlzLnNldHRpbmdzLm1pbkxhdCwgTWF0aC5taW4oIHRoaXMuc2V0dGluZ3MubWF4TGF0LCB0aGlzLmxhdCApICk7XG4gICAgICAgICAgICB0aGlzLmxvbiA9IE1hdGgubWF4KCB0aGlzLnNldHRpbmdzLm1pbkxvbiwgTWF0aC5taW4oIHRoaXMuc2V0dGluZ3MubWF4TG9uLCB0aGlzLmxvbiApICk7XG4gICAgICAgICAgICB0aGlzLnBoaSA9IFRIUkVFLk1hdGguZGVnVG9SYWQoIDkwIC0gdGhpcy5sYXQgKTtcbiAgICAgICAgICAgIHRoaXMudGhldGEgPSBUSFJFRS5NYXRoLmRlZ1RvUmFkKCB0aGlzLmxvbiApO1xuXG4gICAgICAgICAgICBpZighdGhpcy5zdXBwb3J0VmlkZW9UZXh0dXJlKXtcbiAgICAgICAgICAgICAgICB0aGlzLmhlbHBlckNhbnZhcy51cGRhdGUoKTtcbiAgICAgICAgICAgIH1cbiAgICAgICAgICAgIHRoaXMucmVuZGVyZXIuY2xlYXIoKTtcbiAgICAgICAgfSxcblxuICAgICAgICBwbGF5T25Nb2JpbGU6IGZ1bmN0aW9uICgpIHtcbiAgICAgICAgICAgIHRoaXMuaXNQbGF5T25Nb2JpbGUgPSB0cnVlO1xuICAgICAgICAgICAgaWYodGhpcy5zZXR0aW5ncy5hdXRvTW9iaWxlT3JpZW50YXRpb24pXG4gICAgICAgICAgICAgICAgd2luZG93LmFkZEV2ZW50TGlzdGVuZXIoJ2RldmljZW1vdGlvbicsIHRoaXMuaGFuZGxlTW9iaWxlT3JpZW50YXRpb24uYmluZCh0aGlzKSk7XG4gICAgICAgIH0sXG5cbiAgICAgICAgZWw6IGZ1bmN0aW9uKCl7XG4gICAgICAgICAgICByZXR1cm4gdGhpcy5lbF87XG4gICAgICAgIH1cbiAgICB9XG59O1xuXG5leHBvcnQgZGVmYXVsdCBCYXNlQ2FudmFzO1xuIiwiLyoqXG4gKiBDcmVhdGVkIGJ5IHlhbndzaCBvbiA0LzMvMTYuXG4gKi9cblxuaW1wb3J0IEJhc2VDYW52YXMgZnJvbSAnLi9CYXNlQ2FudmFzJztcbmltcG9ydCBVdGlsIGZyb20gJy4vVXRpbCc7XG5cbnZhciBDYW52YXMgPSBmdW5jdGlvbiAoYmFzZUNvbXBvbmVudCwgVEhSRUUsIHNldHRpbmdzID0ge30pIHtcbiAgICB2YXIgcGFyZW50ID0gQmFzZUNhbnZhcyhiYXNlQ29tcG9uZW50LCBUSFJFRSwgc2V0dGluZ3MpO1xuXG4gICAgcmV0dXJuIFV0aWwuZXh0ZW5kKHBhcmVudCwge1xuICAgICAgICBjb25zdHJ1Y3RvcjogZnVuY3Rpb24gaW5pdChwbGF5ZXIsIG9wdGlvbnMpe1xuICAgICAgICAgICAgcGFyZW50LmNvbnN0cnVjdG9yLmNhbGwodGhpcywgcGxheWVyLCBvcHRpb25zKTtcblxuICAgICAgICAgICAgdGhpcy5WUk1vZGUgPSBmYWxzZTtcbiAgICAgICAgICAgIC8vZGVmaW5lIHNjZW5lXG4gICAgICAgICAgICB0aGlzLnNjZW5lID0gbmV3IFRIUkVFLlNjZW5lKCk7XG4gICAgICAgICAgICAvL2RlZmluZSBjYW1lcmFcbiAgICAgICAgICAgIHRoaXMuY2FtZXJhID0gbmV3IFRIUkVFLlBlcnNwZWN0aXZlQ2FtZXJhKG9wdGlvbnMuaW5pdEZvdiwgdGhpcy53aWR0aCAvIHRoaXMuaGVpZ2h0LCAxLCAyMDAwKTtcbiAgICAgICAgICAgIHRoaXMuY2FtZXJhLnRhcmdldCA9IG5ldyBUSFJFRS5WZWN0b3IzKCAwLCAwLCAwICk7XG5cbiAgICAgICAgICAgIC8vZGVmaW5lIGdlb21ldHJ5XG4gICAgICAgICAgICB2YXIgZ2VvbWV0cnkgPSAodGhpcy52aWRlb1R5cGUgPT09IFwiZXF1aXJlY3Rhbmd1bGFyXCIpPyBuZXcgVEhSRUUuU3BoZXJlR2VvbWV0cnkoNTAwLCA2MCwgNDApOiBuZXcgVEhSRUUuU3BoZXJlQnVmZmVyR2VvbWV0cnkoIDUwMCwgNjAsIDQwICkudG9Ob25JbmRleGVkKCk7XG4gICAgICAgICAgICBpZih0aGlzLnZpZGVvVHlwZSA9PT0gXCJmaXNoZXllXCIpe1xuICAgICAgICAgICAgICAgIGxldCBub3JtYWxzID0gZ2VvbWV0cnkuYXR0cmlidXRlcy5ub3JtYWwuYXJyYXk7XG4gICAgICAgICAgICAgICAgbGV0IHV2cyA9IGdlb21ldHJ5LmF0dHJpYnV0ZXMudXYuYXJyYXk7XG4gICAgICAgICAgICAgICAgZm9yICggbGV0IGkgPSAwLCBsID0gbm9ybWFscy5sZW5ndGggLyAzOyBpIDwgbDsgaSArKyApIHtcbiAgICAgICAgICAgICAgICAgICAgbGV0IHggPSBub3JtYWxzWyBpICogMyArIDAgXTtcbiAgICAgICAgICAgICAgICAgICAgbGV0IHkgPSBub3JtYWxzWyBpICogMyArIDEgXTtcbiAgICAgICAgICAgICAgICAgICAgbGV0IHogPSBub3JtYWxzWyBpICogMyArIDIgXTtcblxuICAgICAgICAgICAgICAgICAgICBsZXQgciA9IE1hdGguYXNpbihNYXRoLnNxcnQoeCAqIHggKyB6ICogeikgLyBNYXRoLnNxcnQoeCAqIHggICsgeSAqIHkgKyB6ICogeikpIC8gTWF0aC5QSTtcbiAgICAgICAgICAgICAgICAgICAgaWYoeSA8IDApIHIgPSAxIC0gcjtcbiAgICAgICAgICAgICAgICAgICAgbGV0IHRoZXRhID0gKHggPT0gMCAmJiB6ID09IDApPyAwIDogTWF0aC5hY29zKHggLyBNYXRoLnNxcnQoeCAqIHggKyB6ICogeikpO1xuICAgICAgICAgICAgICAgICAgICBpZih6IDwgMCkgdGhldGEgPSB0aGV0YSAqIC0xO1xuICAgICAgICAgICAgICAgICAgICB1dnNbIGkgKiAyICsgMCBdID0gLTAuOCAqIHIgKiBNYXRoLmNvcyh0aGV0YSkgKyAwLjU7XG4gICAgICAgICAgICAgICAgICAgIHV2c1sgaSAqIDIgKyAxIF0gPSAwLjggKiByICogTWF0aC5zaW4odGhldGEpICsgMC41O1xuICAgICAgICAgICAgICAgIH1cbiAgICAgICAgICAgICAgICBnZW9tZXRyeS5yb3RhdGVYKCBvcHRpb25zLnJvdGF0ZVgpO1xuICAgICAgICAgICAgICAgIGdlb21ldHJ5LnJvdGF0ZVkoIG9wdGlvbnMucm90YXRlWSk7XG4gICAgICAgICAgICAgICAgZ2VvbWV0cnkucm90YXRlWiggb3B0aW9ucy5yb3RhdGVaKTtcbiAgICAgICAgICAgIH1lbHNlIGlmKHRoaXMudmlkZW9UeXBlID09PSBcImR1YWxfZmlzaGV5ZVwiKXtcbiAgICAgICAgICAgICAgICBsZXQgbm9ybWFscyA9IGdlb21ldHJ5LmF0dHJpYnV0ZXMubm9ybWFsLmFycmF5O1xuICAgICAgICAgICAgICAgIGxldCB1dnMgPSBnZW9tZXRyeS5hdHRyaWJ1dGVzLnV2LmFycmF5O1xuICAgICAgICAgICAgICAgIGxldCBsID0gbm9ybWFscy5sZW5ndGggLyAzO1xuICAgICAgICAgICAgICAgIGZvciAoIGxldCBpID0gMDsgaSA8IGwgLyAyOyBpICsrICkge1xuICAgICAgICAgICAgICAgICAgICBsZXQgeCA9IG5vcm1hbHNbIGkgKiAzICsgMCBdO1xuICAgICAgICAgICAgICAgICAgICBsZXQgeSA9IG5vcm1hbHNbIGkgKiAzICsgMSBdO1xuICAgICAgICAgICAgICAgICAgICBsZXQgeiA9IG5vcm1hbHNbIGkgKiAzICsgMiBdO1xuXG4gICAgICAgICAgICAgICAgICAgIGxldCByID0gKCB4ID09IDAgJiYgeiA9PSAwICkgPyAxIDogKCBNYXRoLmFjb3MoIHkgKSAvIE1hdGguc3FydCggeCAqIHggKyB6ICogeiApICkgKiAoIDIgLyBNYXRoLlBJICk7XG4gICAgICAgICAgICAgICAgICAgIHV2c1sgaSAqIDIgKyAwIF0gPSB4ICogb3B0aW9ucy5kdWFsRmlzaC5jaXJjbGUxLnJ4ICogciAqIG9wdGlvbnMuZHVhbEZpc2guY2lyY2xlMS5jb3ZlclggICsgb3B0aW9ucy5kdWFsRmlzaC5jaXJjbGUxLng7XG4gICAgICAgICAgICAgICAgICAgIHV2c1sgaSAqIDIgKyAxIF0gPSB6ICogb3B0aW9ucy5kdWFsRmlzaC5jaXJjbGUxLnJ5ICogciAqIG9wdGlvbnMuZHVhbEZpc2guY2lyY2xlMS5jb3ZlclkgICsgb3B0aW9ucy5kdWFsRmlzaC5jaXJjbGUxLnk7XG4gICAgICAgICAgICAgICAgfVxuICAgICAgICAgICAgICAgIGZvciAoIGxldCBpID0gbCAvIDI7IGkgPCBsOyBpICsrICkge1xuICAgICAgICAgICAgICAgICAgICBsZXQgeCA9IG5vcm1hbHNbIGkgKiAzICsgMCBdO1xuICAgICAgICAgICAgICAgICAgICBsZXQgeSA9IG5vcm1hbHNbIGkgKiAzICsgMSBdO1xuICAgICAgICAgICAgICAgICAgICBsZXQgeiA9IG5vcm1hbHNbIGkgKiAzICsgMiBdO1xuXG4gICAgICAgICAgICAgICAgICAgIGxldCByID0gKCB4ID09IDAgJiYgeiA9PSAwICkgPyAxIDogKCBNYXRoLmFjb3MoIC0geSApIC8gTWF0aC5zcXJ0KCB4ICogeCArIHogKiB6ICkgKSAqICggMiAvIE1hdGguUEkgKTtcbiAgICAgICAgICAgICAgICAgICAgdXZzWyBpICogMiArIDAgXSA9IC0geCAqIG9wdGlvbnMuZHVhbEZpc2guY2lyY2xlMi5yeCAqIHIgKiBvcHRpb25zLmR1YWxGaXNoLmNpcmNsZTIuY292ZXJYICArIG9wdGlvbnMuZHVhbEZpc2guY2lyY2xlMi54O1xuICAgICAgICAgICAgICAgICAgICB1dnNbIGkgKiAyICsgMSBdID0geiAqIG9wdGlvbnMuZHVhbEZpc2guY2lyY2xlMi5yeSAqIHIgKiBvcHRpb25zLmR1YWxGaXNoLmNpcmNsZTIuY292ZXJZICArIG9wdGlvbnMuZHVhbEZpc2guY2lyY2xlMi55O1xuICAgICAgICAgICAgICAgIH1cbiAgICAgICAgICAgICAgICBnZW9tZXRyeS5yb3RhdGVYKCBvcHRpb25zLnJvdGF0ZVgpO1xuICAgICAgICAgICAgICAgIGdlb21ldHJ5LnJvdGF0ZVkoIG9wdGlvbnMucm90YXRlWSk7XG4gICAgICAgICAgICAgICAgZ2VvbWV0cnkucm90YXRlWiggb3B0aW9ucy5yb3RhdGVaKTtcbiAgICAgICAgICAgIH1cbiAgICAgICAgICAgIGdlb21ldHJ5LnNjYWxlKCAtIDEsIDEsIDEgKTtcbiAgICAgICAgICAgIC8vZGVmaW5lIG1lc2hcbiAgICAgICAgICAgIHRoaXMubWVzaCA9IG5ldyBUSFJFRS5NZXNoKGdlb21ldHJ5LFxuICAgICAgICAgICAgICAgIG5ldyBUSFJFRS5NZXNoQmFzaWNNYXRlcmlhbCh7IG1hcDogdGhpcy50ZXh0dXJlfSlcbiAgICAgICAgICAgICk7XG4gICAgICAgICAgICAvL3RoaXMubWVzaC5zY2FsZS54ID0gLTE7XG4gICAgICAgICAgICB0aGlzLnNjZW5lLmFkZCh0aGlzLm1lc2gpO1xuICAgICAgICB9LFxuXG4gICAgICAgIGVuYWJsZVZSOiBmdW5jdGlvbiAoKSB7XG4gICAgICAgICAgICB0aGlzLlZSTW9kZSA9IHRydWU7XG4gICAgICAgICAgICBpZih0eXBlb2YgdnJITUQgIT09ICd1bmRlZmluZWQnKXtcbiAgICAgICAgICAgICAgICB2YXIgZXllUGFyYW1zTCA9IHZySE1ELmdldEV5ZVBhcmFtZXRlcnMoICdsZWZ0JyApO1xuICAgICAgICAgICAgICAgIHZhciBleWVQYXJhbXNSID0gdnJITUQuZ2V0RXllUGFyYW1ldGVycyggJ3JpZ2h0JyApO1xuXG4gICAgICAgICAgICAgICAgdGhpcy5leWVGT1ZMID0gZXllUGFyYW1zTC5yZWNvbW1lbmRlZEZpZWxkT2ZWaWV3O1xuICAgICAgICAgICAgICAgIHRoaXMuZXllRk9WUiA9IGV5ZVBhcmFtc1IucmVjb21tZW5kZWRGaWVsZE9mVmlldztcbiAgICAgICAgICAgIH1cblxuICAgICAgICAgICAgdGhpcy5jYW1lcmFMID0gbmV3IFRIUkVFLlBlcnNwZWN0aXZlQ2FtZXJhKHRoaXMuY2FtZXJhLmZvdiwgdGhpcy53aWR0aCAvMiAvIHRoaXMuaGVpZ2h0LCAxLCAyMDAwKTtcbiAgICAgICAgICAgIHRoaXMuY2FtZXJhUiA9IG5ldyBUSFJFRS5QZXJzcGVjdGl2ZUNhbWVyYSh0aGlzLmNhbWVyYS5mb3YsIHRoaXMud2lkdGggLzIgLyB0aGlzLmhlaWdodCwgMSwgMjAwMCk7XG4gICAgICAgIH0sXG5cbiAgICAgICAgZGlzYWJsZVZSOiBmdW5jdGlvbiAoKSB7XG4gICAgICAgICAgICB0aGlzLlZSTW9kZSA9IGZhbHNlO1xuICAgICAgICAgICAgdGhpcy5yZW5kZXJlci5zZXRWaWV3cG9ydCggMCwgMCwgdGhpcy53aWR0aCwgdGhpcy5oZWlnaHQgKTtcbiAgICAgICAgICAgIHRoaXMucmVuZGVyZXIuc2V0U2Npc3NvciggMCwgMCwgdGhpcy53aWR0aCwgdGhpcy5oZWlnaHQgKTtcbiAgICAgICAgfSxcblxuICAgICAgICBoYW5kbGVSZXNpemU6IGZ1bmN0aW9uICgpIHtcbiAgICAgICAgICAgIHBhcmVudC5oYW5kbGVSZXNpemUuY2FsbCh0aGlzKTtcbiAgICAgICAgICAgIHRoaXMuY2FtZXJhLmFzcGVjdCA9IHRoaXMud2lkdGggLyB0aGlzLmhlaWdodDtcbiAgICAgICAgICAgIHRoaXMuY2FtZXJhLnVwZGF0ZVByb2plY3Rpb25NYXRyaXgoKTtcbiAgICAgICAgICAgIGlmKHRoaXMuVlJNb2RlKXtcbiAgICAgICAgICAgICAgICB0aGlzLmNhbWVyYUwuYXNwZWN0ID0gdGhpcy5jYW1lcmEuYXNwZWN0IC8gMjtcbiAgICAgICAgICAgICAgICB0aGlzLmNhbWVyYVIuYXNwZWN0ID0gdGhpcy5jYW1lcmEuYXNwZWN0IC8gMjtcbiAgICAgICAgICAgICAgICB0aGlzLmNhbWVyYUwudXBkYXRlUHJvamVjdGlvbk1hdHJpeCgpO1xuICAgICAgICAgICAgICAgIHRoaXMuY2FtZXJhUi51cGRhdGVQcm9qZWN0aW9uTWF0cml4KCk7XG4gICAgICAgICAgICB9XG4gICAgICAgIH0sXG5cbiAgICAgICAgaGFuZGxlTW91c2VXaGVlbDogZnVuY3Rpb24oZXZlbnQpe1xuICAgICAgICAgICAgcGFyZW50LmhhbmRsZU1vdXNlV2hlZWwoZXZlbnQpO1xuICAgICAgICAgICAgLy8gV2ViS2l0XG4gICAgICAgICAgICBpZiAoIGV2ZW50LndoZWVsRGVsdGFZICkge1xuICAgICAgICAgICAgICAgIHRoaXMuY2FtZXJhLmZvdiAtPSBldmVudC53aGVlbERlbHRhWSAqIDAuMDU7XG4gICAgICAgICAgICAgICAgLy8gT3BlcmEgLyBFeHBsb3JlciA5XG4gICAgICAgICAgICB9IGVsc2UgaWYgKCBldmVudC53aGVlbERlbHRhICkge1xuICAgICAgICAgICAgICAgIHRoaXMuY2FtZXJhLmZvdiAtPSBldmVudC53aGVlbERlbHRhICogMC4wNTtcbiAgICAgICAgICAgICAgICAvLyBGaXJlZm94XG4gICAgICAgICAgICB9IGVsc2UgaWYgKCBldmVudC5kZXRhaWwgKSB7XG4gICAgICAgICAgICAgICAgdGhpcy5jYW1lcmEuZm92ICs9IGV2ZW50LmRldGFpbCAqIDEuMDtcbiAgICAgICAgICAgIH1cbiAgICAgICAgICAgIHRoaXMuY2FtZXJhLmZvdiA9IE1hdGgubWluKHRoaXMuc2V0dGluZ3MubWF4Rm92LCB0aGlzLmNhbWVyYS5mb3YpO1xuICAgICAgICAgICAgdGhpcy5jYW1lcmEuZm92ID0gTWF0aC5tYXgodGhpcy5zZXR0aW5ncy5taW5Gb3YsIHRoaXMuY2FtZXJhLmZvdik7XG4gICAgICAgICAgICB0aGlzLmNhbWVyYS51cGRhdGVQcm9qZWN0aW9uTWF0cml4KCk7XG4gICAgICAgICAgICBpZih0aGlzLlZSTW9kZSl7XG4gICAgICAgICAgICAgICAgdGhpcy5jYW1lcmFMLmZvdiA9IHRoaXMuY2FtZXJhLmZvdjtcbiAgICAgICAgICAgICAgICB0aGlzLmNhbWVyYVIuZm92ID0gdGhpcy5jYW1lcmEuZm92O1xuICAgICAgICAgICAgICAgIHRoaXMuY2FtZXJhTC51cGRhdGVQcm9qZWN0aW9uTWF0cml4KCk7XG4gICAgICAgICAgICAgICAgdGhpcy5jYW1lcmFSLnVwZGF0ZVByb2plY3Rpb25NYXRyaXgoKTtcbiAgICAgICAgICAgIH1cbiAgICAgICAgfSxcblxuICAgICAgICBoYW5kbGVUb3VjaE1vdmU6IGZ1bmN0aW9uIChldmVudCkge1xuICAgICAgICAgICAgcGFyZW50LmhhbmRsZVRvdWNoTW92ZS5jYWxsKHRoaXMsIGV2ZW50KTtcbiAgICAgICAgICAgIGlmKHRoaXMuaXNVc2VyUGluY2gpe1xuICAgICAgICAgICAgICAgIGxldCBjdXJyZW50RGlzdGFuY2UgPSBVdGlsLmdldFRvdWNoZXNEaXN0YW5jZShldmVudC50b3VjaGVzKTtcbiAgICAgICAgICAgICAgICBldmVudC53aGVlbERlbHRhWSA9ICAoY3VycmVudERpc3RhbmNlIC0gdGhpcy5tdWx0aVRvdWNoRGlzdGFuY2UpICogMjtcbiAgICAgICAgICAgICAgICB0aGlzLmhhbmRsZU1vdXNlV2hlZWwuY2FsbCh0aGlzLCBldmVudCk7XG4gICAgICAgICAgICAgICAgdGhpcy5tdWx0aVRvdWNoRGlzdGFuY2UgPSBjdXJyZW50RGlzdGFuY2U7XG4gICAgICAgICAgICB9XG4gICAgICAgIH0sXG5cbiAgICAgICAgcmVuZGVyOiBmdW5jdGlvbigpe1xuICAgICAgICAgICAgcGFyZW50LnJlbmRlci5jYWxsKHRoaXMpO1xuICAgICAgICAgICAgdGhpcy5jYW1lcmEudGFyZ2V0LnggPSA1MDAgKiBNYXRoLnNpbiggdGhpcy5waGkgKSAqIE1hdGguY29zKCB0aGlzLnRoZXRhICk7XG4gICAgICAgICAgICB0aGlzLmNhbWVyYS50YXJnZXQueSA9IDUwMCAqIE1hdGguY29zKCB0aGlzLnBoaSApO1xuICAgICAgICAgICAgdGhpcy5jYW1lcmEudGFyZ2V0LnogPSA1MDAgKiBNYXRoLnNpbiggdGhpcy5waGkgKSAqIE1hdGguc2luKCB0aGlzLnRoZXRhICk7XG4gICAgICAgICAgICB0aGlzLmNhbWVyYS5sb29rQXQoIHRoaXMuY2FtZXJhLnRhcmdldCApO1xuXG4gICAgICAgICAgICBpZighdGhpcy5WUk1vZGUpe1xuICAgICAgICAgICAgICAgIHRoaXMucmVuZGVyZXIucmVuZGVyKCB0aGlzLnNjZW5lLCB0aGlzLmNhbWVyYSApO1xuICAgICAgICAgICAgfVxuICAgICAgICAgICAgZWxzZXtcbiAgICAgICAgICAgICAgICB2YXIgdmlld1BvcnRXaWR0aCA9IHRoaXMud2lkdGggLyAyLCB2aWV3UG9ydEhlaWdodCA9IHRoaXMuaGVpZ2h0O1xuICAgICAgICAgICAgICAgIGlmKHR5cGVvZiB2ckhNRCAhPT0gJ3VuZGVmaW5lZCcpe1xuICAgICAgICAgICAgICAgICAgICB0aGlzLmNhbWVyYUwucHJvamVjdGlvbk1hdHJpeCA9IFV0aWwuZm92VG9Qcm9qZWN0aW9uKCB0aGlzLmV5ZUZPVkwsIHRydWUsIHRoaXMuY2FtZXJhLm5lYXIsIHRoaXMuY2FtZXJhLmZhciApO1xuICAgICAgICAgICAgICAgICAgICB0aGlzLmNhbWVyYVIucHJvamVjdGlvbk1hdHJpeCA9IFV0aWwuZm92VG9Qcm9qZWN0aW9uKCB0aGlzLmV5ZUZPVlIsIHRydWUsIHRoaXMuY2FtZXJhLm5lYXIsIHRoaXMuY2FtZXJhLmZhciApO1xuICAgICAgICAgICAgICAgIH1lbHNle1xuICAgICAgICAgICAgICAgICAgICB2YXIgbG9uTCA9IHRoaXMubG9uICsgdGhpcy5zZXR0aW5ncy5WUkdhcERlZ3JlZTtcbiAgICAgICAgICAgICAgICAgICAgdmFyIGxvblIgPSB0aGlzLmxvbiAtIHRoaXMuc2V0dGluZ3MuVlJHYXBEZWdyZWU7XG5cbiAgICAgICAgICAgICAgICAgICAgdmFyIHRoZXRhTCA9IFRIUkVFLk1hdGguZGVnVG9SYWQoIGxvbkwgKTtcbiAgICAgICAgICAgICAgICAgICAgdmFyIHRoZXRhUiA9IFRIUkVFLk1hdGguZGVnVG9SYWQoIGxvblIgKTtcblxuICAgICAgICAgICAgICAgICAgICB2YXIgdGFyZ2V0TCA9IFV0aWwuZGVlcENvcHkodGhpcy5jYW1lcmEudGFyZ2V0KTtcbiAgICAgICAgICAgICAgICAgICAgdGFyZ2V0TC54ID0gNTAwICogTWF0aC5zaW4oIHRoaXMucGhpICkgKiBNYXRoLmNvcyggdGhldGFMICk7XG4gICAgICAgICAgICAgICAgICAgIHRhcmdldEwueiA9IDUwMCAqIE1hdGguc2luKCB0aGlzLnBoaSApICogTWF0aC5zaW4oIHRoZXRhTCApO1xuICAgICAgICAgICAgICAgICAgICB0aGlzLmNhbWVyYUwubG9va0F0KHRhcmdldEwpO1xuXG4gICAgICAgICAgICAgICAgICAgIHZhciB0YXJnZXRSID0gVXRpbC5kZWVwQ29weSh0aGlzLmNhbWVyYS50YXJnZXQpO1xuICAgICAgICAgICAgICAgICAgICB0YXJnZXRSLnggPSA1MDAgKiBNYXRoLnNpbiggdGhpcy5waGkgKSAqIE1hdGguY29zKCB0aGV0YVIgKTtcbiAgICAgICAgICAgICAgICAgICAgdGFyZ2V0Ui56ID0gNTAwICogTWF0aC5zaW4oIHRoaXMucGhpICkgKiBNYXRoLnNpbiggdGhldGFSICk7XG4gICAgICAgICAgICAgICAgICAgIHRoaXMuY2FtZXJhUi5sb29rQXQodGFyZ2V0Uik7XG4gICAgICAgICAgICAgICAgfVxuICAgICAgICAgICAgICAgIC8vIHJlbmRlciBsZWZ0IGV5ZVxuICAgICAgICAgICAgICAgIHRoaXMucmVuZGVyZXIuc2V0Vmlld3BvcnQoIDAsIDAsIHZpZXdQb3J0V2lkdGgsIHZpZXdQb3J0SGVpZ2h0ICk7XG4gICAgICAgICAgICAgICAgdGhpcy5yZW5kZXJlci5zZXRTY2lzc29yKCAwLCAwLCB2aWV3UG9ydFdpZHRoLCB2aWV3UG9ydEhlaWdodCApO1xuICAgICAgICAgICAgICAgIHRoaXMucmVuZGVyZXIucmVuZGVyKCB0aGlzLnNjZW5lLCB0aGlzLmNhbWVyYUwgKTtcblxuICAgICAgICAgICAgICAgIC8vIHJlbmRlciByaWdodCBleWVcbiAgICAgICAgICAgICAgICB0aGlzLnJlbmRlcmVyLnNldFZpZXdwb3J0KCB2aWV3UG9ydFdpZHRoLCAwLCB2aWV3UG9ydFdpZHRoLCB2aWV3UG9ydEhlaWdodCApO1xuICAgICAgICAgICAgICAgIHRoaXMucmVuZGVyZXIuc2V0U2Npc3Nvciggdmlld1BvcnRXaWR0aCwgMCwgdmlld1BvcnRXaWR0aCwgdmlld1BvcnRIZWlnaHQgKTtcbiAgICAgICAgICAgICAgICB0aGlzLnJlbmRlcmVyLnJlbmRlciggdGhpcy5zY2VuZSwgdGhpcy5jYW1lcmFSICk7XG4gICAgICAgICAgICB9XG4gICAgICAgIH1cbiAgICB9KTtcbn07XG5cbmV4cG9ydCBkZWZhdWx0IENhbnZhcztcbiIsIi8qKlxuICogQGF1dGhvciBhbHRlcmVkcSAvIGh0dHA6Ly9hbHRlcmVkcXVhbGlhLmNvbS9cbiAqIEBhdXRob3IgbXIuZG9vYiAvIGh0dHA6Ly9tcmRvb2IuY29tL1xuICovXG5cbi8vaW4gY2FzZSBpdCdzIHJ1bm5pbmcgb24gbm9kZS5qc1xubGV0IHdpbiA9IHt9O1xuXG5pZiAodHlwZW9mIHdpbmRvdyAhPT0gXCJ1bmRlZmluZWRcIikge1xuICAgIHdpbiA9IHdpbmRvdztcbn1cblxudmFyIERldGVjdG9yID0ge1xuXG4gICAgY2FudmFzOiAhISB3aW4uQ2FudmFzUmVuZGVyaW5nQ29udGV4dDJELFxuICAgIHdlYmdsOiAoIGZ1bmN0aW9uICgpIHtcblxuICAgICAgICB0cnkge1xuXG4gICAgICAgICAgICB2YXIgY2FudmFzID0gZG9jdW1lbnQuY3JlYXRlRWxlbWVudCggJ2NhbnZhcycgKTsgcmV0dXJuICEhICggd2luLldlYkdMUmVuZGVyaW5nQ29udGV4dCAmJiAoIGNhbnZhcy5nZXRDb250ZXh0KCAnd2ViZ2wnICkgfHwgY2FudmFzLmdldENvbnRleHQoICdleHBlcmltZW50YWwtd2ViZ2wnICkgKSApO1xuXG4gICAgICAgIH0gY2F0Y2ggKCBlICkge1xuXG4gICAgICAgICAgICByZXR1cm4gZmFsc2U7XG5cbiAgICAgICAgfVxuXG4gICAgfSApKCksXG4gICAgd29ya2VyczogISEgd2luLldvcmtlcixcbiAgICBmaWxlYXBpOiB3aW4uRmlsZSAmJiB3aW4uRmlsZVJlYWRlciAmJiB3aW4uRmlsZUxpc3QgJiYgd2luLkJsb2IsXG5cbiAgICAgQ2hlY2tfVmVyc2lvbjogZnVuY3Rpb24oKSB7XG4gICAgICAgICB2YXIgcnYgPSAtMTsgLy8gUmV0dXJuIHZhbHVlIGFzc3VtZXMgZmFpbHVyZS5cblxuICAgICAgICAgaWYgKG5hdmlnYXRvci5hcHBOYW1lID09ICdNaWNyb3NvZnQgSW50ZXJuZXQgRXhwbG9yZXInKSB7XG5cbiAgICAgICAgICAgICB2YXIgdWEgPSBuYXZpZ2F0b3IudXNlckFnZW50LFxuICAgICAgICAgICAgICAgICByZSA9IG5ldyBSZWdFeHAoXCJNU0lFIChbMC05XXsxLH1bXFxcXC4wLTldezAsfSlcIik7XG5cbiAgICAgICAgICAgICBpZiAocmUuZXhlYyh1YSkgIT09IG51bGwpIHtcbiAgICAgICAgICAgICAgICAgcnYgPSBwYXJzZUZsb2F0KFJlZ0V4cC4kMSk7XG4gICAgICAgICAgICAgfVxuICAgICAgICAgfVxuICAgICAgICAgZWxzZSBpZiAobmF2aWdhdG9yLmFwcE5hbWUgPT0gXCJOZXRzY2FwZVwiKSB7XG4gICAgICAgICAgICAgLy8vIGluIElFIDExIHRoZSBuYXZpZ2F0b3IuYXBwVmVyc2lvbiBzYXlzICd0cmlkZW50J1xuICAgICAgICAgICAgIC8vLyBpbiBFZGdlIHRoZSBuYXZpZ2F0b3IuYXBwVmVyc2lvbiBkb2VzIG5vdCBzYXkgdHJpZGVudFxuICAgICAgICAgICAgIGlmIChuYXZpZ2F0b3IuYXBwVmVyc2lvbi5pbmRleE9mKCdUcmlkZW50JykgIT09IC0xKSBydiA9IDExO1xuICAgICAgICAgICAgIGVsc2V7XG4gICAgICAgICAgICAgICAgIHZhciB1YSA9IG5hdmlnYXRvci51c2VyQWdlbnQ7XG4gICAgICAgICAgICAgICAgIHZhciByZSA9IG5ldyBSZWdFeHAoXCJFZGdlXFwvKFswLTldezEsfVtcXFxcLjAtOV17MCx9KVwiKTtcbiAgICAgICAgICAgICAgICAgaWYgKHJlLmV4ZWModWEpICE9PSBudWxsKSB7XG4gICAgICAgICAgICAgICAgICAgICBydiA9IHBhcnNlRmxvYXQoUmVnRXhwLiQxKTtcbiAgICAgICAgICAgICAgICAgfVxuICAgICAgICAgICAgIH1cbiAgICAgICAgIH1cblxuICAgICAgICAgcmV0dXJuIHJ2O1xuICAgICB9LFxuXG4gICAgc3VwcG9ydFZpZGVvVGV4dHVyZTogZnVuY3Rpb24gKCkge1xuICAgICAgICAvL2llIDExIGFuZCBlZGdlIDEyIGRvZXNuJ3Qgc3VwcG9ydCB2aWRlbyB0ZXh0dXJlLlxuICAgICAgICB2YXIgdmVyc2lvbiA9IHRoaXMuQ2hlY2tfVmVyc2lvbigpO1xuICAgICAgICByZXR1cm4gKHZlcnNpb24gPT09IC0xIHx8IHZlcnNpb24gPj0gMTMpO1xuICAgIH0sXG5cbiAgICBpc0xpdmVTdHJlYW1PblNhZmFyaTogZnVuY3Rpb24gKHZpZGVvRWxlbWVudCkge1xuICAgICAgICAvL2xpdmUgc3RyZWFtIG9uIHNhZmFyaSBkb2Vzbid0IHN1cHBvcnQgdmlkZW8gdGV4dHVyZVxuICAgICAgICB2YXIgdmlkZW9Tb3VyY2VzID0gW10uc2xpY2UuY2FsbCh2aWRlb0VsZW1lbnQucXVlcnlTZWxlY3RvckFsbChcInNvdXJjZVwiKSk7XG4gICAgICAgIHZhciByZXN1bHQgPSBmYWxzZTtcbiAgICAgICAgaWYodmlkZW9FbGVtZW50LnNyYyAmJiB2aWRlb0VsZW1lbnQuc3JjLmluZGV4T2YoJy5tM3U4JykgPiAtMSl7XG4gICAgICAgICAgICB2aWRlb1NvdXJjZXMucHVzaCh7XG4gICAgICAgICAgICAgICAgc3JjOiB2aWRlb0VsZW1lbnQuc3JjLFxuICAgICAgICAgICAgICAgIHR5cGU6IFwiYXBwbGljYXRpb24veC1tcGVnVVJMXCJcbiAgICAgICAgICAgIH0pO1xuICAgICAgICB9XG4gICAgICAgIGZvcih2YXIgaSA9IDA7IGkgPCB2aWRlb1NvdXJjZXMubGVuZ3RoOyBpKyspe1xuICAgICAgICAgICAgdmFyIGN1cnJlbnRWaWRlb1NvdXJjZSA9IHZpZGVvU291cmNlc1tpXTtcbiAgICAgICAgICAgIGlmKChjdXJyZW50VmlkZW9Tb3VyY2UudHlwZSA9PT0gXCJhcHBsaWNhdGlvbi94LW1wZWdVUkxcIiB8fCBjdXJyZW50VmlkZW9Tb3VyY2UudHlwZSA9PT0gXCJhcHBsaWNhdGlvbi92bmQuYXBwbGUubXBlZ3VybFwiKSAmJiAvKFNhZmFyaXxBcHBsZVdlYktpdCkvLnRlc3QobmF2aWdhdG9yLnVzZXJBZ2VudCkgJiYgL0FwcGxlIENvbXB1dGVyLy50ZXN0KG5hdmlnYXRvci52ZW5kb3IpKXtcbiAgICAgICAgICAgICAgICByZXN1bHQgPSB0cnVlO1xuICAgICAgICAgICAgICAgIGJyZWFrO1xuICAgICAgICAgICAgfVxuICAgICAgICB9XG4gICAgICAgIHJldHVybiByZXN1bHQ7XG4gICAgfSxcblxuICAgIGdldFdlYkdMRXJyb3JNZXNzYWdlOiBmdW5jdGlvbiAoKSB7XG5cbiAgICAgICAgdmFyIGVsZW1lbnQgPSBkb2N1bWVudC5jcmVhdGVFbGVtZW50KCAnZGl2JyApO1xuICAgICAgICBlbGVtZW50LmlkID0gJ3dlYmdsLWVycm9yLW1lc3NhZ2UnO1xuXG4gICAgICAgIGlmICggISB0aGlzLndlYmdsICkge1xuXG4gICAgICAgICAgICBlbGVtZW50LmlubmVySFRNTCA9IHdpbi5XZWJHTFJlbmRlcmluZ0NvbnRleHQgPyBbXG4gICAgICAgICAgICAgICAgJ1lvdXIgZ3JhcGhpY3MgY2FyZCBkb2VzIG5vdCBzZWVtIHRvIHN1cHBvcnQgPGEgaHJlZj1cImh0dHA6Ly9raHJvbm9zLm9yZy93ZWJnbC93aWtpL0dldHRpbmdfYV9XZWJHTF9JbXBsZW1lbnRhdGlvblwiIHN0eWxlPVwiY29sb3I6IzAwMFwiPldlYkdMPC9hPi48YnIgLz4nLFxuICAgICAgICAgICAgICAgICdGaW5kIG91dCBob3cgdG8gZ2V0IGl0IDxhIGhyZWY9XCJodHRwOi8vZ2V0LndlYmdsLm9yZy9cIiBzdHlsZT1cImNvbG9yOiMwMDBcIj5oZXJlPC9hPi4nXG4gICAgICAgICAgICBdLmpvaW4oICdcXG4nICkgOiBbXG4gICAgICAgICAgICAgICAgJ1lvdXIgYnJvd3NlciBkb2VzIG5vdCBzZWVtIHRvIHN1cHBvcnQgPGEgaHJlZj1cImh0dHA6Ly9raHJvbm9zLm9yZy93ZWJnbC93aWtpL0dldHRpbmdfYV9XZWJHTF9JbXBsZW1lbnRhdGlvblwiIHN0eWxlPVwiY29sb3I6IzAwMFwiPldlYkdMPC9hPi48YnIvPicsXG4gICAgICAgICAgICAgICAgJ0ZpbmQgb3V0IGhvdyB0byBnZXQgaXQgPGEgaHJlZj1cImh0dHA6Ly9nZXQud2ViZ2wub3JnL1wiIHN0eWxlPVwiY29sb3I6IzAwMFwiPmhlcmU8L2E+LidcbiAgICAgICAgICAgIF0uam9pbiggJ1xcbicgKTtcblxuICAgICAgICB9XG5cbiAgICAgICAgcmV0dXJuIGVsZW1lbnQ7XG5cbiAgICB9LFxuXG4gICAgYWRkR2V0V2ViR0xNZXNzYWdlOiBmdW5jdGlvbiAoIHBhcmFtZXRlcnMgKSB7XG5cbiAgICAgICAgdmFyIHBhcmVudCwgaWQsIGVsZW1lbnQ7XG5cbiAgICAgICAgcGFyYW1ldGVycyA9IHBhcmFtZXRlcnMgfHwge307XG5cbiAgICAgICAgcGFyZW50ID0gcGFyYW1ldGVycy5wYXJlbnQgIT09IHVuZGVmaW5lZCA/IHBhcmFtZXRlcnMucGFyZW50IDogZG9jdW1lbnQuYm9keTtcbiAgICAgICAgaWQgPSBwYXJhbWV0ZXJzLmlkICE9PSB1bmRlZmluZWQgPyBwYXJhbWV0ZXJzLmlkIDogJ29sZGllJztcblxuICAgICAgICBlbGVtZW50ID0gRGV0ZWN0b3IuZ2V0V2ViR0xFcnJvck1lc3NhZ2UoKTtcbiAgICAgICAgZWxlbWVudC5pZCA9IGlkO1xuXG4gICAgICAgIHBhcmVudC5hcHBlbmRDaGlsZCggZWxlbWVudCApO1xuXG4gICAgfVxuXG59O1xuXG5leHBvcnQgZGVmYXVsdCBEZXRlY3RvcjsiLCIvKipcbiAqIENyZWF0ZWQgYnkgd2Vuc2hlbmcueWFuIG9uIDUvMjMvMTYuXG4gKi9cbnZhciBIZWxwZXJDYW52YXMgPSBmdW5jdGlvbihiYXNlQ29tcG9uZW50KXtcbiAgICB2YXIgZWxlbWVudCA9IGRvY3VtZW50LmNyZWF0ZUVsZW1lbnQoJ2NhbnZhcycpO1xuICAgIGVsZW1lbnQuY2xhc3NOYW1lID0gXCJ2anMtdmlkZW8taGVscGVyLWNhbnZhc1wiO1xuICAgIHJldHVybiB7XG4gICAgICAgIGNvbnN0cnVjdG9yOiBmdW5jdGlvbiBpbml0KHBsYXllciwgb3B0aW9ucyl7XG4gICAgICAgICAgICB0aGlzLnZpZGVvRWxlbWVudCA9IG9wdGlvbnMudmlkZW87XG4gICAgICAgICAgICB0aGlzLndpZHRoID0gb3B0aW9ucy53aWR0aDtcbiAgICAgICAgICAgIHRoaXMuaGVpZ2h0ID0gb3B0aW9ucy5oZWlnaHQ7XG5cbiAgICAgICAgICAgIGVsZW1lbnQud2lkdGggPSB0aGlzLndpZHRoO1xuICAgICAgICAgICAgZWxlbWVudC5oZWlnaHQgPSB0aGlzLmhlaWdodDtcbiAgICAgICAgICAgIGVsZW1lbnQuc3R5bGUuZGlzcGxheSA9IFwibm9uZVwiO1xuICAgICAgICAgICAgb3B0aW9ucy5lbCA9IGVsZW1lbnQ7XG5cblxuICAgICAgICAgICAgdGhpcy5jb250ZXh0ID0gZWxlbWVudC5nZXRDb250ZXh0KCcyZCcpO1xuICAgICAgICAgICAgdGhpcy5jb250ZXh0LmRyYXdJbWFnZSh0aGlzLnZpZGVvRWxlbWVudCwgMCwgMCwgdGhpcy53aWR0aCwgdGhpcy5oZWlnaHQpO1xuICAgICAgICAgICAgYmFzZUNvbXBvbmVudC5jYWxsKHRoaXMsIHBsYXllciwgb3B0aW9ucyk7XG4gICAgICAgIH0sXG4gICAgICAgIFxuICAgICAgICBnZXRDb250ZXh0OiBmdW5jdGlvbiAoKSB7XG4gICAgICAgICAgcmV0dXJuIHRoaXMuY29udGV4dDsgIFxuICAgICAgICB9LFxuICAgICAgICBcbiAgICAgICAgdXBkYXRlOiBmdW5jdGlvbiAoKSB7XG4gICAgICAgICAgICB0aGlzLmNvbnRleHQuZHJhd0ltYWdlKHRoaXMudmlkZW9FbGVtZW50LCAwLCAwLCB0aGlzLndpZHRoLCB0aGlzLmhlaWdodCk7XG4gICAgICAgIH0sXG5cbiAgICAgICAgZWw6IGZ1bmN0aW9uICgpIHtcbiAgICAgICAgICAgIHJldHVybiBlbGVtZW50O1xuICAgICAgICB9XG4gICAgfVxufTtcblxuZXhwb3J0IGRlZmF1bHQgSGVscGVyQ2FudmFzOyIsIi8qKlxuICogQ3JlYXRlZCBieSB5YW53c2ggb24gNi82LzE2LlxuICovXG52YXIgTW9iaWxlQnVmZmVyaW5nID0ge1xuICAgIHByZXZfY3VycmVudFRpbWU6IDAsXG4gICAgY291bnRlcjogMCxcbiAgICBcbiAgICBpc0J1ZmZlcmluZzogZnVuY3Rpb24gKGN1cnJlbnRUaW1lKSB7XG4gICAgICAgIGlmIChjdXJyZW50VGltZSA9PSB0aGlzLnByZXZfY3VycmVudFRpbWUpIHRoaXMuY291bnRlcisrO1xuICAgICAgICBlbHNlIHRoaXMuY291bnRlciA9IDA7XG4gICAgICAgIHRoaXMucHJldl9jdXJyZW50VGltZSA9IGN1cnJlbnRUaW1lO1xuICAgICAgICBpZih0aGlzLmNvdW50ZXIgPiAxMCl7XG4gICAgICAgICAgICAvL25vdCBsZXQgY291bnRlciBvdmVyZmxvd1xuICAgICAgICAgICAgdGhpcy5jb3VudGVyID0gMTA7XG4gICAgICAgICAgICByZXR1cm4gdHJ1ZTtcbiAgICAgICAgfVxuICAgICAgICByZXR1cm4gZmFsc2U7XG4gICAgfVxufTtcblxuZXhwb3J0IGRlZmF1bHQgTW9iaWxlQnVmZmVyaW5nOyIsIi8qKlxuICogQ3JlYXRlZCBieSB5YW53c2ggb24gNC80LzE2LlxuICovXG5cbnZhciBOb3RpY2UgPSBmdW5jdGlvbihiYXNlQ29tcG9uZW50KXtcbiAgICB2YXIgZWxlbWVudCA9IGRvY3VtZW50LmNyZWF0ZUVsZW1lbnQoJ2RpdicpO1xuICAgIGVsZW1lbnQuY2xhc3NOYW1lID0gXCJ2anMtdmlkZW8tbm90aWNlLWxhYmVsXCI7XG5cbiAgICByZXR1cm4ge1xuICAgICAgICBjb25zdHJ1Y3RvcjogZnVuY3Rpb24gaW5pdChwbGF5ZXIsIG9wdGlvbnMpe1xuICAgICAgICAgICAgaWYodHlwZW9mIG9wdGlvbnMuTm90aWNlTWVzc2FnZSA9PSBcIm9iamVjdFwiKXtcbiAgICAgICAgICAgICAgICBlbGVtZW50ID0gb3B0aW9ucy5Ob3RpY2VNZXNzYWdlO1xuICAgICAgICAgICAgICAgIG9wdGlvbnMuZWwgPSBvcHRpb25zLk5vdGljZU1lc3NhZ2U7XG4gICAgICAgICAgICB9ZWxzZSBpZih0eXBlb2Ygb3B0aW9ucy5Ob3RpY2VNZXNzYWdlID09IFwic3RyaW5nXCIpe1xuICAgICAgICAgICAgICAgIGVsZW1lbnQuaW5uZXJIVE1MID0gb3B0aW9ucy5Ob3RpY2VNZXNzYWdlO1xuICAgICAgICAgICAgICAgIG9wdGlvbnMuZWwgPSBlbGVtZW50O1xuICAgICAgICAgICAgfVxuXG4gICAgICAgICAgICBiYXNlQ29tcG9uZW50LmNhbGwodGhpcywgcGxheWVyLCBvcHRpb25zKTtcbiAgICAgICAgfSxcblxuICAgICAgICBlbDogZnVuY3Rpb24gKCkge1xuICAgICAgICAgICAgcmV0dXJuIGVsZW1lbnQ7XG4gICAgICAgIH1cbiAgICB9XG59O1xuXG5leHBvcnQgZGVmYXVsdCBOb3RpY2U7IiwiLyoqXG4gKlxuICogKGMpIFdlbnNoZW5nIFlhbiA8eWFud3NoQGdtYWlsLmNvbT5cbiAqIERhdGU6IDEwLzIxLzE2XG4gKlxuICogRm9yIHRoZSBmdWxsIGNvcHlyaWdodCBhbmQgbGljZW5zZSBpbmZvcm1hdGlvbiwgcGxlYXNlIHZpZXcgdGhlIExJQ0VOU0VcbiAqIGZpbGUgdGhhdCB3YXMgZGlzdHJpYnV0ZWQgd2l0aCB0aGlzIHNvdXJjZSBjb2RlLlxuICovXG4ndXNlIHN0cmljdCc7XG5cbmltcG9ydCBCYXNlQ2FudmFzIGZyb20gJy4vQmFzZUNhbnZhcyc7XG5pbXBvcnQgVXRpbCBmcm9tICcuL1V0aWwnO1xuXG52YXIgVGhyZWVEQ2FudmFzID0gZnVuY3Rpb24gKGJhc2VDb21wb25lbnQsIFRIUkVFLCBzZXR0aW5ncyA9IHt9KXtcbiAgICB2YXIgcGFyZW50ID0gQmFzZUNhbnZhcyhiYXNlQ29tcG9uZW50LCBUSFJFRSwgc2V0dGluZ3MpO1xuICAgIHJldHVybiBVdGlsLmV4dGVuZChwYXJlbnQsIHtcbiAgICAgICAgY29uc3RydWN0b3I6IGZ1bmN0aW9uIGluaXQocGxheWVyLCBvcHRpb25zKXtcbiAgICAgICAgICAgIHBhcmVudC5jb25zdHJ1Y3Rvci5jYWxsKHRoaXMsIHBsYXllciwgb3B0aW9ucyk7XG4gICAgICAgICAgICAvL29ubHkgc2hvdyBsZWZ0IHBhcnQgYnkgZGVmYXVsdFxuICAgICAgICAgICAgdGhpcy5WUk1vZGUgPSBmYWxzZTtcbiAgICAgICAgICAgIC8vZGVmaW5lIHNjZW5lXG4gICAgICAgICAgICB0aGlzLnNjZW5lID0gbmV3IFRIUkVFLlNjZW5lKCk7XG5cbiAgICAgICAgICAgIHZhciBhc3BlY3RSYXRpbyA9IHRoaXMud2lkdGggLyB0aGlzLmhlaWdodDtcbiAgICAgICAgICAgIC8vZGVmaW5lIGNhbWVyYVxuICAgICAgICAgICAgdGhpcy5jYW1lcmFMID0gbmV3IFRIUkVFLlBlcnNwZWN0aXZlQ2FtZXJhKG9wdGlvbnMuaW5pdEZvdiwgYXNwZWN0UmF0aW8sIDEsIDIwMDApO1xuICAgICAgICAgICAgdGhpcy5jYW1lcmFMLnRhcmdldCA9IG5ldyBUSFJFRS5WZWN0b3IzKCAwLCAwLCAwICk7XG5cbiAgICAgICAgICAgIHRoaXMuY2FtZXJhUiA9IG5ldyBUSFJFRS5QZXJzcGVjdGl2ZUNhbWVyYShvcHRpb25zLmluaXRGb3YsIGFzcGVjdFJhdGlvIC8gMiwgMSwgMjAwMCk7XG4gICAgICAgICAgICB0aGlzLmNhbWVyYVIucG9zaXRpb24uc2V0KCAxMDAwLCAwLCAwICk7XG4gICAgICAgICAgICB0aGlzLmNhbWVyYVIudGFyZ2V0ID0gbmV3IFRIUkVFLlZlY3RvcjMoIDEwMDAsIDAsIDAgKTtcblxuICAgICAgICAgICAgdmFyIGdlb21ldHJ5TCA9IG5ldyBUSFJFRS5TcGhlcmVCdWZmZXJHZW9tZXRyeSg1MDAsIDYwLCA0MCkudG9Ob25JbmRleGVkKCk7XG4gICAgICAgICAgICB2YXIgZ2VvbWV0cnlSID0gbmV3IFRIUkVFLlNwaGVyZUJ1ZmZlckdlb21ldHJ5KDUwMCwgNjAsIDQwKS50b05vbkluZGV4ZWQoKTtcblxuICAgICAgICAgICAgdmFyIHV2c0wgPSBnZW9tZXRyeUwuYXR0cmlidXRlcy51di5hcnJheTtcbiAgICAgICAgICAgIHZhciBub3JtYWxzTCA9IGdlb21ldHJ5TC5hdHRyaWJ1dGVzLm5vcm1hbC5hcnJheTtcbiAgICAgICAgICAgIGZvciAoIHZhciBpID0gMDsgaSA8IG5vcm1hbHNMLmxlbmd0aCAvIDM7IGkgKysgKSB7XG4gICAgICAgICAgICAgICAgdXZzTFsgaSAqIDIgKyAxIF0gPSB1dnNMWyBpICogMiArIDEgXSAvIDI7XG4gICAgICAgICAgICB9XG5cbiAgICAgICAgICAgIHZhciB1dnNSID0gZ2VvbWV0cnlSLmF0dHJpYnV0ZXMudXYuYXJyYXk7XG4gICAgICAgICAgICB2YXIgbm9ybWFsc1IgPSBnZW9tZXRyeVIuYXR0cmlidXRlcy5ub3JtYWwuYXJyYXk7XG4gICAgICAgICAgICBmb3IgKCB2YXIgaSA9IDA7IGkgPCBub3JtYWxzUi5sZW5ndGggLyAzOyBpICsrICkge1xuICAgICAgICAgICAgICAgIHV2c1JbIGkgKiAyICsgMSBdID0gdXZzUlsgaSAqIDIgKyAxIF0gLyAyICsgMC41O1xuICAgICAgICAgICAgfVxuXG4gICAgICAgICAgICBnZW9tZXRyeUwuc2NhbGUoIC0gMSwgMSwgMSApO1xuICAgICAgICAgICAgZ2VvbWV0cnlSLnNjYWxlKCAtIDEsIDEsIDEgKTtcblxuICAgICAgICAgICAgdGhpcy5tZXNoTCA9IG5ldyBUSFJFRS5NZXNoKGdlb21ldHJ5TCxcbiAgICAgICAgICAgICAgICBuZXcgVEhSRUUuTWVzaEJhc2ljTWF0ZXJpYWwoeyBtYXA6IHRoaXMudGV4dHVyZX0pXG4gICAgICAgICAgICApO1xuXG4gICAgICAgICAgICB0aGlzLm1lc2hSID0gbmV3IFRIUkVFLk1lc2goZ2VvbWV0cnlSLFxuICAgICAgICAgICAgICAgIG5ldyBUSFJFRS5NZXNoQmFzaWNNYXRlcmlhbCh7IG1hcDogdGhpcy50ZXh0dXJlfSlcbiAgICAgICAgICAgICk7XG4gICAgICAgICAgICB0aGlzLm1lc2hSLnBvc2l0aW9uLnNldCgxMDAwLCAwLCAwKTtcblxuICAgICAgICAgICAgdGhpcy5zY2VuZS5hZGQodGhpcy5tZXNoTCk7XG5cbiAgICAgICAgICAgIGlmKG9wdGlvbnMuY2FsbGJhY2spIG9wdGlvbnMuY2FsbGJhY2soKTtcbiAgICAgICAgfSxcblxuICAgICAgICBoYW5kbGVSZXNpemU6IGZ1bmN0aW9uICgpIHtcbiAgICAgICAgICAgIHBhcmVudC5oYW5kbGVSZXNpemUuY2FsbCh0aGlzKTtcbiAgICAgICAgICAgIHZhciBhc3BlY3RSYXRpbyA9IHRoaXMud2lkdGggLyB0aGlzLmhlaWdodDtcbiAgICAgICAgICAgIGlmKCF0aGlzLlZSTW9kZSkge1xuICAgICAgICAgICAgICAgIHRoaXMuY2FtZXJhTC5hc3BlY3QgPSBhc3BlY3RSYXRpbztcbiAgICAgICAgICAgICAgICB0aGlzLmNhbWVyYUwudXBkYXRlUHJvamVjdGlvbk1hdHJpeCgpO1xuICAgICAgICAgICAgfWVsc2V7XG4gICAgICAgICAgICAgICAgYXNwZWN0UmF0aW8gLz0gMjtcbiAgICAgICAgICAgICAgICB0aGlzLmNhbWVyYUwuYXNwZWN0ID0gYXNwZWN0UmF0aW87XG4gICAgICAgICAgICAgICAgdGhpcy5jYW1lcmFSLmFzcGVjdCA9IGFzcGVjdFJhdGlvO1xuICAgICAgICAgICAgICAgIHRoaXMuY2FtZXJhTC51cGRhdGVQcm9qZWN0aW9uTWF0cml4KCk7XG4gICAgICAgICAgICAgICAgdGhpcy5jYW1lcmFSLnVwZGF0ZVByb2plY3Rpb25NYXRyaXgoKTtcbiAgICAgICAgICAgIH1cbiAgICAgICAgfSxcblxuICAgICAgICBoYW5kbGVNb3VzZVdoZWVsOiBmdW5jdGlvbihldmVudCl7XG4gICAgICAgICAgICBwYXJlbnQuaGFuZGxlTW91c2VXaGVlbChldmVudCk7XG4gICAgICAgICAgICAvLyBXZWJLaXRcbiAgICAgICAgICAgIGlmICggZXZlbnQud2hlZWxEZWx0YVkgKSB7XG4gICAgICAgICAgICAgICAgdGhpcy5jYW1lcmFMLmZvdiAtPSBldmVudC53aGVlbERlbHRhWSAqIDAuMDU7XG4gICAgICAgICAgICAgICAgLy8gT3BlcmEgLyBFeHBsb3JlciA5XG4gICAgICAgICAgICB9IGVsc2UgaWYgKCBldmVudC53aGVlbERlbHRhICkge1xuICAgICAgICAgICAgICAgIHRoaXMuY2FtZXJhTC5mb3YgLT0gZXZlbnQud2hlZWxEZWx0YSAqIDAuMDU7XG4gICAgICAgICAgICAgICAgLy8gRmlyZWZveFxuICAgICAgICAgICAgfSBlbHNlIGlmICggZXZlbnQuZGV0YWlsICkge1xuICAgICAgICAgICAgICAgIHRoaXMuY2FtZXJhTC5mb3YgKz0gZXZlbnQuZGV0YWlsICogMS4wO1xuICAgICAgICAgICAgfVxuICAgICAgICAgICAgdGhpcy5jYW1lcmFMLmZvdiA9IE1hdGgubWluKHRoaXMuc2V0dGluZ3MubWF4Rm92LCB0aGlzLmNhbWVyYUwuZm92KTtcbiAgICAgICAgICAgIHRoaXMuY2FtZXJhTC5mb3YgPSBNYXRoLm1heCh0aGlzLnNldHRpbmdzLm1pbkZvdiwgdGhpcy5jYW1lcmFMLmZvdik7XG4gICAgICAgICAgICB0aGlzLmNhbWVyYUwudXBkYXRlUHJvamVjdGlvbk1hdHJpeCgpO1xuICAgICAgICAgICAgaWYodGhpcy5WUk1vZGUpe1xuICAgICAgICAgICAgICAgIHRoaXMuY2FtZXJhUi5mb3YgPSB0aGlzLmNhbWVyYUwuZm92O1xuICAgICAgICAgICAgICAgIHRoaXMuY2FtZXJhUi51cGRhdGVQcm9qZWN0aW9uTWF0cml4KCk7XG4gICAgICAgICAgICB9XG4gICAgICAgIH0sXG5cbiAgICAgICAgZW5hYmxlVlI6IGZ1bmN0aW9uKCkge1xuICAgICAgICAgICAgdGhpcy5WUk1vZGUgPSB0cnVlO1xuICAgICAgICAgICAgdGhpcy5zY2VuZS5hZGQodGhpcy5tZXNoUik7XG4gICAgICAgICAgICB0aGlzLmhhbmRsZVJlc2l6ZSgpO1xuICAgICAgICB9LFxuXG4gICAgICAgIGRpc2FibGVWUjogZnVuY3Rpb24oKSB7XG4gICAgICAgICAgICB0aGlzLlZSTW9kZSA9IGZhbHNlO1xuICAgICAgICAgICAgdGhpcy5zY2VuZS5yZW1vdmUodGhpcy5tZXNoUik7XG4gICAgICAgICAgICB0aGlzLmhhbmRsZVJlc2l6ZSgpO1xuICAgICAgICB9LFxuXG4gICAgICAgIHJlbmRlcjogZnVuY3Rpb24oKXtcbiAgICAgICAgICAgIHBhcmVudC5yZW5kZXIuY2FsbCh0aGlzKTtcbiAgICAgICAgICAgIHRoaXMuY2FtZXJhTC50YXJnZXQueCA9IDUwMCAqIE1hdGguc2luKCB0aGlzLnBoaSApICogTWF0aC5jb3MoIHRoaXMudGhldGEgKTtcbiAgICAgICAgICAgIHRoaXMuY2FtZXJhTC50YXJnZXQueSA9IDUwMCAqIE1hdGguY29zKCB0aGlzLnBoaSApO1xuICAgICAgICAgICAgdGhpcy5jYW1lcmFMLnRhcmdldC56ID0gNTAwICogTWF0aC5zaW4oIHRoaXMucGhpICkgKiBNYXRoLnNpbiggdGhpcy50aGV0YSApO1xuICAgICAgICAgICAgdGhpcy5jYW1lcmFMLmxvb2tBdCh0aGlzLmNhbWVyYUwudGFyZ2V0KTtcblxuICAgICAgICAgICAgaWYodGhpcy5WUk1vZGUpe1xuICAgICAgICAgICAgICAgIHZhciB2aWV3UG9ydFdpZHRoID0gdGhpcy53aWR0aCAvIDIsIHZpZXdQb3J0SGVpZ2h0ID0gdGhpcy5oZWlnaHQ7XG4gICAgICAgICAgICAgICAgdGhpcy5jYW1lcmFSLnRhcmdldC54ID0gMTAwMCArIDUwMCAqIE1hdGguc2luKCB0aGlzLnBoaSApICogTWF0aC5jb3MoIHRoaXMudGhldGEgKTtcbiAgICAgICAgICAgICAgICB0aGlzLmNhbWVyYVIudGFyZ2V0LnkgPSA1MDAgKiBNYXRoLmNvcyggdGhpcy5waGkgKTtcbiAgICAgICAgICAgICAgICB0aGlzLmNhbWVyYVIudGFyZ2V0LnogPSA1MDAgKiBNYXRoLnNpbiggdGhpcy5waGkgKSAqIE1hdGguc2luKCB0aGlzLnRoZXRhICk7XG4gICAgICAgICAgICAgICAgdGhpcy5jYW1lcmFSLmxvb2tBdCggdGhpcy5jYW1lcmFSLnRhcmdldCApO1xuXG4gICAgICAgICAgICAgICAgLy8gcmVuZGVyIGxlZnQgZXllXG4gICAgICAgICAgICAgICAgdGhpcy5yZW5kZXJlci5zZXRWaWV3cG9ydCggMCwgMCwgdmlld1BvcnRXaWR0aCwgdmlld1BvcnRIZWlnaHQgKTtcbiAgICAgICAgICAgICAgICB0aGlzLnJlbmRlcmVyLnNldFNjaXNzb3IoIDAsIDAsIHZpZXdQb3J0V2lkdGgsIHZpZXdQb3J0SGVpZ2h0ICk7XG4gICAgICAgICAgICAgICAgdGhpcy5yZW5kZXJlci5yZW5kZXIoIHRoaXMuc2NlbmUsIHRoaXMuY2FtZXJhTCApO1xuXG4gICAgICAgICAgICAgICAgLy8gcmVuZGVyIHJpZ2h0IGV5ZVxuICAgICAgICAgICAgICAgIHRoaXMucmVuZGVyZXIuc2V0Vmlld3BvcnQoIHZpZXdQb3J0V2lkdGgsIDAsIHZpZXdQb3J0V2lkdGgsIHZpZXdQb3J0SGVpZ2h0ICk7XG4gICAgICAgICAgICAgICAgdGhpcy5yZW5kZXJlci5zZXRTY2lzc29yKCB2aWV3UG9ydFdpZHRoLCAwLCB2aWV3UG9ydFdpZHRoLCB2aWV3UG9ydEhlaWdodCApO1xuICAgICAgICAgICAgICAgIHRoaXMucmVuZGVyZXIucmVuZGVyKCB0aGlzLnNjZW5lLCB0aGlzLmNhbWVyYVIgKTtcbiAgICAgICAgICAgIH1lbHNle1xuICAgICAgICAgICAgICAgIHRoaXMucmVuZGVyZXIucmVuZGVyKCB0aGlzLnNjZW5lLCB0aGlzLmNhbWVyYUwgKTtcbiAgICAgICAgICAgIH1cbiAgICAgICAgfVxuICAgIH0pO1xufTtcblxuZXhwb3J0IGRlZmF1bHQgVGhyZWVEQ2FudmFzOyIsIi8qKlxuICogQ3JlYXRlZCBieSB3ZW5zaGVuZy55YW4gb24gNC80LzE2LlxuICovXG5mdW5jdGlvbiB3aGljaFRyYW5zaXRpb25FdmVudCgpe1xuICAgIHZhciB0O1xuICAgIHZhciBlbCA9IGRvY3VtZW50LmNyZWF0ZUVsZW1lbnQoJ2Zha2VlbGVtZW50Jyk7XG4gICAgdmFyIHRyYW5zaXRpb25zID0ge1xuICAgICAgICAndHJhbnNpdGlvbic6J3RyYW5zaXRpb25lbmQnLFxuICAgICAgICAnT1RyYW5zaXRpb24nOidvVHJhbnNpdGlvbkVuZCcsXG4gICAgICAgICdNb3pUcmFuc2l0aW9uJzondHJhbnNpdGlvbmVuZCcsXG4gICAgICAgICdXZWJraXRUcmFuc2l0aW9uJzond2Via2l0VHJhbnNpdGlvbkVuZCdcbiAgICB9O1xuXG4gICAgZm9yKHQgaW4gdHJhbnNpdGlvbnMpe1xuICAgICAgICBpZiggZWwuc3R5bGVbdF0gIT09IHVuZGVmaW5lZCApe1xuICAgICAgICAgICAgcmV0dXJuIHRyYW5zaXRpb25zW3RdO1xuICAgICAgICB9XG4gICAgfVxufVxuXG5mdW5jdGlvbiBtb2JpbGVBbmRUYWJsZXRjaGVjaygpIHtcbiAgICB2YXIgY2hlY2sgPSBmYWxzZTtcbiAgICAoZnVuY3Rpb24oYSl7aWYoLyhhbmRyb2lkfGJiXFxkK3xtZWVnbykuK21vYmlsZXxhdmFudGdvfGJhZGFcXC98YmxhY2tiZXJyeXxibGF6ZXJ8Y29tcGFsfGVsYWluZXxmZW5uZWN8aGlwdG9wfGllbW9iaWxlfGlwKGhvbmV8b2QpfGlyaXN8a2luZGxlfGxnZSB8bWFlbW98bWlkcHxtbXB8bW9iaWxlLitmaXJlZm94fG5ldGZyb250fG9wZXJhIG0ob2J8aW4paXxwYWxtKCBvcyk/fHBob25lfHAoaXhpfHJlKVxcL3xwbHVja2VyfHBvY2tldHxwc3B8c2VyaWVzKDR8NikwfHN5bWJpYW58dHJlb3x1cFxcLihicm93c2VyfGxpbmspfHZvZGFmb25lfHdhcHx3aW5kb3dzIGNlfHhkYXx4aWlub3xhbmRyb2lkfGlwYWR8cGxheWJvb2t8c2lsay9pLnRlc3QoYSl8fC8xMjA3fDYzMTB8NjU5MHwzZ3NvfDR0aHB8NTBbMS02XWl8Nzcwc3w4MDJzfGEgd2F8YWJhY3xhYyhlcnxvb3xzXFwtKXxhaShrb3xybil8YWwoYXZ8Y2F8Y28pfGFtb2l8YW4oZXh8bnl8eXcpfGFwdHV8YXIoY2h8Z28pfGFzKHRlfHVzKXxhdHR3fGF1KGRpfFxcLW18ciB8cyApfGF2YW58YmUoY2t8bGx8bnEpfGJpKGxifHJkKXxibChhY3xheil8YnIoZXx2KXd8YnVtYnxid1xcLShufHUpfGM1NVxcL3xjYXBpfGNjd2F8Y2RtXFwtfGNlbGx8Y2h0bXxjbGRjfGNtZFxcLXxjbyhtcHxuZCl8Y3Jhd3xkYShpdHxsbHxuZyl8ZGJ0ZXxkY1xcLXN8ZGV2aXxkaWNhfGRtb2J8ZG8oY3xwKW98ZHMoMTJ8XFwtZCl8ZWwoNDl8YWkpfGVtKGwyfHVsKXxlcihpY3xrMCl8ZXNsOHxleihbNC03XTB8b3N8d2F8emUpfGZldGN8Zmx5KFxcLXxfKXxnMSB1fGc1NjB8Z2VuZXxnZlxcLTV8Z1xcLW1vfGdvKFxcLnd8b2QpfGdyKGFkfHVuKXxoYWllfGhjaXR8aGRcXC0obXxwfHQpfGhlaVxcLXxoaShwdHx0YSl8aHAoIGl8aXApfGhzXFwtY3xodChjKFxcLXwgfF98YXxnfHB8c3x0KXx0cCl8aHUoYXd8dGMpfGlcXC0oMjB8Z298bWEpfGkyMzB8aWFjKCB8XFwtfFxcLyl8aWJyb3xpZGVhfGlnMDF8aWtvbXxpbTFrfGlubm98aXBhcXxpcmlzfGphKHR8dilhfGpicm98amVtdXxqaWdzfGtkZGl8a2VqaXxrZ3QoIHxcXC8pfGtsb258a3B0IHxrd2NcXC18a3lvKGN8ayl8bGUobm98eGkpfGxnKCBnfFxcLyhrfGx8dSl8NTB8NTR8XFwtW2Etd10pfGxpYnd8bHlueHxtMVxcLXd8bTNnYXxtNTBcXC98bWEodGV8dWl8eG8pfG1jKDAxfDIxfGNhKXxtXFwtY3J8bWUocmN8cmkpfG1pKG84fG9hfHRzKXxtbWVmfG1vKDAxfDAyfGJpfGRlfGRvfHQoXFwtfCB8b3x2KXx6eil8bXQoNTB8cDF8diApfG13YnB8bXl3YXxuMTBbMC0yXXxuMjBbMi0zXXxuMzAoMHwyKXxuNTAoMHwyfDUpfG43KDAoMHwxKXwxMCl8bmUoKGN8bSlcXC18b258dGZ8d2Z8d2d8d3QpfG5vayg2fGkpfG56cGh8bzJpbXxvcCh0aXx3dil8b3Jhbnxvd2cxfHA4MDB8cGFuKGF8ZHx0KXxwZHhnfHBnKDEzfFxcLShbMS04XXxjKSl8cGhpbHxwaXJlfHBsKGF5fHVjKXxwblxcLTJ8cG8oY2t8cnR8c2UpfHByb3h8cHNpb3xwdFxcLWd8cWFcXC1hfHFjKDA3fDEyfDIxfDMyfDYwfFxcLVsyLTddfGlcXC0pfHF0ZWt8cjM4MHxyNjAwfHJha3N8cmltOXxybyh2ZXx6byl8czU1XFwvfHNhKGdlfG1hfG1tfG1zfG55fHZhKXxzYygwMXxoXFwtfG9vfHBcXC0pfHNka1xcL3xzZShjKFxcLXwwfDEpfDQ3fG1jfG5kfHJpKXxzZ2hcXC18c2hhcnxzaWUoXFwtfG0pfHNrXFwtMHxzbCg0NXxpZCl8c20oYWx8YXJ8YjN8aXR8dDUpfHNvKGZ0fG55KXxzcCgwMXxoXFwtfHZcXC18diApfHN5KDAxfG1iKXx0MigxOHw1MCl8dDYoMDB8MTB8MTgpfHRhKGd0fGxrKXx0Y2xcXC18dGRnXFwtfHRlbChpfG0pfHRpbVxcLXx0XFwtbW98dG8ocGx8c2gpfHRzKDcwfG1cXC18bTN8bTUpfHR4XFwtOXx1cChcXC5ifGcxfHNpKXx1dHN0fHY0MDB8djc1MHx2ZXJpfHZpKHJnfHRlKXx2ayg0MHw1WzAtM118XFwtdil8dm00MHx2b2RhfHZ1bGN8dngoNTJ8NTN8NjB8NjF8NzB8ODB8ODF8ODN8ODV8OTgpfHczYyhcXC18ICl8d2ViY3x3aGl0fHdpKGcgfG5jfG53KXx3bWxifHdvbnV8eDcwMHx5YXNcXC18eW91cnx6ZXRvfHp0ZVxcLS9pLnRlc3QoYS5zdWJzdHIoMCw0KSkpY2hlY2sgPSB0cnVlfSkobmF2aWdhdG9yLnVzZXJBZ2VudHx8bmF2aWdhdG9yLnZlbmRvcnx8d2luZG93Lm9wZXJhKTtcbiAgICByZXR1cm4gY2hlY2s7XG59XG5cbmZ1bmN0aW9uIGlzSW9zKCkge1xuICAgIHJldHVybiAvaVBob25lfGlQYWR8aVBvZC9pLnRlc3QobmF2aWdhdG9yLnVzZXJBZ2VudCk7XG59XG5cbmZ1bmN0aW9uIGlzUmVhbElwaG9uZSgpIHtcbiAgICByZXR1cm4gL2lQaG9uZXxpUG9kL2kudGVzdChuYXZpZ2F0b3IucGxhdGZvcm0pO1xufVxuXG4vL2Fkb3B0IGNvZGUgZnJvbTogaHR0cHM6Ly9naXRodWIuY29tL01velZSL3ZyLXdlYi1leGFtcGxlcy9ibG9iL21hc3Rlci90aHJlZWpzLXZyLWJvaWxlcnBsYXRlL2pzL1ZSRWZmZWN0LmpzXG5mdW5jdGlvbiBmb3ZUb05EQ1NjYWxlT2Zmc2V0KCBmb3YgKSB7XG4gICAgdmFyIHB4c2NhbGUgPSAyLjAgLyAoZm92LmxlZnRUYW4gKyBmb3YucmlnaHRUYW4pO1xuICAgIHZhciBweG9mZnNldCA9IChmb3YubGVmdFRhbiAtIGZvdi5yaWdodFRhbikgKiBweHNjYWxlICogMC41O1xuICAgIHZhciBweXNjYWxlID0gMi4wIC8gKGZvdi51cFRhbiArIGZvdi5kb3duVGFuKTtcbiAgICB2YXIgcHlvZmZzZXQgPSAoZm92LnVwVGFuIC0gZm92LmRvd25UYW4pICogcHlzY2FsZSAqIDAuNTtcbiAgICByZXR1cm4geyBzY2FsZTogWyBweHNjYWxlLCBweXNjYWxlIF0sIG9mZnNldDogWyBweG9mZnNldCwgcHlvZmZzZXQgXSB9O1xufVxuXG5mdW5jdGlvbiBmb3ZQb3J0VG9Qcm9qZWN0aW9uKCBmb3YsIHJpZ2h0SGFuZGVkLCB6TmVhciwgekZhciApIHtcblxuICAgIHJpZ2h0SGFuZGVkID0gcmlnaHRIYW5kZWQgPT09IHVuZGVmaW5lZCA/IHRydWUgOiByaWdodEhhbmRlZDtcbiAgICB6TmVhciA9IHpOZWFyID09PSB1bmRlZmluZWQgPyAwLjAxIDogek5lYXI7XG4gICAgekZhciA9IHpGYXIgPT09IHVuZGVmaW5lZCA/IDEwMDAwLjAgOiB6RmFyO1xuXG4gICAgdmFyIGhhbmRlZG5lc3NTY2FsZSA9IHJpZ2h0SGFuZGVkID8gLTEuMCA6IDEuMDtcblxuICAgIC8vIHN0YXJ0IHdpdGggYW4gaWRlbnRpdHkgbWF0cml4XG4gICAgdmFyIG1vYmogPSBuZXcgVEhSRUUuTWF0cml4NCgpO1xuICAgIHZhciBtID0gbW9iai5lbGVtZW50cztcblxuICAgIC8vIGFuZCB3aXRoIHNjYWxlL29mZnNldCBpbmZvIGZvciBub3JtYWxpemVkIGRldmljZSBjb29yZHNcbiAgICB2YXIgc2NhbGVBbmRPZmZzZXQgPSBmb3ZUb05EQ1NjYWxlT2Zmc2V0KGZvdik7XG5cbiAgICAvLyBYIHJlc3VsdCwgbWFwIGNsaXAgZWRnZXMgdG8gWy13LCt3XVxuICAgIG1bMCAqIDQgKyAwXSA9IHNjYWxlQW5kT2Zmc2V0LnNjYWxlWzBdO1xuICAgIG1bMCAqIDQgKyAxXSA9IDAuMDtcbiAgICBtWzAgKiA0ICsgMl0gPSBzY2FsZUFuZE9mZnNldC5vZmZzZXRbMF0gKiBoYW5kZWRuZXNzU2NhbGU7XG4gICAgbVswICogNCArIDNdID0gMC4wO1xuXG4gICAgLy8gWSByZXN1bHQsIG1hcCBjbGlwIGVkZ2VzIHRvIFstdywrd11cbiAgICAvLyBZIG9mZnNldCBpcyBuZWdhdGVkIGJlY2F1c2UgdGhpcyBwcm9qIG1hdHJpeCB0cmFuc2Zvcm1zIGZyb20gd29ybGQgY29vcmRzIHdpdGggWT11cCxcbiAgICAvLyBidXQgdGhlIE5EQyBzY2FsaW5nIGhhcyBZPWRvd24gKHRoYW5rcyBEM0Q/KVxuICAgIG1bMSAqIDQgKyAwXSA9IDAuMDtcbiAgICBtWzEgKiA0ICsgMV0gPSBzY2FsZUFuZE9mZnNldC5zY2FsZVsxXTtcbiAgICBtWzEgKiA0ICsgMl0gPSAtc2NhbGVBbmRPZmZzZXQub2Zmc2V0WzFdICogaGFuZGVkbmVzc1NjYWxlO1xuICAgIG1bMSAqIDQgKyAzXSA9IDAuMDtcblxuICAgIC8vIFogcmVzdWx0ICh1cCB0byB0aGUgYXBwKVxuICAgIG1bMiAqIDQgKyAwXSA9IDAuMDtcbiAgICBtWzIgKiA0ICsgMV0gPSAwLjA7XG4gICAgbVsyICogNCArIDJdID0gekZhciAvICh6TmVhciAtIHpGYXIpICogLWhhbmRlZG5lc3NTY2FsZTtcbiAgICBtWzIgKiA0ICsgM10gPSAoekZhciAqIHpOZWFyKSAvICh6TmVhciAtIHpGYXIpO1xuXG4gICAgLy8gVyByZXN1bHQgKD0gWiBpbilcbiAgICBtWzMgKiA0ICsgMF0gPSAwLjA7XG4gICAgbVszICogNCArIDFdID0gMC4wO1xuICAgIG1bMyAqIDQgKyAyXSA9IGhhbmRlZG5lc3NTY2FsZTtcbiAgICBtWzMgKiA0ICsgM10gPSAwLjA7XG5cbiAgICBtb2JqLnRyYW5zcG9zZSgpO1xuXG4gICAgcmV0dXJuIG1vYmo7XG59XG5cbmZ1bmN0aW9uIGZvdlRvUHJvamVjdGlvbiggZm92LCByaWdodEhhbmRlZCwgek5lYXIsIHpGYXIgKSB7XG4gICAgdmFyIERFRzJSQUQgPSBNYXRoLlBJIC8gMTgwLjA7XG5cbiAgICB2YXIgZm92UG9ydCA9IHtcbiAgICAgICAgdXBUYW46IE1hdGgudGFuKCBmb3YudXBEZWdyZWVzICogREVHMlJBRCApLFxuICAgICAgICBkb3duVGFuOiBNYXRoLnRhbiggZm92LmRvd25EZWdyZWVzICogREVHMlJBRCApLFxuICAgICAgICBsZWZ0VGFuOiBNYXRoLnRhbiggZm92LmxlZnREZWdyZWVzICogREVHMlJBRCApLFxuICAgICAgICByaWdodFRhbjogTWF0aC50YW4oIGZvdi5yaWdodERlZ3JlZXMgKiBERUcyUkFEIClcbiAgICB9O1xuXG4gICAgcmV0dXJuIGZvdlBvcnRUb1Byb2plY3Rpb24oIGZvdlBvcnQsIHJpZ2h0SGFuZGVkLCB6TmVhciwgekZhciApO1xufVxuXG5mdW5jdGlvbiBleHRlbmQoc3VwZXJDbGFzcywgc3ViQ2xhc3NNZXRob2RzID0ge30pXG57XG4gICAgZm9yKHZhciBtZXRob2QgaW4gc3VwZXJDbGFzcyl7XG4gICAgICAgIGlmKHN1cGVyQ2xhc3MuaGFzT3duUHJvcGVydHkobWV0aG9kKSAmJiAhc3ViQ2xhc3NNZXRob2RzLmhhc093blByb3BlcnR5KG1ldGhvZCkpe1xuICAgICAgICAgICAgc3ViQ2xhc3NNZXRob2RzW21ldGhvZF0gPSBzdXBlckNsYXNzW21ldGhvZF07XG4gICAgICAgIH1cbiAgICB9XG4gICAgcmV0dXJuIHN1YkNsYXNzTWV0aG9kcztcbn1cblxuZnVuY3Rpb24gZGVlcENvcHkob2JqKSB7XG4gICAgdmFyIHRvID0ge307XG5cbiAgICBmb3IgKHZhciBuYW1lIGluIG9iailcbiAgICB7XG4gICAgICAgIHRvW25hbWVdID0gb2JqW25hbWVdO1xuICAgIH1cblxuICAgIHJldHVybiB0bztcbn1cblxuZnVuY3Rpb24gZ2V0VG91Y2hlc0Rpc3RhbmNlKHRvdWNoZXMpe1xuICAgIHJldHVybiBNYXRoLnNxcnQoXG4gICAgICAgICh0b3VjaGVzWzBdLmNsaWVudFgtdG91Y2hlc1sxXS5jbGllbnRYKSAqICh0b3VjaGVzWzBdLmNsaWVudFgtdG91Y2hlc1sxXS5jbGllbnRYKSArXG4gICAgICAgICh0b3VjaGVzWzBdLmNsaWVudFktdG91Y2hlc1sxXS5jbGllbnRZKSAqICh0b3VjaGVzWzBdLmNsaWVudFktdG91Y2hlc1sxXS5jbGllbnRZKSk7XG59XG5cbmV4cG9ydCBkZWZhdWx0IHtcbiAgICB3aGljaFRyYW5zaXRpb25FdmVudDogd2hpY2hUcmFuc2l0aW9uRXZlbnQsXG4gICAgbW9iaWxlQW5kVGFibGV0Y2hlY2s6IG1vYmlsZUFuZFRhYmxldGNoZWNrLFxuICAgIGlzSW9zOiBpc0lvcyxcbiAgICBpc1JlYWxJcGhvbmU6IGlzUmVhbElwaG9uZSxcbiAgICBmb3ZUb1Byb2plY3Rpb246IGZvdlRvUHJvamVjdGlvbixcbiAgICBleHRlbmQ6IGV4dGVuZCxcbiAgICBkZWVwQ29weTogZGVlcENvcHksXG4gICAgZ2V0VG91Y2hlc0Rpc3RhbmNlOiBnZXRUb3VjaGVzRGlzdGFuY2Vcbn07IiwiLyoqXG4gKiBDcmVhdGVkIGJ5IHlhbndzaCBvbiA4LzEzLzE2LlxuICovXG5cbnZhciBWUkJ1dHRvbiA9IGZ1bmN0aW9uKEJ1dHRvbkNvbXBvbmVudCl7XG4gICAgcmV0dXJuIHtcbiAgICAgICAgY29uc3RydWN0b3I6IGZ1bmN0aW9uIGluaXQocGxheWVyLCBvcHRpb25zKXtcbiAgICAgICAgICAgIEJ1dHRvbkNvbXBvbmVudC5jYWxsKHRoaXMsIHBsYXllciwgb3B0aW9ucyk7XG4gICAgICAgIH0sXG5cbiAgICAgICAgYnVpbGRDU1NDbGFzczogZnVuY3Rpb24oKSB7XG4gICAgICAgICAgICByZXR1cm4gYHZqcy1WUi1jb250cm9sICR7QnV0dG9uQ29tcG9uZW50LnByb3RvdHlwZS5idWlsZENTU0NsYXNzLmNhbGwodGhpcyl9YDtcbiAgICAgICAgfSxcblxuICAgICAgICBoYW5kbGVDbGljazogZnVuY3Rpb24gKCkge1xuICAgICAgICAgICAgdmFyIGNhbnZhcyA9IHRoaXMucGxheWVyKCkuZ2V0Q2hpbGQoXCJDYW52YXNcIik7XG4gICAgICAgICAgICAoIWNhbnZhcy5WUk1vZGUpPyBjYW52YXMuZW5hYmxlVlIoKSA6IGNhbnZhcy5kaXNhYmxlVlIoKTtcbiAgICAgICAgICAgIChjYW52YXMuVlJNb2RlKT8gdGhpcy5hZGRDbGFzcyhcImVuYWJsZVwiKSA6IHRoaXMucmVtb3ZlQ2xhc3MoXCJlbmFibGVcIik7XG4gICAgICAgICAgICAoY2FudmFzLlZSTW9kZSk/ICB0aGlzLnBsYXllcigpLnRyaWdnZXIoJ1ZSTW9kZU9uJyk6ICB0aGlzLnBsYXllcigpLnRyaWdnZXIoJ1ZSTW9kZU9mZicpO1xuICAgICAgICB9LFxuXG4gICAgICAgIGNvbnRyb2xUZXh0XzogXCJWUlwiXG4gICAgfVxufTtcblxuZXhwb3J0IGRlZmF1bHQgVlJCdXR0b247IiwiLyoqXG4gKiBDcmVhdGVkIGJ5IHlhbndzaCBvbiA0LzMvMTYuXG4gKi9cbid1c2Ugc3RyaWN0JztcblxuaW1wb3J0IHV0aWwgZnJvbSAnLi9saWIvVXRpbCc7XG5pbXBvcnQgRGV0ZWN0b3IgZnJvbSAnLi9saWIvRGV0ZWN0b3InO1xuXG5jb25zdCBydW5Pbk1vYmlsZSA9ICh0eXBlb2Ygd2luZG93ICE9PSBcInVuZGVmaW5lZFwiKT8gdXRpbC5tb2JpbGVBbmRUYWJsZXRjaGVjaygpIDogZmFsc2U7XG5cbi8vIERlZmF1bHQgb3B0aW9ucyBmb3IgdGhlIHBsdWdpbi5cbmNvbnN0IGRlZmF1bHRzID0ge1xuICAgIGNsaWNrQW5kRHJhZzogcnVuT25Nb2JpbGUsXG4gICAgc2hvd05vdGljZTogdHJ1ZSxcbiAgICBOb3RpY2VNZXNzYWdlOiBcIlBsZWFzZSB1c2UgeW91ciBtb3VzZSBkcmFnIGFuZCBkcm9wIHRoZSB2aWRlby5cIixcbiAgICBhdXRvSGlkZU5vdGljZTogMzAwMCxcbiAgICAvL2xpbWl0IHRoZSB2aWRlbyBzaXplIHdoZW4gdXNlciBzY3JvbGwuXG4gICAgc2Nyb2xsYWJsZTogdHJ1ZSxcbiAgICBpbml0Rm92OiA3NSxcbiAgICBtYXhGb3Y6IDEwNSxcbiAgICBtaW5Gb3Y6IDUxLFxuICAgIC8vaW5pdGlhbCBwb3NpdGlvbiBmb3IgdGhlIHZpZGVvXG4gICAgaW5pdExhdDogMCxcbiAgICBpbml0TG9uOiAtMTgwLFxuICAgIC8vQSBmbG9hdCB2YWx1ZSBiYWNrIHRvIGNlbnRlciB3aGVuIG1vdXNlIG91dCB0aGUgY2FudmFzLiBUaGUgaGlnaGVyLCB0aGUgZmFzdGVyLlxuICAgIHJldHVyblN0ZXBMYXQ6IDAuNSxcbiAgICByZXR1cm5TdGVwTG9uOiAyLFxuICAgIGJhY2tUb1ZlcnRpY2FsQ2VudGVyOiAhcnVuT25Nb2JpbGUsXG4gICAgYmFja1RvSG9yaXpvbkNlbnRlcjogIXJ1bk9uTW9iaWxlLFxuICAgIGNsaWNrVG9Ub2dnbGU6IGZhbHNlLFxuXG4gICAgLy9saW1pdCB2aWV3YWJsZSB6b29tXG4gICAgbWluTGF0OiAtODUsXG4gICAgbWF4TGF0OiA4NSxcblxuICAgIG1pbkxvbjogLUluZmluaXR5LFxuICAgIG1heExvbjogSW5maW5pdHksXG5cbiAgICB2aWRlb1R5cGU6IFwiZXF1aXJlY3Rhbmd1bGFyXCIsXG5cbiAgICByb3RhdGVYOiAwLFxuICAgIHJvdGF0ZVk6IDAsXG4gICAgcm90YXRlWjogMCxcblxuICAgIGF1dG9Nb2JpbGVPcmllbnRhdGlvbjogZmFsc2UsXG4gICAgbW9iaWxlVmlicmF0aW9uVmFsdWU6IChydW5Pbk1vYmlsZSAmJiB1dGlsLmlzSW9zKCkpPyAwLjAyMiA6IDEsXG5cbiAgICBWUkVuYWJsZTogdHJ1ZSxcbiAgICBWUkdhcERlZ3JlZTogMi41LFxuXG4gICAgY2xvc2VQYW5vcmFtYTogZmFsc2UsXG5cbiAgICBoZWxwZXJDYW52YXM6IHt9LFxuXG4gICAgZHVhbEZpc2g6IHtcbiAgICAgICAgd2lkdGg6IDE5MjAsXG4gICAgICAgIGhlaWdodDogMTA4MCxcbiAgICAgICAgY2lyY2xlMToge1xuICAgICAgICAgICAgeDogMC4yNDA2MjUsXG4gICAgICAgICAgICB5OiAwLjU1MzcwNCxcbiAgICAgICAgICAgIHJ4OiAwLjIzMzMzLFxuICAgICAgICAgICAgcnk6IDAuNDMxNDgsXG4gICAgICAgICAgICBjb3Zlclg6IDAuOTEzLFxuICAgICAgICAgICAgY292ZXJZOiAwLjlcbiAgICAgICAgfSxcbiAgICAgICAgY2lyY2xlMjoge1xuICAgICAgICAgICAgeDogMC43NTcyOTIsXG4gICAgICAgICAgICB5OiAwLjU1MzcwNCxcbiAgICAgICAgICAgIHJ4OiAwLjIzMjI5MixcbiAgICAgICAgICAgIHJ5OiAwLjQyOTYyOTYsXG4gICAgICAgICAgICBjb3Zlclg6IDAuOTEzLFxuICAgICAgICAgICAgY292ZXJZOiAwLjkzMDhcbiAgICAgICAgfVxuICAgIH1cbn07XG5cbmZ1bmN0aW9uIHBsYXllclJlc2l6ZShwbGF5ZXIpe1xuICAgIHZhciBjYW52YXMgPSBwbGF5ZXIuZ2V0Q2hpbGQoJ0NhbnZhcycpO1xuICAgIHJldHVybiBmdW5jdGlvbiAoKSB7XG4gICAgICAgIHBsYXllci5lbCgpLnN0eWxlLndpZHRoID0gd2luZG93LmlubmVyV2lkdGggKyBcInB4XCI7XG4gICAgICAgIHBsYXllci5lbCgpLnN0eWxlLmhlaWdodCA9IHdpbmRvdy5pbm5lckhlaWdodCArIFwicHhcIjtcbiAgICAgICAgY2FudmFzLmhhbmRsZVJlc2l6ZSgpO1xuICAgIH07XG59XG5cbmZ1bmN0aW9uIGZ1bGxzY3JlZW5PbklPUyhwbGF5ZXIsIGNsaWNrRm4pIHtcbiAgICB2YXIgcmVzaXplRm4gPSBwbGF5ZXJSZXNpemUocGxheWVyKTtcbiAgICBwbGF5ZXIuY29udHJvbEJhci5mdWxsc2NyZWVuVG9nZ2xlLm9mZihcInRhcFwiLCBjbGlja0ZuKTtcbiAgICBwbGF5ZXIuY29udHJvbEJhci5mdWxsc2NyZWVuVG9nZ2xlLm9uKFwidGFwXCIsIGZ1bmN0aW9uIGZ1bGxzY3JlZW4oKSB7XG4gICAgICAgIHZhciBjYW52YXMgPSBwbGF5ZXIuZ2V0Q2hpbGQoJ0NhbnZhcycpO1xuICAgICAgICBpZighcGxheWVyLmlzRnVsbHNjcmVlbigpKXtcbiAgICAgICAgICAgIC8vc2V0IHRvIGZ1bGxzY3JlZW5cbiAgICAgICAgICAgIHBsYXllci5pc0Z1bGxzY3JlZW4odHJ1ZSk7XG4gICAgICAgICAgICBwbGF5ZXIuZW50ZXJGdWxsV2luZG93KCk7XG4gICAgICAgICAgICByZXNpemVGbigpO1xuICAgICAgICAgICAgd2luZG93LmFkZEV2ZW50TGlzdGVuZXIoXCJkZXZpY2Vtb3Rpb25cIiwgcmVzaXplRm4pO1xuICAgICAgICB9ZWxzZXtcbiAgICAgICAgICAgIHBsYXllci5pc0Z1bGxzY3JlZW4oZmFsc2UpO1xuICAgICAgICAgICAgcGxheWVyLmV4aXRGdWxsV2luZG93KCk7XG4gICAgICAgICAgICBwbGF5ZXIuZWwoKS5zdHlsZS53aWR0aCA9IFwiXCI7XG4gICAgICAgICAgICBwbGF5ZXIuZWwoKS5zdHlsZS5oZWlnaHQgPSBcIlwiO1xuICAgICAgICAgICAgY2FudmFzLmhhbmRsZVJlc2l6ZSgpO1xuICAgICAgICAgICAgd2luZG93LnJlbW92ZUV2ZW50TGlzdGVuZXIoXCJkZXZpY2Vtb3Rpb25cIiwgcmVzaXplRm4pO1xuICAgICAgICB9XG4gICAgfSk7XG59XG5cbi8qKlxuICogRnVuY3Rpb24gdG8gaW52b2tlIHdoZW4gdGhlIHBsYXllciBpcyByZWFkeS5cbiAqXG4gKiBUaGlzIGlzIGEgZ3JlYXQgcGxhY2UgZm9yIHlvdXIgcGx1Z2luIHRvIGluaXRpYWxpemUgaXRzZWxmLiBXaGVuIHRoaXNcbiAqIGZ1bmN0aW9uIGlzIGNhbGxlZCwgdGhlIHBsYXllciB3aWxsIGhhdmUgaXRzIERPTSBhbmQgY2hpbGQgY29tcG9uZW50c1xuICogaW4gcGxhY2UuXG4gKlxuICogQGZ1bmN0aW9uIG9uUGxheWVyUmVhZHlcbiAqIEBwYXJhbSAgICB7UGxheWVyfSBwbGF5ZXJcbiAqIEBwYXJhbSAgICB7T2JqZWN0fSBbb3B0aW9ucz17fV1cbiAqL1xuY29uc3Qgb25QbGF5ZXJSZWFkeSA9IChwbGF5ZXIsIG9wdGlvbnMsIHNldHRpbmdzKSA9PiB7XG4gICAgcGxheWVyLmFkZENsYXNzKCd2anMtcGFub3JhbWEnKTtcbiAgICBpZighRGV0ZWN0b3Iud2ViZ2wpe1xuICAgICAgICBQb3B1cE5vdGlmaWNhdGlvbihwbGF5ZXIsIHtcbiAgICAgICAgICAgIE5vdGljZU1lc3NhZ2U6IERldGVjdG9yLmdldFdlYkdMRXJyb3JNZXNzYWdlKCksXG4gICAgICAgICAgICBhdXRvSGlkZU5vdGljZTogb3B0aW9ucy5hdXRvSGlkZU5vdGljZVxuICAgICAgICB9KTtcbiAgICAgICAgaWYob3B0aW9ucy5jYWxsYmFjayl7XG4gICAgICAgICAgICBvcHRpb25zLmNhbGxiYWNrKCk7XG4gICAgICAgIH1cbiAgICAgICAgcmV0dXJuO1xuICAgIH1cbiAgICBwbGF5ZXIuYWRkQ2hpbGQoJ0NhbnZhcycsIHV0aWwuZGVlcENvcHkob3B0aW9ucykpO1xuICAgIHZhciBjYW52YXMgPSBwbGF5ZXIuZ2V0Q2hpbGQoJ0NhbnZhcycpO1xuICAgIGlmKHJ1bk9uTW9iaWxlKXtcbiAgICAgICAgdmFyIHZpZGVvRWxlbWVudCA9IHNldHRpbmdzLmdldFRlY2gocGxheWVyKTtcbiAgICAgICAgaWYodXRpbC5pc1JlYWxJcGhvbmUoKSl7XG4gICAgICAgICAgICBsZXQgbWFrZVZpZGVvUGxheWFibGVJbmxpbmUgPSByZXF1aXJlKCdpcGhvbmUtaW5saW5lLXZpZGVvJyk7XG4gICAgICAgICAgICAvL2lvcyAxMCBzdXBwb3J0IHBsYXkgdmlkZW8gaW5saW5lXG4gICAgICAgICAgICB2aWRlb0VsZW1lbnQuc2V0QXR0cmlidXRlKFwicGxheXNpbmxpbmVcIiwgXCJcIik7XG4gICAgICAgICAgICBtYWtlVmlkZW9QbGF5YWJsZUlubGluZSh2aWRlb0VsZW1lbnQsIHRydWUpO1xuICAgICAgICB9XG4gICAgICAgIGlmKHV0aWwuaXNJb3MoKSl7XG4gICAgICAgICAgICBmdWxsc2NyZWVuT25JT1MocGxheWVyLCBzZXR0aW5ncy5nZXRGdWxsc2NyZWVuVG9nZ2xlQ2xpY2tGbihwbGF5ZXIpKTtcbiAgICAgICAgfVxuICAgICAgICBwbGF5ZXIuYWRkQ2xhc3MoXCJ2anMtcGFub3JhbWEtbW9iaWxlLWlubGluZS12aWRlb1wiKTtcbiAgICAgICAgcGxheWVyLnJlbW92ZUNsYXNzKFwidmpzLXVzaW5nLW5hdGl2ZS1jb250cm9sc1wiKTtcbiAgICAgICAgY2FudmFzLnBsYXlPbk1vYmlsZSgpO1xuICAgIH1cbiAgICBpZihvcHRpb25zLnNob3dOb3RpY2Upe1xuICAgICAgICBwbGF5ZXIub24oXCJwbGF5aW5nXCIsIGZ1bmN0aW9uKCl7XG4gICAgICAgICAgICBQb3B1cE5vdGlmaWNhdGlvbihwbGF5ZXIsIHV0aWwuZGVlcENvcHkob3B0aW9ucykpO1xuICAgICAgICB9KTtcbiAgICB9XG4gICAgaWYob3B0aW9ucy5WUkVuYWJsZSl7XG4gICAgICAgIHBsYXllci5jb250cm9sQmFyLmFkZENoaWxkKCdWUkJ1dHRvbicsIHt9LCBwbGF5ZXIuY29udHJvbEJhci5jaGlsZHJlbigpLmxlbmd0aCAtIDEpO1xuICAgIH1cbiAgICBjYW52YXMuaGlkZSgpO1xuICAgIHBsYXllci5vbihcInBsYXlcIiwgZnVuY3Rpb24gKCkge1xuICAgICAgICBjYW52YXMuc2hvdygpO1xuICAgIH0pO1xuICAgIHBsYXllci5vbihcImZ1bGxzY3JlZW5jaGFuZ2VcIiwgZnVuY3Rpb24gKCkge1xuICAgICAgICBjYW52YXMuaGFuZGxlUmVzaXplKCk7XG4gICAgfSk7XG4gICAgaWYob3B0aW9ucy5jYWxsYmFjaykgb3B0aW9ucy5jYWxsYmFjaygpO1xufTtcblxuY29uc3QgUG9wdXBOb3RpZmljYXRpb24gPSAocGxheWVyLCBvcHRpb25zID0ge1xuICAgIE5vdGljZU1lc3NhZ2U6IFwiXCJcbn0pID0+IHtcbiAgICB2YXIgbm90aWNlID0gcGxheWVyLmFkZENoaWxkKCdOb3RpY2UnLCBvcHRpb25zKTtcblxuICAgIGlmKG9wdGlvbnMuYXV0b0hpZGVOb3RpY2UgPiAwKXtcbiAgICAgICAgc2V0VGltZW91dChmdW5jdGlvbiAoKSB7XG4gICAgICAgICAgICBub3RpY2UuYWRkQ2xhc3MoXCJ2anMtdmlkZW8tbm90aWNlLWZhZGVPdXRcIik7XG4gICAgICAgICAgICB2YXIgdHJhbnNpdGlvbkV2ZW50ID0gdXRpbC53aGljaFRyYW5zaXRpb25FdmVudCgpO1xuICAgICAgICAgICAgdmFyIGhpZGUgPSBmdW5jdGlvbiAoKSB7XG4gICAgICAgICAgICAgICAgbm90aWNlLmhpZGUoKTtcbiAgICAgICAgICAgICAgICBub3RpY2UucmVtb3ZlQ2xhc3MoXCJ2anMtdmlkZW8tbm90aWNlLWZhZGVPdXRcIik7XG4gICAgICAgICAgICAgICAgbm90aWNlLm9mZih0cmFuc2l0aW9uRXZlbnQsIGhpZGUpO1xuICAgICAgICAgICAgfTtcbiAgICAgICAgICAgIG5vdGljZS5vbih0cmFuc2l0aW9uRXZlbnQsIGhpZGUpO1xuICAgICAgICB9LCBvcHRpb25zLmF1dG9IaWRlTm90aWNlKTtcbiAgICB9XG59O1xuXG5jb25zdCBwbHVnaW4gPSBmdW5jdGlvbihzZXR0aW5ncyA9IHt9KXtcbiAgICAvKipcbiAgICAgKiBBIHZpZGVvLmpzIHBsdWdpbi5cbiAgICAgKlxuICAgICAqIEluIHRoZSBwbHVnaW4gZnVuY3Rpb24sIHRoZSB2YWx1ZSBvZiBgdGhpc2AgaXMgYSB2aWRlby5qcyBgUGxheWVyYFxuICAgICAqIGluc3RhbmNlLiBZb3UgY2Fubm90IHJlbHkgb24gdGhlIHBsYXllciBiZWluZyBpbiBhIFwicmVhZHlcIiBzdGF0ZSBoZXJlLFxuICAgICAqIGRlcGVuZGluZyBvbiBob3cgdGhlIHBsdWdpbiBpcyBpbnZva2VkLiBUaGlzIG1heSBvciBtYXkgbm90IGJlIGltcG9ydGFudFxuICAgICAqIHRvIHlvdTsgaWYgbm90LCByZW1vdmUgdGhlIHdhaXQgZm9yIFwicmVhZHlcIiFcbiAgICAgKlxuICAgICAqIEBmdW5jdGlvbiBwYW5vcmFtYVxuICAgICAqIEBwYXJhbSAgICB7T2JqZWN0fSBbb3B0aW9ucz17fV1cbiAgICAgKiAgICAgICAgICAgQW4gb2JqZWN0IG9mIG9wdGlvbnMgbGVmdCB0byB0aGUgcGx1Z2luIGF1dGhvciB0byBkZWZpbmUuXG4gICAgICovXG4gICAgY29uc3QgdmlkZW9UeXBlcyA9IFtcImVxdWlyZWN0YW5ndWxhclwiLCBcImZpc2hleWVcIiwgXCIzZFZpZGVvXCIsIFwiZHVhbF9maXNoZXllXCJdO1xuICAgIGNvbnN0IHBhbm9yYW1hID0gZnVuY3Rpb24ob3B0aW9ucykge1xuICAgICAgICBpZihzZXR0aW5ncy5tZXJnZU9wdGlvbikgb3B0aW9ucyA9IHNldHRpbmdzLm1lcmdlT3B0aW9uKGRlZmF1bHRzLCBvcHRpb25zKTtcbiAgICAgICAgaWYodHlwZW9mIHNldHRpbmdzLl9pbml0ID09PSBcInVuZGVmaW5lZFwiIHx8IHR5cGVvZiBzZXR0aW5ncy5faW5pdCAhPT0gXCJmdW5jdGlvblwiKSB7XG4gICAgICAgICAgICBjb25zb2xlLmVycm9yKFwicGx1Z2luIG11c3QgaW1wbGVtZW50IGluaXQgZnVuY3Rpb24oKS5cIik7XG4gICAgICAgICAgICByZXR1cm47XG4gICAgICAgIH1cbiAgICAgICAgaWYodmlkZW9UeXBlcy5pbmRleE9mKG9wdGlvbnMudmlkZW9UeXBlKSA9PSAtMSkgb3B0aW9ucy52aWRlb1R5cGUgPSBkZWZhdWx0cy52aWRlb1R5cGU7XG4gICAgICAgIHNldHRpbmdzLl9pbml0KG9wdGlvbnMpO1xuICAgICAgICAvKiBpbXBsZW1lbnQgY2FsbGJhY2sgZnVuY3Rpb24gd2hlbiB2aWRlb2pzIGlzIHJlYWR5ICovXG4gICAgICAgIHRoaXMucmVhZHkoKCkgPT4ge1xuICAgICAgICAgICAgb25QbGF5ZXJSZWFkeSh0aGlzLCBvcHRpb25zLCBzZXR0aW5ncyk7XG4gICAgICAgIH0pO1xuICAgIH07XG5cbi8vIEluY2x1ZGUgdGhlIHZlcnNpb24gbnVtYmVyLlxuICAgIHBhbm9yYW1hLlZFUlNJT04gPSAnMC4xLjcnO1xuXG4gICAgcmV0dXJuIHBhbm9yYW1hO1xufTtcblxuZXhwb3J0IGRlZmF1bHQgcGx1Z2luO1xuIiwiJ3VzZSBzdHJpY3QnO1xuXG5pbXBvcnQgQ2FudmFzICBmcm9tICcuL2xpYi9DYW52YXMnO1xuaW1wb3J0IFRocmVlRENhbnZhcyBmcm9tICcuL2xpYi9UaHJlZUNhbnZhcyc7XG5pbXBvcnQgTm90aWNlICBmcm9tICcuL2xpYi9Ob3RpY2UnO1xuaW1wb3J0IEhlbHBlckNhbnZhcyBmcm9tICcuL2xpYi9IZWxwZXJDYW52YXMnO1xuaW1wb3J0IFZSQnV0dG9uIGZyb20gJy4vbGliL1ZSQnV0dG9uJztcbmltcG9ydCBwYW5vcmFtYSBmcm9tICcuL3BsdWdpbic7XG5cbmZ1bmN0aW9uIGdldFRlY2gocGxheWVyKSB7XG4gICAgcmV0dXJuIHBsYXllci50ZWNoPyBwbGF5ZXIudGVjaC5lbCgpOlxuICAgICAgICBwbGF5ZXIuaC5lbCgpO1xufVxuXG5mdW5jdGlvbiBnZXRGdWxsc2NyZWVuVG9nZ2xlQ2xpY2tGbihwbGF5ZXIpIHtcbiAgICByZXR1cm4gcGxheWVyLmNvbnRyb2xCYXIuZnVsbHNjcmVlblRvZ2dsZS5vbkNsaWNrIHx8IHBsYXllci5jb250cm9sQmFyLmZ1bGxzY3JlZW5Ub2dnbGUudTtcbn1cblxudmFyIGNvbXBvbmVudCA9IHZpZGVvanMuQ29tcG9uZW50O1xudmFyIGNvbXBhdGlhYmxlSW5pdGlhbEZ1bmN0aW9uID0gZnVuY3Rpb24gKHBsYXllciwgb3B0aW9ucykge1xuICAgIHRoaXMuY29uc3RydWN0b3IocGxheWVyLCBvcHRpb25zKTtcbn07XG5cbnZhciBub3RpY2UgPSBOb3RpY2UoY29tcG9uZW50KTtcbm5vdGljZS5pbml0ID0gY29tcGF0aWFibGVJbml0aWFsRnVuY3Rpb247XG52aWRlb2pzLk5vdGljZSA9IGNvbXBvbmVudC5leHRlbmQobm90aWNlKTtcblxudmFyIGhlbHBlckNhbnZhcyA9IEhlbHBlckNhbnZhcyhjb21wb25lbnQpO1xuaGVscGVyQ2FudmFzLmluaXQgPSBjb21wYXRpYWJsZUluaXRpYWxGdW5jdGlvbjtcbnZpZGVvanMuSGVscGVyQ2FudmFzID0gY29tcG9uZW50LmV4dGVuZChoZWxwZXJDYW52YXMpO1xuXG52YXIgYnV0dG9uID0gdmlkZW9qcy5CdXR0b247XG52YXIgdnJCdG4gPSBWUkJ1dHRvbihidXR0b24pO1xudnJCdG4uaW5pdCA9IGNvbXBhdGlhYmxlSW5pdGlhbEZ1bmN0aW9uO1xudnJCdG4ub25DbGljayA9IHZyQnRuLnUgPSB2ckJ0bi5oYW5kbGVDbGljaztcbnZyQnRuLmJ1dHRvblRleHQgPSB2ckJ0bi50YSA9IHZyQnRuLmNvbnRyb2xUZXh0XztcbnZyQnRuLlQgPSBmdW5jdGlvbiAoKSB7XG4gICAgcmV0dXJuIGB2anMtVlItY29udHJvbCAke2J1dHRvbi5wcm90b3R5cGUuVC5jYWxsKHRoaXMpfWA7XG59O1xudmlkZW9qcy5WUkJ1dHRvbiA9IGJ1dHRvbi5leHRlbmQodnJCdG4pO1xuXG4vLyBSZWdpc3RlciB0aGUgcGx1Z2luIHdpdGggdmlkZW8uanMuXG52aWRlb2pzLnBsdWdpbigncGFub3JhbWEnLCBwYW5vcmFtYSh7XG4gICAgX2luaXQ6IGZ1bmN0aW9uIChvcHRpb25zKSB7XG4gICAgICAgIHZhciBjYW52YXMgPSAob3B0aW9ucy52aWRlb1R5cGUgIT09IFwiM2RWaWRlb1wiKT9cbiAgICAgICAgICAgIENhbnZhcyhjb21wb25lbnQsIHdpbmRvdy5USFJFRSwge1xuICAgICAgICAgICAgICAgIGdldFRlY2g6IGdldFRlY2hcbiAgICAgICAgICAgIH0pIDpcbiAgICAgICAgICAgIFRocmVlRENhbnZhcyhjb21wb25lbnQsIHdpbmRvdy5USFJFRSwge1xuICAgICAgICAgICAgICAgIGdldFRlY2g6IGdldFRlY2hcbiAgICAgICAgICAgIH0pO1xuICAgICAgICBjYW52YXMuaW5pdCA9IGNvbXBhdGlhYmxlSW5pdGlhbEZ1bmN0aW9uO1xuICAgICAgICB2aWRlb2pzLkNhbnZhcyA9IGNvbXBvbmVudC5leHRlbmQoY2FudmFzKTtcbiAgICB9LFxuICAgIG1lcmdlT3B0aW9uOiBmdW5jdGlvbiAoZGVmYXVsdHMsIG9wdGlvbnMpIHtcbiAgICAgICAgcmV0dXJuIHZpZGVvanMudXRpbC5tZXJnZU9wdGlvbnMoZGVmYXVsdHMsIG9wdGlvbnMpO1xuICAgIH0sXG4gICAgZ2V0VGVjaDogZ2V0VGVjaCxcbiAgICBnZXRGdWxsc2NyZWVuVG9nZ2xlQ2xpY2tGbjogZ2V0RnVsbHNjcmVlblRvZ2dsZUNsaWNrRm5cbn0pKTsiXX0=
