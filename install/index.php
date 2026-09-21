<?php
ini_set('display_errors', '0');
require_once __DIR__ . '/installer.php';
$configured = installerConfigured();
if (!$configured) { installerSession(); }
header('Cache-Control: no-store');
header('X-Frame-Options: DENY');
function h($value) { return htmlspecialchars($value, ENT_QUOTES, 'UTF-8'); }
$configured = installerConfigured();
$checks = $configured ? [] : installerChecks();
$passedChecks = array_filter($checks, function ($check) { return $check['ok']; });
$failedChecks = array_filter($checks, function ($check) { return !$check['ok']; });
$ready = !in_array(false, array_column($checks, 'ok'), true);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Setup • AVideo Streamer</title>
    <link rel="icon" href="assets/favicon.png">
    <link rel="stylesheet" href="installer.css">
    <link rel="stylesheet" href="checklist.css">
    <script src="installer.js" defer></script>
</head>
<body>
<div class="shell">
    <aside class="sidebar">
        <a class="brand" href="https://avideo.com/" aria-label="AVideo"><img src="assets/logo.png" alt="AVideo" width="250" height="70"></a>
        <div class="product">VIDEO PLATFORM</div>
        <div class="sidebar-heading">Your videos.<br>Ready to<br><span>stream.</span></div>
        <p class="sidebar-copy">Create your own home for videos, live streams, and your community.</p>
        <?php if (!$configured): ?>
        <nav aria-label="Setup steps">
            <a href="#database"><span>01</span><div>Database<small>Connection and storage</small></div></a>
            <a href="#network"><span>02</span><div>Your site<small>Address and language</small></div></a>
            <a href="#streamer"><span>03</span><div>Administrator<small>Your first account</small></div></a>
        </nav>
        <?php endif; ?>
        <div class="sidebar-footer"><span class="signal" aria-hidden="true"></span> GUIDED SETUP<small>MySQL / MariaDB · Windows / Linux</small></div>
    </aside>
    <main>
        <header class="topbar"><span>Initial setup</span><span class="pill">AVideo Streamer</span></header>
        <div class="content">
        <?php if ($configured): ?>
            <section class="complete card">
                <span class="complete-icon" aria-hidden="true">✓</span>
                <div class="eyebrow">SETUP COMPLETE</div>
                <h1>Installation complete.</h1>
                <p>Setup is locked. Your application is ready to open.</p>
                <a class="button primary" href="../">Open application</a>
            </section>
        <?php else: ?>
            <div class="eyebrow">GET STARTED</div>
            <h1>Set up your video platform.</h1>
            <p class="intro">Enter your details below. We will create the database, install the tables,<br class="desktop"> and generate your configuration file.</p>
            <section class="environment" aria-labelledby="checklistHeading">
                <div class="environment-title"><span class="status-dot <?= $ready ? '' : 'bad' ?>" aria-hidden="true"></span><strong><?= $ready ? 'Environment ready' : 'Action required' ?></strong><span>Server check</span></div>
                <h2 id="checklistHeading" class="checklist-heading">Installation checklist</h2>
                <p class="checklist-summary"><?= count($passedChecks) ?> checks passed · <?= count($failedChecks) ?> need attention. Reload this page after making corrections.</p>
                <?php foreach (['Needs attention' => $failedChecks, 'Checks passed' => $passedChecks] as $heading => $group): if (!$group) { continue; } ?>
                <h3 class="checklist-heading"><?= h($heading) ?> (<?= count($group) ?>)</h3>
                <ul class="requirement-list">
                    <?php foreach ($group as $check): ?>
                    <li class="requirement-item <?= $check['ok'] ? 'passed' : 'failed' ?>">
                        <span class="requirement-icon" aria-hidden="true"><?= $check['ok'] ? '✓' : '✕' ?></span>
                        <div><strong><?= h($check['label']) ?></strong><span class="requirement-status"><?= $check['ok'] ? 'Check passed' : 'Missing or needs configuration' ?></span></div>
                    </li>
                    <?php endforeach; ?>
                </ul>
                <?php endforeach; ?>
                <p class="checklist-summary">These checks cover the installation requirements. Use <a href="#database">Test connection</a> below to verify database access. Upload limits and web server rules need a separate review below.</p>
                <?php if (!$ready): ?>
                    <details open class="requirement-help"><summary>How to resolve missing requirements</summary>
                        <?php foreach ($checks as $check): if ($check['ok']) { continue; } $help = installerRequirementHelp($check); ?>
                        <h3><?= h($check['label']) ?></h3><p><?= h($help['text']) ?></p>
                        <?php if (!empty($help['command'])): ?><pre><code><?= h($help['command']) ?></code></pre><?php endif; ?>
                        <?php endforeach; ?><p>Then reload this page.</p>
                    </details>
                <?php endif; ?>
                <details class="advisory"><summary>Upload limits and web server configuration</summary>
                    <p>For video uploads, set upload_max_filesize and post_max_size to at least 100M in the web server php.ini. Current values: <?= h(ini_get('upload_max_filesize')) ?> and <?= h(ini_get('post_max_size')) ?>.</p>
                    <p>Apache needs mod_rewrite and AllowOverride enabled. With Nginx, configure the equivalent AVideo rewrite rules before opening your site.</p>
                </details>
            </section>
            <?php include __DIR__ . '/ubuntu-help.php'; ?>
            <form id="configurationForm" data-ready="<?= $ready ? '1' : '0' ?>">
                <input type="hidden" name="install_csrf_token" value="<?= h($_SESSION['install_csrf_token']) ?>">
                <section class="card" id="database">
                    <div class="section-heading"><span class="number">01</span><div><h2>Database</h2><p>Where your video platform stores its data.</p></div><span class="tag">MySQL / MariaDB</span></div>
                    <div class="fields">
                        <div class="field wide"><label for="databaseHost">Database host</label><input id="databaseHost" name="databaseHost" value="localhost" required maxlength="253" autocomplete="off" spellcheck="false"><small>Use localhost if the database runs on this server.</small></div>
                        <div class="field narrow"><label for="databasePort">Port</label><input id="databasePort" name="databasePort" type="number" value="3306" min="1" max="65535" required></div>
                        <div class="field"><label for="databaseUser">Username</label><input id="databaseUser" name="databaseUser" value="root" required maxlength="80" autocomplete="off" spellcheck="false"></div>
                        <div class="field"><label for="databasePass">Database password <span class="optional">if applicable</span></label><div class="password-field"><input id="databasePass" name="databasePass" type="password" autocomplete="new-password"><button type="button" class="reveal" data-target="databasePass" aria-label="Show database password" aria-pressed="false">Show</button></div></div>
                        <div class="field full"><label for="databaseName">Database name</label><input id="databaseName" name="databaseName" value="avideo" pattern="[A-Za-z0-9_\-]{1,64}" maxlength="64" required spellcheck="false"><small>We will create this database if it does not exist. An existing database must contain no data.</small></div>
                    </div>
                    <div class="field full database-mode"><label for="createTables">Database setup</label><select id="createTables" name="createTables">
                        <option value="2">Create database and tables</option><option value="1">Create tables in an existing empty database</option><option value="0">Use an empty schema imported manually</option>
                    </select><small>Existing sites must be recovered from their configuration backup.</small></div>
                    <div class="card-footer"><span>Testing the connection does not change any data.</span><button type="button" class="button secondary" id="testConnection">Test connection <span aria-hidden="true">↗</span></button></div>
                    <div id="connectionResult" class="inline-result" role="status" hidden></div>
                </section>
                <section class="card" id="network">
                    <div class="section-heading"><span class="number">02</span><div><h2>Your site</h2><p>Give your platform a name and a public address.</p></div></div>
                    <div class="fields">
                        <div class="field full"><label for="webSiteRootURL">Site URL</label><input id="webSiteRootURL" name="webSiteRootURL" type="url" value="<?= h(installerURL()) ?>" required maxlength="254" spellcheck="false"><small>The address viewers will use, including any port or subdirectory.</small></div>
                        <div class="field"><label for="webSiteTitle">Site title</label><input id="webSiteTitle" name="webSiteTitle" value="AVideo" maxlength="45" required></div>
                        <div class="field"><label for="mainLanguage">Site language</label><select id="mainLanguage" name="mainLanguage"><?php foreach (installerLanguages() as $code => $label): ?><option value="<?= h($code) ?>" <?= $code === 'en_US' ? 'selected' : '' ?>><?= h($label) ?></option><?php endforeach; ?></select></div>
                        <div class="field full"><label for="contactEmail">Contact email</label><input id="contactEmail" name="contactEmail" type="email" required maxlength="254" autocomplete="email" placeholder="you@example.com"><small>Used for your administrator account and site contact details.</small></div>
                    </div>
                    <details class="path-details"><summary>Automatically detected directory</summary><code><?= h(installerRoot()) ?></code><p>Setup writes the configuration to the videos directory of this installation.</p></details>
                    <input type="hidden" name="systemRootPath" value="<?= h(installerRoot()) ?>">
                </section>
                <section class="card" id="streamer">
                    <div class="section-heading"><span class="number">03</span><div><h2>Administrator</h2><p>Create the first account for your new platform.</p></div></div>
                    <div class="fields">
                        <div class="field full"><label for="adminUsername">Username</label><input id="adminUsername" value="admin" readonly autocomplete="username"><small>Sign in as admin after installation.</small></div>
                        <div class="field"><label for="systemAdminPass">Administrator password</label><div class="password-field"><input id="systemAdminPass" name="systemAdminPass" type="password" required autocomplete="new-password"><button type="button" class="reveal" data-target="systemAdminPass" aria-label="Show administrator password" aria-pressed="false">Show</button></div></div>
                        <div class="field"><label for="confirmSystemAdminPass">Confirm password</label><div class="password-field"><input id="confirmSystemAdminPass" name="confirmSystemAdminPass" type="password" required autocomplete="new-password"><button type="button" class="reveal" data-target="confirmSystemAdminPass" aria-label="Show password confirmation" aria-pressed="false">Show</button></div></div>
                    </div>
                    <div class="info"><span aria-hidden="true">i</span><p>Keep this password somewhere safe. You will need it to manage your site.</p></div>
                </section>
                <section id="result" class="card result" tabindex="-1" aria-live="polite" hidden></section>
                <div class="submit-row"><p><strong>Everything in one step.</strong><br>Database, tables, and configuration.php.</p><button class="button primary" id="installButton" type="submit" <?= $ready ? '' : 'disabled' ?>>Install AVideo <span aria-hidden="true">→</span></button></div>
                <p class="install-note">If anything goes wrong, instructions and commands to run on your server will appear here.</p>
            </form>
            <noscript><p class="info">Enable JavaScript in your browser to test the connection and run setup.</p></noscript>
        <?php endif; ?>
        <footer class="page-footer"><span>AVideo Streamer</span><span>Your videos. Your community.</span></footer>
        </div>
    </main>
</div>
</body>
</html>
