<?php
$myWallet = YPTWallet::getWallet(User::getId());
?>
<div class="panel panel-default">
    <div class="panel-heading"><?php echo __("Configurations"); ?></div>
    <div class="panel-body">
        <form id="form">
            <div class="form-group" style="<?php echo $walletDataObject->CryptoWalletEnabled ? '' : 'display:none;' ?>">
                <label for="CryptoWallet"><?php echo $walletDataObject->CryptoWalletName; ?>:</label>
                <input type="text" class="form-control" name="CryptoWallet" value="<?php echo $myWallet->getCrypto_wallet_address(); ?>">
            </div>
            <div class="form-group">
                <label for="donation_notification_url">
                    <?php echo __('Donation Notification URL'); ?> (Webhook):
                    <button type="button" class="btn btn-xs btn-info" data-toggle="collapse" data-target="#webhookDocs" style="margin-left: 5px;">
                        <i class="fa fa-question-circle"></i> <?php echo __('Help'); ?>
                    </button>
                </label>
                <input type="url" class="form-control" name="donation_notification_url" value="<?php echo YPTWallet::getDonationNotificationUrl(User::getId()); ?>" placeholder="<?php echo __('Donation Notification URL'); ?>"
                    title="<?php echo __('This URL will be called when a donation is made.'); ?>">

                <div class="well well-sm" style="margin-top: 10px;">
                    <h5><i class="fa fa-key"></i> <?php echo __('Your Webhook Secret Key:'); ?></h5>
                    <div class="input-group">
                        <input type="text" class="form-control" id="webhookSecret" value="<?php echo YPTWallet::getDonationNotificationSecret(User::getId()); ?>" readonly>
                        <span class="input-group-btn">
                            <button class="btn btn-default" type="button" onclick="copyToClipboard(document.getElementById('webhookSecret'))">
                                <i class="fa fa-copy"></i> <?php echo __('Copy'); ?>
                            </button>
                            <button class="btn btn-warning" type="button" onclick="regenerateSecret()" title="<?php echo __('Generate new secret key'); ?>">
                                <i class="fa fa-refresh"></i> <?php echo __('Regenerate'); ?>
                            </button>
                        </span>
                    </div>
                    <small class="text-muted"><?php echo __('Use this secret to verify webhook signatures. Keep it safe and private!'); ?></small>
                </div>

                <!-- Webhook Documentation -->
                <div class="collapse" id="webhookDocs" style="margin-top: 10px;">
                    <div class="panel panel-info">
                        <div class="panel-heading">
                            <h4 class="panel-title">
                                <i class="fa fa-info-circle"></i> <?php echo __('Webhook Documentation'); ?>
                            </h4>
                        </div>
                        <div class="panel-body">
                            <p><i class="fa fa-globe"></i> <strong><?php echo __('How it works:'); ?></strong></p>
                            <p><?php echo __('When someone makes a donation, AVideo will automatically send a POST request to your URL with the following parameters and security headers:'); ?></p>

                            <div class="well well-sm">
                                <h5><i class="fa fa-shield"></i> <?php echo __('Security Headers:'); ?></h5>
                                <table class="table table-condensed">
                                    <thead>
                                        <tr>
                                            <th><i class="fa fa-tag"></i> <?php echo __('Header'); ?></th>
                                            <th><i class="fa fa-info"></i> <?php echo __('Description'); ?></th>
                                            <th><i class="fa fa-eye"></i> <?php echo __('Example'); ?></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td><code>X-Webhook-Signature</code></td>
                                            <td><?php echo __('HMAC SHA256 signature of the POST data for verification'); ?></td>
                                            <td><code>sha256=abc123...</code></td>
                                        </tr>
                                        <tr>
                                            <td><code>X-Webhook-Timestamp</code></td>
                                            <td><?php echo __('Unix timestamp when the request was sent'); ?></td>
                                            <td><code><?php echo time(); ?></code></td>
                                        </tr>
                                        <tr>
                                            <td><code>Content-Type</code></td>
                                            <td><?php echo __('Request content type'); ?></td>
                                            <td><code>application/x-www-form-urlencoded</code></td>
                                        </tr>
                                        <tr>
                                            <td><code>User-Agent</code></td>
                                            <td><?php echo __('Identifies the request as coming from AVideo'); ?></td>
                                            <td><code>AVideoStreamer_*</code></td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>

                            <h5><i class="fa fa-list"></i> <?php echo __('Parameters explained:'); ?></h5>
                            <div class="table-responsive">
                                <table class="table table-condensed table-striped">
                                    <thead>
                                        <tr>
                                            <th><i class="fa fa-tag"></i> <?php echo __('Parameter'); ?></th>
                                            <th><i class="fa fa-info"></i> <?php echo __('Description'); ?></th>
                                            <th><i class="fa fa-eye"></i> <?php echo __('Example'); ?></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td><code>from_users_id</code></td>
                                            <td><?php echo __('ID of the user who made the donation'); ?></td>
                                            <td><span class="label label-info">1</span></td>
                                        </tr>
                                        <tr>
                                            <td><code>from_users_name</code></td>
                                            <td><?php echo __('Name of the user who made the donation'); ?></td>
                                            <td><span class="label label-info">John Doe</span></td>
                                        </tr>
                                        <tr>
                                            <td><code>currency</code></td>
                                            <td><?php echo __('Currency code configured in wallet'); ?></td>
                                            <td><span class="label label-warning">USD</span></td>
                                        </tr>
                                        <tr>
                                            <td><code>how_much</code></td>
                                            <td><?php echo __('Raw amount donated (numeric value)'); ?></td>
                                            <td><span class="label label-success">21</span></td>
                                        </tr>
                                        <tr>
                                            <td><code>how_much_human</code></td>
                                            <td><?php echo __('Formatted amount with currency symbol'); ?></td>
                                            <td><span class="label label-success">$21.00</span></td>
                                        </tr>
                                        <tr>
                                            <td><code>message</code></td>
                                            <td><?php echo __('Message sent with the donation'); ?></td>
                                            <td><span class="label label-default">Great content</span></td>
                                        </tr>
                                        <tr>
                                            <td><code>videos_id</code></td>
                                            <td><?php echo __('ID of the video (0 for live chat)'); ?></td>
                                            <td><span class="label label-primary">0</span></td>
                                        </tr>
                                        <tr>
                                            <td><code>users_id</code></td>
                                            <td><?php echo __('ID of the user receiving the donation (You)'); ?></td>
                                            <td><span class="label label-info">1</span></td>
                                        </tr>
                                        <tr>
                                            <td><code>time</code></td>
                                            <td><?php echo __('Unix timestamp when the donation was made'); ?></td>
                                            <td><span class="label label-default"><?php echo time(); ?></span></td>
                                        </tr>
                                        <tr>
                                            <td><code>extraParameters[superChat]</code></td>
                                            <td><?php echo __('Super chat flag (1 if super chat)'); ?></td>
                                            <td><span class="label label-warning">1</span></td>
                                        </tr>
                                        <tr>
                                            <td><code>extraParameters[message]</code></td>
                                            <td><?php echo __('Duplicate of message parameter for compatibility'); ?></td>
                                            <td><span class="label label-default">Great content</span></td>
                                        </tr>
                                        <tr>
                                            <td><code>extraParameters[live_transmitions_history_id]</code></td>
                                            <td><?php echo __('Live transmission ID (if donation during live stream)'); ?></td>
                                            <td><span class="label label-danger">32</span></td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>

                            <div class="alert alert-danger">
                                <i class="fa fa-shield"></i>
                                <strong><?php echo __('Security Warning:'); ?></strong>
                                <?php echo __('ALWAYS verify the webhook signature before processing any data. Never trust webhook data without proper signature verification!'); ?>
                            </div>

                            <div class="well well-sm">
                                <h5><i class="fa fa-code"></i> <?php echo __('PHP Verification Example:'); ?></h5>
                                <pre><code><?php echo htmlspecialchars('<?php
// Step 1: Get headers and raw POST data
$signature = $_SERVER[\'HTTP_X_WEBHOOK_SIGNATURE\'] ?? \'\';
$timestamp = $_SERVER[\'HTTP_X_WEBHOOK_TIMESTAMP\'] ?? \'\';
$rawPostData = file_get_contents(\'php://input\');

// Step 2: Your webhook secret (copy from above)
$webhookSecret = \'YOUR_WEBHOOK_SECRET_FROM_ABOVE\';

// Step 3: Verify timestamp (optional but recommended)
$currentTime = time();
$timeDifference = $currentTime - intval($timestamp);
if ($timeDifference > 300) { // 5 minutes tolerance
    http_response_code(400);
    exit(\'Request expired\');
}

// Step 4: Calculate expected signature
$expectedSignature = \'sha256=\' . hash_hmac(\'sha256\', $rawPostData, $webhookSecret);

// Step 5: Verify signature using timing-safe comparison
if (!hash_equals($signature, $expectedSignature)) {
    http_response_code(401);
    exit(\'Invalid signature\');
}

// Step 6: Signature is valid, process the webhook data
parse_str($rawPostData, $donationData);

// Now you can safely use the donation data
$donorId = $donationData[\'from_users_id\'];
$donorName = $donationData[\'from_users_name\'];
$amount = $donationData[\'how_much\'];
$formattedAmount = $donationData[\'how_much_human\'];
$message = $donationData[\'message\'];
$videoId = $donationData[\'videos_id\'];
$receiverId = $donationData[\'users_id\'];

// Your processing logic here...
// Example: Save to database, send notifications, etc.

// Always respond with 200 OK
http_response_code(200);
echo \'Webhook processed successfully\';
?>'); ?></code></pre>
                            </div>

                            <div class="well well-sm">
                                <h5><i class="fa fa-code"></i> <?php echo __('Node.js/JavaScript Example:'); ?></h5>
                                <pre><code><?php echo htmlspecialchars('const crypto = require(\'crypto\');
const express = require(\'express\');
const app = express();

// Middleware to get raw body
app.use(\'/webhook\', express.raw({type: \'application/x-www-form-urlencoded\'}));

app.post(\'/webhook\', (req, res) => {
    const signature = req.headers[\'x-webhook-signature\'];
    const timestamp = req.headers[\'x-webhook-timestamp\'];
    const rawBody = req.body;

    // Your webhook secret
    const webhookSecret = \'YOUR_WEBHOOK_SECRET_FROM_ABOVE\';

    // Verify timestamp
    const currentTime = Math.floor(Date.now() / 1000);
    const timeDifference = currentTime - parseInt(timestamp);
    if (timeDifference > 300) {
        return res.status(400).send(\'Request expired\');
    }

    // Calculate expected signature
    const expectedSignature = \'sha256=\' + crypto
        .createHmac(\'sha256\', webhookSecret)
        .update(rawBody)
        .digest(\'hex\');

    // Verify signature
    if (!crypto.timingSafeEqual(Buffer.from(signature), Buffer.from(expectedSignature))) {
        return res.status(401).send(\'Invalid signature\');
    }

    // Parse form data
    const params = new URLSearchParams(rawBody.toString());
    const donationData = Object.fromEntries(params);

    // Process webhook data
    console.log(\'Donation received:\', donationData);

    res.status(200).send(\'OK\');
});'); ?></code></pre>
                            </div>

                            <div class="alert alert-info">
                                <i class="fa fa-key"></i>
                                <strong><?php echo __('Security Best Practices:'); ?></strong>
                                <ul class="list-unstyled" style="margin-top: 10px;">
                                    <li><i class="fa fa-check text-success"></i> <?php echo __('Always use hash_equals() or crypto.timingSafeEqual() for signature comparison'); ?></li>
                                    <li><i class="fa fa-check text-success"></i> <?php echo __('Verify timestamp to prevent replay attacks'); ?></li>
                                    <li><i class="fa fa-check text-success"></i> <?php echo __('Use the raw POST body for signature calculation, not parsed data'); ?></li>
                                    <li><i class="fa fa-check text-success"></i> <?php echo __('Keep your webhook secret private and secure'); ?></li>
                                    <li><i class="fa fa-check text-success"></i> <?php echo __('Regenerate your webhook secret if compromised'); ?></li>
                                    <li><i class="fa fa-check text-success"></i> <?php echo __('Always respond with HTTP 200 for valid requests'); ?></li>
                                </ul>
                            </div>

                            <div class="alert alert-warning">
                                <i class="fa fa-exclamation-triangle"></i>
                                <strong><?php echo __('Important:'); ?></strong>
                                <?php echo __('Your webhook endpoint should respond with HTTP 200 status code for successful processing. The request timeout is 1 second, so ensure your endpoint responds quickly.'); ?>
                            </div>

                            <div class="alert alert-info">
                                <i class="fa fa-lightbulb-o"></i>
                                <strong><?php echo __('Use Cases:'); ?></strong>
                                <?php echo __('You can use this webhook to integrate with external systems, trigger notifications, update databases, send emails, integrate with Discord/Slack, or create custom donation alerts when donations are received.'); ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <button type="submit" class="btn btn-success btn-block"><i class="fas fa-save"></i> <?php echo __("Save"); ?></button>
        </form>
    </div>
</div>
<script>
    function copyToClipboard(element) {
        element.select();
        element.setSelectionRange(0, 99999);
        document.execCommand("copy");
        avideoToast("<?php echo __('Copied to clipboard!'); ?>");
    }

    function regenerateSecret() {
        avideoConfirmCallBack(
            __('Are you sure you want to generate a new webhook secret? This will invalidate the current one and any existing integrations will need to be updated with the new secret.'),
            function() {
                // Confirm callback - user clicked confirm
                modal.showPleaseWait();
                $.ajax({
                    url: webSiteRootURL + 'plugin/YPTWallet/view/saveConfiguration.php',
                    data: {
                        regenerate_webhook_secret: 1
                    },
                    type: 'post',
                    success: function(response) {
                        if (!response.error && response.new_webhook_secret) {
                            $('#webhookSecret').val(response.webhook_secret);
                            avideoToastSuccess(__('New webhook secret generated successfully!'));
                        } else {
                            avideoAlertError(__('Error generating new secret'));
                        }
                        modal.hidePleaseWait();
                    },
                    error: function() {
                        avideoAlertError(__('Failed to regenerate webhook secret'));
                        modal.hidePleaseWait();
                    }
                });
            },
            function() {
                // Cancel callback - user clicked cancel
                console.log("User cancelled webhook secret regeneration");
            }
        );
    }

    $(document).ready(function() {
        $("#form").submit(function(event) {
            event.preventDefault();
            modal.showPleaseWait();
            $.ajax({
                url: webSiteRootURL + 'plugin/YPTWallet/view/saveConfiguration.php',
                data: $("#form").serialize(),
                type: 'post',
                success: function(response) {
                    if (!response.error) {
                        avideoAlertSuccess(__("Configuration Saved"));
                        // Update webhook secret display if returned
                        if (response.webhook_secret) {
                            document.getElementById('webhookSecret').value = response.webhook_secret;
                        }
                    } else {
                        avideoAlertError(response.msg);
                    }
                    modal.hidePleaseWait();
                }
            });
        });
    });
</script>
