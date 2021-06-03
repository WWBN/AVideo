<?php
$configFile = $global['systemRootPath'] . 'videos/configuration.php';


require_once $configFile;
require_once $global['systemRootPath'] . 'plugin/TopMenu/Objects/Menu.php';
require_once $global['systemRootPath'] . 'plugin/TopMenu/Objects/MenuItem.php';

$menu = Menu::getAllActive(Menu::$typeActionMenu);
?>
<!-- action menu start -->
<?php
foreach ($menu as $key => $value) {
    $menuItems = MenuItem::getAllFromMenu($value['id'], true);
    foreach ($menuItems as $key2 => $value2) {
        ?>
        <div>
            <a href="<?php echo $value2['finalURL']; ?>" <?php echo $value2['target']; ?>  class="text-primary">
                <?php
                if (!empty($value2['icon'])) {
                    ?>
                    <i class="<?php echo $value2['icon'] ?>"></i> 
                    <?php
                }
                ?>
                <span  class="hidden-sm hidden-xs">
                    <?php echo __($value2['title']); ?>
                </span>
            </a> 
        </div>          
        <?php
    }
}
$menu = Menu::getAllActive(Menu::$typeActionMenuCustomURL);
foreach ($menu as $key => $value) {
    $menuItems = MenuItem::getAllFromMenu($value['id'], true);
    foreach ($menuItems as $key2 => $value2) {
        $url = TopMenu::getVideoMenuURL($videos_id, $value2['id']);
        if (empty($url)) {
            continue;
        }
        ?>
        <div>
            <a href="<?php echo $url; ?>" <?php echo $value2['target']; ?>  class="text-primary">
                <?php
                if (!empty($value2['icon'])) {
                    ?>
                    <i class="<?php echo $value2['icon'] ?>"></i> 
                    <?php
                }
                ?>
                <span  class="hidden-sm hidden-xs">
                    <?php echo __($value2['title']); ?>
                </span>
            </a> 
        </div>  
        <?php
    }
}
if (User::isLogged()) {
    $menu = Menu::getAllActive(Menu::$typeActionMenuCustomURLForLoggedUsers);
    foreach ($menu as $key => $value) {
        $menuItems = MenuItem::getAllFromMenu($value['id'], true);
        foreach ($menuItems as $key2 => $value2) {
            $url = TopMenu::getVideoMenuURL($videos_id, $value2['id']);
            if (empty($url)) {
                continue;
            }
            ?>
            <div>
                <a href="<?php echo $url; ?>" <?php echo $value2['target']; ?>  class="text-primary">
                    <?php
                    if (!empty($value2['icon'])) {
                        ?>
                        <i class="<?php echo $value2['icon'] ?>"></i> 
                        <?php
                    }
                    ?>
                    <span  class="hidden-sm hidden-xs">
                        <?php echo __($value2['title']); ?>
                    </span>
                </a> 
            </div>  
            <?php
        }
    }
    if (User::canWatchVideo($videos_id)) {
        $menu = Menu::getAllActive(Menu::$typeActionMenuCustomURLForUsersThatCanWatchVideo);
        foreach ($menu as $key => $value) {
            $menuItems = MenuItem::getAllFromMenu($value['id'], true);
            foreach ($menuItems as $key2 => $value2) {
                $url = TopMenu::getVideoMenuURL($videos_id, $value2['id']);
                if (empty($url)) {
                    continue;
                }
                ?>
                <div>
                    <a href="<?php echo $url; ?>" <?php echo $value2['target']; ?>  class="text-primary">
                        <?php
                        if (!empty($value2['icon'])) {
                            ?>
                            <i class="<?php echo $value2['icon'] ?>"></i> 
                            <?php
                        }
                        ?>
                        <span  class="hidden-sm hidden-xs">
                            <?php echo __($value2['title']); ?>
                        </span>
                    </a> 
                </div>  
                <?php
            }
        }
    }
}

if (!User::canWatchVideo($videos_id)) {
    $menu = Menu::getAllActive(Menu::$typeActionMenuCustomURLForUsersThatCanNotWatchVideo);
    foreach ($menu as $key => $value) {
        $menuItems = MenuItem::getAllFromMenu($value['id'], true);
        foreach ($menuItems as $key2 => $value2) {
            $url = TopMenu::getVideoMenuURL($videos_id, $value2['id']);
            if (empty($url)) {
                continue;
            }
            ?>
            <div>
                <a href="<?php echo $url; ?>" <?php echo $value2['target']; ?>  class="text-primary">
                    <?php
                    if (!empty($value2['icon'])) {
                        ?>
                        <i class="<?php echo $value2['icon'] ?>"></i> 
                        <?php
                    }
                    ?>
                    <span  class="hidden-sm hidden-xs">
                        <?php echo __($value2['title']); ?>
                    </span>
                </a> 
            </div>  
            <?php
        }
    }
}
?>
<!-- action menu start -->