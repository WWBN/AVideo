const assert = require('node:assert/strict');
const fs = require('node:fs');
const test = require('node:test');
const vm = require('node:vm');

const html = fs.readFileSync(0, 'utf8');
let config;
const encode = value => String(value).replaceAll('&', '&amp;').replaceAll('"', '&quot;').replaceAll('<', '&lt;');
const context = vm.createContext({
    tinymce: { init(value) { config = value; }, DOM: { encode } },
    setTimeout(callback) { callback(); },
    avideoToastError() {},
});
for (const script of html.matchAll(/<script\b[^>]*>([\s\S]*?)<\/script>/g)) {
    if (script[1].trim()) vm.runInContext(script[1], context);
}
assert.ok(config, 'The real PHP template must initialize TinyMCE.');

test('the media resolver accepts channel embeds from a subdirectory installation', async () => {
    for (const type of ['videoEmbed', 'audioEmbed', 'articleEmbed']) {
        const url = 'https://example.com/avideo/channel/MyChannel/' + type + '/.abc_1/title';
        const result = await config.media_url_resolver({ url });
        assert.ok(result.html.includes('src="' + url + '"'));
    }
});

test('the media resolver accepts embed links whose video title contains dots', async () => {
    for (const url of [
        'https://example.com/avideo/videoEmbed/12/wait-for-it...',
        'https://example.com/avideo/channel/MyChannel/videoEmbed/.abc_1/version-2..0-released',
    ]) {
        const result = await config.media_url_resolver({ url });
        assert.ok(result.html.includes('src="' + url + '"'), url);
    }
});

test('the media resolver still rejects non-embed pages and channel traversal', async () => {
    for (const url of [
        'https://example.com/avideo/channel/MyChannel/video/12',
        'https://example.com/avideo/channel/MyChannel/objects/logoff.php',
        'https://example.com/avideo/channel/MyChannel/videoEmbed/../../objects/logoff.php',
        'https://example.com/avideo/channel/%2e%2e/videoEmbed/12',
        'https://example.com/avideo/videoEmbed/12/.%2E/objects/logoff.php',
        'https://example.com/avideo/videoEmbed/12/..',
        'https://example.com/avideo/videoEmbed/12\\..\\objects/logoff.php',
        'https://example.com/avideo/videoEmbed/12/%5c..%5cobjects/logoff.php',
        'https://example.com/avideo/videoEmbed/12/%2f..%2fobjects/logoff.php',
        // the editor puts this URL in the iframe as typed, and browsers drop tabs and newlines
        'https://example.com/avideo/videoEmbed/12/.\t./.\n./objects/logoff.php',
        'https://example.com/avideo/channel/./videoEmbed/12',
        'https://example.com.evil.test/avideo/channel/MyChannel/videoEmbed/12',
    ]) {
        await assert.rejects(config.media_url_resolver({ url }));
    }
});

test('Shorts keep their embed URL when the media dialog converts a source for resizing', async () => {
    const canonical = 'https://www.youtube.com/embed/abc_123-xyz';
    for (const url of [
        'https://www.youtube.com/shorts/abc_123-xyz?feature=share',
        'https://m.youtube.com/shorts/abc_123-xyz',
        'youtube.com/shorts/abc_123-xyz',
    ]) {
        const result = await config.media_url_resolver({ url });
        assert.ok(result.html.includes('src="' + canonical + '"'));
        assert.ok(result.html.includes('width="315" height="560"'));
        // media.dataToHtml() calls editor.convertURL(source, 'source') during dimension changes.
        const resizedSource = config.urlconverter_callback(url, undefined, true, 'source');
        assert.equal(resizedSource, canonical);
        assert.equal(config.urlconverter_callback(resizedSource, undefined, true, 'source'), canonical);
        const resized = await config.media_url_resolver({ url: resizedSource });
        assert.ok(resized.html.includes('src="' + canonical + '"'));
        assert.equal(config.urlconverter_callback(url, 'iframe', true, 'src'), canonical);
        assert.equal(config.urlconverter_callback(url, { nodeName: 'IFRAME' }, true, 'src'), canonical);
    }
});

test('URL conversion leaves links, images and other video providers unchanged', () => {
    const shorts = 'https://www.youtube.com/shorts/abc123';
    assert.equal(config.urlconverter_callback(shorts, 'a', true, 'href'), shorts);
    assert.equal(config.urlconverter_callback(shorts, 'img', true, 'src'), shorts);
    for (const url of ['https://player.vimeo.com/video/12', 'https://example.com/avideo/channel/MyChannel/videoEmbed/12']) {
        assert.equal(config.urlconverter_callback(url, undefined, true, 'source'), url);
    }
});
