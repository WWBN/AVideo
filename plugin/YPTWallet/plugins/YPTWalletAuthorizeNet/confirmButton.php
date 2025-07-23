<?php
$obj = AVideoPlugin::getObjectData("AuthorizeNet");
?>
<div class="btn-group" role="group" aria-label="Authorize.Net Actions">
    <button class="btn btn-primary" onclick="startAuthorizeNetAcceptHosted()">
        <i class="fas fa-credit-card"></i>
        <?php
        if (!empty($_REQUEST['plans_id'])) {
            echo __($obj->subscriptionButtonLabel);
        } else {
            echo __($obj->paymentButtonLabel);
        }
        ?>
    </button>
    <button class="btn btn-info" onclick="openAuthorizeNetProfileManager()">
        <i class="fas fa-user-cog"></i>
    </button>
</div>

<script>
    function startAuthorizeNetAcceptHosted() {
        const amount = $('#value<?php echo @$_GET['plans_id']; ?>').val();
        const plans_id = <?php echo intval(@$_GET['plans_id']); ?>;
        if (!amount || isNaN(amount) || amount <= 0) {
            if (plans_id <= 0) {
                avideoAlertError('Invalid data');
                return;
            }
        }
        modal.showPleaseWait();

        $.ajax({
            url: webSiteRootURL + 'plugin/AuthorizeNet/getAcceptHostedToken.json.php',
            type: 'POST',
            data: {
                "plans_id": plans_id,
                "amount": amount
            },
            success: function(resp) {
                modal.hidePleaseWait();

                if (resp.error) {
                    avideoAlertError(resp.msg || 'Unknown error');
                    return;
                }

                // Show payment details and redirect to Accept Hosted
                if (resp.token && resp.url) {
                    const strWindowFeatures = "directories=no,titlebar=no,toolbar=no,location=no,status=no,menubar=no,resizable=no,height=650,width=1000";
                    openWindowWithPost(resp.url, 'Authorize.Net', {
                        'token': resp.token
                    }, strWindowFeatures);
                    return;
                }

                // Fallback
                avideoAlertSuccess(resp.msg || "<?php echo __('Request processed successfully'); ?>");
            },
            error: function(xhr, status, error) {
                modal.hidePleaseWait();
                console.error('XHR Error:', xhr, status, error);
                try {
                    var errorResp = JSON.parse(xhr.responseText);
                    avideoAlertError(errorResp.msg || 'Connection error');
                } catch (e) {
                    avideoAlertError('Connection error: ' + (error || 'Unknown error'));
                }
            }
        });
    }

    function openAuthorizeNetProfileManager() {
        modal.showPleaseWait();
        fetch(webSiteRootURL + 'plugin/AuthorizeNet/getProfileManager.json.php')
            .then(response => response.json())
            .then(data => {
                modal.hidePleaseWait();
                if (!data.error && data.token && data.url) {
                    avideoDialogWithPost(data.url, {
                        'token': data.token
                    });
                } else {
                    avideoAlertError('Profile error: ' + (data.msg || 'Could not get profile token'));
                }
            })
            .catch(err => {
                modal.hidePleaseWait();
                avideoAlertError('Profile request failed: ' + err);
            });
    }
</script>
