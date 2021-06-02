<?php
$configFile = $global['systemRootPath'] . 'videos/configuration.php';

require_once $configFile;
require_once $global['systemRootPath'] . 'plugin/TopMenu/Objects/Menu.php';
require_once $global['systemRootPath'] . 'plugin/TopMenu/Objects/MenuItem.php';

$menu = Menu::getAllActive(Menu::$typeLeftMenu);
?>
<!-- left menu start -->
<?php
foreach ($menu as $key => $value) {
    ?>
    <li>
        <hr>
        <h3 class="text-danger">
            <?php
            if (!empty($value['icon'])) {
                ?>
                <i class="<?php echo $value['icon'] ?>"></i> 
                <?php
            }
            ?>
            <?php echo __($value['menuName']); ?>
        </h3>
    </li>
    <?php
    $menuItems = MenuItem::getAllFromMenu($value['id'], true);
    foreach ($menuItems as $key2 => $value2) {
        ?>
        <li>
            <a  href="<?php echo $value2['finalURL']; ?>" <?php echo $value2['target']; ?> >
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
}
?> 
<!-- left menu end -->       
