/*
 *
 *
 *
 * Copyright (c) 2016 Tigran Sargsyan
 * Licensed under the CC-BY-NC-SA-4.0 license.
 */

'use strict';

jQuery.widget('tl.circleProgress', {
	options: jQuery.extend({}, CircleProgress.defaults),


	_create: function() {
		this.circleProgress = new CircleProgress(this.element[0], this.options);
		this.options = this.circleProgress._attrs;
	},

	_destroy: function() {

	},

	_setOptions(opts) {
		this.circleProgress.attr(opts);
		// this._super(Object.keys(opts).reduce((opts, key) => {
		// 	opts[key] = this.circleProgress.attr(key);
		// 	return opts;
		// }, {}));
	},

	value(val) {
		if(val === undefined) return this.options.value;
		this._setOptions({value: val});
	},

	min(val) {
		if(val === undefined) return this.options.min;
		this._setOptions({min: val});
	},

	max(val) {
		if(val === undefined) return this.options.max;
		this._setOptions({max: val});
	},
});

jQuery.fn.circleProgress.defaults = CircleProgress.defaults;
