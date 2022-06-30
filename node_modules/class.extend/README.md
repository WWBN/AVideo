class.extend
============
copy/paste node package implementation of John Resig's simple javascript inheritance, http://ejohn.org/blog/simple-javascript-inheritance

Install
-------
    npm install class.extend

Usage
-------
    var Class = require('class.extend');

    var Person = Class.extend({
      init: function(isDancing){
        this.dancing = isDancing;
      },
      dance: function(){
        return this.dancing;
      }
    });
     
    var Ninja = Person.extend({
      init: function(){
        this._super( false );
      },
      dance: function(){
        // Call the inherited version of dance()
        return this._super();
      },
      swingSword: function(){
        return true;
      }
    });
     
    var p = new Person(true);
     
    var n = new Ninja();
