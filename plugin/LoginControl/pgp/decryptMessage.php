<?php
$pass = time();
$keys = createKeys('Test <test@example.com>', $pass);
?>

<div class="panel panel-default">
    <div class="panel-body">
        <div class="row">
            <div class="col-md-6">
                <h3>
                    <i class="fas fa-key"></i> <?php echo __('Private Key') ?>
                    <button class="btn btn-default pull-right btn-xs" onclick="$('#privateKeyToDecryptMsg').val($('#privateKey').val())"><i class="fas fa-copy"></i> <?php echo __('Copy from generated') ?></button>
                </h3>
                <textarea class="form-control monospacedKey" rows="5" id="privateKeyToDecryptMsg" placeholder="<?php echo $keys['public']; ?>"></textarea>
                <h3>
                    <i class="fas fa-lock"></i> <?php echo __('Text to Decrypt') ?>
                    <button class="btn btn-default pull-right btn-xs" onclick="$('#textToDecrypt').val($('#textEncrypted').val())"><i class="fas fa-copy"></i> <?php echo __('Copy from encripted message') ?></button>
                </h3>
                <textarea class="form-control monospacedKey" rows="5" id="textToDecrypt"><?php echo LoginControl::getChallenge(); ?></textarea>
            </div>
            <div class="col-md-6">
                <h3><i class="fas fa-key"></i> <?php echo __('Key Password'); ?></h3>
                <input type="password" class="form-control" id="keyPasswordToDecrypt" placeholder="<?php echo __('Key Password'); ?>"/>
                <h3>
                    <i class="fas fa-envelope-open-text"></i> <?php echo __('Decrypted Text') ?>
                    <button class="btn btn-default pull-right btn-xs" onclick="copyToClipboard($('#textDecrypted').text());"><i class="fas fa-copy"></i> <?php echo __('Copy to clipboard') ?></button>
                </h3>
                <textarea class="form-control" rows="5" id="textDecrypted" style="font-size: 1.5em;" readonly="readonly"></textarea>
            </div>
        </div>
    </div>
    <div class="panel-footer">
        <button class="btn btn-block btn-primary" onclick="decryptMessage();"><?php echo __('Decrypt') ?></button>
    </div>
</div>
<script>
    $(document).ready(function () {

    });

    function decryptMessage() {
        modal.showPleaseWait();
        $.ajax({
            url: webSiteRootURL + 'plugin/LoginControl/pgp/decryptMessage.json.php',
            method: 'POST',
            data: {
                'privateKeyToDecryptMsg': $('#privateKeyToDecryptMsg').val(),
                'textToDecrypt': $('#textToDecrypt').val(),
                'keyPassword': $('#keyPasswordToDecrypt').val()
            },
            success: function (response) {
                if (response.error) {
                    avideoAlertError(response.msg);
                } else {
                    $('#textDecrypted').val(response.textDecrypted);
                }
                modal.hidePleaseWait();
            }
        });
    }
</script>