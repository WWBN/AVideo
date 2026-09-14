'use strict';
const form = document.getElementById('configurationForm');
const result = document.getElementById('result');
const testButton = document.getElementById('testConnection');
const installButton = document.getElementById('installButton');
let busy = false;
let installed = false;
const stages = {validation: 'Form details', requirements: 'Server requirements', connection: 'MySQL connection', streamer: 'Administrator verification', database: 'Database creation', tables: 'Table installation', records: 'Initial registration', configuration: 'Configuration file'};
function element(tag, text, className) {
    const node = document.createElement(tag);
    if (text) node.textContent = text;
    if (className) node.className = className;
    return node;
}
function commandBlock(parent, command, label) {
    parent.append(element('h3', label));
    const pre = element('pre');
    pre.append(element('code', command));
    const copy = element('button', 'Copy command', 'copy');
    copy.type = 'button';
    copy.addEventListener('click', async () => {
        try { await navigator.clipboard.writeText(command); copy.textContent = 'Copied!'; }
        catch (_) {
            const selection = window.getSelection();
            const range = document.createRange(); range.selectNodeContents(pre);
            selection.removeAllRanges(); selection.addRange(range);
            copy.textContent = 'Selected — press Ctrl+C';
        }
    });
    parent.append(pre, copy);
}
function showResult(data, testing) {
    result.hidden = false;
    result.className = 'card result' + (data.error ? ' error' : '');
    result.replaceChildren();
    result.append(element('h2', data.error ? 'Let’s fix this issue.' : (data.installed ? 'Installation complete.' : 'Connection confirmed.')));
    result.append(element('p', data.msg));
    if (data.error && data.stage) result.append(element('p', 'Step: ' + (stages[data.stage] || data.stage), 'error-stage'));
    if (Array.isArray(data.steps) && data.steps.length) {
        const list = element('ul');
        data.steps.forEach(step => list.append(element('li', step.label)));
        result.append(list);
    }
    if (data.note) result.append(element('p', data.note));
    if (data.help && (data.help.text || data.help.command)) {
        const help = element('div', '', 'help');
        help.append(element('h3', data.help.title || 'How to fix it'));
        if (data.help.text) help.append(element('p', data.help.text));
        if (data.help.command) commandBlock(help, data.help.command, 'In the server terminal');
        if (data.help.sql) commandBlock(help, data.help.sql, 'In the SQL console, as an administrator');
        result.append(help);
    } else if (data.error) {
        result.append(element('p', 'Update the indicated fields and try again.'));
    }
    if (data.installed) {
        installed = true;
        form.querySelectorAll('input').forEach(input => { input.value = ''; });
        const link = element('a', 'Open application', 'button primary');
        const url = new URL(data.url || form.dataset.openUrl || '../', location.href);
        link.href = ['http:', 'https:'].includes(url.protocol) ? url.href : (form.dataset.openUrl || '../');
        result.replaceChildren(element('h2', 'Installation complete.'), element('p', 'Setup is locked. Your application is ready to open.'), link);
        form.before(result);
        form.remove();
        document.querySelectorAll('.environment, .ubuntu-help, .intro, .eyebrow, .sidebar nav').forEach(node => node.remove());
        const heading = document.querySelector('main h1');
        if (heading) heading.textContent = 'Your application is ready.';
        result.focus({preventScroll: true});
        window.scrollTo({top: 0, behavior: 'smooth'});
        return;
    }
    const connectionResult = document.getElementById('connectionResult');
    if (testing && connectionResult) {
        connectionResult.hidden = false;
        connectionResult.textContent = data.error ? 'The connection could not be confirmed. See the diagnostic details below.' : '✓ Connection to MySQL / MariaDB confirmed.';
    }
    result.focus({preventScroll: true});
    result.scrollIntoView({behavior: 'smooth', block: 'center'});
}
async function run(testing) {
    if (busy || installed) return;
    if (testing) {
        for (const id of ['databaseHost', 'databasePort', 'databaseUser', 'databaseName']) {
            if (!document.getElementById(id).reportValidity()) return;
        }
    } else {
        if (form.dataset.ready !== '1') return;
        const confirmation = document.getElementById('confirmSystemAdminPass');
        if (confirmation) confirmation.setCustomValidity(confirmation.value === document.getElementById('systemAdminPass').value ? '' : 'The administrator passwords do not match.');
        if (!form.reportValidity()) return;
    }
    const payload = new FormData(form);
    payload.set('action', testing ? 'test' : 'install');
    busy = true;
    form.setAttribute('aria-busy', 'true');
    testButton.disabled = installButton.disabled = true;
    result.hidden = false;
    result.className = 'card result';
    result.replaceChildren();
    const title = element('h2');
    title.append(element('span', '', 'spinner'), document.createTextNode(testing ? 'Testing the connection…' : 'Installing your application…'));
    result.append(title, element('p', testing ? 'Checking access to the MySQL / MariaDB server.' : 'Verifying your settings, preparing the database, and saving the configuration. Wait for the result before closing this page.'));
    if (!testing) result.scrollIntoView({behavior: 'smooth', block: 'center'});
    try {
        const response = await fetch('checkConfiguration.php', {method: 'POST', body: payload, credentials: 'same-origin', headers: {'Accept': 'application/json'}});
        let data;
        try { data = await response.json(); }
        catch (_) { throw new Error('The server returned an unexpected response (HTTP ' + response.status + ').'); }
        if (!data || (typeof data.error !== 'boolean' && typeof data.error !== 'string')) throw new Error('The server response does not contain a valid result.');
        showResult(data, testing);
    } catch (error) {
        showResult({error: true, msg: error.message || 'Unable to receive the server response.', help: {
            title: 'Check the server before trying again',
            text: 'The operation may have completed even if the response did not reach your browser. Reload this page: if the configuration exists, setup will be locked. Otherwise, check the Apache/PHP error log and your network connection.',
            command: 'php --ini\nphp -m'
        }}, testing);
    } finally {
        busy = false;
        form.removeAttribute('aria-busy');
        if (!installed) { testButton.disabled = false; installButton.disabled = form.dataset.ready !== '1'; }
    }
}
if (form) {
    form.addEventListener('invalid', event => {
        const input = event.target;
        if (input.validity.valueMissing) input.setCustomValidity('Please fill out this field.');
        else if (input.validity.typeMismatch) input.setCustomValidity((input.type === 'email' ? 'Please enter a valid email address.' : 'Please enter a valid URL, including http:// or https://.'));
        else if (input.validity.rangeUnderflow || input.validity.rangeOverflow || input.validity.badInput) input.setCustomValidity(input.id === 'defaultPriority' ? 'Please enter a priority between 1 and 10.' : 'Please enter a port number between 1 and 65535.');
        else if (input.validity.patternMismatch) input.setCustomValidity(input.id === 'tablesPrefix' ? 'Use up to 25 letters, numbers, or underscores.' : 'Use 1 to 64 letters, numbers, hyphens, or underscores.');
    }, true);
    form.addEventListener('input', event => {
        if (event.target.setCustomValidity) event.target.setCustomValidity('');
        if (event.target.id === 'systemAdminPass') document.getElementById('confirmSystemAdminPass').setCustomValidity('');
        if (event.target.id.startsWith('database') || event.target.id === 'createTables') document.getElementById('connectionResult').hidden = true;
    });
    form.addEventListener('submit', event => { event.preventDefault(); run(false); });
    testButton.addEventListener('click', () => run(true));
    form.querySelectorAll('.reveal').forEach(button => button.addEventListener('click', () => {
        const input = document.getElementById(button.dataset.target);
        const reveal = input.type === 'password';
        input.type = reveal ? 'text' : 'password';
        button.textContent = reveal ? 'Hide' : 'Show';
        button.setAttribute('aria-pressed', String(reveal));
        button.setAttribute('aria-label', (reveal ? 'Hide' : 'Show') + (input.id === 'databasePass' ? ' database password' : ' administrator password'));
    }));
    window.addEventListener('beforeunload', event => { if (busy) { event.preventDefault(); event.returnValue = ''; } });
}

document.querySelectorAll('.ubuntu-help .copy').forEach(button => button.addEventListener('click', async () => {
    const code = button.previousElementSibling;
    try { await navigator.clipboard.writeText(code.textContent); button.textContent = 'Copied!'; }
    catch (_) { const range = document.createRange(); range.selectNodeContents(code); const selection = window.getSelection(); selection.removeAllRanges(); selection.addRange(range); button.textContent = 'Selected - press Ctrl+C'; }
}));
window.addEventListener('pageshow', event => { if (event.persisted) location.reload(); });
