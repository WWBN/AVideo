<link href="<?php echo getURL('plugin/Layout/accessibility/accessibility.css'); ?>" rel="stylesheet" type="text/css"/>
<style>
    <?php
    for($i=150;$i<=300;$i+=10){
        ?>
            .accessibility-fontsize-<?php echo $i; ?>, 
            .accessibility-fontsize-<?php echo $i; ?> h1, 
            .accessibility-fontsize-<?php echo $i; ?> h2, 
            .accessibility-fontsize-<?php echo $i; ?> h3, 
            .accessibility-fontsize-<?php echo $i; ?> h4, 
            .accessibility-fontsize-<?php echo $i; ?> h5, 
            .accessibility-fontsize-<?php echo $i; ?> h6,
            .accessibility-fontsize-<?php echo $i; ?> .gallery .title, .videosDetails .title{
                font-size: <?php echo $i; ?>% !important;
                max-height: none;
            }
        <?php
    }
    ?>
</style>
<nav id="accessibility-toolbar" role="navigation">
    <div class="accessibility-toolbar-toggle list-group-item animate__animated animate__bounceInRight" data-toggle="tooltip" title="<?php echo __('Accessibility Tools'); ?>" data-placement="left"> 
        <div class="button" onclick="$('#accessibility-toolbar').toggleClass('active');"> 
            <span class="sr-only">Open toolbar</span> 
            <i class="fas fa-universal-access fa-3x"></i>
        </div>
    </div>
    <div class="accessibility-toolbar-overlay">
        <div class="list-group">            
            <a href="#" class="list-group-item" action="increase-text"> 
                <i class="fas fa-search-plus"></i>
                <span class="accessibility-toolbar-text">Increase Text</span> 
            </a>
            <a href="#" class="list-group-item" action="decrease-text"> 
                <i class="fas fa-search-minus"></i>
                <span class="accessibility-toolbar-text">Decrease Text</span> 
            </a>
            <a href="#"  class="list-group-item" action="grayscale">
                <i class="fas fa-palette"></i>
                <span class="accessibility-toolbar-text">Grayscale</span> 
            </a>
            <a href="#"  class="list-group-item" action="high-contrast">
                <i class="fas fa-adjust"></i>
                <span class="accessibility-toolbar-text">High Contrast</span> 
            </a>
            <a href="#"  class="list-group-item" action="negative-contrast">
                <i class="fas fa-minus-circle"></i>
                <span class="accessibility-toolbar-text">Negative Contrast</span> 
            </a>
            <a href="#"  class="list-group-item" action="links-underline">
                <i class="fas fa-underline"></i>
                <span class="accessibility-toolbar-text">Links Underline</span> 
            </a>
            <a href="#"  class="list-group-item" action="readable-font">
                <i class="fas fa-font"></i>
                <span class="accessibility-toolbar-text">Readable Font</span> 
            </a>
            <a href="#"  class="list-group-item" action="reset">
                <i class="fas fa-power-off"></i>
                <span class="accessibility-toolbar-text">Reset</span> 
            </a>
            </ul>
        </div>
</nav>
<script>
    var currentFontsize = 100;
    $(function () {
        $('.accessibility-toolbar-overlay a').click(function (event) {
            event.preventDefault();
            var action = $(this).attr('action');

            switch (action) {
                case 'increase-text':
                    var newFontSize = currentFontsize + 10;
                    if (newFontSize < 150) {
                        newFontSize = 150;
                    }
                    setFontSize(newFontSize);
                    break;
                case 'decrease-text':
                    var newFontSize = currentFontsize - 10;
                    if (newFontSize < 150) {
                        newFontSize = 150;
                    }
                    setFontSize(currentFontsize - 10);
                    break;

                case 'grayscale':
                    $('body').toggleClass('accessibility-grayscale');
                    $(this).toggleClass('active');
                    break;
                case 'high-contrast':
                    $('body').toggleClass('accessibility-high-contrast');
                    $(this).toggleClass('active');
                    break;
                case 'negative-contrast':
                    $('body').toggleClass('accessibility-negative-contrast');
                    $(this).toggleClass('active');
                    break;
                case 'links-underline':
                    $('body').toggleClass('accessibility-links-underline');
                    $(this).toggleClass('active');
                    break;
                case 'readable-font':
                    $('body').toggleClass('accessibility-readable-font');
                    $(this).toggleClass('active');
                    break;
                case 'reset':
                    resetAccessibility();
                    break;

                default:

                    break;
            }
        });
    });

    function setFontSize(num) {
        if (num < 100) {
            num = 100;
        } else if (num > 300) {
            num = 300;
        }
        for (i = 10; i <= 200; i += 10) {
            var fontsizeNum = 100 + i;
            $('body').removeClass('accessibility-fontsize-' + fontsizeNum);
        }
        $('body').addClass('accessibility-fontsize-' + num);
        currentFontsize = num;
    }

    function resetAccessibility() {
        $('.accessibility-toolbar-overlay a').removeClass('active');
        var classItems = $('body').attr('class').split(/\s+/);
        for (var item in classItems) {
            var className = classItems[item];
            if (/^accessibility/.test(className)) {
                $('body').removeClass(className);
            }
        }
    }
</script>