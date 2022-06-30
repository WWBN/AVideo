require("should");
var Class = require('../lib/class.js');

describe('Mode Parser', function() {
  it('should parse 0777',function() {
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
    p.dance().should.be.true;
     
    var n = new Ninja();
    n.dance().should.be.false;
    n.swingSword().should.be.true;
     
    // Should all be true
    (p instanceof Person).should.be.true;
    (p instanceof Class).should.be.true;
    (n instanceof Ninja).should.be.true;
    (n instanceof Person).should.be.true;
    (n instanceof Class).should.be.true;
  });
});
