'use strict';

var introEl = document.querySelector('.intro');
var cp = new CircleProgress(document.querySelector('.progress'), {max: 100, value: 60, textFormat: 'value', animation: 'easeInOutCubic'});

cp.graph.paper.svg.setAttribute('viewBox', '-10 -10 120 120');


setTimeout(function() {
	introEl.dataset.demo = 'responsive';
}, 3000);

setTimeout(function() {
	introEl.dataset.demo = 'animated';
	setTimeout(function() {
		cp.value = 90;
		setTimeout(function() {
			cp.value = 20;
		}, 800);
		setTimeout(function() {
			cp.value = 60;
		}, 1600);
	}, 700);
}, 5700);

setTimeout(function() {
	introEl.dataset.demo = 'stylable';
	var i = 0;
	var interv = setInterval(function() {
		i += 1;
		if(i === 6) {
			cp.textFormat = 'valueOnCircle';
			cp.graph.text.el.style.transition = 'none';
			cp.graph.text.el.style.transform = 'none';
			clearInterval(interv);
			return;
		}
		cp.el.dataset.style = i;
		if(i === 5) {
			updateGraph();
			setTimeout(function() {
				introEl.dataset.demo = 'accessible';
			}, 700);
		}
	}, 1600);

	function updateGraph() {
		cp._updateGraph();
		if(i < 6) {
			requestAnimationFrame(updateGraph);
		}
	}
}, 8500);

setTimeout(function() {
	introEl.dataset.demo = 'finished';
}, 19000);
