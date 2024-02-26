<?php include $global['systemRootPath'] . 'view/include/footer.php'; ?>
<script>
    $(document).ready(function () {
        $('#TrailerModal').on('hidden.bs.modal', function () {
            $('#TrailerModal iframe').attr('src', '');
        });
        $('#bigVideoCarousel').bind('slide.bs.carousel', function (e) {
            setTimeout(function(){
                lazyImage();
            },500);
        });
    });
</script>