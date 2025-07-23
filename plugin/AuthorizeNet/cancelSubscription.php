<?php
require_once __DIR__ . '/../../videos/configuration.php';

// Check if user is logged in
if (!User::isLogged()) {
    forbiddenPage('You must be logged in to access this page');
}

// Check if AuthorizeNet plugin is enabled
$plugin = AVideoPlugin::loadPluginIfEnabled('AuthorizeNet');
if (empty($plugin)) {
    forbiddenPage('AuthorizeNet plugin is disabled');
}

$users_id = User::getId();
$subscriptionId = $_GET['subscription_id'] ?? '';

// If specific subscription ID is provided, get that subscription
$subscription = null;
if (!empty($subscriptionId)) {
    $result = AuthorizeNet::getSubscriptionWithCurrentStatus($subscriptionId);
    if (!$result['error'] && !empty($result['subscription'])) {
        $subscription = $result['subscription'];
    }
}

$_page = new Page(['Manage Subscription']);
?>

<div class="container-fluid">
    <div class="panel panel-default">
        <div class="panel-heading">
            <h4>
                <i class="fa fa-credit-card"></i>
                <?php echo __('Manage Subscriptions'); ?>
            </h4>
        </div>
        <div class="panel-body">

            <?php if (!empty($subscription)): ?>
                <!-- Single Subscription View -->
                <div class="well">
                    <div class="row">
                        <div class="col-md-8">
                            <h4 class="media-heading">
                                <i class="fa fa-file-text-o"></i>
                                <?php echo htmlspecialchars($subscription['name'] ?? 'Subscription'); ?>
                                <?php
                                $status = strtolower($subscription['currentStatus'] ?? $subscription['status']);
                                $badgeClass = '';
                                switch($status) {
                                    case 'active':
                                        $badgeClass = 'label-success';
                                        break;
                                    case 'suspended':
                                        $badgeClass = 'label-warning';
                                        break;
                                    case 'canceled':
                                    case 'expired':
                                        $badgeClass = 'label-danger';
                                        break;
                                    default:
                                        $badgeClass = 'label-default';
                                }
                                ?>
                                <span class="label <?php echo $badgeClass; ?>">
                                    <?php echo htmlspecialchars($subscription['currentStatus'] ?? $subscription['status']); ?>
                                </span>
                            </h4>

                            <div class="row" style="margin-top: 15px;">
                                <div class="col-sm-6">
                                    <p><strong><i class="fa fa-tag"></i> <?php echo __('Subscription ID'); ?>:</strong><br>
                                    <?php echo htmlspecialchars($subscription['subscriptionId']); ?></p>
                                </div>
                                <div class="col-sm-6">
                                    <p><strong><i class="fa fa-dollar"></i> <?php echo __('Amount'); ?>:</strong><br>
                                    $<?php echo number_format($subscription['amount'], 2); ?></p>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-sm-6">
                                    <p><strong><i class="fa fa-clock-o"></i> <?php echo __('Billing Cycle'); ?>:</strong><br>
                                    Every <?php echo $subscription['interval']['length']; ?>
                                    <?php echo $subscription['interval']['unit']; ?></p>
                                </div>
                                <?php if (!empty($subscription['startDate'])): ?>
                                <div class="col-sm-6">
                                    <p><strong><i class="fa fa-calendar"></i> <?php echo __('Start Date'); ?>:</strong><br>
                                    <?php echo htmlspecialchars($subscription['startDate']); ?></p>
                                </div>
                                <?php endif; ?>
                            </div>

                            <?php if (!empty($subscription['totalOccurrences']) || !empty($subscription['plans_id'])): ?>
                            <div class="row">
                                <?php if (!empty($subscription['totalOccurrences'])): ?>
                                <div class="col-sm-6">
                                    <p><strong><i class="fa fa-repeat"></i> <?php echo __('Total Occurrences'); ?>:</strong><br>
                                    <?php echo htmlspecialchars($subscription['totalOccurrences']); ?></p>
                                </div>
                                <?php endif; ?>

                                <?php if (!empty($subscription['plans_id'])): ?>
                                <div class="col-sm-6">
                                    <p><strong><i class="fa fa-list-alt"></i> <?php echo __('Plan ID'); ?>:</strong><br>
                                    <?php echo htmlspecialchars($subscription['plans_id']); ?></p>
                                </div>
                                <?php endif; ?>
                            </div>
                            <?php endif; ?>
                        </div>

                        <div class="col-md-4 text-right">
                            <?php if ($subscription['isActive'] ?? false): ?>
                                <button type="button"
                                        class="btn btn-danger btn-lg btn-block"
                                        onclick="cancelSubscription('<?php echo htmlspecialchars($subscription['subscriptionId']); ?>')">
                                    <i class="fa fa-times"></i> <?php echo __('Cancel Subscription'); ?>
                                </button>
                            <?php else: ?>
                                <div class="alert alert-info">
                                    <i class="fa fa-info-circle"></i>
                                    <?php echo __('This subscription is no longer active'); ?>
                                </div>
                            <?php endif; ?>

                            <button type="button"
                                    class="btn btn-default btn-block"
                                    onclick="refreshSubscriptionStatus('<?php echo htmlspecialchars($subscription['subscriptionId']); ?>')">
                                <i class="fa fa-refresh"></i> <?php echo __('Refresh Status'); ?>
                            </button>
                        </div>
                    </div>
                </div>

                <div class="text-center">
                    <a href="<?php echo $global['webSiteRootURL']; ?>plugin/AuthorizeNet/cancelSubscription.php" class="btn btn-primary">
                        <i class="fa fa-arrow-left"></i> <?php echo __('Back to All Subscriptions'); ?>
                    </a>
                </div>

            <?php else: ?>
                <!-- All Subscriptions View -->
                <div id="subscriptionsContainer">
                    <div class="text-center">
                        <i class="fa fa-spinner fa-spin fa-2x"></i>
                        <p><?php echo __('Loading your subscriptions...'); ?></p>
                    </div>
                </div>

                <div class="text-center" style="margin-top: 20px;">
                    <button type="button" class="btn btn-primary" onclick="loadSubscriptions()">
                        <i class="fa fa-refresh"></i> <?php echo __('Refresh Subscriptions'); ?>
                    </button>
                </div>
            <?php endif; ?>

        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    <?php if (empty($subscription)): ?>
    loadSubscriptions();
    <?php endif; ?>
});

function loadSubscriptions() {
    $('#subscriptionsContainer').html(`
        <div class="text-center">
            <i class="fa fa-spinner fa-spin fa-2x"></i>
            <p><?php echo __('Loading your subscriptions...'); ?></p>
        </div>
    `);

    $.ajax({
        url: webSiteRootURL + 'plugin/AuthorizeNet/getSubscriptions.json.php',
        type: 'GET',
        dataType: 'json',
        success: function(response) {
            if (response.error) {
                $('#subscriptionsContainer').html(`
                    <div class="alert alert-danger">
                        <i class="fa fa-exclamation-triangle"></i>
                        <strong><?php echo __('Error'); ?>:</strong> ${response.msg}
                    </div>
                `);
                return;
            }

            if (response.subscriptions.length === 0) {
                $('#subscriptionsContainer').html(`
                    <div class="alert alert-info">
                        <i class="fa fa-info-circle"></i>
                        <strong><?php echo __('No subscriptions found'); ?></strong>
                        <p><?php echo __('You don\'t have any active subscriptions at the moment.'); ?></p>
                    </div>
                `);
                return;
            }

            let html = '';
            response.subscriptions.forEach(function(subscription) {
                html += renderSubscriptionCard(subscription);
            });
            $('#subscriptionsContainer').html(html);
        },
        error: function() {
            $('#subscriptionsContainer').html(`
                <div class="alert alert-danger">
                    <i class="fa fa-exclamation-triangle"></i>
                    <strong><?php echo __('Error'); ?>:</strong> <?php echo __('Failed to load subscriptions'); ?>
                </div>
            `);
        }
    });
}

function renderSubscriptionCard(subscription) {
    const status = subscription.currentStatus ? subscription.currentStatus.toLowerCase() : subscription.status.toLowerCase();
    const displayStatus = subscription.currentStatus || subscription.status;
    const isActive = subscription.isActive || (subscription.status && subscription.status.toLowerCase() === 'active');

    let badgeClass = '';
    switch(status) {
        case 'active':
            badgeClass = 'label-success';
            break;
        case 'suspended':
            badgeClass = 'label-warning';
            break;
        case 'canceled':
        case 'expired':
            badgeClass = 'label-danger';
            break;
        default:
            badgeClass = 'label-default';
    }

    return `
        <div class="well">
            <div class="row">
                <div class="col-md-8">
                    <h4 class="media-heading">
                        <i class="fa fa-file-text-o"></i>
                        ${subscription.name || 'Subscription'}
                        <span class="label ${badgeClass}">
                            ${displayStatus}
                        </span>
                    </h4>

                    <div class="row" style="margin-top: 15px;">
                        <div class="col-sm-6">
                            <p><strong><i class="fa fa-tag"></i> <?php echo __('Subscription ID'); ?>:</strong><br>
                            ${subscription.subscriptionId}</p>
                        </div>
                        <div class="col-sm-6">
                            <p><strong><i class="fa fa-dollar"></i> <?php echo __('Amount'); ?>:</strong><br>
                            $${parseFloat(subscription.amount).toFixed(2)}</p>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-sm-6">
                            <p><strong><i class="fa fa-clock-o"></i> <?php echo __('Billing Cycle'); ?>:</strong><br>
                            Every ${subscription.interval.length} ${subscription.interval.unit}</p>
                        </div>
                        ${subscription.startDate ? `
                        <div class="col-sm-6">
                            <p><strong><i class="fa fa-calendar"></i> <?php echo __('Start Date'); ?>:</strong><br>
                            ${subscription.startDate}</p>
                        </div>
                        ` : ''}
                    </div>

                    ${subscription.plans_id ? `
                    <div class="row">
                        <div class="col-sm-6">
                            <p><strong><i class="fa fa-list-alt"></i> <?php echo __('Plan ID'); ?>:</strong><br>
                            ${subscription.plans_id}</p>
                        </div>
                    </div>
                    ` : ''}
                </div>

                <div class="col-md-4 text-right">
                    ${isActive ? `
                        <button type="button"
                                class="btn btn-danger btn-block"
                                onclick="cancelSubscription('${subscription.subscriptionId}')">
                            <i class="fa fa-times"></i> <?php echo __('Cancel'); ?>
                        </button>
                    ` : ''}
                </div>
            </div>
        </div>
    `;
}

function cancelSubscription(subscriptionId) {
    swal({
        title: "<?php echo __('Are you sure?'); ?>",
        text: "<?php echo __('This will cancel your subscription permanently. You will not be able to recover this action!'); ?>",
        icon: "warning",
        buttons: [
            "<?php echo __('Cancel'); ?>",
            "<?php echo __('Yes, cancel subscription'); ?>"
        ],
        dangerMode: true,
    }).then(function(willCancel) {
        if (willCancel) {
            modal.showPleaseWait();

            $.ajax({
                url: webSiteRootURL + 'plugin/AuthorizeNet/cancelSubscription.json.php',
                type: 'POST',
                data: {
                    subscriptionId: subscriptionId
                },
                dataType: 'json',
                success: function(response) {
                    modal.hidePleaseWait();

                    if (response.error) {
                        swal("<?php echo __('Error'); ?>", response.msg, "error");
                    } else {
                        swal("<?php echo __('Success'); ?>", "<?php echo __('Subscription canceled successfully'); ?>", "success")
                        .then(function() {
                            <?php if (!empty($subscription)): ?>
                            location.reload();
                            <?php else: ?>
                            loadSubscriptions();
                            <?php endif; ?>
                        });
                    }
                },
                error: function() {
                    modal.hidePleaseWait();
                    swal("<?php echo __('Error'); ?>", "<?php echo __('Failed to cancel subscription'); ?>", "error");
                }
            });
        }
    });
}

function refreshSubscriptionStatus(subscriptionId) {
    modal.showPleaseWait();

    $.ajax({
        url: webSiteRootURL + 'plugin/AuthorizeNet/getSubscriptionStatus.json.php',
        type: 'GET',
        data: {
            subscriptionId: subscriptionId
        },
        dataType: 'json',
        success: function(response) {
            modal.hidePleaseWait();

            if (response.error) {
                avideoAlert("<?php echo __('Error'); ?>", response.msg, "error");
            } else {
                avideoToast("<?php echo __('Status refreshed successfully'); ?>");
                location.reload();
            }
        },
        error: function() {
            modal.hidePleaseWait();
            avideoAlert("<?php echo __('Error'); ?>", "<?php echo __('Failed to refresh status'); ?>", "error");
        }
    });
}

function viewSubscriptionDetails(subscriptionId) {
    modal.showPleaseWait();

    $.ajax({
        url: webSiteRootURL + 'plugin/AuthorizeNet/getSubscriptionDetails.json.php',
        type: 'GET',
        data: { subscriptionId: subscriptionId },
        dataType: 'json',
        success: function(response) {
            modal.hidePleaseWait();

            if (response.error) {
                avideoAlert("<?php echo __('Error'); ?>", response.msg, "error");
                return;
            }

            const subscription = response.subscription;
            const modalContent = `
                <div class="modal fade" id="subscriptionDetailsModal" tabindex="-1">
                    <div class="modal-dialog modal-lg">
                        <div class="modal-content">
                            <div class="modal-header">
                                <button type="button" class="close" data-dismiss="modal">&times;</button>
                                <h4 class="modal-title">
                                    <i class="fa fa-file-text-o"></i> <?php echo __('Subscription Details'); ?>
                                </h4>
                            </div>
                            <div class="modal-body">
                                <div class="row">
                                    <div class="col-sm-6">
                                        <h5><i class="fa fa-info-circle"></i> <?php echo __('Basic Information'); ?></h5>
                                        <table class="table table-striped">
                                            <tr><td><strong><?php echo __('Subscription ID'); ?>:</strong></td><td>${subscription.subscriptionId}</td></tr>
                                            <tr><td><strong><?php echo __('Name'); ?>:</strong></td><td>${subscription.name || 'N/A'}</td></tr>
                                            <tr><td><strong><?php echo __('Status'); ?>:</strong></td><td><span class="label label-${getStatusClass(subscription.currentStatus || subscription.status)}">${subscription.currentStatus || subscription.status}</span></td></tr>
                                            <tr><td><strong><?php echo __('Amount'); ?>:</strong></td><td>$${parseFloat(subscription.amount).toFixed(2)}</td></tr>
                                        </table>
                                    </div>
                                    <div class="col-sm-6">
                                        <h5><i class="fa fa-calendar"></i> <?php echo __('Billing Information'); ?></h5>
                                        <table class="table table-striped">
                                            <tr><td><strong><?php echo __('Billing Cycle'); ?>:</strong></td><td>Every ${subscription.interval.length} ${subscription.interval.unit}</td></tr>
                                            <tr><td><strong><?php echo __('Start Date'); ?>:</strong></td><td>${subscription.startDate || 'N/A'}</td></tr>
                                            <tr><td><strong><?php echo __('Next Payment'); ?>:</strong></td><td>${subscription.nextPaymentDate || 'N/A'}</td></tr>
                                            <tr><td><strong><?php echo __('Total Occurrences'); ?>:</strong></td><td>${subscription.totalOccurrences || 'Unlimited'}</td></tr>
                                        </table>
                                    </div>
                                </div>
                                ${subscription.paymentHistory ? `
                                <div class="row">
                                    <div class="col-sm-12">
                                        <h5><i class="fa fa-history"></i> <?php echo __('Payment History'); ?></h5>
                                        <div class="table-responsive">
                                            <table class="table table-striped">
                                                <thead>
                                                    <tr>
                                                        <th><?php echo __('Date'); ?></th>
                                                        <th><?php echo __('Amount'); ?></th>
                                                        <th><?php echo __('Status'); ?></th>
                                                        <th><?php echo __('Transaction ID'); ?></th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    ${subscription.paymentHistory.map(payment => `
                                                        <tr>
                                                            <td>${payment.date}</td>
                                                            <td>$${parseFloat(payment.amount).toFixed(2)}</td>
                                                            <td><span class="label label-${payment.status === 'completed' ? 'success' : 'warning'}">${payment.status}</span></td>
                                                            <td>${payment.transactionId}</td>
                                                        </tr>
                                                    `).join('')}
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                                ` : ''}
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-default" data-dismiss="modal">
                                    <i class="fa fa-times"></i> <?php echo __('Close'); ?>
                                </button>
                                ${subscription.isActive ? `
                                <button type="button" class="btn btn-danger" onclick="cancelSubscription('${subscription.subscriptionId}')">
                                    <i class="fa fa-times"></i> <?php echo __('Cancel Subscription'); ?>
                                </button>
                                ` : ''}
                            </div>
                        </div>
                    </div>
                </div>
            `;

            $('body').append(modalContent);
            $('#subscriptionDetailsModal').modal('show');

            $('#subscriptionDetailsModal').on('hidden.bs.modal', function() {
                $(this).remove();
            });
        },
        error: function() {
            modal.hidePleaseWait();
            avideoAlert("<?php echo __('Error'); ?>", "<?php echo __('Failed to load subscription details'); ?>", "error");
        }
    });
}

function getStatusClass(status) {
    switch(status.toLowerCase()) {
        case 'active': return 'success';
        case 'suspended': return 'warning';
        case 'canceled':
        case 'expired': return 'danger';
        default: return 'default';
    }
}
</script>

<?php
$_page->print();
?>
