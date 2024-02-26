let jsdom = require('jsdom')
let {JSDOM} = jsdom
let exposedProperties = ['window', 'navigator']
let dom = new JSDOM('')

global.window = dom.window
global.navigator = {
  userAgent: 'node.js'
}
global.Blob = function () {}
global.URL = {createObjectURL: function () {}}

Object.keys(dom.window).forEach(property => {
  if (typeof global[property] === 'undefined') {
    exposedProperties.push(property)
    global[property] = dom.window[property]
  }
})
