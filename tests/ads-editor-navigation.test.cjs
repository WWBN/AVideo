// node --test tests/ads-editor-navigation.test.cjs
const assert = require('node:assert/strict');
const test = require('node:test');
const fs = require('node:fs');
const path = require('node:path');
const vm = require('node:vm');

const source = fs.readFileSync(path.join(__dirname, '../plugin/ADs/editor.js'), 'utf8');
const functions = ['getPosition', 'getMapSlots', 'showMapPage', 'highlight'].map(name => {
    const start = source.indexOf('    function ' + name + '(');
    assert.notEqual(start, -1, name);
    let depth = 0;
    let end = source.indexOf('{', start);
    for (; end < source.length; end++) {
        if (source[end] === '{') depth++;
        else if (source[end] === '}' && --depth === 0) break;
    }
    return source.slice(start, end + 1);
}).join('\n');

function mapEnvironment() {
    const map = { attrs: { 'data-page': 'home' } };
    const positionSelect = {};
    const pages = ['video', 'home', 'channel'].map(page => ({ page, classes: new Set(page === 'home' ? [] : ['hidden']), attrs: {} }));
    const buttons = pages.map(node => ({ page: node.page, classes: new Set(), attrs: {} }));
    function collection(nodes) {
        return {
            each(fn) { nodes.forEach(node => fn.call(node)); return this; },
            data(key) { return nodes[0]?.[key]; },
            val(value) { nodes.forEach(node => { node.value = value; }); return this; },
            attr(key, value) {
                if (arguments.length === 1) return nodes[0]?.attrs[key];
                nodes.forEach(node => { node.attrs[key] = value; }); return this;
            },
            toggleClass(name, enabled) { nodes.forEach(node => enabled ? node.classes.add(name) : node.classes.delete(name)); return this; },
            removeClass() { return this; }, addClass() { return this; }, removeAttr() { return this; },
            filter() { return collection([]); }, add() { return this; },
        };
    }
    const $ = selector => {
        if (typeof selector !== 'string') return collection([selector]);
        if (selector === '#ADsMap') return collection([map]);
        if (selector === '#ADsPositionSelect') return collection([positionSelect]);
        if (selector === '#ADsMap .ADsMapPage') return collection(pages);
        if (selector === '#ADsMap .ADsMapPageButton') return collection(buttons);
        return collection([]);
    };
    const context = {
        $, selectedType: 'leaderBoardBigVideo',
        config: { positions: {
            leaderBoardBigVideo: { map: ['home', 'channel'] },
            channelLeaderBoardTop: { map: ['channel'] },
            leaderBoardTop: { map: ['video', 'home', 'channel'] },
        }, splits: {} },
        updatePreview() {},
    };
    vm.createContext(context);
    vm.runInContext(functions, context);
    return { context, map, pages, buttons, positionSelect };
}

test('a manual page choice is kept when the selected ad belongs to a different page', () => {
    const { context, map, pages, buttons } = mapEnvironment();
    context.showMapPage('video');
    assert.equal(map.attrs['data-page'], 'video');
    assert.equal(pages.find(p => p.page === 'video').classes.has('hidden'), false);
    assert.equal(pages.find(p => p.page === 'home').classes.has('hidden'), true);
    assert.equal(buttons.find(p => p.page === 'video').attrs['aria-pressed'], 'true');
});

test('selecting a position still opens a page that can show that position', () => {
    const { context, map, positionSelect } = mapEnvironment();
    context.highlight('channelLeaderBoardTop');
    assert.equal(map.attrs['data-page'], 'channel');
    assert.equal(context.selectedType, 'channelLeaderBoardTop');
    assert.equal(positionSelect.value, 'channelLeaderBoardTop');
});

test('a compatible page is kept when selecting a position used on several pages', () => {
    const { context, map } = mapEnvironment();
    context.showMapPage('video');
    context.highlight('leaderBoardTop');
    assert.equal(map.attrs['data-page'], 'video');
});
