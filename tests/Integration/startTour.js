/* Run: node tests/Integration/startTour.js */
const assert = require('assert');
const fs = require('fs');
const vm = require('vm');
const path = require('path');
const source = fs.readFileSync(path.join(__dirname, '../../view/js/script.js'), 'utf8');
const start = source.indexOf('async function startTour(');
const code = source.slice(start, source.indexOf('\n\nfunction findIndex(', start));

function harness(response) {
    const state = {loads: 0, starts: 0, errors: [], notices: [], busy: 0};
    const visible = {visible: true}, hidden = {visible: false};
    function $(selector) {
        const element = selector === '#visible' ? visible : selector === '#hidden' ? hidden : null;
        return {
            length: element ? 1 : 0,
            first() { return this; },
            is() { return !!element && element.visible; }
        };
    }
    $.ajax = async () => { state.loads++; if (state.fail) { throw new Error('Network failure'); } return response; };
    const tour = {
        setOptions(options) { state.options = options; },
        onBeforeChange(callback) { state.before = callback; },
        onExit(callback) { state.exit = callback; },
        onComplete(callback) { state.complete = callback; },
        async start() { state.starts++; },
        async exit() { state.exit(); }
    };
    const context = {
        $, introJs: {tour: () => tour}, webSiteRootURL: '/',
        window: {}, document: {activeElement: null, contains: () => false},
        modal: {showPleaseWait: () => state.busy++, hidePleaseWait: () => state.busy--},
        __: value => value, avideoToastError: text => state.errors.push(text),
        avideoToastInfo: text => state.notices.push(text), console: {error() {}}
    };
    vm.createContext(context);
    vm.runInContext(code, context);
    return {state, context};
}
(async function () {
    const legacy = harness([
        {element: '#visible', intro: 'Existing help'},
        {element: '#hidden', intro: 'Hidden control'},
        {element: '#missing', intro: 'Unavailable feature'}
    ]);
    await legacy.context.startTour('legacy.help.json');
    assert.strictEqual(legacy.state.options.steps.length, 1, 'Legacy tours still exclude hidden and absent controls');
    assert.strictEqual(legacy.state.options.steps[0].intro, 'Existing help');
    assert.strictEqual(legacy.state.busy, 0);
    await legacy.context.startTour('legacy.help.json');
    assert.strictEqual(legacy.state.starts, 1, 'Repeated clicks must not stack active tours');
    legacy.state.exit();
    await legacy.context.startTour('legacy.help.json');
    assert.strictEqual(legacy.state.starts, 2, 'Help can be reopened after closing');

    const modern = harness({options: {showProgress: true, nextLabel: 'Next'}, steps: [{element: '#visible', title: 'Title', intro: 'Help'}]});
    await modern.context.startTour('modern.help.json');
    assert.strictEqual(modern.state.options.showProgress, true);
    assert.strictEqual(modern.state.options.steps[0].title, 'Title');
    modern.state.complete();
    assert.strictEqual(modern.context.startTour.active, null, 'Finish releases the active-tour guard');

    const empty = harness([]);
    await empty.context.startTour('empty.help.json');
    assert.strictEqual(empty.state.starts, 0);
    assert.strictEqual(empty.state.notices.length, 1);
    assert.strictEqual(empty.state.busy, 0, 'Empty tours release the loading overlay');

    const failure = harness([{element: '#visible', intro: 'Help'}]);
    failure.state.fail = true;
    await failure.context.startTour('failure.help.json');
    assert.strictEqual(failure.state.errors.length, 1);
    assert.strictEqual(failure.state.busy, 0);
    assert.strictEqual(failure.context.startTour.loading, false, 'Network errors allow a retry');
    failure.state.fail = false;
    await failure.context.startTour('failure.help.json');
    assert.strictEqual(failure.state.starts, 1);

    const loading = harness([{element: '#visible', intro: 'Help'}]);
    await Promise.all([loading.context.startTour('help.json'), loading.context.startTour('help.json')]);
    assert.strictEqual(loading.state.loads, 1, 'Repeated clicks during loading make only one request');
    console.log('PASS: legacy tours, new options, missing targets, reopening, loading guards and network-error recovery.');
})().catch(error => { console.error(error); process.exitCode = 1; });
