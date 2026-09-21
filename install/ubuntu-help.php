<?php if (!defined('INSTALLER_PRODUCT')) { return; }
if (!installerConfigured() && installerHasMissingDependencies($checks ?? [])): ?>
<details class="card ubuntu-help">
    <summary>Ubuntu setup help and troubleshooting commands</summary>
    <?php foreach (installerUbuntuHelp() as $help): ?>
    <section><h3><?= h($help['title']) ?></h3><p><?= h($help['text']) ?></p><pre><code><?= h($help['command']) ?></code></pre><button class="copy" type="button">Copy commands</button></section>
    <?php endforeach; ?>
    <p><a href="https://ubuntu.com/server/docs/how-to/web-services/install-php/" target="_blank" rel="noopener noreferrer">Ubuntu PHP installation documentation</a></p>
</details>
<?php endif; ?>
