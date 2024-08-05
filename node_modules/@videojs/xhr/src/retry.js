class RetryManager {
  constructor() {
    this.maxAttempts_ = 1;
    this.delayFactor_ = 0.1;
    this.fuzzFactor_ = 0.1;
    this.initialDelay_ = 1000;
    this.enabled_ = false;
  }

  getIsEnabled() {
    return this.enabled_;
  }

  enable() {
    this.enabled_ = true;
  }

  disable() {
    this.enabled_ = false;
  }

  reset() {
    this.maxAttempts_ = 1;
    this.delayFactor_ = 0.1;
    this.fuzzFactor_ = 0.1;
    this.initialDelay_ = 1000;
    this.enabled_ = false;
  }

  getMaxAttempts() {
    return this.maxAttempts_;
  }

  setMaxAttempts(maxAttempts) {
    this.maxAttempts_ = maxAttempts;
  }

  getDelayFactor() {
    return this.delayFactor_;
  }

  setDelayFactor(delayFactor) {
    this.delayFactor_ = delayFactor;
  }

  getFuzzFactor() {
    return this.fuzzFactor_;
  }

  setFuzzFactor(fuzzFactor) {
    this.fuzzFactor_ = fuzzFactor;
  }

  getInitialDelay() {
    return this.initialDelay_;
  }

  setInitialDelay(initialDelay) {
    this.initialDelay_ = initialDelay;
  }

  createRetry({ maxAttempts, delayFactor, fuzzFactor, initialDelay } = {}) {
    return new Retry({
      maxAttempts: maxAttempts || this.maxAttempts_,
      delayFactor: delayFactor || this.delayFactor_,
      fuzzFactor: fuzzFactor || this.fuzzFactor_,
      initialDelay: initialDelay || this.initialDelay_,
    });
  }
}

class Retry {
  constructor(options) {
    this.maxAttempts_ = options.maxAttempts;
    this.delayFactor_ = options.delayFactor;
    this.fuzzFactor_ = options.fuzzFactor;
    this.currentDelay_ = options.initialDelay;

    this.currentAttempt_ = 1;
  }

  moveToNextAttempt() {
    this.currentAttempt_++;
    const delayDelta = this.currentDelay_ * this.delayFactor_;
    this.currentDelay_ = this.currentDelay_ + delayDelta;
  }

  shouldRetry() {
    return this.currentAttempt_ < this.maxAttempts_;
  }

  getCurrentDelay() {
    return this.currentDelay_;
  }

  getCurrentMinPossibleDelay() {
    return (1 - this.fuzzFactor_) * this.currentDelay_;
  }

  getCurrentMaxPossibleDelay() {
    return (1 + this.fuzzFactor_) * this.currentDelay_;
  }

  /**
   * For example fuzzFactor is 0.1
   * This means ±10% deviation
   * So if we have delay as 1000
   * This function can generate any value from 900 to 1100
   */
  getCurrentFuzzedDelay() {
    const lowValue = this.getCurrentMinPossibleDelay();
    const highValue = this.getCurrentMaxPossibleDelay();

    return lowValue + Math.random() * (highValue - lowValue);
  }
}

module.exports = RetryManager;
