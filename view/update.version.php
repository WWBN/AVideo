<?php
// Shared by the update page and health check, after their administrator checks.
if (!isset($versionOverview, $config, $updateFiles)) {
    return;
}
$escapeVersion = function ($value) {
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
};
$formatVersionDate = function ($value) use ($escapeVersion) {
    $time = strtotime($value ?? '');
    return $time ? $escapeVersion(gmdate('Y-m-d H:i', $time) . ' UTC') : $escapeVersion(__('Unavailable'));
};
$releaseStates = [
    'identical' => ['success', 'Latest release installed', 'The installed commit matches the latest published release.'],
    'ahead' => ['info', 'Newer than the latest release', 'This installation includes commits made after the latest published release.'],
    'behind' => ['warning', 'New release available', 'The latest published release includes changes not present in this installation.'],
    'diverged' => ['info', 'Different development history', 'The installed code and the latest release have different changes. Review the comparison before updating.'],
    'unknown' => ['muted', 'Release status unavailable', 'The installed code could not be compared with the latest release. This does not confirm that the system is up to date.']
];
$releaseState = $releaseStates[$versionOverview['status']];
$repositoryStates = [
    'identical' => ['success', 'Your code is up to date', 'The installed commit matches the latest commit on GitHub (master).', 'fa-check'],
    'behind' => ['warning', 'Your code is outdated', 'GitHub (master) contains newer commits that are not installed on this server.', 'fa-arrow-up'],
    'ahead' => ['info', 'Ahead of GitHub', 'The installed commit includes changes beyond GitHub (master).', 'fa-code-branch'],
    'diverged' => ['warning', 'Commit histories have diverged', 'This installation and GitHub (master) both contain exclusive changes. Review the comparison before updating.', 'fa-code-branch'],
    'unknown' => ['muted', 'Update status unknown', 'The installed commit could not be compared with GitHub (master). Update status is unknown.', 'fa-question']
];
$repositoryState = $repositoryStates[$versionOverview['repository_status']];
$installed = $versionOverview['local'];
$latestRelease = $versionOverview['release'];
$latestMaster = $versionOverview['master'];
$githubURL = 'https://github.com/WWBN/AVideo';
$versionHeadingTag = empty($versionOverviewCompact) ? 'h1' : 'h2';
?>
<div class="version-dashboard<?php echo empty($versionOverviewCompact) ? '' : ' version-dashboard-compact'; ?>">
    <div class="version-toolbar">
        <p class="version-eyebrow"><i class="fas fa-code-branch" aria-hidden="true"></i> AVideo <span aria-hidden="true">/</span> <?php echo __('Updates & versions'); ?></p>
        <span class="version-source text-muted"><i class="fab fa-github" aria-hidden="true"></i> WWBN/AVideo <span aria-hidden="true">&middot;</span> master</span>
    </div>
    <section class="panel panel-default version-hero" aria-label="<?php echo $escapeVersion(__('Code update status')); ?>">
        <div class="version-hero-glow text-<?php echo $repositoryState[0]; ?>" aria-hidden="true"></div>
        <div class="version-hero-main">
            <div class="version-hero-copy">
                <p class="version-eyebrow text-<?php echo $repositoryState[0]; ?>"><?php echo __('Code update status'); ?></p>
                <<?php echo $versionHeadingTag; ?> class="version-hero-title"><?php echo __($repositoryState[1]); ?></<?php echo $versionHeadingTag; ?>>
                <p class="version-hero-description text-muted"><?php echo __($repositoryState[2]); ?></p>
                <div class="version-actions">
                    <a class="btn btn-<?php echo $repositoryState[0] === 'muted' ? 'default' : $repositoryState[0]; ?>" href="https://github.com/WWBN/AVideo/wiki/How-to-Update-your-AVideo-Platform" target="_blank" rel="noopener noreferrer"><i class="fas fa-book-open" aria-hidden="true"></i> <?php echo __('Update guide'); ?> <i class="fas fa-external-link-alt" aria-hidden="true"></i></a>
                    <?php if ($installed['sha'] && $latestMaster && $installed['sha'] !== $latestMaster['sha']) { ?>
                        <a class="btn btn-default" target="_blank" rel="noopener noreferrer" href="<?php echo $githubURL . '/compare/' . $latestMaster['sha'] . '...' . $installed['sha']; ?>"><?php echo __('Compare with GitHub'); ?> <i class="fas fa-arrow-right" aria-hidden="true"></i></a>
                    <?php } ?>
                </div>
            </div>
            <div class="version-status-seal text-<?php echo $repositoryState[0]; ?>" aria-hidden="true">
                <div class="version-status-ring"><i class="fas <?php echo $repositoryState[3]; ?>"></i></div>
            </div>
        </div>
        <div class="version-commit-strip">
            <div class="version-commit">
                <p class="version-eyebrow text-muted"><i class="fas fa-server" aria-hidden="true"></i> <?php echo __('Installed code'); ?></p>
                <p class="version-commit-value"><?php if ($installed['sha']) { ?><a href="<?php echo $githubURL . '/commit/' . $installed['sha']; ?>" target="_blank" rel="noopener noreferrer"><?php echo substr($installed['sha'], 0, 12); ?> <i class="fas fa-external-link-alt" aria-hidden="true"></i></a><?php } else { echo __('Unavailable'); } ?></p>
                <p class="small text-muted"><?php echo __('Branch'); ?>: <?php echo $escapeVersion($installed['branch'] ?: ($installed['sha'] ? __('Detached HEAD') : __('Unavailable'))); ?><?php if ($installed['tag']) { ?> <span aria-hidden="true">&middot;</span> <?php echo __('Git tag'); ?>: <?php echo $escapeVersion($installed['tag']); ?><?php } ?></p>
                <p class="small text-muted"><?php echo $installed['sha'] ? $formatVersionDate($installed['date']) : __('Git metadata is unavailable in this installation.'); ?></p>
            </div>
            <div class="version-commit-connector text-<?php echo $repositoryState[0]; ?>" aria-hidden="true"><i class="fas <?php echo $versionOverview['repository_status'] === 'identical' ? 'fa-equals' : 'fa-code-compare'; ?>"></i></div>
            <div class="version-commit">
                <p class="version-eyebrow text-muted"><i class="fab fa-github" aria-hidden="true"></i> <?php echo __('Latest on GitHub'); ?></p>
                <p class="version-commit-value"><?php if ($latestMaster) { ?><a href="<?php echo $githubURL . '/commit/' . $latestMaster['sha']; ?>" target="_blank" rel="noopener noreferrer"><?php echo substr($latestMaster['sha'], 0, 12); ?> <i class="fas fa-external-link-alt" aria-hidden="true"></i></a><?php } else { echo __('Unavailable'); } ?></p>
                <p class="small text-muted"><?php echo __('Last commit on GitHub'); ?></p>
                <p class="small text-muted"><?php echo $latestMaster ? $formatVersionDate($latestMaster['commit']['committer']['date'] ?? '') : __('GitHub information is temporarily unavailable.'); ?></p>
            </div>
        </div>
        <div class="version-check-time text-muted"><i class="far fa-clock" aria-hidden="true"></i> <?php echo __('GitHub check'); ?>: <?php echo $formatVersionDate(gmdate('c', $versionOverview['checked_at'])); ?> <span aria-hidden="true">&middot;</span> <?php echo __('Remote information is cached for up to 15 minutes.'); ?></div>
    </section>
    <div class="version-section-intro">
        <h2><?php echo __('Release & database'); ?></h2>
        <p class="text-muted"><?php echo __('The release number and the AVideo database version are independent. Code update status is determined by commit history.'); ?></p>
    </div>
    <div class="version-cards">
        <section class="panel panel-default version-card">
            <div class="version-card-heading"><h3><i class="fas fa-tag" aria-hidden="true"></i> <?php echo __('Latest release'); ?></h3><span class="version-pill text-<?php echo $releaseState[0]; ?>"><?php echo __($releaseState[1]); ?></span></div>
            <p class="version-value"><?php echo $latestRelease ? $escapeVersion($latestRelease['tag_name']) : __('Unavailable'); ?></p>
            <p class="text-muted"><?php echo __($releaseState[2]); ?></p>
            <p class="small text-muted"><?php if ($versionOverview['release_sha']) { ?><?php echo __('Commit'); ?>: <span class="version-hash"><?php echo substr($versionOverview['release_sha'], 0, 12); ?></span> <span aria-hidden="true">&middot;</span> <?php } ?><?php echo $latestRelease ? $formatVersionDate($latestRelease['published_at'] ?? '') : __('GitHub information is temporarily unavailable.'); ?></p>
            <div class="version-card-links"><a href="<?php echo $githubURL . ($latestRelease ? '/releases/tag/' . rawurlencode($latestRelease['tag_name']) : '/releases'); ?>" target="_blank" rel="noopener noreferrer"><?php echo __('Release notes'); ?> <i class="fas fa-external-link-alt" aria-hidden="true"></i></a>
                <?php if ($installed['sha'] && $versionOverview['release_sha'] && $installed['sha'] !== $versionOverview['release_sha']) { ?><a target="_blank" rel="noopener noreferrer" href="<?php echo $githubURL . '/compare/' . $versionOverview['release_sha'] . '...' . $installed['sha']; ?>"><?php echo __('Compare with release'); ?></a><?php } ?>
            </div>
        </section>
        <section class="panel panel-default version-card">
            <div class="version-card-heading"><h3><i class="fas fa-database" aria-hidden="true"></i> <?php echo __('AVideo version (database)'); ?></h3><span class="version-pill text-<?php echo $updateFiles ? 'warning' : 'success'; ?>"><i class="fas <?php echo $updateFiles ? 'fa-arrow-up' : 'fa-check'; ?>" aria-hidden="true"></i> <?php echo $updateFiles ? sprintf(__('%s pending database updates'), count($updateFiles)) : __('No pending local migrations'); ?></span></div>
            <p class="version-value"><?php echo $escapeVersion($config->getVersion()); ?></p>
            <p class="text-muted"><?php echo __('Tracks database migrations, independently of releases.'); ?></p>
            <p class="small text-muted"><?php echo __('The database version changes only when a database migration is applied. It is not expected to match the release tag or commit.'); ?></p>
        </section>
    </div>
    <details class="version-explanation">
        <summary><i class="fas fa-info-circle" aria-hidden="true"></i> <?php echo __('Why are these versions different?'); ?></summary>
        <dl>
            <dt><?php echo __('Installed code'); ?></dt><dd class="text-muted"><?php echo __('The commit identifies the Git revision checked out on this server; local file edits are not included. A Git tag is shown when one points directly to that commit.'); ?></dd>
            <dt><?php echo __('Latest release'); ?></dt><dd class="text-muted"><?php echo __('A release is a published snapshot with its own version and release notes. The master branch can contain newer changes that have not been released yet.'); ?></dd>
        </dl>
    </details>
</div>
