<div class="row">
    <div class="col-md-12">
        <div class="panel panel-default">
            <div class="panel-heading"><i class="fa fa-cog"></i> Subscription Configuration </div>
            <div class="panel-body" style="overflow: hidden;">
                <?php
                $filter = array(
                    'displayTopBarSubscribeButton' => 'Show a button on the top to subscribe',
                    'displayLeftMenuSubscribeButton' => 'Show a button on the the left menu to subscribe',
                    'textSubscribe' => 'The button text label');
                createTable("Subscription", $filter);
                ?>
            </div>
        </div>
    </div>
</div>