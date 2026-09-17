const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');
const vm = require('node:vm');

const view = fs.readFileSync(path.join(__dirname, '../../plugin/AI/tabs/companionChat.php'), 'utf8');
const script = view.split('<script>')[1].split('</script>')[0]
    .replace(/<\?php echo \(int\) \$videos_id; \?>/g, '627')
    .replace(/<\?php echo json_encode\(__\((.*?)\)\); \?>/g, (_, literal) => literal);
const document = {documentElement: {lang: 'en'}};
const context = {document, $: () => ({ready() {}})};
vm.createContext(context);
vm.runInContext(script, context);

assert.equal(context.companionPrice('0.01'), '0.01 USD');
assert.equal(context.companionPrice('0.000123'), '0.000123 USD');
assert.equal(context.companionPrice(0), '0.00 USD');
assert.equal(context.companionPrice(1.103156, 4), '1.1032 USD');
assert.equal(context.companionPrice(220.58038, 2), '220.58 USD');
for (const missing of [null, undefined, '', 'invalid', -1]) {
    assert.equal(context.companionPrice(missing), 'Unavailable');
    assert.equal(context.companionProcessingEstimate(missing, 0.02, 2859), null);
    assert.equal(context.companionProcessingEstimate(0.15, missing, 2859), null);
}
assert.ok(Math.abs(context.companionProcessingEstimate(0.15, 0.02, 2859) - 1.103) < 1e-10);
assert.equal(context.companionProcessingEstimate(0, 0, 2859), 0);
for (const duration of [null, undefined, '', 'invalid', 0, -1]) {
    assert.equal(context.companionProcessingEstimate(0.15, 0.02, duration), null);
}
document.documentElement.lang = 'pt-BR';
assert.equal(context.companionPrice(0.01), '0,01 USD');
console.log('PASS: pricing precision, localized values, estimates, and unavailable rates.');

// The AI usage table is formatted server side and must follow the same rule:
// per-token charges are routinely below a cent, and the wallet's default
// precision of 2 renders every one of those rows as "0.00".
const {spawnSync} = require('node:child_process');
const ai = fs.readFileSync(path.join(__dirname, '../../plugin/AI/AI.php'), 'utf8');
const method = ai.match(/ {4}static function getPriceDecimals[\s\S]*?\n {4}\}/);
assert.ok(method, 'AI::getPriceDecimals must exist');
const fixture = `<?php
    class AI {
${method[0]}
    }
    $out = [];
    foreach ([0, 0.01, 0.004, 0.000123, 1.2345, 220.58038, 5] as $value) {
        $out[] = AI::getPriceDecimals($value);
    }
    echo json_encode($out);
`;
const phpResult = spawnSync('php', [], {input: fixture, encoding: 'utf8'});
assert.equal(phpResult.status, 0, phpResult.stderr || phpResult.stdout);
assert.deepEqual(JSON.parse(phpResult.stdout), [2, 2, 3, 6, 4, 5, 2]);
console.log('PASS: server-side AI price precision matches the client formatting rule.');
