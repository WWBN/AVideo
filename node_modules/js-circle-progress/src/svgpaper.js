'use strict';

var svgpaper = (function() {

var paper,
	paperproto,
	element,
	elementproto;


/**
 * Create new paper holding a new SVG element
 * @param  {(HTMLElement|string)} container      Container element or selector string
 * @param  {(number|string)}      width          SVG width
 * @param  {(number|string)}      height         SVG height
 * @param  {Document}             [doc=document] HTML document. Defaults to current document
 * @return {Object}                              The paper
 */
paper = function(container, width, height, doc) {
	var svg, me;

	doc = doc || document;

	me = Object.create(paperproto);

	if(typeof container === 'string') container = doc.querySelector(container);

	if(!container) return;

	svg = doc.createElementNS('http://www.w3.org/2000/svg', 'svg');
	svg.setAttribute('version', '1.1');
	if(width) svg.setAttribute('width', width);
	if(height) svg.setAttribute('height', height);
	if(width && height) svg.setAttribute('viewBox', '0 0 ' + width + ' ' + height);
	container.appendChild(svg);

	me.svg = svg;
	return me;
};

paperproto = {
	/**
	 * Create a new SVG element
	 * @param  {string}     name      Element name
	 * @param  {Object}     attrs     Map of attributes
	 * @param  {string}     content   Element content
	 * @param  {SVGElement} [parent]  An element to append to. Defaults to the root SVG element
	 * @return {object}               Element
	 */
	element: function(name, attrs, content, parent) {
		var el;

		el = element(this, name, attrs, parent);
		if(content) el.el.innerHTML = content;

		return el;
	},
};

/**
 * General purpose element maker
 * @param  {Object}     paper    SVG Paper
 * @param  {string}     name     Element tag name
 * @param  {Object}     attrs    Attributes for the element
 * @param  {SVGElement} [parent] Another SVG Element to append the
 * @param  {Document}   [doc]    Document
 * @return {Object}              Element
 */
element = function(paper, name, attrs, parent, doc) {
	var attrNames, me;

	doc = doc || document;

	me = Object.create(elementproto);

	me.el = doc.createElementNS('http://www.w3.org/2000/svg', name);
	me.attr(attrs);

	(parent ? parent.el || parent : paper.svg).appendChild(me.el);

	return me;
};

elementproto = {
	/**
	 * Set an attribute to a value
	 * @param  {string} name  Attribute name
	 * @param  {*}      value Attribute value
	 * @return {object}       The element
	 * *//**
	 * Set attributes
	 * @param {object} attrs  Map of name - values
	 * @return {object}       The element
	 */
	attr: function(name, value) {
		if(name === undefined) return this;
		if(typeof name === 'object') {
			for(var key in name) {
				this.attr(key, name[key]);
			}
			return this;
		}
		if(value === undefined)
			return this.el.getAttributeNS(null, name);
		this.el.setAttribute(name, value);
		return this;
	},
	/**
	 * Set content (innerHTML) for the element
	 * @param  {string} content String of SVG
	 * @return {object}         The element
	 */
	content: function(content) {
		this.el.innerHTML = content;
		return this;
	}
};


// Export paper.
return paper;

}());
