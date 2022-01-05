export default class States {
  static getState(name) {
    if (!name) {
      return;
    }

    if (States.states_ && States.states_[name]) {
      return States.states_[name];
    }
  }

  static registerState(name, StateToRegister) {
    if (typeof name !== 'string' || !name) {
      throw new Error(`Illegal state name, "${name}"; must be a non-empty string.`);
    }

    if (!States.states_) {
      States.states_ = {};
    }

    States.states_[name] = StateToRegister;

    return StateToRegister;
  }
}
