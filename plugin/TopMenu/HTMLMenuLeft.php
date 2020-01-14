<?php
$configFile = $global['systemRootPath'] . 'videos/configuration.php';

require_once $configFile;
require_once $global['systemRootPath'] . 'plugin/TopMenu/Objects/Menu.php';
require_once $global['systemRootPath'] . 'plugin/TopMenu/Objects/MenuItem.php';

$menu = Menu::getAllActive(2);
?>
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
            <?php echo $value['menuName']; ?>
        </h3>
    </li>
    <?php
    $menuItems = MenuItem::getAllFromMenu($value['id'], true);
    foreach ($menuItems as $key2 => $value2) {
        $url = $value2['url'];
        if (empty($url) || strpos($url, 'iframe:') !== false) {
            if (!empty($value2['menuSeoUrlItem'])) {
                $url = $global['webSiteRootURL'] . "menu/{$value2['menuSeoUrlItem']}";
            } else {
                $url = $global['webSiteRootURL'] . "plugin/TopMenu/?id={$value2['id']}";
            }
        }
        ?>
        <li>
            <a  href="<?php echo $url; ?>" >
                <?php
                if (!empty($value2['icon'])) {
                    ?>
                    <i class="<?php echo $value2['icon'] ?>"></i> 
                    <?php
                }
                ?>
                <?php echo $value2['title'] ?>
            </a>
        </li>            
        <?php
    }
}
?>
                
