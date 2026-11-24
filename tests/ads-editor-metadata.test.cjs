// node --test tests/ads-editor-metadata.test.cjs
const assert = require('node:assert/strict');
const test = require('node:test');
const fs = require('node:fs');
const path = require('node:path');
const vm = require('node:vm');

const source = fs.readFileSync(path.join(__dirname, '../plugin/ADs/editor.php'), 'utf8');

function editorAction(name) {
    const start = source.indexOf('        function ' + name + '(');
    assert.notEqual(start, -1, name);
    const end = source.indexOf('\n        function ', start + 1);
    return source.slice(start, end === -1 ? source.indexOf('\n    </script>', start) : end);
}

function actionEnvironment(name) {
    const events = [];
    const context = {
        $() { return { length: 2, val() { return 'leaderBoardTop'; } }; },
        modal: { showPleaseWait() { events.push('busy'); }, hidePleaseWait() { events.push('idle'); } },
        ADsEditor: { refreshPreview() { events.push('preview'); } },
        ADsEditorConfig: { texts: { error: 'An error occurred' } },
        webSiteRootURL: 'https://site.test/',
        is_regular_user: false,
        getResultListOrder() { return [{ order: 1, imgSrc: '/new-first.png' }]; },
        loadAdsList(type) { events.push('reload:' + type); },
        avideoToastSuccess() { events.push('saved'); },
        avideoToastError() { events.push('network-error'); },
        avideoAlertError() { events.push('save-error'); },
        __(text) { return text; },
    };
    context.$.ajax = options => { context.request = options; };
    vm.createContext(context);
    vm.runInContext(editorAction(name), context);
    return { context, events };
}

test('saving image order reloads the persisted first image and refreshes the open preview', () => {
    const { context, events } = actionEnvironment('saveAdsMetadata');
    context.saveAdsMetadata('leaderBoardTop');
    assert.equal(context.request.data.metadata[0].imgSrc, '/new-first.png');
    context.request.success({ error: false });
    context.request.complete();
    assert.deepEqual(events, ['busy', 'saved', 'reload:leaderBoardTop', 'preview', 'idle']);
});

test('rejected metadata does not reload the list or discard unsaved edits', () => {
    const { context, events } = actionEnvironment('saveAdsMetadata');
    context.saveAdsMetadata('leaderBoardTop');
    context.request.success({ error: true, msg: 'Not saved' });
    context.request.complete();
    assert.deepEqual(events, ['busy', 'save-error', 'idle']);
});

for (const name of ['saveAdsMetadata', 'saveAdsImage', 'deleteAdsImage']) {
    test(name + ' releases the loading overlay after a transport failure', () => {
        const { context, events } = actionEnvironment(name);
        if (name === 'saveAdsMetadata') context[name]('leaderBoardTop');
        else context[name]('fixture', 'leaderBoardTop');
        context.request.error();
        context.request.complete();
        assert.deepEqual(events, ['busy', 'network-error', 'idle']);
    });
}
