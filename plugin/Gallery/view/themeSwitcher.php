<style>
    .showThemeImage{
        position: absolute;
        top: -135px;
        left: -100%;
        width: 100%;
    }
    .showThemeImage img{
        filter: drop-shadow(0 0 0.75rem black);
    }
    #openThemeOptionsUL{
        top: auto; 
        max-height: 250px; 
        overflow-y: auto; 
        width: 100%; 
        margin-left: -100%; 
        margin-top: -26px; 
        overflow-x: visible;
    }
</style>
<li class="dropdown-submenu" style="position: relative;">
    <a class="openThemeOptions" tabindex="-1" href="#"><i class="fas fa-adjust"></i> <?php echo __("Change theme"); ?> </a>
    <ul id="openThemeOptionsUL" class="dropdown-menu">
        <?php
        $themes = getThemes();
        foreach ($themes as $key => $value) {
            echo '<li class="" ><a class="openThemeOptionsSub" tabindex="-1" href="#" '
            . 'onmouseover="showThemeImage(' . $key . ');"'
            . 'onclick="changeTheme(\'' . $value . '\');"'
            . '><i class="far fa-image"></i> ' . $value . '</a></li>';
        }
        ?>
    </ul>
    <?php
    $themes = getThemes();
    foreach ($themes as $key => $value) {
        echo '<div class="showThemeImage" id="showThemeImage' . $key . '" style="display:none;" ><img class="img img-responsive img-rounded" src="' . $global['webSiteRootURL'] . 'view/css/custom/' . $value . '.png" alt=""/></div>';
    }
    ?>
</li>
<script>
    $(document).ready(function () {

        $('.openThemeOptions').on("click", function (e) {
            $(this).next('ul').toggle();
            e.stopPropagation();
            e.preventDefault();
        });
        $('.openThemeOptionsSub').on("click", function (e) {
            $(this).next('.themeImage').toggle();
            e.stopPropagation();
            e.preventDefault();
        });
    });

    function showThemeImage(index) {
        $('.showThemeImage').hide();
        $('#showThemeImage' + index).show();
    }

    function changeTheme(name) {
        $('#customCSS').attr('href', webSiteRootURL+'view/css/custom/'+name+'.css');
        Cookies.set('customCSS', name, {
            path: '/',
            expires: 365
        });
    }
</script>