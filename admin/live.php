<div class="row">
    <div class="col-md-6">
        <div class="panel panel-default">
            <div class="panel-heading"><?php echo __('Live') ?> <div class="pull-right"><?php echo getPluginSwitch('Live'); ?></div></div>
            <div class="panel-body">
                <?php
                
                createTable("Live");
                ?>

            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="panel panel-default">
            <div class="panel-heading"><?php echo __('Live Chat') ?> <div class="pull-right"><?php echo getPluginSwitch('LiveChat'); ?></div></div>
            <div class="panel-body">
                <?php
                
                createTable("LiveChat");
                ?>

            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="panel panel-default">
            <div class="panel-heading"><?php echo __('Live Users') ?> <div class="pull-right"><?php echo getPluginSwitch('LiveUsers'); ?></div></div>
            <div class="panel-body">
                <?php
                
                $filter = array(
                    'doNotDisplayCounter' => 'It will collect usage info but will not display the counter on the live video');
                
                createTable("LiveUsers",$filter);
                ?>

            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="panel panel-default">
            <div class="panel-heading"><?php echo __('Live Links') ?> <div class="pull-right"><?php echo getPluginSwitch('LiveLinks'); ?></div></div>
            <div class="panel-body">
                <?php
                
                createTable("LiveLinks");
                ?>

            </div>
        </div>
    </div>
</div>