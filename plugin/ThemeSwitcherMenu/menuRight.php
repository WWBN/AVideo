<?php
$firstPages = array('Default', 'FBTube', 'Gallery', 'YouPHPFlix');
$obj = ThemeSwitcherMenu::getCurrent();
?>
<style>

    .dropdown-submenu {
        position: relative;
    }

    .open2{
        z-index: 99999;
    }

    .dropdown-submenu .dropdown-menu {
        margin-right: 50px;
        margin-left: -200px;
    }
</style>
<li>
    <div class="btn-group">
        <button type="button" class="btn btn-default  dropdown-toggle navbar-btn pull-left"  data-toggle="dropdown">
            <span class="fa fa-gear"></span> <span class="hidden-sm"><?php echo __("Style & Themes") ?></span> <span class="caret"></span>
        </button>
        <ul class="dropdown-menu dropdown-menu-right" role="menu">
            <li class="dropdown-header"><?php echo __("Current Style & Theme") ?></li> 
            <li  class="dropdown-submenu active" style=" min-width: 165px;">
                <img style='height: 60px; width: 60px; float: left;' class='img img-responsive  img-thumbnail ' src="<?php echo $global['webSiteRootURL']; ?>plugin/ThemeSwitcherMenu/icons/<?php echo $obj->page; ?>.png"/>
                <img style='height: 60px; width: 100px;' class='img img-responsive  img-thumbnail' src="<?php echo $global['webSiteRootURL']; ?>view/css/custom/<?php echo $obj->theme; ?>.png"/>
            </li>
            <li class="divider"></li> 
            <li class="dropdown-header"><?php echo __("Change Style") ?></li> 
            <?php
            foreach ($firstPages as $value) {
                ?>
                <li class="dropdown-submenu" data-placement="left" data-toggle="tooltip" title="<img style='height: 100px; width: 100px;' class='img img-responsive  img-thumbnail' src='<?php echo $global['webSiteRootURL']; ?>plugin/ThemeSwitcherMenu/icons/<?php echo $value; ?>.png' />">
                    <a class="test" tabindex="-1" href="#"><?php echo $value; ?> <span class="caret"></span></a>
                    <ul class="dropdown-menu dropdown-menu-right dropdown-submenu-themes">
                        <li class="dropdown-header"><?php echo __("Theme for Style:") ?> <strong><?php echo $value; ?></strong></li> 
                        <?php
                        foreach (glob("{$global['systemRootPath']}view/css/custom/*.css") as $filename) {
                            //echo "$filename size " . filesize($filename) . "\n";
                            $file = basename($filename);         // $file is set to "index.php"
                            $fileEx = basename($filename, ".css"); // $file is set to "index"
                            ?>
                            <li data-toggle="tooltip" title="<img style='height: 60px; width: 100px;' class='img img-responsive  img-thumbnail' src='<?php echo $global['webSiteRootURL']; ?>view/css/custom/<?php echo $fileEx; ?>.png' />">
                                <a tabindex="-1" href="<?php echo strtok($_SERVER["REQUEST_URI"], '?'); ?>?firstPage=<?php echo $value; ?>&theme=<?php echo $fileEx; ?>"><?php echo ucfirst($fileEx); ?></a>
                            </li>
                            <?php
                        }
                        ?>
                    </ul>
                </li>
                <?php
            }
            ?>
            <li class="divider"></li> 
            <li class="dropdown-submenu">
                <a tabindex="-1" href="<?php echo $global['webSiteRootURL']; ?>plugin/ThemeSwitcherMenu/reset.php">
                    <i class="fa fa-repeat" aria-hidden="true"></i>
                    <?php echo __("Reset to Default"); ?>
                </a>
            </li> 
        </ul>
    </div>

</li>

<script>
    $(document).ready(function () {
        $('.dropdown-submenu a.test').on("click", function (e) {

            $('.open2').slideUp();
            if ($(this).next('ul').hasClass('open2')) {
                $(this).next('ul').slideUp();
                $(this).next('ul').removeClass('open2');
            } else {
                $(this).next('ul').slideDown();
                $(this).next('ul').addClass('open2');
            }
            e.stopPropagation();
            e.preventDefault();
        });

        $('li[data-toggle="tooltip"]').tooltip({
            animated: 'fade',
            html: true
        });
    });
</script>