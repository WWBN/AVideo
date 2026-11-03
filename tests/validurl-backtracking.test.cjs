// node --test tests/validurl-backtracking.test.cjs
// validURL() (view/js/script.js, and its copy in plugin/BulkEmbed/search.php) repeated the inner
// part of a domain label, so a long run of letters ending in '.' (e.g. typed in the search box)
// took exponential time and froze the tab. The label group is now optional instead of repeated:
// same accepted strings, linear time.
const assert = require('node:assert/strict');
const test = require('node:test');
const fs = require('node:fs');
const path = require('node:path');

const root = path.join(__dirname, '..');

function loadValidURL(relativeFile) {
    const source = fs.readFileSync(path.join(root, relativeFile), 'utf8');
    const match = source.match(/function validURL\(str\) \{[\s\S]*?\n\s*\}/);
    assert.ok(match, 'validURL() not found in ' + relativeFile);
    return new Function(match[0] + '\nreturn validURL;')();
}

// The previous (ambiguous) domain-name group, used only as a reference for equivalence.
function oldPattern(pathChars) {
    return new RegExp('^(https?:\\/\\/)?' +
        '((([a-z\\d]([a-z\\d-]*[a-z\\d])*)\\.)+[a-z]{2,}|' +
        '((\\d{1,3}\\.){3}\\d{1,3}))' +
        '(\\:\\d+)?(\\/[' + pathChars + ']*)*' +
        '(\\?[;&a-z\\d%_.~+=-]*)?' +
        '(\\#[-a-z\\d_]*)?$', 'i');
}

const files = [
    { file: 'view/js/script.js', pathChars: '-a-z\\d%_.~+:@' },
    { file: 'plugin/BulkEmbed/search.php', pathChars: '-a-z\\d%_.~+' },
];

const corpus = [
    'https://www.youtube.com/watch?v=abc123',
    'http://vimeo.com/123456',
    'youtu.be/abc',
    'https://sub-domain.example.co.uk:8080/path/to/file.mp4?x=1&y=2#t',
    'https://a.bc',
    'https://a-.bc',
    'https://-a.bc',
    'https://a--b.bc',
    'https://1.2.3.4/x',
    'https://localhost',
    'https://example.com/a b',
    'example',
    'example.',
    'example.c',
    'https://exa_mple.com',
    'https://example.com/user@host:1',
    '',
];

function randomString(rng, alphabet, maxLength) {
    const length = Math.floor(rng() * maxLength);
    let out = '';
    for (let i = 0; i < length; i++) {
        out += alphabet[Math.floor(rng() * alphabet.length)];
    }
    return out;
}

function seededRandom(seed) {
    return function () {
        seed = (seed * 1103515245 + 12345) % 2147483648;
        return seed / 2147483648;
    };
}

for (const { file, pathChars } of files) {
    test(file + ': validURL accepts exactly what the previous pattern accepted', () => {
        const validURL = loadValidURL(file);
        const reference = oldPattern(pathChars);
        const rng = seededRandom(42);
        const inputs = corpus.slice();
        for (let i = 0; i < 3000; i++) {
            // short strings only: the reference pattern is exponential on long labels
            inputs.push(randomString(rng, 'ab1-.:/?#', 14));
            inputs.push('https://' + randomString(rng, 'ab-.', 12) + '/' + randomString(rng, 'a/?=&#', 6));
        }
        for (const input of inputs) {
            assert.equal(validURL(input), reference.test(input), JSON.stringify(input));
        }
    });

    test(file + ': validURL rejects a long label without a TLD quickly', () => {
        const validURL = loadValidURL(file);
        const started = Date.now();
        assert.equal(validURL('a'.repeat(5000) + '.'), false);
        assert.equal(validURL('https://' + 'a1-'.repeat(2000) + 'a.'), false);
        assert.equal(validURL('a'.repeat(5000) + '.com'), true);
        assert.ok(Date.now() - started < 1000, 'validURL took ' + (Date.now() - started) + 'ms');
    });
}
