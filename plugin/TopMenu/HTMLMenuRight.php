<?php
$configFile = $global['systemRootPath'] . 'videos/configuration.php';


require_once $configFile;
require_once $global['systemRootPath'] . 'plugin/TopMenu/Objects/Menu.php';
require_once $global['systemRootPath'] . 'plugin/TopMenu/Objects/MenuItem.php';
$objTopMenu = AVideoPlugin::getDataObject('TopMenu');
$menu = Menu::getAllActive(Menu::$typeTopMenu);
$dropdownClass = '';
?>
<!-- right menu start -->
<?php
if (count($menu) < $objTopMenu->compactMenuIfIsGreaterThen->value) {
    $dropdownClass = 'hidden-lg';
    foreach ($menu as $key => $value) {
?>
        <li class="dropdown visible-lg">
            <a href="#" class=" btn  btn-default btn-light navbar-btn" data-toggle="dropdown" data-toggle="tooltip" title="<?php echo $value['menuName']; ?>" data-placement="bottom">
                <?php
                $hiddenClass = "hidden-md hidden-sm";
                if (!empty($value['icon'])) {
                ?>
                    <i class="<?php echo $value['icon'] ?>"></i>
                <?php
                    $hiddenClass = "hidden-md hidden-sm  hidden-mdx";
                }
                ?>
                <span class="<?php echo $hiddenClass; ?>">
                    <?php echo __($value['menuName']); ?>
                </span>
                <b class="caret"></b>
            </a>
            <ul class="dropdown-menu dropdown-menu-right" id="availableLive">
                <?php
                $menuItems = MenuItem::getAllFromMenu($value['id'], true);
                foreach ($menuItems as $key2 => $value2) {
                ?>
                    <li style="margin-right: 0;">
                        <a href="<?php echo $value2['finalURL']; ?>" <?php echo $value2['target']; ?>>
                            <?php
                            if (!empty($value2['icon'])) {
                            ?>
                                <i class="<?php echo $value2['icon'] ?>"></i>
                            <?php
                            }
                            ?>
                            <?php echo __($value2['title']); ?>
                        </a>
                    </li>
                <?php
                }
                ?>
            </ul>
        </li>
<?php
    }
}
?>
<!-- This is for smaller screens (the hamburger menu) -->
<div class="<?php echo $dropdownClass; ?>">
    <li class="dropdown">
        <a href="#" class="btn btn-default btn-light navbar-btn" data-toggle="dropdown">
            <i class="fas fa-bars"></i> <b class="caret"></b>
        </a>
        <ul class="dropdown-menu dropdown-menu-right">
            <?php foreach ($menu as $key => $value) : ?>
                <li class="dropdown-header"><?php echo __($value['menuName']); ?></li>
                <?php
                $menuItems = MenuItem::getAllFromMenu($value['id'], true);
                foreach ($menuItems as $key2 => $value2) :
                ?>
                    <li style="margin-right: 0;">
                        <a href="<?php echo $value2['finalURL']; ?>" <?php echo $value2['target']; ?>>
                            <?php
                            if (!empty($value2['icon'])) {
                            ?>
                                <i class="<?php echo $value2['icon'] ?>"></i>
                            <?php
                            }
                            ?>
                            <?php echo __($value2['title']); ?>
                        </a>
                    </li>
                <?php endforeach; ?>
            <?php endforeach; ?>
        </ul>

    </li>
</div>
<!-- right menu start -->