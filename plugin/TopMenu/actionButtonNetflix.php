<?php
$configFile = $global['systemRootPath'] . 'videos/configuration.php';


require_once $configFile;
require_once $global['systemRootPath'] . 'plugin/TopMenu/Objects/Menu.php';
require_once $global['systemRootPath'] . 'plugin/TopMenu/Objects/MenuItem.php';

$menu = Menu::getAllActive(Menu::$typeActionMenu);
if(empty($videos_id)){
    $videos_id = getVideos_id();
}
?>
<!-- action menu flix start videos_id=<?php echo $videos_id; ?> -->
<?php
foreach ($menu as $key => $value) {
    $menuItems = MenuItem::getAllFromMenu($value['id'], true);
    foreach ($menuItems as $key2 => $value2) {
        ?>
        <!-- typeActionMenu videos_id=<?php echo $videos_id; ?> -->
        <a href="<?php echo $value2['finalURL']; ?>" <?php echo $value2['target']; ?> class="btn btn-default no-outline">
            <?php
            if (!empty($value2['icon'])) {
                ?>
                <i class="<?php echo $value2['icon'] ?>"></i> 
                <?php
            }
            ?>
            <span class="hidden-sm hidden-xs">
                <?php echo __($value2['title']); ?>
            </span>
        </a> 
        <?php
    }
}
$menu = Menu::getAllActive(Menu::$typeActionMenuCustomURL);
echo PHP_EOL.'<!-- action menu typeActionMenuCustomURL start count='.count($menu).' videos_id='.$videos_id.'; ?> -->'.PHP_EOL;

foreach ($menu as $key => $value) {
    $menuItems = MenuItem::getAllFromMenu($value['id'], true);
    //echo PHP_EOL.'<!-- action menuItems typeActionMenuCustomURL start countItens='.count($menuItems).' -->'.PHP_EOL;
    foreach ($menuItems as $key2 => $value2) {
        $url = TopMenu::getVideoMenuURL($videos_id, $value2['id']);
        if (empty($url)) {
            echo "<!-- actionButtonNetflix there invalid URL $videos_id, {$value2['id']} url=$url -->";
            continue;
        }
        ?>
        <!-- typeActionMenuCustomURL -->
        <a href="<?php echo $url; ?>" <?php echo $value2['target']; ?> class="btn btn-default no-outline">
            <?php
            if (!empty($value2['icon'])) {
                ?>
                <i class="<?php echo $value2['icon'] ?>"></i> 
                <?php
            }
            ?>
            <span class="hidden-sm hidden-xs">
                <?php echo __($value2['title']); ?>
            </span>
        </a> 
        <?php
    }
}

if (User::isLogged()) {
    $menu = Menu::getAllActive(Menu::$typeActionMenuCustomURLForLoggedUsers);
    echo PHP_EOL.'<!-- action menu typeActionMenuCustomURLForLoggedUsers count='.count($menu).' start -->'.PHP_EOL;
    foreach ($menu as $key => $value) {
        $menuItems = MenuItem::getAllFromMenu($value['id'], true);
        foreach ($menuItems as $key2 => $value2) {
            $url = TopMenu::getVideoMenuURL($videos_id, $value2['id']);
            if (empty($url)) {
                continue;
            }
            ?>
            <!-- typeActionMenuCustomURLForLoggedUsers -->
            <a href="<?php echo $url; ?>" class="btn btn-default no-outline">
                <?php
                if (!empty($value2['icon'])) {
                    ?>
                    <i class="<?php echo $value2['icon'] ?>"></i> 
                    <?php
                }
                ?>
                <span class="hidden-sm hidden-xs">
                    <?php echo __($value2['title']); ?>
                </span>
            </a>  
            <?php
        }
    }
    if (User::canWatchVideo($videos_id)) {
        echo PHP_EOL.'<!-- action menu typeActionMenuCustomURLForUsersThatCanWatchVideo start -->'.PHP_EOL;
    
        $menu = Menu::getAllActive(Menu::$typeActionMenuCustomURLForUsersThatCanWatchVideo);
        foreach ($menu as $key => $value) {
            $menuItems = MenuItem::getAllFromMenu($value['id'], true);
            foreach ($menuItems as $key2 => $value2) {
                $url = TopMenu::getVideoMenuURL($videos_id, $value2['id']);
                if (empty($url)) {
                    continue;
                }
                ?>
                <!-- typeActionMenuCustomURLForUsersThatCanWatchVideo -->
                <a href="<?php echo $url; ?>" target="_blank" class="btn btn-default no-outline">
                    <?php
                    if (!empty($value2['icon'])) {
                        ?>
                        <i class="<?php echo $value2['icon'] ?>"></i> 
                        <?php
                    }
                    ?>
                    <span class="hidden-sm hidden-xs">
                        <?php echo __($value2['title']); ?>
                    </span>
                </a>   
                <?php
            }
        }
    }
}

if (!User::canWatchVideo($videos_id)) {
    echo PHP_EOL.'<!-- action menu typeActionMenuCustomURLForUsersThatCanNotWatchVideo start -->'.PHP_EOL;
    $menu = Menu::getAllActive(Menu::$typeActionMenuCustomURLForUsersThatCanNotWatchVideo);
    foreach ($menu as $key => $value) {
        $menuItems = MenuItem::getAllFromMenu($value['id'], true);
        foreach ($menuItems as $key2 => $value2) {
            $url = TopMenu::getVideoMenuURL($videos_id, $value2['id']);
            if (empty($url)) {
                continue;
            }
            ?>
            <!-- typeActionMenuCustomURLForUsersThatCanNotWatchVideo -->
            <a href="<?php echo $url; ?>" <?php echo $value2['target']; ?> class="btn btn-default no-outline">
                <?php
                if (!empty($value2['icon'])) {
                    ?>
                    <i class="<?php echo $value2['icon'] ?>"></i> 
                    <?php
                }
                ?>
                <span class="hidden-sm hidden-xs">
                    <?php echo __($value2['title']); ?>
                </span>
            </a>   
            <?php
        }
    }
}
?>
<!-- action menu flix end -->
