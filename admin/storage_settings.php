<div class="row">
    <div class="col-md-6">
        <div class="panel panel-default">
            <div class="panel-heading"><i class="fab fa-aws"></i> S3 Storage <div class="pull-right"><?php echo getPluginSwitch('AWS_S3'); ?></div></div>
            <div class="panel-body" style="overflow: hidden;">
                <?php
                createTable("AWS_S3");
                ?>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="panel panel-default">
            <div class="panel-heading"><i class="fas fa-hdd"></i> B2 Storage <div class="pull-right"><?php echo getPluginSwitch('Blackblaze_B2'); ?></div></div>
            <div class="panel-body" style="overflow: hidden;">
                <?php
                createTable("Blackblaze_B2");
                ?>
            </div>
        </div>
    </div>
    <div class="col-md-12">
        <div class="panel panel-default">
            <div class="panel-heading"><i class="fas fa-hdd"></i> FTP <div class="pull-right"><?php echo getPluginSwitch('FTP_Storage'); ?></div></div>
            <div class="panel-body" style="overflow: hidden;">
                <?php
                createTable("FTP_Storage");
                ?>
            </div>
        </div>
    </div>
</div>