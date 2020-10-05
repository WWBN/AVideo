<div class="row">
    <div class="col-lg-9">
        <div class="panel panel-default">
            <div class="panel-heading"><i class="fas fa-undo"></i> <?php echo __('Backup Files and Database'); ?> <div class="pull-right"><?php echo getPluginSwitch('Backup'); ?></div></div>
            <div class="panel-body" style="overflow: hidden;">
                <?php
                if (AVideoPlugin::exists('Backup')) {
                    include $global['systemRootPath'] . 'plugin/Backup/backupEditor_head.php';
                    include $global['systemRootPath'] . 'plugin/Backup/backupEditor_body.php';
                    ?>
                    <hr>
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i> <?php echo __('As a database increases in size full database backups take more time to complete, and require more storage space. please be patience'); ?>
                    </div>
                    <?php
                } else {
                    ?>
                    <div class="alert alert-info">
                        <h2><i class="fas fa-undo"></i> <?php echo __('Prevent Data Loss'); ?></h2>
                        <?php echo __('Backing up your video files and databases, running test restores procedures on your backups, and storing copies of backups in a safe, off-site location protects you from potentially catastrophic data loss. Backing up is the only way to protect your data.'); ?>
                        <br> <?php echo __('We can help you with this task,'); ?>
                        <a class="btn btn-info btn-sm btn-xs" href="https://youphp.tube/plugins/"><?php echo __('Buy our Backup Plugin Now'); ?></a>
                    </div>
                    <?php
                }
                ?>
            </div>
        </div>
    </div>
    <div class="col-lg-3">
        <div class="panel panel-default">
            <div class="panel-heading"><i class="fa fa-clone"></i> Clone Site <div class="pull-right"><?php echo getPluginSwitch('CloneSite'); ?></div></div>
            <div class="panel-body" style="overflow: hidden;">
                <div class="alert alert-info">
                    <i class="fas fa-info-circle"></i> <?php echo __('This Plugin helps you to clone your video site, it is really helpful for backup routines, load balance, etc.'); ?><br>
                    <?php echo __('You May find some help how to use Clone Plugin'); ?> <a target="_blank" href="https://github.com/WWBN/AVideo/wiki/Clone-Site-Plugin" rel="noopener noreferrer"><?php echo __('here'); ?></a>
                </div>
                <?php
                $filter = array(
                    'cloneSiteURL' => __('Place here the URL of the site you want to clone'));
                echo createTable("CloneSite", $filter);
                include $global['systemRootPath'] . 'plugin/CloneSite/pluginMenu.html';
                ?>
            </div>
        </div>
    </div>

</div>
