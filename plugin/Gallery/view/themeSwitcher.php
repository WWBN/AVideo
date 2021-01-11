<?php
global $themeSwitcherAdded;
$iframeWidth = 400;
$iframeHeight = 200;
$zoom = 0.7;
if (!isset($themeSwitcherAdded)) {
    $themeSwitcherAdded = 1;
    ?>
    <style>
        .openThemeOptionsUL{
            top: auto; 
            max-height: 250px; 
            overflow-y: auto; 
            width: 100%; 
            margin-left: -100%; 
            margin-top: -26px; 
            overflow-x: visible;
        }
        .openThemeOptionsUL li .fas{
            display: none;
        }
        .openThemeOptionsUL li.active .far{
            display: none;
        }
        .openThemeOptionsUL li.active .fas{
            display: inline-block;
        }

        #sideBarContainer .openThemeOptionsUL{
            margin: 0;  
        }

        #showThemeIframeDiv{
            position: fixed;
            top: 70px;
            left: 50%;
            margin-left: -<?php echo $iframeWidth/2; ?>px;
            background-color: #000;
            -webkit-box-shadow: 0 0 5px 2px #000;  /* Safari 3-4, iOS 4.0.2 - 4.2, Android 2.3+ */
            -moz-box-shadow:    0 0 5px 2px #000;  /* Firefox 3.5 - 3.6 */
            box-shadow:         0 0 5px 2px #000;  /* Opera 10.5, IE 9, Firefox 4+, Chrome 6+, iOS 5 */
            z-index: 2000;
            width: <?php echo $iframeWidth; ?>px; 
            height: <?php echo $iframeHeight; ?>px; 
            padding: 0; 
            overflow: hidden; 
        }
        #frame { width: <?php echo $iframeWidth/$zoom; ?>px; height: <?php echo $iframeHeight/$zoom; ?>px; border: 1px solid black; }
        #frame {
            -ms-zoom: <?php echo $zoom; ?>;
            -moz-transform: scale(<?php echo $zoom; ?>);
            -moz-transform-origin: 0 0;
            -o-transform: scale(<?php echo $zoom; ?>);
            -o-transform-origin: 0 0;
            -webkit-transform: scale(<?php echo $zoom; ?>);
            -webkit-transform-origin: 0 0;
        }
    </style>
    <script>
        $(document).ready(function () {
            $(window).click(function () {
                $('.openThemeOptions').next('ul').hide();
                $('#showThemeIframeDiv').fadeOut();
            });
            $('.liThemes').click(function (event) {
                event.stopPropagation();
            });
            $('.openThemeOptions').on("click", function (e) {
                $(this).next('ul').toggle();
                if (!$(this).next('ul').is(":visible")) {
                $('#showThemeIframeDiv').fadeOut();
                }
                e.stopPropagation();
                e.preventDefault();
            });

            $('body').append('<div id="showThemeIframeDiv" style="display:none;"></div>');
            setInterval(function(){
                if(!$('.openThemeOptions').is(":visible")){
                    $('#showThemeIframeDiv').fadeOut();
                }
            },1000);
        });

        function showThemeIframe(name) {
            $('#showThemeIframeDiv').fadeIn();
            $('#showThemeIframeDiv').html('<iframe id="frame" frameBorder="0" width="100%" height="250px" src="' + webSiteRootURL + 'view/css/custom/theme.php?theme=' + name + '" ></iframe>');
        }


        function changeTheme(name) {
            $('.liThemes').removeClass('active');
            $('#li' + name).addClass('active');
            $('#customCSS').attr('href', webSiteRootURL + 'view/css/custom/' + name + '.css');
            Cookies.set('customCSS', name, {
                path: '/',
                expires: 365
            });
        }
    </script>
    <?php
}
$aClass = "";
$keyComplement = "";
if ($navBarButtons) {
    $aClass = "btn btn-default btn-block";
    $keyComplement .= "navBarButtons";
}
?>
<li class="dropdown-submenu" style="position: relative;">
    <a class="openThemeOptions <?php echo $aClass; ?>" tabindex="-1" href="#"><i class="fas fa-adjust"></i> <?php echo __("Change theme"); ?> </a>
    <ul class="dropdown-menu openThemeOptionsUL">
        <?php
        $themes = Gallery::getThemes();
        $curentTheme = getCurrentTheme();
        foreach ($themes as $key => $value) {
            $k = $key . $keyComplement;
            $class = "";
            if ($curentTheme == $value['name']) {
                $class = "active";
            }

            echo '<li class="' . $class . ' liThemes" id="li' . $value['name'] . '" ><a class="openThemeOptionsSub" tabindex="-1" href="#" '
            . 'onmouseover="showThemeIframe(\'' . $value['name'] . '\');"'
            . 'onclick="changeTheme(\'' . $value['name'] . '\');"'
            . '><i class="far fa-images"></i><i class="fas fa-image"></i> ' . $value['label'] . '</a></li>';
        }
        ?>
    </ul>
</li>