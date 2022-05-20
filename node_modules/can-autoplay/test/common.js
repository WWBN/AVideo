module.exports = {
  mockPlay: function (implementation) {
    window.HTMLMediaElement.prototype.play = () => implementation()
  }
}
