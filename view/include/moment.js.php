<script src="<?php echo getURL('node_modules/moment/min/moment.min.js'); ?>"></script>
<?php
$lang = getLanguage();
$momentFile = 'node_modules/moment/locale/' . $lang . '.js';
if (file_exists($global['systemRootPath'] . $momentFile)) {
    ?>
    <script src="<?php echo getURL($momentFile); ?>"></script>
    <?php
}
?>        
<script src="<?php echo getURL('node_modules/moment-timezone/builds/moment-timezone-with-data.min.js'); ?>"></script>