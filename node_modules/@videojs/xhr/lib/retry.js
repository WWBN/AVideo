"use strict";

var RetryManager = /*#__PURE__*/function () {
  function RetryManager() {
    this.maxAttempts_ = 1;
    this.delayFactor_ = 0.1;
    this.fuzzFactor_ = 0.1;
    this.initialDelay_ = 1000;
    this.enabled_ = false;
  }

  var _proto = RetryManager.prototype;

  _proto.getIsEnabled = function getIsEnabled() {
    return this.enabled_;
  };

  _proto.enable = function enable() {
    this.enabled_ = true;
  };

  _proto.disable = function disable() {
    this.enabled_ = false;
  };

  _proto.reset = function reset() {
    this.maxAttempts_ = 1;
    this.delayFactor_ = 0.1;
    this.fuzzFactor_ = 0.1;
    this.initialDelay_ = 1000;
    this.enabled_ = false;
  };

  _proto.getMaxAttempts = function getMaxAttempts() {
    return this.maxAttempts_;
  };

  _proto.setMaxAttempts = function setMaxAttempts(maxAttempts) {
    this.maxAttempts_ = maxAttempts;
  };

  _proto.getDelayFactor = function getDelayFactor() {
    return this.delayFactor_;
  };

  _proto.setDelayFactor = function setDelayFactor(delayFactor) {
    this.delayFactor_ = delayFactor;
  };

  _proto.getFuzzFactor = function getFuzzFactor() {
    return this.fuzzFactor_;
  };

  _proto.setFuzzFactor = function setFuzzFactor(fuzzFactor) {
    this.fuzzFactor_ = fuzzFactor;
  };

  _proto.getInitialDelay = function getInitialDelay() {
    return this.initialDelay_;
  };

  _proto.setInitialDelay = function setInitialDelay(initialDelay) {
    this.initialDelay_ = initialDelay;
  };

  _proto.createRetry = function createRetry(_temp) {
    var _ref = _temp === void 0 ? {} : _temp,
        maxAttempts = _ref.maxAttempts,
        delayFactor = _ref.delayFactor,
        fuzzFactor = _ref.fuzzFactor,
        initialDelay = _ref.initialDelay;

    return new Retry({
      maxAttempts: maxAttempts || this.maxAttempts_,
      delayFactor: delayFactor || this.delayFactor_,
      fuzzFactor: fuzzFactor || this.fuzzFactor_,
      initialDelay: initialDelay || this.initialDelay_
    });
  };

  return RetryManager;
}();

var Retry = /*#__PURE__*/function () {
  function Retry(options) {
    this.maxAttempts_ = options.maxAttempts;
    this.delayFactor_ = options.delayFactor;
    this.fuzzFactor_ = options.fuzzFactor;
    this.currentDelay_ = options.initialDelay;
    this.currentAttempt_ = 1;
  }

  var _proto2 = Retry.prototype;

  _proto2.moveToNextAttempt = function moveToNextAttempt() {
    this.currentAttempt_++;
    var delayDelta = this.currentDelay_ * this.delayFactor_;
    this.currentDelay_ = this.currentDelay_ + delayDelta;
  };

  _proto2.shouldRetry = function shouldRetry() {
    return this.currentAttempt_ < this.maxAttempts_;
  };

  _proto2.getCurrentDelay = function getCurrentDelay() {
    return this.currentDelay_;
  };

  _proto2.getCurrentMinPossibleDelay = function getCurrentMinPossibleDelay() {
    return (1 - this.fuzzFactor_) * this.currentDelay_;
  };

  _proto2.getCurrentMaxPossibleDelay = function getCurrentMaxPossibleDelay() {
    return (1 + this.fuzzFactor_) * this.currentDelay_;
  }
  /**
   * For example fuzzFactor is 0.1
   * This means ±10% deviation
   * So if we have delay as 1000
   * This function can generate any value from 900 to 1100
   */
  ;

  _proto2.getCurrentFuzzedDelay = function getCurrentFuzzedDelay() {
    var lowValue = this.getCurrentMinPossibleDelay();
    var highValue = this.getCurrentMaxPossibleDelay();
    return lowValue + Math.random() * (highValue - lowValue);
  };

  return Retry;
}();

module.exports = RetryManager;