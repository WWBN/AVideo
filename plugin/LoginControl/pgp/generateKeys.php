
<div class="panel panel-default">
    <div class="panel-heading"><i class="fas fa-key"></i> <?php echo __('Generate Keys') ?> </div>
    <div class="panel-body">
        <div class="row">
            <div class="col-md-5">

                <h3><i class="fas fa-key"></i> <?php echo __('Your Name'); ?></h3>
                <input type="text" class="form-control" id="keyName" placeholder="<?php echo __('Your Name'); ?>"/>
                <h3><i class="fas fa-key"></i> <?php echo __('Your Email'); ?></h3>
                <input type="email" class="form-control" id="keyEmail" placeholder="<?php echo __('Your Email'); ?>"/>
                <h3><i class="fas fa-key"></i> <?php echo __('Key Password'); ?></h3>
                <input type="password" class="form-control" id="keyPassword" placeholder="<?php echo __('Key Password'); ?>"/>
            </div>
            <div class="col-md-7">
                <h3>
                    <i class="fas fa-key"></i> <?php echo __('Public Key') ?> 
                    <button class="btn btn-default pull-right btn-xs" onclick="copyToClipboard($('#publicKey').val());"><i class="fas fa-copy"></i> <?php echo __('Copy to clipboard') ?></button>
                    <button class="btn btn-default pull-right btn-xs" onclick="download('public.pgp.key.txt', $('#publicKey').val());"><i class="fas fa-download"></i> <?php echo __('Download') ?></button>
                </h3>
                <textarea class="form-control" rows="10" id="publicKey"></textarea>
                <h3>
                    <i class="fas fa-key"></i> <?php echo __('Private Key') ?>
                    <button class="btn btn-default pull-right btn-xs" onclick="copyToClipboard($('#privateKey').val());"><i class="fas fa-copy"></i> <?php echo __('Copy to clipboard') ?></button>
                    <button class="btn btn-default pull-right btn-xs" onclick="download('private.pgp.key.txt', $('#privateKey').val());"><i class="fas fa-download"></i> <?php echo __('Download') ?></button>
                </h3>
                <textarea class="form-control" rows="14" id="privateKey"></textarea>
            </div>
            <div class="col-md-12" id="keyDownloadButton">

            </div>
        </div>
    </div>
    <div class="panel-footer" >
        <button class="btn btn-block btn-primary" onclick="generateKeys();"><?php echo __('Generate') ?></button>
    </div>
</div>
<script>
    $(document).ready(function () {

    });

    function generateKeys() {
        modal.showPleaseWait();
        $.ajax({
            url: webSiteRootURL + 'plugin/LoginControl/pgp/generateKeys.json.php',
            method: 'POST',
            data: {
                'keyName': $('#keyName').val(),
                'keyEmail': $('#keyEmail').val(),
                'keyPassword': $('#keyPassword').val()
            },
            success: function (response) {
                if (response.error) {
                    avideoAlertError(response.msg);
                } else {
                    $('#publicKey').text(response.public);
                    $('#privateKey').text(response.private);
                    $('#keyDownloadButton').html(response.downloadButton);
                    download('pgp.keys.bundle.txt', $('#publicKey').val()+'\n\r\n\r'+$('#privateKey').val());
                }
                modal.hidePleaseWait();
            }
        });
    }

    function download(filename, text) {
        if (!text) {
            avideoToastError("<?php echo __("Key cannot be empty"); ?>");
            return false;
        }
        var element = document.createElement('a');
        element.setAttribute('href', 'data:text/plain;charset=utf-8,' + encodeURIComponent(text));
        element.setAttribute('download', filename);

        element.style.display = 'none';
        document.body.appendChild(element);

        element.click();

        document.body.removeChild(element);
    }
</script>