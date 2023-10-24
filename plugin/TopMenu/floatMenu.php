<?php

require_once $global['systemRootPath'] . 'plugin/TopMenu/Objects/Menu.php';
require_once $global['systemRootPath'] . 'plugin/TopMenu/Objects/MenuItem.php';

$menu = Menu::getAllActive(Menu::$typeFloatMenu);
?>
<!-- floatmenu start -->
<?php
$menuItems = array();
$icon = '';
foreach ($menu as $key => $value) {
    $menuItems = MenuItem::getAllFromMenu($value['id'], true);
    if (!empty($menuItems)) {
        $icon = $value['icon'];
        break;
    }
}
if (empty($menuItems)) {
    return;
}

?>
<style>
    #topMenuFloatMenu {
        position: fixed;
        bottom: 35px;
        right: 35px;
        z-index: 19999;
    }

    .Chat2StaticRight #topMenuFloatMenu,
    .chat2Collapsed #topMenuFloatMenu{
        display: none;
        bottom: 90px;
    }

    .chat2Collapsed #topMenuFloatMenu{
        display: block;
    }

    .circle-menu {
        z-index: 9999;
        width: 60px;
        height: 60px;
        border-radius: 50%;
        box-shadow: 0 0 15px 1px black;
    }

    .submenu {
        position: absolute;
        bottom: 60px;
        right: 0;
    }

    .submenu-item {
        width: 60px;
        height: 60px;
        border-radius: 50%;
        margin-bottom: 10px;
        padding-top: 15px;
        box-shadow: 0 0 15px 1px black;
    }
</style>

<div id="topMenuFloatMenu">

    <button class="btn btn-primary btn-lg btn-circle circle-menu" data-toggle="tooltip" title="<?php echo __('Open'); ?>" data-placement="left">
        <i class="<?php echo $icon ?> fa-2x"></i>
    </button>

    <div class="submenu hidden">
        <?php
        foreach ($menuItems as $key2 => $value2) {
        ?>
            <a href="<?php echo $value2['finalURL']; ?>" <?php echo $value2['target']; ?> class="btn btn-default btn-lg btn-circle submenu-item <?php echo getCSSAnimationClassAndStyle('animate__bounceIn', 'topFloatMenu'); ?> " data-toggle="tooltip" title="<?php echo __($value2['title']); ?>" data-placement="left">
                <?php
                if (!empty($value2['icon'])) {
                ?>
                    <i class="<?php echo $value2['icon'] ?> fa-2x"></i>
                <?php
                } else {
                    echo '<i class="fas fa-folder fa-2x"></i>';
                }
                ?>
            </a>
        <?php
        }
        ?>
    </div>
</div>

<script>
    $(document).ready(function() {
        $('.circle-menu').click(function() {
            $('.submenu').toggleClass('hidden');
        });


        $(document).click(function(event) {
            var target = $(event.target);
            if (!target.closest('#topMenuFloatMenu').length) {
                $('.submenu').addClass('hidden');
            }
        });
    });
</script>