// Source: https://github.com/rogodec/svg-innerhtml-polyfill

(function () {
  try {
    if (typeof SVGElement === 'undefined' || Boolean(SVGElement.prototype.innerHTML)) {
      return;
    }
  } catch (e) {
      return;
  }

  function serializeNode (node) {
    switch (node.nodeType) {
      case 1:
        return serializeElementNode(node);
      case 3:
        return serializeTextNode(node);
      case 8:
        return serializeCommentNode(node);
    }
  }

  function serializeTextNode (node) {
      return node.textContent.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
  }

  function serializeCommentNode (node) {
      return '<!--' + node.nodeValue + '-->'
  }

  function serializeElementNode (node) {
      var output = '';

      output += '<' + node.tagName;

      if (node.hasAttributes()) {
          [].forEach.call(node.attributes, function(attrNode) {
              output += ' ' + attrNode.name + '="' + attrNode.value + '"'
          })
      }

      output += '>';

      if (node.hasChildNodes()) {
          [].forEach.call(node.childNodes, function(childNode) {
              output += serializeNode(childNode);
          });
      }

      output += '</' + node.tagName + '>';

      return output;
  }

  Object.defineProperty(SVGElement.prototype, 'innerHTML', {
    get: function () {
      var output = '';

      [].forEach.call(this.childNodes, function(childNode) {
          output += serializeNode(childNode);
      });

      return output;
    },
    set: function (markup) {
      while (this.firstChild) {
        this.removeChild(this.firstChild);
      }

      try {
        var dXML = new DOMParser();
        dXML.async = false;

        var sXML = '<svg xmlns=\'http://www.w3.org/2000/svg\' xmlns:xlink=\'http://www.w3.org/1999/xlink\'>' + markup + '</svg>';
        var svgDocElement = dXML.parseFromString(sXML, 'text/xml').documentElement;

        [].forEach.call(svgDocElement.childNodes, function(childNode) {
            this.appendChild(this.ownerDocument.importNode(childNode, true));
        }.bind(this));
      } catch (e) {
          throw new Error('Error parsing markup string');
      }
    }
  });

  Object.defineProperty(SVGElement.prototype, 'innerSVG', {
    get: function () {
      return this.innerHTML;
    },
    set: function (markup) {
      this.innerHTML = markup;
    }
  });

})();
