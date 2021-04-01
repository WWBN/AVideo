<?php
$pass = time();
$keys = createKeys('Test <test@example.com>', $pass);
?>

<div class="panel panel-default">
    <div class="panel-body">
        <div class="row">
            <div class="col-md-6">
                <h3><i class="fas fa-key"></i> 
                    <?php echo __('Public Key') ?>
                    <button class="btn btn-default pull-right btn-xs" onclick="$('#publicKeyToEncryptMsg').val($('#publicKey').val())"><i class="fas fa-copy"></i> <?php echo __('Copy from generated') ?></button>
                </h3>
                <textarea class="form-control monospacedKey" rows="5" id="publicKeyToEncryptMsg" placeholder="<?php echo $keys['public']; ?>"><?php echo LoginControl::getPGPKey(User::getId()); ?></textarea>
                <h3><i class="fas fa-envelope-open-text"></i> <?php echo __('Text to Encrypt') ?></h3>
                <textarea class="form-control" rows="5" id="textToEncrypt"></textarea>
            </div>
            <div class="col-md-6">
                <h3>
                    <i class="fas fa-lock"></i> <?php echo __('Encrypted Text') ?>
                    <button class="btn btn-default pull-right btn-xs" onclick="copyToClipboard($('#textEncrypted').val());"><i class="fas fa-copy"></i> <?php echo __('Copy to clipboard') ?></button>
                </h3>
                <textarea class="form-control monospacedKey" rows="12" id="textEncrypted"></textarea>
            </div>
        </div>
    </div>
    <div class="panel-footer">
        <button class="btn btn-block btn-primary" onclick="encryptMessage();"><?php echo __('Encrypt') ?></button>
    </div>
</div>
<script>
    $(document).ready(function () {

    });

    function encryptMessage() {
        modal.showPleaseWait();
        $.ajax({
            url: webSiteRootURL + 'plugin/LoginControl/pgp/encryptMessage.json.php',
            method: 'POST',
            data: {
                'publicKeyToEncryptMsg': $('#publicKeyToEncryptMsg').val(),
                'textToEncrypt': $('#textToEncrypt').val()
            },
            success: function (response) {
                if (response.error) {
                    avideoAlertError(response.msg);
                } else {
                    $('#textEncrypted, #textToDecrypt').val(response.textEncrypted);
                }
                modal.hidePleaseWait();
            }
        });
    }
</script>