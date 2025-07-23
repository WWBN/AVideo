
<?php
// Authorize.Net Accept Hosted payment & profile management buttons for YPTWallet integration
?>
<div class="btn-group" role="group" aria-label="Authorize.Net Actions">
    <button class="btn btn-primary" onclick="startAuthorizeNetAcceptHosted()">
        <i class="fas fa-credit-card"></i> Pay with Authorize.Net
    </button>
    <button class="btn btn-info" onclick="openAuthorizeNetProfileManager()">
        <i class="fas fa-user-cog"></i>
    </button>
</div>

<script>
function startAuthorizeNetAcceptHosted() {
    modal.showPleaseWait();
    var amount = $('#value<?php echo @$_GET['plans_id']; ?>').val();
    if (!amount || isNaN(amount) || amount <= 0) {
        avideoAlertError('Invalid amount');
        return;
    }
    fetch(webSiteRootURL + 'plugin/AuthorizeNet/getAcceptHostedToken.json.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded'
        },
        body: 'amount=' + encodeURIComponent(amount)
    })
    .then(response => response.json())
    .then(data => {
        modal.hidePleaseWait();
        if (!data.error && data.token && data.url) {
            const strWindowFeatures = "directories=no,titlebar=no,toolbar=no,location=no,status=no,menubar=no,resizable=no,height=650,width=1000";
            openWindowWithPost(data.url, 'Authorize.Net' ,{'token': data.token}, strWindowFeatures);
        } else {
            avideoAlertError('Payment error: ' + (data.msg || 'Could not get payment token'));
        }
    })
    .catch(err => {
        modal.hidePleaseWait();
        avideoAlertError('Payment request failed: ' + err);
    });
}

function openAuthorizeNetProfileManager() {
    modal.showPleaseWait();
    fetch(webSiteRootURL + 'plugin/AuthorizeNet/getProfileManager.json.php')
    .then(response => response.json())
    .then(data => {
        modal.hidePleaseWait();
        if (!data.error && data.token && data.url) {
            avideoDialogWithPost(data.url, {'token': data.token});
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
