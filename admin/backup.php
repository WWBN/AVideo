<div class="row">
    <div class="col-lg-9">
        <div class="panel panel-default">
            <div class="panel-heading"><i class="fas fa-undo"></i> Backup Files and Database <div class="pull-right"><?php echo getPluginSwitch('Backup'); ?></div></div>
            <div class="panel-body" style="overflow: hidden;">
                <?php
                include $global['systemRootPath'] . 'plugin/Backup/backupEditor_head.php';
                include $global['systemRootPath'] . 'plugin/Backup/backupEditor_body.php';
                ?>
            </div>
        </div>
    </div>
    <div class="col-lg-3">
        <div class="panel panel-default">
            <div class="panel-heading"><i class="fa fa-clone"></i> Clone Site <div class="pull-right"><?php echo getPluginSwitch('CloneSite'); ?></div></div>
            <div class="panel-body" style="overflow: hidden;">
                <?php
                $filter = array(
                    'cloneSiteURL' => 'You May find some help how to use Clone Plugin <a target="_blank" href="https://github.com/DanielnetoDotCom/YouPHPTube/wiki/Clone-Site-Plugin">here</a>');
                echo createTable("CloneSite", $filter);
                include $global['systemRootPath'] . 'plugin/CloneSite/pluginMenu.html';
                ?>
            </div>
        </div>
    </div>

</div>