class InterceptorsStorage {
  constructor() {
    this.typeToInterceptorsMap_ = new Map();
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
    this.typeToInterceptorsMap_ = new Map();
    this.enabled_ = false;
  }

  addInterceptor(type, interceptor) {
    if (!this.typeToInterceptorsMap_.has(type)) {
      this.typeToInterceptorsMap_.set(type, new Set());
    }

    const interceptorsSet = this.typeToInterceptorsMap_.get(type);

    if (interceptorsSet.has(interceptor)) {
      // already have this interceptor
      return false;
    }

    interceptorsSet.add(interceptor);

    return true;
  }

  removeInterceptor(type, interceptor) {
    const interceptorsSet = this.typeToInterceptorsMap_.get(type);

    if (interceptorsSet && interceptorsSet.has(interceptor)) {
      interceptorsSet.delete(interceptor);
      return true;
    }

    return false;
  }

  clearInterceptorsByType(type) {
    const interceptorsSet = this.typeToInterceptorsMap_.get(type);

    if (!interceptorsSet) {
      return false;
    }

    this.typeToInterceptorsMap_.delete(type);
    this.typeToInterceptorsMap_.set(type, new Set());

    return true;
  }

  clear() {
    if (!this.typeToInterceptorsMap_.size) {
      return false;
    }

    this.typeToInterceptorsMap_ = new Map();

    return true;
  }

  getForType(type) {
    return this.typeToInterceptorsMap_.get(type) || new Set();
  }

  execute(type, payload) {
    const interceptors = this.getForType(type);

    for (const interceptor of interceptors) {
      try {
        payload = interceptor(payload);
      } catch (e) {
        //ignore
      }
    }

    return payload;
  }
}


module.exports = InterceptorsStorage;
