<?php
$uniqid = uniqid('anetSub_');
?>

<div class="btn-group" role="group" aria-label="Authorize.Net Actions">
    <button type="button"
        class="btn btn-primary"
        id="AuthorizeNetRecurringBtn<?php echo $uniqid; ?>">
        <i class="fas fa-sync-alt"></i> <?php echo __('Subscribe with Authorize.Net'); ?>
    </button>
    <button class="btn btn-info" onclick="openAuthorizeNetProfileManager()">
        <i class="fas fa-user-cog"></i>
    </button>
</div>

<script>
    $(function() {
        $('#AuthorizeNetRecurringBtn<?php echo $uniqid; ?>').on('click', function(e) {
            e.preventDefault();
            modal.showPleaseWait();

            $.ajax({
                url: webSiteRootURL + 'plugin/AuthorizeNet/getAcceptHostedToken.json.php',
                type: 'POST',
                data: {
                    "plans_id": "<?php echo @$_GET['plans_id']; ?>"
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
        });
    });


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
