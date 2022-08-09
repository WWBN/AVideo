<script src="<?php echo getCDN() ?>plugin/AD_Server/videojs-markers/videojs-markers.js" type="text/javascript"></script>
<script>
    $(document).ready(function () {
            if (typeof player == 'undefined') {
                player = videojs('mainVideo'<?php echo PlayerSkins::getDataSetup(); ?>);
            }
            player.markers({
                markerStyle: {
                    'width': '10px',
                    'background-color': 'yellow'
                },
                markerTip: {
                    display: true,
                    text: function (marker) {
                        return marker.text;
                    }
                },
                markers: [
<?php
foreach ($rows as $value) {
    ?>
                        {time: <?php echo $value['timeInSeconds']; ?>, text: "<?php echo addcslashes($value['name'], '"'); ?>"},
    <?php
}
?>
                ]
            });
    });

</script>