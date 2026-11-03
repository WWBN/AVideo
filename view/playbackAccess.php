<?php
require_once __DIR__ . '/../videos/configuration.php';
require_once __DIR__ . '/../objects/PlaybackAccess.php';
if (!User::isAdmin()) {
    forbiddenPage('Only an admin can configure playback access', true);
}
$scope = !empty($_GET['videos_id']) ? 'video' : 'category';
$scopeId = intval($_GET['videos_id'] ?? $_GET['categories_id'] ?? 0);
$table = $scope === 'video' ? 'videos' : 'categories';
$nameColumn = $scope === 'video' ? 'title' : 'name';
$res = sqlDAL::readSql("SELECT {$nameColumn} AS name FROM {$table} WHERE id = ?", 'i', [$scopeId]);
$target = sqlDAL::fetchAssoc($res);
sqlDAL::close($res);
if (!$target) {
    forbiddenPage('Save and select a video or category first');
}
$installed = PlaybackAccess::isInstalled();
$stored = $effective = null;
$loadError = false;
try {
    $stored = $installed ? PlaybackAccess::getStoredRule($scope, $scopeId) : null;
    $effective = $installed ? ($scope === 'video' ? PlaybackAccess::getVideoRule($scopeId) : PlaybackAccess::getCategoryRule($scopeId)) : null;
} catch (Throwable $th) {
    $loadError = true;
    _error_log($th->getMessage(), AVideoLog::$ERROR);
}
$mode = $stored['mode'] ?? 'inherit';
$groups = UserGroups::getAllUsersGroups();
$groupNames = [];
foreach ($groups as $group) {
    $groupNames[intval($group['id'])] = $group['group_name'];
}
$page = new Page([__('Playback access rules')]);
?>
<div class="container">
    <div class="panel panel-default">
        <div class="panel-heading">
            <div class="pull-right"><?php echo getTourHelpButton('view/playbackAccess.help.json', 'btn btn-default btn-xs'); ?></div>
            <h1><?php echo __('Playback access rules'); ?></h1>
            <p><?php echo htmlspecialchars($target['name'], ENT_QUOTES, 'UTF-8'); ?> (<?php echo htmlspecialchars($scope); ?> #<?php echo $scopeId; ?>)</p>
        </div>
        <div class="panel-body">
            <div class="alert alert-info" id="playbackAccessSummary">
                <strong><?php echo __('Current effective rule'); ?>:</strong>
                <?php if ($loadError) {
                    echo __('Could not resolve the saved rule. Playback is blocked until the rule is repaired.');
                } elseif ($effective && $effective['mode'] === 'require') { ?>
                    <?php echo __('Require membership in at least one of these user groups'); ?>:
                    <?php echo htmlspecialchars(implode(', ', array_map(function ($id) use ($groupNames) { return $groupNames[$id] ?? ('#' . $id); }, $effective['groups'])), ENT_QUOTES, 'UTF-8'); ?>.
                    <?php echo htmlspecialchars($effective['scope']); ?> #<?php echo intval($effective['scope_id']); ?>
                <?php } else { echo __('No mandatory group restriction. Existing visibility and payment rules still apply.'); } ?>
            </div>
            <p><?php echo __('This requirement is checked before PPV, subscriptions and free playback with ads. It does not grant free access or change advertising benefits.'); ?></p>
            <?php if (!$installed) { ?>
                <div class="alert alert-warning"><?php echo __('Update the database before configuring playback access'); ?></div>
            <?php } ?>
            <form id="playbackAccessForm">
                <div class="form-group" id="playbackAccessModeGroup">
                    <label for="playbackAccessMode"><?php echo __('Mandatory playback rule'); ?></label>
                    <select class="form-control" id="playbackAccessMode" name="mode">
                        <option value="inherit" <?php echo $mode === 'inherit' ? 'selected' : ''; ?>><?php echo __('Inherit from category or parent category'); ?></option>
                        <option value="require" <?php echo $mode === 'require' ? 'selected' : ''; ?>><?php echo __('Require membership in selected user groups'); ?></option>
                        <option value="unrestricted" <?php echo $mode === 'unrestricted' ? 'selected' : ''; ?>><?php echo __('Explicit exception: no mandatory group restriction'); ?></option>
                    </select>
                    <p class="help-block"><?php echo __('With no inherited rule, normal access rules apply. Category rules include subcategories and future videos. An explicit exception overrides inheritance; it does not make paid videos free.'); ?></p>
                </div>
                <fieldset id="playbackAccessGroups">
                    <legend><?php echo __('Required user groups'); ?></legend>
                    <p><?php echo __('A viewer needs at least one selected group. Select only groups that should grant this prerequisite, not a general rental or registered-users group.'); ?></p>
                    <?php foreach ($groups as $group) { ?>
                        <div class="checkbox"><label>
                            <input type="checkbox" name="groups[]" value="<?php echo intval($group['id']); ?>" <?php echo in_array(intval($group['id']), $stored['groups'] ?? [], true) ? 'checked' : ''; ?>>
                            <?php echo htmlspecialchars($group['group_name'], ENT_QUOTES, 'UTF-8'); ?> (#<?php echo intval($group['id']); ?>)
                        </label></div>
                    <?php } ?>
                </fieldset>
                <button type="submit" class="btn btn-primary" <?php echo $installed ? '' : 'disabled'; ?>><i class="fa fa-save"></i> <?php echo __('Save'); ?></button>
            </form>
            <?php if ($scope === 'video') { ?>
                <hr>
                <form method="get" id="playbackAccessTest">
                    <input type="hidden" name="videos_id" value="<?php echo $scopeId; ?>">
                    <label for="playbackTestUser"><?php echo __('Test the saved mandatory rule for user ID (0 = visitor)'); ?></label>
                    <div class="input-group">
                        <input id="playbackTestUser" name="test_user" type="number" min="0" class="form-control" value="<?php echo intval($_GET['test_user'] ?? 0); ?>">
                        <span class="input-group-btn"><button class="btn btn-default" type="submit"><?php echo __('Test'); ?></button></span>
                    </div>
                </form>
                <?php if (isset($_GET['test_user'])) { ?>
                    <p class="alert alert-info"><?php echo PlaybackAccess::isAllowed($scopeId, intval($_GET['test_user'])) ? __('Mandatory group requirement passed. Normal visibility and payment checks still apply.') : __('Blocked by the mandatory group requirement, including playback with ads.'); ?></p>
                <?php } ?>
            <?php } ?>
        </div>
    </div>
</div>
<script>
$(function () {
    function updatePlaybackGroups() {
        $('#playbackAccessGroups').toggle($('#playbackAccessMode').val() === 'require');
    }
    $('#playbackAccessMode').on('change', updatePlaybackGroups);
    updatePlaybackGroups();
    $('#playbackAccessForm').on('submit', function (event) {
        event.preventDefault();
        modal.showPleaseWait();
        $.ajax({
            type: 'POST',
            url: webSiteRootURL + 'objects/playbackAccess.save.json.php',
            data: $(this).serialize() + '&scope=<?php echo $scope; ?>&scope_id=<?php echo $scopeId; ?>&globalToken=' + encodeURIComponent(<?php echo json_encode(getToken(3600)); ?>),
            success: function (response) {
                if (response.error) { avideoResponse(response); } else { location.reload(); }
            },
            error: function () { avideoToastError(<?php echo json_encode(__('Request failed. Please check your connection.')); ?>); },
            complete: function () { modal.hidePleaseWait(); }
        });
    });
});
</script>
<?php $page->print(); ?>
