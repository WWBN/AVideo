import QUnit from 'qunit';
import {obtainUsPrivacyString, getCurrentUspString} from '../../src/usPrivacy.js';
import sinon from 'sinon';

QUnit.module('US Privacy - obtainUsPrivacyString()', () => {

  QUnit.test('should call callback with uspString when __uspapi is available', (assert) => {
    const done = assert.async();
    const uspData = {uspString: '1YNN'};

    const customWindow = {
      __uspapi: (command, version, cb) => {
        cb(uspData, true);
      }
    };

    obtainUsPrivacyString((result) => {
      assert.equal(result, uspData.uspString, 'uspString is returned');
      done();
    }, customWindow);
  });

  QUnit.test('should call callback with null when __uspapi is available but call is unsuccessful', (assert) => {
    const done = assert.async();

    const customWindow = {
      __uspapi: (command, version, cb) => {
        cb(null, false);
      }
    };

    obtainUsPrivacyString((result) => {
      assert.equal(result, null, 'null is returned');
      done();
    }, customWindow);
  });

  QUnit.test('should call callback with uspString when __uspapi is not available and received message is valid', (assert) => {
    const done = assert.async();
    const uspData = {uspString: '1YNN'};
    const uniqueId = 'testUniqueId'.toString(36).substring(2);

    const successfulMessageEvent = {
      data: {
        __uspapiReturn: {
          callId: uniqueId,
          returnValue: uspData,
          success: true
        }
      }
    };

    const eventListeners = {};

    const customWindow = {
      addEventListener: (event, handler) => {
        eventListeners[event] = handler;
      },
      removeEventListener: (event) => {
        delete eventListeners[event];
      }
    };

    customWindow.parent = {
      frames: {
        __uspapiLocator: true
      },
      postMessage: () => {
        setTimeout(() => {
          eventListeners.message(successfulMessageEvent);
        }, 0);
      }
    };

    customWindow.top = customWindow.parent;

    sinon.stub(Math, 'random').returns('testUniqueId');

    obtainUsPrivacyString((result) => {
      assert.equal(result, uspData.uspString, 'uspString is returned');
      Math.random.restore();
      done();
    }, customWindow);
  });

  QUnit.test('should call callback with uspString when __uspapi is not available and received message is not valid', (assert) => {
    const done = assert.async();
    const uniqueId = 'testUniqueId'.toString(36).substring(2);

    const unsuccessfulMessageEvent = {
      data: {
        __uspapiReturn: {
          callId: uniqueId,
          returnValue: {},
          success: false
        }
      }
    };

    const eventListeners = {};

    const customWindow = {
      addEventListener: (event, handler) => {
        eventListeners[event] = handler;
      },
      removeEventListener: (event) => {
        delete eventListeners[event];
      }
    };

    customWindow.parent = {
      frames: {
        __uspapiLocator: true
      },
      postMessage: () => {
        setTimeout(() => {
          eventListeners.message(unsuccessfulMessageEvent);
        }, 0);
      }
    };

    customWindow.top = customWindow.parent;

    sinon.stub(Math, 'random').returns('testUniqueId');

    obtainUsPrivacyString((result) => {
      assert.equal(result, null, 'null is returned');
      Math.random.restore();
      done();
    }, customWindow);
  });
});

QUnit.test('should call callback with null when __uspapi and __uspapiLocator are not available in any window', (assert) => {
  const done = assert.async();

  const customWindow = {
    addEventListener: () => {},
    removeEventListener: () => {},
    postMessage: () => {}
  };

  customWindow.parent = customWindow;
  customWindow.top = customWindow;

  obtainUsPrivacyString((result) => {
    assert.equal(result, null, 'null is returned when no __uspapi or __uspapiLocator are present');
    done();
  }, customWindow);
});

QUnit.module('US Privacy - getCurrentUspString()', () => {

  QUnit.test('should return the latest uspString', (assert) => {
    const done1 = assert.async();
    const done2 = assert.async();
    const uspData1 = {uspString: '1YNN'};
    const uspData2 = {uspString: '1YNY'};

    const customWindow1 = {
      __uspapi: (command, version, cb) => {
        cb(uspData1, true);
      }
    };

    const customWindow2 = {
      __uspapi: (command, version, cb) => {
        cb(uspData2, true);
      }
    };

    obtainUsPrivacyString((result1) => {
      assert.equal(result1, uspData1.uspString, 'uspString1 is returned');
      assert.equal(getCurrentUspString(), uspData1.uspString, 'getCurrentUspString() returns the latest uspString1');
      done1();
    }, customWindow1);

    obtainUsPrivacyString((result2) => {
      assert.equal(result2, uspData2.uspString, 'uspString2 is returned');
      assert.equal(getCurrentUspString(), uspData2.uspString, 'getCurrentUspString() returns the latest uspString2');
      done2();
    }, customWindow2);
  });
});
