<!-- Modal -->
<div id="TrailerModal" class="modal fade" role="dialog">
    <div class="modal-dialog">
        <!-- Modal content-->
        <div class="modal-content">
            <div class="modal-body">
                <div class="embed-responsive embed-responsive-16by9">
                    <iframe class="embed-responsive-item" frameborder="0"></iframe>
                </div>
            </div>
        </div>
    </div>
</div>
<script src="<?php echo $global['webSiteRootURL']; ?>plugin/Gallery/script.js" type="text/javascript"></script>
<?php include $global['systemRootPath'] . 'view/include/footer.php'; ?>
<script>
    $('#TrailerModal').modal({show: false});
    function showTrailer(iframe) {
        $('#TrailerModal iframe').attr('src', iframe);
        $('#TrailerModal').modal("show");
        return false;
    }
    $('#TrailerModal').on('hidden.bs.modal', function () {
        $('#TrailerModal iframe').attr('src', '');
    });
    $('#bigVideoCarousel').bind('slide.bs.carousel', function (e) {
        setTimeout(function(){
            lazyImage();
        },500);
    });
</script>