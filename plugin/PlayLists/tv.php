<?php
global $global, $config;
if (!isset($global['systemRootPath'])) {
    require_once '../../videos/configuration.php';
}
require_once $global['systemRootPath'] . 'objects/playlist.php';
require_once $global['systemRootPath'] . 'objects/configuration.php';
?>
<!DOCTYPE html>
<html lang="us">
    <head>
        <title>TV</title>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="device_id" content="<?php echo getDeviceID(); ?>">

        <link rel="apple-touch-icon" sizes="180x180" href="<?php echo $config->getFavicon(true); ?>">
        <link rel="icon" type="image/png" href="<?php echo $config->getFavicon(true); ?>">
        <link rel="shortcut icon" href="<?php echo $config->getFavicon(); ?>" sizes="16x16,24x24,32x32,48x48,144x144">
        <meta name="msapplication-TileImage" content="<?php echo $config->getFavicon(true); ?>">

        <link rel="apple-touch-icon" sizes="180x180" href="<?php echo $global['webSiteRootURL']; ?>videos/favicon.png?1602260237">
        <link rel="icon" type="image/png" href="<?php echo $global['webSiteRootURL']; ?>videos/favicon.png?1602260237">
        <link rel="shortcut icon" href="<?php echo $global['webSiteRootURL']; ?>videos/favicon.ico?1601872356" sizes="16x16,24x24,32x32,48x48,144x144">
        <meta name="msapplication-TileImage" content="<?php echo $global['webSiteRootURL']; ?>videos/favicon.png?1602260237">
        <link href="<?php echo $global['webSiteRootURL']; ?>view/bootstrap/css/bootstrap.min.css" rel="stylesheet" type="text/css"/>
        <link href="<?php echo $global['webSiteRootURL']; ?>view/css/custom/cyborg.css" rel="stylesheet" type="text/css"/>
        <link href="<?php echo $global['webSiteRootURL']; ?>view/css/fontawesome-free-5.5.0-web/css/all.min.css" rel="stylesheet" type="text/css"/>
        <link href="<?php echo $global['webSiteRootURL']; ?>view/css/font-awesome-animation.min.css" rel="stylesheet" type="text/css"/>
        <script src="<?php echo $global['webSiteRootURL']; ?>view/js/jquery-3.5.1.min.js"></script>
        <script>
            var loopBGHLS = '<?php echo $global['webSiteRootURL']; ?>plugin/Live/view/loopBGHLS/index.m3u8';
        </script>
        <style>
            .playListIsLive .fas{
                -webkit-animation: flash 2s ease infinite;
                animation: flash 2s ease infinite;
                color: red;
            }
            body.showingVideo{
                margin:0px;
                padding:0px;
                overflow:hidden
            }
            .videoLinkDiv{
                padding: 10px;
                text-align: center;
            }
            .videoLinkDiv h4{
                color: #999; 
            }

            .videoLinkDiv img{
                border: transparent 6px solid;
            }

            .videoLinkDiv:focus, .focus{
                outline: none;
            }
            .videoLinkDiv:focus img, .focus img{
                border-color: #FFF;
                background-color: #333;
            }

            .videoLinkDiv:focus h4, .focus h4{
                color: #FFF;
            }

            #channelTop{
                position: fixed;
                top: 20px;
                right: 20px;
                font-size: 2em;
                padding: 10px;
                color: white;
                text-shadow: 2px 2px 4px #000000;
            }
            #leftNav{
                width: 100px;
                height: 100%;
                position: fixed;
                top: 0;
                left: 0;
                border-color: #FFF;
                background-color: #333;
                padding: 10px;
                overflow: hidden;
                opacity: 0.5;
                transition: all 0.25s ease-in-out;
                -moz-transition: all 0.25s ease-in-out;
                -webkit-transition: all 0.25s ease-in-out;
            }
            .container-fluid{
                margin-left: 100px;
                
                transition: all 0.25s ease-in-out;
                -moz-transition: all 0.25s ease-in-out;
                -webkit-transition: all 0.25s ease-in-out;
            }
            body.menuOpen #leftNav{
                width: 300px;
                opacity: 1;
            }
            .listLabel {
                display: none;
            }
            body.menuOpen #leftNav .listLabel {
                display: block;
            }
            body.menuOpen .container-fluid{
                margin-left: 300px;
            }
            body.menuOpen .videoLinkCol {
                padding: 0;
            }
        </style>
    </head>
    <body class="">
        <div id="leftNav">
            <ul class="list-group">
                <li class="list-group-item active" nabBarIndex="1">
                    <button class="btn btn-danger btn-block" id="closeMenuBtn">
                        <i class="fas fa-times"></i>
                        <span class="listLabel">Close</span>             
                    </button>
                </li>
                <li class="list-group-item" nabBarIndex="2">
                    <a href="http://192.168.1.4/YouPHPTube/epg" target="_blank" class="btn btn-primary btn-block ">
                        <i class="fas fa-stream"></i>
                        <span class="listLabel">EPG</span>              
                    </a>
                </li>
                <li class="list-group-item" nabBarIndex="3">
                    <a href="http://192.168.1.4/YouPHPTube/iptv" target="_blank" class="btn btn-primary btn-block ">
                        <i class="fas fa-stream"></i>
                        <span class="listLabel">IPTV m3u</span>                                         
                    </a>
                </li>
                <li class="list-group-item" nabBarIndex="4">
                    <a href="http://192.168.1.4/YouPHPTube/epg.xml" target="_blank" class="btn btn-primary btn-block ">
                        <i class="fas fa-stream"></i>
                        <span class="listLabel">EPG XMLTV</span>                                      
                    </a>
                </li>
            </ul>
        </div>
        <div class="container-fluid">
            <?php
            include $global['systemRootPath'] . 'plugin/PlayLists/tv_body.php';
            ?>
        </div>
        <?php
        include $global['systemRootPath'] . 'plugin/PlayLists/tv_videoPlayer.php';
        ?>
        <div id="channelTop" style="display: none;"></div>
        <script>
            var undoArray = [];
            var totalItems = 0;
            var tabindex = 0;
            var nabBarIndex = 1;
            var nabBarTotalItems = 0;
            $(function () {

                totalItems = $('.videoLink').length;
                nabBarTotalItems = $('#leftNav li').length;
                $('.videoLink').click(function (e) {
                    e.preventDefault();
                    tabindex = parseInt($(this).parent().attr('tabindex'));
                    loadLiveVideo($(this).attr('href'));
                    if (isIframe) {
                        loadLiveVideoIframe($(this).attr('href'));
                    } else {
                        loadLiveVideo($(this).attr('source'))
                    }
                });

                $('#closeMenuBtn').click(function (e) {
                    closeNavBar();
                });

                $(document).keyup(function (e) {
                    console.log(e.key, tabindex);
                    if (e.key === "Escape") { // escape key maps to keycode `27`
                        e.preventDefault();
                        backButton();
                    } else if (e.key === "ArrowUp") {
                        e.preventDefault();
                        //moveTotabindex(-<?php echo $itemsPerRow ?>);
                        moveToColAbove();
                    } else if (e.key === "ArrowDown") {
                        e.preventDefault();
                        //moveTotabindex(<?php echo $itemsPerRow ?>);
                        moveToColBelow();
                    } else if (e.key === "ArrowRight") {
                        e.preventDefault();
                        moveTotabindex(1);
                    } else if (e.key === "ArrowLeft") {
                        e.preventDefault();
                        //moveTotabindex(-1);
                        moveToLeft();
                    } else if (e.key === "Tab") {
                        e.preventDefault();
                        cicleTotabindex();
                    } else if (e.key === "Enter") {
                        e.preventDefault();
                        triggerChannelClick();
                    }else if(e.key.match(/^[0-9]$/)){
                        typeNumber(e.key);
                    }
                });

                setInterval(function () {
                    loadBody();
                }, 60000);
                
                setInterval(function () {
                    reloadImages();
                }, 30000);

            });
            
            var channelTypedNumber = "";
            var typeNumberTimeout;
            
            function backButton(){
                var evalCode = undoArray.pop();
                if (evalCode) {
                    eval(evalCode);
                }
            }
            
            function typeNumber(num){
                channelTypedNumber += num;
                showMessage(channelTypedNumber);
                clearTimeout(typeNumberTimeout);
                typeNumberTimeout = setTimeout(function(){goToChannelNumber()},1000);
            }
            
            function goToChannelNumber(){
                var index = parseInt($('div[channelNumber="' + channelTypedNumber + '"]').attr('tabindex'));
                if(index){
                    tabindex = index;
                    triggerChannelClick();
                }else{
                    showMessage("Channel not found");
                }
                channelTypedNumber = "";
            }
            
            function isVideoOpened() {
                return $('body').hasClass('showingVideo');
            }

            var showChannelTopTimeout;
            function showChannelTop() {
                showMessage($('div[tabindex="' + tabindex + '"] a h5').html());
            }

            function showMessage(msg) {
                console.log('showMessage', msg);
                $('#channelTop').html(msg);
                $('#channelTop').show();
                clearTimeout(showChannelTopTimeout);
                showChannelTopTimeout = setTimeout(function () {
                    $('#channelTop').fadeOut('slow');
                }, 4000);
            }

            function gettabindex(total) {
                tabindex += total;
                if (tabindex < 1) {
                    tabindex = $('.videoLink').length;
                } else if (tabindex > $('.videoLink').length) {
                    tabindex = 1;
                }
                return tabindex;
            }

            function getLiveTabindex(total) {
                if (!thereIsSomethingLive()) {
                    return gettabindex(total);
                }

                tabindex += total;
                if (tabindex < 1) {
                    tabindex = $('.videoLink').length;
                } else if (tabindex > $('.videoLink').length) {
                    tabindex = 1;
                }

                if (!isIndexLive(tabindex)) {
                    return getLiveTabindex(total);
                }

                return tabindex;
            }


            function moveTotabindex(total) {
                if (isNavBarOpen()) {
                    closeNavBar();
                } else {
                    if (isVideoOpened()) {
                        var currentTIndex = tabindex;
                        var tindex = getLiveTabindex(total);
                        if (currentTIndex != tindex) {
                            tabFocus(tindex);
                            triggerChannelClick();
                        } else {
                            showMessage("There are no more live programs");
                        }
                    } else {
                        var tindex = gettabindex(total);
                        tabFocus(tindex);
                    }
                }
            }

            function thereIsSomethingLive() {
                return $('.playListIsLive').length;
            }

            function isIndexLive(index) {
                return $('div[tabindex="' + index + '"]').find('.playListIsLive').length;
            }

            function moveToLeft() {
                var col = getCurrentColumn();
                if (col === 0 && !isVideoOpened()) {
                    openNavBar();
                } else {
                    moveTotabindex(-1);
                }
            }

            function getCurrentColumn() {
                return parseInt($('div[tabindex="' + tabindex + '"]').attr('tabindexCol'));
            }

            function openNavBar() {
                $('body').addClass('menuOpen');
            }

            function closeNavBar() {
                $('body').removeClass('menuOpen');
            }

            function isNavBarOpen() {
                return $('body').hasClass('menuOpen');
            }

            function getCurrentNavbarIndex() {
                return parseInt($('#leftNav .active').attr("nabBarIndex"));
            }

            function cicleNavbar(goTo) {
                var index = getCurrentNavbarIndex();
                var newIndex = index + goTo;
                if (newIndex < 1) {
                    newIndex = nabBarTotalItems;
                } else if (newIndex > nabBarTotalItems) {
                    newIndex = 1;
                }
                $('#leftNav li').removeClass("active");
                $('#leftNav li[nabBarIndex="' + newIndex + '"]').addClass("active");
                //nabBarIndex


            }

            function moveToColAbove() {
                if (isVideoOpened()) {
                    showChannelTop();
                    if (isIframe) {
                        setFocus();
                    } else {
                        changeVolume(0.1);
                    }
                } else if (isNavBarOpen()) {
                    cicleNavbar(-1);
                } else
                if (tabindex > 0) {
                    var tabPosition = tabindex;
                    var col = getCurrentColumn();
                    tabPosition--;
                    for (i = tabPosition; i > 0; i--) {
                        var element = $('div[tabindex="' + i + '"]').parent().find('div[tabindexCol="' + col + '"]');
                        var element = $('div[tabindex="' + i + '"]').parent().find('div[tabindexCol="' + col + '"]');
                        if (element.length) {
                            $(element).focus();
                            tabindex = parseInt($(element).attr('tabindex'));
                            tabFocus(tabindex);
                            break;
                        }
                    }
                }
            }
            
            var changeVolumeTimeOut;
            function changeVolume(total){
                clearTimeout(changeVolumeTimeOut);
                $("#volumeBar").fadeIn('fast');
                var newVolume = player.volume();
                newVolume += total;
                if (newVolume > 1) {
                    newVolume = 1;
                }
                if (newVolume < 0) {
                    newVolume = 0;
                }
                console.log('changeVolume: ' + newVolume);
                player.volume(newVolume);
                
                for(i=0;i<=newVolume*10;i++){
                    console.log('changeVolume:fadeIn ' + i);
                    $('.volume'+i).fadeIn('slow');
                }
                for(;i<=10;i++){
                    console.log('changeVolume:fadeOut ' + i);
                    $('.volume'+i).fadeOut('slow');
                }
                changeVolumeTimeOut = setTimeout(function(){$("#volumeBar").fadeOut();},3000);
                
            }

            function setFocus() {
                if (isIframe) {
                    focusIframe();
                } else {
                    focusVideo();
                }
            }

            function moveToColBelow() {
                if (isVideoOpened()) {
                    showChannelTop();
                    if (isIframe) {
                        setFocus();
                    } else {
                        changeVolume(-0.1);
                    }
                } else if (isNavBarOpen()) {
                    cicleNavbar(1);
                } else
                if (tabindex < totalItems) {
                    var tabPosition = tabindex;
                    var col = getCurrentColumn();
                    tabPosition++;
                    for (i = tabPosition; i <= totalItems; i++) {
                        var element = $('div[tabindex="' + i + '"]').parent().find('div[tabindexCol="' + col + '"]');
                        var element = $('div[tabindex="' + i + '"]').parent().find('div[tabindexCol="' + col + '"]');
                        if (element.length) {
                            $(element).focus();
                            tabindex = parseInt($(element).attr('tabindex'));
                            tabFocus(tabindex);
                            break;
                        }
                    }
                }
            }

            function cicleTotabindex() {
                tabindex += 1;
                if (tabindex > $('.videoLink').length) {
                    tabindex = 1;
                }
                console.log('div[tabindex="' + tabindex + '"]');
                tabFocus(tabindex);
            }

            function triggerChannelClick() {
                if (isNavBarOpen()) {
                    console.log('triggerChannelClick', 'isNavBarOpen');
                    var index = getCurrentNavbarIndex();
                    console.log($('#leftNav li[nabBarIndex="' + index + '"]').find('.btn'));
                    var href = $('#leftNav li[nabBarIndex="' + index + '"]').find('.btn').attr('href');
                    if (href) {
                        var win = window.open(href, '_blank');
                        win.focus();
                    } else {
                        $('#leftNav li[nabBarIndex="' + index + '"]').find('.btn').trigger('click');
                    }
                } else {
                    console.log('triggerChannelClick', 'click');
                    if (isIframe) {
                        loadLiveVideoIframe($('div[tabindex="' + tabindex + '"]').find('.videoLink').attr('href'));
                    } else {
                        loadLiveVideo($('div[tabindex="' + tabindex + '"]').find('.videoLink').attr('source'))
                    }
                }
            }

            function tabFocus(index) {
                $('.videoLinkDiv').removeClass('focus');
                $('div[tabindex="' + tabindex + '"]').addClass('focus').focus();
            }

            function loadBody() {
                $.ajax({
                    url: '<?php echo $global['webSiteRootURL']; ?>plugin/PlayLists/tv_body.php',
                    success: function (response) {
                        $(response).find('.videoLinkDiv').each(function (index) {
                            var bodyTabindex = parseInt($(this).attr('tabindex'));
                            if (bodyTabindex != tabindex) {
                                if ($('div[tabindex="' + bodyTabindex + '"]').find('a').hasClass('playListIsLive') !== $(this).find('a').hasClass('playListIsLive')) {
                                    $('div[tabindex="' + bodyTabindex + '"]').html($(this).html());
                                    console.log("loadBody: changed", bodyTabindex);
                                }
                            }
                        });
                    }
                });
            }

            function reloadImages() {
                $('.videoLinkDiv').each(function (index) {
                    var bodyTabindex = parseInt($(this).attr('tabindex'));
                    var img = $('div[tabindex="' + bodyTabindex + '"]').find('img');
                    var originalSrc = $(img).attr('originalSrc');
                    if (originalSrc.indexOf('?') > -1) {
                        originalSrc += '&' + Math.round();
                    } else {
                        originalSrc += '?' + Math.round();
                    }

                    $(img).attr('src', originalSrc);
                });
            }

        </script>
    </body>
</html>
