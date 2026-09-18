/* Run with node tests/Integration/companionWidget.js. No network or provider calls. */
const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');
const vm = require('node:vm');
const script = fs.readFileSync(path.join(__dirname, '../../plugin/AI/View/companionWidget.js'), 'utf8');

function page() {
    function element(hidden = false) {
        const classes = new Set();
        return {
            hidden, dataset: {}, children: {}, attributes: {}, events: {}, style: {},
            classList: {
                add: name => classes.add(name), remove: name => classes.delete(name),
                contains: name => classes.has(name),
                toggle: (name, value) => value ? classes.add(name) : classes.delete(name)
            },
            querySelector(selector) { return this.children[selector]; },
            getAttribute(name) { return this.attributes[name] || null; },
            setAttribute(name, value) { this.attributes[name] = value; },
            addEventListener(name, handler) { this.events[name] = handler; },
            focus() {}, click() { this.events.click({stopPropagation() {}}); }
        };
    }
    const widget = element(), panel = element(true), launcher = element(), close = element();
    const teaser = element(true), text = element(), dismiss = element(), frame = element();
    widget.children = {
        '.companion-widget-panel': panel, '.companion-widget-launcher': launcher,
        '.companion-widget-close': close, '.companion-widget-expand': element(), iframe: frame,
        '.companion-widget-teaser': teaser, '.companion-widget-title-text': element()
    };
    launcher.children = {'.companion-widget-launcher-photo': element(true), '.companion-widget-launcher-status': element(true)};
    teaser.children = {'.companion-widget-teaser-text': text, '.companion-widget-teaser-dismiss': dismiss};
    launcher.dataset = {labelOpen: 'Open chat', labelClose: 'Close chat'};
    teaser.dataset.defaultMessage = 'Default invitation';
    frame.dataset.src = 'https://companion.example/widget/test';
    widget.dataset.configUrl = 'https://companion.example/api/v1/public/widget/test';
    const timers = new Map();
    let nextTimer = 0, respond;
    const window = {
        location: {href: 'https://video.example/video/1'}, addEventListener() {},
        // The old version saved this flag when the chat was first opened.
        sessionStorage: {getItem: () => '1', setItem() {}},
        localStorage: {getItem: () => null}
    };
    window.fetch = () => new Promise(resolve => { respond = resolve; });
    vm.runInNewContext(script, {
        window, URL, fetch: window.fetch,
        document: {getElementById: () => widget, body: element(), addEventListener() {}},
        setTimeout(fn) { timers.set(++nextTimer, fn); return nextTimer; },
        clearTimeout(id) { timers.delete(id); }
    });
    return {
        widget, panel, launcher, close, teaser, text, dismiss,
        async config(value, ok = true) {
            respond({ok, json: async () => value});
            await new Promise(resolve => setImmediate(resolve));
        },
        tick() { for (const [id, fn] of timers) { timers.delete(id); fn(); } }
    };
}

(async () => {
    const first = page();
    await first.config({launcher_message: 'Current invitation', theme: 'dark', agent_name: 'Agent'});
    assert.equal(first.teaser.hidden, true, 'Invitation keeps its short initial delay');
    first.tick();
    assert.equal(first.teaser.hidden, false, 'A previous session dismissal must not hide this visit');
    assert.equal(first.text.textContent, 'Current invitation');
    assert.equal(first.widget.dataset.theme, 'dark');
    first.launcher.click();
    assert.equal(first.panel.hidden, false);
    assert.equal(first.teaser.hidden, true);
    assert.equal(first.launcher.attributes['aria-expanded'], 'true');
    first.close.click();
    first.tick();
    assert.equal(first.panel.hidden, true);
    assert.equal(first.teaser.hidden, true, 'Closing chat should not repeatedly interrupt this visit');

    const revisit = page();
    await revisit.config({launcher_message: 'Edited invitation', theme: 'light'});
    revisit.tick();
    assert.equal(revisit.teaser.hidden, false, 'A new visit shows the newly saved invitation');
    assert.equal(revisit.text.textContent, 'Edited invitation');
    assert.equal(revisit.widget.dataset.theme, 'light');
    revisit.dismiss.click();
    revisit.tick();
    assert.equal(revisit.teaser.hidden, true, 'Explicit dismissal still works');

    const slow = page();
    slow.launcher.click();
    await slow.config({launcher_message: 'Late invitation', agent_name: 'Agent'});
    slow.tick();
    assert.equal(slow.teaser.hidden, true, 'Late branding must not overlay an open chat');
    assert.equal(slow.launcher.attributes.title, 'Close chat');

    const unavailable = page();
    await unavailable.config(null, false);
    unavailable.tick();
    assert.equal(unavailable.text.textContent, 'Default invitation');
    assert.equal(unavailable.teaser.hidden, false);
    console.log('PASS: invitation before chat, reopening/reloading, saved message updates, dismissal, themes and delayed/unavailable config.');
})().catch(error => { console.error(error); process.exitCode = 1; });
