<?php
require_once '../../../videos/configuration.php';

if (!User::isAdmin()) {
    forbiddenPage('Permission denied', true);
}

$plugin = AVideoPlugin::loadPluginIfEnabled('ReportVideo');
if (empty($plugin)) {
    forbiddenPage('Plugin disabled');
}

$reports = VideosReported::getAllReports(500);
$statusLabels = Video::$statusDesc;

$_page = new Page(array('Reported videos'));
?>
<div class="container-fluid">
    <div class="panel panel-default">
        <div class="panel-heading"><i class="fas fa-flag"></i> <?php echo __('Reported videos'); ?>
            <div class="pull-right">
                <?php echo AVideoPlugin::getSwitchButton("ReportVideo"); ?>
            </div>
        </div>
        <div class="panel-body">
            <p class="text-muted">
                <?php echo __('Reports sent from the website and from the mobile apps. Open the video to review it, then use the videos manager to unlist, deactivate or delete it and the users manager to deactivate the author.'); ?>
            </p>
            <div class="table-responsive">
                <table class="table table-striped table-condensed">
                    <thead>
                        <tr>
                            <th><?php echo __('Date'); ?></th>
                            <th><?php echo __('Video'); ?></th>
                            <th><?php echo __('Author'); ?></th>
                            <th><?php echo __('Reported by'); ?></th>
                            <th><?php echo __('Reason'); ?></th>
                            <th><?php echo __('Status'); ?></th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        if (empty($reports)) {
                            ?>
                            <tr><td colspan="7" class="text-center"><?php echo __('No reports'); ?></td></tr>
                            <?php
                        }
                        foreach ($reports as $row) {
                            $videos_id = intval($row['videos_id']);
                            $videoTitle = empty($row['video_title']) ? __('Video removed') : $row['video_title'];
                            $author = empty($row['video_users_id']) ? '' : User::getNameIdentificationById($row['video_users_id']);
                            $reporter = empty($row['users_id']) ? '' : User::getNameIdentificationById($row['users_id']);
                            $status = (string) @$row['video_status'];
                            $statusLabel = empty($statusLabels[$status]) ? $status : $statusLabels[$status];
                            $videoLink = empty($row['video_title']) ? '' : Video::getPermaLink($videos_id);
                            ?>
                            <tr>
                                <td><?php echo htmlspecialchars((string) $row['created']); ?></td>
                                <td>
                                    <?php if (!empty($videoLink)) { ?>
                                        <a href="<?php echo htmlspecialchars($videoLink); ?>" target="_blank"><?php echo htmlspecialchars($videoTitle); ?></a>
                                    <?php } else { ?>
                                        <?php echo htmlspecialchars($videoTitle); ?>
                                    <?php } ?>
                                    <small class="text-muted">#<?php echo $videos_id; ?></small>
                                </td>
                                <td><?php echo htmlspecialchars((string) $author); ?></td>
                                <td><?php echo htmlspecialchars((string) $reporter); ?></td>
                                <td><?php echo htmlspecialchars((string) $row['obs']); ?></td>
                                <td><?php echo htmlspecialchars((string) $statusLabel); ?></td>
                                <td class="text-right">
                                    <?php if (!empty($videoLink)) { ?>
                                        <a class="btn btn-default btn-xs" href="<?php echo $global['webSiteRootURL']; ?>mvideos?search=<?php echo urlencode($videoTitle); ?>" target="_blank"><i class="fas fa-edit"></i> <?php echo __('Manage video'); ?></a>
                                    <?php } ?>
                                    <?php if (!empty($row['video_users_id'])) { ?>
                                        <a class="btn btn-default btn-xs" href="<?php echo $global['webSiteRootURL']; ?>users?search=<?php echo urlencode((string) $author); ?>" target="_blank"><i class="fas fa-user"></i> <?php echo __('Manage user'); ?></a>
                                    <?php } ?>
                                </td>
                            </tr>
                            <?php
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<?php
$_page->print();
