<link href="<?php echo $global['webSiteRootURL']; ?>view/js/bootstrap3-wysiwyg/bootstrap3-wysihtml5.min.css" rel="stylesheet" type="text/css"/>
<div class="panel panel-default">
    <div class="panel-heading"><?php echo __("Email All Users"); ?></div>
    <div class="panel-body">

        <div class="row">
            <div class="col-lg-12">
                <textarea id="emailMessage" placeholder="<?php echo __("Enter text"); ?> ..." style="width: 100%;"></textarea>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-12">
                <button type="button" class="btn btn-success btn-block" id="sendSubscribeBtn">
                    <i class="fas fa-envelope-square"></i> <?php echo __("Email All Users"); ?>
                </button>
            </div>
        </div>

    </div>
</div>

<script src="<?php echo $global['webSiteRootURL']; ?>view/js/bootstrap3-wysiwyg/bootstrap3-wysihtml5.all.min.js" type="text/javascript"></script>
<script>
    function notify() {
        modal.showPleaseWait();
        $.ajax({
            url: '<?php echo $global['webSiteRootURL']; ?>objects/emailAllUsers.json.php',
            method: 'POST',
            data: {'message': $('#emailMessage').val()},
            success: function (response) {
                console.log(response);
                if (response.error) {
                    swal("<?php echo __("Sorry!"); ?>", response.msg[0], "error");
                } else {
                    swal("<?php echo __("Success"); ?>", "You have sent "+response.count+" emails", "success");
                }
                modal.hidePleaseWait();
            }
        });
    }
    $(document).ready(function () {
        $('#emailMessage').wysihtml5();
        $("#sendSubscribeBtn").click(function () {
            notify();
        });

    });

</script>