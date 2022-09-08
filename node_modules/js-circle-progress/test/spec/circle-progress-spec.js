/* jshint jquery: true */
/* global describe, it, expect, CircleProgress */

'use strict';

describe('Circle Progress', function() {
	const container = document.createElement('div');
	container.style.marginBottom = '30px';
	document.body.appendChild(container);

	const cp = new CircleProgress(container, {
		min: 0,
		max: 100,
	});

	beforeEach(() => {
		cp.min = -1000
		cp.max = 1000
		cp.constrain = false
	})

	it('sets value', function() {
		cp.min = 0;
		cp.max = 10;
		cp.value = 5;
		expect(cp.value).toBe(5);
		cp.value = '6';
		expect(cp.value).toBe(6);
	});

	it('sets min', function() {
		cp.max = 10;
		cp.min = 1;
		expect(cp.min).toBe(1);
		cp.min = '2';
		expect(cp.min).toBe(2);
	});

	it('sets max', function() {
		cp.min = 1;
		cp.max = 9;
		expect(cp.max).toBe(9);
		cp.max = '10';
		expect(cp.max).toBe(10);
	});

	it('can set negative min and max and constrain value between them', function() {
		cp.min = -10;
		cp.max = 0;
		expect(cp.min).toBe(-10);
		expect(cp.max).toBe(0);
		cp.max = -11;
		expect(cp.max).toBe(0);
		cp.constrain = true;
		cp.value = -11;
		expect(cp.value).toBe(-10);
		cp.value = 1;
		expect(cp.value).toBe(0);
	});

	it('does not accept min greater than max and max less than min', function() {
		cp.min = 2;
		cp.max = 10;
		cp.min = 11;
		expect(cp.min).toBe(2);
		cp.max = 1;
		expect(cp.max).toBe(10);
	});

	it('sets constrain', function() {
		cp.constrain = true;
		expect(cp.constrain).toBe(true);
		cp.constrain = false;
		expect(cp.constrain).toBe(false);
	});

	it('can constrain value between min and max', function() {
		cp.constrain = true;
		cp.min = 2;
		cp.max = 10;
		cp.value = -2;
		expect(cp.value).toBe(2);
		cp.value = 20;
		expect(cp.value).toBe(10);
		cp.max = 8;
		expect(cp.value).toBe(8);
		cp.value = 3;
		cp.min = 4;
		expect(cp.value).toBe(4);
	});

	it('can extend value outside min and max, if constrain is set to false', function() {
		cp.constrain = false;
		cp.min = 2;
		cp.max = 10;
		cp.value = -2;
		expect(cp.value).toBe(-2);
		cp.value = 20;
		expect(cp.value).toBe(20);
	});

	it('sets start angle', function() {
		cp.startAngle = 45;
		expect(cp.startAngle).toBe(45);
		cp.startAngle = '90';
		expect(cp.startAngle).toBe(90);
	});

	it('should constrain start angle between 0 and 360', function() {
		cp.startAngle = -30;
		expect(cp.startAngle).toBe(0);
		cp.startAngle = 400;
		expect(cp.startAngle).toBe(360);
	});

	it('uses attr method to set and retrieve properties', function() {
		cp.attr('min', '0');
		cp.attr('max', '10');
		expect(cp.max).toBe(10);
		expect(cp.attr('max')).toBe(10);
		cp.attr('value', '7');
		expect(cp.value).toBe(7);
		cp.attr({min: 1, value: '8'});
		expect(cp.attr('min')).toBe(1);
		expect(cp.attr('value')).toBe(8);
	});

	// it('goes clockwise and anticlockwise', function() {
	// 	cp.clockwise = true;
	// 	expect()
	// });
});


describe('Circle Progress jQuery plugin', function() {
	const container = document.createElement('div');
	container.style.marginBottom = '30px';
	document.body.appendChild(container);
	const $cp = $(container);

	$cp.circleProgress({
		min: 0,
		max: 100,
	});

	it('sets value', function() {
		$cp.circleProgress('option', 'min', 0);
		$cp.circleProgress('option', 'max', 10);
		$cp.circleProgress('option', 'value', 5);
		expect($cp.circleProgress('option', 'value')).toBe(5);
		$cp.circleProgress('option', 'value', '6');
		expect($cp.circleProgress('option', 'value')).toBe(6);
		$cp.circleProgress('value', '7');
		expect($cp.circleProgress('value')).toBe(7);
	});

	it('sets min', function() {
		$cp.circleProgress('option', 'min', 10);
		$cp.circleProgress('option', 'min', 1);
		expect($cp.circleProgress('option', 'min')).toBe(1);
		$cp.circleProgress('option', 'min', '2');
		expect($cp.circleProgress('option', 'min')).toBe(2);
		$cp.circleProgress('min', '3');
		expect($cp.circleProgress('min')).toBe(3);
	});

	it('sets max', function() {
		$cp.circleProgress('option', 'min', 0);
		$cp.circleProgress('option', 'max', 9);
		expect($cp.circleProgress('option', 'max')).toBe(9);
		$cp.circleProgress('option', 'max', '10');
		expect($cp.circleProgress('option', 'max')).toBe(10);
		$cp.circleProgress('max', '11');
		expect($cp.circleProgress('max')).toBe(11);
	});

	it('does not accept min greater than max and max less than min', function() {
		$cp.circleProgress('option', 'min', 2);
		$cp.circleProgress('option', 'max', 10);
		$cp.circleProgress('option', 'min', 11);
		expect($cp.circleProgress('option', 'min')).toBe(2);
		$cp.circleProgress('option', 'max', 1);
		expect($cp.circleProgress('option', 'max')).toBe(10);
	});

	it('sets constrain', function() {
		$cp.circleProgress('option', 'constrain', true);
		expect($cp.circleProgress('option', 'constrain')).toBe(true);
		$cp.circleProgress('option', 'constrain', false);
		expect($cp.circleProgress('option', 'constrain')).toBe(false);
		$cp.circleProgress('option', 'constrain', 1);
		expect($cp.circleProgress('option', 'constrain')).toBe(true);
		$cp.circleProgress('option', 'constrain', '');
		expect($cp.circleProgress('option', 'constrain')).toBe(false);
	});

	it('can constrain value between min and max', function() {
		$cp.circleProgress('option', 'constrain', true);
		$cp.circleProgress('option', 'min', 2);
		$cp.circleProgress('option', 'max', 10);
		$cp.circleProgress('option', 'value', -2);
		expect($cp.circleProgress('option', 'value')).toBe(2);
		$cp.circleProgress('option', 'value', 20);
		expect($cp.circleProgress('option', 'value')).toBe(10);
		$cp.circleProgress('option', 'max', 8);
		expect($cp.circleProgress('option', 'value')).toBe(8);
		$cp.circleProgress('option', 'value', 3);
		$cp.circleProgress('option', 'min', 4);
		expect($cp.circleProgress('option', 'value')).toBe(4);
	});

	it('can extend value outside min and max, if constrain is set to false', function() {
		$cp.circleProgress('option', 'constrain', false);
		$cp.circleProgress('option', 'min', 2);
		$cp.circleProgress('option', 'max', 10);
		$cp.circleProgress('option', 'value', -2);
		expect($cp.circleProgress('option', 'value')).toBe(-2);
		$cp.circleProgress('option', 'value', 20);
		expect($cp.circleProgress('option', 'value')).toBe(20);
	});

	it('sets start angle', function() {
		$cp.circleProgress('option', 'startAngle', 45);
		expect($cp.circleProgress('option', 'startAngle')).toBe(45);
		$cp.circleProgress('option', 'startAngle', '90');
		expect($cp.circleProgress('option', 'startAngle')).toBe(90);
	});

	it('should constrain start angle between 0 and 360', function() {
		$cp.circleProgress('option', 'startAngle', -30);
		expect($cp.circleProgress('option', 'startAngle')).toBe(0);
		$cp.circleProgress('option', 'startAngle', 400);
		expect($cp.circleProgress('option', 'startAngle')).toBe(360);
	});

	// it('goes clockwise and anticlockwise', function() {
	// 	cp.clockwise = true;
	// 	expect()
	// });
});
