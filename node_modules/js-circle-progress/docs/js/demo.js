'use strict';

// IE11 polyfills
if (!Element.prototype.matches)
	Element.prototype.matches = Element.prototype.msMatchesSelector ||
		Element.prototype.webkitMatchesSelector;

if (!Element.prototype.closest) {
	Element.prototype.closest = function(s) {
		var el = this;
		if (!document.documentElement.contains(el)) return null;
		do {
			if (el.matches(s)) return el;
			el = el.parentElement || el.parentNode;
		} while (el !== null && el.nodeType === 1);
		return null;
	};
}


// Examples
var options = [
	{max: 100, value: 60, textFormat: 'percent'},
	{max: 100, value: 60},
	{max: 100, value: 60},
	{max: 100, value: 60, textFormat: 'vertical'},
	{max: 100, value: 60, textFormat: 'vertical'},
	{max: 100, value: 60, textFormat: 'vertical'},
	{max: 12, value: 9, textFormat: function(value, max) {
		return value + ' dots';
	}},
	{max: 100, value: 40, textFormat: 'valueOnCircle'},
	{max: 100, value: 40, textFormat: 'percent'},
	{max: 100, value: 80, textFormat: 'percent'},
	{max: 100, value: 60, textFormat: 'percent'},
	{max: 100, value: 75, textFormat: 'percent', startAngle: -90},
	{max: 4, value: 3, textFormat: 'vertical', clockwise: false, animation: 'none'},
];

options.forEach(function(opts, i) {
	var exampleEl = document.querySelector('.example:nth-child(' + (i + 1) + ')');
	new CircleProgress(exampleEl.querySelector('.progress'), opts);
	// $(exampleEl.querySelector('.progress')).circleProgress(opts);
	var optsStr = '{\n';
	for(var name in opts) {
		var value = opts[name];
		if(typeof value === 'string') {
			value = '\'' + value + '\'';
		}
		optsStr += '\t' + name + ': ' + value + ',\n';
	}
	optsStr += '}';
	exampleEl.querySelector('.variant-vanilla code').innerText = 'new CircleProgress(\'.progress\', ' + optsStr + ');';
	exampleEl.querySelector('.variant-jquery code').innerText = '$(\'.progress\').circleProgress(' + optsStr + ');';
	exampleEl.querySelector('.example-figure').insertAdjacentHTML('beforeend', '<div class="controls">' +
		'<label><input type="number" name="min" value="0">min</label>' +
		'<label><input type="number" name="value" value="' + opts.value + '">value</label>' +
		'<label><input type="number" name="max" value="' + opts.max + '">max</label>' +
	'</div>');
});




hljs.initHighlightingOnLoad();

Array.prototype.slice.call(document.querySelectorAll('.select-variant')).forEach(function(btn) {
	btn.addEventListener('click', function(e) {
		e.preventDefault();
		if(this.dataset.variant === 'vanilla') {
			document.body.classList.remove('show-variant-jquery');
			document.body.classList.add('show-variant-vanilla');
		} else {
			document.body.classList.remove('show-variant-vanilla');
			document.body.classList.add('show-variant-jquery');
		}
	});
});


Array.prototype.slice.call(document.querySelectorAll('.code')).forEach(function(el) {
	el.addEventListener('click', function() {
		var r = document.createRange();
		r.selectNode(this);
		var s = document.getSelection();
		s.empty();
		s.addRange(r);
	});
});


document.body.addEventListener('change', function(e) {
	if(e.target.nodeName !== 'INPUT') return;
	var key = e.target.name;
	var exampleEl = e.target.closest('.example');
	var cp = exampleEl.querySelector('.progress').circleProgress;
	cp[key] = e.target.value;
	Array.prototype.slice.call(exampleEl.querySelectorAll('.controls input')).forEach(function(input) {
		input.value = cp[input.name];
	});
});
