const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');
const test = require('node:test');
const vm = require('node:vm');

const root = path.resolve(__dirname, '..');

function fixture() {
    const handlers = new Map();
    const requests = [];
    const messages = [];
    const state = { waiting: false, reloads: 0 };
    function element(selector) {
        return {
            on(event, target, callback) {
                handlers.set(event + ':' + (typeof target === 'string' ? target : selector), callback || target);
                return this;
            },
            find: element,
            val: () => '123456',
            is: () => false
        };
    }
    const $ = element;
    $.ajax = (options) => { requests.push(options); if (options.beforeSend) options.beforeSend(); };
    const context = vm.createContext({
        $, document: {}, grid: element('grid'), webSiteRootURL: 'https://video.example.invalid/',
        modal: { showPleaseWait: () => { state.waiting = true; }, hidePleaseWait: () => { state.waiting = false; } },
        swal: () => Promise.resolve(true),
        avideoToastError: (message) => messages.push(message),
        __: (message) => message,
        console: { log() {} },
        window: { location: { reload: () => { state.reloads++; } } },
        this_: element('switch')
    });
    return { context, handlers, requests, messages, state };
}

const actions = {
    submitVerificationCode: 'submit:#verifyEmailForm',
    resendVerificationCode: 'resendCodeAjax()',
    authAccount: 'click:#wwbnIndexAuthBtn',
    submitIndex: 'wwbnIndexSubmit()',
    getIndexTermsAndConditions: 'click:#wwbnIndexIndexActiveBtn',
    reIndex: 'click:#wwbnIndexReIndexBtn',
    unIndex: 'click:#wwbnIndexIndexUnindexBtn',
    requestResetKeys: 'click:#wwbnIndexRequestResetBtn'
};

for (const [action, trigger] of Object.entries(actions)) {
    test(action + ': denied AJAX releases the overlay and explains recovery', async () => {
        const f = fixture();
        vm.runInContext(fs.readFileSync(path.join(root, 'plugin/WWBNIndex/script.js'), 'utf8'), f.context);
        if (trigger.endsWith('()')) {
            vm.runInContext(trigger, f.context);
        } else {
            assert.equal(typeof f.handlers.get(trigger), 'function');
            f.handlers.get(trigger).call({}, { preventDefault() {} });
        }
        await Promise.resolve();
        assert.equal(f.requests.length, 1);
        const request = f.requests[0];
        assert.equal(request.data.action, action);
        assert.equal(request.type.toUpperCase(), 'POST');
        assert.equal(request.url, 'https://video.example.invalid/plugin/WWBNIndex/ajax.php');
        // jQuery uses error for an HTML 403, and for a login redirect that fails JSON parsing.
        for (const response of [{ status: 403, responseText: '<html>Forbidden</html>' }, { status: 200, responseText: '<html>Login</html>' }]) {
            f.state.waiting = true;
            request.error(response, response.status === 403 ? 'error' : 'parsererror');
            assert.equal(f.state.waiting, false);
            assert.match(f.messages.at(-1), /reload|sign in/i);
            assert.equal(f.requests.length, 1, 'Denial must not retry a mutating action');
        }
    });
}

test('changePluginStatus: denial is visible after the plugin was disabled', () => {
    const source = fs.readFileSync(path.join(root, 'view/managerPlugins_body.php'), 'utf8');
    const start = source.indexOf('$.ajax({', source.indexOf('if (this_.data("pname") == "WWBNIndex")'));
    const end = source.indexOf('\n                        });', start);
    assert.ok(start >= 0 && end > start);
    const f = fixture();
    vm.runInContext(source.slice(start, end + '\n                        });'.length).replace(/<\?=.*?\?>/g, 'https://video.example.invalid/'), f.context);
    assert.equal(f.requests.length, 1);
    const request = f.requests[0];
    assert.equal(request.data.action, 'changePluginStatus');
    assert.equal(request.data.enabled, false);
    assert.equal(typeof request.error, 'function');
    request.error({ status: 403 });
    assert.match(f.messages.at(-1), /WWBN|index/i);
    assert.equal(f.state.reloads, 0);
    assert.equal(f.requests.length, 1);
});
