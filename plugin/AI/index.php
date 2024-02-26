<?php

require_once '../../videos/configuration.php';


$_page = new Page(array('AI'));

?>
<style>
    html {
        height: unset;
    }

    body {
        background-color: #193c6d;
        filter: progid: DXImageTransform.Microsoft.gradient(gradientType=1, startColorstr='#003073', endColorstr='#029797');
        background-image: url(//img.alicdn.com/tps/TB1d.u8MXXXXXXuXFXXXXXXXXXX-1900-790.jpg);
        background-size: 100%;
        background-image: -webkit-gradient(linear, 0 0, 100% 100%, color-stop(0, #003073), color-stop(100%, #029797));
        background-image: -webkit-linear-gradient(135deg, #003073, #029797);
        background-image: -moz-linear-gradient(45deg, #003073, #029797);
        background-image: -ms-linear-gradient(45deg, #003073 0, #029797 100%);
        background-image: -o-linear-gradient(45deg, #003073, #029797);
        background-image: linear-gradient(135deg, #003073, #029797);
        margin: 0px;
        overflow: hidden;
        height: 100%;
    }
</style>

<script src="<?php echo getURL('js/three.js'); ?>" type="text/javascript"></script>
<?php
$_page->print();
?>