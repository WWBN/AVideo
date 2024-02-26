'use strict';


/**
 * Change any value using an animation easing function.
 * @param  {string}   Easing function.
 * @param  {number}   The initial value
 * @param  {number}   Change in value
 * @param  {number}   Animation duration
 * @param  {Function} Callback to be called on each iteration. The callback is passed one argument: current value.
 */
const animator = function(easing, startValue, valueChange, dur, cb) {
	const easeFunc = typeof easing === 'string' ? animator.easings[easing] : easing;
	let tStart;

	const frame = function(t) {
		if(!tStart) tStart = t;
		t -= tStart;
		t = Math.min(t, dur);
		const curVal = easeFunc(t, startValue, valueChange, dur);
		cb(curVal);
		if(t < dur) requestAnimationFrame(frame);
		else cb(startValue + valueChange);
	};

	requestAnimationFrame(frame);
};


/**
 * Map of easings' strings to functions
 * Easing functions from http://gizma.com/easing/
 * @type {Object}
 */
animator.easings = {
	linear:  function (t, b, c, d) {
		return c*t/d + b;
	},

	easeInQuad: function (t, b, c, d) {
		t /= d;
		return c*t*t + b;
	},
	easeOutQuad: function (t, b, c, d) {
		t /= d;
		return -c * t*(t-2) + b;
	},
	easeInOutQuad: function (t, b, c, d) {
		t /= d/2;
		if (t < 1) return c/2*t*t + b;
		t--;
		return -c/2 * (t*(t-2) - 1) + b;
	},

	easeInCubic: function (t, b, c, d) {
		t /= d;
		return c*t*t*t + b;
	},
	easeOutCubic: function (t, b, c, d) {
		t /= d;
		t--;
		return c*(t*t*t + 1) + b;
	},
	easeInOutCubic: function (t, b, c, d) {
		t /= d/2;
		if (t < 1) return c/2*t*t*t + b;
		t -= 2;
		return c/2*(t*t*t + 2) + b;
	},

	easeInQuart: function (t, b, c, d) {
		t /= d;
		return c*t*t*t*t + b;
	},
	easeOutQuart: function (t, b, c, d) {
		t /= d;
		t--;
		return -c * (t*t*t*t - 1) + b;
	},
	easeInOutQuart: function (t, b, c, d) {
		t /= d/2;
		if (t < 1) return c/2*t*t*t*t + b;
		t -= 2;
		return -c/2 * (t*t*t*t - 2) + b;
	},

	easeInQuint: function (t, b, c, d) {
		t /= d;
		return c*t*t*t*t*t + b;
	},
	easeOutQuint: function (t, b, c, d) {
		t /= d;
		t--;
		return c*(t*t*t*t*t + 1) + b;
	},
	easeInOutQuint: function (t, b, c, d) {
		t /= d/2;
		if (t < 1) return c/2*t*t*t*t*t + b;
		t -= 2;
		return c/2*(t*t*t*t*t + 2) + b;
	},

	easeInSine: function (t, b, c, d) {
		return -c * Math.cos(t/d * (Math.PI/2)) + c + b;
	},
	easeOutSine: function (t, b, c, d) {
		return c * Math.sin(t/d * (Math.PI/2)) + b;
	},
	easeInOutSine: function (t, b, c, d) {
		return -c/2 * (Math.cos(Math.PI*t/d) - 1) + b;
	},

	easeInExpo: function (t, b, c, d) {
		return c * Math.pow( 2, 10 * (t/d - 1) ) + b;
	},
	easeOutExpo: function (t, b, c, d) {
		return c * ( -Math.pow( 2, -10 * t/d ) + 1 ) + b;
	},
	easeInOutExpo: function (t, b, c, d) {
		t /= d/2;
		if (t < 1) return c/2 * Math.pow( 2, 10 * (t - 1) ) + b;
		t--;
		return c/2 * ( -Math.pow( 2, -10 * t) + 2 ) + b;
	},

	easeInCirc: function (t, b, c, d) {
		t /= d;
		return -c * (Math.sqrt(1 - t*t) - 1) + b;
	},
	easeOutCirc: function (t, b, c, d) {
		t /= d;
		t--;
		return c * Math.sqrt(1 - t*t) + b;
	},
	easeInOutCirc: function (t, b, c, d) {
		t /= d/2;
		if (t < 1) return -c/2 * (Math.sqrt(1 - t*t) - 1) + b;
		t -= 2;
		return c/2 * (Math.sqrt(1 - t*t) + 1) + b;
	},
};
