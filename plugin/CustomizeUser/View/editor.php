<?php
require_once '../../../videos/configuration.php';
if (!User::isAdmin()) {
    forbiddenPage("Must be admin");
}
AVideoPlugin::loadPlugin("CustomizeUser");
$_page = new Page(array('Customize User'));
$_page->loadBasicCSSAndJS();
?>
<div class="container-fluid">
    <div class="panel panel-default">
        <div class="panel-heading"><i class="fas fa-user"></i> <?php echo __('CustomizeUser') ?></div>
        <div class="panel-body">
            <ul class="nav nav-tabs">
                <li class="active"><a data-toggle="tab" href="#Categories_has_users_groups"><i class="fas fa-list"></i> <?php echo __("Categories"); ?> <i class="fas fa-times"></i> <i class="fas fa-users"></i> <?php echo __("Categories Has Users Groups"); ?></a></li>
                <li class=""><a data-toggle="tab" href="#Users_extra_info"><i class="fas fa-address-book"></i> <?php echo __("Users Extra Info"); ?></a></li>
                <li class=""><a data-toggle="tab" href="#Users_affiliations"><i class="fas fa-child"></i> <?php echo __("Users Affiliations"); ?></a></li>
            </ul>
            <div class="tab-content">
                <div id="Categories_has_users_groups" class="tab-pane fade in active" style="padding: 10px;">
                    <?php
                    include $global['systemRootPath'] . 'plugin/CustomizeUser/View/Categories_has_users_groups/index_body.php';
                    ?>
                </div>
                <div id="Users_extra_info" class="tab-pane fade " style="padding: 10px;">
                    <?php
                    include $global['systemRootPath'] . 'plugin/CustomizeUser/View/Users_extra_info/index_body.php';
                    ?>
                </div>
                <div id="Users_affiliations" class="tab-pane fade" style="padding: 10px;">
                    <?php
                    include $global['systemRootPath'] . 'plugin/CustomizeUser/View/Users_affiliations/index_body.php';
                    ?>
                </div>
            </div>
        </div>
    </div>
</div>
<?php
$_page->print();
?>